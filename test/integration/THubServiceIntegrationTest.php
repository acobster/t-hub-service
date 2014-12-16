<?php

/**
 * Test the server response at a high level
 */
class THubServiceIntegrationTest extends PHPUnit_Framework_TestCase {
  public function testBadXml() {
    $response = $this->postTHub( 'foo' );
    $this->assertRegExp( '/400 Bad Request/', $response );
  }

  public function testAuthentication() {
    $cases = array(
      self::MISSING_PASSWORD_XML,
      self::BAD_PASSWORD_XML,
      self::MISSING_USER_XML,
      self::BAD_USER_XML,
      self::BAD_SECURITY_KEY_XML,
    );

    foreach( $cases as $xml ) {
      $response = $this->postTHub( $xml );
      $this->assertRegExp( '/401 Unauthorized/', $response );
    }
  }

  public function testBadCommandResponse() {
    $response = $this->postTHub( self::BAD_COMMAND_XML );
    $this->assertRegExp( '/400 Bad Request: No such command: FOO/', $response );
  }

  public function testMalformedXmlResponse() {
    $response = $this->postTHub( '<xml>bad xml</wtf>' );
    $this->assertRegExp( '/400 Bad Request: /', $response );
  }

  public function testNormalResponse() {
    $response = $this->postTHub( self::NORMAL_REQUEST_XML );
    $this->assertInternalType( 'string', $response );
  }

  protected function postTHub( $requestXml ) {
    $uri = 'http://' . WEB_SERVER_HOST . ':' . WEB_SERVER_PORT;
    $handle = curl_init( $uri );

    $options = array(
      CURLOPT_HEADER => 0,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => array( 'XML' => $requestXml ),
      CURLOPT_RETURNTRANSFER => true,
    );

    curl_setopt_array( $handle, $options );
    $out = curl_exec( $handle );

    if( $out === false ) {
      $error = curl_error( $handle );
      $this->fail( $error );
    }

    curl_close( $handle );
    return $out;
  }

  const NORMAL_REQUEST_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const BAD_COMMAND_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>FOO</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const BAD_PASSWORD_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>BAD</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const BAD_USER_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>BAD</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const BAD_SECURITY_KEY_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>BAD</SecurityKey>
</REQUEST>
_XML_;

  const MISSING_PASSWORD_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>user</UserID>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const MISSING_USER_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <Password>password</Password>
   <Status>all</Status>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;
}