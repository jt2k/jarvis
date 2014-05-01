<?php
namespace jt2k\Jarvis;

class YouTubeResponder extends Responder
{
    public static $pattern = '^youtube (.+)$';

    public function respond()
    {
        $q = urlencode(trim($this->matches[1]));
        $url = "https://gdata.youtube.com/feeds/api/videos?q={$q}&max-results=4&v=2&alt=json";
        $obj = $this->request($url);
        if (!empty($obj->feed->entry)) {
            $results = $obj->feed->entry;
            shuffle($results);
            $id = $results[0]->{'media$group'}->{'yt$videoid'}->{'$t'};
            $title = $results[0]->title->{'$t'};
            return "{$title} http://www.youtube.com/watch?v={$id}";
        }
    }
}
