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
    // TODO delete?
    if (empty($method)) die($method);

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

    // this can be whatever, just needs to be numeric
    $invoiceNum = rand();

    $sql = <<<_SQL_
INSERT INTO invoices SET {$setOrderId}
  INVOICE_NUMBER=$invoiceNum,
  FIRST='{$bill['first_name']}',
  LAST='{$bill['last_name']}',
  STATUSID='{$bill['statusid']}',
  PAID_DATETIME='{$bill['pay_date']}',
  CREATED='{$created}',
  LAST_UPDATED='{$updated}',
  ORGANIZATION='{$bill['company_name']}',
  ADDRESS='{$bill['address1']}',
  ADDRESS2='{$bill['address2']}',
  CITY='{$bill['city']}',
  STATE='{$bill['state']}',
  ZIP='{$bill['zip']}',
  COUNTRY='{$bill['country']}',
  PO_NUMBER='{$poNumber}',
  SHIPPING_FIRST='{$ship['first_name']}',
  SHIPPING_LAST='{$ship['last_name']}',
  SHIPPING_ORGANIZATION='{$ship['company_name']}',
  SHIPPING_ADDRESS='{$ship['address1']}',
  SHIPPING_ADDRESS2='{$ship['address2']}',
  SHIPPING_CITY='{$ship['city']}',
  SHIPPING_STATE='{$ship['state']}',
  SHIPPING_ZIP='{$ship['zip']}',
  SHIPPING_COUNTRY='{$ship['country']}',
  SHIPPING_CARRIER='',
  SHIPPING_METHOD='{$ship['ship_carrier_name']} {$ship['ship_method']}',
  SHIPPING_WEIGHT=10,
  SHIPPING_TRACKING='',
  SHIPPING_DATE=NOW(),
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
  COMMENTS='{$order['comment']}',
  PAYMENT_TYPE='$method',
  TRANSACTIONID='1234',
  CARD_TYPE='Visa',
  CARD_LAST4='1111',
  TAX_RATE=0.9,
  TAX_CODE='tax-code',
  TAX_ADDRESS='123 Fake St',
  TAX_CITY='Springfield',
  TAX_STATE='IL',
  TAX_ZIP='12345',
  TAX_COUNTRY='USA',
  OTHER_TAX=10.0,
  BALANCE=10.0,
  PROMO_CODE='#acmepromo',
  PROMO_DESCRIPTION='Hash Tag ACME Promo',
  LOGIN_KEY='user',
  INCENTIVE=666,
  INCENTIVE_EXPIRATION=NOW(),
  AFFILIATEID=567,
  AFFILIATE_COMMISSION=420,
  FULFILLED_DATETIME='1970-01-01 00:00:00',
  FULFILLED_USERID=345,
  FOOTER_MESSAGE='blah',
  BOTTOM_MESSAGE='blah blah',
  PACKING_MESSAGE='blah blah blah',
  ADMIN_NOTES='blah blah blah blah',
  ADMIN_NOTES_UPDATED=NOW(),
  SUBSCRIPTIONID=9876,
  LAST_EXPORT=CURDATE(),
  LAST_VIEWED=CURDATE(),
  LAST_PRINTED=CURDATE(),
  PAID_FULL=CURDATE(),
  FULFILLED=0,
  CONTACTID=5432,
  DATE=CURDATE(),
  DATE_DUE=CURDATE()
_SQL_;

    self::write( $sql );
    $orderId = self::lastId();

    // Don't INSERT shipping row for "New" orders
    if ( $order['ship']['ship_status'] === 'Shipped' ) {
      self::insertShipping( $order, $orderId );
    }

    foreach( $order['order_items'] as $item ) {
      self::insertOrderItem( $item, $orderId );
    }
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
INSERT INTO invoices_shipping_tracking SET INVOICEID={$orderId},
  CARRIER = '{$shipping['ship_carrier_name']}',
  SHIPPING_METHOD = '{$shipping['ship_carrier_name']} {$shipping['ship_method']}',
  TRACKING_NUMBER = '{$shipping['tracking']}',
  CREATED = '{$shipDate}',
  BOXID=1234,
  PREDEFINED_TYPE='parcel',
  PREDEFINED_PACKAGE='in a box',
  WEIGHT=3,
  WEIGHT_UNIT='pounds',
  LENGTH=10,
  WIDTH=10,
  HEIGHT=10,
  POSTAGE=10,
  POSTAGE_LINK=''
_SQL_;

    self::write( $sql );
  }

  public static function insertOrderItem( $item, $orderId ) {
    $inventoryId = self::insertInventory( $item, $orderId );

    $sql = <<<_SQL_
INSERT INTO invoices_details SET
  INVOICEID={$orderId},
  PRODUCTID={$inventoryId},
  SKU='{$item['item_code']}',
  NAME='foo',
  DESCRIPTION='{$item['item_description']}',
  QUANTITY={$item['quantity']},
  RATE={$item['unit_price']},
  LINE_TOTAL={$item['item_total']},
  DOWNLOADS_REMAINING=3,
  PROMOCODEID=3,
  MISC_RATE=3,
  MISC_RATE2=3,
  DESCRIPTION_OPTION='lorem ipsum dolor sit amet',
  UNIT='unit',
  SERVICEID=123
_SQL_;

    self::write( $sql );
    return self::lastId();
  }

  public static function insertInventory( $item ) {
    $sql = <<<_SQL_
INSERT INTO content_products SET
  SKU='{$item['item_code']}',
  CONTENTID=123,
  SHIPPING_WEIGHT=10.0,
  SHIPPING_VOLUME=20.0,
  SHIPPING_GIRTH=20.0,
  ADDITIONAL_SHIPPING_US=0,
  ADDITIONAL_SHIPPING_CANADA=0,
  ADDITIONAL_SHIPPING_INTERNATIONAL=0,
  LABEL='',
  SIZE_LABEL='',
  UNIT_LABEL='',
  SEQUENCE=0,
  DESCRIPTION_OPTION='',
  ORIGIN_COUNTRY='',
  LOW_STOCK_NOTICE_SENT=NOW(),
  MANUFACTURER='',
  CONNECTION='something',
  CONNECTION2='',
  PIN='',
  COLOR='',
  TUBE_COLOR='',
  EAR_SIDE='',
  PTT='',
  GTIN='',
  MPN='',
  COST=10.0,
  MISC_RATE=10.0,
  MISC_RATE2=10.0,
  RETAIL_RATE=10.0,
  SALE_RATE=10.0,
  SALE_START=CURDATE(),
  SALE_END=CURDATE(),
  STOCK=45,
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
      'invoices',
      'invoices_details',
      'invoices_shipping_tracking',
      'content_products',
    );

    foreach( $tables as $table ) {
      self::db()->exec( "TRUNCATE $table" );
    }
  }

  public static function read( $sql ) {
    $result = self::db()->query($sql, PDO::FETCH_ASSOC);
    if( $result === false ) {
      $info = self::db()->errorInfo();
      $message = sprintf( 'DB ERROR: %s (%s:%d)', $info[2], $info[0], $info[1] );
      throw new RuntimeException( $message );
    }
    return $result;
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
