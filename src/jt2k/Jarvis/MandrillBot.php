<?php
namespace jt2k\Jarvis;

class MandrillBot extends Bot
{
    public function __construct(array $config)
    {
        if (!isset($config['email_address'])) {
            throw new \Exception('email address must be configured');
        }
        if (!isset($config['mandrill_username']) || !isset($config['mandrill_password'])) {
            throw new \Exception('mandrill username and password must be configured');
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

    protected function getCommunication($msg)
    {
        if (!isset($msg->from_email)) {
            return false;
        }

        $text = false;
        if (isset($msg->subject)) {
            $text = $msg->subject;
        } elseif (isset($msg->text)) {
            $text = $msg->text;
        }

        if (!$text) {
            return false;
        }

        return array('text' => $text, 'user_name' => $msg->from_email);
    }

    public function respond(array $communication, \stdClass $msg)
    {
        $result = $this->generateResponse($communication);

        if (preg_match('/^\s*re:/', $msg->subject)) {
            $subject = $msg->subject;
        } else {
            $subject = 'Re: ' . $msg->subject;
        }

        if (isset($this->config['name'])) {
            $from = array($this->config['email_address'] => $this->config['name']);
        } else {
            $from = array($this->config['email_address']);
        }

        $to = array($msg->from_email);

        if (isset($msg->headers->{'Message-Id'})) {
            $message_id = $msg->headers->{'Message-Id'};
        } else {
            $message_id = false;
        }
        // TODO - set reply to id

        $transport = \Swift_SmtpTransport::newInstance('smtp.mandrillapp.com', 587);
        $transport->setUsername($this->config['mandrill_username']);
        $transport->setPassword($this->config['mandrill_password']);
        $swift = \Swift_Mailer::newInstance($transport);

        $message = new \Swift_Message($subject);
        $message->setFrom($from);
        $message->setBody($result);
        $message->setTo($to);

        $result = $swift->send($message);
    }

    public function respondToAll(array $post)
    {
        if (isset($post['mandrill_events']) && ($messages = json_decode($post['mandrill_events'])) && is_array($messages)) {
            foreach ($messages as $message) {
                if (is_object($message->msg) && $communication = $this->getCommunication($message->msg)) {
                    $this->respond($communication, $message->msg);
                }
            }
        }
    }
}
