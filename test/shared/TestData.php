<?php

class TestData {
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