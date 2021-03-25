<?php
namespace jt2k\Jarvis;

class SPCResponder extends Responder
{
    public static $pattern = '^(tornado|hail|wind|fire|hurricane)$';
    protected $endpoint = 'http://www.spc.noaa.gov/products';
    protected $outlookTimes = [
        '0100',
        '1200',
        '1300',
        '1630',
        '2000'
    ];
    protected $typeMap = [
        'tornado' => 'torn',
        'hail' => 'hail',
        'wind' => 'wind'
    ];

    protected function getSpcOutlookFilename($type)
    {
        $now = new \DateTime();
        $now->setTimezone(new \DateTimeZone('UTC'));
        $utcTime = $now->format('Hi');
        $latestTime = end($this->outlookTimes);
        reset($this->outlookTimes);
        foreach ($this->outlookTimes as $time) {
            if ($utcTime >= $time) {
                $latestTime = $time;
            }
        }
        $slug = $this->typeMap[$type];

        return "day1probotlk_{$latestTime}_{$slug}.gif";
    }

    public function respond()
    {
        $type = $this->matches[1];
        switch (strtolower($type)) {
            case 'tornado':
            case 'hail':
            case 'wind':
                $filename = $this->getSpcOutlookFilename($type);
                $url = "{$this->endpoint}/outlook/{$filename}";
                break;

            case 'fire':
                $url = "{$this->endpoint}/fire_wx/day1otlk_fire.gif";
                break;

            // Including hurricane outlook from NHC despite not coming from the SPC
            case 'hurricane':
                $url = "http://www.nhc.noaa.gov/xgtwo/two_atl_2d0.png";
                break;

            default:
                return;
        }

        return $url . '?' . time();
    }
}
