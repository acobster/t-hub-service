<?= '<?xml version="1.0" encoding="ISO-8859-1"?>'.PHP_EOL ?>
<RESPONSE Version="2.8">
  <Envelope>
    <Command><?= $this->command ?></Command>
    <StatusCode><?= $this->statusCode ?></StatusCode>
    <StatusMessage><?= $this->statusMessage ?></StatusMessage>

    <?php if( $this->command == self::COMMAND_GET_ORDERS ) : ?>
      <Provider><?= $this->thirdPartyProvider ?></Provider>
    <?php endif; ?>
  </Envelope>
  <?php if( $this->orders ) : ?>
    <Orders>
      <?php foreach( $this->orders as $order ) : ?>
        <Order>
          <?php if( $this->command == self::COMMAND_GET_ORDERS ) : ?>
            <OrderID><?= $order['order_id'] ?></OrderID>
            <ProviderOrderRef><?= $order['provider_order_ref'] ?></ProviderOrderRef>
            <TransactionType><?= $order['transaction_type'] ?></TransactionType>

            <?php if( $order['transaction_type'] == self::TYPE_RETURN ) : ?>
              <OrigOrderID><?= $order['orig_id'] ?></OrigOrderID>
            <?php endif; ?>

            <Date><?= $order['date'] ?></Date>
            <Time><?= $order['time'] ?></Time>
            <TimeZone><?= $order['time_zone'] ?></TimeZone>
            <UpdatedOn><?= $order['updated_on'] ?></UpdatedOn>
          <?php elseif( $this->command == self::COMMAND_UPDATE_SHIPPING_STATUS ) : ?>
            <HostOrderID><?= $order['host_order_id'] ?></HostOrderID>
            <LocalOrderID><?= $order['local_order_id'] ?></LocalOrderID>
            <HostStatus><?= $order['host_status'] ?></HostStatus>
          <?php endif; ?>

          <?php if( $order['comment'] ) : ?>
            <Comment><?= $order['comment'] ?></Comment>
          <?php endif; ?>

          <?php if( isset($order['bill']) and $bill = $order['bill'] ) : ?>
            <Bill>
              <PayMethod><?= $bill['pay_method'] ?></PayMethod>
              <PayStatus><?= $bill['pay_status'] ?></PayStatus>
              <?php if( isset($bill['pay_date']) ) :?><PayDate><?= $bill['pay_date'] ?></PayDate><?php endif; ?>
              <FirstName><?= $bill['first_name'] ?></FirstName>
              <LastName><?= $bill['last_name'] ?></LastName>
              <?php if( isset($bill['middle_name']) ) : ?><MiddleName><?= $bill['middle_name'] ?></MiddleName><?php endif; ?>
              <CompanyName><?= $bill['company_name'] ?></CompanyName>
              <Address1><?= $bill['address1'] ?></Address1>
              <Address2><?= $bill['address2'] ?></Address2>
              <City><?= $bill['city' ] ?></City>
              <State><?= $bill['state'] ?></State>
              <Zip><?= $bill['zip'] ?></Zip>
              <Country><?= $bill['country'] ?></Country>
              <Email><?= $bill['email'] ?></Email>
              <Phone><?= $bill['phone'] ?></Phone>
              <?php if( isset($bill['po_number']) ) : ?><PONumber><?= $bill['po_number'] ?></PONumber><?php endif; ?>

              <?php if( isset($bill['credit_card']) and $card = $bill['credit_card'] ) : ?>
                <CreditCard>
                  <CreditCardType><?= $card['credit_card_type'] ?></CreditCardType>
                  <CreditCardCharge><?= $card['credit_card_charge'] ?></CreditCardCharge>
                  <ExpirationDate><?= $card['expiration_date'] ?></ExpirationDate>
                  <CreditCardName><?= $card['credit_card_name'] ?></CreditCardName>
                  <CreditCardNumber><?= $card['credit_card_number'] ?></CreditCardNumber>

                  <?php if( isset($card['cvv2']) ) : ?>
                    <CVV2><?= $card['cvv2'] ?></CVV2>
                  <?php endif; ?>

                  <?php if( isset($card['transaction_id']) ) : ?>
                    <TransactionID><?= $card['transaction_id'] ?></TransactionID>
                  <?php endif; ?>

                  <?php if( isset($card['settlement_batch_id']) ) : ?>
                    <SettlementBatchID><?= $card['settlement_batch_id'] ?></SettlementBatchID>
                  <?php endif; ?>

                  <?php if( isset($card['auth_details']) ) : ?>
                    <AuthDetails><?= $card['auth_details'] ?></AuthDetails>
                  <?php endif; ?>

                  <?php if( isset($card['reconciliation_data']) ) : ?>
                    <ReconciliationData><?= $card['reconciliation_data'] ?></ReconciliationData>
                  <?php endif; ?>

                </CreditCard>
              <?php endif; ?>
            </Bill>
          <?php endif; ?>


          <?php if( isset($order['ship']) and $ship = $order['ship'] ) : ?>
            <Ship>
              <ShipCarrierName><?= $ship['ship_carrier_name'] ?></ShipCarrierName>
              <ShipMethod><?= $ship['ship_method'] ?></ShipMethod>
              <FirstName><?= $ship['first_name'] ?></FirstName>
              <LastName><?= $ship['last_name'] ?></LastName>
              <Address1><?= $ship['address1'] ?></Address1>
              <City><?= $ship['city'] ?></City>
              <State><?= $ship['state'] ?></State>
              <Zip><?= $ship['zip'] ?></Zip>
              <Country><?= $ship['country'] ?></Country>
              <Email><?= $ship['email'] ?></Email>
              <Phone><?= $ship['phone'] ?></Phone>

              <?php if( isset($ship['ship_status']) ) : ?>
                <ShipStatus><?= $ship['ship_status'] ?></ShipStatus>
              <?php endif; ?>

              <?php if( isset($ship['ship_date']) ) : ?>
                <ShipDate><?= $ship['ship_date'] ?></ShipDate>
              <?php endif; ?>

              <?php if( isset($ship['tracking']) ) : ?>
                <Tracking><?= $ship['tracking'] ?></Tracking>
              <?php endif; ?>

              <?php if( isset($ship['ship_cost']) ) : ?>
                <ShipCost><?= $ship['ship_cost'] ?></ShipCost>
              <?php endif; ?>

              <?php if( isset($ship['middle_name']) ) : ?>
                <MiddleName><?= $ship['middle_name'] ?></MiddleName>
              <?php endif; ?>

              <?php if( isset($ship['company_name']) ) : ?>
                <CompanyName><?= $ship['company_name'] ?></CompanyName>
              <?php endif; ?>

              <?php if( isset($ship['address2']) ) : ?>
                <Address2><?= $ship['address2'] ?></Address2>
              <?php endif; ?>
            </Ship>
          <?php endif; ?>

          <?php if( isset($order['order_items']) ) : ?>
            <Items>
              <?php foreach( $order['order_items'] as $item ) : ?>
                <Item>
                  <ItemCode><?= $item['item_code'] ?></ItemCode>
                  <ItemDescription><?= $item['item_description'] ?></ItemDescription>
                  <Quantity><?= $item['quantity'] ?></Quantity>
                  <UnitPrice><?= $item['unit_price'] ?></UnitPrice>
                  <ItemTotal><?= $item['item_total'] ?></ItemTotal>

                  <?php if( isset($item['unit_cost']) ) : ?>
                    <UnitCost><?= $item['unit_cost'] ?></UnitCost>
                  <?php endif; ?>

                  <?php if( isset($item['vendor']) ) : ?>
                    <Vendor><?= $item['vendor'] ?></Vendor>
                  <?php endif; ?>

                  <?php if( isset($item['unit_weight']) ) : ?>
                    <UnitWeight><?= $item['unit_weight'] ?></UnitWeight>
                  <?php endif; ?>

                  <!-- currently not used for anything... -->
                  <CustomField1 />
                  <CustomField2 />
                  <CustomField3 />
                  <CustomField4 />
                  <CustomField5 />

                  <?php if( isset($order['item_options']) and $options = $item['item_options'] ) : ?>
                    <ItemOptions>
                      <?php foreach( $options as $k => $v ) : ?>
                        <Option Name="<?= $k ?>" Value="<?= $v ?>" />
                      <?php endforeach; ?>
                    </ItemOptions>
                  <?php endif; ?>
                </Item>
              <?php endforeach; ?>
            </Items>
          <?php endif; ?>

          <?php if( isset($order['charges']) and $charges = $order['charges'] ) : ?>
            <Charges>
              <Shipping><?= $charges['shipping'] ?></Shipping>
              <Handling><?= $charges['handling'] ?></Handling>
              <Tax><?= $charges['tax'] ?></Tax>
              <Discount><?= $charges['discount'] ?></Discount>
              <Total><?= $charges['total'] ?></Total>

              <?php if( isset($charges['tax_other']) ) : ?>
                <TaxOther><?= $charges['tax_other'] ?></TaxOther>
              <?php endif; ?>

              <?php if( isset($charges['channel_fee']) ) : ?>
                <ChannelFee><?= $charges['channel_fee'] ?></ChannelFee>
              <?php endif; ?>

              <?php if( isset($charges['payment_fee']) ) : ?>
                <PaymentFee><?= $charges['payment_fee'] ?></PaymentFee>
              <?php endif; ?>

              <?php if( isset($charges['gift_certificate']) ) : ?>
                <GiftCertificate><?= $charges['gift_certificate'] ?></GiftCertificate>
              <?php endif; ?>

              <?php if( isset($charges['other_charge']) ) : ?>
                <OtherCharge><?= $charges['other_charge'] ?></OtherCharge>
              <?php endif; ?>

              <?php if( isset($charges['item_sub_total']) ) : ?>
                <ItemSubTotal><?= $charges['item_sub_total'] ?></ItemSubTotal>
              <?php endif; ?>

              <?php if( isset($order['coupons']) and $coupons = $charges['coupons'] ) : ?>
                <Coupons>
                  <?php foreach( $coupons as $coupon ) : ?>
                    <Coupon>
                      <CouponCode><?= $coupon['coupon_code'] ?></CouponCode>
                      <CouponID><?= $coupon['coupon_id'] ?></CouponID>
                      <CouponDescription><?= $coupon['coupon_description'] ?></CouponDescription>
                      <CouponValue><?= $coupon['coupon_value'] ?></CouponValue>
                    </Coupon>
                  <?php endforeach; ?>
                </Coupons>
              <?php endif; ?>
            </Charges>
          <?php endif; ?>
        </Order>
      <?php endforeach; ?>
    </Orders>
  <?php endif; ?>
</RESPONSE>
