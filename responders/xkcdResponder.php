<?php
namespace jt2k\Jarvis;

class xkcdResponder extends Responder
{
    public static $pattern = '^xkcd( random)?( [\d]+)?$';

    public function respond()
    {
        if (isset($this->matches[1]) && $this->matches[1]) {
            return $this->random();
        } elseif (isset($this->matches[2])) {
            return $this->single(trim($this->matches[2]));
        } else {
            return $this->current();
        }
    }

    protected function getJSON($num = false)
    {
        if ($num) {
            return $this->request("http://xkcd.com/{$num}/info.0.json", 3600 * 24, 'xkcd');
        } else {
            return $this->request('http://xkcd.com/info.0.json', 600, 'xkcd');
        }
    }

    protected static function obj2text($xkcd)
    {
        if (is_object($xkcd) && isset($xkcd->img)) {
            $str = $xkcd->img;
            $str .= "\n\"{$xkcd->title}\" - {$xkcd->month}/{$xkcd->day}/{$xkcd->year}";
            if (isset($xkcd->alt)) {
                $alt = utf8_decode($xkcd->alt);
                $str .= "\n[{$alt}]";
            }

            return $str;
        }
    }

    protected function current()
    {
        return self::obj2text($this->getJSON());
    }

    protected function single($num)
    {
        $num = intval($num);
        if ($num > 0) {
            return self::obj2text($this->getJSON($num));
        }
    }

    protected function random()
    {
        $xkcd = $this->getJSON();
        if (is_object($xkcd) && isset($xkcd->num)) {
            $num = rand(1, $xkcd->num);

            return self::obj2text($this->getJSON($num));
        }
    }
}
