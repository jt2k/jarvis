<?php
namespace jt2k\Jarvis;

abstract class RandomResponder extends Responder
{
    protected static $options = array();

    public function respond()
    {
        if (count(static::$options) > 0) {
            $index = rand(0, count(static::$options) - 1);

            return static::$options[$index];
        }
    }
}
