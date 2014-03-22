<?php
/*
    PHP REST API
    Jason Tan
    http://code.google.com/p/php-rest-api/
*/
namespace jt2k\RestApi;

require_once __DIR__ . '/../../OAuth/OAuth.php';

class OAuthRestApi extends RestApi
{
    protected $oa_method;
    protected $consumer;
    protected $request_token;
    protected $access_token;

    public function __construct($consumer_key, $consumer_secret)
    {
        $this->consumer = new \OAuthConsumer($consumer_key, $consumer_secret);
        $this->oa_method = new \OAuthSignatureMethod_HMAC_SHA1();
        parent::__construct();
    }

    public function login($oauth_token, $oauth_token_secret)
    {
        $this->access_token = new \OAuthConsumer($oauth_token, $oauth_token_secret);
    }

    public static function parseToken($string)
    {
        $token = array();
        parse_str($string, $token);
        if (isset($token['oauth_token']) && isset($token['oauth_token_secret']))
            return new \OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        else
            return false;
    }

    public function getAuthorizeUrl($request_url, $authorize_url, $callback = false)
    {
        $req = \OAuthRequest::from_consumer_and_token($this->consumer, null, 'GET', $request_url);
        $req->sign_request($this->oa_method, $this->consumer, null);

        $format = $this->format;
        $this->format = "text";
        $result = parent::request($req->to_url(), array('cache_life'=>0));
        $this->format = $format;

        if (($token = self::parseToken($result))===false)
            return false;

        $this->request_token = $token;
        $_SESSION['request_token'] = $token;

        $authorize_url .= "?oauth_token={$this->request_token->key}";
        if ($callback)
            $authorize_url .= "&oauth_callback=" . urlencode($callback);

        return $authorize_url;
    }

    public function getAccessToken($access_url)
    {
        if (!is_object($this->request_token)) {
            if (is_object($_SESSION['request_token']))
                $this->request_token = $_SESSION['request_token'];
            else
                return false;
        }
        $req = \OAuthRequest::from_consumer_and_token($this->consumer, $this->request_token, 'GET', $access_url);
        $req->sign_request($this->oa_method, $this->consumer, $this->request_token);

        $format = $this->format;
        $this->format = "text";
        $result = parent::request($req->to_url(), array('cache_life'=>0));
        $this->format = $format;

        if (($token = self::parseToken($result))===false)
            return false;
        return $this->access_token = $token;
    }

    public function getCacheFile($url)
    {
        $url = preg_replace('/[\?|&]oauth_version.*$/','',$url);
        $cache_file = $this->cache_dir . '/' .  md5($url.'|'.$this->access_token->key);
        if ($this->cache_ext)
            $cache_file .= ".{$this->cache_ext}";

        return $cache_file;
    }

    public function request($url, $extra = array(), $force_post = false)
    {
        $oauth = array(
            'oauth_version' => \OAuthRequest::$version,
            'oauth_nonce' => \OAuthRequest::generate_nonce(),
            'oauth_timestamp' => \OAuthRequest::generate_timestamp(),
            'oauth_consumer_key' => $this->consumer->key,
            'oauth_token' => $this->access_token->key,
            'oauth_signature_method'=>$this->oa_method->get_name()
        );

        if (isset($extra['post']))
            $params = $extra['post'];
        elseif (isset($extra['get']))
            $params = $extra['get'];
        else
            $params = array();

        if (isset($extra['post']) || $force_post)
            $method = 'POST';
        else
            $method = 'GET';

        $params = array_merge($params, $oauth);
        $request = new \OAuthRequest($method, $url, $params);
        $params['oauth_signature'] = $request->build_signature($this->oa_method, $this->consumer, $this->access_token);

        $extra[strtolower($method)] = $params;

        return parent::request($url, $extra, $force_post);
    }
}
