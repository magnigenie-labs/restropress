<?php 

$payment_id     = absint( $_GET['payment_id'] );
$payment        = new RPRESS_Payment( $payment_id );
$cart_items     = $payment->cart_details;
$payment_amount = rpress_payment_amount( $payment_id );
$currency_code  = $payment->currency;
$payment_fees   = $payment->fees;

$subtotal = 0;
$taxes = 0;
$discounts = 0;

?>

<table style="border-spacing: 0; margin-top: 0px; width: 100%; padding: 10px; border-top: 1px dashed #000000; border-bottom: 1px dashed #000000;">
  <?php if ( is_array( $cart_items ) && !empty( $cart_items ) ) : ?>
  <thead>
    <tr>
      <td style="padding: 6px 0;"><strong><?php echo apply_filters( 'rpress_receipt_product_column', esc_html_e( 'Items', 'restropress' ) ); ?></strong></td>
      <td style="padding: 6px 0; text-align: right;"><strong><?php echo apply_filters( 'rpress_receipt_price_column', esc_html_e( 'Price', 'restropress' ) ); ?></strong></td>
    </tr>
  </thead>
  <tbody>

    <?php foreach( $cart_items as $key => $item ) : ?>

      <?php if ( isset( $item['name'] ) ) :
      $item_name = isset( $item['name'] ) ? $item['name'] : '';
      $item_qty = isset( $item['item_number']['quantity'] ) ? $item['item_number']['quantity'] : '';
      $item_id = isset( $item['id'] ) ? $item['id'] : '';
      $item_price = $item['item_price'] * $item_qty;

      $subtotal = $subtotal + $item['subtotal'];
      $taxes = $taxes + $item['tax'];
      $discounts = $discounts + $item['discount'];

      ?>

      <tr>
        <td style="padding: 4px 0; vertical-align: top;"><?php echo $item_qty; ?> x <?php echo $item_name; ?></td>
        <td style="padding: 4px 0; text-align: right; font-weight: bold; vertical-align: top;"><?php echo rpress_currency_filter( rpress_format_amount( $item_price ) ); ?></td>
      </tr>
      <?php if ( isset($item['item_number']['options']) && is_array( $item['item_number']['options'] )  ) : ?>
        <?php foreach( $item['item_number']['options'] as $key => $addon_item ): ?>
          <tr>
            <?php if ( isset( $addon_item['addon_item_name'] ) ) :
              $addon_item_name = $addon_item['addon_item_name'];
              $addon_qty = $addon_item['quantity'];
              $addon_price = rpress_currency_filter( rpress_format_amount( $addon_item['price'] ) );
              ?>
              <!-- <td style="padding: 4px 0; vertical-align: top;"> - </td> -->
              <td style="padding: 4px 0; font-size: 10pt;">- <?php echo $addon_qty; ?> x <?php echo $addon_item_name; ?></td>
              <td style="padding: 4px 0; font-size: 10pt; text-align: right; vertical-align: top;"><?php echo $addon_price; ?></td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        <?php if( $item['instruction'] != '' ): ?>
          <tr>
            <td colspan="3" style="padding: 6px 0;"><b><?php echo apply_filters( 'rpress_receipt_item_note_label', esc_html_e( 'Customer Note:&nbsp;', 'restropress' ) ); ?></b><?php echo $item['instruction']; ?></td>
          </tr>
        <?php endif; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </tbody>
  <?php endif; ?>
</table>

<table style="width: 100%; margin-top: 12px; font-size: 14px;">
  <tr>
    <td style="width: 70%; text-align: left;"><?php echo apply_filters( 'rpress_receipt_subtotal_amount', esc_html_e( 'Subtotal', 'restropress' ) ); ?>:</td>
    <td style="width: 30%; text-align: right"><b><?php echo rpress_currency_filter( $subtotal, $currency_code ); ?></b></td>
  </tr>
  <?php if( $discounts > 0 ) : ?>
    <tr>
      <td style="width: 70%; text-align: left;"><?php echo apply_filters( 'rpress_receipt_discount_price', esc_html_e( 'Discounts', 'restropress' ) ); ?>:</td>
      <td style="width: 30%; text-align: right"><b><?php echo rpress_currency_filter( $discounts, $currency_code ); ?></b></td>
    </tr>
  <?php endif; ?>
  <?php if( $taxes > 0 ) : ?>
    <tr>
      <td style="width: 70%; text-align: left;"><?php echo apply_filters( 'rpress_receipt_tax_price', rpress_get_tax_name() ); ?>:</td>
      <td style="width: 30%; text-align: right"><b><?php echo rpress_currency_filter( $taxes, $currency_code ); ?></b></td>
    </tr>
  <?php endif; ?>
  <?php if ( ! empty( $payment_fees ) ) : ?>
    <?php foreach( $payment_fees as $fee ) : ?>
      <tr>
        <td style="width: 70%; text-align: left;"><?php echo $fee['label']; ?>:</td>
        <td style="width: 30%; text-align: right"><b><?php echo rpress_currency_filter( $fee['amount'], $currency_code ); ?></b></td>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
  <tr>
    <td colspan="2"><hr></td>
  </tr>
  <tr>
    <td style="width: 70%; text-align: left;"><?php echo apply_filters( 'rpress_receipt_total_price', esc_html_e( 'Total', 'restropress' ) ); ?>:</td>
    <td style="width: 30%; text-align: right"><b><?php echo $payment_amount; ?></b></td>
  </tr>
  <tr>
    <td colspan="2"><hr></td>
  </tr>
</table>