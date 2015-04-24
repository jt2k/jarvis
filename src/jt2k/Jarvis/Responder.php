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
            if (filemtime($file) < time() - 3600*24) {
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

    protected function request($url, $cache_ttl = false, $cache_ext = '', $format = 'json')
    {
        $restapi = new RestApi();
        $restapi->setFormat($format);
        if ($cache_ttl && $this->cacheEnabled()) {
            $restapi->setCache($cache_ttl, $this->config['cache_directory'], $cache_ext);
        } else {
            $restapi->setCacheLife(0);
        }
        return $restapi->request($url, array('headers' => array('User-Agent: jarvis')));
    }

    protected function requestRaw($url, $cache_ttl = false, $cache_ext = '')
    {
        return $this->request($url, $cache_ttl, $cache_ext, 'text');
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
}
