<?php
namespace jt2k\Jarvis;

class LevelUpResponder extends Responder
{
    public static $pattern = '(level|lvl)\s*up\s*(.+)';
    
    const TRAIT_MIN = 2;
    const TRAIT_MAX = 4;
    const BONUS_MIN = 1;
    const BONUS_MAX = 8;
    
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
    
    public function respond($redirect = false) {
        $hero = $this->matches[2];
        $r = ":sparkles: *LEVEL UP* :sparkles: {$hero} gained a level!\n";
        $gained = rand(self::TRAIT_MIN, self::TRAIT_MAX);
        for ($i = 0; $i < $gained; $i++) {
            $bonus = rand(self::BONUS_MIN, self::BONUS_MAX);
            $trait = self::$traits[rand(0, count(self::$traits) - 1)];
            $r .= "> +{$bonus} _{$trait}_\n";
        }
        return $r;
    }
}