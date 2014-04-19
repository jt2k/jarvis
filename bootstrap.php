<?php
spl_autoload_register(function ($class) {
    foreach (array('/src/') as $dir) {
        $file = __DIR__ . $dir . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require $file;

            return;
        }
    }
});

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

$jarvis_config = array();
$jarvis_config['responders_directory'] = __DIR__ . '/responders';
$jarvis_config['name'] = 'jarvis';

if (file_exists(__DIR__ . '/config.php')) {
    require 'config.php';
}

if (isset($jarvis_config['cache_directory'])) {
    // quick but insecure way to make cache files writable from cli and web
    umask(0000);

    // to do it the right way, see:
    // http://symfony.com/doc/current/book/installation.html#book-installation-permissions
}