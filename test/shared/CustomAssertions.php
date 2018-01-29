<?php

trait CustomAssertions {
  protected function assertOrder( $expected, $actual ) {
    $this->assertEquals( $expected['order_id'],           $actual->OrderID );
    $this->assertEquals( $expected['provider_order_ref'], $actual->OrderID );
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

    $this->assertEqualsIfPresent( $expected, 'pay_date',     $actual->PayDate );
    $this->assertEqualsIfPresent( $expected, 'middle_name', $actual->MiddleName );
    $this->assertEqualsIfPresent( $expected, 'company_name', $actual->CompanyName );
    $this->assertEqualsIfPresent( $expected, 'address2',     $actual->Address2 );
    $this->assertEqualsIfPresent( $expected, 'po_number', $actual->PONumber );
    $this->assertEqualsIfPresent( $expected, 'payment_amount', $actual->PaymentAmount );

    if( isset($expected['credit_card']) ) {
      $this->assertOrderBillCard( $expected['credit_card'], $actual->CreditCard );
    }
  }

  protected function assertOrderBillCard( $expected, $actual ) {
    $this->assertEquals( $expected['credit_card_type'],     $actual->CreditCardType );
    $this->assertEquals( $expected['credit_card_charge'],   $actual->CreditCardCharge );
    $this->assertEquals( $expected['expiration_date'],      $actual->ExpirationDate );
    $this->assertEquals( $expected['credit_card_number'],   $actual->CreditCardNumber );

    $this->assertEqualsIfPresent( $expected, 'cvv2', $actual->CVV2 );
    $this->assertEqualsIfPresent( $expected, 'auth_details', $actual->AuthDetails );
    $this->assertEqualsIfPresent( $expected, 'transaction_id', $actual->TransactionID );
    $this->assertEqualsIfPresent( $expected, 'settlement_batch_id', $actual->SettlementBatchID );
    $this->assertEqualsIfPresent( $expected, 'reconciliation_data', $actual->ReconciliationData );
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

    $this->assertEqualsIfPresent( $expected, 'ship_status',     $actual->ShipStatus );
    $this->assertEqualsIfPresent( $expected, 'ship_date',       $actual->ShipDate );
    $this->assertEqualsIfPresent( $expected, 'tracking',        $actual->Tracking );
    $this->assertEqualsIfPresent( $expected, 'ship_cost',       $actual->ShipCost );
    $this->assertEqualsIfPresent( $expected, 'middle_name',     $actual->MiddleName );
    $this->assertEqualsIfPresent( $expected, 'company_name',    $actual->CompanyName );
    $this->assertEqualsIfPresent( $expected, 'address2',        $actual->Address2 );
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

    $this->assertEqualsIfPresent( $expected, 'unit_cost',   $actual->UnitCost );
    $this->assertEqualsIfPresent( $expected, 'vendor',      $actual->Vendor );
    $this->assertEqualsIfPresent( $expected, 'unit_weight', $actual->UnitWeight );

    // options
    if( isset($expected['item_options']) and $options = $expected['item_options'] ) {
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

    $this->assertEqualsIfPresent( $expected, 'tax_other',         $actual->TaxOther );
    $this->assertEqualsIfPresent( $expected, 'channel_fee',       $actual->ChannelFee );
    $this->assertEqualsIfPresent( $expected, 'payment_fee',       $actual->PaymentFee );
    $this->assertEqualsIfPresent( $expected, 'gift_certificate',  $actual->GiftCertificate );
    $this->assertEqualsIfPresent( $expected, 'other_charge',      $actual->OtherCharge );
    $this->assertEqualsIfPresent( $expected, 'item_sub_total',    $actual->ItemSubTotal );

    if( isset($expected['coupons']) ) {
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

  protected function assertEqualsIfPresent( $expected, $index, $actual ) {
    if( isset($expected[$index]) ) {
      $this->assertEquals( $expected[$index], $actual );
    }
  }
}

?>
