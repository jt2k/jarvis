<?php
namespace jt2k\Jarvis;

class BoardGameResponder extends Responder
{
    public static $pattern = '^board ?game (.+?)$';

    public function respond()
    {
        $search = trim($this->matches[1]);
        $url = "https://api.geekdo.com/xmlapi2/search?type=boardgame&query=" . urlencode($search);
        $result = $this->request($url, 3600 , 'bgg', 'xml');
        if (!$result) {
            return 'Could not connect to BoardGameGeek API';
        }
        $items = $result->xpath("/items/item");
        if (count($items) === 0) {
            return 'No results found';
        }
        $item = $items[0];
        $id = (string)$item['id'];
        if (!$id) {
            return 'Could not find game ID';
        }
        $title = (string)$item->name['value'];
        $year = (string)$item->yearpublished['value'];
        $response = '';
        if ($title) {
            $response .= $title;
            if ($year) {
                $response .= " ($year)";
            }
            $response .= "\n";
        }
        $response .= "https://boardgamegeek.com/boardgame/{$id}";
        return $response;
    }
}
