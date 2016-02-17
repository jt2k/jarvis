<?php
namespace jt2k\Jarvis;

class DictionaryResponder extends Responder
{
    public static $pattern = '^define (.+?)( all)?$';
    public static $help = array(
        'define [word] - returns the first definition of [word] from the Merriam Webster Collegiate Dictionary',
        'define [word] all - returns all definitions'
    );
    public static $help_words = array('define');

    protected $word;
    protected $data;

    protected function parseDefinition($dt)
    {
        $xml = $dt->asXML();
        $xml = preg_replace('/<vi>(.*?)<\/vi>/', '', $xml);
        $xml = preg_replace('/<sxn>(.*?)<\/sxn>/', '', $xml);
        $def = strip_tags($xml);
        $def = trim($def, ': ');

        return $def;
    }

    protected function parseNumber($sn)
    {
        $prefix = $sn->asXML();
        $prefix = strip_tags($prefix);
        $prefix = trim(str_replace(' ', '', $prefix)) . '. ';

        return $prefix;
    }

    protected function parseEntry($entry, $single = false)
    {
        if (strcasecmp((string)$entry->ew, $this->word) !== 0) {
            return false;
        }
        $part_of_speech = (string) $entry->fl;
        $defs = array();
        $i = 0;
        $v = 0;
        foreach ($entry->def->dt as $dt) {
            if (!$single && isset($entry->def->sn[$i])) {
                $prefix = $this->parseNumber($entry->def->sn[$i]);
            } else {
                $prefix = '';
            }
            if (!$single && preg_match('/^1/', $prefix) && isset($entry->def->vt[$v])) {
                $defs[] = '(' . $entry->def->vt[$v] . ')';
                $v++;
            }
            $defs[] =  $prefix . $this->parseDefinition($dt);
            if ($single) {
                break;
            }
            $i++;
        }

        return array($part_of_speech, $defs);
    }

    protected function defineAll()
    {
        $entries = array();
        foreach ($this->data->xpath("/entry_list/entry") as $entry) {
            if ($defs = $this->parseEntry($entry)) {
                $entries[] = $defs;
            }
        }
        $string = '';
        foreach ($entries as $entry) {
            $string .= "{$this->word}, {$entry[0]}:\n";
            $string .= join("\n", $entry[1]);
            $string .= "\n\n";
        }

        return trim($string);
    }

    protected function defineOne()
    {
        $entry = $this->data->xpath("/entry_list/entry[1]");
        if (!$entry) {
            return;
        }
        $result = $this->parseEntry($entry[0], true);
        if (!$result) {
            return;
        }

        return "{$this->word}, {$result[0]}: {$result[1][0]}";
    }

    public function respond()
    {
        if (!$this->requireConfig(array('merriam_webster_key'))) {
            return;
        }
        if (isset($this->matches[2])) {
            $all = true;
        } else {
            $all = false;
        }

        $this->word = trim(strtolower($this->matches[1]));
        $key = $this->config['merriam_webster_key'];
        $word = urlencode($this->word);
        $url = "http://www.dictionaryapi.com/api/v1/references/collegiate/xml/{$word}?key={$key}";

        $this->data = $this->request($url, 3600 * 24, 'mw', 'xml');

        if ($all) {
            return $this->defineAll();
        } else {
            return $this->defineOne();
        }
    }
}
