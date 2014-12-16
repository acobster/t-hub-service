<?php

error_reporting( E_ERROR | E_PARSE );

require './autoload.php';
if( file_exists('./tweb_config.php') ) require './tweb_config.php';

if( !empty($_POST) ) {
  // TODO implement or import OrderProvider
  $orderProvider = array();

  $thub = new THub\THubService( $orderProvider );
  echo $thub->parseRequest( $_POST['XML'] );
} else {
  header('Allow: POST');
  http_response_code(405);
  echo "Please use the HTTP POST method!";
}
