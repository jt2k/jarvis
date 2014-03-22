<?php
namespace jt2k\Jarvis;

class WikipediaResponder extends Responder
{
    public static $pattern = '^wiki (.+)$';

    public function respond()
    {
        // There's probably a better way to do this.
        // Currently, doing search to get title, then looking up title to get URL

        $search = urlencode($this->matches[1]);
        $url = "http://en.wikipedia.org/w/api.php?action=query&list=search&srsearch={$search}&srlimit=1&format=json";
        $results = $this->request($url);
        $title = $results->query->search[0]->title;
        if (!$title) {
            return false;
        }

        $title = urlencode($title);
        $url = "http://en.wikipedia.org/w/api.php?action=query&titles={$title}&prop=info&inprop=url&format=json";
        $results = $this->request($url);
        if (is_object($results) && is_object($results->query) && is_object($results->query->pages)) {
            $arr  = (array) $results->query->pages;
            $page = current($arr);

            return $page->fullurl;
        }

        return false;
    }
}
