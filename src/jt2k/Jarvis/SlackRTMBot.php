<?php
namespace jt2k\Jarvis;
use jt2k\RestApi\RestApi;

class SlackRTMBot extends Bot
{
    protected $oauth_token;
    protected $debug = false;
    protected $connected = false;
    protected $client;
    protected $id = 1;

    public function __construct(array $config)
    {
        $parameters = array('oauth_token');
        foreach ($parameters as $parameter) {
            if (isset($config['slack_' . $parameter])) {
                $this->$parameter = $config['slack_' . $parameter];
            }
            if (is_null($this->$parameter)) {
                throw new \Exception("slack_{$parameter} must be configured");
            }
        }
        return parent::__construct($config, 'slack');
    }

    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;
    }

    public function connect()
    {
        $restapi = new RestApi();
        $restapi->setCacheLife(0);
        $result = $restapi->request('https://slack.com/api/rtm.connect', array(
            'get' => array(
                'token' => $this->oauth_token
            )
        ));
        if (is_object($result) && !empty($result->url)) {
            $loop = \React\EventLoop\Factory::create();
            $logger = new \Zend\Log\Logger();
            if ($this->debug) {
                $writer = new \Zend\Log\Writer\Stream("php://output");
            } else {
                $writer = new \Zend\Log\Writer\Noop;
            }
            $logger->addWriter($writer);
            $this->client = $client = new \Devristo\Phpws\Client\WebSocket($result->url, $loop, $logger);

            $client->on("connect", function() use ($logger, $client){
                $logger->notice("Connected");
            });

            $client->on("message", function($message) use ($client, $logger){
                $event = json_decode($message->getData());
                if (!is_object($event)) {
                    $logger->warning("Invalid JSON");
                    return;
                }
                $logger->notice("Received message:\n" . print_r($event, true));

                if (isset($event->type) && $event->type == 'message' && empty($event->subtype)) {
                    $user = $event->user; // TODO: get username
                    $channel = $event->channel;
                    $message = $event->text;
                    $message = preg_replace('/<(http[^\|]+)\|[^>]+>/', '$1', $message);
                    $message = preg_replace('/<(http[^\>]+)>/', '$1', $message);

                    $communication = array(
                        'user_name' => $user,
                        'text' => $message,
                        'bot_type' => 'slack'
                    );

                    if ($response = $this->respond($communication)) {
                        $responseEvent = array(
                            'id' => $this->id,
                            'type' => 'message',
                            'channel' => $channel,
                            'text' => $response
                        );
                        if (!empty($event->thread_ts)) {
                            $responseEvent['thread_ts'] = $event->thread_ts;
                        }
                        $logger->notice("Sending message:\n" . print_r($responseEvent, true));
                        $client->send(json_encode($responseEvent));
                    }
                }
            });

            $loop->addPeriodicTimer(5, function() use($client, $logger){
                $id = ++$this->id;
                $logger->info('Sending ping');
                $event = array(
                    'id' => $id,
                    'type' => 'ping'
                );
                $client->send(json_encode($event));
            });

            $client->open();
            $loop->run();
        }
    }

    public function __destruct()
    {
        $this->client->close();
    }
}
