<?php
require '../bootstrap.php';

use jt2k\Jarvis\Bot;

if (isset($_POST) && isset($_POST['text'])) {
    $bot = new Bot($jarvis_config);
    $response = $bot->respond(array('text' => $_POST['text'], 'user_name' => 'Test Console'));
}

if (isset($jarvis_config['name']) && $jarvis_config['name']) {
    $title = htmlspecialchars($jarvis_config['name']) . ' test console';
} else {
    $title = "jarvis test console";
}
?>
<html>
<head>
    <title><?= $title ?></title>
    <style>
    pre {margin: 5px 0 15px 0}
    </style>
</head>
<body>
    <h1><?= $title ?></h1>

    <form method="post" action="">
        <input type="text" name="text" id="text" value="<?php echo isset($_POST['text'])?htmlspecialchars($_POST['text']):''; ?>" /> <input type="submit" />
    </form>

    <?php if (isset($_POST) && isset($_POST['text'])): ?>
        Input:
        <pre><strong><?php echo htmlspecialchars($_POST['text']); ?></strong></pre>
        Response:
        <pre><strong><?php echo htmlspecialchars($response); ?></strong></pre>
    <?php endif; ?>

    <script>
    document.getElementById('text').select();
    </script>
</body>
</html>
