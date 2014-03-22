<?php
namespace jt2k\Jarvis;

class RSSResponder extends Responder
{
    public static $pattern = '^rss (.+?)( \d+)?$';

    public function respond()
    {
        $url = trim($this->matches[1]);
        $index = 0;
        if (isset($this->matches[2])) {
            $index = intval(trim($this->matches[2]));
            $index = $index - 1;
            $index = min(9, $index);
            $index = max(0, $index);
        }
        $error_level = error_reporting();
        error_reporting($error_level ^ E_USER_NOTICE);

        $feed = new \SimplePie();
        if ($this->cacheEnabled()) {
            $feed->set_cache_location($this->config['cache_directory']);
            $feed->set_cache_duration(600);
        }
        $feed->set_feed_url($url);
        $feed->init();
        $feed->handle_content_type();
        if ($index > $feed->get_item_quantity() - 1) {
            $index = $feed->get_item_quantity();
        }
        $item = $feed->get_item($index);

        $result = null;
        if ($item) {
            $title = $item->get_title();
            $link = $item->get_permalink();
            $date = $item->get_date();
            $result = "{$date} - {$title} - {$link}";
        }
        error_reporting($error_level);

        return $result;
    }
}
