<?php

require './autoload.php';

if( file_exists('./tweb_config.php') ) {
  require './tweb_config.php';
}

try {
  if( $_POST['XML'] ) {
    // TODO implement or import OrderProvider
    $orderProvider = array();

    $thub = new THub\THubService( $orderProvider );
    echo $thub->parseRequest( $_POST['XML'] );
  } else {
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found";
  }
} catch( THub\AuthException $e ) {
  header("HTTP/1.0 401 Unauthorized");
  echo "401 Unauthorized";
} catch( THub\BadRequestException $e ) {
  http_response_code(400);
  echo "400 Bad Request";
}
