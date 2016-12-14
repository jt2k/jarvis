<?php
namespace jt2k\Jarvis;

class DroneStrikeResponder extends Responder
{
    public static $pattern = '^drone\s*strike\s+(.+)$';
    
    private static $dirs = array(
        'north',
        'northeast',
        'east',
        'southeast',
        'south',
        'southwest',
        'west',
        'northwest'
    );
    
    private static $sublocs = array(
        '{fives} clicks {dir} of',
        'just {dir} of',
        'on the outskirts of',
        'in a shantytown {dir} of',
        'an abandoned mine near',
        'downtown',
        'hocking pirated DVDs on the streets of',
        'sailing in a hot air balloon over',
        'an opium den in',
        'a tent city outside'
    );
    
    private static $locs = array(
        array('name' => 'Abu Dhabi, UAE', 'latitude' => 24.3862905, 'longitude' => 54.2797189),
        array('name' => 'Albuquerque, NM', 'latitude' => 35.0826099, 'longitude' => -106.8169076),
        array('name' => 'Black Rock City, NV', 'latitude' => 40.7859574, 'longitude' => -119.2234273),
        array('name' => 'Bogota, Colombia', 'latitude' => 4.6482837, 'longitude' => -74.247893),
        array('name' => 'Da Nang, Vietnam', 'latitude' => 16.0466742, 'longitude' => 108.206706),
        array('name' => 'Delhi, India', 'latitude' => 28.6454414, 'longitude' => 77.0907573),
        array('name' => 'Djibouti, Africa', 'latitude' => 11.8234622, 'longitude' => 42.0264945),
        array('name' => 'Easter Island', 'latitude' => -27.1258097, 'longitude' => -109.4090265),
        array('name' => 'Gainesville, FL', 'latitude' => 29.6864011, 'longitude' => -82.3899579),
        array('name' => 'Havana, Cuba', 'latitude' => 23.0509193, 'longitude' => -82.4731386),
        array('name' => 'Ho Chi Minh City, Vietnam', 'latitude' => 10.768451, 'longitude' => 106.6943626),
        array('name' => 'Kahoʻolawe, Hawaii', 'latitude' => 20.5526138, 'longitude' => -156.6865398),
        array('name' => 'Karachi, Pakistan', 'latitude' => 25.0115039, 'longitude' => 66.7838259),
        array('name' => 'Key West, FL', 'latitude' => 24.5583954, 'longitude' => -81.7978063),
        array('name' => 'Kokomo', 'latitude' => 25.0910105, 'longitude' => -77.40933),
        array('name' => 'Mauritius', 'latitude' => -20.2004971, 'longitude' => 56.5514817),
        array('name' => 'Mexico City, Mexico', 'latitude' => 19.3907336, 'longitude' => -99.1436127),
        array('name' => 'Mt. Kilimanjaro, Tanzania', 'latitude' => -3.0674246, 'longitude' => 37.3468725),
        array('name' => 'Nashville, TN', 'latitude' => 36.1866405, 'longitude' => -86.7852455),
        array('name' => 'Neuschwanstein Castle, Germany', 'latitude' => 47.557574, 'longitude' => 10.7476117),
        array('name' => 'Omaha, NE', 'latitude' => 41.2918589, 'longitude' => -96.0812485),
        array('name' => 'Panamá City, Panama', 'latitude' => 9.0831986, 'longitude' => -79.5924652),
        array('name' => 'Phuket, Thailand', 'latitude' => 7.8833605, 'longitude' => 98.3744039),
        array('name' => 'Prague, Czech Republic', 'latitude' => 50.0595854, 'longitude' => 14.3255418),
        array('name' => 'Rio de Janeiro, Brazil', 'latitude' => -22.0626323, 'longitude' => -44.044488),
        array('name' => 'Rochester, MN', 'latitude' => 43.9961486, 'longitude' => -92.6215996),
        array('name' => 'the Ross Ice Shelf, Antarctica', 'latitude' => -81.4999691, 'longitude' => -175.0021619),
        array('name' => 'Ross River, Yukon Territory', 'latitude' => 61.9717011, 'longitude' => -132.4854746),
        array('name' => 'San Juan, Puerto Rico', 'latitude' => 18.3849764, 'longitude' => -66.1285536),
        array('name' => 'Shanghai, China', 'latitude' => 31.2243489, 'longitude' => 121.4767528),
        array('name' => 'South of the Border, SC', 'latitude' => 34.497657, 'longitude' => -79.3182127),
        array('name' => 'St. Petersburg, Russia', 'latitude' => 59.9174911, 'longitude' => 30.0441967),
        array('name' => 'Tallinn, Estonia', 'latitude' => 59.4250582, 'longitude' => 24.5978164),
        array('name' => 'Tangiers, Morocco', 'latitude' => 35.7632488, 'longitude' => -5.9034189),
        array('name' => 'Tehran, Iran', 'latitude' => 35.6970114, 'longitude' => 51.2093905),
        array('name' => 'Tikrit, Iraq', 'latitude' => 34.6144649, 'longitude' => 43.5981679),
        array('name' => 'Timbuktu, Mali', 'latitude' => 16.7713828, 'longitude' => -3.025489),
        array('name' => 'Tokyo, Japan', 'latitude' => 35.673343, 'longitude' => 139.710388),
        array('name' => 'Walt Disney World, Orlando', 'latitude' => 28.3852377, 'longitude' => -81.566068),
        array('name' => 'Zanzibar, Tanzania', 'latitude' => -6.1659168, 'longitude' => 39.1938862)
    );
    
