<?php

namespace Data;

class OrderModel implements OrderProvider {
  const TRANSACTION_TYPE_SALE = 'Sale';
  const SHIP_STATUS_SHIPPED = 'Shipped';
  const SHIP_STATUS_NEW = 'New';

  const DEFAULT_HANDLING = 0.00;
  const DEFAULT_DISCOUNT = 0.00;

  protected static $CARRIERS = [
    'USPS',
    'UPS',
    'FedEx',
  ];

  protected $db;

  public function __construct(DB $db) {
    $this->db = $db;
  }

  public function getNewOrders( $options ) {
    $bindings = array(
      ':limit' => intval($options['limit']),
    );

    if( $options['start_date'] ) {
      $bindings[':start_date'] = $options['start_date'];
      $whereClause = "invoices.CREATED > :start_date";

    } elseif( intval($options['start_id']) ) {
      $bindings[':start_id'] = $options['start_id'];
      $whereClause = "invoices.ID > :start_id";

    } elseif( $options['num_days'] ) {
      $bindings[':days'] = intval( $options['num_days'] );
      $whereClause = "invoices.CREATED > DATE_SUB( CURDATE(), INTERVAL :days DAY )";

    } else {
      $whereClause = "invoices.ID > 0";
    }

    $sql = <<<_SQL_
SELECT invoices.*,
    IF(invoices.STATUSID = '7', 'Cleared', 'Pending') AS PAY_STATUS,
    (ship.ID IS NOT NULL) AS SHIPPED,
    ship.TRACKING_NUMBER,
    ship.CREATED AS SHIPPED_DATE,
    ship.CARRIER AS UPDATED_CARRIER,
    ship.SHIPPING_METHOD AS UPDATED_SHIPPING_METHOD
  FROM invoices
  LEFT JOIN invoices_shipping_tracking AS ship ON (invoices.ID = ship.INVOICEID)
  WHERE {$whereClause}
  LIMIT :limit
_SQL_;

    $results = $this->db->read( $sql, $bindings );

    $structuredOrders = array();
    foreach( $results as $order ) {
      $order['items'] = $this->getOrderItems( $order );
      $structuredOrders[] = $this->structureOrderData( $order );
    }

    return $structuredOrders;
  }

  public function updateOrders( $orders ) {
    return array_map( array(&$this, 'updateOrder'), $orders );
  }

  public function getShippingCarrierAndMethod( $data ) {
    // TODO honor updated fields separately?
    // check shipping table, fallback to original order data to get
    // the shipping method, which includes carrier name
    // e.g. "FedEx Ground"
    $method = !empty($data['UPDATED_SHIPPING_METHOD'])
      ? $data['UPDATED_SHIPPING_METHOD']
      : $data['SHIPPING_METHOD'];

    foreach (self::$CARRIERS as $carrier) {
      if (stripos($method, $carrier) !== false) {
        return [
          // e.g. "FedEx" or "FEDEX"
          $carrier,
          // e.g. "Ground"
          trim(preg_split("/$carrier/i", $method)[1]),
        ];
      }
    }
  }

  protected function updateOrder( $order ) {
    $sql = <<<_SQL_
UPDATE invoices SET
  QUICKBOOKS_ORDERID = :quickbooks_id,
  LAST_UPDATED       = UTC_TIMESTAMP(),
  FULFILLED          = 1,
  FULFILLED_DATETIME = UTC_TIMESTAMP()
  WHERE ID = :id LIMIT 1
_SQL_;

    $bindings = array(
      ':id'            =>  $order['host_order_id'],
      ':quickbooks_id' =>  $order['local_order_id'],
    );

    if( ! $this->db->write( $sql, $bindings ) ) {
      throw new \Data\DBException( 'Database error' );
    }

    $this->updateShipping( $order );

    return array(
      'host_order_id'  => $order['host_order_id'],
      'local_order_id' => $order['local_order_id'],
      'host_status'    => 'Success',
    );
  }

  protected function updateShipping( $order ) {
    $sql = <<<_SQL_
INSERT INTO invoices_shipping_tracking SET
  CARRIER            = :carrier,
  SHIPPING_METHOD    = :method,
  TRACKING_NUMBER    = :tracking,
  INVOICEID          = :id,

  CREATED            = UTC_TIMESTAMP(),
  BOXID              = 0,
  PREDEFINED_TYPE    = 'custom',
  PREDEFINED_PACKAGE = '',
  WEIGHT             = 0,
  WEIGHT_UNIT        = 'pounds',
  LENGTH             = 0,
  WIDTH              = 0,
  HEIGHT             = 0,
  POSTAGE            = 0.00,
  POSTAGE_LINK       = ''
_SQL_;

    if( ! $this->db->write( $sql, array(
      ':id'       => intval( $order['host_order_id'] ),
      ':carrier'  => $order['shipped_via'],
      // TODO only do this in invoices?
      ':method'   => "{$order['shipped_via']} {$order['service_used']}",
      ':tracking' => $order['tracking_number'],
    ))) {
      throw new \Data\DBException( 'Database error' );
    }
  }

