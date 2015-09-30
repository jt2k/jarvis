<?php
namespace jt2k\Jarvis;

class Proxy
{
    protected $config = array();
    protected $hash;
    protected $type;
    protected $extension;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function request(array $params)
    {
        if (empty($params['type']) || empty($params['hash']) || empty($params['ext'])) {
            $this->sendError('type, hash, and ext parameters must be specified');
        }
        if (!preg_match('/^[0-9a-f]+$/', $params['hash'])) {
            $this->sendError('Invalid hash');
        }
        if (!preg_match('/^[0-9a-z]+$/', $params['type'])) {
            $this->sendError('Invalid type');
        }

        $this->hash = $params['hash'];
        $this->type = $params['type'];
        $this->extension = $params['ext'];
    }

    protected function getCacheFilename()
    {
        return "{$this->config['cache_directory']}/{$this->hash}.{$this->type}.proxy";
    }

    public function output()
    {
        if ($this->config['enabled_adapters'] != 'all' && !in_array('proxy', $this->config['enabled_adapters'])) {
            $this->sendError('Proxy not enabled');
        }
        if (empty($this->config['cache_directory'])) {
            $this->sendError('Cache not enabled');
        }
        $file = $this->getCacheFilename();
        if (!file_exists($file) || !is_readable($file)) {
            $this->sendError('File not found');
        }

        switch ($this->extension) {
            case 'gif':
                header('Content-Type: image/gif');
                break;
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            default:
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="proxiedfile"');
        }
        readfile($file);
    }

    public function sendError($message)
    {
        header('HTTP/1.0 404 Not Found');
        echo $message;
        exit;
    }
}
