<?php
namespace jt2k\Jarvis;

use jt2k\DB\DB;
use DateTime;
use Exception;

class Bot
{
    protected $name = 'jarvis';
    protected $config = array();
    protected $map = array();
    protected $responders_loaded = false;
    protected $max_response_length = 1024;
    protected $start;
    protected $db;

    public function __construct(array $config, $adapter = null)
    {
        $this->start = new DateTime();
        $this->config = $config;
        if ($adapter && !$this->enabledAdapter($adapter)) {
            throw new Exception("{$adapter} adpater is disabled");
        }
        if (isset($this->config['max_response_length'])) {
            $this->max_response_length = $this->config['max_response_length'];
        }
        if (isset($this->config['name'])) {
            $this->name = $this->config['name'];
        }
        $this->loadResponders();
    }

    protected function getDb()
    {
        if (is_object($this->db)) {
            return $this->db;
        } elseif (is_array($this->config['database'])) {
            switch ($this->config['database']['engine']) {
                case 'mysql':
                    $dsn = DB::dsnMySQL($this->config['database']['host'], $this->config['database']['schema']);
                    $this->db = new DB($dsn, $this->config['database']['user'], $this->config['database']['password']);
                    $this->db->setErrorMode('warning');
                    return $this->db;
                    break;
                default:
                    throw new Exception("{$this->config['database']['engine']} database engine not supported");
            }
        }
    }

    protected function disconnectDb()
    {
        $this->db = null;
    }

    protected function enabledAdapter($adapter)
    {
        return ($this->config['enabled_adapters'] == 'all' ||
                in_array($adapter, $this->config['enabled_adapters']));
    }

