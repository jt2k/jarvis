<?php
/*
 * NOTE: Only use this web-based OAuth workflow if you want to set up your
 * SlackRTM responder to connect as regular (non-bot) user.
 *
 * If you simply want to create a new bot user, go here:
 * https://my.slack.com/services/new/bot
 * You will be able to copy your OAuth token from there.
 */
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

$path = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
$redirect_uri = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $path;

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
            <p>Copy into config.php: <input type="text" value="<?php echo htmlspecialchars('$jarvis_config[\'slack_oauth_token\'] = \'' . $result->access_token) . '\';'; ?>" size="100"/></p>
        <?php else: ?>
            <p>Error retrieving access token. Result:</p>
            <pre><?php var_dump($result); ?></pre>
            <p><a href="./slack-auth.php">Try again</a></p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>