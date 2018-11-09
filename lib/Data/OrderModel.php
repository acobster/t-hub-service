<?php

namespace Data;

class OrderModel implements OrderProvider {
  const TRANSACTION_TYPE_SALE = 'Sale';
  const SHIP_STATUS_SHIPPED = 'Shipped';
  const SHIP_STATUS_NEW = 'New';

  const DEFAULT_HANDLING = 0.00;
  const DEFAULT_DISCOUNT = 0.00;

  protected $db;

  public function __construct() {
    $this->db = DB::get();
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
    (ship.ID IS NOT NULL) AS SHIPPED,
    ship.TRACKING_NUMBER,
    ship.CREATED,
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

  protected function updateOrder( $order ) {
    // TODO what happened to QUICKBOOKS_ORDERID?
    $sql = <<<_SQL_
UPDATE invoices SET
  LAST_UPDATED = NOW()
  WHERE ID = :id LIMIT 1
_SQL_;

    $bindings = array(
      ':id' =>  $order['host_order_id'],
    );

    if( ! $this->db->write( $sql, $bindings ) ) {
      throw new \Data\DBException( 'Database error' );
    }

    $this->updateShipping( $order );

    return array(
      'host_order_id'      => $order['host_order_id'],
      'local_order_id'      => $order['local_order_id'],
      'host_status'   => 'Success',
    );
  }

  protected function updateShipping( $order ) {
    $shipDate = new \DateTime( $order['shipped_on'], new \DateTimeZone('GST') );
    $formattedDate = $shipDate->format('Y-m-d');

    // TODO insert a new row; don't count on there being an old one
    $sql = <<<_SQL_
UPDATE invoices_shipping_tracking SET
  CREATED = :date,
  CARRIER = :carrier,
  SHIPPING_METHOD = :method,
  TRACKING_NUMBER = :tracking
  WHERE INVOICEID = :id LIMIT 1
_SQL_;

    if( ! $this->db->write( $sql, array(
      ':id'       => intval( $order['host_order_id'] ),
      ':date'     => $formattedDate,
      ':carrier'  => $order['shipped_via'],
      ':method'   => $order['service_used'],
      ':tracking' => $order['tracking_number'],
    ))) {
      throw new \Data\DBException( 'Database error' );
    }
  }

  protected function getOrderItems( $order ) {
    $sql = <<<_SQL_
SELECT invoices_details.*,
  inventory.PRODUCT_CODE
FROM invoices_details
LEFT JOIN content_products AS inventory
  ON (invoices_details.INVENTORYID = inventory.ID)
WHERE INVOICEID = :id
_SQL_;

    return $this->db->read( $sql, array(':id' => $order['ID']) );
  }

  protected function structureOrderData( $data ) {
    $dateTime = new \DateTime( $data['CREATED'] );
    $updatedOnDateTime = new \DateTime( $data['LAST_UPDATED'] );
    $payDateTime = new \DateTime( $data['PAID_DATETIME'] );

    if( $data['CREATED'] != '0000-00-00' && $data['CREATED'] != '1970-01-01' ) {
      $shipDate = new \DateTime( $data['CREATED'] );
      $shipDate = $shipDate->format('Y-m-d');
    }

    $shipStatus = $data['SHIPPED']
      ? self::SHIP_STATUS_SHIPPED
      : self::SHIP_STATUS_NEW;

    // "Credit Card" -> "CreditCard"
    $method = str_replace( ' ', '', $data['PAYMENT_TYPE'] );

    $order = array(
      'order_id'            => $data['ID'],
      'provider_order_ref'  => $data['ID'],
      'transaction_type'    => self::TRANSACTION_TYPE_SALE,
      'date'                => $dateTime->format('Y-m-d'),
      'time'                => $dateTime->format('H:i:s'),
      'time_zone'           => 'UTC',
      'updated_on'          => $updatedOnDateTime->format('Y-m-d H:i:s'),
      'bill' => array(
        'pay_method'        => $method,
        'pay_status'        => $data['PAYSTATUS'],
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
        'ship_carrier_name'   => $data['UPDATED_CARRIER'] ?: $data['CARRIER'],
        'ship_method'         => $data['UPDATED_SHIPPING_METHOD'] ?: $data['SHIPPING_METHOD'],

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
        'item_code'           => $item['PRODUCT_CODE'],
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
