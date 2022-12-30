<div id="rpress-payment-processing">
	<p><?php printf( __( 'Your order is processing. This page will reload automatically in 8 seconds. If it does not, click <a href="%s">here</a>.', 'restropress' ), rpress_get_success_page_uri() ); ?>
	<span class="rpress-cart-ajax"><i class="rpress-icon-spinner rpress-icon-spin"></i></span>
	<script type="text/javascript">setTimeout(function(){ window.location = '<?php echo rpress_get_success_page_uri(); ?>'; }, 8000);</script>
</div>