<?php
namespace jt2k\Jarvis;

class LevelUpResponder extends Responder
{
    /**
     * Examples:
     * level up foo
     * lvl up foo
     * lvlup foo
     * foo++
     * level status foo
     * lvl status foo
     * lvlstatus foo
     */
    public static $pattern = '(^(level|lvl)\s*(up|(down|dn)|status)\s*(.+))|((.+)\s*(\+\+|\-\-)$)';

    const TRAIT_MIN = 2;
    const TRAIT_MAX = 4;
    const BONUS_MIN = 1;
    const BONUS_MAX = 8;
    const TREASURE_PCT = 0.4;
    const CHUNK_LEN = 255;

    private $hero = array(
        'level' => 0,
        'traits' => array(),
        'items' => array()
    );

    private $cache = array(
        'traits' => array(),
        'item' => ''
    );

    private static $traits = array(
        0 => 'Accident-Prone',
        1 => 'Alchemy',
        2 => 'Animal Husbandry',
        3 => 'Artsy-Fartsy',
        4 => 'Automation',
        5 => 'Bandwidth',
        6 => 'Bioluminescence',
        7 => 'Bootstrapping',
        8 => 'Boozehound',
        9 => 'Breakdancing',
        10 => 'Bungee-jumping',
        11 => 'Dadaism',
        12 => 'Daytripping',
        13 => 'Dentistry',
        14 => 'Drunk-dialing',
        15 => 'Catcalling',
        16 => 'Cat-whispering',
        17 => 'Claptrap',
        18 => 'Cuddling',
        19 => 'Cholesterol',
        20 => 'Eavesdropping',
        21 => 'Electromagnetism',
        22 => 'Eyestrain',
        23 => 'Extrasensory Perception',
        24 => 'Facetiousness',
        25 => 'Fertility',
        26 => 'Fiddling',
        27 => 'Fortunetelling',
        28 => 'Gardening',
        29 => 'Genetic Engineering',
        30 => 'Genuflection',
        31 => 'Glassblowing',
        32 => 'Grandstanding',
        33 => 'Grave-digging',
        34 => 'Hacking',
        35 => 'Hepcat',
        36 => 'Hindsight',
        37 => 'Ice-fishing',
        38 => 'Immune System',
        39 => 'Income',
        40 => 'Irony',
        41 => 'Jiggery-pokery',
        42 => 'Jump cut',
        43 => 'Junk DNA',
        44 => 'Keelhauling',
        45 => 'Kickflipping',
        46 => 'Kidnapping',
        47 => 'Knife Juggling',
        48 => 'Landscaping',
        49 => 'Locksmithery',
        50 => 'Luckpennies',
        51 => 'Lung Capacity',
        52 => 'Magick',
        53 => 'Manliness',
        54 => 'Mind control',
        55 => 'Moonwalking',
        56 => 'Mountaineering',
        57 => 'Multicasting',
        58 => 'Mumbling',
        59 => 'Name-dropping',
        60 => 'Necromancy',
        61 => 'Nefarious',
        62 => 'Nitpicking',
        63 => 'Numerology',
        64 => 'Obsessive-compulsive',
        65 => 'Occultism',
        66 => 'Off-roading',
        67 => 'Ovaries',
        68 => 'Panhandling',
        69 => 'Pickpocketing',
        70 => 'Pokerface',
        71 => 'Prescience',
        72 => 'Programming',
        73 => 'Proselytizing',
        74 => 'Racketeering',
        75 => 'Robotic dance moves',
        76 => 'Sanity',
        77 => 'Small talk',
        78 => 'Sorcery',
        79 => 'Stargazing',
        80 => 'Streaming',
        81 => 'Streetwise',
        82 => 'Telepathy',
        83 => 'Tomfoolery',
        84 => 'Trollery',
        85 => 'Virulence',
        86 => 'Whining',
        87 => 'Warcrafting'
    );

