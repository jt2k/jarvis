<?php
use jt2k\Jarvis\Responder;

abstract class AbstractResponderTest extends PHPUnit_Framework_TestCase
{
    protected static $name;
    protected $responder;

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
}
