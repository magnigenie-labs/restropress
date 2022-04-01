<li class="rpress-cart-item" data-cart-key="{cart_item_id}">
	<span class="rpress-cart-item-qty qty-class">{item_qty}</span>
	<span class="separator">x</span>
	<span class="rpress-cart-item-title">{item_title}</span>&nbsp;
	<span class="cart-item-quantity-wrap">
		<span class="rpress-cart-item-price qty-class">{item_formated_amount}</span>
	</span>
	<div>{addon_items}</div>
	<span class="rpress-special-instruction">{special_instruction}</span>
	<div>
		<span class="cart-action-wrap">
			<a class="rpress-edit-from-cart" data-cart-item="{cart_item_id}" data-item-name="{item_title}" data-item-id="{item_id}" data-item-price="{item_amount}" data-remove-item="{edit_food_item}">
				<span class="rp-ajax-toggle-text"><?php echo apply_filters( 'rpress_cart_edit', __('Edit', 'restropress' ) ); ?></span>
			</a>
			<a href="{remove_url}" data-cart-item="{cart_item_id}" data-fooditem-id="{item_id}" data-action="rpress_remove_from_cart" class="rpress-remove-from-cart"><?php echo apply_filters( 'rpress_cart_remove', __('Remove', 'restropress' ) ); ?></a>
		</span>
	</div>
</li>