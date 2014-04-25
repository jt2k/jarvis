<?php
require '../bootstrap.php';
if ($jarvis_config['enabled_adapters'] != 'all' && !in_array('web', $jarvis_config['enabled_adapters'])) {
    exit("Web adapter is disabled");
}

if (isset($jarvis_config['name']) && $jarvis_config['name']) {
    $jarvis = htmlspecialchars($jarvis_config['name']);
} else {
    $jarvis = "jarvis";
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= $jarvis ?> console</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/jarvis.css">
    <script src="assets/jquery-1.11.0.min.js"></script>
    <script src="assets/jarvis.js"></script>
</head>
<body>
    <div id="controls">
        <img src="assets/mic-off.png" id="speak"/>
        <img src="assets/speaker-off.png" id="listen"/>
    </div>

    <h1><?= $jarvis ?> console</h1>

    <div id="log_container">
        <table id="log">
        <?php for ($i=0; $i<50; $i++): ?>
        <tr><td>&nbsp;</td><td></td></tr>
        <?php endfor; ?>
        </table>
    </div>

    <div id="form">
    <label for="name" id="name_label"><a href="#">anonymous:</a></label> <input type="text" id="name" value="anonymous" />
    <input type="text" name="text" id="text" />
    <button id="submit">Send</button><br />
    </div>

    <script>
    var jarvis = '<?= $jarvis ?>';
    </script>
</body>
</html>
