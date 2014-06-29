<?php
namespace jt2k\Jarvis;

class IRCBot extends Bot
{
    protected $hostname;
    protected $port = 6667;
    protected $nickname;
    protected $password = false;
    protected $channels = array();

    protected $debug = false;
    protected $socket;

    protected $empty_count = 0;

    public function __construct(array $config)
    {
        $parameters = array('hostname', 'port', 'password', 'channels');
        foreach ($parameters as $parameter) {
            if (isset($config['irc_' . $parameter])) {
                $this->$parameter = $config['irc_' . $parameter];
            }
            if (is_null($this->$parameter)) {
                throw new \Exception("irc_{$parameter} must be configured");
            }
        }
        $this->nickname = $config['name'];
        return parent::__construct($config, 'irc');
    }

    public function setDebug($debug)
    {
        $this->debug = (bool)$debug;
    }

    public function connect()
    {
        $this->socket = fsockopen($this->hostname, $this->port);
        if (!is_resource($this->socket)) {
            throw new \Exception("Could not connect to {$this->hostname}:{$this->port}");
        }

        $this->login();

        $this->write("NICK {$this->nickname}");        
        foreach ($this->channels as $channel) {
            $this->write("JOIN {$channel}");
        }
        $this->listen();
    }

    protected function login()
    {
        if ($this->password) {
            $this->write("PASS {$this->password}");
        }
        $this->write("USER {$this->nickname} 0 * :{$this->nickname}");
    }

    protected function disconnect()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
            echo "Connection closed\n";
        }
    }

    protected function write($data)
    {
        if ($this->debug) {
            echo ">>> $data\n";
        }
        return fwrite($this->socket, $data . "\r\n");
    }

    protected function read()
    {
        return fgets($this->socket, 1024);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    protected function listen()
    {
        while (true) {
            if ($line = $this->read()) {
                if ($this->debug) {
                    echo "<<< $line";
                }
                $this->empty_count = 0;
                $this->process($line);
            } else {
                if ($this->debug) {
                    echo "!<<\n";
                }
                if (++$this->empty_count > 5) {
                    echo "Lost connection\n";
                    break;
                }
                sleep(1);
            }
        }
    }

    protected function process($line)
    {
        $pieces = explode(' ', $line);

        switch ($pieces[0]) {
            case 'PING':
                $this->write("PONG {$pieces[1]}");
                return;
                break;
        }

        if (isset($pieces[1])) {
            $action = $pieces[1];
            switch ($action) {
                case 'PRIVMSG':
                    if (preg_match('/^:([^!]+)!.*? PRIVMSG ([^ ]+) :(.+)$/', $line, $m)) {
                        $user = $m[1];
                        $channel = $m[2];
                        $message = trim($m[3]);
                        if ($channel == $this->nickname) {
                            // if it's a direct message, respond via direct message.
                            $channel = $user;
                        }
                        $communication = array(
                            'user_name' => $user,
                            'text' => $message,
                            'bot_type' => 'irc'
                        );
                        if ($response = $this->respond($communication)) {
                            foreach (explode("\n", $response) as $line) {
                                $this->write("PRIVMSG {$channel} :{$line}");
                                usleep(800000);
                            }
                        }
                    }
                    return;
                    break;
            }
        }
    }
}