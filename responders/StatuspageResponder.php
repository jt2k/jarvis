<?php
namespace jt2k\Jarvis;

class StatuspageResponder extends Responder
{
    public static $pattern = '^statuspage\s*(\w+)?$';

    public function respond()
    {
        if ($this->requireConfig(['statuspage'])) {
            $config = $this->config['statuspage'];
        } else {
            return 'statuspage configuration is missing.';
        }

        if ($service = $this->matches[1]) {
            if (array_key_exists($service, $config)) {
                $config = [$service => $config[$service]];
            } else {
                return "statuspage configuration for {$service} is missing.";
            }
        }

        $response = '';
        foreach ($config as $service => $id) {
            $status = $this->request("https://{$id}.statuspage.io/api/v2/status.json", 300, 'statuspage');
            if (is_object($status) && isset($status->status)) {
                $response .= "{$status->page->name}: {$status->status->description}\n";
            }
        }

        return $response;
    }
}
