<?php
namespace jt2k\Jarvis;

class RadarResponder extends Responder
{
    public static $pattern = '^radar$';

    public function respond()
    {
        if (!$this->requireConfig(array('wunderground_key', 'location'))) {
            return 'wunderground_key and location are required.';
        }

        $url = "https://api.wunderground.com/api/{$this->config['wunderground_key']}/animatedradar/image.gif?";
        $params = array(
            'centerlat' => $this->config['location'][0],
            'centerlon' => $this->config['location'][1],
            'radius' => 70,
            'newmaps' => 1,
            'rainsnow' => 1,
            'timelabel' => 1,
            'timelabel.y' => 15,
            'width' => 400,
            'height' => 300,
            'num' => 10,
            'delay' => 30
        );
        $url .= http_build_query($params);

        return $this->requestProxy($url, 300, 'radar', 'gif');
    }
}
