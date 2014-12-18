<?php

require_once 'lib/THub/ThubService.php';
require_once 'test/shared/TestData.php';

class THubServiceTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->mockProvider = $this->getMockBuilder('OrderProvider')
      ->setMockClassName( 'OrderModel' )
      ->setMethods( array('getNewOrders') )
      ->getMock();
    $this->thub = new THub\THubService( $this->mockProvider );
  }

  public function testParseRequestWithBadXml() {
    $parsed = $this->getParsedResponse( TestData::BAD_XML );
    $this->assertEquals( 'UNKNOWN', $parsed->Envelope->Command );
    $this->assertEquals( '9999', $parsed->Envelope->StatusCode );
    $this->assertEquals( 'String could not be parsed as XML',
      $parsed->Envelope->StatusMessage );
  }

  public function testBadCommandResponse() {
    $parsed = $this->getParsedResponse( TestData::BAD_COMMAND_XML );
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
    $this->mockProvider->method('getNewOrders')
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

  public function testUpdateOrdersShippingStatus() {
    $this->markTestIncomplete();
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
}

?>