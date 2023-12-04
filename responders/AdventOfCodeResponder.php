<?php
namespace jt2k\Jarvis;

class AdventOfCodeResponder extends Responder
{
    public static $pattern = '^(?:aoc|advent of code)(?:\s+leaderboard)?(\s+\d{4})?$';

    public function respond()
    {
        if ($this->requireConfig(['adventofcode'])) {
            $config = $this->config['adventofcode'];
        } else {
            return 'adventofcode configuration is missing';
        }

        if (!isset($config['id']) || !isset($config['token'])) {
            return 'adventofcode configuration is missing id or token';
        }

        if (isset($this->matches[1]) && trim($this->matches[1])) {
            $year = trim($this->matches[1]);
        } else {
            $year = date('Y');
        }
        $url = "https://adventofcode.com/{$year}/leaderboard/private/view/{$config['id']}.json";


        $result = $this->request($url, 900, 'aoc', 'json', ["Cookie: session={$config['token']}"]);
        if (!isset($result->members)) {
            return 'Error retrieving leaderboard';
        }
        $scores = [];
        foreach ($result->members as $member) {
            $scores[$member->name] = $member->local_score;
        }
        arsort($scores);
        $result = "$year leaderboard\n";
        foreach ($scores as $name => $score) {
            $result .= "{$name}: {$score}\n";
        }
        return $result;
    }
}
