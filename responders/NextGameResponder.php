<?php
namespace jt2k\Jarvis;

class NextGameResponder extends Responder
{
    public static $pattern = '^next game (.*)$';

    public function respond()
    {
        if (!$this->requireConfig(array('seatgeek_key'))) {
            return 'SeatGeek API key required';
        }
        $clientId = $this->config['seatgeek_key'];

        $search = $this->matches[1];
        $response = $this->request('https://api.seatgeek.com/2/performers?client_id=' . urlencode($clientId) . '&taxonomies.name=sports&q=' . urlencode($search), 600);
        if (empty($response->performers) || empty($response->performers[0]->slug)) {
            return 'Team not found';
        }

        $team = $response->performers[0];
        $response = $this->request('https://api.seatgeek.com/2/events?client_id=' . urlencode($clientId) . '&per_page=1&performers.slug=' . urlencode($team->slug), 600);
        if (empty($response->events) || empty($response->events[0]->short_title)) {
            return 'No upcoming events found';
        }

        $event = $response->events[0];

        $text = "Next {$team->name} game:\n";
        $text .= $event->short_title . "\n";

        // Add date and time
        if (empty($event->datetime_utc) || (!empty($event->date_tbd) && $event->date_tbd)) {
            $text .= 'TBD';
        } else {
            $date = new \DateTime($event->datetime_utc, new \DateTimeZone('UTC'));
            $date->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
            if (!empty($event->time_tbd) && $event->time_tbd) {
                $text .= $date->format('l, F j, Y') . ' TBD';
            } else {
                $text .= $date->format('l, F j, Y \a\t g:ia (T)');
            }
        }

        // Add location
        if (!empty($event->venue) && !empty($event->venue->name)) {
            if (!empty($event->venue->display_location)) {
                $text .= "\n{$event->venue->display_location} ({$event->venue->name})";
            } else {
                $text .= "\n{$event->venue->name}";
            }
        }

        return $text;
    }
}
