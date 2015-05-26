<?php
class HelloResponderTest extends ResponderTest
{
    protected static $name = 'HelloResponder';
    protected static $command = 'hello';

    public function testRespond()
    {
        $response = $this->responder->respond();
        $this->assertRegExp('/(Hi|Hello|Hey)/', $response);
    }

    public function testGetName()
    {
        $this->assertEquals('Hello', $this->responder->getName());
    }

    public function testHasStorage()
    {
        $this->assertFalse($this->responder->hasStorage());
    }
}
