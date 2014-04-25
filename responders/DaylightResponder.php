<?php
namespace jt2k\Jarvis;

class DaylightResponder extends Responder
{
    public static $pattern = '^(daylight|sunset|sunrise)$';

    protected static function plural($number, $string, $plural = null) {
        if ($number == 1) {
            return "1 {$string}";
        } else {
            if (is_null($plural)) {
                return "{$number} {$string}s";
            } else {
                return "{$number} {$plural}";
            }
        }
    }

    public function respond()
    {
        if (!$this->requireConfig(array('location'))) {
            return 'location is required';
        }
        list($lat, $lon) = $this->config['location'];
        if (isset($this->config['timzone'])) {
            $timezone = $this->config['timezone'];
        } else {
            $timezone = date_default_timezone_get();
        }
        try {
            $tz = new \DateTimeZone($timezone);
            $offset = $tz->getOffset(new \DateTime());
            $offset = $offset / 3600;
        } catch (Exception $e) {
            return "Could not determine timezone";
        }

        if ($this->matches[1] == 'sunrise') {
            $mode = 'sunrise';
            if (time() > strtotime('16:00')) {
                $day = strtotime('+1 day');
            } else {
                $day = time();
            }
            $ts = date_sunrise($day, SUNFUNCS_RET_TIMESTAMP, $lat, $lon, ini_get("date.sunrise_zenith"), $offset);
        } else {
            $mode = 'sunset';
            $ts = date_sunset(time(), SUNFUNCS_RET_TIMESTAMP, $lat, $lon, ini_get("date.sunset_zenith"), $offset);
        }
        
        $time = date('g:ia', $ts);
        $seconds = $ts - time();
        $minutes = $hours = 0;
        $past = false;

        if ($seconds < 0) {
            $past = true;
            $seconds = abs($seconds);
        }
        if ($seconds > 60) {
            $minutes = floor($seconds / 60);
            $seconds = $seconds % 60;
        }
        if ($minutes > 60) {
            $hours = floor($minutes / 60);
            $minutes = $minutes % 60;
        }

        $time_string = '';
        if ($hours > 0) {
            $time_string = self::plural($hours, 'hour') . ' and ' . self::plural($minutes, 'minute');
        } elseif ($minutes > 0) {
            $time_string = self::plural($minutes, 'minute') . ' and ' . self::plural($seconds, 'second');
        } else {
            $time_string = self::plural($seconds, 'second');
        }


        if ($past) {
            $verb = ($mode == 'sunrise'?'rose':'set');
            return "The sun {$verb} {$time_string} ago ({$time})";
        } else {
            $verb = ($mode == 'sunrise'?'rise':'set');
            return "The sun will {$verb} in {$time_string} ({$time})";
        }
        
    }
}
