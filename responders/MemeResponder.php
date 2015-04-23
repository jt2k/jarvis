<?php
namespace jt2k\Jarvis;

class MemeResponder extends Responder
{
    public static $pattern = '.*';

    protected static $patterns = array(
        'fry.png' => '(not sure if .*) (or .*)',
        'ned_stark.jpg' => '(brace (?:yourself|yourselves)[ ,]*)(.*)',
        'most_interesting.jpg' => '(i don\'t always .*)(but when i do.*)',
        'boromir.jpg' => '(one does not simply) (.*)',
        'y_u_no.jpg' => '(.*)(y u no .*)',
        'all_the_things.jpg' => '(.*) (all the .*)',
        'xzibit.jpg' => '(.*i heard you .*) (so i .*)'
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

    public function respond()
    {
        foreach (self::$patterns as $image => $regex) {
            if (preg_match("/{$regex}/i", $this->matches[0], $m)) {
                $params = array(
                    'u' => "http://v1.memecaptain.com/{$image}",
                );
                if (!empty($m[1])) {
                    $params['t1'] = $m[1];
                }
                if (!empty($m[2])) {
                    $params['t2'] = $m[2];
                }
                $url = 'http://v1.memecaptain.com/g?' . http_build_query($params);
                $response = $this->request($url);
                if (is_object($response) && !empty($response->imageUrl)) {
                    return $response->imageUrl;
                } else {
                    return 'Failed to generate image. Use your imagination.';
                }
            }
        }
    }
}
