<?php if ( ! rpress_has_variable_prices( get_the_ID() ) ) : ?>
	<?php $item_props = rpress_add_schema_microdata() ? ' itemprop="offers" itemscope itemtype="http://schema.org/Offer"' : ''; ?>
	<div<?php echo esc_attr( $item_props ); ?>>
		<div itemprop="price" class="rpress_price">
			<?php rpress_price( get_the_ID() ); ?>
		</div>
	</div>
<?php endif; ?>
