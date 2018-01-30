<?php

class OrderFixtures {
  protected static $DB;

  protected static function db() {
    if( !self::$DB ) {
      self::$DB = new PDO(
        'mysql:host=database;dbname=lamp',
        'lamp',
        'lamp'
      );
    }

    return self::$DB;
  }

  public static function insertOrders( $orders ) {
    foreach( $orders as $order ) {
      self::insertOrder( $order );
    }
  }

  public static function insertOrder( $order ) {
    $setOrderId = $order['order_id']
      ? "ID = {$order['order_id']},"
      : '';

    $bill     = $order['bill'];
    $ship     = $order['ship'];
    $charges  = $order['charges'];

    $createdDT = new DateTime( $order['date'] . ' ' . $order['time'] );
    $created = $createdDT->format( 'Y-m-d H:i:s' );
    $updatedDT = new DateTime( $order['updated_on'] );
    $updated = $updatedDT->format( 'Y-m-d H:i:s' );

    $poNumber = isset($bill['po_number']) ? $bill['po_number'] : '';

    // ensure we have a PAYMENT_TYPE
    $method = str_replace('CreditCard', 'Credit Card', $order['bill']['pay_method']);

    // Corporate shipping account?
    if( !empty($order['ship']['corporate_account']) ) {
      $useCorporateAccount = '1';
      $corporateAccountCarrier = strtolower($order['ship']['ship_carrier_name']);
      $corporateAccountNumber = '1234';
      $corporateAccountMethod = 'carrier pigeon';
    } else {
      $useCorporateAccount = '0';
      $corporateAccountCarrier = '';
      $corporateAccountNumber = '';
      $corporateAccountMethod = '';
    }

    if (empty($method)) die($method);
    $sql = <<<_SQL_
INSERT INTO orders SET {$setOrderId}
  ORDER_NUMBER='9876',
  FIRST='{$bill['first_name']}',
  LAST='{$bill['last_name']}',
  PAYSTATUS='{$bill['pay_status']}',
  PAID_DATETIME='{$bill['pay_date']}',
  CREATED='{$created}',
  LASTUPDATED='{$updated}',
  ORGANIZATION='{$bill['company_name']}',
  ADDRESS='{$bill['address1']}',
  ADDRESS2='{$bill['address2']}',
  CITY='{$bill['city']}',
  STATE='{$bill['state']}',
  ZIP='{$bill['zip']}',
  COUNTRY='{$bill['country']}',
  PONUMBER='{$poNumber}',
  SHIPPING_METHOD='{$ship['ship_method']}',
  SHIPPING_FIRST='{$ship['first_name']}',
  SHIPPING_LAST='{$ship['last_name']}',
  SHIPPING_ORGANIZATION='{$ship['company_name']}',
  SHIPPING_ADDRESS='{$ship['address1']}',
  SHIPPING_ADDRESS2='{$ship['address2']}',
  SHIPPING_CITY='{$ship['city']}',
  SHIPPING_STATE='{$ship['state']}',
  SHIPPING_ZIP='{$ship['zip']}',
  SHIPPING_COUNTRY='{$ship['country']}',
  USE_SHIPPING_ACCOUNT='{$useCorporateAccount}',
  SHIPPING_ACCOUNT_CARRIER='{$corporateAccountCarrier}',
  SHIPPING_ACCOUNT_NUMBER='{$corporateAccountNumber}',
  SHIPPING_ACCOUNT_METHOD='{$corporateAccountMethod}',
  PHONE='{$ship['phone']}',
  EMAIL='{$ship['email']}',
  SUBTOTAL={$charges['item_sub_total']},
  TAX={$charges['tax']},
  SHIPPING={$charges['shipping']},
  TOTAL={$charges['total']},
  PAYMENT_TYPE='$method',
  TRANSACTIONID='1234',
  CARD_TYPE='Visa',
  CARD_LAST4='1111',
  TAX_RATE=0.9,
  TAX_CODE='tax-code',
  COMMENTS='foo',
  FULFILLED=1, ACCOUNTID=5432,
  DATE=CURDATE()
_SQL_;

    self::write( $sql );
    $orderId = self::lastId();

    self::insertShipping( $order, $orderId );

    foreach( $order['order_items'] as $item ) {
      self::insertOrderItem( $item, $orderId );
    }
  }

