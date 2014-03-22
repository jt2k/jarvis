<?php
namespace jt2k\Jarvis;

class GeocodeResponder extends Responder
{
    public static $pattern = '^geocode (.+)$';

    public function respond()
    {
        $address = urlencode(trim($this->matches[1]));
        $url = "http://maps.googleapis.com/maps/api/geocode/json?address={$address}&sensor=false";
        $obj = $this->request($url);
        if (!is_object($obj) || $obj->status !== 'OK') {
            return 'Not found';
        }
        if (!is_array($obj->results) || !isset($obj->results[0]) || !isset($obj->results[0]->geometry->location)) {
            return 'Not found';
        }

        return "{$obj->results[0]->geometry->location->lat}, {$obj->results[0]->geometry->location->lng}";
    }
}
