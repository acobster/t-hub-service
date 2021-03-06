<?php

use PHPUnit\Framework\TestCase;

require_once realpath(__DIR__.'/../shared/CustomAssertions.php');
require_once realpath(__DIR__.'/../../lib/THub/AuthError.php');
require_once realpath(__DIR__.'/../../lib/THub/InvalidParamError.php');
require_once realpath(__DIR__.'/../../lib/THub/THubService.php');
require_once realpath(__DIR__.'/../shared/TestData.php');

/**
 * Test all the things
 * @group unit
 */
class THubServiceTest extends TestCase {
  use CustomAssertions;

  public function setUp() {
    // TODO mock the filesystem instead of hard-coding lando passwd file
    $this->mockProvider = $this->getMockBuilder('Data\OrderProvider')
      ->setMockClassName( 'OrderModel' )
      ->setMethods( array('getNewOrders', 'updateOrders') )
      ->getMock();
    $this->thub = new THub\THubService( $this->mockProvider );
  }

  public function testConfig() {
    $originalConfig = THub\THubService::config();

    $newConfig = array(
      'viewDir'         => './some/other/dir/',
      'user'            => 'abneorgw',
      'securityKey'     => 'aosigh',
      'requireKey'      => false,
      'passwordFile'    => '/my/passwd/file',
    );

    // Test it returns correctly the first time
    $this->assertEquals( $newConfig, THub\THubService::config( $newConfig ) );
    // Test that static property is actually persisted
    $this->assertEquals( $newConfig, THub\THubService::config() );

    // restore original settings...
    THub\THubService::config( $originalConfig );
  }

  public function testParseRequestWithBadXml() {
    $parsed = $this->getParsedResponse( TestData::BAD_XML );
    $this->assertEquals( 'UNKNOWN', $parsed->Envelope->Command );
    $this->assertEquals( '9999', $parsed->Envelope->StatusCode );
    $this->assertEquals( 'String could not be parsed as XML',
      $parsed->Envelope->StatusMessage );
  }

  public function testValidation() {
    $parsed = $this->getParsedResponse( TestData::BAD_COMMAND_XML );
    $this->assertEquals( 'No such command: FOO', $parsed->Envelope->StatusMessage );

    $cases = array(
      TestData::BAD_START_DATE_XML      => 'Invalid DownloadStartDate',
      TestData::BAD_NUM_DAYS            => 'Invalid NumberOfDays',
      TestData::BAD_LIMIT_ORDER_COUNT   => 'Invalid LimitOrderCount',
      TestData::BAD_ORDER_START_NUMBER  => 'Invalid OrderStartNumber',
      TestData::BAD_SHIPPED_ON_XML      => 'Invalid ShippedOn',
    );

    foreach( $cases as $xml => $message) {
      $parsed = $this->getParsedResponse( $xml );
      $this->assertEquals( '9999', $parsed->Envelope->StatusCode );
      $this->assertEquals( $message, $parsed->Envelope->StatusMessage );
    }
  }

  public function testAuthenticate() {
    $creds = array('thub_client', 'thub', 'ASDFQWERTY');
    $this->assertTrue( $this->callProtectedMethod('authenticate', $creds) );

    $cases = array(
      TestData::MISSING_USER_XML,
      TestData::MISSING_PASSWORD_XML,
      TestData::BAD_PASSWORD_XML,
      TestData::BAD_USER_XML,
      TestData::BAD_SECURITY_KEY_XML,
    );

    foreach( $cases as $xml ) {
      $parsed = $this->getParsedResponse( $xml );
      $this->assertInstanceOf( 'SimpleXMLElement', $parsed );
      $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
      $this->assertEquals( 'Login failed', $parsed->Envelope->StatusMessage );
      $this->assertEquals( '9000', $parsed->Envelope->StatusCode );
    }
  }

