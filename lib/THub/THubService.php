<?php

namespace THub;

use \OrderProvider;

/**
 * A Library that implements the T-HUB Service Specifications as described in:
 *   http://www.atandra.com/downloads/THUB_Service_Spec_43.pdf
 *
 * Example usage:
 *
 *  THub\THubService::config( array(
 *    'viewDir' => 'path/to/views',
 *    'user'    => 'productionUserID',
 *    'user'    => 'productionPass',
 *    ...
 *  ));
 *
 *  $thub = new THub\THubService( $dataProvider );
 *  $orders = $thub->parseRequest( $_POST['XML'] );
 *
 *   // do something with $orders
 *
 * An THubService object should be instantiated with an instance of the
 * OrderProvider interface to ensure that THubService has access to the data it
 * needs.
 */
class THubService {
  const DEFAULT_LIMIT_ORDER_COUNT = 25;
  const DEFAULT_NUM_DAYS = 0;

  const STATUS_CODE_OK              = '0';
  const STATUS_CODE_NO_ORDERS       = '1000';
  const STATUS_CODE_LOGIN_FAILURE   = '9000';
  const STATUS_CODE_OTHER           = '9999';

  const STATUS_MESSAGE_OK             = 'All Ok';
  const STATUS_MESSAGE_NO_ORDERS      = 'No new orders';
  const STATUS_MESSAGE_LOGIN_FAILURE  = 'Login failed';

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

  // Set up default view variables...
  protected $thirdPartyProvider = self::PROVIDER_GENERIC;
  protected $command = 'UNKNOWN';

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

  /**
   * Constructor.
   * @param OrderProvider $orderProvider An instance of the OrderProvider
   * interface, so we have access to the order data/
   */
  public function __construct( \OrderProvider $orderProvider ) {
    $this->orderProvider = $orderProvider;
  }

  /**
   * Parse the XML request from Atandra and send them XML
   * @param  string $requestXml the XML from Atandra
   * @return string the XML to send back
   */
  public function parseRequest( $requestXml ) {
    // catch & throw more specific exception if XML is bad
    try {
      $request = new \SimpleXMLElement( $requestXml );
    } catch( \Exception $e ) {
      return $this->renderBadRequest( $e->getMessage() );
    }

    // determine command
    $this->command = $request->Command;
    if( ! $this->isValidCommand($this->command) ) {
      return $this->renderBadRequest( "No such command: {$this->command}" );
    }

    // throw an exception if unable to authenticate
    if( ! $this->authenticate(
        $request->UserID,
        $request->Password,
        $request->SecurityKey
      )) {
      return $this->renderLoginFailure();
    }

    $method = "render{$this->command}";
    return $this->$method( $request );
  }

  /**
   * Respond to the GetOrders command with XML
   * @param  string $request the XML from Atandra
   * @return string GetOrders XML
   */
  public function renderGetOrders( $request ) {
    $this->orders   = $this->orderProvider->getNewOrders();
    $this->command  = self::COMMAND_GET_ORDERS;

    if( empty($this->orders) ) {
      $this->statusCode     = self::STATUS_CODE_NO_ORDERS;
      $this->statusMessage  = self::STATUS_MESSAGE_NO_ORDERS;
    } else {
      $this->statusCode     = self::STATUS_CODE_OK;
      $this->statusMessage  = self::STATUS_MESSAGE_OK;
    }

    return $this->renderView( 'response' );
  }

  /**
   * Report a bad request, e.g. malformed XML, back to Atandra
   * @param  string $message the StatusMessage to report
   * @return string the XML to send back
   */
  protected function renderbadRequest( $message ) {
    $this->statusMessage = $message;
    $this->statusCode = self::STATUS_CODE_OTHER;
    return $this->renderView( 'response' );
  }

  /**
   * Report a login failure back to Atandra
   * @return string the XML to send back
   */
  protected function renderLoginFailure() {
    $this->statusMessage  = self::STATUS_MESSAGE_LOGIN_FAILURE;
    $this->statusCode     = self::STATUS_CODE_LOGIN_FAILURE;
    return $this->renderView( 'response' );
  }

  /**
   * Render an arbitrary view in the viewDir
   * @param  string $name the name of the view to render.
   * @return string the rendered view
   */
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

  /**
   * Check provided user/password against current configuration.
   * If CONFIG[requireKey] is set to true, this will check securityKey as well;
   * otherwise securityKey will be ignored.
   * @param  string $user the UserID passed in by Atandra
   * @param  string $pw the Password passed in by Atandra
   * @param  string $securityKey the SecurityKey passed in by Atandra
   * @return boolean true if credentials are valid.
   */
  protected function authenticate( $user, $pw, $securityKey ) {
    $valid  = $user == self::$CONFIG['user']
            && $pw == self::$CONFIG['password'];

    if( $valid && self::$CONFIG['requireKey'] ) {
      $valid = self::$CONFIG['securityKey'] == $securityKey;
    }

    return $valid;
  }

  /**
   * Whether Atandra sent over a valid Command or not. Cuz if they didn't.... :/
   * @param  string  $command the command in question
   * @return boolean true if valid, false otherwise
   */
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