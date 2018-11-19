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

  public function testGetShippingCarrierAndMethodCaseInsensitive() {
    $this->assertEquals(
      ['FedEx', 'Express'],
      $this->model->getShippingCarrierAndMethod([
        'SHIPPING_METHOD' => 'FEDEX Express',
      ])
    );
  }

  public function testGetShippingCarrierAndMethodWithOverride() {
    // TODO Honor UPDATED_CARRIER/UPDATED_SHIPPING_METHOD separately?
    $this->assertEquals(
      ['UPS', 'Express'],
      $this->model->getShippingCarrierAndMethod([
        'UPDATED_SHIPPING_METHOD' => 'UPS Express',
        'SHIPPING_METHOD'         => 'TOTALLY BOGUS',
      ])
    );
  }

  public function testGetNewOrdersQueryDefault() {
    $this->assertEquals([
      'bindings' => [
        ':limit' => 10,
      ],
      'where'    => 'invoices.ID > 0',
    ], $this->model->getNewOrdersQuery(['limit' => 10]));
  }

  public function testGetNewOrdersQueryWithStartDate() {
    $this->assertEquals([
      'bindings'      => [
        ':limit'      => 10,
        ':start_date' => '2019-01-01',
      ],
      'where'         => 'invoices.CREATED > :start_date',
    ], $this->model->getNewOrdersQuery(['limit' => 10, 'start_date' => '2019-01-01']));
  }

  public function testGetNewOrdersQueryWithStartId() {
    $this->assertEquals([
      'bindings'    => [
        ':limit'    => 10,
        ':start_id' => 123,
      ],
      'where'       => 'invoices.ID > :start_id',
    ], $this->model->getNewOrdersQuery(['limit' => 10, 'start_id' => 123]));
  }

  public function testGetNewOrdersQueryWithNumDays() {
    $this->assertEquals([
      'bindings' => [
        ':limit' => 10,
        ':days'  => 3,
      ],
      'where'    => 'invoices.CREATED > DATE_SUB( CURDATE(), INTERVAL :days DAY )',
    ], $this->model->getNewOrdersQuery(['limit' => 10, 'num_days' => 3]));
  }
}
