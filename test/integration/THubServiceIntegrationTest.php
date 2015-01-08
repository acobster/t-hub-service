<?php

require_once 'test/shared/TestData.php';

/**
 * Test the server response at a high level
 */
class THubServiceIntegrationTest extends PHPUnit_Framework_TestCase {
  public function testResponseIsXml() {
    $requests = array(
      TestData::BAD_XML,
      TestData::BAD_PASSWORD_XML,
      TestData::GET_ORDERS_REQUEST_XML,
    );

    foreach( $requests as $request ) {
      $parsed = $this->getParsedResponse( $request );
      $this->assertInstanceOf( 'SimpleXMLElement', $parsed );
    }
  }

  public function testEmptyGetOrdersResponse() {
    $parsed = $this->getParsedResponse( TestData::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( 'No new orders', $parsed->Envelope->StatusMessage );
  }

  public function testGetOrdersResponse() {
    $parsed = $this->getParsedResponse( TestData::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    // $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );
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

}