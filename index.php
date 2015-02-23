<?php

date_default_timezone_set('UTC');
error_reporting( E_ERROR | E_PARSE | E_RECOVERABLE_ERROR );

require './autoload.php';
if( file_exists('./thub_config.php') ) require_once './thub_config.php';

if( $_POST ) {
  $orderProvider = new Data\OrderModel();
  $thub = new THub\THubService( $orderProvider );
  echo $thub->parseRequest( $_POST['XML'] );
} else {
  header('Allow: POST');
  header('HTTP/1.0 405 Method Not Allowed');
  echo "Please use the HTTP POST method!";
}
