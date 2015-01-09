<?php

namespace THub;

use Data\OrderProvider as OrderProvider;

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
  const DEFAULT_START_ORDER_ID = 0;

  const STATUS_CODE_OK              = '0';
  const STATUS_CODE_NO_ORDERS       = '1000';
  const STATUS_CODE_LOGIN_FAILURE   = '9000';
  const STATUS_CODE_OTHER           = '9999';

  const STATUS_MESSAGE_OK             = 'All Ok';
  const STATUS_MESSAGE_NO_ORDERS      = 'No new orders';
  const STATUS_MESSAGE_LOGIN_FAILURE  = 'Login failed';
  const STATUS_MESSAGE_NO_ORDERS_RECEIVED = 'No orders were specificed in the update';

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
  public static function config( $settings=array() ) {
    foreach( self::$CONFIG as $key => $setting ) {
      if( isset($settings[$key]) ) {
        self::$CONFIG[$key] = $settings[$key];
      }
    }
    return self::$CONFIG;
  }

  /**
   * Constructor.
   * @param OrderProvider $orderProvider An instance of the OrderProvider
   * interface, so we have access to the order data/
   */
  public function __construct( OrderProvider $orderProvider ) {
    $this->orderProvider = $orderProvider;
  }

  /**
   * Parse the XML request from Atandra and send them XML
   * @param  string $requestXml the XML from Atandra
   * @return string the XML to send back
   */
  public function parseRequest( $requestXml ) {
    try {
      $request = new \SimpleXMLElement( $requestXml );
    } catch( \Exception $e ) {
      return $this->renderError( $e->getMessage() );
    }

    try {
      $this->validateRequest( $request );

      $this->authenticate(
        $request->UserID,
        $request->Password,
        $request->SecurityKey
      );

      $method = "render{$this->command}";
      return $this->$method( $request );

    } catch( AuthError $e ) {
      return $this->renderLoginFailure();

    } catch( InvalidParamError $e ) {
      return $this->renderError( $e->getMessage() );
    }
  }

  /**
   * Respond to the GetOrders command with XML
   * @param  SimpleXMLElement $request the parsed XML from Atandra
   * @return string GetOrders XML
   */
  public function renderGetOrders( $request ) {
    $this->command  = self::COMMAND_GET_ORDERS;

    try {
      $queryOptions   = $this->getQueryOptions( $request );
      $this->orders   = $this->orderProvider->getNewOrders( $queryOptions );
    } catch( \PDOException $e ) {
      return $this->renderError(
        'There was a database error. Please contact the website administrator.'
      );
    }

    if( $this->orders ) {
      $this->statusCode     = self::STATUS_CODE_OK;
      $this->statusMessage  = self::STATUS_MESSAGE_OK;
    } else {
      $this->statusCode     = self::STATUS_CODE_NO_ORDERS;
      $this->statusMessage  = self::STATUS_MESSAGE_NO_ORDERS;
    }

    return $this->renderView( 'response' );
  }

  /**
   * Respond to the UpdateOrdersShippingStatus command with XML
   * @param  SimpleXMLElement $request the parsed XML from Atandra
   * @return string UpdateOrdersShippingStatus XML
   */
  public function renderUpdateOrdersShippingStatus( $request ) {
    $this->command = self::COMMAND_UPDATE_SHIPPING_STATUS;

    $requestedOrders = $request->Orders;

    if( $requestedOrders && $requestedOrders->children() ) {
      $orders = $this->getOrdersFromXml( $requestedOrders );
      // $this->orderProvider->updateOrders( $orders );
    } else {
      $this->statusCode = self::STATUS_CODE_OTHER;
      $this->statusMessage = self::STATUS_MESSAGE_NO_ORDERS_RECEIVED;
    }

    return $this->renderView( 'response' );
  }

  /**
   * This method takes the query approach recommended in the THub Service Spec:
   *   - Default to 25-order limit
   *   - Use DownloadStartDate to download historical data starting with a
   *     specific order date
   *   - The service should return all orders whose Order number in database >
   *     OrderStartNumber; default OrderStartNumber is 0
   *   - When OrderStartNumber = 0 then use the value sent in NumberOfDays to
   *     return last x days orders as specified by NumberOfDays parameter.
   *   - When OrderStartNumber > 0 then ignore the NumberOfDays parameter and
   *     send orders based on OrderNumber >= OrderStartNumber
   * @param  SimpleXMLElement $request the parse XML from Atandra
   * @return array an array of options to pass to OrderProvider::getNewOrders()
   */
  protected function getQueryOptions( $request ) {
    $options = array(
      'limit'       => self::DEFAULT_LIMIT_ORDER_COUNT,
      'start_id'    => self::DEFAULT_START_ORDER_ID,
    );

    if( $request->DownloadStartDate ) {
      $date = new \DateTime( $request->DownloadStartDate );
      $options['start_date'] = $date->format('Y-m-d H:i:s');
    } elseif( intval($request->OrderStartNumber) ) {
      $options['start_id'] = intval( $request->OrderStartNumber );
    } elseif( intval($request->NumberOfDays) ) {
      $options['num_days'] = intval( $request->NumberOfDays );
    }

    return $options;
  }

  protected function getOrdersFromXml( $xml ) {
    $orders = array();
    foreach( $xml->children() as $orderXml ) {
      $order = array(
        'host_order_id'     => $orderXml->HostOrderID,
        'local_order_id'    => $orderXml->LocalOrderID,
        'shipped_on'        => $orderXml->ShippedOn,
        'shipped_via'       => $orderXml->ShippedVia,
        'tracking_number'   => $orderXml->TrackingNumber,
      );

      if( !empty($orderXml->NotifyCustomer) ) {
        $order['notify_customer'] = $orderXml->NotifyCustomer;
      }
      if( !empty($orderXml->ServiceUsed) ) {
        $order['service_used'] = $orderXml->ServiceUsed;
      }

      $orders[] = $order;
    }

    return $orders;
  }

  /**
   * Report a bad request, e.g. malformed XML, back to Atandra
   * @param  string $message the StatusMessage to report
   * @return string the XML to send back
   */
  protected function renderError( $message ) {
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
   * Throw an error if request is invalid.
   * @param  SimpleXMLElement $request the XML from Atandra
   * @throws InvalidParamError if any of the request params is invalid
   */
  protected function validateRequest( $request ) {
    // determine command
    $this->command = $request->Command;
    if( ! $this->isValidCommand($this->command) ) {
      throw new InvalidParamError( "No such command: {$this->command}" );
    }

    $startDate = $request->DownloadStartDate;
    if( $startDate && !$this->isValidDate( $startDate )) {
      throw new InvalidParamError( 'Invalid DownloadStartDate' );
    }

    $intParams = array(
      'NumberOfDays',
      'LimitOrderCount',
      'OrderStartNumber',
    );

    foreach( $intParams as $param ) {
      $val = $request->$param;
      if( $val && filter_var($val, FILTER_VALIDATE_INT) === false ) {
        throw new InvalidParamError( "Invalid $param" );
      }
    }
  }

  /**
   * Validate a date string.
   * @param  string $date
   * @return boolean whether $date is a valid date string
   */
  protected function isValidDate( $date, $format='m/d/Y H:i:s A' ) {
    return \DateTime::createFromFormat( $format, $date ) !== false;
  }

  /**
   * Check provided user/password against current configuration.
   * If CONFIG[requireKey] is set to true, this will check securityKey as well;
   * otherwise securityKey will be ignored.
   * @param  string $user the UserID passed in by Atandra
   * @param  string $pw the Password passed in by Atandra
   * @param  string $securityKey the SecurityKey passed in by Atandra
   * @throws THub\AuthError if authentication fails
   */
  protected function authenticate( $user, $pw, $securityKey ) {
    $valid  = $user == self::$CONFIG['user']
            && $pw == self::$CONFIG['password'];

    if( $valid && self::$CONFIG['requireKey'] ) {
      $valid = self::$CONFIG['securityKey'] == $securityKey;
    }

    if( ! $valid ) {
      throw new AuthError( 'Authentication failed' );
    }

    return true;
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