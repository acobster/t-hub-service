<?php

class TestData {
  public static $orders = array(
    array(
      'order_id'            => '1',
      'provider_order_ref'  => '1',
      'transaction_type'    => 'Sale',
      'date'                => '2014-12-16',
      'time'                => '11:40:21',
      'time_zone'           => 'GST',
      'updated_on'          => '2014-12-16 22:40:21',
      'bill'                => array(
        'pay_method'          => 'CreditCard',
        'pay_status'          => 'Cleared',
        'pay_date'            => '2014-12-16',
        'first_name'          => 'Bob',
        'last_name'           => 'Barker',
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
        // no credit card info for now.
      ),
      'ship' => array(
        'ship_status'       => 'Shipped',
        'ship_date'         => '2014-12-01',
        'tracking'          => 'TRACKING-NO-234',
        'ship_cost'         => '4.20',
        'ship_carrier_name' => 'USPS',
        'ship_method'       => 'Ground',
        'first_name'        => 'Bobby',
        'last_name'         => 'Baker',
        'company_name'      => 'ACME c/o Bobby Baker',
        'address1'          => '321 Fake St.',
        'address2'          => 'Ste. 420',
        'city'              => 'Seattle',
        'state'             => 'WA',
        'zip'               => '54321',
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
          'item_total'          => '13.50',
          'item_unit_weight'    => '7',
          // No custom fields for now...
          // 'item_options'        => array(
          //   'foo' => 'bar',
          //   'qux' => 'tal',
          // ),
        ),
        array(
          'item_code'           => 'SDGE',
          'item_description'    => 'Woot woot',
          'quantity'            => '1',
          'unit_price'          => '4.50',
          'item_total'          => '13.50',
        ),
      ),
      'charges' => array(
        'shipping'          => '4.20',
        'handling'          => '0.00',
        'tax'               => '1.00',
        'discount'          => '0.00',
        'total'             => '100.00',
        'item_sub_total'    => '80.00',
        // T-HUB allows allows the following fields,
        // which we'll exclude for now:
        //  * payment_fee
        //  * channel_fee
        //  * gift_certificate
        //  * other_charge
        //  * other_tax
        //  * coupons (collection of arbitrary number of coupon elements)
        //    ...might look like:
        //    'coupons'   => array(
        //      array(
        //        'coupon_code'         => 'XCVB',
        //        'coupon_id'           => '98766',
        //        'coupon_description'  => 'foo',
        //        'coupon_value'        => '5.00',
        //      ),
        //    ),
      ),
    ),
    array(
      'order_id'            => '2',
      'provider_order_ref'  => '2',
      'transaction_type'    => 'Sale',
      'date'                => '2014-12-16',
      'time'                => '11:45:21',
      'time_zone'           => 'GST',
      'updated_on'          => '2014-12-16 22:45:21',
      'bill'                => array(
        'pay_method'          => 'CreditCard',
        'pay_status'          => 'Pending',
        'first_name'          => 'Bob',
        'last_name'           => 'Barker',
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
        'ship_status'       => 'New',
        'ship_carrier_name' => 'FedEx',
        'ship_method'       => 'Ground',
        'first_name'        => 'Bob',
        'last_name'         => 'Barker',
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
          'item_total'          => '16.50',
          'item_unit_weight'    => '7',
        ),
      ),
      'charges' => array(
        'shipping'          => '4.20',
        'handling'          => '0.00',
        'tax'               => '1.00',
        'discount'          => '0.00',
        'total'             => '100.00',
        'item_sub_total'    => '80.00',
      ),
    ),
  );

  public static $ordersToUpdate = array(
    array(
      'host_order_id' => '1234',
      'local_order_id' => '2345',
      'nofity_customer' => 'Yes',
      'shipped_on' => '12/01/2014',
      'shipped_via' => 'UPS',
      'service_used' => 'Ground',
      'tracking_number' => 'VNKV45356',
    ),
    array(
      'host_order_id' => '3456',
      'local_order_id' => '4567',
      'nofity_customer' => 'No',
      'shipped_on' => '12/01/2014',
      'shipped_via' => 'FEDEX',
      'service_used' => 'Next day air',
      'tracking_number' => 'ZDSF2356',
    ),
  );

  /**
   * Dynamically create some new and old orders
   * @return [type] [description]
   */
  public static function newAndOldOrders( $numEach=3 ) {
    $allOrders = array();
    for( $i=1; $i<=$numEach; $i++ ) {
      // Some week-old orders
      $oldOrder = self::$orders[1];
      $oldOrder['order_id'] = $i;
      $dt = new DateTime();
      $dt->sub( new DateInterval('P7D') );
      $oldOrder['date'] = $dt->format('Y-m-d');
      $oldOrder['time'] = $dt->format('H:i:s');
      $allOrders[] = $oldOrder;

      // Some orders from today
      $newOrder = self::$orders[0];
      $newOrder['order_id'] = $i+$numEach;
      $newOrder['date'] = date('Y-m-d');
      $newOrder['time'] = date('H:i:s');
      $allOrders[] = $newOrder;
    }

    return $allOrders;
  }

  public static $ALL_CASES = array(
    self::BASE64_ENCODED_XML,
    self::GET_ORDERS_REQUEST_XML,
    self::GET_ORDERS_REQUEST_XML_BY_ORDER_START_NUMBER,
    self::GET_ORDERS_REQUEST_XML_BY_NUM_DAYS,
    self::UPDATE_ORDERS_SHIPPING_STATUS_REQUEST_XML,
    self::UPDATE_ORDERS_SHIPPING_STATUS_NO_ORDERS_REQUEST_XML,
    self::UPDATE_ORDERS_SHIPPING_STATUS_NO_ORDER_CHILDREN_REQUEST_XML,
    self::BAD_XML,
    self::BAD_START_DATE_XML,
    self::BAD_NUM_DAYS,
    self::BAD_LIMIT_ORDER_COUNT,
    self::BAD_ORDER_START_NUMBER,
    self::BAD_COMMAND_XML,
    self::BAD_PASSWORD_XML,
    self::BAD_USER_XML,
    self::BAD_SECURITY_KEY_XML,
    self::MISSING_PASSWORD_XML,
    self::MISSING_USER_XML,
  );

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

  const GET_ORDERS_REQUEST_XML_BY_ORDER_START_NUMBER = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
   <OrderStartNumber>3</OrderStartNumber>