    private static function isPositive8Ball($response) {
        switch ($response) {
            case 'It is certain':
            case 'It is decidedly so':
            case 'Without a doubt':
            case 'Yes definitely':
            case 'You may rely on it':
            case 'As I see it, yes':
            case 'Most likely':
            case 'Outlook good':
            case 'Yes':
            case 'Signs point to yes':
                return true;
            default: return false;
        }
    }
    
    public function respond($redirect = false) {
        if (!$this->requireConfig(array('forecast.io_key'))) {
            return 'forecast.io_key is required.';
        }
        
        $target = $this->matches[1];
        $subloc = self::$sublocs[rand(0, count(self::$sublocs) - 1)];
        $subloc = str_replace('{fives}', rand(1, 4) * 5, $subloc);
        $subloc = str_replace('{dir}', self::$dirs[rand(0, count(self::$dirs) - 1)], $subloc);
        $loc = self::$locs[rand(0, count(self::$locs) - 1)];
        
        // get weather and time at the location
        $apikey = $this->config['forecast.io_key'];
        $weatherUrl = "https://api.forecast.io/forecast/{$apikey}/{$loc['latitude']},{$loc['longitude']}";
        $this->data = $this->request($weatherUrl, 600, 'weather'); // cache for 10 minutes
        if (!is_object($this->data) || !is_object($this->data->currently)) {
            $forecast = 'unknown';
            $time = date('g:ia');
        }
        else {
            $temp = round($this->data->currently->temperature);
            $forecast = "{$temp}°F, {$this->data->currently->summary}";
            $date = new \DateTime("now", new \DateTimeZone($this->data->timezone));
            $time = $date->format('g:ia');
        }
        
        $permission = $this->callResponder('EightBall', '8ball');
        if (self::isPositive8Ball($permission)) {
            $booms = str_repeat(':boom:', 5);
            $mission = ":airplane: ... :three: ... :two: ... :one: ... $booms\n" .
                ":smiley: We have visual on *$target's* smoldering corpse - mission accomplished!";
        }
        else {
            $mission = ':disappointed: Understood, sir. Mission aborted.';
        }
        
        return ":neutral_face: Sir, agents report a fix on high-value enemy combatant *$target*:\n" .
            "```\n" .
            "LOCATION: $subloc {$loc['name']}\n" .
            "CONDITIONS: $time, $forecast\n" .
            "```\n" .
            ":angry: Do we have permission to neutralize the target?\n" .
            ":guardsman: _{$permission}._\n$mission";
    }
}