<?php
namespace jt2k\Jarvis;

class RSSResponder extends Responder
{
    public static $pattern = '^rss (.+?)(?: (\d+)(?:-(\d+))?)?$';
    public static $help = array(
        'rss [example.com] - returns the newest story',
        'rss [example.com] [n] - returns a specific story',
        'rss [example.com] [n]-[m] - returns a range of stories'
    );

    public function respond()
    {
        $url = trim($this->matches[1]);
        $index = 0;
        if (isset($this->matches[2])) {
            $index = intval(trim($this->matches[2]));
            $index = $index - 1;
            $index = max(0, $index);
        }
        $indexEnd = false;
        if (isset($this->matches[3])) {
            $indexEnd = intval(trim($this->matches[3]));
            $indexEnd = $indexEnd - 1;
            if ($indexEnd <= $index) {
                $indexEnd = false;
            }
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
            $index = $feed->get_item_quantity() - 1;
        }

        if ($indexEnd) {
            $itemIndicies = range($index, $indexEnd);
        } else {
            $itemIndicies = array($index);
        }

        $result = '';
        foreach ($itemIndicies as $index) {
            $item = $feed->get_item($index);
            if ($item) {
                $title = html_entity_decode($item->get_title());
                $link = $item->get_permalink();
                $date = $item->get_date();
                $i = $index + 1;
                $result .= "[{$i}] {$date} - {$title} - {$link}\n";
            }
        }
        error_reporting($error_level);

        return trim($result);
    }
}
