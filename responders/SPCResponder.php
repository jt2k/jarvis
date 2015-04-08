<?php
namespace jt2k\Jarvis;

class SPCResponder extends Responder
{
    public static $pattern = '^(tornado|hail|wind|fire)';
    protected $endpoint = 'http://www.spc.noaa.gov/products';

    public function respond()
    {
        $type = $this->matches[1];
        switch (strtolower($type)) {
            case 'tornado':
                $url = "{$this->endpoint}/outlook/day1probotlk_1300_torn.gif";
                break;

            case 'hail':
                $url = "{$this->endpoint}/outlook/day1probotlk_1300_hail.gif";
                break;

            case 'wind':
                $url = "{$this->endpoint}/outlook/day1probotlk_1300_wind.gif";
                break;

            case 'fire':
                $url = "{$this->endpoint}/fire_wx/day1otlk_fire.gif";
                break;

            default:
                return;
        }

        return $url . '?' . time();
    }
}
