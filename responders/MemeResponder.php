<?php
namespace jt2k\Jarvis;

class MemeResponder extends Responder
{
    public static $pattern = '.*';

    protected static $patterns = array(
        'CsNF8w' => '(not sure if .*) (or .*)',
        '_I74XA' => '(brace (?:yourself|yourselves)[ ,]*)(.*)',
        'V8QnRQ' => '(i don\'t always .*)(but when i do.*)',
        'da2i4A' => '(one does not simply) (.*)',
        'NryNmg' => '(.*)(y u no .*)',
        'Dv99KQ' => '(.*) (all the [\w!\.]+)$',
        'Yqk_kg' => '(.*i heard you .*) (so i .*)'
    );

    public static $help = array(
        'Generates meme text images:',
        '  (not sure if .*) (or .*)',
        '  (brace (?:yourself|yourselves)[ ,]*)(.*)',
        '  (i don\'t always .*)(but when i do.*)',
        '  (one does not simply) (.*)',
        '  (.*)(y u no .*)',
        '  (.*) (all the .*)',
        '  (.*i heard you .*) (so i .*)'
    );

    protected function curlLocationAndResponseCode($url, $options = array(), $post = null)
    {
        $options += array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => false
        );
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
        }
        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (preg_match('/^Location:(.*)$/mi', $response, $m)) {
            $location = trim($m[1]);
        } else {
            $location = false;
        }
        return array($responseCode, $location);
    }

    public function respond()
    {
        foreach (self::$patterns as $image => $regex) {
            if (preg_match("/{$regex}/i", $this->matches[0], $m)) {
                $url = 'http://memecaptain.com/gend_images';
                $topText = $bottomText = '';
                if (!empty($m[1])) {
                    $topText = $m[1];
                }
                if (!empty($m[2])) {
                    $bottomText = $m[2];
                }

                $post = [
                    'src_image_id' => $image,
                    'private' => true,
                    'captions_attributes' => [
                        [
                            'text' => $topText,
                            'top_left_x_pct' => 0.05,
                            'top_left_y_pct' => 0,
                            'width_pct' => 0.9,
                            'height_pct' => 0.25
                        ],
                        [
                            'text' => $bottomText,
                            'top_left_x_pct' => 0.05,
                            'top_left_y_pct' => 0.75,
                            'width_pct' => 0.9,
                            'height_pct' => 0.25
                        ]
                    ]
                ];

                $options = [
                    CURLOPT_HTTPHEADER => [
                        'Accept: application/json',
                        'Content-Type: application/json'
                    ]
                ];
                $image = false;
                list($code, $pendingLocation) = $this->curlLocationAndResponseCode($url, $options, json_encode($post));
                if ($pendingLocation) {
                    $counter = 0;
                    do {
                        list($code, $imageLocation) = $this->curlLocationAndResponseCode($pendingLocation);
                        if ($code == 303) {
                            $image = $imageLocation;
                            break;
                        }
                        sleep(1);
                    } while (++$counter < 5);
                }

                if ($image) {
                    return $image;
                } else {
                    return 'Failed to generate image. Use your imagination.';
                }
            }
        }
    }
}
