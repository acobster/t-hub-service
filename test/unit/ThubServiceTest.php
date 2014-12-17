<?php

require 'lib/THub/ThubService.php';

class THubServiceTest extends PHPUnit_Framework_TestCase {
  public static $orders = array(
    array(
      'id'                => '1',
      'ref_id'            => '123456789',
      'transaction_type'  => 'Sale',
      'date'              => '2014-16-12',
      'time'              => '11:40:21',
      'time_zone'         => 'PST',
      'updated_on'        => '2014-16-12 22:40:21',
      'bill'              => array(
        'pay_method'        => 'CreditCard',
        'pay_status'        => 'Pending',
        'pay_date'          => '2014-16-12',
        'first_name'        => 'Bob',
        'last_name'         => 'Barker',
        'middle_name'       => 'Billy',
        'company_name'      => 'ACME',
        'address1'          => '123 Fake St.',
        'address2'          => 'Ste. 500',
        'city'              => 'Tacoma',
        'state'             => 'WA',
        'zip'               => '12345',
        'country'           => 'USA',
        'email'             => 'me@email.com',
        'phone'             => '123-456-2345',
        'po_number'         => '1234',
        'credit_card'             => array(
          'credit_card_type'      => 'VISA',
          'credit_card_charge'    => '100.00',
          'expiration_date'       => '12/2018',
          'credit_card_number'    => 'XXXXXXXXXXXX1234',
          'cvv2'                  => '321',
          'transaction_id'        => '456789',
          'settlement_batch_id'   => '34578',
          'reconciliation_data'   => '...Some data...',
        ),
      ),
      'ship' => array(
        'ship_status'       => 'New',
        'ship_carrier_name' => 'FedEx',
        'ship_method'       => 'Ground',
        'first_name'        => 'Bob',
        'last_name'         => 'Barker',
        'middle_name'       => 'Billy',
        'company_name'      => 'ACME',
        'address1'          => '123 Fake St.',
        'address2'          => 'Ste. 500',
        'city'              => 'Tacoma',
        'state'             => 'WA',
        'zip'               => '12345',
        'country'           => 'USA',
        'email'             => 'me@email.com',
        'phone'             => '123-456-2345',
      ),
      'items' => array(
        array(

        ),
        array(

        ),
      ),
      'charges' => array(
        'coupons' => array(
        ),
      ),
    ),
    array(
      'id'                => '2',
      'ref_id'            => '234567890',
      'transaction_type'  => 'Sale',
      'date'              => '2014-16-12',
      'time'              => '11:45:21',
      'time_zone'         => 'PST',
      'updated_on'        => '2014-16-12 22:45:21',
      'bill'              => array(
        'pay_method'        => 'CreditCard',
        'pay_status'        => 'Pending',
        'pay_date'          => '2014-16-12',
        'first_name'        => 'Bob',
        'last_name'         => 'Barker',
        'middle_name'       => 'Billy',
        'company_name'      => 'ACME',
        'address1'          => '123 Fake St.',
        'address2'          => 'Ste. 500',
        'city'              => 'Tacoma',
        'state'             => 'WA',
        'zip'               => '12345',
        'country'           => 'USA',
        'email'             => 'me@email.com',
        'phone'             => '123-456-2345',
        'po_number'         => '1234',
      ),
      'ship' => array(
        'ship_status'       => 'Shipped',
        'ship_date'         => '2014-12-01',
        'ship_tracking'     => 'TRACKING-NO-234',
        'ship_cost'         => '4.20',
        'ship_carrier_name' => 'USPS',
        'ship_method'       => 'Ground',
        'first_name'        => 'Bob',
        'last_name'         => 'Barker',
        'middle_name'       => 'Billy',
        'company_name'      => 'ACME',
        'address1'          => '123 Fake St.',
        'address2'          => 'Ste. 500',
        'city'              => 'Tacoma',
        'state'             => 'WA',
        'zip'               => '12345',
        'country'           => 'USA',
        'email'             => 'me@email.com',
        'phone'             => '123-456-2345',
      ),
      'items' => array(
        array(

        ),
        array(

        ),
      ),
      'charges' => array(
        'coupons' => array(
        ),
      ),
    ),
  );

