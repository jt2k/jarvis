<?php
namespace jt2k\Jarvis;

/**
 * Sign up for an API key here: https://developer.forecast.io/
 * Then, add the key and coordinates in config.php (forecast.io_key and forecast.io_coords)
 */
class WeatherResponder extends Responder
{
    public static $pattern = '(weather|temperature|rain)( hourly( \d+)?| forecast( \d+)?)?';
    public static $help = array(
        'weather - returns current temperature and conditions',
        'temperature - returns current temperature and conditions',
        'rain - returns the probabilty of rain for the next 24 hours',
        'weather foreacst - returns weather foreacst for the next hour, today, and tomorrow',
        'weather foreacst [n] - returns weather foreacst for the next n days',
        'weather hourly - returns hourly temperature forecast',
        'weather hourly [n] - returns hourly temperature forecast for the next n hours',
    );
    public static $help_words = array('temperature', 'rain');

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
        if (isset($this->matches[4])) {
            $days = (int) trim($this->matches[4]);
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
        if (isset($this->matches[3])) {
            $hours = (int) trim($this->matches[3]);
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

    protected function generateRain()
    {
        $str = 'Currently: ' . round(100 * $this->data->currently->precipProbability) . "% chance of rain\n";
        $count = 0;
        for ($i = 1; $i <= 24; $i++) {
            if (!isset($this->data->hourly->data[$i])) {
                break;
            }
            $hour = $this->data->hourly->data[$i];
            if ($hour->precipProbability > 0) {
                $str .= $this->formatTime('ga', $hour->time);
                $precip = round(100 * $hour->precipProbability);
                $str .= ": {$precip}%, ";
                $count++;
                if ($count > 5) {
                    break;
                }
            }
        }
        if ($count == 0) {
            $str .= "No chance of rain in the next 24 hours";
        }
        $str = trim($str, ', ');
        return $str;
    }

    protected function generateCurrent()
    {
        $temp = round($this->data->currently->temperature);

        return "{$temp}°F, {$this->data->currently->summary}";
    }

    public function respond()
    {
        if (!$this->requireConfig(array('forecast.io_key', 'location'))) {
            return 'forecast.io_key and location are required.';
        }

        $apikey = $this->config['forecast.io_key'];
        $location = join(',', $this->config['location']);

        $url = "https://api.forecast.io/forecast/{$apikey}/{$location}";
        $this->data = $this->request($url, 600, 'weather'); // cache for 10 minutes

        if (!is_object($this->data) || !is_object($this->data->currently)) {
            return 'Sorry, I could not retrieve the weather from forecast.io.';
        }

        if (strtolower($this->matches[1]) == 'rain') {
            return $this->generateRain();
        } elseif (!empty($this->matches[2])) {
            if (preg_match('/hourly/i', $this->matches[2])) {
                return $this->generateHourly();
            } else {
                return $this->generateForecast();
            }
        } else {
            return $this->generateCurrent();
        }
    }
}
