<?php
namespace jt2k\Jarvis;

class GoogleMapsResponder extends Responder
{
    public static $pattern = '^map (streetview|roadmap|satellite|hybrid|terrain)?(.+)$';

    public function respond()
    {
        $type = $this->matches[1];
        $location = urlencode(trim($this->matches[2]));
        switch ($type) {
            case 'streetview':
                return "http://maps.googleapis.com/maps/api/streetview?size=800x400&location={$location}&fov=120&sensor=false";
                break;
            case 'roadmap':
            case 'satellite':
            case 'hybrid':
            case 'terrain':
                return "http://maps.googleapis.com/maps/api/staticmap?size=500x500&center={$location}&maptype={$type}&sensor=false";
                break;
            default:
                return "http://maps.googleapis.com/maps/api/staticmap?size=500x500&center={$location}&sensor=false";
                break;
        }
    }
}
