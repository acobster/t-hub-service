<RESPONSE Version=“2.8”>
  <Envelope>
    <Command>GetOrders</Command>
    <StatusCode>0</StatusCode>
    <StatusMessage>All Ok</StatusMessage>
    <Provider>GENERIC</Provider>
  </Envelope>
  <Orders>
    <?php foreach( $orders as $order ) : ?>
      <Order>

      </Order>
    <?php endforeach; ?>
  </Orders>
</RESPONSE>