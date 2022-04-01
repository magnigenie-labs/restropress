<div class="fooditem-description">{itemdescription}</div>
<div class="view-food-item-wrap">
	<form id="fooditem-details">{fooditemslist}</form>
	<div class="clear"></div>
	<div class="rp-col-md-12 md-4-top special-margin <?php echo wp_kses_post( $color ); ?>">
		<a href="#" class="special-instructions-link">
			<?php echo apply_filters('rpress_special_instruction_text', __('Special Instructions?', 'restropress')); ?>
		</a>
		<textarea placeholder="<?php esc_html_e('Add Instructions...', 'restropress') ?>" class="rp-col-md-12 special-instructions " name="special_instruction"></textarea>
	</div>
</div>