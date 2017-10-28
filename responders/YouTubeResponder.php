<?php
namespace jt2k\Jarvis;

class YouTubeResponder extends Responder
{
    public static $pattern = '^youtube (.+)$';

    public function respond()
    {
        if (!$this->requireConfig(['youtube_key'])) {
            return 'youtube_key required';
        }
        $q = urlencode(trim($this->matches[1]));
        $key = urlencode($this->config['youtube_key']);
        $url = "https://www.googleapis.com/youtube/v3/search?q={$q}&maxResults=4&part=snippet&type=video&key={$key}";
        $obj = $this->request($url);
        if (!empty($obj->items)) {
            $results = $obj->items;
            shuffle($results);
            $id = $results[0]->id->videoId;
            $title = $results[0]->snippet->title;
            return "{$title} http://www.youtube.com/watch?v={$id}";
        }
    }
}
