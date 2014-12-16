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
        </Order>
      <?php endforeach; ?>
    </Orders>
  <?php endif; ?>
</RESPONSE>