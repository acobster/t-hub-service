<?php

require_once 'lib/Data/OrderProvider.php';
require_once 'lib/Data/OrderModel.php';
require_once 'lib/Data/DBException.php';
require_once 'lib/Data/DB.php';
require_once 'test/shared/TestData.php';
require_once 'tweb_config.php';

class OrderModelTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->model = new Data\OrderModel();
  }

  public function testGetNewOrders() {
    $orders = $this->model->getNewOrders(
      array('limit' => 25, 'start_id' => 0)
    );
    $this->assertEquals( array(), $orders );
  }
}

?>