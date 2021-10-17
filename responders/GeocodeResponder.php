<?php
namespace jt2k\Jarvis;

class GeocodeResponder extends Responder
{
    public static $pattern = '^geocode (.+)$';

    public function respond()
    {
        $query = trim($this->matches[1]);
        $reverse = false;
        if (preg_match('/^(-?[0-9\.]+)[ ,]+(-?[0-9\.]+)$/', $query, $m)) {
            $reverse = true;
            $lat = $m[1];
            $lon = $m[2];
            $url = "https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lon}";
        } else {
            $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($query);
        }
        $url .= '&format=json';
        $obj = $this->request($url, 3600*24*7, 'geocode');
        if ($reverse) {
            if (!is_object($obj) || !isset($obj->address)) {
                return 'Not found';
            }
            $address = '';
            if (!empty($obj->address->city)) {
                $address = "{$obj->address->city}, ";
            } elseif (!empty($obj->address->town)) {
                $address = "{$obj->address->town}, ";
            }
            $address .= "{$obj->address->state} {$obj->address->postcode}";
            if ($obj->address->country && !in_array($obj->address->country, ['USA', 'United States'])) {
                $address .= ", {$obj->address->country}";
            }
            return $address;
        } else {
            if (!is_array($obj) || count($obj) === 0 || !isset($obj[0]->lat) || !isset($obj[0]->lon)) {
                return 'Not found';
            }
            return "{$obj[0]->lat}, {$obj[0]->lon}";
        }
    }
}
