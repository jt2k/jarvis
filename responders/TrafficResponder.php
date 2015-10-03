<?php
namespace jt2k\Jarvis;

class TrafficResponder extends Responder
{
    public static $pattern = '^traffic$';

    public function respond()
    {
        if (!$this->requireConfig(array('bingmaps_key', 'location'))) {
            return 'bingmaps_key and location are required.';
        }

        $url = 'http://dev.virtualearth.net/REST/v1/Imagery/Map/Road/'
            . "{$this->config['location'][0]},{$this->config['location'][1]}"
            . '/12?mapSize=600,400&mapLayer=TrafficFlow&format=png'
            . "&key={$this->config['bingmaps_key']}";

        $image = $this->requestProxy($url, 300, 'traffic', 'png');
        return "Traffic conditions at " . date('g:ia') . ":\n{$image}";
    }
}
