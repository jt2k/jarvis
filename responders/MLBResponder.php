<?php

namespace jt2k\Jarvis;

use DateTime;
use DateTimeZone;
use Exception;

class MLBResponder extends Responder
{
    public static $pattern = '^(mlb|baseball)(\s+scores)?$';

    public function respond()
    {
        $response = $this->request('https://statsapi.mlb.com/api/v1/schedule?sportId=1', 120, 'mlb');
        if (!$response || empty($response->dates) || empty($response->dates[0]->games)) {
            return 'No games found';
        }
        $text = '';
        foreach ($response->dates[0]->games as $game) {
            switch ($game->status->detailedState) {
                case 'Scheduled':
                    try {
                        $dt = new DateTime($game->gameDate);
                        $dt->setTimezone(new DateTimeZone(date_default_timezone_get()));
                        $date = $dt->format('g:ia T');
                    } catch (Exception $e) {
                        $date = 'Scheduled';
                    }
                    $text .= sprintf("%s at %s (%s)\n",
                        $game->teams->away->team->name,
                        $game->teams->home->team->name,
                        $date
                    );
                    break;
                default:
                    $text .= sprintf("%s %d, %s %d (%s)\n",
                        $game->teams->away->team->name,
                        $game->teams->away->score,
                        $game->teams->home->team->name,
                        $game->teams->home->score,
                        $game->status->detailedState
                    );
            }
        }

        return $text;
    }
}
