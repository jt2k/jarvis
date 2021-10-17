<?php

use jt2k\Jarvis\SlackBot;
use PHPUnit\Framework\TestCase;

class SlackBotTest extends TestCase
{
    protected $bot;

    protected function setUp(): void
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
        $this->assertStringContainsString('Bot type: SlackBot', $response);
        $this->assertMatchesRegularExpression('/PID: \d+/', $response);
        $this->assertMatchesRegularExpression('/Memory usage: \d/', $response);
    }

    public function testMissingConfiguration()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('slackbot_token must be configured');

        $this->bot = new SlackBot([]);
        $this->respond('status');
    }
}
