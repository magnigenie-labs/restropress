<?php 
	$class = !empty($special_instruction) ? '' : 'hide';
?>

<div class="fancybox-content pointer">
	<div class="view-food-item-wrap">
		<div class="row">
			<div class="col-md-12">
				<h1 class="text-center">{FoodName}</h1>
			</div>
		</div>

		<form id="fooditem-update-details" class="row">{FormattedCats}</form>
		
		<div class="col-md-12 md-12-top special-inst">
			<a href="#" class="special-instructions-link">
				<?php echo __('Special Instructions?', 'restro-press'); ?>		
			</a>
		
			<textarea placeholder="Add Instructions..." class="col-md-12 special-instructions <?php echo $class; ?> " name="special_instruction">{SpecialInstruction}</textarea>
		</div>
	</div>
	
	<div class="row row-top update-bottom">
		<div class="col-md-6 qty-button">
			<div class="col-md-3 col-xs-3 col-sm-3 left">
				<input type="button" value="-" class="qtyminus" field="quantity" style="font-weight: bold;" />
			</div>
			<div class="col-md-4 col-xs-4 col-sm-3 cent">
				<input type="text" name="quantity" value="{ItemQty}" class="qty" style="margin-bottom: 0px !important"/>
			</div>
			<div class="col-md-4 col-xs-3 col-sm-3 right">
				<input type="button" value="+" class="qtyplus col-md-3 qty_plus_font" field="quantity" style="font-weight: bold;" />
			</div>
		</div>
		
		<div class="rpress-popup-actions  edit-pop-up-custom-button">
			<a data-item-qty="{ItemQty}" data-cart-key="{CartKey}" data-item-id="{FoodItemId}" data-item-price="{FoodItemPrice}" class="center update-fooditem-button inline"><?php echo __('Update Cart', 'restro-press'); ?></a>
		</div>
	</div>
</div>