  public static function insertCard( $order, $orderId ) {
    $card = $order['bill']['credit_card'];
    $cardType = ( $card && $card['credit_card_type'] )
      ? $card['credit_card_type']
      : '';

    $pmtType = ( $order['bill']['pay_method'] == 'CreditCard' )
      ? 'Credit Card'
      : $order['bill']['pay_method'];

    $sql = <<<_SQL_
INSERT INTO orders_activity SET ORDERID={$orderId},
  PAYMENT_TYPE='$pmtType',
  CCTYPE='{$cardType}',
  NOTES='foo', TRANSACTIONID='1234', LAST4CC='1234', CHECK_NUMBER='123',
  PAYMENT=2.00, IP_ADDRESS='1.2.3.4', ACCOUNTID=4321, CREATED=NOW()
_SQL_;

    self::write( $sql );
  }

  public static function insertShipping( $order, $orderId ) {
    $shipping = $order['ship'];

    if( isset($shipping['ship_date']) ) {
      $dt = new DateTime( $shipping['ship_date'] );
      $shipDate = "{$dt->format('Y-m-d')}";
    } else {
      $shipDate = "1970-01-01";
    }

    $shipped = ( $shipping['ship_status'] == 'Shipped' ) ? 1 : 0;

    $sql = <<<_SQL_
INSERT INTO orders_shipping_tracking SET ORDERID={$orderId},
  CARRIER = '{$shipping['ship_carrier_name']}',
  SHIPPING_METHOD = '{$shipping['ship_method']}',
  TRACKING_NUMBER = '{$shipping['tracking']}',
  SHIPPED_DATE = '{$shipDate}',
  SHIPPED = {$shipped},
  SHIPPED_EMAIL_NOTICE = 0
_SQL_;

    self::write( $sql );
  }

  public static function insertOrderItem( $item, $orderId ) {
    $inventoryId = self::insertInventory( $item, $orderId );

    $sql = <<<_SQL_
INSERT INTO orders_details SET
  ORDERID={$orderId},
  INVENTORYID={$inventoryId},
  NAME='foo',
  DESCRIPTION='{$item['item_description']}',
  QUANTITY={$item['quantity']},
  RATE={$item['unit_price']},
  LINE_TOTAL={$item['item_total']},
  UNIT='unit'
_SQL_;

    self::write( $sql );
    return self::lastId();
  }

  public static function insertInventory( $item ) {
    $sql = <<<_SQL_
INSERT INTO inventory SET
  PRODUCT_CODE='{$item['item_code']}',
  CONTENTID=123,
  DESCRIPTION='the best thing ever',
  RETAIL_PRICE=2.00,
  OUR_PRICE=1.00,
  SHIPPING_WEIGHT=10.0,
  ADDITIONAL_SHIPPING=1.00,
  MANUFACTURER='',
  CONNECTION='something',
  CONNECTION2='',
  PIN='',
  COLOR='',
  TUBE_COLOR='',
  EAR_SIDE='',
  PTT='',
  `SIZE`='',
  BOOM='',
  PRODUCT_TYPE='',
  WIRE_COUNT='',
  EAR_PIECE_STYLE='',
  CASE_TYPE='',
  HEADSET_TYPE='Tactical'
_SQL_;

    self::write( $sql );
    return self::lastId();
  }

  public static function createSchema() {
    self::db()->exec(file_get_contents(__DIR__.'/schema.sql'));
  }

  public static function truncateAll() {
    $tables = array(
      'orders',
      'orders_details',
      'orders_shipping_tracking',
      'inventory',
    );

    foreach( $tables as $table ) {
      self::db()->exec( "TRUNCATE $table" );
    }
  }

  protected static function write( $sql ) {
    if( self::db()->exec($sql) === false ) {
      $info = self::db()->errorInfo();
      $message = sprintf( 'DB ERROR: %s (%s:%d)', $info[2], $info[0], $info[1] );
      throw new RuntimeException( $message );
    }
  }

  protected static function lastId() {
    return self::db()->lastInsertId();
  }
}

?>
