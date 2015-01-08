<?php

namespace Data;

class OrderModel implements OrderProvider {
  protected $db;

  public function __construct() {
    $this->db = DB::get();
  }

  public function getNewOrders( $options ) {
    if( $options['start_date'] ) {
      $whereClause = "invoices.CREATED > '{$options['start_date']}'";
    } elseif( $options['start_id'] ) {
      $whereClause = "invoices.ID > {$options['start_id']}";
    } elseif( $options['num_days'] ) {
      $startDate = new \DateTime();
      $days = new \DateInterval("P{$options['num_days']}D");
      $startDate->sub( $days );
      $formatted = $startDate->format('Y-m-d H:i:s');
      $whereClause = "invoices.CREATED > '{$formatted}'";
    } else {
      $whereClause = "invoices.ID > 0";
    }

    $sql = <<<_SQL_
SELECT invoices.*, card.PAYMENT_TYPE, ship.SHIPPED,
    ship.CARRIER, ship.SHIPPING_METHOD, ship.TRACKING_NUMBER, ship.SHIPPED_DATE
  FROM invoices
  LEFT JOIN invoices_shipping_tracking AS ship ON (invoices.ID = ship.INVOICEID)
  LEFT JOIN invoices_activity AS card ON (invoices.ID = card.INVOICEID)
  WHERE {$whereClause}
  LIMIT {$options['limit']}
_SQL_;

    $results = $this->db->read( $sql );
    // var_dump($results);

    $structuredOrders = array();
    foreach( $results as $order ) {
      $order['items'] = $this->getOrderItems( $order );
      $structuredOrders[] = $this->structureOrderData( $order );
    }

    return $structuredOrders;
  }

  protected function getOrderItems( $order ) {
    $sql = <<<_SQL_
SELECT invoices_details.*, inventory.SKU FROM invoices_details
  LEFT JOIN inventory ON (invoices_details.INVENTORYID = inventory.ID)
  WHERE INVOICEID = {$order['ID']}
_SQL_;

    return $this->db->read( $sql );
  }

  protected function structureOrderData( $data ) {
    $dateTime = new \DateTime( $data['CREATED'] );
    $updatedOnDateTime = new \DateTime( $data['LASTUPDATED'] );

    $order = array(
      'order_id'            => $data['ID'],
      'provider_order_ref'  => $data['ID'],
      'transaction_type'    => 'Sale',
      'date'                => $dateTime->format('Y-m-d'),
      'time'                => $dateTime->format('H:i:s'),
      'time_zone'           => $dateTime->format('T'),
      'updated_on'          => $updatedOnDateTime->format('Y-m-d H:i:s'),
      'bill' => array(
        'pay_method'        => $data['PAYMENT_TYPE'],
        'pay_status'        => $data['PAYSTATUS'],
        'pay_date'          => $data['PAID_DATETIME'],
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
        'po_number'         => $data['PONUMBER'],
        // No credit card info for now.
      ),
      'ship' => array(
        'ship_status'         => $data['SHIPPED'],
        'ship_carrier_name'   => $data['CARRIER'],
        'ship_method'         => $data['SHIPPING_METHOD'],
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
        'handling'            => 0, // TODO ?
        'tax'                 => $data['TAX'],
        'discount'            => 0, // TODO ?
        'total'               => $data['TOTAL'],
        'item_sub_total'      => $data['SUBTOTAL'],
        // no coupon data for now
      ),
      'order_items' => array(),
    );

    foreach( $data['items'] as $item ) {
      $order['order_items'][] = array(
        'item_code'           => $item['SKU'], // TODO ?
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