  protected function getOrderItems( $order ) {
    $sql = <<<_SQL_
SELECT invoices_details.*,
  inventory.SKU
FROM invoices_details
LEFT JOIN content_products AS inventory
  ON (invoices_details.PRODUCTID = inventory.ID)
WHERE INVOICEID = :id
_SQL_;

    return $this->db->read( $sql, array(':id' => $order['ID']) );
  }

  protected function structureOrderData( $data ) {
    $dateTime = new \DateTime( $data['CREATED'] );
    $updatedOnDateTime = new \DateTime( $data['LAST_UPDATED'] );
    $payDateTime = new \DateTime( $data['PAID_DATETIME'] );

    // determine shipping carrier and method
    // based on invoices.SHIPPING_METHOD value
    list($carrier, $shippingMethod) = $this->getShippingCarrierAndMethod( $data );

    if(
      !empty($data['SHIPPED_DATE'])
      && $data['SHIPPED_DATE'] != '0000-00-00'
      && $data['SHIPPED_DATE'] != '1970-01-01'
    ) {
      $shipDate = new \DateTime( $data['SHIPPED_DATE'] );
      $shipDate = $shipDate->format('Y-m-d');
    }

    $shipStatus = $data['SHIPPED']
      ? self::SHIP_STATUS_SHIPPED
      : self::SHIP_STATUS_NEW;

    // "Credit Card" -> "CreditCard"
    $paymentMethod = str_replace( ' ', '', $data['PAYMENT_TYPE'] );

    $order = array(
      'order_id'            => $data['ID'],
      'provider_order_ref'  => 'W' . $data['INVOICE_NUMBER'], // prepend "W" for web
      'transaction_type'    => self::TRANSACTION_TYPE_SALE,
      'date'                => $dateTime->format('Y-m-d'),
      'time'                => $dateTime->format('H:i:s'),
      'time_zone'           => 'UTC',
      'updated_on'          => $updatedOnDateTime->format('Y-m-d H:i:s'),
      'comment'             => $data['COMMENTS'],
      'bill' => array(
        'pay_method'        => $paymentMethod,
        'pay_status'        => $data['PAY_STATUS'],
        'pay_date'          => $payDateTime->format('Y-m-d'),
        'first_name'        => $data['FIRST'],
        'last_name'         => $data['LAST'],
        'company_name'      => $data['ORGANIZATION'],
        'address1'          => $data['ADDRESS'],
        'address2'          => $data['ADDRESS2'],
        'city'              => $data['CITY'],
        'state'             => $data['STATE'],
        'zip'               => $data['ZIP'],
        'country'           => $data['COUNTRY'],
        'email'             => $data['EMAIL'],
        'phone'             => $data['PHONE'],
        'po_number'         => $data['PO_NUMBER'],
        // No credit card info for now.
      ),
      'ship' => array(
        'ship_status'         => $shipStatus,
        'ship_date'           => $shipDate,

        // these can come from either orders or orders_shipping_tracking
        'ship_carrier_name'   => $carrier,
        'ship_method'         => $shippingMethod,

        'tracking'            => $data['TRACKING_NUMBER'],
        'ship_cost'           => $data['SHIPPING'],
        'first_name'          => $data['SHIPPING_FIRST'],
        'last_name'           => $data['SHIPPING_LAST'],
        'company_name'        => $data['SHIPPING_ORGANIZATION'],
        'address1'            => $data['SHIPPING_ADDRESS'],
        'address2'            => $data['SHIPPING_ADDRESS2'],
        'city'                => $data['SHIPPING_CITY'],
        'state'               => $data['SHIPPING_STATE'],
        'zip'                 => $data['SHIPPING_ZIP'],
        'country'             => $data['SHIPPING_COUNTRY'],
        'email'               => $data['EMAIL'],
        'phone'               => $data['PHONE'],
      ),
      'charges' => array(
        'shipping'            => $data['SHIPPING'],
        'handling'            => self::DEFAULT_HANDLING,
        'tax'                 => $data['TAX'],
        'discount'            => self::DEFAULT_DISCOUNT,
        'total'               => $data['TOTAL'],
        'item_sub_total'      => $data['SUBTOTAL'],
        // no coupon data for now
      ),
      'order_items' => array(),
    );

    foreach( $data['items'] as $item ) {
      $order['order_items'][] = array(
        'item_code'           => $item['SKU'],
        'item_description'    => $item['DESCRIPTION'],
        'quantity'            => $item['QUANTITY'],
        'unit_price'          => $item['RATE'],
        'item_total'          => $item['LINE_TOTAL'],
      );
    }

    return $order;
  }
}

?>
