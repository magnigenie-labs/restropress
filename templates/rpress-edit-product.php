<?php 
	$class = !empty($special_instruction) ? '' : 'hide';
?>
<div class="view-food-item-wrap">
	<form id="fooditem-update-details" class="row">{FormattedCats}</form>
	<div class="clear"></div>
	<div class="rp-col-md-12 md-12-top special-inst">
		<a href="#" class="special-instructions-link">
			<?php echo __('Special Instructions?', 'restropress'); ?>		
		</a>
		
		<textarea placeholder="Add Instructions..." class="rp-col-md-12 special-instructions " name="special_instruction">{SpecialInstruction}</textarea>
	</div>
</div>
	
		
		<?php 
		/*
		<div class="rpress-popup-actions  edit-pop-up-custom-button">
			<a data-item-qty="{ItemQty}" data-cart-key="{CartKey}" data-item-id="{FoodItemId}" data-item-price="{FoodItemPrice}" class="center update-fooditem-button inline"><?php echo __('Update Cart', 'restropress'); ?></a>
		</div>
		*/
		?>
