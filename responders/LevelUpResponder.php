<?php
namespace jt2k\Jarvis;

class LevelUpResponder extends Responder
{
    public static $pattern = '(level|lvl)\s*(up|down)\s*(.+)';
    
    const TRAIT_MIN = 2;
    const TRAIT_MAX = 4;
    const BONUS_MIN = 1;
    const BONUS_MAX = 8;
    const TREASURE_PCT = 0.4;
    
    private static $traits = array(
        'Accident-Prone',
        'Animal Husbandry',
        'Artsy-Fartsy',
        'Automation',
        'Bandwidth',
        'Bioluminescence',
        'Bootstrapping',
        'Boozehound',
        'Breakdancing',
        'Bungee-jumping',
        'Dadaism',
        'Dentistry',
        'Dialectic',
        'Drunk-dialing',
        'Catcalling',
        'Claptrap',
        'Cuddling',
        'Cholesterol',
        'Eavesdropping',
        'Electromagnetism',
        'Eyestrain',
        'Extrasensory Perception',
        'Facetiousness',
        'Fertility',
        'Fiddling',
        'Fortunetelling',
        'Gardening',
        'Genuflection',
        'Glassblowing',
        'Grave-digging',
        'Hacking',
        'Hepcat',
        'Hindsight',
        'Ice-fishing',
        'Immune System',
        'Income',
        'Irony',
        'Jiggery-pokery',
        'Jump cut',
        'Junk DNA',
        'Keelhauling',
        'Kickflipping',
        'Kidnapping',
        'Knife Juggling',
        'Landscaping',
        'Locksmithery',
        'Luckpennies',
        'Lung Capacity',
        'Magick',
        'Manliness',
        'Mountaineering',
        'Multicasting',
        'Mumbling',
        'Name-dropping',
        'Nefarious',
        'Normal',
        'Numerology',
        'Obsessive-compulsive',
        'Occultism',
        'Off-roading',
        'Ovaries',
        'Pickpocketing',
        'Prescience',
        'Programming',
        'Proselytizing',
        'Streaming',
        'Telepathy',
        'Whining',
        'Warcrafting'
    );
    
    private static $adjectives = array(
        'adamantium',
        'antique',
        'blessed',
        'cursed',
        'Elvish',
        'enchanted',
        'gilded',
        'holy',
        'jankety',
        'jewel-encrusted',
        'magic',
        'mithril',
        'shoddy',
        'spectral',
        'uranium-enriched'
    );
    
    private static $treasure = array(
        'alethiometer',
        'Amazon delivery drone',
        'bowcaster',
        'crème brûlée',
        'Dvorak keyboard',
        'e-cigarette',
        'first edition copy of Dianetics',
        'flu vaccine',
        'French press',
        'grenade',
        'Honda Civic',
        'horcrux',
        'ion cannon',
        'iPhone',
        'jQuery plugin',
        'klaxon',
        'lightsaber',
        'Luck dragon',
        'maser',
        'nanoprobe',
        'orrery',
        'paintball gun',
        'phaser',
        'QWERTY keyboard',
        'railgun',
        'shiv',
        'smartwatch',
        'Star Trek Voyager DVD set',
        'taser',
        'USB stick',
        'vortex',
        'warp drive',
        'xylophone',
        'YOLO t-shirt',
        'zeppelin'
    );
    
    public function respond($redirect = false) {
        $direction = strtoupper($this->matches[2]);
        $hero = $this->matches[3];
        if ($direction === 'UP') {
            $lvlIcon = 'sparkles';
            $modifier = '+';
            $lvlAction = 'gained';
        }
        elseif ($direction === 'DOWN') {
            $lvlIcon = 'skull';
            $modifier = '-';
            $lvlAction = 'lost';
        }
        $r = ":{$lvlIcon}: *LEVEL {$direction}* :{$lvlIcon}: {$hero} {$lvlAction} a level!\n";
        $gained = rand(self::TRAIT_MIN, self::TRAIT_MAX);
        $dupes = array();
        for ($i = 0; $i < $gained; $i++) {
            $bonus = rand(self::BONUS_MIN, self::BONUS_MAX);
            do {
                $trait = self::$traits[rand(0, count(self::$traits) - 1)];
            } while (isset($dupes[$trait]));
            $dupes[$trait] = true;
            $r .= "> {$modifier}{$bonus} _{$trait}_\n";
        }
        // check for treasure
        if (rand(1, 10) <= 10 * self::TREASURE_PCT) {
            $adj = self::$adjectives[rand(0, count(self::$adjectives) - 1)];
            $a = preg_match('/[aeiou]/i', substr($adj, 0, 1)) === 1 ? 'an' : 'a';
            $item = self::$treasure[rand(0, count(self::$treasure) - 1)];
            if ($direction === 'UP') {
                $r .= ":gem: _TREASURE!_ {$hero} found $a ";
            }
            elseif ($direction === 'DOWN') {
                $r .= ":smiling_imp: _THIEF!_ An imp stole {$hero}'s ";
            }
            $r .= "$adj *$item*\n";
        }
        return trim($r);
    }
}