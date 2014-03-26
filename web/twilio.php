<?php
require '../bootstrap.php';

use jt2k\Jarvis\TwilioBot;

if (isset($_POST)) {
    $jarvis = new TwilioBot($jarvis_config);
    $jarvis->respond($_POST);
}
