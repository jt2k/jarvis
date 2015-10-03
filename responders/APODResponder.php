<?php
namespace jt2k\Jarvis;

class APODResponder extends Responder
{
    public static $pattern = '^(apod|astronomy photo)$';

    public function respond()
    {
        if (!empty($this->config['nasa_key'])) {
            $key = $this->config['nasa_key'];
        } else {
            $key = 'DEMO_KEY';
        }
        $url = "https://api.nasa.gov/planetary/apod?api_key={$key}";
        $obj = $this->request($url);
        if (!is_object($obj) || empty($obj->url)) {
            return 'Could not retrieve today\'s APOD';
        }
        $text = '';
        if (!empty($obj->title)) {
            $text .= $obj->title . "\n";
        }
        $text .= $obj->url . "\n";
        if (!empty($obj->explanation)) {
            $text .= $obj->explanation;
        }
        return trim($text);
    }
}
