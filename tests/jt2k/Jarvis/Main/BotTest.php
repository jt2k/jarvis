<?php
use jt2k\Jarvis\Bot;

class BotTest extends PHPUnit_Framework_TestCase
{
    protected $bot;

    protected function setUp()
    {
        $this->bot = new Bot($GLOBALS['jarvis_config']);
    }

    protected function respond($text)
    {
        $request = array(
            'user_name' => 'phpunit',
            'text' => $text,
            'bot_type' => 'cli'
        );
        return (string)$this->bot->respond($request);
    }

    public function testBot()
    {
        $this->assertInstanceOf('jt2k\Jarvis\Bot', $this->bot);
    }

    public function testStatus()
    {
        $response = $this->respond('status');
        $this->assertContains('Bot type: Bot', $response);
        $this->assertRegExp('/PID: \d+/', $response);
        $this->assertRegExp('/Memory usage: \d/', $response);
    }

    public function testHelp()
    {
        $response = $this->respond('help');
        $this->assertContains("Responders:\n", $response);
    }
}