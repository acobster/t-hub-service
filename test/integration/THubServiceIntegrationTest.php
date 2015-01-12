<?php

require_once 'test/shared/CustomAssertions.php';
require_once 'test/shared/TestData.php';
require_once 'test/integration/OrderFixtures.php';

/**
 * Test the server response at a high level
 */
class THubServiceIntegrationTest extends PHPUnit_Framework_TestCase {
  use CustomAssertions;

  public function tearDown() {
    OrderFixtures::truncateAll();
  }

  public function testResponseIsXml() {
    $this->markTestSkipped();
    foreach( TestData::$ALL_CASES as $request ) {
      $parsed = $this->getParsedResponse( $request );
      $this->assertInstanceOf( 'SimpleXMLElement', $parsed );
    }
  }

  public function testEmptyGetOrdersResponse() {
    $parsed = $this->getParsedResponse( TestData::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( 'No new orders', $parsed->Envelope->StatusMessage );
    $this->assertEmpty( $parsed->Order );
  }

  public function testGetOrdersResponse() {
    OrderFixtures::insertOrders( TestData::$orders );
    $parsed = $this->getParsedResponse( TestData::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );

    $orders = $parsed->Orders->Order;

    $this->assertEquals( 2, $orders->count() );
    $this->assertOrder( TestData::$orders[0], $orders[0] );
    $this->assertOrder( TestData::$orders[1], $orders[1] );
  }

  public function testGetOrdersResponseByOrderStartNumber() {
    OrderFixtures::insertOrders( TestData::newAndOldOrders() );
    $parsed = $this->getParsedResponse(
      TestData::GET_ORDERS_REQUEST_XML_BY_ORDER_START_NUMBER );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );

    $orders = $parsed->Orders->Order;

    $this->assertEquals( 3, $orders->count() );
    foreach( $orders as $order ) {
      $this->assertGreaterThanOrEqual( 3, $order->OrderID );
    }
  }

  public function testGetOrdersResponseByNumDays() {
    OrderFixtures::insertOrders( TestData::newAndOldOrders() );
    $parsed = $this->getParsedResponse(
      TestData::GET_ORDERS_REQUEST_XML_BY_NUM_DAYS );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );

    $orders = $parsed->Orders->Order;

    $this->assertEquals( 3, $orders->count() );
    $nDaysAgo = new DateTime();
    $nDaysAgo->sub( new DateInterval('P5D') );
    foreach( $orders = $parsed->Orders->Order as $order ) {
      $orderDate = new DateTime( $order->Date );
      $this->assertGreaterThanOrEqual( $nDaysAgo, $orderDate );
    }
  }

  public function testUpdateOrdersShippingStatus() {
    OrderFixtures::insertOrders( TestData::newAndOldOrders() );
    $parsed = $this->getParsedResponse(
      TestData::UPDATE_ORDERS_SHIPPING_STATUS_REQUEST_XML );
    $this->assertEquals( 'UpdateOrdersShippingStatus', $parsed->Envelope->Command );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );

    $orders = $parsed->Orders->Order;
    $this->assertEquals( 3, $orders->count() );

    // check IDs and status
    foreach( $orders as $order ) {
      $this->assertContains( intval($order->HostOrderID), array(4, 5, 6) );
      $this->assertEquals( 'Success', $order->HostStatus );
    }

    // now test the persistence...
  }

  protected function getParsedResponse( $request ) {
    try {
      $response = $this->postTHub($request);
      return new SimpleXMLElement( $response );
    } catch( Exception $e ) {
      if( $response ) {
        $this->fail( "couldn't parse response:" . PHP_EOL
          . $response . PHP_EOL );
      } else {
        $this->fail( "Response is empty" );
      }
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