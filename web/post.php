<?php
require '../bootstrap.php';

use jt2k\Jarvis\Bot;

if (isset($_POST) && isset($_POST['text'])) {
    $bot = new Bot($jarvis_config);
    echo $bot->respond(array('text' => $_POST['text'], 'user_name' => $_POST['name']));
}