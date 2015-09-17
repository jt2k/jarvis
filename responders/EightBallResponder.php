<?php
namespace jt2k\Jarvis;

class EightBallResponder extends RandomResponder
{
    public static $pattern = '(?:8|eight|magic) ?ball';
    protected static $options = array(
        'It is certain',
        'It is decidedly so',
        'Without a doubt',
        'Yes definitely',
        'You may rely on it',
        'As I see it, yes',
        'Most likely',
        'Outlook good',
        'Yes',
        'Signs point to yes',
        'Reply hazy try again',
        'Ask again later',
        'Better not tell you now',
        'Cannot predict now',
        'Concentrate and ask again',
        'Don\'t count on it',
        'My reply is no',
        'My sources say no',
        'Outlook not so good',
        'Very doubtful'
    );
    
    public static function isPositive($response) {
        switch ($response) {
            case 'It is certain':
            case 'It is decidedly so':
            case 'Without a doubt':
            case 'Yes definitely':
            case 'You may rely on it':
            case 'As I see it, yes':
            case 'Most likely':
            case 'Outlook good':
            case 'Yes':
            case 'Signs point to yes':
                return true;
            default: return false;
        }
    }
}
