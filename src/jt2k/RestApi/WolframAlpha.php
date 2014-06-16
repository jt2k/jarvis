<?php
/*
    PHP REST API
    Jason Tan
    http://code.google.com/p/php-rest-api/
*/
namespace jt2k\RestApi;

class WolframAlpha extends RestApi
{
    protected $appid;
    protected $endpoint = 'http://api.wolframalpha.com/v2/query';
    protected $cache_life = 0;
    protected $format = 'xml';

    public function __construct($appid)
    {
        $this->appid = $appid;
    }

    public function query($input)
    {
        $params = array(
            'appid' => $this->appid,
            'input' => $input,
            'format' => 'plaintext,image'
        );

        return $this->request($this->endpoint, array('get' => $params));
    }

    public function getAnswer($input)
    {
        $xml = $this->query($input);
        if ((string) $xml['success'] === 'true') {
            $results = $xml->xpath('/queryresult/pod[@title="Result"]/subpod/plaintext');
            if (is_array($results) && count($results) > 0 && trim((string) $results[0])) {
                return (string) $results[0];
            }

            $results = $xml->xpath('/queryresult/pod[not(starts-with(@title, "Input"))]/subpod/plaintext');
            if (is_array($results) && count($results) > 0 && trim((string) $results[0])) {
                return (string) $results[0];
            }

            $results = $xml->xpath('/queryresult/pod[@title="Result"]/subpod/img');
            if ((string) $results[0]['src']) {
                return (string) $results[0]['src'];
            }

            $results = $xml->xpath('/queryresult/pod[not(starts-with(@title, "Input"))]/subpod/img');
            if ((string) $results[0]['src']) {
                return (string) $results[0]['src'];
            }
        }

        return false;
    }
}
