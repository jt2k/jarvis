<?php
namespace jt2k\Jarvis;

class GeocodeResponder extends Responder
{
    public static $pattern = '^geocode (.+)$';

    public function respond()
    {
        $query = trim($this->matches[1]);
        if (preg_match('/^(-?[0-9\.]+)[ ,]+(-?[0-9\.]+)$/', $query, $m)) {
            $param = 'latlng';
        } else {
            $param = 'address';
        }
        $query = urlencode($query);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?{$param}={$query}&sensor=false";
        $obj = $this->request($url, 3600*24, 'geocode');
        if (!is_object($obj) || $obj->status !== 'OK') {
            return 'Not found';
        }
        if (!is_array($obj->results) || !isset($obj->results[0])) {
            return 'Not found';
        }

        if ($param == 'address') {
            return "{$obj->results[0]->geometry->location->lat}, {$obj->results[0]->geometry->location->lng}";
        } else {
            $full_address = $obj->results[0]->formatted_address;
            $full_address = preg_replace('/^[^,]+, /', '', $full_address);
            $full_address = preg_replace('/, USA$/', '', $full_address);
            return $full_address;
        }
    }
}
