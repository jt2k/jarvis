<?php
namespace jt2k\Jarvis;

class WikipediaResponder extends Responder
{
    public static $pattern = '^wiki (.+)$';

    public function respond()
    {
        $search = urlencode($this->matches[1]);
        $url = "http://en.wikipedia.org/w/api.php?action=query&list=search&srsearch={$search}&srlimit=1&format=json";
        $results = $this->request($url);
        if (!isset($results->query->search) || count($results->query->search) == 0) {
            return false;
        }
        $title = $results->query->search[0]->title;
        $title = urlencode($title);
        $url = "http://en.wikipedia.org/w/api.php?action=query&titles={$title}&prop=info|extracts&inprop=url&exintro=1&format=json";
        $results = $this->request($url);
        if (is_object($results) && is_object($results->query) && is_object($results->query->pages)) {
            $arr  = (array) $results->query->pages;
            $page = current($arr);
            $content = trim(strip_tags($page->extract));
            $content = preg_replace('/\s+/m', ' ', $content);
            $content = substr($content, 0, 400);
            $content = preg_replace('/ [^ ]+$/', '', $content);
            return "{$content}...\n{$page->fullurl}";
        }

        return false;
    }
}
