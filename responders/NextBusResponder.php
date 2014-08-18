<?php
namespace jt2k\Jarvis;

class NextBusResponder extends Responder
{
    public static $pattern = '^nextbus (.+)$';

    public function respond()
    {
        if (!$this->requireConfig(array('nextbus_key'))) {
            return 'Missing nextbus_key';
        }
        $key = $this->config['nextbus_key'];

        if (preg_match('/(\d+)\s+(\d+)\s+(\d+)/', trim($this->matches[1]), $m)) {
            $response = $this->request("http://nextbus.jt2k.com/api/route/{$m[1]}/dir/{$m[2]}/stop/{$m[3]}?key=" . urlencode($key));
            if (is_object($response) && isset($response->times)) {
                if (!empty($response->next)) {
                    $time = strtotime($response->next->arrival_time);
                    $diff = $time - time();
                    $minutes = floor($diff / 60);
                    $str = "{$minutes} minute";
                    if ($minutes != 1) {
                        $str .= 's';
                    }
                    return "Next bus arrives in {$str} ({$response->next->arrival_time_str} - {$response->next->trip_headsign})";
                } else {
                    return "Last bus has left for the day";
                }
            } else {
                return 'Could not retrieve data from Next Bus Nashville';
            }
        }

    }
}
