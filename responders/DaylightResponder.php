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

        $now = time();
        $tomorrow = strtotime('+1 day', $now);
        $yesterday = strtotime('-1 day', $now);

        $sun_info = date_sun_info($now, $lat, $lon);
        $sun_info_tomorrow = date_sun_info($tomorrow, $lat, $lon);
        $sun_info_yesterday = date_sun_info($yesterday, $lat, $lon);

        if ($this->matches[1] == 'sunrise') {
            $mode = 'sunrise';
            if ($now > $sun_info['sunset']) {
                $ts = $sun_info_tomorrow['sunrise'];
            } else {
                $ts = $sun_info['sunrise'];
            }
        } else {
            $mode = 'sunset';
            if ($now < $sun_info['sunrise']) {
                $ts = $sun_info_yesterday['sunset'];
            } else {
                $ts = $sun_info['sunset'];
            }
        }
        
        $time = date('g:ia', $ts);
        $seconds = $ts - $now;
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
