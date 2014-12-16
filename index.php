<?php

require './autoload.php';
if( file_exists('./tweb_config.php') ) require './tweb_config.php';

try {
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
} catch( THub\AuthException $e ) {
  http_response_code(401);
  echo "401 Unauthorized";
} catch( THub\BadRequestException $e ) {
  http_response_code(400);
  echo "400 Bad Request: " . $e->getMessage();
}