    protected function loadAllResponders()
    {
        $directories = $this->config['responders_directory'];
        if (!is_array($directories)) {
            $directories = array($directories);
        }
        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                throw new Exception("Could not find responders directory {$directory}");
            }
            foreach (glob("{$directory}/*Responder.php") as $file) {
                if (preg_match('/\/([^\/]+Responder)\.php$/', $file, $m)) {
                    require_once $file;
                    $class_name = __NAMESPACE__ . '\\' . $m[1];
                    if ($class_name::$pattern) {
                        $this->map[$class_name::$pattern] = $class_name;
                    }
                }
            }
        }
    }

    protected function loadEnabledResponders()
    {
        if (!is_array($this->config['enabled_responders'])) {
            return;
        }

        $directories = $this->config['responders_directory'];
        if (!is_array($directories)) {
            $directories = array($directories);
        }

        foreach ($this->config['enabled_responders'] as $name) {
            foreach ($directories as $directory) {
                $file = "{$directory}/{$name}Responder.php";
                if (file_exists($file)) {
                    require_once $file;
                    $class_name = __NAMESPACE__ . '\\' . $name . 'Responder';
                    if ($class_name::$pattern) {
                        $this->map[$class_name::$pattern] = $class_name;
                    }
                    continue;
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

    protected function getUserConfig($user, $bot_type)
    {
        $config = array();
        $db = $this->getDb();
        $settings = $db->getRows("SELECT * FROM user_settings WHERE user = ? AND bot_type = ?", array($user, $bot_type));
        foreach ($settings as $setting) {
            if ($value = unserialize($setting['value'])) {
                $config[$setting['setting']] = $value;
            }
        }
        return $config;
    }

    protected function setUserConfig($user, $bot_type, $setting, $value)
    {
        // unset
        if (is_null($value)) {
            if ($setting == 'all') {
                $this->getDb()->execute("DELETE FROM user_settings WHERE user = ? AND bot_type = ?", array($user, $bot_type));
                return "All settings cleared";
            } else {
                $this->getDb()->execute("DELETE FROM user_settings WHERE user = ? AND bot_type = ? AND setting = ?", array($user, $bot_type, $setting));
                return "Cleared {$setting} setting";
            }
        }

        // TODO - configure configuratble settings in config.php
        $available_settings = array('location');
        if (!in_array($setting, $available_settings)) {
            return "No user-configurable setting for {$setting}";
        }

        // special settings that require pre-processing
        switch ($setting) {
            case 'location':
                $ll_regex = '/^(-?[0-9\.]+)[ ,]+(-?[0-9\.]+)$/';
                if (preg_match($ll_regex, $value, $m)) {
                    $value = array(floatval($m[1]), floatval($m[2]));
                } else {
                    $geocode = new GeocodeResponder($this->config, array(), array("geocode $value", $value));
                    $result = $geocode->respond();
                    if (preg_match($ll_regex, $result, $m)) {
                        $value = array(floatval($m[1]), floatval($m[2]));
                    } else {
                        return 'Could not geocode "' . $value . '"';
                    }
                }
                break;
        }

        $this->getDb()->replace('user_settings', array(
            'user' => $user,
            'bot_type' => $bot_type,
            'setting' => $setting,
            'value' => serialize($value)
        ));
        if (is_array($value) || is_object($value)) {
            return "Set {$setting} to " . json_encode($value);
        } else {
            return "Set {$setting} to {$value}";
        }
    }

    public function respond($communication)
    {
        $result = $this->generateResponse($communication);

        // disconnect from db to prevent long-running connections that might timeout
        $this->disconnectDb();

        return $result;
    }

    protected function generateHelp()
    {
        $help = "Responders:\n";
        foreach ($this->map as $regex => $class_name) {
            $title = $class_name;
            $title = str_replace('Responder', '', $title);
            $title = str_replace(__NAMESPACE__ . '\\', '', $title);
            $help .= "{$title} - {$regex}\n";
        }
        $help .= "\nUser settings:\n";
        $help .= "Set - set [setting] [value]\n";
        $help .= "Unset - unset [setting]\n";
        $help .= "Reset - unset all\n";
        $help .= "List - settings";

        return $help;
    }

    protected function generateStatus()
    {
        $convertBytes = function($bytes) {
            if ($bytes < 1024) {
                return $bytes . 'B';
            } elseif ($bytes < (1024*1024)) {
                return number_format($bytes/1024, 2) . 'KB';
            } else {
                return number_format($bytes/(1024*1024), 2) . 'MB';
            }
        };
        $status = '';
        $botType = get_class($this);
        $botType = preg_replace('/^.*\\\\/', '', $botType);
        $status .= "Bot type: {$botType}\n";
        $status .= "PID: " . getmypid() . "\n";
        $memory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);
        $status .= "Memory usage: " . $convertBytes($memory) . "\n";
        if ($peakMemory > $memory) {
            $status .= "Memory usage (peak): " . $convertBytes($peakMemory) . "\n";
        }
        $now = new DateTime();
        $seconds = $now->getTimestamp() - $this->start->getTimestamp();
        if ($seconds > 1) {
            $interval = $this->start->diff($now);
            $status .= "Uptime: {$interval->days} day" . ($interval->days != 1 ? 's' : '') . ', ' . $interval->format('%H:%I:%S');
        }

        return trim($status);
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
            $command = trim(preg_replace("/^{$name}[:,\-]*/i", '', $command));
        }

        $responses = array();

        // respond to help command
        if (isset($this->config['enable_help']) && $this->config['enable_help'] === true && preg_match('/^help(:? |$)/i', $command)) {
            if (preg_match('/^help (.+)$/i', $command, $m)) {
                $module = trim($m[1]);
                $help = $this->generateModuleHelp($module);
            } else {
                $help = $this->generateHelp();
            }
            if ($help) {
                $responses[] = $help;
            }
        }

        // respond to status command
        if (isset($this->config['enable_status']) && $this->config['enable_status'] === true && preg_match('/^status$/i', $command)) {
            $status = $this->generateStatus();
            if ($status) {
                $responses[] = $status;
            }
        }

        // Handle user setting commands
        if (isset($communication['user_name']) && isset($communication['bot_type']) && isset($this->config['database'])) {
            // Set
             if (preg_match('/^set (\w+) (.+)$/', $command, $m)) {
                $setting = $this->setUserConfig($communication['user_name'], $communication['bot_type'], $m[1], $m[2]);
                if ($setting) {
                    $responses[] = $setting;
                }
            }
            // Unset
            if (preg_match('/^unset (\w+)$/', $command, $m)) {
                $setting = $this->setUserConfig($communication['user_name'], $communication['bot_type'], $m[1], null);
                if ($setting) {
                    $responses[] = $setting;
                }
            }
            // Get
            if (preg_match('/^settings$/i', $command, $m)) {
                $settings = $this->getUserConfig($communication['user_name'], $communication['bot_type']);
                $responses[] = "Your settings: " . json_encode($settings);
            }
        }

        if (count($responses) == 0) {
            // execute matching responders, unless help was triggered or user settings configured

            if (isset($communication['user_name']) && isset($communication['bot_type']) && isset($this->config['database'])) {
                $user_config = $this->getUserConfig($communication['user_name'], $communication['bot_type']);
                $config = array_merge($this->config, $user_config);
            } else {
                $config = $this->config;
            }

            foreach ($this->map as $regex => $class_name) {
                if (preg_match("/{$regex}/i", $command, $matches)) {
                    $responder = new $class_name($config, $communication, $matches);
                    $response = $responder->respond();
                    $response = trim($response);
                    if ($response !== '') {
                        if ($this->max_response_length && strlen($response) > $this->max_response_length) {
                            $response = substr($response, 0, $this->max_response_length). '...';
                        }
                        $responses[] = $response;
                    }
                }
            }
        }

        if (count($responses) > 0) {
            return join("\n", $responses);
        } else {
            return false;
        }
    }
}
