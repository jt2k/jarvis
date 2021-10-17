<?php

use jt2k\Jarvis\Responder;

class ResponderTest extends AbstractResponderTest
{
    protected static $name = 'TestResponder';

    protected function setUp(): void
    {
        require_once 'TestResponder.php';
        $this->responder = $this->initResponder('confirm');
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
