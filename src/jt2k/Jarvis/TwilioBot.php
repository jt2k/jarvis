<?php
namespace jt2k\Jarvis;

class TwilioBot extends Bot
{
    public function __construct(array $config)
    {
        $config['max_response_length'] = 800; // limit to 5 messages of 160 characters
        return parent::__construct($config, 'sms');
    }

    public function respond(array $post)
    {
        if (isset($post['Body']) && isset($post['From'])) {
            $communication = array(
                'text' => $post['Body'],
                'user_name' => $post['From'],
                'bot_type' => 'twilio'
            );
            $result = $this->generateResponse($communication);
            if ($result) {
                header("Content-type: text/xml");
                echo '<?xml version="1.0" encoding="UTF-8"?>';
                echo "\n<Response>\n\t<Message><![CDATA[{$result}]]></Message>\n</Response>";
            }
        }
    }
}
