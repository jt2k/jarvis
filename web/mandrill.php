<?php
require '../bootstrap.php';

use jt2k\Jarvis\MandrillBot;

if (isset($_POST['mandrill_events'])) {
    $jarvis = new MandrillBot($jarvis_config);
    $jarvis->respondToAll($_POST);
}
