<?php

use PHPUnit\Framework\TestCase;

require_once realpath(__DIR__.'/../shared/CustomAssertions.php');
require_once realpath(__DIR__.'/../shared/TestData.php');
require_once realpath(__DIR__.'/OrderFixtures.php');

/**
 * Test the server response at a high level
 * @group integration
 */
class THubServiceIntegrationTest extends TestCase {
  use CustomAssertions;

  public static function setUpBeforeClass() {
    OrderFixtures::createSchema();
  }

  public function tearDown() {
    OrderFixtures::truncateAll();
  }

  public function testResponseIsXml() {
    $reflection = new ReflectionClass( 'TestData' );
    $cases = $reflection->getConstants();

    foreach( $cases as $request ) {
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
    $orders = $parsed->Orders->Order;

    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );
    $this->assertEquals( 2, $orders->count() );
    $this->assertOrder( TestData::$orders[0], $orders[0] );
    $this->assertOrder( TestData::$orders[1], $orders[1] );
  }

  public function testGetOrdersResponseByOrderStartNumber() {
    return $this->markTestSkipped();
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
    return $this->markTestSkipped();
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
    return $this->markTestSkipped();
    OrderFixtures::insertOrders( TestData::newAndOldOrders() );

    $parsed = $this->getParsedResponse(
      TestData::GET_ORDERS_REQUEST_XML_BY_ORDER_START_NUMBER );
    $orders = $parsed->Orders->Order;
    $newOrderIds = array(4, 5, 6);
    $localOrderIds = array(4122, 4123, 4124);

    foreach( $orders as $order ) {
      $this->assertContains( intval($order->OrderID), $newOrderIds );
      // make sure there's actually some state for
      // testUpdateOrdersShippingStatus to change
      $this->assertEquals( 'New', $order->Ship->ShipStatus );
      $this->assertEmpty( (string) $order->Ship->ShipDate );
      $this->assertEquals( '', $order->Ship->Tracking );
      $this->assertEmpty( (string) $order->Ship->ShipMethod );
      $this->assertEmpty( (string) $order->Ship->ShipCarrierName );
    }

    $parsed = $this->getParsedResponse(
      TestData::UPDATE_ORDERS_SHIPPING_STATUS_REQUEST_XML );
    $this->assertEquals( 'UpdateOrdersShippingStatus', $parsed->Envelope->Command );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );

    $orders = $parsed->Orders->Order;
    $this->assertEquals( 3, $orders->count() );

    // check IDs and status
    foreach( $orders as $order ) {
      $this->assertContains( intval($order->HostOrderID), $newOrderIds );
      $this->assertContains( intval($order->LocalOrderID), $localOrderIds );
      $this->assertEquals( 'Success', $order->HostStatus );
    }

    // now test the persistence...
    $parsed = $this->getParsedResponse(
      TestData::GET_ORDERS_REQUEST_XML_BY_ORDER_START_NUMBER );
    $orders = $parsed->Orders->Order;

    $i = 0;
    foreach( $orders as $order ) {
      $this->assertEquals( 'Shipped', $order->Ship->ShipStatus );
      $this->assertNotEmpty( (string) $order->Ship->ShipDate );
      $this->assertNotEmpty( (string) $order->Ship->Tracking );
      $this->assertEquals( TestData::UPDATED_SHIPPING_METHODS[$i], $order->Ship->ShipMethod );
      $this->assertEquals( TestData::UPDATED_SHIPPING_CARRIERS[$i], $order->Ship->ShipCarrierName );

      $i++;
    }
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
    $handle = curl_init( SERVICE_URL );

    $body = http_build_query(['request' => $requestXml]);

    $options = array(
      CURLOPT_HEADER => 0,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => $body,
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