</REQUEST>
_XML_;

  const GET_ORDERS_REQUEST_XML_BY_NUM_DAYS = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
   <OrderStartNumber>0</OrderStartNumber>
   <NumberOfDays>5</NumberOfDays>
</REQUEST>
_XML_;

  const UPDATE_ORDERS_SHIPPING_STATUS_REQUEST_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
  <Command>UpdateOrdersShippingStatus</Command>
  <UserID>user</UserID>
  <Password>password</Password>
  <Status>all</Status>
  <SecurityKey>xyz</SecurityKey>
  <Orders>
    <Order>
      <HostOrderID>34088</HostOrderID>
      <LocalOrderID>4122</LocalOrderID>
      <NotifyCustomer>Yes</NotifyCustomer>
      <ShippedOn>12/05/2005</ShippedOn>
      <ShippedVia>UPS</ShippedVia>
      <ServiceUsed>Ground</ServiceUsed>
      <TrackingNumber>Z3121231213243455</TrackingNumber>
    </Order>
    <Order>
      <HostOrderID>34089</HostOrderID>
      <LocalOrderID>4123</LocalOrderID>
      <NotifyCustomer>No</NotifyCustomer>
      <ShippedOn>12/04/2005</ShippedOn>
      <ShippedVia>FEDEX</ShippedVia>
      <ServiceUsed>2nd Day Air</ServiceUsed>
      <TrackingNumber>F334523234234555</TrackingNumber>
    </Order>
  </Orders>
</REQUEST>
_XML_;

  const UPDATE_ORDERS_SHIPPING_STATUS_NO_ORDERS_REQUEST_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
  <Command>UpdateOrdersShippingStatus</Command>
  <UserID>user</UserID>
  <Password>password</Password>
  <Status>all</Status>
  <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const UPDATE_ORDERS_SHIPPING_STATUS_NO_ORDER_CHILDREN_REQUEST_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
  <Command>UpdateOrdersShippingStatus</Command>
  <UserID>user</UserID>
  <Password>password</Password>
  <Status>all</Status>
  <SecurityKey>xyz</SecurityKey>
  <Orders></Orders>
</REQUEST>
_XML_;

  const BAD_XML = '<xml>bad xml</wtf>';

  const BAD_START_DATE_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
   <DownloadStartDate>blah</DownloadStartDate>
</REQUEST>
_XML_;

  const BAD_NUM_DAYS = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
   <NumberOfDays>blah</NumberOfDays>
</REQUEST>
_XML_;

  const BAD_LIMIT_ORDER_COUNT = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
   <LimitOrderCount>blah</LimitOrderCount>
</REQUEST>
_XML_;

  const BAD_ORDER_START_NUMBER = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
   <OrderStartNumber>blah</OrderStartNumber>
</REQUEST>
_XML_;

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