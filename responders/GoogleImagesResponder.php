<?php
namespace jt2k\Jarvis;

class GoogleImagesResponder extends Responder
{
    public static $pattern = '^(image|animate) (.+)$';

    public function respond()
    {
        $q = urlencode(trim($this->matches[2]));
        $url = "https://ajax.googleapis.com/ajax/services/search/images?v=1.0&q={$q}&safe=active&rsz=4";
        if (strtolower($this->matches[1]) == 'animate') {
            $url .= "&imgtype=animated";
        }
        $obj = $this->request($url);
        if (!empty($obj->responseData->results)) {
            $results = $obj->responseData->results;
            shuffle($results);
            return $results[0]->unescapedUrl;
        }
    }
}
