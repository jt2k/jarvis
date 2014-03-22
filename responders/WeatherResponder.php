<?php
namespace jt2k\Jarvis;

/**
 * Sign up for an API key here: https://developer.forecast.io/
 * Then, add the key and coordinates in config.php (forecast.io_key and forecast.io_coords)
 */
class WeatherResponder extends Responder
{
    public static $pattern = '(?:weather|temperature)( hourly( \d+)?| forecast( \d+)?)?';
    protected $data;

    protected function formatTime($format, $ts)
    {
        try {
            $dt = new \DateTime("@{$ts}");
            $dt->setTimezone(new \DateTimeZone($this->data->timezone));
            $string = $dt->format($format);
        } catch (\Exception $e) {
            $string = date($format, $ts);
        }

        return $string;
    }

    protected function generateForecast()
    {
        $temp = round($this->data->currently->temperature);
        $string = "Currently: {$temp}°F, {$this->data->currently->summary}\n";
        $string .= "Next hour: {$this->data->minutely->summary}\n";
        if (isset($this->matches[3])) {
            $days = (int) trim($this->matches[3]);
            $days = min(7, $days);
            $days = max(0, $days);
        } else {
            $days = 1;
        }
        for ($i = 0; $i <= $days; $i++) {
            if (!isset($this->data->daily->data[$i])) {
                break;
            }
            $day = $this->data->daily->data[$i];
            if ($i == 0) {
                $string .= 'Today';
            } elseif ($i == 1) {
                $string .= 'Tomorrow';
            } else {
                $string .= $this->formatTime('D', $day->time);
            }
            $low = round($day->temperatureMin);
            $high = round($day->temperatureMax);
            $string .= ": {$day->summary} High of {$high}°F, Low of {$low}°F\n";
        }

        return trim($string);
    }

    protected function generateHourly()
    {
        $temp = round($this->data->currently->temperature);
        $string = "Currently: {$temp}°F, {$this->data->currently->summary}\n";
        if (isset($this->matches[2])) {
            $hours = (int) trim($this->matches[2]);
            $hours = min(24, $hours);
            $hours = max(1, $hours);
        } else {
            $hours = 8;
        }
        $count = 0;
        for ($i = 1; $i <= $hours; $i++) {
            if (!isset($this->data->hourly->data[$i])) {
                break;
            }
            $hour = $this->data->hourly->data[$i];
            $string .= $this->formatTime('ga', $hour->time);
            $temp = round($hour->temperature);
            $string .= ": {$temp}°F, ";
        }
        $string = trim($string, ', ');

        return $string;
    }

    protected function generateCurrent()
    {
        $temp = round($this->data->currently->temperature);

        return "{$temp}°F, {$this->data->currently->summary}";
    }

    public function respond()
    {
        if (!$this->requireConfig(array('forecast.io_key', 'forecast.io_coords'))) {
            return 'forecast.io_key and forecast.io_coords are required.';
        }

        $apikey = $this->config['forecast.io_key'];
        $location = $this->config['forecast.io_coords'];

        $url = "http://api.forecast.io/forecast/{$apikey}/{$location}";
        $this->data = $this->request($url, 600, 'weather'); // cache for 10 minutes

        if (!is_object($this->data) || !is_object($this->data->currently)) {
            return 'Sorry, I could not retrieve the weather from forecast.io.';
        }

        if (isset($this->matches[1]) && preg_match('/hourly/', $this->matches[1])) {
            return $this->generateHourly();
        } elseif (isset($this->matches[1]) && $this->matches[1]) {
            return $this->generateForecast();
        } else {
            return $this->generateCurrent();
        }
    }
}
