<?php

namespace THub;

use THub\AuthException;
use THub\BadRequestException;

/**
 * A Library that implements the T-HUB Service Specifications as described in:
 *   http://www.atandra.com/downloads/THUB_Service_Spec_43.pdf
 *
 * Example usage:
 *
 *  THub\THubService::config( array(
 *    'viewDir' => 'path/to/views'
 *  ));
 *
 *  $thub = new THub\THubService( $dataProvider );
 *  $orders = $thub->parseRequest( $_POST['XML'] );
 *
 *   // do something with $orders
 */
class THubService {
  const DEFAULT_LIMIT_ORDER_COUNT = 25;
  const DEFAULT_NUM_DAYS = 0;

  const STATUS_CODE_OK = '0';
  const STATUS_CODE_NO_ORDERS = '1000';

  const STATUS_MESSAGE_OK = 'All Ok';
  const STATUS_MESSAGE_NO_ORDERS = 'No new orders';

  const PROVIDER_GENERIC = 'GENERIC';

  const TYPE_SALE = 'Sale';
  const TYPE_RETURN = 'Return';

  const COMMAND_GET_ORDERS              = 'GetOrders';
  const COMMAND_UPDATE_SHIPPING_STATUS  = 'UpdateOrdersShippingStatus';
  const COMMAND_UPDATE_INVENTORY        = 'UpdateInventory';

  protected static $CONFIG = array(
    'viewDir'         => './views/',
    'user'            => 'user',
    'password'        => 'password',
    'securityKey'     => 'xyz',
    'requireKey'      => true,
  );

  protected static $validCommands = array(
    self::COMMAND_GET_ORDERS,
    self::COMMAND_UPDATE_SHIPPING_STATUS,
    self::COMMAND_UPDATE_INVENTORY,
  );

  protected $thirdPartyProvider = self::PROVIDER_GENERIC;

  /**
   * Configure the THub Service.
   * NOTE: It's probably a good idea to use absolute paths in production.
   * TRAILING SLASHES ARE REQUIRED FOR DIRECTORY SETTINGS.
   * @param  array $settings array of settings
   */
  public static function config( $settings ) {
    foreach( self::$CONFIG as $key => $setting ) {
      if( !empty($settings[$key]) ) {
        self::$CONFIG[$key] = $setting;
      }
    }
  }

  public function __construct( $orderProvider ) {
    $this->orderProvider = $orderProvider;
  }

  /**
   * [parseRequest description]
   * @param  [type] $requestXml
   * @throws  If [this condition is met]
   * @return [type]
   */
  public function parseRequest( $requestXml ) {
    // catch & throw more specific exception if XML is bad
    try {
      $request = new \SimpleXMLElement( $requestXml );
    } catch( \Exception $e ) {
      throw new BadRequestException( $e->getMessage() );
    }

    // throw an exception if unable to authenticate
    if( ! $this->authenticate(
        $request->UserID,
        $request->Password,
        $request->SecurityKey
      )) {
      throw new AuthException('Unable to authenticate');
    }

    $command = $request->Command;
    if( ! $this->isValidCommand($command) ) {
      throw new BadRequestException( "No such command: {$command}" );
    }

    $method = "action{$command}";
    return $this->$method( $request );
  }

  public function actionGetOrders( $request ) {
    $this->orders   = $this->orderProvider->getNewOrders();
    $this->command  = self:: COMMAND_GET_ORDERS;

    if( empty($this->orders) ) {
      $this->statusCode     = self::STATUS_CODE_NO_ORDERS;
      $this->statusMessage  = self::STATUS_MESSAGE_NO_ORDERS;
    } else {
      $this->statusCode     = self::STATUS_CODE_OK;
      $this->statusMessage  = self::STATUS_MESSAGE_OK;
    }

    return $this->renderView( 'response' );
  }

  protected function renderView( $name ) {
    $file = self::$CONFIG['viewDir'] . "thub/{$name}.php";

    ob_start();
    if( file_exists($file) ) {
      include($file);
    } else {
      throw new \RuntimeException("File not found: {$file}");
    }
    return ob_get_clean();
  }

  protected function authenticate( $user, $pw, $securityKey ) {
    $valid =    $user == self::$CONFIG['user']
             && $pw   == self::$CONFIG['password'];

    if( $valid && self::$CONFIG['requireKey'] ) {
      $valid = self::$CONFIG['securityKey'] == $securityKey;
    }

    return $valid;
  }

  protected function isValidCommand( $command ) {
    return in_array( $command, self::$validCommands );
  }

  /**
   * Decode and return the value of a SimpleXMLElement.
   * If the element is unencoded, simply return it.
   * @param  SimpleXMLElement $simpleXml
   * @return SimpleXMLElement the decoded element
   */
  protected function getDecodedValue( $simpleXml ) {
    return $simpleXml['encoding'] == 'yes'
      ? base64_decode($simpleXml)
      : $simpleXml;
  }
}

?>