<?php
namespace jt2k\Jarvis;

class HTTPResponder extends Responder
{
    public static $pattern = '^check (https?:\/\/[\w\.\/:\-%]+|[\w\/:\-%]+\.[\w\.\/:\-%]+)$';
    public function respond()
    {
        $ch = curl_init($this->matches[1]);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (curl_exec($ch) === false) {
            $result = curl_error($ch);
        } else {
            $info = curl_getinfo($ch);
            if ($info['size_download'] < 1024) {
                $size = $info['size_download'] . 'B';
            } else {
                $size = round($info['size_download'] / 1024) . 'KB';
            }

            $time = round($info['total_time'] * 1000) . 'ms';
            $nstime = round($info['namelookup_time'] * 1000) . 'ms';
            $ctime = round($info['connect_time'] * 1000) . 'ms';
            $result = "Status: HTTP {$info['http_code']}, Size: {$size}, Time: {$time} ({$nstime} name lookup, {$ctime} connect)";
            if (isset($info['redirect_url']) && $info['redirect_url']) {
                $result .= ", Redirect: {$info['redirect_url']}";
            }
        }

        return $result;
    }
}
