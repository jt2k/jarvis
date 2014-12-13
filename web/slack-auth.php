<?php
use jt2k\RestApi\RestApi;

require '../bootstrap.php';

if ($jarvis_config['enabled_adapters'] != 'all'
    && !in_array('slack', $jarvis_config['enabled_adapters'])) {
    exit("Slack adapter is disabled");
}

if (!isset($jarvis_config['slack_client_id'])
    || !isset($jarvis_config['slack_client_secret'])) {
    exit("Slack client id and/or secret are not defined in configuration\n");
}

if (isset($jarvis_config['slack_oauth_token'])) {
    exit("Slack authentication has already been completed\n");
}

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/slack-auth.php';

if (empty($_GET['code'])) {
    $state = 'start';
} else {
    $state = 'callback';
    $restapi = new RestApi();
    $result = $restapi->request('https://slack.com/api/oauth.access', array(
        'post' => array(
            'client_id' => $jarvis_config['slack_client_id'],
            'client_secret' => $jarvis_config['slack_client_secret'],
            'code' => $_GET['code'],
            'redirect_uri' => $redirect_uri
        )
    ));
}
?>
<html>
<body>
    <?php if ($state == 'start'): ?>
    <p>
        <a href="https://slack.com/oauth/authorize?client_id=<?= $jarvis_config['slack_client_id'] ?>&amp;redirect_uri=<?= urlencode($redirect_uri) ?>&amp;scope=identify,read,post,client">Authorize</a>
    </p>
    <?php else: ?>
        <?php if (is_object($result) && !empty($result->access_token)): ?>
            <p>Successuflly authorized!</p>
            <p>Access token: <input type="text" value="<?php echo htmlspecialchars($result->access_token); ?>" />
        <?php else: ?>
            <p>Error retrieving access token. Result:</p>
            <pre><?php var_dump($result); ?></pre>
            <p><a href="./slack-auth.php">Try again</a></p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>