    private static $adjectives = array(
        0 => 'adamantium',
        1 => 'antique',
        2 => 'blessed',
        3 => 'cantilevered',
        4 => 'cursed',
        5 => 'Elvish',
        6 => 'enchanted',
        7 => 'fetid',
        8 => 'gilded',
        9 => 'holy',
        10 => 'jankety',
        11 => 'jewel-encrusted',
        12 => 'magic',
        13 => 'mithril',
        14 => 'off-brand',
        15 => 'rusty',
        16 => 'shoddy',
        17 => 'spectral',
        18 => 'uranium-enriched'
    );

    private static $treasure = array(
        0 => 'alethiometer',
        1 => 'alien life form',
        2 => 'Amazon delivery drone',
        3 => 'bone saw',
        4 => 'bowcaster',
        5 => 'crème brûlée',
        6 => 'Dvorak keyboard',
        7 => 'e-cigarette',
        8 => 'first edition copy of Dianetics',
        9 => 'flu vaccine',
        10 => 'Foley catheter',
        11 => 'French press',
        12 => 'grenade',
        13 => 'Honda Civic',
        14 => 'horcrux',
        15 => 'ion cannon',
        16 => 'iPhone',
        17 => 'jQuery plugin',
        18 => 'klaxon',
        19 => 'lightsaber',
        20 => 'Luck dragon',
        21 => 'maser',
        22 => 'nanoprobe',
        23 => 'nugget',
        24 => 'orrery',
        25 => 'paintball gun',
        26 => 'phaser',
        27 => 'pickleball paddle',
        28 => 'QWERTY keyboard',
        29 => 'railgun',
        30 => 'shiv',
        31 => 'smartwatch',
        32 => 'Star Trek Voyager DVD set',
        33 => 'taser',
        34 => 'token',
        35 => 'USB stick',
        36 => 'vortex',
        37 => 'warp drive',
        38 => 'xylophone',
        39 => 'YOLO t-shirt',
        40 => 'zeppelin'
    );

    public function respond($redirect = false) {
        if (isset($this->matches[8]) && $this->matches[8] === '++') {
            $direction = 'UP';
            $name = $this->matches[7];
        }
        elseif (isset($this->matches[8]) && $this->matches[8] === '--') {
            $direction = 'DOWN';
            $name = $this->matches[7];
        }
        else {
            $direction = strtoupper($this->matches[3]);
            if ($direction === 'DN') $direction = 'DOWN';
            $name = $this->matches[5];
        }
        $name = trim($name);
        $this->migrateHero($name);
        $this->loadHero($name);

        if ($direction === 'UP') {
            $lvlIcon = 'sparkles';
            $modifier = '+';
            $lvlAction = 'gained';            
            $this->hero['level'] += 1;            
        }
        elseif ($direction === 'DOWN') {
            $lvlIcon = 'skull';
            $modifier = '-';
            $lvlAction = 'lost';
            $this->hero['level'] -= 1;            
        }
        elseif ($direction === 'STATUS') {
            return $this->characterSheet($name);
        }
        $r = ":{$lvlIcon}: *LEVEL {$direction}* :{$lvlIcon}: {$name} {$lvlAction} a level!\n";

        $this->makeTraits($direction);
        $this->makeItem($direction);
        $this->saveHero($name);
        
        if (count($this->cache['traits'])) {
            foreach ($this->cache['traits'] as $tdx => $bonus) {
                $trait = self::$traits[$tdx];
                $r .= "> {$modifier}{$bonus} _{$trait}_\n";
            }
        }

        if (strlen($this->cache['item'])) {
            $parts = explode(',', $this->cache['item']);
            $adj = self::$adjectives[$parts[0]];
            $treasure = self::$treasure[$parts[1]];
            $a = preg_match('/[aeiou]/i', substr($adj, 0, 1)) === 1 ? 'an' : 'a';
            if ($direction === 'UP') {
                $r .= ":gem: _TREASURE!_ {$name} found $a ";
            }
            elseif ($direction === 'DOWN') {
                $r .= ":smiling_imp: _THIEF!_ An imp stole {$name}'s ";
            }
            $r .= "$adj *$treasure*\n";
        }

        if ($this->hasStorage()) {
            $r .= "{$name} is now at level {$this->hero['level']}";
        }
        return trim($r);
    }

