<?php
namespace jt2k\Jarvis;

class RedditNewsResponder extends Responder
{
    public static $pattern = '^news.*$';

    public function respond()
    {
        $reddit = 'worldnews';
        $time = 'day';
        if (preg_match('/(hour|day|week|month|year|all)/i', $this->matches[0], $m)) {
            $time = strtolower($m[1]);
        }
        if (preg_match('/(world|us|tech|science)/i', $this->matches[0], $m)) {
            switch (strtolower($m[1])) {
                case 'world':
                    $reddit = 'worldnews';
                    break;
                case 'us':
                    $reddit = 'news';
                    break;
                case 'tech':
                    $reddit = 'technology';
                    break;
                case 'science':
                    $reddit = 'science';
                    break;
            }
        }
        $url = "http://www.reddit.com/r/{$reddit}/top.json?t={$time}&limit=10";
        $result = $this->request($url);
        $string = '';
        if (isset($result->data->children)) {
            foreach ($result->data->children as $story) {
                if ($story->data->over_18 || $story->data->is_self) {
                    continue;
                }
                if ($time == 'all') {
                    $label = 'all time';
                } else {
                    $label = 'last ' . $time;
                }
                $string = "Top {$reddit} story on reddit ({$label})\n{$story->data->title}\n{$story->data->url}";
                break;
            }
        }
        $string = trim($string);
        if (!empty($string)) {
            return $string;
        }
    }
}
