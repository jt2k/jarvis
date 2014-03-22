<?php
namespace jt2k\Jarvis;

class TDOTAvgSpeedResponder extends Responder
{
    public static $pattern = '(440|40|65|24) ?([NESW])';

    public function respond()
    {
        libxml_use_internal_errors(true);
        $i = $this->matches[1];
        $dir = strtoupper($this->matches[2]);

        $url = 'http://ww2.tdot.state.tn.us/tsw/GeoRSS/TDOTNashSpeedGeorss.xml';
        $xml = $this->request($url, 300, 'tdot', 'xml');
        if (!is_object($xml)) {
            return;
        }
        $date = (string) $xml->channel->date . ' ' . (string) $xml->channel->time;
        $return = "TDOT average speed for {$i}{$dir} (" . date('n/j g:ia', strtotime($date)) . ")\n";
        $results = $xml->xpath("/rss/channel/item[starts-with(title, \"I-{$i}{$dir}\") or starts-with(title, \"I-{$i} {$dir}\")]");
        foreach ($results as $node) {
            $speed = (string) $node->AverageSpeed;
            $label = (string) $node->title;
            $label = preg_replace('/\s*-\s*0 TO 0/', '', $label);
            $label = preg_replace('/^I-\d+ ?[NESW]B?([\/\\\]\s*I-\d+ ?[NESW]B?)?/', '', $label);
            $label = trim($label);
            $return .= "{$speed}mph {$label}\n";
        }

        return trim($return);
    }
}
