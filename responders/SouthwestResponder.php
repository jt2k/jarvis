<?php
namespace jt2k\Jarvis;

class SouthwestResponder extends Responder
{
    public static $pattern = '^(?:southwest|swa) ([a-z]{3}) ([a-z]{3}) (\d+)?';

    public function respond()
    {
        $from = strtoupper($this->matches[1]);
        $to = strtoupper($this->matches[2]);
        $flight = $this->matches[3];
        $url = "http://www.southwest.com/flight/flight-status-details.html?originAirport={$from}&destinationAirport={$to}&flightNumber={$flight}";

        $html = $this->requestRaw($url);
        $statuses = array();
        $times = array();

        if (preg_match_all('/"swa_text_flightStatus_status">(.+?)<\/span/', $html, $m)) {
            $statuses = $m[1];
        }
        if (preg_match_all('/"swa_text_time">(.+?)<\/span/is', $html, $m)) {
            $times = $m[1];
            foreach ($times as &$time) {
                $time = ltrim(trim(strip_tags($time)), '0');
            }
        }
        if (count($statuses) == 2 && count($times) == 4) {
            $result = '';
            if (preg_match('/"swa_route_subtitle">(.+?)<\/h2/is', $html, $m)) {
                $title = strip_tags($m[1]);
                $title = str_replace('&nbsp;', ' ', $title);
                $title = html_entity_decode($title);
                $title = preg_replace('/\s+/', ' ', $title);
                $title = trim($title);
                $result .= "$title\n";
            }
            $result .= "Departure: {$statuses[0]} - {$times[2]} (scheduled {$times[0]})\n";
            $result .= "Arrival: {$statuses[1]} - {$times[3]} (scheduled {$times[1]})";
            return $result;
        } else {
            return "Error retrieving flight status";
        }
    }
}
