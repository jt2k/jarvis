<?php

use jt2k\Jarvis\Bot;
use PHPUnit\Framework\TestCase;

class BotTest extends TestCase
{
    protected $bot;

    protected function setUp(): void
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
        $this->assertStringContainsString('Bot type: Bot', $response);
        $this->assertMatchesRegularExpression('/PID: \d+/', $response);
        $this->assertMatchesRegularExpression('/Memory usage: \d/', $response);
    }

    public function testHelp()
    {
        $response = $this->respond('help');
        $this->assertStringContainsString("Responders:\n", $response);
    }
}
