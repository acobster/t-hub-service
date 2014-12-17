<?= '<?xml version="1.0" encoding="ISO-8859-1"?>'.PHP_EOL ?>
<RESPONSE Version="2.8">
  <Envelope>
    <Command><?= $this->command ?></Command>
    <StatusCode><?= $this->statusCode ?></StatusCode>
    <StatusMessage><?= $this->statusMessage ?></StatusMessage>
    <Provider><?= $this->thirdPartyProvider ?></Provider>
  </Envelope>
  <?php if( !empty($this->orders) ) : ?>
    <Orders>
      <?php foreach( $this->orders as $order ) : ?>
        <Order>
          <OrderID><?= $order['id'] ?></OrderID>
          <ProviderOrderRef><?= $order['ref_id'] ?></ProviderOrderRef>
          <TransactionType><?= $order['transaction_type'] ?></TransactionType>

          <?php if( $order['transaction_type'] == self::TYPE_RETURN ) : ?>
            <OrigOrderID><?= $order['orig_id'] ?></OrigOrderID>
          <?php endif; ?>

          <Date><?= $order['date'] ?></Date>
          <Time><?= $order['time'] ?></Time>
          <TimeZone><?= $order['time_zone'] ?></TimeZone>
          <UpdatedOn><?= $order['updated_on'] ?></UpdatedOn>

          <Bill>
            <?php $bill = $order['bill']; ?>
            <PayMethod><?= $bill['pay_method'] ?></PayMethod>
            <PayStatus><?= $bill['pay_status'] ?></PayStatus>
            <PayDate><?= $bill['pay_date'] ?></PayDate>
            <FirstName><?= $bill['first_name'] ?></FirstName>
            <LastName><?= $bill['last_name'] ?></LastName>
            <MiddleName><?= $bill['middle_name'] ?></MiddleName>
            <CompanyName><?= $bill['company_name'] ?></CompanyName>
            <Address1><?= $bill['address1'] ?></Address1>
            <Address2><?= $bill['address2'] ?></Address2>
            <City><?= $bill['city' ] ?></City>
            <State><?= $bill['state'] ?></State>
            <Zip><?= $bill['zip'] ?></Zip>
            <Country><?= $bill['country'] ?></Country>
            <Email><?= $bill['email'] ?></Email>
            <Phone><?= $bill['phone'] ?></Phone>
            <PONumber><?= $bill['po_number'] ?></PONumber>

            <?php if( $card = $bill['credit_card'] ) : ?>
              <CreditCard>
                <CreditCardType><?= $card['credit_card_type'] ?></CreditCardType>
                <CreditCardCharge><?= $card['credit_card_charge'] ?></CreditCardCharge>
                <ExpirationDate><?= $card['expiration_date'] ?></ExpirationDate>
                <CreditCardNumber><?= $card['credit_card_number'] ?></CreditCardNumber>

                <?php if( $card['cvv2'] ) : ?>
                  <CVV2><?= $card['cvv2'] ?></CVV2>
                <?php endif; ?>

                <?php if( $card['transaction_id'] ) : ?>
                  <TransactionID><?= $card['transaction_id'] ?></TransactionID>
                <?php endif; ?>

                <?php if( $card['settlement_batch_id'] ) : ?>
                  <SettlementBatchID><?= $card['settlement_batch_id'] ?></SettlementBatchID>
                <?php endif; ?>

                <?php if( $card['auth_details'] ) : ?>
                  <AuthDetails><?= $card['auth_details'] ?></AuthDetails>
                <?php endif; ?>

                <?php if( $card['reconciliation_data'] ) : ?>
                  <ReconciliationData><?= $card['reconciliation_data'] ?></ReconciliationData>
                <?php endif; ?>

              </CreditCard>
            <?php endif; ?>
          </Bill>

          <Ship>
            <?php $ship = $order['ship']; ?>
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

            <?php if( $ship['ship_status'] ) : ?>
              <ShipStatus><?= $ship['ship_status'] ?></ShipStatus>
            <?php endif; ?>

            <?php if( $ship['ship_date'] ) : ?>
              <ShipDate><?= $ship['ship_date'] ?></ShipDate>
            <?php endif; ?>

            <?php if( $ship['ship_tracking'] ) : ?>
              <ShipTracking><?= $ship['ship_tracking'] ?></ShipTracking>
            <?php endif; ?>

            <?php if( $ship['ship_cost'] ) : ?>
              <ShipCost><?= $ship['ship_cost'] ?></ShipCost>
            <?php endif; ?>

            <?php if( $ship['middle_name'] ) : ?>
              <MiddleName><?= $ship['middle_name'] ?></MiddleName>
            <?php endif; ?>

            <?php if( $ship['company_name'] ) : ?>
              <CompanyName><?= $ship['company_name'] ?></CompanyName>
            <?php endif; ?>

            <?php if( $ship['address2'] ) : ?>
              <Address2><?= $ship['address2'] ?></Address2>
            <?php endif; ?>
          </Ship>

          <Items>

          </Items>

          <Charges>

          </Charges>
        </Order>
      <?php endforeach; ?>
    </Orders>
  <?php endif; ?>
</RESPONSE>