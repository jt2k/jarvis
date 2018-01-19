<?php
// Set in bootstrap.php, defaults to /responders
// $jarvis_config['responders_directory'] = __DIR__ . '/responders';

// Or, specify an array of directories:
// $jarvis_config['responders_directory'] = array(
//     __DIR__ . '/responders',
//     '/home/jarvisresponders'
// );

$jarvis_config['enabled_responders'] = 'all';
// or, array of responders:
// $jarvis_config['enabled_responders'] = array('Hello', 'Weather');

$jarvis_config['enabled_adapters'] = 'all';
// or, array of adapters:
// $jarvis_config['enabled_adapters'] = array('console', 'web', 'slack', 'irc', 'email', 'sms');


/*
 * Optional configuration options
 */
$jarvis_config['name'] = 'jarvis';
$jarvis_config['cache_directory'] = __DIR__ . '/cache';
$jarvis_config['enable_help'] = true;
$jarvis_config['location'] = array(LATITUDE, LONGITUDE);

// Configure persistent storage
$jarvis_config['database'] = array(
    'engine' => 'mysql',
    'host' => '',
    'schema' => '',
    'user' => '',
    'password' => ''
);
/*
 * Configuration for adapters
 */
if ($jarvis_config['enabled_adapters'] == 'all' || in_array('slack', $jarvis_config['enabled_adapters'])) {
	$jarvis_config['slackbot_token'] = 'SLACKBOT_TOKEN';
}

if ($jarvis_config['enabled_adapters'] == 'all' || in_array('email', $jarvis_config['enabled_adapters'])) {
	$jarvis_config['email_address'] = 'EMAIL_ADDRESS';
	$jarvis_config['mandrill_username'] = 'MANDRILL_USERNAME';
	$jarvis_config['mandrill_password'] = 'MANDRILL_PASSWORD';
}

if ($jarvis_config['enabled_adapters'] == 'all' || in_array('irc', $jarvis_config['enabled_adapters'])) {
	$jarvis_config['irc_hostname'] = 'IRC_HOSTNAME';
	$jarvis_config['irc_port'] = 6667;
	$jarvis_config['irc_channels'] = array('#example');
	// $jarvis_config['irc_password'] = 'PASSWORD'; // server password
}


/*
 * Configuration for responders
 */
$jarvis_config['forecast.io_key'] = 'FORECAST.IO_KEY';
$jarvis_config['wunderground_key'] = 'WUNDERGROUND_KEY';
$jarvis_config['bingmaps_key'] = 'BINGMAPS_KEY';

$jarvis_config['twitter_consumer_key'] = 'TWITTER_CONSUMER_KEY';
$jarvis_config['twitter_consumer_secret'] = 'TWITTER_CONSUMER_SECRET';
$jarvis_config['twitter_oauth_token'] = 'TWITTER_OAUTH_TOKEN';
$jarvis_config['twitter_oauth_token_secret'] = 'TWITTER_OAUTH_TOKEN_SECRET';

$jarvis_config['wolframalpha_appid'] = 'WOLFRAMALPHA_APPID';

$jarvis_config['merriam_webster_key'] = 'MERRIAM_WEBSTER_KEY';

$jarvis_config['github_access_token'] = 'GITHUB_ACCESS_TOKEN';
$jarvis_config['github_username'] = 'GITHUB_USERNAME';

$jarvis_config['riverflood_default_gauge'] = 'RIVERFLOOD_DEFAULT_GAUGE'; // e.g. 'omot1' - see http://water.weather.gov/ahps/

$jarvis_config['google_key'] = 'GOOGLE_KEY';
