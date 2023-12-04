<?php
namespace jt2k\Jarvis;

use jt2k\RestApi\RestApi;
use jt2k\DB\DB;

abstract class Responder
{
    protected $config = array();
    protected $communication = array();
    protected $matches = array();
    private $db;

    public static $pattern;
    public static $help;
    public static $help_words;
    public static $require_direct_address = false;

    abstract public function respond();

    public function __construct(array $config, array $communication, array $matches, DB $db = null)
    {
        $this->config = $config;
        $this->communication = $communication;
        $this->matches = $matches;
        $this->db = $db;
    }

    public function getName()
    {
        $name = get_class($this);
        $name = preg_replace('/^.*\\\\/', '', $name);
        $name = preg_replace('/Responder$/', '', $name);
        return $name;
    }

    public function hasStorage()
    {
        return $this->db instanceof DB;
    }

    protected function requireConfig(array $parameters)
    {
        foreach ($parameters as $parameter) {
            if (!isset($this->config[$parameter])) {
                return false;
            }
        }

        return true;
    }

    protected function cleanupCache()
    {
        foreach (glob($this->config['cache_directory'] . '/*') as $file) {
            if (filemtime($file) < time() - 3600*24*7) {
                @unlink($file);
            }
        }
    }

    protected function cacheEnabled()
    {
        $enabled = (
            isset($this->config['cache_directory']) &&
            $this->config['cache_directory'] &&
            is_dir($this->config['cache_directory']) &&
            is_writable($this->config['cache_directory'])
        );
        if ($enabled && rand(0,20) == 0) {
            $this->cleanupCache();
        }

        return $enabled;
    }

    protected function proxyEnabled()
    {
        return (
            $this->cacheEnabled() &&
            ($this->config['enabled_adapters'] == 'all' ||
                in_array('proxy', $this->config['enabled_adapters'])) &&
            !empty($this->config['web_url'])
        );
    }

    protected function callResponder($name, $command)
    {
        if (!preg_match('/.+Responder$/', $name)) {
            $name .= 'Responder';
        }
        $class = __NAMESPACE__ . '\\' . $name;

        if (!class_exists($class)) {
            return false;
        }

        $regex = $class::$pattern;
        if (preg_match("/{$regex}/i", $command, $matches)) {
            $responder = new $class($this->config, array(), $matches, $this->db);
            return $responder->respond();
        }

        return false;
    }

    protected function request($url, $cache_ttl = false, $cache_ext = '', $format = 'json', $headers = [])
    {
        $restapi = new RestApi();
        $restapi->setFormat($format);
        if ($cache_ttl && $this->cacheEnabled()) {
            $restapi->setCache($cache_ttl, $this->config['cache_directory'], $cache_ext);
        } else {
            $restapi->setCacheLife(0);
        }
        $headers[] = 'User-Agent: jarvis';
        return $restapi->request($url, ['headers' => $headers]);
    }

    protected function requestRaw($url, $cache_ttl = false, $cache_ext = '')
    {
        return $this->request($url, $cache_ttl, $cache_ext, 'text');
    }

    protected function requestProxy($url, $cache_ttl, $cache_ext, $file_type)
    {
        if (!$this->proxyEnabled()) {
            return false;
        }
        if ($cache_ttl > 0) {
            $ts = floor(time() / $cache_ttl) * $cache_ttl;
            if (strpos($url, '?') === false) {
                $url .= "?{$ts}";
            } else {
                $url .= "&{$ts}";
            }
        }
        $this->requestRaw($url, $cache_ttl, $cache_ext . '.proxy');
        $restapi = new RestApi();
        $restapi->setCache(-1, '', '');
        $cacheHash = ltrim($restapi->getCacheFile($url, false), '/');

        return "{$this->config['web_url']}proxy/{$cache_ext}/{$cacheHash}.{$file_type}?{$ts}";
    }

    protected function getProxyUrl($url, $type)
    {
        $hash = $this->requestCacheHash($url);
    }

    protected function getStorage($key)
    {
        return $this->db->getValue("SELECT value FROM storage WHERE responder = ? and `key` = ?", array($this->getName(), $key));
    }

    protected function setStorage($key, $value)
    {
        return $this->db->replace('storage', array(
            'responder' => $this->getName(),
            'key' => $key,
            'value' => $value
        ));
    }

    protected function getStorageSet($prefix)
    {
        $rows = $this->db->getRows("SELECT `key`, value FROM storage WHERE responder = ? and `key` like ?", array($this->getName(), "{$prefix}::%"));
        $set = [];
        foreach ($rows as $row) {
            $key = preg_replace('/^' . preg_quote($prefix) . '::/', '', $row['key']);
            $set[$key] = $row['value'];
        }
        return $set;
    }

    protected function setStorageSet($prefix, $values)
    {
        foreach ($values as $key => $value) {
            $this->db->replace('storage', array(
                'responder' => $this->getName(),
                'key' => "{$prefix}::{$key}",
                'value' => $value
            ));
        }
    }
}
