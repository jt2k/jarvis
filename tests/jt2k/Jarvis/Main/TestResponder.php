<?php
namespace jt2k\Jarvis;

class TestResponder extends Responder
{
    public static $pattern = '^confirm$';

    public function respond()
    {
        return 'Affirmative';
    }
}