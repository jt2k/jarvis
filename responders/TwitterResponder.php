<?php
namespace jt2k\Jarvis;

use jt2k\RestApi\Twitter;

class TwitterResponder extends Responder
{
    public static $pattern = '^(.+?)( random)? tweets?$';

    public function respond()
    {
        $search = $this->matches[1];
        if (isset($this->matches[2])) {
            $random = true;
            $rpp = 50;
        } else {
            $random = false;
            $rpp = 1;
        }

        $required_config = array(
            'twitter_consumer_key',
            'twitter_consumer_secret',
            'twitter_oauth_token',
            'twitter_oauth_token_secret'
        );

        if (!$this->requireConfig($required_config)) {
            return 'Twitter API credentials are missing.';
        }

        $twitter = new Twitter($this->config['twitter_consumer_key'], $this->config['twitter_consumer_secret']);
        $twitter->login($this->config['twitter_oauth_token'], $this->config['twitter_oauth_token_secret']);

        if ($random && $this->cacheEnabled()) {
            $twitter->setCache(600, $this->config['cache_directory'], 'twitter');
        } else {
            $twitter->setCacheLife(0);
        }

        $result = $twitter->search($search, array('rpp' => $rpp));
        if (is_object($result) && is_array($result->statuses) && count($result->statuses) > 0) {
            if ($random) {
                $index = rand(0, count($result->statuses) - 1);
            } else {
                $index = 0;
            }
            $tweet = $result->statuses[$index];

            return "{$tweet->user->screen_name}: {$tweet->text} https://twitter.com/{$tweet->user->screen_name}/status/{$tweet->id_str}";
        } else {
            return 'No tweets found';
        }
    }
}
