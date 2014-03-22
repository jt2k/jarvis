<?php
namespace jt2k\Jarvis;

use jt2k\RestApi\WolframAlpha;

class WolframAlphaResponder extends Responder
{
    public static $pattern = '^(:?how|when|where|what|who) .+\?$';

    public function respond()
    {
        if (!$this->requireConfig(array('wolframalpha_appid'))) {
            return;
        }

        $wa = new WolframAlpha($this->config['wolframalpha_appid']);
        if ($this->cacheEnabled()) {
            $wa->setCache(3600, $this->config['cache_directory'], 'wa');
        } else {
            $wa->setCacheLife(false);
        }

        $result = $wa->getAnswer($this->matches[0]);
        if ($result) {
            return $result;
        } else {
            return "I don't know";
        }
    }
}
