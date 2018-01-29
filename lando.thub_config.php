<?php

define( 'DB_NAME', 'lamp' );
define( 'DB_USER', 'lamp' );
define( 'DB_PASSWORD', 'lamp' );
define( 'DB_HOST', 'database' );

THub\THubService::config([
  'viewDir' => '/app/views/',
  'user'  => 'thub_client',
  'passwordFile' => '/app/thub.passwd',
  'securityKey' => 'ASDFQWERTY',
]);

?>
