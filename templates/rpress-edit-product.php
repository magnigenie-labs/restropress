<div class="fooditem-description">{itemdescription}</div>
<div class="view-food-item-wrap">
	<form id="fooditem-update-details" class="row">{fooditemslist}</form>
	<div class="clear"></div>
	<?php if( apply_filters( 'rpress_special_instructions', true ) ) : ?>
	<div class="rp-col-md-12 md-12-top special-inst">
		<a href="#" class="special-instructions-link">
			<?php echo apply_filters( 'rpress_special_instruction_text', __('Special Instructions?', 'restropress' ) ); ?>
		</a>
		<textarea placeholder="<?php esc_html_e( 'e.g. allergies, extra spicy, etc.', 'restropress' ); ?>" class="rp-col-md-12 special-instructions" name="special_instruction">{cartinstructions}</textarea>
	</div>
	<?php endif; ?>
</div>
