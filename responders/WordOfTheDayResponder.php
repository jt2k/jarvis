<?php
namespace jt2k\Jarvis;

class WordOfTheDayResponder extends Responder
{
    public static $pattern = '^(word of the day|wotd)$';

    public function respond()
    {
        $url = "http://www.merriam-webster.com/word-of-the-day/";
        $html = $this->requestRaw($url, 3600);
        if (preg_match('/<h1>([^>]+)<\/h1>/', $html, $m)) {
            return $this->callResponder('Dictionary', "define {$m[1]} all");
        } else {
            return 'Cannot find word of the day';
        }
    }
}
