<div class="fancybox-main">
	<div class="fancybox-first">
		<div class="view-food-item-wrap">
			<div class="row">
				<div class="col-md-12">
					<h1 class="text-center">{Food_Title}</h1>
				</div>
			</div>

			<form id="fooditem-details" class="row">
				{Formatted_Cats}
			</form>
			
			<div class="col-md-12 md-4-top special-margin">
				<a href="#" class="special-instructions-link">
					<?php echo __('Special Instructions?', 'restro-press'); ?>
				</a>
				<textarea placeholder="<?php echo __('Add Instructions...', 'restro-press') ?>" class="col-md-12 special-instructions " name="special_instruction"></textarea>
		</div>
		</div>
		</div>
		<div class="rpress-popup-actions edit-rpress-popup-actions  col-md-12">
			<div class="row row-top">
				<div class="col-md-6 btn-count">
					<div class="col-md-3 col-xs-3 col-sm-2"><input type="button" value="-" class="qtyminus qtyminus-style qtyminus-style-edit" field="quantity"/></div>
					<div class="col-md-4 col-xs-4  col-sm-4 md-4-mar-lft">
						<input type="text" name="quantity" value="1" class="qty qty-style">
					</div>
					<div class="col-md-2 col-sm-2 col-xs-3 plus-symb">
						<input type="button" value="+" class="qtyplus col-md-3 qtyplus-style qtyplus-style-edit" field="quantity"/>
					</div>';
				</div>
			</div>
		<a data-item-qty="1" data-item-id="{Food_ID}" data-item-price="{Food_Price}" class="center submit-fooditem-button text-center inline col-md-6"><?php echo __('Add To Cart', 'restro-press'); ?></a>
		</div>
		</div>