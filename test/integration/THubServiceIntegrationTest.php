<?php

/**
 * Test the server response at a high level
 */
class THubServiceIntegrationTest extends PHPUnit_Framework_TestCase {
  public function testResponseIsXml() {
    $requests = array(
      self::BAD_XML,
      self::BAD_PASSWORD_XML,
      // self::NORMAL_REQUEST_XML,
    );

    foreach( $requests as $request ) {
      $parsed = $this->getParsedResponse( $request );
      $this->assertInstanceOf( 'SimpleXMLElement', $parsed );
    }
  }

  protected function getParsedResponse( $request ) {
    try {
      $response = $this->postTHub($request);
      return new SimpleXMLElement( $response );
    } catch( Exception $e ) {
      $this->fail( "couldn't parse response:" . PHP_EOL
        . $response . PHP_EOL );
    }
  }

  protected function postTHub( $requestXml ) {
    $uri = 'http://' . WEB_SERVER_HOST . ':' . WEB_SERVER_PORT;
    $handle = curl_init( $uri );

    $options = array(
      CURLOPT_HEADER => 0,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => array( 'XML' => $requestXml ),
      CURLOPT_RETURNTRANSFER => true,
    );

    curl_setopt_array( $handle, $options );
    $out = curl_exec( $handle );

    if( $out === false ) {
      $error = curl_error( $handle );
      $this->fail( $error );
    }

    curl_close( $handle );
    return $out;
  }

  const BAD_XML = 'foo';

  const NORMAL_REQUEST_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const BAD_PASSWORD_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>BAD</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

}