<?php

class THubIntegrationTest extends PHPUnit_Framework_TestCase {
  public function testBadXml() {
    $response = $this->postTHub( 'foo' );
    $this->assertRegExp( '/400 Bad Request/', $response );
  }

  public function testNormalResponse() {
    $this->markTestSkipped();
    $response = $this->postTHub( self::NORMAL_REQUEST_XML );
    $this->assertInternalType( 'string', $response );
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

  const NORMAL_REQUEST_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>myloginId</UserID>
   <Password>myPassword</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;
}