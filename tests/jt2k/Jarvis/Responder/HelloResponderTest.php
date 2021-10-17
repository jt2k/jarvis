<?php

class HelloResponderTest extends AbstractResponderTest
{
    protected static $name = 'HelloResponder';
    protected static $command = 'hello';

    public function testRespond()
    {
        $responder = $this->initResponder('hello');
        $this->assertMatchesRegularExpression('/(Hi|Hello|Hey)/', $responder->respond());
    }
}
