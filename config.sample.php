<?php
// Set in bootstrap.php, defaults to /responders
// $jarvis_config['responders_directory'] = __DIR__ . '/responders';

$jarvis_config['enabled_responders'] = 'all';
// or, array of responders:
// $jarvis_config['enabled_responders'] = array('Hello', 'Weather');

// Optional
$jarvis_config['name'] = 'jarvis';
$jarvis_config['cache_directory'] = __DIR__ . '/cache';
$jarvis_config['enable_help'] = true;

// Configuration for Bot types
$jarvis_config['slackbot_token'] = 'SLACKBOT_TOKEN';
$jarvis_config['email_address'] = 'EMAIL_ADDRESS';
$jarvis_config['mandrill_username'] = 'MANDRILL_USERNAME';
$jarvis_config['mandrill_password'] = 'MANDRILL_PASSWORD';

// Configuration for responders
$jarvis_config['forecast.io_key'] = 'FORECAST.IO_KEY';
$jarvis_config['forecast.io_coords'] = 'FORECAST.IO_COORDS'; // ex. '36.1678,-86.7782'

$jarvis_config['twitter_consumer_key'] = 'TWITTER_CONSUMER_KEY';
$jarvis_config['twitter_consumer_secret'] = 'TWITTER_CONSUMER_SECRET';
$jarvis_config['twitter_oauth_token'] = 'TWITTER_OAUTH_TOKEN';
$jarvis_config['twitter_oauth_token_secret'] = 'TWITTER_OAUTH_TOKEN_SECRET';

$jarvis_config['wolframalpha_appid'] = 'WOLFRAMALPHA_APPID';

$jarvis_config['merriam_webster_key'] = 'MERRIAM_WEBSTER_KEY';
