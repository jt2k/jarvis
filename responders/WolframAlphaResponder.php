<?php
namespace jt2k\Jarvis;

use jt2k\RestApi\WolframAlpha;

class WolframAlphaResponder extends Responder
{
    public static $pattern = '^(?:(?:wa|wolfram|wolfram alpha) (.+)|(?:how|when|where|what|who|which) .+\?)$';

    public function respond()
    {
        if (!$this->requireConfig(array('wolframalpha_appid'))) {
            return;
        }

        if (isset($this->matches[1])) {
            $query = $this->matches[1];
        } else {
            $query = $this->matches[0];
        }

        $wa = new WolframAlpha($this->config['wolframalpha_appid']);
        if ($this->cacheEnabled()) {
            $wa->setCache(3600, $this->config['cache_directory'], 'wa');
        } else {
            $wa->setCacheLife(false);
        }
        $result = $wa->getAnswer($query);
        
        if ($result) {
            return $result;
        } else {
            return "I don't know";
        }
    }
}
