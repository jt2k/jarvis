<?php
namespace jt2k\Jarvis;

class Bot
{
    protected $config = array();
    protected $map = array();
    protected $responders_loaded = false;
    protected $max_response_length = 1024;

    public function __construct(array $config)
    {
        $this->config = $config;
        if (isset($this->config['max_response_length'])) {
            $this->max_response_length = $this->config['max_response_length'];
        }
        $this->loadResponders();
    }

    protected function loadAllResponders()
    {
        foreach (glob("{$this->config['responders_directory']}/*Responder.php") as $file) {
            if (preg_match('/\/([^\/]+Responder)\.php$/', $file, $m)) {
                require_once $file;
                $class_name = __NAMESPACE__ . '\\' . $m[1];
                if ($class_name::$pattern) {
                    $this->map[$class_name::$pattern] = $class_name;
                }
            }
        }
    }

    protected function loadEnabledResponders()
    {
        if (!is_array($this->config['enabled_responders'])) {
            return;
        }

        foreach ($this->config['enabled_responders'] as $name) {
            $file = "{$this->config['responders_directory']}/{$name}Responder.php";
            if (file_exists($file)) {
                require_once $file;
                $class_name = __NAMESPACE__ . '\\' . $name . 'Responder';
                if ($class_name::$pattern) {
                    $this->map[$class_name::$pattern] = $class_name;
                }
            }
        }
    }

    protected function loadResponders()
    {
        if ($this->responders_loaded) {
            return;
        }
        if (isset($this->config['enabled_responders']) && is_array($this->config['enabled_responders'])) {
            $this->loadEnabledResponders();
        } else {
            $this->loadAllResponders();
        }
        $this->responders_loaded = true;
    }

    public function respond($communication)
    {
        $result = $this->generateResponse($communication);

        return $result;
    }

    protected function generateHelp()
    {
        $help = '';
        foreach ($this->map as $regex => $class_name) {
            $title = $class_name;
            $title = str_replace('Responder', '', $title);
            $title = str_replace(__NAMESPACE__ . '\\', '', $title);
            $help .= "{$title} - {$regex}\n";
        }
        $help = trim($help);

        return $help;
    }

    protected function generateModuleHelp($module)
    {
        foreach ($this->map as $regex => $class_name) {
            $title = $class_name;
            $title = str_replace('Responder', '', $title);
            $title = str_replace(__NAMESPACE__ . '\\', '', $title);
            if (strtolower($title) == $module || (is_array($class_name::$help_words) && in_array(strtolower($module), $class_name::$help_words))) {
                if (is_array($class_name::$help)) {
                    return join("\n", $class_name::$help);
                }
            }
        }

        return "No help found for {$module}";
    }

    protected function generateResponse($communication)
    {
        $command = trim($communication['text']);
        if (isset($this->config['name']) && $this->config['name']) {
            $name = preg_quote($this->config['name']);
            $command = trim(preg_replace("/^{$name}[ :,\-]*/i", '', $command));
        }

        $responses = array();

        // respond to help command
        if (isset($this->config['enable_help']) && $this->config['enable_help'] === true && preg_match('/^help(:? |$)/', $command)) {
            if (preg_match('/^help (.+)$/', $command, $m)) {
                $module = trim($m[1]);
                $help = $this->generateModuleHelp($module);
            } else {
                $help = $this->generateHelp();
            }
            if ($help) {
                $responses[] = $help;
            }
        }

        // execute matching responders
        foreach ($this->map as $regex => $class_name) {
            if (preg_match("/{$regex}/i", $command, $matches)) {
                $responder = new $class_name($this->config, $communication, $matches);
                $response = $responder->respond();
                if ($this->max_response_length && strlen($response) > $this->max_response_length) {
                    $response = substr($response, 0, $this->max_response_length). '...';
                }
                $responses[] = $response;
            }
        }

        if (count($responses) > 0) {
            return join("\n", $responses);
        } else {
            return false;
        }
    }
}
