<?php
namespace jt2k\Jarvis;

class WeatherCompareResponder extends Responder
{
    public static $pattern = '^compare (.+) and (.+) weather';
    protected $weatherA;
    protected $weatherB;
    protected $locationA;
    protected $locationB;

    protected function formatTime($format, $ts)
    {
        try {
            $dt = new \DateTime("@{$ts}");
            $dt->setTimezone(new \DateTimeZone($this->weatherA->timezone));
            $string = $dt->format($format);
        } catch (\Exception $e) {
            $string = date($format, $ts);
        }

        return $string;
    }

    protected function generateCurrentDiff()
    {
        $tempA = round($this->weatherA->currently->temperature);
        $tempB = round($this->weatherB->currently->temperature);

        $diff = abs($tempA - $tempB);
        if ($diff == 1) {
            $degrees = 'degree';
        } else {
            $degrees = 'degrees';
        }
        $text = "It's currently {$tempA}°F in {$this->locationA}.";
        if ($tempA < $tempB) {
            $text .= " That's {$diff} {$degrees} colder than {$this->locationB}. :snowflake:";
        } elseif ($tempA > $tempB) {
            $text .= " That's {$diff} {$degrees} warmer than {$this->locationB}. :sunny:";
        } else {
            $text .= " That's exactly the same as in {$this->locationB}.";
        }

        return $text;
    }


    protected function generateChart()
    {
        $hourlyA = array();
        $hourlyB = array();

        $xTicks = array();
        for ($i = 1; $i <= 25; $i+=8) {
            // Use location A for x-axis labels
            $xTicks[] = $this->formatTime('ga', $this->weatherA->hourly->data[$i]->time);
        }
        for ($i = 1; $i <= 25; $i++) {
            $hourlyA[] = round($this->weatherA->hourly->data[$i]->temperature);
            $hourlyB[] = round($this->weatherB->hourly->data[$i]->temperature);
        }
        $dataA = implode(',', $hourlyA);
        $dataB = implode(',', $hourlyB);
        return "https://chart.googleapis.com/chart?cht=lc&chs=450x200&chd=t:{$dataA}|{$dataB}&chxt=x,y&chds=a&chco=990000,0000CC&chdl=" . urlencode($this->locationA) . '|' . urlencode($this->locationB) . "&chxl=0:|" . implode('|', $xTicks);
    }

    protected function getCoords($location)
    {
        if (preg_match('/^(-?[0-9\.]+)[ ,]+(-?[0-9\.]+)$/', $location, $m)) {
            $coords = $location;
        } else {
            $coords = $this->callResponder('Geocode', "geocode {$location}");
            if ($coords == 'Not found') {
                return false;
            }
        }
        if ($coords) {
            $coords = str_replace(' ', '', $coords);
        }

        return $coords;
    }

    public function respond()
    {
        if (!$this->requireConfig(array('forecast.io_key'))) {
            return 'forecast.io_key and location are required.';
        }

        $apikey = $this->config['forecast.io_key'];

        $this->locationA = $this->matches[1];
        $this->locationB = $this->matches[2];

        $coordsA = $this->getCoords($this->locationA);
        if (!$coordsA) {
            return "Could not geocode {$this->locationA}";
        }
        $coordsB = $this->getCoords($this->locationB);
        if (!$coordsB) {
            return "Could not geocode {$this->locationB}";
        }

        $url = "https://api.forecast.io/forecast/{$apikey}/";

        $this->weatherA = $this->request($url . $coordsA, 600, 'weather'); // cache for 10 minutes
        $this->weatherB = $this->request($url . $coordsB, 600, 'weather'); // cache for 10 minutes

        if (!is_object($this->weatherA) || !is_object($this->weatherA->currently) || !is_object($this->weatherB) || !is_object($this->weatherB->currently)) {
            return 'Sorry, I could not retrieve the weather from forecast.io.';
        }

        $text = $this->generateChart();
        $text .= "\n\n";
        $text .= $this->generateCurrentDiff();
        return $text;
    }
}
