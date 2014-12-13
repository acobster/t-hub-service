<?php

namespace THub;

use THub\AuthException;

/**
 * A Library that implements the T-HUB Service Specifications as described in:
 *   http://www.atandra.com/downloads/THUB_Service_Spec_43.pdf
 *
 * Example usage:
 *
 *  THub\THubService::config( array(
 *    'libDir' => 'path/to/lib',
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

  protected static $config = array(
    'libDir' => './lib/',
    'viewDir' => './views/'
  );

  protected static $validCommands = array(
    'GetOrders',
    'UpdateOrdersShippingStatus',
    'UpdateInventory',
  );

  /**
   * Configure the THub Service.
   * NOTE: It's probably a good idea to use absolute paths in production.
   * TRAILING SLASHES ARE REQUIRED FOR DIRECTORY SETTINGS.
   * @param  array $settings array of settings
   */
  public static function config( $settings ) {
    foreach( self::$config as $key => $setting ) {
      if( !empty($settings[$key]) ) {
        self::$config[$key] = $setting;
      }
    }
  }

  public function __construct( $provider ) {
    $this->provider = $provider;
  }

  /**
   * [parseRequest description]
   * @param  [type] $requestXml
   * @throws  If [this condition is met]
   * @return [type]
   */
  public function parseRequest( $requestXml ) {
    $request = new \SimpleXMLElement( $requestXml );
    // throw an exception if unable to authenticate
    if( ! $this->authenticate(
        $request->UserID,
        $request->Password,
        $request->SecurityKey
      )) {
      throw new AuthException('Unable to authenticate');
    }
    $command = $request->Envelope->Command;

    if( in_array($command, self::$validCommands) ) {
      $method = "action{$command}";
      return $this->$method( $request );
    } else {
      return '';
    }
  }

  public function actionGetOrders( $request ) {
    $newOrders = $this->provider->getNewOrders();
    return $this->renderView( 'get_orders' );
  }

  protected function renderView( $name ) {
    $file = self::$config['viewDir'] . "thub/{$name}.php";

    ob_start();
    if( file_exists($file) ) {
      include($file);
    } else {
      throw new \RuntimeException("File not found: {$file}");
    }
    return ob_get_clean();
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