--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ACCOUNTID` int(11) NOT NULL,
  `ORDER_NUMBER` varchar(60) NOT NULL,
  `QUICKBOOKS_ORDERID` int(10) unsigned DEFAULT NULL,
  `DATE` date NOT NULL,
  `FIRST` varchar(32) NOT NULL,
  `LAST` varchar(32) NOT NULL,
  `ORGANIZATION` varchar(100) NOT NULL,
  `ADDRESS` varchar(100) NOT NULL,
  `ADDRESS2` varchar(100) NOT NULL,
  `CITY` char(32) NOT NULL,
  `STATE` char(32) NOT NULL,
  `ZIP` varchar(10) NOT NULL,
  `COUNTRY` char(20) NOT NULL,
  `SHIPPING_FIRST` varchar(32) NOT NULL,
  `SHIPPING_LAST` varchar(32) NOT NULL,
  `SHIPPING_ORGANIZATION` varchar(100) NOT NULL,
  `SHIPPING_ADDRESS` varchar(100) NOT NULL,
  `SHIPPING_ADDRESS2` varchar(100) NOT NULL,
  `SHIPPING_CITY` varchar(60) NOT NULL,
  `SHIPPING_STATE` varchar(32) NOT NULL,
  `SHIPPING_ZIP` varchar(10) NOT NULL,
  `SHIPPING_COUNTRY` varchar(60) NOT NULL,
  `SHIPPING_METHOD` varchar(60) NOT NULL,
  `PHONE` varchar(20) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `SUBTOTAL` decimal(10,2) NOT NULL,
  `TAX` decimal(10,2) NOT NULL,
  `TAX_RATE` double NOT NULL,
  `TAX_CODE` varchar(32) NOT NULL,
  `SHIPPING` decimal(10,2) NOT NULL,
  `TOTAL` decimal(10,2) NOT NULL,
  `USE_SHIPPING_ACCOUNT` tinyint(1) NOT NULL DEFAULT '0',
  `SHIPPING_ACCOUNT_CARRIER` varchar(32) NOT NULL,
  `SHIPPING_ACCOUNT_NUMBER` varchar(32) NOT NULL,
  `SHIPPING_ACCOUNT_METHOD` varchar(32) NOT NULL,
  `PAYSTATUS` enum('Cleared','Pending') NOT NULL,
  `PAYMENT_TYPE` enum('Credit Card','PayPal') NOT NULL,
  `TRANSACTIONID` varchar(60) NOT NULL,
  `CARD_TYPE` enum('Visa','Mastercard','Discover','American Express') NOT NULL,
  `CARD_LAST4` varchar(4) NOT NULL,
  `COMMENTS` text NOT NULL,
  `FULFILLED` tinyint(1) NOT NULL,
  `LASTUPDATED` datetime NOT NULL,
  `CREATED` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ACCOUNTID` (`ACCOUNTID`),
  KEY `PAYSTATUS` (`PAYSTATUS`),
  KEY `ORDER_NUMBER` (`ORDER_NUMBER`),
  KEY `QUICKBOOKS_ORDERID` (`QUICKBOOKS_ORDERID`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_details`
--

DROP TABLE IF EXISTS `orders_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_details` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ORDERID` int(11) NOT NULL,
  `INVENTORYID` int(11) NOT NULL,
  `NAME` varchar(200) NOT NULL,
  `DESCRIPTION` text NOT NULL,
  `QUANTITY` double NOT NULL,
  `RATE` decimal(10,2) NOT NULL,
  `TAXABLE` tinyint(1) NOT NULL DEFAULT '1',
  `UNIT` varchar(32) NOT NULL,
  `LINE_TOTAL` decimal(10,2) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ORDERID` (`ORDERID`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders_shipping_tracking`
--

DROP TABLE IF EXISTS `orders_shipping_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_shipping_tracking` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ORDERID` int(11) NOT NULL,
  `CARRIER` varchar(32) NOT NULL,
  `SHIPPING_METHOD` varchar(60) NOT NULL,
  `TRACKING_NUMBER` varchar(100) NOT NULL,
  `SHIPPED_EMAIL_NOTICE` tinyint(1) NOT NULL DEFAULT '0',
  `SHIPPED` tinyint(1) NOT NULL DEFAULT '0',
  `SHIPPED_DATE` date NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ORDERID` (`ORDERID`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CONTENTID` int(11) NOT NULL,
  `PRODUCT_CODE` varchar(100) NOT NULL,
  `DESCRIPTION` varchar(255) NOT NULL,
  `RETAIL_PRICE` decimal(10,2) NOT NULL,
  `OUR_PRICE` decimal(10,2) NOT NULL,
  `ALLOW_SALES` tinyint(1) NOT NULL DEFAULT '1',
  `TAXABLE` tinyint(1) NOT NULL DEFAULT '1',
  `SHIPPING_WEIGHT` double NOT NULL,
  `SHIPPING_LENGTH` varchar(32) NOT NULL DEFAULT '12',
  `SHIPPING_WIDTH` varchar(32) NOT NULL DEFAULT '12',
  `SHIPPING_HEIGHT` varchar(32) NOT NULL DEFAULT '6',
  `ADDITIONAL_SHIPPING` decimal(10,2) NOT NULL,
  `FREE_SHIP` tinyint(1) NOT NULL DEFAULT '0',
  `INDIVIDUAL_SHIPPING` tinyint(1) NOT NULL DEFAULT '0',
  `MANUFACTURER` varchar(60) NOT NULL,
  `CONNECTION` varchar(32) NOT NULL,
  `CONNECTION2` varchar(32) NOT NULL,
  `PIN` char(32) NOT NULL,
  `COLOR` varchar(32) NOT NULL,
  `TUBE_COLOR` varchar(32) NOT NULL,
  `EAR_SIDE` char(10) NOT NULL,
  `HAS_PTT` tinyint(1) NOT NULL DEFAULT '1',
  `PTT` enum('','No Spare PTT','Ring','Barrel') NOT NULL,
  `SIZE` char(32) NOT NULL,
  `BOOM` varchar(100) NOT NULL,
  `QUANTITY` int(11) NOT NULL DEFAULT '1',
  `PRODUCT_TYPE` varchar(32) NOT NULL,
  `WIRE_COUNT` varchar(32) NOT NULL,
  `EAR_PIECE_STYLE` varchar(32) NOT NULL,
  `CASE_TYPE` varchar(32) NOT NULL,
  `HEADSET_TYPE` enum('Noise Cancelling','Lightweight','Tactical') NOT NULL,
  `RADIO_CONNECTOR_CHART` tinyint(1) NOT NULL DEFAULT '1',
  `UPSELLID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `MANUFACTURER` (`MANUFACTURER`),
  KEY `CONTENTID` (`CONTENTID`)
) ENGINE=MyISAM AUTO_INCREMENT=3792 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


