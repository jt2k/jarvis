<?php
require '../bootstrap.php';

$proxy = new \jt2k\Jarvis\Proxy($jarvis_config);
$proxy->request($_GET);
$proxy->output();
