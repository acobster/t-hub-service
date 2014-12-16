<?php

require 'lib/THub/ThubService.php';
require 'lib/THub/AuthException.php';
require 'lib/THub/BadRequestException.php';

class THubServiceTest extends PHPUnit_Framework_TestCase {
  public static $order1 = array(
    'id' => '1',
    'transaction_type' => 'Sale',
  );

  public static $order2 = array(
    'id' => '2',
    'transaction_type' => 'Sale',
  );

  public function setUp() {
    $this->mockProvider = $this->getMockBuilder('OrderModel')
      ->setMethods( array('getNewOrders') )
      ->getMock();
    $this->thub = new THub\THubService( $this->mockProvider );
  }

  public function testAuthenticate() {
    $creds = array('user', 'password', 'xyz');
    $this->assertTrue( $this->callProtectedMethod('authenticate', $creds) );
  }

  public function testGetOrdersXmlWithNoNewOrders() {
    $this->mockProvider->method('getNewOrders')
      ->willReturn( array() );

    $parsed = $this->getParsedResponse( self::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( '1000', $parsed->Envelope->StatusCode );
    $this->assertEquals( 'No new orders', $parsed->Envelope->StatusMessage );
    $this->assertEquals( 'GENERIC', $parsed->Envelope->Provider );
    $this->assertEmpty( $parsed->Orders );
  }

  public function testGetOrdersXmlWithArray() {
    $this->mockProvider->method('getNewOrders')
      ->willReturn( array( self::$order1, self::$order2 ));

    $parsed = $this->getParsedResponse( self::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( '0', $parsed->Envelope->StatusCode );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );
    $this->assertEquals( 'GENERIC', $parsed->Envelope->Provider );
    $this->assertEquals( 2, $parsed->Orders->children()->count() );

    $orders = $parsed->Orders->Order;

    $this->assertOrder( self::$order1, $orders[0] );
    $this->assertOrder( self::$order2, $orders[1] );
  }

  public function testDecodeElement() {
    $simple = new SimpleXMLElement( self::BASE64_ENCODED_XML );

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

  protected function assertOrder( $expected, $actual ) {
    $this->assertEquals( $expected['id'], $actual->OrderID );
    $this->assertEquals( $expected['transaction_type'], $actual->TransactionType );
  }

  protected function getParsedResponse( $request ) {
    return new SimpleXMLElement( $this->thub->parseRequest($request) );
  }

  protected function callProtectedMethod( $name, $params=array() ) {
    $reflection = new ReflectionClass( 'THub\THubService' );
    $method = $reflection->getMethod( $name );
    $method->setAccessible( true );

    return $method->invokeArgs( $this->thub, $params );
  }

  const BASE64_ENCODED_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<Foo>
  <Bar encoding="yes">Zm9vL2Jhcg==</Bar>
  <Qux arbitrary="attribute">
    <Blub encoding="yes">Zm9vL3F1eC9ibHVi</Blub>
    <Plain>plain</Plain>
  </Qux>
</Foo>
_XML_;

  const GET_ORDERS_REQUEST_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

}

?>