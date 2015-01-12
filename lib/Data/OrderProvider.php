<?php

namespace Data;

interface OrderProvider {
  public function getNewOrders( $options );
  public function updateOrders( $orders );
}

?>