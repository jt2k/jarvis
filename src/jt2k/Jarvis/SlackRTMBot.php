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
        $result = $restapi->request('https://slack.com/api/rtm.start', array(
            'get' => array(
                'token' => $this->oauth_token
            )
        ));
        if (is_object($result) && !empty($result->url)) {
            $this->client = new \Wrench\Client(
                $result->url,
                'http://slack.com/',
                array('on_data_callback' => array($this, 'payloadCallback'))
            );
            if ($this->client->connect()) {
                $this->connected = true;
                $this->listen();
            } else {
                throw new \Exception('Failed to connect');
            }

        }
    }

    protected function send($event)
    {  
        $this->id++;
        $this->client->sendData(json_encode($event));
    }

    public function payloadCallback($payload)
    {
        $json = $payload->getPayload();
        $event = json_decode($json);
        if (!is_object($event)) {
            return;
        }
        if ($this->debug) {
            echo "Received event:\n";
            print_r($event);
        }
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
                $this->send($responseEvent);
            }
        }
    }

    protected function ping()
    {
        $event = array(
            'id' => $this->id,
            'type' => 'ping'
        );
        $this->send($event);
    }

    protected function listen()
    {
        $lastPing = 0;
        while (true) {
            if ($this->debug) {
                echo ".";
            }
            if ($lastPing < time() - 2) {
                $this->ping();
                $lastPing = time();
            }
            $this->client->receive();
        }
    }

    protected function disconnect()
    {
        if ($this->connected) {
            $this->client->disconnect();
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}