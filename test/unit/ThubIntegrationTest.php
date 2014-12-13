<?php

require 'lib/thub/ThubService.php';

class THubServiceTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $this->mockProvider = $this->getMockBuilder('OrderModel')
      ->setMethods( array('getNewOrders') )
      ->getMock();
    $this->thub = new THub\THubService( $this->mockProvider );
  }

  public function testGetOrdersXmlWithEmptyArray() {
    $this->markTestSkipped();
    $this->mockProvider->method('getNewOrders')
      ->willReturn( array() );

    $xml = $this->thub->getOrdersXml();
    $this->assertInternalType( 'string', $xml );
    $this->assertEquals( $xml, self::GET_ORDERS_RESPONSE_XML );
  }

  public function testDecodeElement() {
    $simple = new SimpleXMLElement( self::BASE64_ENCODED_XML );

    $cases = array(
      'foo/bar' => $simple->Bar,
      'foo/qux/blub' => $simple->Qux->Blub,
      'plain' => $simple->Qux->Plain,
    );

    foreach( $cases as $expected => $element ) {
      $this->assertEquals(
        $expected,
        $this->callProtectedMethod( 'getDecodedValue', array($element) )
      );
    }
  }

  protected function callProtectedMethod( $name, $params=array() ) {
    $reflection = new ReflectionClass( 'THub\THubService' );
    $method = $reflection->getMethod( $name );
    $method->setAccessible( true );

    return $method->invokeArgs( $this->thub, $params );
  }

  const BASE64_ENCODED_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<Foo>
  <Bar encoding="yes">Zm9vL2Jhcg==</Bar>
  <Qux arbitrary="attribute">
    <Blub encoding="yes">Zm9vL3F1eC9ibHVi</Blub>
    <Plain>plain</Plain>
  </Qux>
</Foo>
_XML_;

  const GET_ORDERS_REQUEST_XML = <<<_XML_
<?xml version="1.0" encoding="ISO-8859-1"?>
<REQUEST Version="2.8">
   <Command>GetOrders</Command>
   <UserID>myloginId</UserID>
   <Password>myPassword</Password>
   <Status>all</Status>
   <Provider>YAHOO</Provider>
   <LimitOrderCount>25</LimitOrderCount>
   <OrderStartNumber>1066</OrderStartNumber>
   <NumberOfDays>5</NumberOfDays>
   <DownloadStartDate>4/8/2014 04:02:33 AM</DownloadStartDate>
   <SecurityKey>xyz</SecurityKey>
</REQUEST>
_XML_;

  const GET_ORDERS_RESPONSE_XML = <<<_XML_
