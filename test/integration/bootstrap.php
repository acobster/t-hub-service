<?php

date_default_timezone_set('UTC');

define( 'WEB_SERVER_HOST', 'localhost' );
define( 'WEB_SERVER_PORT', '1349' );
define( 'WEB_SERVER_DOCROOT', __DIR__.'/../../' );

// Command that starts the built-in web server
$command = sprintf(
    'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
    WEB_SERVER_HOST,
    WEB_SERVER_PORT,
    WEB_SERVER_DOCROOT
);

// Execute the command and store the process ID
$output = array();
exec($command, $output);
$pid = (int) $output[0];

echo sprintf(
    '%s - Web server started on %s:%d with PID %d...waiting a few seconds...',
    date('r'),
    WEB_SERVER_HOST,
    WEB_SERVER_PORT,
    $pid
) . PHP_EOL;

// wait for the server to start listening...
usleep(70000);

// Kill the web server when the process ends
register_shutdown_function(function() use ($pid) {
    echo sprintf('%s - Killing process with ID %d', date('r'), $pid) . PHP_EOL;
    exec('kill ' . $pid);
});