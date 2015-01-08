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
    $bill     = $order['bill'];
    $ship     = $order['ship'];
    $charges  = $order['charges'];

    if( $ship['ship_date'] ) {
      $dt = new DateTime( $ship['ship_date'] );
      $shipDate = "'{$dt->format('Y-m-d')}'";
    } else {
      $shipDate = 'CURDATE()';
    }

    $createdDT = new DateTime( $order['date'] . ' ' . $order['time'] );
    $created = $createdDT->format( 'Y-m-d' );
    $updatedDT = new DateTime( $order['updated_on'] );
    $updated = $updatedDT->format( 'Y-m-d' );

    $sql = <<<_SQL_
INSERT INTO invoices SET INVOICE_NUMBER='9876',
  FIRST='{$bill['first_name']}',
  LAST='{$bill['last_name']}',
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
  SHIPPING_DATE={$shipDate},
  PHONE='{$ship['phone']}',
  EMAIL='{$ship['email']}',
  SUBTOTAL={$charges['item_sub_total']},
  TAX={$charges['tax']},
  SHIPPING={$charges['shipping']},
  TOTAL={$charges['total']},
  BALANCE=0.0, TAX_RATE=0.9, TAX_CODE='tax-code', TAX_CITY='Taxville',
  TAX_STATE='TX', TAX_COUNTRY='USA', COMMENTS='foo', PROMO_CODE='1235sdfg',
  PROMO_DESCRIPTION='everythingallofthetime', STATUSID=99, AFFILIATE_CONTACTID=5,
  FULFILLED=1, FULFILLED_ACCOUNTID=5, LOGINKEY='asdf', FOOTER_MESSAGE='hello',
  BOTTOM_MESSAGE='hello', PACKING_MESSAGE='hello', ACCOUNTID=234, DATE=CURDATE()
_SQL_;

    self::write( $sql );
    $orderId = self::lastId();

    foreach( $order['order_items'] as $item ) {
      self::insertOrderItem( $item, $orderId );
    }
  }

  public static function insertOrderItem( $item, $orderId ) {
      $order['order_items'][] = array(
        'item_code'           => $item['SKU'], // TODO ?
        'item_description'    => $item['DESCRIPTION'],
        'quantity'            => $item['QUANTITY'],
        'unit_price'          => $item['RATE'],
        'item_total'          => $item['LINE_TOTAL'],
      );

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
INSERT INTO inventory SET SKU='{$item['SKU']}',
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