<?php
use jt2k\Jarvis\SlackBot;

class SlackBotTest extends PHPUnit_Framework_TestCase
{
    protected $bot;

    protected function setUp()
    {
        $GLOBALS['jarvis_config']['slackbot_token'] = 'FOOBAR123';
        $this->bot = new SlackBot($GLOBALS['jarvis_config']);
    }

    protected function respond($text)
    {
        $request = array(
            'user_name' => 'phpunit',
            'text' => $text,
            'bot_type' => 'slack',
            'token' => $GLOBALS['jarvis_config']['slackbot_token']
        );
        $_POST = $request;
        return $this->bot->respond($request, ['return' => true]);
    }

    public function testStatus()
    {
        $response = $this->respond('status');
        $this->assertContains('Bot type: SlackBot', $response);
        $this->assertRegExp('/PID: \d+/', $response);
        $this->assertRegExp('/Memory usage: \d/', $response);
    }

    public function testMissingConfiguration()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('slackbot_token must be configured');

        $this->bot = new SlackBot([]);
        $this->respond('status');
    }
}
