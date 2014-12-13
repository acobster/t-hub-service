<?php

require 'autoload.php';

if( file_exists('tweb_config.php') ) {
  require 'tweb_config.php';
}

try {
  $thub = new THub\THubService();
  echo $thub->parseRequest( $_POST['XML'] );
} catch( THub\AuthException $e ) {
  header("HTTP/1.0 401 Unauthorized");
  echo "401 Unauthorized";
}