<RESPONSE Version="2.8">
   <Envelope>
      <Command>GetOrders</Command>
      <StatusCode>0</StatusCode>
      <StatusMessage>All Ok</StatusMessage>
      <Provider>GENERIC</Provider>
   </Envelope>
   <Orders>
      <Order>
         <OrderID>34088</OrderID>
         <ProviderOrderRef>yst-9976</ProviderOrderRef>
         <Date>2010-03-05</Date>
         <Time>11:22:33</Time>
         <TimeZone>PST</TimeZone>
         <UpdatedOn>2010-02-21 18:13:40</UpdatedOn>
         <StoreID>133</StoreID>
         <StoreName>My Store</StoreName>
         <CustomerID>14467</CustomerID>
         <CustomerType>Online</CustomerType>
         <SalesRep>JON</SalesRep>
         <Comment>Please send this order in QuickBooks</Comment>
         <MerchantNotes>Put on expedited shipping as promised on phone</MerchantNotes>
         <Currency>USD</Currency>
         <Bill>ml

            <PayMethod>
               CreditCard</PayMethod>
            <PayStatus>Pending</PayStatus>
            <PayDate>2010-03-07</PayDate>
            <FirstName>John</FirstName>
            <LastName>Doe</LastName>
            <MiddleName>Smith</MiddleName>
            <CompanyName>ACE Hardware</CompanyName>
            <Address1>Suite B160-424</Address1>
            <Address2>22431 Antonio Parkway,</Address2>
            <City>Rancho Santa Margarita</City>
            <State>CA</State>
            <Zip>92688</Zip>
            <Country>USA</Country>
            <Email>me@somewhere.com</Email>
            <Phone>912-222-1111</Phone>
            <PONumber>PO123456</PONumber>
            <PaymentAmount>530.00</PaymentAmount>
            <CreditCard>
               <CreditCardType>VISA</CreditCardType>
               <CreditCardCharge>530.00</CreditCardCharge>
               <ExpirationDate>11/2008</ExpirationDate>
               <CreditCardName>John Smith Doe</CreditCardName>
               <CreditCardNumber>XXXXXXXXXXXXX3445</CreditCardNumber>
               <CVV2>345</CVV2>
               <AuthDetails>AuthCode=Q31234;TransId=4423412312;AVSCode=P</AuthDetails>
               <TransactionID>4423412312232312</TransactionID>
               <SettlementBatchID>3330115015</SettlementBatchID>
               <ReconciliationData>1223212323</ReconciliationData>
            </CreditCard>
         </Bill>
         <Ship>
            <ShipStatus>New/Shipped</ShipStatus>
            <ShipDate>2009-09-15</ShipDate>
            <Tracking>1Z1231231233231</Tracking>
            <ShipCost>3.45</ShipCost>
            <ShipCarrierName>Fedex</ShipCarrierName>
            <ShipMethod>Ground Shipping</ShipMethod>
            <FirstName>John</FirstName>
            <LastName>Doe</LastName>
            <MiddleName>Smith</MiddleName>
            <CompanyName>ACE Hardware</CompanyName>
            <Address1>Suite B160-424</Address1>
            <Address2>22431 Antonio Parkway,</Address2>
            <City>Rancho Santa Margarita </City>
            <State>CA</State>
            <Zip>92688</Zip>
            <Country>USA</Country>
            <Email>me@somewhere.com</Email>
            <Phone>912-222-1111</Phone>
         </Ship>
         <Items>
            <Item>
               <ItemCode>SKU1001</ItemCode>
               <ItemDescription>Leather Jacket</ItemDescription>
               <Quantity>1</Quantity>
               <UnitPrice>300.00</UnitPrice>
               <UnitCost>102.00</UnitCost>
               <Vendor>ACEShoes.com</Vendor>
               <ItemTotal>300.00</ItemTotal>
               <ItemUnitWeight>2.5</ItemUnitWeight>
               <Length>2.5</Length>
               <Depth>2.5</Depth>
               <Height>2.5</Height>
               <CustomField1/>
               <CustomField2/>
               <CustomField3/>
               <CustomField4/>
               <CustomField5/>
               <ItemOptions>
                  <ItemOption Name="Color" Value="Black"/>
               </ItemOptions>
            </Item>
            <Item>
               <ItemCode>SKU8976</ItemCode>
               <ItemDescription>Leather Shoes</ItemDescription>
               <Quantity>1</Quantity>
               <UnitPrice>200.00</UnitPrice>
               <UnitCost>102.00</UnitCost>
               <Vendor>ACEShoes.com</Vendor>
               <ItemTotal>200.00</ItemTotal>
               <ItemUnitWeight>1</ItemUnitWeight>
               <CustomField1/>
               <CustomField2/>
               <CustomField3/>
               <CustomField4/>
               <CustomField5/>
               <ItemOptions>
                  <ItemOption Name="Color" Value="Brown"/>
               </ItemOptions>
            </Item>
         </Items>
         <Charges>
            <Shipping>50.00</Shipping>
            <Handling>0.00</Handling>
            <Tax Name="Sales Tax">10</Tax>
            <TaxOther>0</TaxOther>
            <ChannelFee>0.7</ChannelFee>
            <PaymentFee>0.3</PaymentFee>
            <FeeDetails>
               <FeeDetail>
                  <FeeName>Final Value Fee</FeeName>
                  <FeeValue>0.7</FeeValue>
               </FeeDetail>
               <FeeDetail>
                  <FeeName>PayPal Fee</FeeName>
                  <FeeValue>0.3</FeeValue>
               </FeeDetail>
            </FeeDetails>
            <Discount>20.00</Discount>
            <GiftCertificate Code="12345678">10.00</GiftCertificate>
            <OtherCharge Name="Gift Wrapping">1.00</OtherCharge>
            <Total>541.00</Total>
            <Coupons>
               <Coupon>
                  <CouponCode>MOTHERS DAY</CouponCode>
                  <CouponID>000232</CouponID>
                  <CouponDescription>MOTHERS DAY DISCOUNT </CouponDescription>
                  <CouponValue>20.00</CouponValue>
               </Coupon>
            </Coupons>
         </Charges>
         <CustomField1/>
         <CustomField2/>
         <CustomField3/>
         <CustomField4/>
         <CustomField5/>
      </Order>
   </Orders>
</RESPONSE>
_XML_;
}

?>