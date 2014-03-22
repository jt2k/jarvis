<?php
namespace jt2k\Jarvis;

class SlackBot extends Bot
{
    public function __construct(array $config)
    {
        if (!isset($config['slackbot_token'])) {
            throw new \Exception('slackbot_token must be configured');
        }

        return parent::__construct($config);
    }

    protected function parsePost()
    {
        $communication = array();
        $post = isset($_POST)?$_POST:array();
        $fields = array(
            'token',
            'team_id',
            'channel_id',
            'channel_name',
            'timestamp',
            'user_id',
            'user_name',
            'text'
        );
        foreach ($post as $field => $value) {
            if (in_array($field, $fields)) {
                $communication[$field] = $value;
            }
        }
        if (isset($communication['text'])) {
            $communication['text'] = preg_replace('/<(http[^\|]+)\|[^>]+>/', '$1', $communication['text']);
            $communication['text'] = preg_replace('/<(http[^\>]+)>/', '$1', $communication['text']);
        }

        return $communication;
    }

    protected function isValidCommunication($communication)
    {
        if (!isset($communication['token']) || $communication['token'] !== $this->config['slackbot_token']) {
            return false;  // invalid token
        }

        if (!isset($communication['text'])) {
            return false; // no text
        }

        return true;
    }

    public function respond(array $post, $return = false)
    {
        $communication = $this->parsePost();
        if (!$this->isValidCommunication($communication)) {
            return false;
        }

        $result = $this->generateResponse($communication);
        if ($return) {
            return $result;
        } elseif ($result) {
            echo json_encode(array('text' => $result, 'parse' => 'full'));
        } else {
            return false;
        }
    }
}