    private function characterSheet($name) {
        $r = "{$name} is level {$this->hero['level']}\nAttributes:\n";
        
        if (count($this->hero['traits'])) {
            $traits = array();
            foreach ($this->hero['traits'] as $tdx => $bonus) {
                $trait = self::$traits[$tdx];
                $traits[$trait] = $bonus;
            }
            ksort($traits);
            foreach ($traits as $trait => $bonus) {
                if ($bonus >= 0) {
                    $bonus = "+$bonus";
                }
                $r .= "> $bonus _{$trait}_\n";           
            }
        } else {
            $r .= "*none*\n";
        }
            
        $r .= "Items:\n";
        if (count($this->hero['items'])) {
            foreach ($this->hero['items'] as $item) {
                $parts = explode(',', $item);
                $adj = self::$adjectives[$parts[0]];
                $treasure = self::$treasure[$parts[1]];
                $r .= "> $adj *$treasure*\n";
            }
        } else {
            $r .= "*none*";
        }
        
        return $r;
    }

    private function makeTraits($direction) {
        $gained = rand(self::TRAIT_MIN, self::TRAIT_MAX);
        $dupes = array();
        for ($i = 0; $i < $gained; $i++) {
            $bonus = rand(self::BONUS_MIN, self::BONUS_MAX);
            if ($direction === 'DOWN') {
                $bonus *= -1;
            }
            do {
                $tdx = rand(0, count(self::$traits) - 1);             
            } while (isset($dupes[$tdx]));
            $dupes[$tdx] = true;  
            if(isset($this->hero['traits'][$tdx])) {
                $this->hero['traits'][$tdx] += $bonus;
            } else {
                $this->hero['traits'][$tdx] = $bonus;
            }
            // Modifier will be applied when printing
            $this->cache['traits'][$tdx] = abs($bonus);
        }
    }

    private function makeItem($direction) {
        if (rand(1, 10) <= 10 * self::TREASURE_PCT) {
            $adx = rand(0, count(self::$adjectives) - 1);
            $tdx = rand(0, count(self::$treasure) - 1);
            $item = "$adx,$tdx";
            if ($direction === 'UP') {
                $this->hero['items'][] = $item;
                $this->cache['item'] = $item;
            }
            elseif ($direction === 'DOWN') {   
                if($this->hasStorage()) {
                    // Steal existing item if hero has one
                    if (count($this->hero['items'])) {
                        $idx = rand(0, count($this->hero['items']) - 1);
                        $this->cache['item'] = $this->hero['items'][$idx];
                        array_splice($this->hero['items'],$idx,1);        
                    }
                } else {
                    $this->cache['item'] = $item;
                }
            }
        }
    }

    private function saveHero($name) {
        if (!$this->hasStorage()) {
            return;
        }

        $this->hero['level'] = max(0, $this->hero['level']);
        $json = json_encode($this->hero, true);
        // Storage can hold N characters. Split JSON into storable chunks
        $chunks = array_filter(explode('|', chunk_split($json, self::CHUNK_LEN, '|')));
        foreach($chunks as $i => $chunk) {
            $this->setStorage("{$name}_chunk_{$i}", $chunk);
        }
        $this->setStorage("{$name}_chunks", count($chunks));
    }

    private function loadHero($name) {
        if (!$this->hasStorage()) {
            return;
        }

        $count = (int)$this->getStorage("{$name}_chunks");
        if ($count > 0) {
            $json = '';
            for ($i = 0; $i < $count; $i++) {
                $json .= $this->getStorage("{$name}_chunk_{$i}");
            }
            $this->hero = array_merge($this->hero, json_decode($json, true));
        }
    }

    private function migrateHero($name) {
        if (!$this->hasStorage()) {
            return;
        }
        $chunks = $this->getStorage("{$name}_chunks");
        if (is_numeric($chunks)) {
            return;
        }
        // Old version only stored hero level
        $level = (int)$this->getStorage($name);
        $this->hero['level'] = $level;
        for ($i = 0; $i < $level; $i++) {
            $this->makeTraits('UP');
            $this->makeItem('UP');
        }
        $this->cache = array(
            'traits' => array(),
            'item' => ''
        );
        $this->saveHero($name);
    }
}