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
     */
    public static $pattern = '(^(level|lvl)\s*(up|(down|dn))\s*(.+))|((.+)\s*(\+\+|\-\-)$)';
    
    const TRAIT_MIN = 2;
    const TRAIT_MAX = 4;
    const BONUS_MIN = 1;
    const BONUS_MAX = 8;
    const TREASURE_PCT = 0.4;
    
    private static $traits = array(
        'Accident-Prone',
        'Alchemy',
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
        'Daytripping',
        'Dentistry',
        'Drunk-dialing',
        'Catcalling',
        'Cat-whispering',
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
        'Genetic Engineering',
        'Genuflection',
        'Glassblowing',
        'Grandstanding',
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
        'Mind control',
        'Moonwalking',
        'Mountaineering',
        'Multicasting',
        'Mumbling',
        'Name-dropping',
        'Necromancy',
        'Nefarious',
        'Nitpicking',
        'Numerology',
        'Obsessive-compulsive',
        'Occultism',
        'Off-roading',
        'Ovaries',
        'Panhandling',
        'Pickpocketing',
        'Pokerface',
        'Prescience',
        'Programming',
        'Proselytizing',
        'Racketeering',
        'Robotic dance moves',
        'Sanity',
        'Small talk',
        'Sorcery',
        'Stargazing',
        'Streaming',
        'Streetwise',
        'Telepathy',
        'Tomfoolery',
        'Trollery',
        'Virulence',
        'Whining',
        'Warcrafting'
    );
    
    private static $adjectives = array(
        'adamantium',
        'antique',
        'blessed',
        'cantilevered',
        'cursed',
        'Elvish',
        'enchanted',
        'fetid',
        'gilded',
        'holy',
        'jankety',
        'jewel-encrusted',
        'magic',
        'mithril',
        'off-brand',
        'rusty',
        'shoddy',
        'spectral',
        'uranium-enriched'
    );
    
    private static $treasure = array(
        'alethiometer',
        'alien life form',
        'Amazon delivery drone',
        'bone saw',
        'bowcaster',
        'crème brûlée',
        'Dvorak keyboard',
        'e-cigarette',
        'first edition copy of Dianetics',
        'flu vaccine',
        'Foley catheter',
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
        'nugget',
        'orrery',
        'paintball gun',
        'phaser',
        'pickleball paddle',
        'QWERTY keyboard',
        'railgun',
        'shiv',
        'smartwatch',
        'Star Trek Voyager DVD set',
        'taser',
        'token',
        'USB stick',
        'vortex',
        'warp drive',
        'xylophone',
        'YOLO t-shirt',
        'zeppelin'
    );
    
    public function respond($redirect = false) {
        if (isset($this->matches[8]) && $this->matches[8] === '++') {
            $direction = 'UP';
            $hero = $this->matches[7];
        }
        elseif (isset($this->matches[8]) && $this->matches[8] === '--') {
            $direction = 'DOWN';
            $hero = $this->matches[7];
        }
        else {
            $direction = strtoupper($this->matches[3]);
            if ($direction === 'DN') $direction = 'DOWN';
            $hero = $this->matches[5];
        }
        $hero = trim($hero);
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

        if ($this->hasStorage()) {
            $level = (int)$this->getStorage($hero);
            if ($direction === 'UP') {
                $level++;
            } elseif ($direction === 'DOWN') {
                $level--;
            }
            $level = max(0, $level);
            $this->setStorage($hero, $level);
            $r .= "{$hero} is now at level {$level}";
        }
        return trim($r);
    }
}