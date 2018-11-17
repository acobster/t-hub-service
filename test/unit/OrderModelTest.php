<?php

use PHPUnit\Framework\TestCase;

require_once realpath(__DIR__.'/../../lib/Data/OrderProvider.php');
require_once realpath(__DIR__.'/../../lib/Data/OrderModel.php');

use Data\OrderModel;

/**
 * Test all the things
 * @group unit
 */
class OrderModelTest extends TestCase {
  public function setUp() {
    $this->db = $this->getMockBuilder('Data\DB')
      ->getMock();
    $this->model = new OrderModel($this->db);
  }

  public function testGetShippingCarrierAndMethod() {
    $this->assertEquals(
      ['FedEx', 'Ground'],
      $this->model->getShippingCarrierAndMethod([
        'SHIPPING_METHOD' => 'FedEx Ground',
      ])
    );

    $this->assertEquals(
      ['USPS', 'Priority'],
      $this->model->getShippingCarrierAndMethod([
        'SHIPPING_METHOD' => 'USPS Priority',
      ])
    );

    $this->assertEquals(
      ['UPS', 'Express'],
      $this->model->getShippingCarrierAndMethod([
        'SHIPPING_METHOD' => 'UPS Express',
      ])
    );
  }

  public function testGetShippingCarrierAndMethodWithOverride() {
    $this->assertEquals(
      ['UPS', 'Express'],
      $this->model->getShippingCarrierAndMethod([
        'UPDATED_SHIPPING_METHOD' => 'UPS Express',
        'SHIPPING_METHOD'         => 'TOTALLY BOGUS',
      ])
    );
  }
}
