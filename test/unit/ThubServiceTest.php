<?php

require 'lib/THub/ThubService.php';

class THubServiceTest extends PHPUnit_Framework_TestCase {
  public static $orders = array(
    array(
      'order_id'            => '1',
      'provider_order_ref'  => '123456789',
      'transaction_type'    => 'Sale',
      'date'                => '2014-16-12',
      'time'                => '11:40:21',
      'time_zone'           => 'PST',
      'updated_on'          => '2014-16-12 22:40:21',
      'bill'                => array(
        'pay_method'          => 'CreditCard',
        'pay_status'          => 'Pending',
        'pay_date'            => '2014-16-12',
        'first_name'          => 'Bob',
        'last_name'           => 'Barker',
        'middle_name'         => 'Billy',
        'company_name'        => 'ACME',
        'address1'            => '123 Fake St.',
        'address2'            => 'Ste. 500',
        'city'                => 'Tacoma',
        'state'               => 'WA',
        'zip'                 => '12345',
        'country'             => 'USA',
        'email'               => 'me@email.com',
        'phone'               => '123-456-2345',
        'po_number'           => '1234',
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
      'order_items' => array(
        array(
          'item_code'           => 'ASDF',
          'item_description'    => 'An awesome product',
          'quantity'            => '3',
          'unit_price'          => '4.50',
          'unit_cost'           => '4.50',
          'vendor'              => 'ACME',
          'item_total'          => '13.50',
          'item_unit_weight'    => '7',
          // No custom fields for now...
          // But let's use options for kicks:
          'item_options'        => array(
            'foo' => 'bar',
            'qux' => 'tal',
          ),
        ),
        array(
          'item_code'           => 'SDGE',
          'item_description'    => 'Woot woot',
          'quantity'            => '1',
          'unit_price'          => '4.50',
          'unit_cost'           => '4.50',
          'vendor'              => 'StrexCorp',
          'item_total'          => '13.50',
        ),
      ),
      'charges' => array(
        'shipping'          => '4.20',
        'handling'          => '3.00',
        'tax'               => '1.00',
        'discount'          => '0.50',
        'total'             => '100.00',
        'tax_other'         => '1.20',
        'channel_fee'       => '0.75',
        'payment_fee'       => '2.00',
        'gift_certificate'  => '10.00',
        'other_charge'      => '0.30',
        'item_sub_total'    => '80.00',
        'coupons'   => array(
          array(
            'coupon_code'         => 'XCVB',
            'coupon_id'           => '98766',
            'coupon_description'  => 'foo',
            'coupon_value'        => '5.00',
          ),
        ),
      ),
    ),
    array(
      'order_id'            => '2',
      'provider_order_ref'  => '234567890',
      'transaction_type'    => 'Sale',
      'date'                => '2014-16-12',
      'time'                => '11:45:21',
      'time_zone'           => 'PST',
      'updated_on'          => '2014-16-12 22:45:21',
      'bill'                => array(
        'pay_method'          => 'CreditCard',
        'pay_status'          => 'Pending',
        'pay_date'            => '2014-16-12',
        'first_name'          => 'Bob',
        'last_name'           => 'Barker',
        'middle_name'         => 'Billy',
        'company_name'        => 'ACME',
        'address1'            => '123 Fake St.',
        'address2'            => 'Ste. 500',
        'city'                => 'Tacoma',
        'state'               => 'WA',
        'zip'                 => '12345',
        'country'             => 'USA',
        'email'               => 'me@email.com',
        'phone'               => '123-456-2345',
        'po_number'           => '1234',
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
      'order_items' => array(
        array(
          'item_code'           => 'ERTEY',
          'item_description'    => 'Blah Blah Blah...',
          'quantity'            => '2',
          'unit_price'          => '3.50',
          'item_total'          => '10.50',
        ),
        array(
          'item_code'           => 'OSHGNG',
          'item_description'    => 'asbnwoviwongoahf ...',
          'quantity'            => '10',
          'unit_price'          => '5.50',
          'unit_cost'           => '5.50',
          'item_total'          => '16.50',
          'item_unit_weight'    => '7',
        ),
      ),
      'charges' => array(
        'shipping'          => '4.20',
        'handling'          => '3.00',
        'tax'               => '1.00',
        'discount'          => '0.50',
        'total'             => '100.00',
        'tax_other'         => '1.20',
        'channel_fee'       => '0.75',
        'payment_fee'       => '2.00',
        'gift_certificate'  => '10.00',
        'other_charge'      => '0.30',
        'item_sub_total'    => '80.00',
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

  public function testUpdateOrdersShippingStatus() {
    $this->markTestIncomplete();
  }

  public function testUpdateInventory() {
    $this->markTestIncomplete();
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


  /* CUSTOM ASSERTIONS */

  protected function assertOrder( $expected, $actual ) {
    $this->assertEquals( $expected['order_id'],           $actual->OrderID );
    $this->assertEquals( $expected['provider_order_ref'], $actual->ProviderOrderRef );
    $this->assertEquals( $expected['transaction_type'],   $actual->TransactionType );
    $this->assertEquals( $expected['date'],               $actual->Date );
    $this->assertEquals( $expected['time'],               $actual->Time );
    $this->assertEquals( $expected['time_zone'],          $actual->TimeZone );
    $this->assertEquals( $expected['updated_on'],         $actual->UpdatedOn );

    $this->assertOrderBill( $expected['bill'],          $actual->Bill );
    $this->assertOrderShip( $expected['ship'],          $actual->Ship );
    $this->assertOrderItems( $expected['order_items'],        $actual->OrderItems );
    $this->assertOrderCharges( $expected['charges'],    $actual->Charges );
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
    $itemCount = count($expected);
    $this->assertEquals( 2, $actual->children()->count() );

    for( $i=0; $i<$itemCount; $i++ ) {
      $this->assertSingleOrderItem( $expected[$i], $actual->children()[$i] );
    }
  }

  protected function assertSingleOrderItem( $expected, $actual ) {
    $this->assertEquals( $expected['item_code'],          $actual->ItemCode );
    $this->assertEquals( $expected['item_description'],   $actual->ItemDescription );
    $this->assertEquals( $expected['quantity'],           $actual->Quantity );
    $this->assertEquals( $expected['unit_price'],         $actual->UnitPrice );
    $this->assertEquals( $expected['item_total'],         $actual->ItemTotal );

    $this->assertEqualsIfPresent( $expected['unit_cost'],   $actual->UnitCost );
    $this->assertEqualsIfPresent( $expected['vendor'],      $actual->Vendor );
    $this->assertEqualsIfPresent( $expected['unit_weight'], $actual->UnitWeight );

    // options
    if( $options = $expected['item_options'] ) {
      $optionCount = count( $options );
      $this->assertEquals( $optionCount, $actual->ItemOptions->children()->count() );

      $i = 0;
      foreach( $options as $k => $v ) {
        $this->assertEquals(
          $k,
          $actual->ItemOptions->children()[$i]['Name']
        );
        $this->assertEquals(
          $v,
          $actual->ItemOptions->children()[$i]['Value']
        );
        $i++;
      }
    }
  }

  protected function assertOrderCharges( $expected, $actual ) {
    $this->assertEquals( $expected['shipping'],   $actual->Shipping );
    $this->assertEquals( $expected['handling'],   $actual->Handling );
    $this->assertEquals( $expected['tax'],        $actual->Tax );
    $this->assertEquals( $expected['discount'],   $actual->Discount );
    $this->assertEquals( $expected['total'],      $actual->Total );

    $this->assertEqualsIfPresent( $expected['tax_other'],         $actual->TaxOther );
    $this->assertEqualsIfPresent( $expected['channel_fee'],       $actual->ChannelFee );
    $this->assertEqualsIfPresent( $expected['payment_fee'],       $actual->PaymentFee );
    $this->assertEqualsIfPresent( $expected['gift_certificate'],  $actual->GiftCertificate );
    $this->assertEqualsIfPresent( $expected['other_charge'],      $actual->OtherCharge );
    $this->assertEqualsIfPresent( $expected['item_sub_total'],    $actual->ItemSubTotal );

    if( $expected['coupons'] ) {
      $this->assertOrderCoupons( $expected['coupons'], $actual->Coupons );
    }
  }

  protected function assertOrderCoupons( $expected, $actual ) {
    $couponCount = count( $expected );
    $this->assertEquals( $couponCount, $actual->children()->count() );

    foreach( $expected as $i => $coupon ) {
      $actualCoupon = $actual->children()[$i];
      $this->assertEquals( $coupon['coupon_code'],         $actualCoupon->CouponCode );
      $this->assertEquals( $coupon['coupon_id'],           $actualCoupon->CouponID );
      $this->assertEquals( $coupon['coupon_description'],  $actualCoupon->CouponDescription );
      $this->assertEquals( $coupon['coupon_value'],        $actualCoupon->CouponValue );
    }
  }

  protected function assertEqualsIfPresent( $expected, $actual ) {
    if( !empty($expected) ) {
      $this->assertEquals( $expected, $actual );
    }
  }


  /* UTILITY METHODS */

  protected function getParsedResponse( $request ) {
    return new SimpleXMLElement( $this->thub->parseRequest($request) );
  }

  protected function callProtectedMethod( $name, $params=array() ) {
    $reflection = new ReflectionClass( 'THub\THubService' );
    $method = $reflection->getMethod( $name );
    $method->setAccessible( true );

    return $method->invokeArgs( $this->thub, $params );
  }


  /* EXAMPLE XML REQUESTS */

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