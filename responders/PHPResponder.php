<?php
namespace jt2k\Jarvis;

class PHPResponder extends Responder
{
    public static $pattern = '^php (.+)$';

    protected static function cleanString($string)
    {
        $string = strip_tags($string);
        $string = html_entity_decode($string);
        $string = preg_replace('/\s+/', ' ', $string);
        $string = trim($string);

        return $string;
    }

    public function respond($redirect = false)
    {
        if ($redirect) {
            $url = 'http://us2.php.net' . $redirect;
        } else {
            $url = 'http://us2.php.net/' . urlencode($this->matches[1]);
        }
        $html = $this->request($url, 3600, 'phpnet', 'html');
        if (preg_match('/<h2 class="title">Class synopsis<\/h2>/', $html)) {
            $result = '';
            if (preg_match('/<h1 class="title">(.*?)<\/h1>/', $html, $n)) {
                $result = $n[1];
            }
            if (preg_match('/<h2 class="title">Introduction<\/h2>\s*<p class="para">(.*?)<\/p>/s', $html, $o)) {
                $result .= ' - ' . $o[1];
            }
            $result = self::cleanString($result);
            if ($result) {
                return $result;
            }
        } elseif (preg_match('/<div class="(?:method|constructor)synopsis dc-description">(.*?)<\/div>/s', $html, $m)) {
            return self::cleanString($m[1]);
        } elseif (preg_match('/<p class="refpurpose">(.*?)<\/p>/s', $html, $m)) {
            return self::cleanString($m[1]);
        } elseif (!$redirect && preg_match('/<ul id="quickref_functions">.*?href="([^"]+)"/s', $html, $m)) {
            return $this->respond($m[1]);
        }

        return 'Nothing found';
    }
}
