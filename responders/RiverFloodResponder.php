<?php
namespace jt2k\Jarvis;

class RiverFloodResponder extends Responder
{
    public static $pattern = '(?:river|flood)(\s+([a-z0-9]+))?';

    public function respond()
    {
        if (isset($this->matches[2])) {
            $id = strtolower($this->matches[2]);
        } else {
            if (!$this->requireConfig(array('riverflood_default_gauge'))) {
                return;
            }
            $id = $this->config['riverflood_default_gauge'];
        }

        $url = "http://water.weather.gov/ahps2/hydrograph_to_xml.php?gage={$id}&output=xml";
        $xml = $this->request($url, 300, 'hydro', 'xml');
        if (!is_object($xml)) {
            return "Could not retrieve information on {$id}";
        }
        if (!is_object($xml->observed->datum) || !is_object($xml->observed->datum[0])) {
            return 'No data for this gauge';
        }

        // Get name
        $name = (string)$xml->attributes()->name;

        // Get flood stages
        $stage_types = array('action', 'flood', 'moderate', 'major');
        $stages = array();
        foreach ($stage_types as $type) {
            $stages[$type] = (float)$xml->sigstages->{$type};
        }

        // Get max level
        $max = 0;
        $previous = null;
        $previous_date = null;
        $trend = null;
        $trend_date = null;
        $max_date = null;
        foreach (array_reverse($xml->xpath('/site/observed/datum')) as $datum) {
            $level = (float)$datum->primary;
            $date = (string)$datum->valid;
            if ($level > $max) {
                $max = $level;
                $max_date = $date;
            }
            if (!is_null($previous)) {
                if ($level == $previous && $trend !== 0) {
                    $trend = 0;
                    $trend_date = $previous_date;
                } elseif ($level > $previous) {
                    if ($trend >= 0) {
                        $trend++;
                    } else {
                        $trend = 1;
                        $trend_date = $previous_date;
                    }
                } elseif ($level < $previous) {
                    if ($trend <= 0) {
                        $trend--;
                    } else {
                        $trend = -1;
                        $trend_date = $previous_date;
                    }
                }
            }
            $previous = $level;
            $previous_date = $date;
        }
        $max_date = date('n/j g:ia', strtotime($max_date));
        $trend_date = date('n/j g:ia', strtotime($trend_date));

        // Get current level
        $current_level = (float)$xml->observed->datum[0]->primary;
        $current_date = (string)$xml->observed->datum[0]->valid;
        $current_date = date('n/j g:ia', strtotime($current_date));

        // Get flood stage for current and max
        $category = false;
        $max_category = false;
        foreach ($stage_types as $type) {
            if ($current_level >= $stages[$type]) {
                $category = strtoupper($type);
            }
            if ($max >= $stages[$type]) {
                $max_category = strtoupper($type);
            }
        }

        $unit = (string)$xml->observed->datum[0]->primary->attributes()->units;
        $string = "River hydrograph for {$name}";
        $string .= "\nLatest reading: {$current_date} - {$current_level} {$unit}";
        if ($category) {
            $string .= " **{$category}**";
        }
        $string .= "\nTrend: ";
        if ($trend == 0) {
            $string .= 'holding steady';
        } else if ($trend > 0) {
            $string .= 'rising';
        } else {
            $string .= 'falling';
        }
        $string .= " since {$trend_date}";
        $string .= "\nPeak: {$max_date} - {$max} {$unit}";
        if ($max_category) {
            $string .= " *{$max_category}*";
        }
        $string .= "\nhttp://water.weather.gov/resources/hydrographs/{$id}_hg.png";

        return $string;
    }
}
