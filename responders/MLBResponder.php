<?php
namespace jt2k\Jarvis;

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
            $text .= sprintf("%s %d, %s %d (%s)\n",
                $game->teams->away->team->name,
                $game->teams->away->score,
                $game->teams->home->team->name,
                $game->teams->home->score,
                $game->status->detailedState
            );
        }

        return $text;
    }
}
