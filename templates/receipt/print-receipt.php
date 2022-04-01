<?php
/**
 * This template is used to display the printer receipt
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="purchase-invoice" style='font-family: {rpp_choosen_font}; width: {rpp_paper_size}; max-width: 80mm; margin: 2mm'>
  <div class="header-info">
      {rpp_store_logo}
      <div class="customer-info">
          <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;"><?php echo __('Order', 'restropress'); ?>: #<b>{rpp_order_id}</b></p>
          <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;">{rpp_customer_name} {rpp_customer_phone}</p>
          <p style="margin-top: 0; margin-bottom: 0; text-align: center; font-size: 12pt;">{rpp_customer_email}</p>
      </div>
      <div class="order-info" style="width:100%;padding:0;margin-bottom: 0px;">
          <table style="border-spacing: 0; border-top: 1px dashed; #000000; margin-top: 10px; width: 100%; padding: 10px;">
              <thead>
                  <tr>
                      <td style="text-align: center; padding-bottom: 8px;"><strong style="text-transform: uppercase; font-size: 15pt;">{rpp_order_type}</strong></td>
                  </tr>
                  <tr>
                      <td>
                          <p>{rpp_order_type} Time: <b>{rpp_order_time} {rpp_order_date}</b></p>
                      </td>
                  </tr>
                  <tr>
                      <td>
                          {rpp_order_location}
                      </td>
                  </tr>
                  <tr>
                      <td>
                          {rpp_order_payment_type}
                      </td>
                  </tr>
                  <tr>
                      <td>
                          {rpp_order_note}
                      </td>
                  </tr>
              </thead>
          </table>
      </div>
      <div class="order-items"> {rpp_order_items} </div>
  </div>
  <div class="order-footer-note">
      {footer_note}
  </div>
  <hr>
  <div class="order-footer-complimentary">
      {footer_complementary}
  </div>
</div>