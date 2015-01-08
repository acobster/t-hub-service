<?php

date_default_timezone_set('UTC');
error_reporting( E_ERROR | E_PARSE | E_RECOVERABLE_ERROR );

require './autoload.php';
if( file_exists('./tweb_config.php') ) require_once './tweb_config.php';

if( $_POST ) {
  $orderProvider = new Data\OrderModel();
  $thub = new THub\THubService( $orderProvider );
  echo $thub->parseRequest( $_POST['XML'] );
} else {
  header('Allow: POST');
  http_response_code(405);
  echo "Please use the HTTP POST method!";
}
