<?php
require '../bootstrap.php';

use jt2k\Jarvis\SlackBot;

if (isset($_POST)) {
    $slackbot = new SlackBot($jarvis_config);
    $response = $slackbot->respond($_POST);
}
