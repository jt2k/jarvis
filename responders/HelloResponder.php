<?php
namespace jt2k\Jarvis;

class HelloResponder extends Responder
{
    public static $pattern = '^(?:hello|hi)$';

    public function respond()
    {
        $greetings = array('Hello', 'Hello', 'Hello', 'Hi', 'Hi', 'Hey');
        shuffle($greetings);
        $punctuation = rand(0,2)>0?'!':'.';
        if (isset($this->communication['user_name']) && $this->communication['user_name'] && rand(0,3)>0) {
            return "{$greetings[0]}, {$this->communication['user_name']}{$punctuation}";
        } else {
            return "{$greetings[0]}{$punctuation}";
        }
    }
}