  public function testGetOrdersXmlWithNoNewOrders() {
    $this->mockProvider->method('getNewOrders')
      ->willReturn( array() );

    $parsed = $this->getParsedResponse( TestData::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( '1000', $parsed->Envelope->StatusCode );
    $this->assertEquals( 'No new orders', $parsed->Envelope->StatusMessage );
    $this->assertEquals( 'GENERIC', $parsed->Envelope->Provider );
    $this->assertEmpty( $parsed->Orders );
  }

  public function testGetOrdersXmlWithArray() {
    $this->mockProvider
      ->method('getNewOrders')
      ->willReturn( TestData::$orders );

    $parsed = $this->getParsedResponse( TestData::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( '0', $parsed->Envelope->StatusCode );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );
    $this->assertEquals( 'GENERIC', $parsed->Envelope->Provider );
    $this->assertEquals( 2, $parsed->Orders->children()->count() );

    $orders = $parsed->Orders->Order;

    $this->assertOrder( TestData::$orders[0], $orders[0] );
    $this->assertOrder( TestData::$orders[1], $orders[1] );
  }

  public function testUpdateOrdersShippingStatusWithNoOrders() {
    $cases = array(
      TestData::UPDATE_ORDERS_SHIPPING_STATUS_NO_ORDER_CHILDREN_REQUEST_XML,
      TestData::UPDATE_ORDERS_SHIPPING_STATUS_NO_ORDERS_REQUEST_XML,
    );

    foreach( $cases as $case ) {
      $parsed = $this->getParsedResponse( $case );
      $this->assertEquals( 'UpdateOrdersShippingStatus', $parsed->Envelope->Command );
      $this->assertEquals( '9999', $parsed->Envelope->StatusCode );
      $this->assertEquals( 'No orders were specificed in the update', $parsed->Envelope->StatusMessage );
    }
  }

  public function testUpdateOrdersShippingStatus() {
    $this->mockProvider->method('updateOrders')
      ->willReturn( TestData::$updatedOrders );

    $parsed = $this->getParsedResponse(
      TestData::UPDATE_ORDERS_SHIPPING_STATUS_REQUEST_XML );
    $this->assertEquals( 'UpdateOrdersShippingStatus',
      $parsed->Envelope->Command );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );
  }

  public function testGetQueryOptions() {
    $simple = new SimpleXMLElement( TestData::GET_ORDERS_REQUEST_XML );

    $this->assertEquals(array(
      'limit'    => 25,
      'start_id' => 0,
      'num_days' => 0,
    ), $this->callProtectedMethod('getQueryOptions', array($simple)));
  }

  public function testGetQueryOptionsWithNumDays() {
    $simple = new SimpleXMLElement( TestData::GET_ORDERS_REQUEST_XML_BY_NUM_DAYS );

    $this->assertEquals(array(
      'limit'    => 25,
      'start_id' => 0,
      'num_days' => 5,
    ), $this->callProtectedMethod('getQueryOptions', array($simple)));
  }

  public function testGetQueryOptionsWithOrderStartNumber() {
    $simple = new SimpleXMLElement( TestData::GET_ORDERS_REQUEST_XML_BY_ORDER_START_NUMBER );

    $this->assertEquals(array(
      'limit'    => 25,
      'start_id' => 'W3',
      'num_days' => 0,
    ), $this->callProtectedMethod('getQueryOptions', array($simple)));
  }

  public function testGetOrdersFromXml() {
    $simple = new SimpleXMLElement( TestData::UPDATE_ORDERS_SHIPPING_STATUS_REQUEST_XML );
    $ordersXml = $simple->Orders->Order;

    $orders = $this->callProtectedMethod(
      'getOrdersFromXml',
      array($ordersXml)
    );

    foreach( $orders as $i => $order ) {
      $orderXml = $ordersXml[$i];
      $this->assertEquals( $orderXml->HostOrderID,              $order['host_order_id'] );
      $this->assertEquals( intval($orderXml->LocalOrderID),     $order['local_order_id'] );
      $this->assertEquals( $orderXml->ShippedOn,                $order['shipped_on'] );
      $this->assertEquals( $orderXml->ShippedVia,               $order['shipped_via'] );
      $this->assertEquals( $orderXml->TrackingNumber,           $order['tracking_number'] );

      $this->assertEquals( $orderXml->NotifyCustomer,  $order['notify_customer'] );
      $this->assertEquals( $orderXml->ServiceUsed,     $order['service_used'] );
    }
  }

  public function testDecodeElement() {
    $simple = new SimpleXMLElement( TestData::BASE64_ENCODED_XML );

    $cases = array(
      'foo/bar' => $simple->Bar,
      'foo/qux/blub' => $simple->Qux->Blub,
      'plain' => $simple->Qux->Plain,
    );

    foreach( $cases as $expected => $element ) {
      $this->assertEquals(
        $expected,
        $this->callProtectedMethod( 'getDecodedValue', array($element) )
      );
    }
  }


  /* UTILITY METHODS */

  protected function getParsedResponse( $request ) {
    try {
      $response = @$this->thub->parseRequest( $request );
      return new SimpleXMLElement( $response );
    } catch( Exception $e ) {
      if( !empty($response) ) {
        $this->fail( "could not parse response: {$response}" );
      } else {
        $this->fail( "empty response with exception: {$e->getMessage()}" );
      }
    }
  }

  protected function callProtectedMethod( $name, $params=array() ) {
    $reflection = new ReflectionClass( 'THub\THubService' );
    $method = $reflection->getMethod( $name );
    $method->setAccessible( true );

    return $method->invokeArgs( $this->thub, $params );
  }
}

?>
