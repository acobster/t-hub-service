<?php

class OrderFixtures {
  protected static $DB;

  protected static function db() {
    if( !self::$DB ) {
      self::$DB = new PDO(
        'mysql:host=localhost;dbname=planethe_database;unix_socket=/tmp/mysql.sock',
        'thub_test',
        'Df3CroK)lELo'
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

    if( $bill['pay_date'] ) {
      $dt = new DateTime( $bill['pay_date'] );
      $payDate = "'{$dt->format('Y-m-d H:i:s')}'";
    } else {
      $payDate = "'0000-00-00 00:00:00'";
    }

    $createdDT = new DateTime( $order['date'] . ' ' . $order['time'] );
    $created = $createdDT->format( 'Y-m-d H:i:s' );
    $updatedDT = new DateTime( $order['updated_on'] );
    $updated = $updatedDT->format( 'Y-m-d H:i:s' );

    $sql = <<<_SQL_
INSERT INTO invoices SET {$setOrderId}
  INVOICE_NUMBER='9876',
  FIRST='{$bill['first_name']}',
  LAST='{$bill['last_name']}',
  PAID_DATETIME={$payDate},
  PAYSTATUS='{$bill['pay_status']}',
  CREATED='{$created}',
  LASTUPDATED='{$updated}',
  ORGANIZATION='{$bill['company_name']}',
  ADDRESS='{$bill['address1']}',
  ADDRESS2='{$bill['address2']}',
  CITY='{$bill['city']}',
  STATE='{$bill['state']}',
  ZIP='{$bill['zip']}',
  COUNTRY='{$bill['country']}',
  PONUMBER='{$bill['po_number']}',
  SHIPPING_METHOD='{$ship['shipping_method']}',
  SHIPPING_FIRST='{$ship['first_name']}',
  SHIPPING_LAST='{$ship['last_name']}',
  SHIPPING_ORGANIZATION='{$ship['company_name']}',
  SHIPPING_ADDRESS='{$ship['address1']}',
  SHIPPING_ADDRESS2='{$ship['address2']}',
  SHIPPING_CITY='{$ship['city']}',
  SHIPPING_STATE='{$ship['state']}',
  SHIPPING_ZIP='{$ship['zip']}',
  SHIPPING_COUNTRY='{$ship['country']}',
  PHONE='{$ship['phone']}',
  EMAIL='{$ship['email']}',
  SUBTOTAL={$charges['item_sub_total']},
  TAX={$charges['tax']},
  SHIPPING={$charges['shipping']},
  TOTAL={$charges['total']},
  BALANCE=0.0, TAX_RATE=0.9, TAX_CODE='tax-code', TAX_CITY='Taxville',
  TAX_STATE='TX', TAX_COUNTRY='USA', COMMENTS='foo', PROMO_CODE='1235sdfg',
  PROMO_DESCRIPTION='everythingallofthetime', AFFILIATE_CONTACTID=5,
  FULFILLED=1, FULFILLED_ACCOUNTID=5, LOGINKEY='asdf', ACCOUNTID=5432,
  DATE=CURDATE()
_SQL_;

    self::write( $sql );
    $orderId = self::lastId();

    self::insertCard( $order, $orderId );
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
INSERT INTO invoices_activity SET INVOICEID={$orderId},
  PAYMENT_TYPE='$pmtType',
  CCTYPE='{$cardType}',
  NOTES='foo', TRANSACTIONID='1234', LAST4CC='1234', CHECK_NUMBER='123',
  PAYMENT=2.00, IP_ADDRESS='1.2.3.4', ACCOUNTID=4321, CREATED=NOW()
_SQL_;

    self::write( $sql );
  }

  public static function insertShipping( $order, $orderId ) {
    $shipping = $order['ship'];

    if( $shipping['ship_date'] ) {
      $dt = new DateTime( $shipping['ship_date'] );
      $shipDate = "{$dt->format('Y-m-d')}";
    } else {
      $shipDate = "0000-00-00";
    }

    $shipped = ( $shipping['ship_status'] == 'Shipped' ) ? 1 : 0;

    $sql = <<<_SQL_
INSERT INTO invoices_shipping_tracking SET INVOICEID={$orderId},
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
INSERT INTO invoices_details SET INVOICEID={$orderId},
  INVENTORYID={$inventoryId},
  DESCRIPTION='{$item['item_description']}',
  QUANTITY={$item['quantity']},
  RATE={$item['unit_price']},
  LINE_TOTAL={$item['item_total']},
  PARENT_INVENTORYID=1, NAME='foo',  UNIT='unit', PROMOCODEID=321
_SQL_;

    self::write( $sql );
    return self::lastId();
  }

  public static function insertInventory( $item ) {
    $sql = <<<_SQL_
INSERT INTO inventory SET SKU='{$item['item_code']}',
  CONTENTID=123, PARENT_INVENTORYID=10, RETAIL_PRICE=2.00, OUR_PRICE=1.00,
  SHIPPING_WEIGHT=10.0, ADDITIONAL_SHIPPING=1.00, LABEL='foo',
  CONNECTION='something'
_SQL_;

    self::write( $sql );
    return self::lastId();
  }

  public static function truncateAll() {
    $tables = array(
      'invoices',
      'invoices_activity',
      'invoices_details',
      'invoices_shipping_tracking',
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