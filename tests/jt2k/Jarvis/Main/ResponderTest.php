<?php
use jt2k\Jarvis\Responder;

class ResponderTest extends PHPUnit_Framework_TestCase
{
    protected static $name = 'TestResponder';
    protected static $command = 'confirm';
    protected $responder;

    protected function setUp()
    {
        if (static::$name == 'TestResponder') {
            require_once 'TestResponder.php';
        }
        $this->responder = $this->initResponder(static::$command);
    }

    protected function initResponder($text)
    {
        $communication = array(
            'user_name' => 'phpunit',
            'text' => $text,
            'bot_type' => 'cli'
        );
        $class = 'jt2k\\Jarvis\\' . static::$name;
        $regex = $class::$pattern;
        if (preg_match("/{$regex}/i", $text, $matches)) {
            return new $class(
                $GLOBALS['jarvis_config'],
                $communication,
                $matches
            );
        } else {
            $this->fail('Initialized responder did not match command');
        }
    }

    public function testRespond()
    {
        $this->assertEquals('Affirmative', $this->responder->respond());
    }

    public function testGetName()
    {
        $this->assertEquals('Test', $this->responder->getName());
    }

    public function testHasStorage()
    {
        $this->assertFalse($this->responder->hasStorage());
    }
}