  public function setUp() {
    $this->mockProvider = $this->getMockBuilder('OrderProvider')
      ->setMockClassName( 'OrderModel' )
      ->setMethods( array('getNewOrders') )
      ->getMock();
    $this->thub = new THub\THubService( $this->mockProvider );
  }

  public function testParseRequestWithBadXml() {
    $parsed = $this->getParsedResponse( self::BAD_XML );
    $this->assertEquals( 'UNKNOWN', $parsed->Envelope->Command );
    $this->assertEquals( '9999', $parsed->Envelope->StatusCode );
    $this->assertEquals( 'String could not be parsed as XML',
      $parsed->Envelope->StatusMessage );
  }

  public function testBadCommandResponse() {
    $parsed = $this->getParsedResponse( self::BAD_COMMAND_XML );
    $this->assertEquals( 'No such command: FOO', $parsed->Envelope->StatusMessage );
  }

  public function testAuthenticate() {
    $creds = array('user', 'password', 'xyz');
    $this->assertTrue( $this->callProtectedMethod('authenticate', $creds) );

    $badAttempts = array(
      array('', 'password', 'xyz'),
      array('user', '', 'xyz'),
      array('user', 'password', ''),
    );

    foreach( $badAttempts as $attempt ) {
      $this->assertFalse( $this->callProtectedMethod('authenticate', $attempt) );
    }

    $cases = array(
      self::MISSING_USER_XML,
      self::MISSING_PASSWORD_XML,
      self::BAD_PASSWORD_XML,
      self::BAD_USER_XML,
      self::BAD_SECURITY_KEY_XML,
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

    $parsed = $this->getParsedResponse( self::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( '1000', $parsed->Envelope->StatusCode );
    $this->assertEquals( 'No new orders', $parsed->Envelope->StatusMessage );
    $this->assertEquals( 'GENERIC', $parsed->Envelope->Provider );
    $this->assertEmpty( $parsed->Orders );
  }

  public function testGetOrdersXmlWithArray() {
    $this->mockProvider->method('getNewOrders')
      ->willReturn( self::$orders );

    $parsed = $this->getParsedResponse( self::GET_ORDERS_REQUEST_XML );
    $this->assertEquals( 'GetOrders', $parsed->Envelope->Command );
    $this->assertEquals( '0', $parsed->Envelope->StatusCode );
    $this->assertEquals( 'All Ok', $parsed->Envelope->StatusMessage );
    $this->assertEquals( 'GENERIC', $parsed->Envelope->Provider );
    $this->assertEquals( 2, $parsed->Orders->children()->count() );

    $orders = $parsed->Orders->Order;

    $this->assertOrder( self::$orders[0], $orders[0] );
    $this->assertOrder( self::$orders[1], $orders[1] );
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
    $this->assertEquals( $expected['id'],               $actual->OrderID );
    $this->assertEquals( $expected['ref_id'],           $actual->ProviderOrderRef );
    $this->assertEquals( $expected['transaction_type'], $actual->TransactionType );
    $this->assertEquals( $expected['date'],             $actual->Date );
    $this->assertEquals( $expected['time'],             $actual->Time );
    $this->assertEquals( $expected['time_zone'],        $actual->TimeZone );
    $this->assertEquals( $expected['updated_on'],       $actual->UpdatedOn );

    $this->assertOrderBill( $expected['bill'],          $actual->Bill );
    $this->assertOrderShip( $expected['ship'],          $actual->Ship );
    // $this->assertOrderItems( $expected['items'],        $actual->OrderItems );
    // $this->assertOrderCharges( $expected['charges'],    $actual->OrderCharges );
  }

  protected function assertOrderBill( $expected, $actual ) {
    $this->assertEquals( $expected['pay_method'],   $actual->PayMethod );
    $this->assertEquals( $expected['pay_status'],   $actual->PayStatus );
    $this->assertEquals( $expected['first_name'],   $actual->FirstName );
    $this->assertEquals( $expected['last_name'],    $actual->LastName );
    $this->assertEquals( $expected['address1'],     $actual->Address1 );
    $this->assertEquals( $expected['address2'],     $actual->Address2 );
    $this->assertEquals( $expected['city' ],        $actual->City );
    $this->assertEquals( $expected['state'],        $actual->State );
    $this->assertEquals( $expected['zip'],          $actual->Zip );
    $this->assertEquals( $expected['country'],      $actual->Country );
    $this->assertEquals( $expected['email'],        $actual->Email );
    $this->assertEquals( $expected['phone'],        $actual->Phone );

    $this->assertEqualsIfPresent( $expected['pay_date'],     $actual->PayDate );
    $this->assertEqualsIfPresent( $expected['middle_name'], $actual->MiddleName );
    $this->assertEqualsIfPresent( $expected['company_name'], $actual->CompanyName );
    $this->assertEqualsIfPresent( $expected['address2'],     $actual->Address2 );
    $this->assertEqualsIfPresent( $expected['po_number'], $actual->PONumber );
    $this->assertEqualsIfPresent( $expected['payment_amount'], $actual->PaymentAmount );

    if( $expected['credit_card'] )
      $this->assertOrderBillCard( $expected['credit_card'], $actual->CreditCard );
  }

  protected function assertOrderBillCard( $expected, $actual ) {
    $this->assertEquals( $expected['credit_card_type'],     $actual->CreditCardType );
    $this->assertEquals( $expected['credit_card_charge'],   $actual->CreditCardCharge );
    $this->assertEquals( $expected['expiration_date'],      $actual->ExpirationDate );
    $this->assertEquals( $expected['credit_card_number'],   $actual->CreditCardNumber );

    $this->assertEqualsIfPresent( $expected['cvv2'], $actual->CVV2 );
    $this->assertEqualsIfPresent( $expected['auth_details'], $actual->AuthDetails );
    $this->assertEqualsIfPresent( $expected['transaction_id'], $actual->TransactionID );
    $this->assertEqualsIfPresent( $expected['settlement_batch_id'], $actual->SettlementBatchID );
    $this->assertEqualsIfPresent( $expected['reconciliation_data'], $actual->ReconciliationData );
  }

  protected function assertOrderShip( $expected, $actual ) {
    $this->assertEquals( $expected['ship_carrier_name'],    $actual->ShipCarrierName );
    $this->assertEquals( $expected['ship_method'],          $actual->ShipMethod );
    $this->assertEquals( $expected['first_name'],           $actual->FirstName );
    $this->assertEquals( $expected['last_name'],            $actual->LastName );
    $this->assertEquals( $expected['address1'],             $actual->Address1 );
    $this->assertEquals( $expected['city'],                 $actual->City );
    $this->assertEquals( $expected['state'],                $actual->State );
    $this->assertEquals( $expected['zip'],                  $actual->Zip );
    $this->assertEquals( $expected['country'],              $actual->Country );
    $this->assertEquals( $expected['email'],                $actual->Email );
    $this->assertEquals( $expected['phone'],                $actual->Phone );

    $this->assertEqualsIfPresent( $expected['ship_status'],     $actual->ShipStatus );
    $this->assertEqualsIfPresent( $expected['ship_date'],       $actual->ShipDate );
    $this->assertEqualsIfPresent( $expected['ship_tracking'],   $actual->ShipTracking );
    $this->assertEqualsIfPresent( $expected['ship_cost'],       $actual->ShipCost );
    $this->assertEqualsIfPresent( $expected['middle_name'],     $actual->MiddleName );
    $this->assertEqualsIfPresent( $expected['company_name'],    $actual->CompanyName );
    $this->assertEqualsIfPresent( $expected['address2'],        $actual->Address2 );
  }

  protected function assertOrderItems( $expected, $actual ) {

  }

  protected function assertOrderCharges( $expected, $actual ) {

  }

  protected function assertEqualsIfPresent( $expected, $actual ) {
    if( !empty($expected) ) {
      $this->assertEquals( $expected, $actual );
    }
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

  const BAD_XML = '<xml>bad xml</wtf>';

  const BAD_COMMAND_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>FOO</Command>
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

  const BAD_USER_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>BAD</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const BAD_SECURITY_KEY_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>BAD</SecurityKey>
</REQUEST>
_XML_;

  const MISSING_PASSWORD_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const MISSING_USER_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

}

?>