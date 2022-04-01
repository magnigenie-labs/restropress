<?php
/**
 * Template Functions
 *
 * @package     RPRESS
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Append Purchase Link
 *
 * Automatically appends the purchase link to fooditem content, if enabled.
 *
 * @since 1.0
 * @param int $fooditem_id Item ID
 * @return void
 */

function rpress_append_purchase_link( $fooditem_id ) {
	if ( ! get_post_meta( $fooditem_id, '_rpress_hide_purchase_link', true ) ) {
		echo rpress_get_purchase_link( array( 'fooditem_id' => $fooditem_id ) );
	}
}
add_action( 'rpress_after_fooditem_content', 'rpress_append_purchase_link' );


/**
 * Get Purchase Link
 *
 * Builds a Purchase link for a specified fooditem based on arguments passed.
 * This function is used all over RPRESS to generate the Purchase or Add to Cart
 * buttons. If no arguments are passed, the function uses the defaults that have
 * been set by the plugin. The Purchase link is built for simple and variable
 * pricing and filters are available throughout the function to override
 * certain elements of the function.
 *
 * $fooditem_id = null, $link_text = null, $style = null, $color = null, $class = null
 *
 * @since 1.0
 * @param array $args Arguments for display
 * @return string $purchase_form
 */
function rpress_get_purchase_link( $args = array() ) {

	global $post, $rpress_displayed_form_ids;

	$purchase_page = rpress_get_option( 'purchase_page', false );

	if ( ! $purchase_page || $purchase_page == 0 ) {

		global $no_checkout_error_displayed;
		if ( ! is_null( $no_checkout_error_displayed ) ) {
			return false;
		}

		rpress_set_error( 'set_checkout', sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'restropress' ), admin_url( 'admin.php?page=rpress-settings' ) ) );
		rpress_print_errors();

		$no_checkout_error_displayed = true;

		return false;
	}

	$post_id = is_object( $post ) ? $post->ID : 0;
	$button_behavior = rpress_get_fooditem_button_behavior( $post_id );

	$defaults = apply_filters( 'rpress_purchase_link_defaults', array(
		'fooditem_id' => $post_id,
		'price'       => (bool) true,
		'price_id'    => isset( $args['price_id'] ) ? $args['price_id'] : false,
		'direct'      => $button_behavior == 'direct' ? true : false,
		'text'        => $button_behavior == 'direct' ? rpress_get_option( 'buy_now_text', __( 'Buy Now', 'restropress' ) ) : rpress_get_option( 'add_to_cart_text', __( 'Purchase', 'restropress' ) ),
		'checkout'    => rpress_get_option( 'checkout_button_text', _x( 'Checkout', 'text shown on the Add to Cart Button when the product is already in the cart', 'restropress' ) ),
		'style'       => rpress_get_option( 'button_style', 'button' ),
		'color'       => '',
		'class'       => 'rpress-submit'
	) );

	$args = wp_parse_args( $args, $defaults );

	// Override the straight_to_gateway if the shop doesn't support it
	if ( ! rpress_shop_supports_buy_now() ) {
		$args['direct'] = false;
	}

	$fooditem = new RPRESS_Fooditem( $args['fooditem_id'] );

	if( empty( $fooditem->ID ) ) {
		return false;
	}

	if( 'publish' !== $fooditem->post_status && ! current_user_can( 'edit_product', $fooditem->ID ) ) {
		return false; // Product not published or user doesn't have permission to view drafts
	}

	$options          = array();
	$variable_pricing = $fooditem->has_variable_prices();
	$data_variable    = $variable_pricing ? ' data-variable-price="yes"' : 'data-variable-price="no"';
	$type             = $fooditem->is_single_price_mode() ? 'data-price-mode=multi' : 'data-price-mode=single';

	$show_price       = $args['price'] && $args['price'] !== 'no';
	$data_price_value = 0;
	$price            = false;

	if ( $variable_pricing && false !== $args['price_id'] ) {

		$price_id            = $args['price_id'];
		$prices              = $fooditem->prices;
		$options['price_id'] = $args['price_id'];
		$found_price         = isset( $prices[$price_id] ) ? $prices[$price_id]['amount'] : false;

		$data_price_value    = $found_price;

		if ( $show_price ) {
			$price = $found_price;
		}

	} elseif ( ! $variable_pricing ) {

		$data_price_value = $fooditem->price;

		if ( $show_price ) {
			$price = $fooditem->price;
		}

	}

	$data_price  = 'data-price="' . $data_price_value . '"';

	$button_text = ! empty( $args['text'] ) ? '&nbsp;&ndash;&nbsp;' . $args['text'] : '';

	if ( false !== $price ) {

		if ( 0 == $price ) {
			$args['text'] = __( 'Free', 'restropress' ) . $button_text;
		} else {
			$args['text'] = rpress_currency_filter( rpress_format_amount( $price ) ) . $button_text;
		}

	}

	if ( rpress_item_in_cart( $fooditem->ID, $options ) && ( ! $variable_pricing || ! $fooditem->is_single_price_mode() ) ) {
		$button_display   = '';
		$checkout_display = '';
	} else {
		$button_display   = '';
		$checkout_display = 'style="display:none;"';
	}

	// Collect any form IDs we've displayed already so we can avoid duplicate IDs
	if ( isset( $rpress_displayed_form_ids[ $fooditem->ID ] ) ) {
		$rpress_displayed_form_ids[ $fooditem->ID ]++;
	} else {
		$rpress_displayed_form_ids[ $fooditem->ID ] = 1;
	}

	$form_id = ! empty( $args['form_id'] ) ? $args['form_id'] : 'rpress_purchase_' . $fooditem->ID;

	// If we've already generated a form ID for this fooditem ID, append -#
	if ( $rpress_displayed_form_ids[ $fooditem->ID ] > 1 ) {
		$form_id .= '-' . $rpress_displayed_form_ids[ $fooditem->ID ];
	}

	$args = apply_filters( 'rpress_purchase_link_args', $args );

	ob_start();
?>
	<form id="<?php echo esc_attr( $form_id ); ?>" class="rpress_fooditem_purchase_form rpress_purchase_<?php echo absint( $fooditem->ID ); ?>" method="post">

		<?php do_action( 'rpress_purchase_link_top', $fooditem->ID, $args ); ?>

		<div class="rpress_purchase_submit_wrapper">
			<?php
			$class = implode( ' ', array( $args['style'], trim( $args['class'] ) ) );

			if ( rpress_fooditem_available( $fooditem->ID ) ) :

				if ( ! rpress_is_ajax_disabled() ) {

					$add_to_cart_label = apply_filters( 'rpress_add_to_cart_text',
					__( 'ADD', 'restropress' ) );

					echo '<a href="#" data-title="'.get_the_title( $fooditem->ID ).'" class="rpress-add-to-cart ' . esc_attr( $class ) . '" data-action="rpress_add_to_cart" data-fooditem-id="' . esc_attr( $fooditem->ID ) . '" ' . $data_variable . ' ' . $type . ' ' . $data_price . ' ' . $button_display . '><span class="rpress-add-to-cart-label rp-ajax-toggle-text">' . $add_to_cart_label . '</span> </a>';
				}
				?>

				<?php if ( ! rpress_is_ajax_disabled() ) : ?>
					<span class="rpress-cart-ajax-alert" aria-live="assertive">
						<span class="rpress-cart-added-alert" style="display: none;">
							<svg class="rpress-icon rpress-icon-check" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" aria-hidden="true">
								<path d="M26.11 8.844c0 .39-.157.78-.44 1.062L12.234 23.344c-.28.28-.672.438-1.062.438s-.78-.156-1.06-.438l-7.782-7.78c-.28-.282-.438-.673-.438-1.063s.156-.78.438-1.06l2.125-2.126c.28-.28.672-.438 1.062-.438s.78.156 1.062.438l4.594 4.61L21.42 5.656c.282-.28.673-.438 1.063-.438s.78.155 1.062.437l2.125 2.125c.28.28.438.672.438 1.062z"/>
							</svg>
							<?php esc_html_e( 'Added to cart', 'restropress' ); ?>
						</span>
					</span>
				<?php endif; ?>

			<?php else: ?>

				<?php
				$not_available_lable = apply_filters( 'rpress_not_available',
					__( 'Not Available', 'restropress' ), $fooditem->ID );

					echo '<a href="javascript:void(0)" data-title="'.get_the_title( $fooditem->ID ).'" class="rpress-not-available ' . esc_attr( $class ) . '"  data-fooditem-id="' . esc_attr( $fooditem->ID ) . '" ' . ' ' . $button_display . '><span class="rpress-add-to-cart-label">' . $not_available_lable . '</span> </a>';
				?>

			<?php endif; ?>

		</div><!--end .rpress_purchase_submit_wrapper-->

		<input type="hidden" name="fooditem_id" value="<?php echo esc_attr( $fooditem->ID ); ?>">
		<input type="hidden" class="fooditem_qty" name="fooditem_qty" value="">
		<?php if ( $variable_pricing && isset( $price_id ) && isset( $prices[$price_id] ) ): ?>
			<input type="hidden" name="rpress_options[price_id][]" id="rpress_price_option_<?php echo esc_attr( $fooditem->ID ); ?>_1" class="rpress_price_option_<?php echo esc_attr( $fooditem->ID ); ?>" value="<?php echo esc_attr( $price_id ); ?>">
		<?php endif; ?>
		<?php if( ! empty( $args['direct'] ) && ! $fooditem->is_free( $args['price_id'] ) ) { ?>
			<input type="hidden" name="rpress_action" class="rpress_action_input" value="straight_to_gateway">
		<?php } else { ?>
			<input type="hidden" name="rpress_action" class="rpress_action_input" value="add_to_cart">
		<?php } ?>

		<?php if( apply_filters( 'rpress_fooditem_redirect_to_checkout', rpress_straight_to_checkout(), $fooditem->ID, $args ) ) : ?>
			<input type="hidden" name="rpress_redirect_to_checkout" id="rpress_redirect_to_checkout" value="1">
		<?php endif; ?>

		<?php do_action( 'rpress_purchase_link_end', $fooditem->ID, $args ); ?>

	</form><!--end #<?php echo esc_attr( $form_id ); ?>-->


<?php
	$purchase_form = ob_get_clean();

	return apply_filters( 'rpress_purchase_fooditem_form', $purchase_form, $args );
}

/**
 * Output schema markup for products.
 *
 * @since 1.0.0
 * @param  int $fooditem_id The fooditem being output.
 * @return void
 */
function rpress_purchase_link_pricing_schema( $fooditem_id = 0, $args = array() ) {

	// Bail if we aren't showing schema data.
	if ( ! rpress_add_schema_microdata() ) {
		return;
	}

	// Grab the information we need.
	$fooditem = new RPRESS_Fooditem( $fooditem_id );

	if( rpress_has_variable_prices( $fooditem_id ) ) {
		$price = rpress_get_lowest_price_option( $fooditem_id );
	} else {
		$price = $fooditem->price;
	}

	?>
	<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<meta itemprop="price" content="<?php echo esc_attr( $price ); ?>" />
		<meta itemprop="priceCurrency" content="<?php echo esc_attr( rpress_get_currency() ); ?>" />
	</span>
	<?php
}
add_action( 'rpress_purchase_link_top', 'rpress_purchase_link_pricing_schema', 10, 2 );


/**
 *
 * Adds an action to the beginning of fooditem post content that can be hooked to
 * by other functions.
 *
 * @since 1.0.0
 * @global $post
 *
 * @param $content The the_content field of the fooditem object
 * @return string the content with any additional data attached
 */
function rpress_before_fooditem_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'fooditem' && is_singular( 'fooditem' ) && is_main_query() && !post_password_required() ) {
		ob_start();
		do_action( 'rpress_before_fooditem_content', $post->ID );
		$content = ob_get_clean() . $content;
	}

	return $content;
}
add_filter( 'the_content', 'rpress_before_fooditem_content' );

/**
 * After Item Content
 *
 * Adds an action to the end of fooditem post content that can be hooked to by
 * other functions.
 *
 * @since 1.0.0
 * @global $post
 *
 * @param $content The the_content field of the fooditem object
 * @return string the content with any additional data attached
 */
function rpress_after_fooditem_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'fooditem' && is_singular( 'fooditem' ) && is_main_query() && !post_password_required() ) {
		ob_start();
		do_action( 'rpress_after_fooditem_content', $post->ID );
		$content .= ob_get_clean();
	}

	return $content;
}
add_filter( 'the_content', 'rpress_after_fooditem_content' );

/**
 * Get Button Colors
 *
 * Returns an array of button colors.
 *
 * @since 1.0
 * @return array $colors Button colors
 */
function rpress_get_button_colors() {
	$colors = array(
		'gray'      => array(
			'label' => __( 'Gray', 'restropress' ),
			'hex'   => '#f0f0f0'
		),
		'blue'      => array(
			'label' => __( 'Blue', 'restropress' ),
			'hex'   => '#428bca'
		),
		'red'       => array(
			'label' => __( 'Red', 'restropress' ),
			'hex'   => '#d9534f'
		),
		'green'     => array(
			'label' => __( 'Green', 'restropress' ),
			'hex'   => '#5cb85c'
		),
		'yellow'    => array(
			'label' => __( 'Yellow', 'restropress' ),
			'hex'   => '#f0ad4e'
		),
		'orange'    => array(
			'label' => __( 'Orange', 'restropress' ),
			'hex'   => '#ed9c28'
		),
		'dark-gray' => array(
			'label' => __( 'Dark Gray', 'restropress' ),
			'hex'   => '#363636'
		),
		'inherit'	=> array(
			'label' => __( 'Inherit', 'restropress' ),
			'hex'   => ''
		)
	);

	return apply_filters( 'rpress_button_colors', $colors );
}

/**
 * Get Button Styles
 *
 * Returns an array of button styles.
 *
 * @since  1.0.0
 * @return array $styles Button styles
 */
function rpress_get_button_styles() {
	$styles = array(
		'button'	=> __( 'Button', 'restropress' ),
		'plain'     => __( 'Plain Text', 'restropress' )
	);

	return apply_filters( 'rpress_button_styles', $styles );
}

/**
 * Default formatting for fooditem excerpts
 *
 * This excerpt is primarily used in the [fooditems] shortcode
 *
 * @since 1.0
 * @param string $excerpt Content before filtering
 * @return string $excerpt Content after filtering
 * @return string
 */
function rpress_fooditems_default_excerpt( $excerpt ) {
	return do_shortcode( wpautop( $excerpt ) );
}
add_filter( 'rpress_fooditems_excerpt', 'rpress_fooditems_default_excerpt' );

/**
 * Default formatting for full fooditem content
 *
 * This is primarily used in the [fooditems] shortcode
 *
 * @since 1.0
 * @param string $content Content before filtering
 * @return string $content Content after filtering
 */
function rpress_fooditems_default_content( $content ) {
	return do_shortcode( wpautop( $content ) );
}
add_filter( 'rpress_fooditems_content', 'rpress_fooditems_default_content' );

/**
 * Returns the path to the RPRESS templates directory
 *
 * @since 1.0.0
 * @return string
 */
function rpress_get_templates_dir() {
	return RP_PLUGIN_DIR . 'templates';
}

/**
 * Returns the URL to the RPRESS templates directory
 *
 * @since 1.0.0
 * @return string
 */
function rpress_get_templates_url() {
	return RP_PLUGIN_URL . 'templates';
}

/**
 * Retrieves a template part
 *
 * @since 1.0
 *
 * Taken from bbPress
 *
 * @param string $slug
 * @param string $name Optional. Default null
 * @param bool   $load
 *
 * @return string
 *
 * @uses rpress_locate_template()
 * @uses load_template()
 * @uses get_template_part()
 */
function rpress_get_template_part( $slug, $name = null, $load = true ) {

	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	$load_template = apply_filters( 'rpress_allow_template_part_' . $slug . '_' . $name, true );
	if ( false === $load_template ) {
		return '';
	}

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) )
		$templates[] = $slug . '-' . $name . '.php';
	$templates[] = $slug . '.php';

	// Allow template parts to be filtered
	$templates = apply_filters( 'rpress_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return rpress_locate_template( $templates, $load, false );
}

/**
 * Only allow the pending verification message to display once
 * @since 1.0.0
 * @param $load_template
 *
 * @return bool
 */
function rpress_load_verification_template_once( $load_template ) {
	static $account_pending_loaded;
	if ( ! is_null( $account_pending_loaded ) ) {
		return false;
	}

	$account_pending_loaded = true;
	return $load_template;
}
add_filter( 'rpress_allow_template_part_account_pending', 'rpress_load_verification_template_once', 10, 1 );

/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * Taken from bbPress
 *
 * @since 1.0.0
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true.
 *   Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function rpress_locate_template( $template_names, $load = false, $require_once = true ) {

	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) )
			continue;

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// try locating this template file by looping through the template paths
		foreach( rpress_get_theme_template_paths() as $template_path ) {
			if( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) )
		load_template( $located, $require_once );

	return $located;
}

/**
 * Returns a list of paths to check for template locations
 *
 * @since 1.0.0
 * @return mixed|void
 */
function rpress_get_theme_template_paths() {

	$template_dir = rpress_get_theme_template_dir_name();

	$file_paths = array(
		1 => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10 => trailingslashit( get_template_directory() ) . $template_dir,
		100 => rpress_get_templates_dir()
	);

	$file_paths = apply_filters( 'rpress_template_paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );
}

/**
 * Returns the template directory name.
 *
 * Themes can filter this by using the rpress_templates_dir filter.
 *
 * @since 1.0.0
 * @return string
*/
function rpress_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'rpress_templates_dir', 'restropress' ) );
}

/**
 * Should we add schema.org microdata?
 *
 * @since  1.0.0
 * @return bool
 */
function rpress_add_schema_microdata() {
	// Don't modify anything until after wp_head() is called
	$ret = (bool)did_action( 'wp_head' );
	return apply_filters( 'rpress_add_schema_microdata', $ret );
}

/**
 * Add Microdata to fooditem titles
 *
 * @since 1.0
 * @author RestroPress
 * @param string $title Post Title
 * @param int $id Post ID
 * @return string $title New title
 */
function rpress_microdata_title( $title, $id = 0 ) {
	global $post;

	if( ! rpress_add_schema_microdata() || ! is_object( $post ) ) {
		return $title;
	}

	if ( $post->ID == $id && is_singular( 'fooditem' ) && 'fooditem' == get_post_type( intval( $id ) ) ) {
		$title = '<span itemprop="name">' . $title . '</span>';
	}

	return $title;
}
add_filter( 'the_title', 'rpress_microdata_title', 10, 2 );

/**
 * Start Microdata to wrapper fooditem
 *
 * @since 1.0
 * @author RestroPress
 *
 * @return void
 */
function rpress_microdata_wrapper_open( $query ) {
	global $post;

	static $microdata_open = NULL;

	if( ! rpress_add_schema_microdata() || true === $microdata_open || ! is_object( $query ) ) {
		return;
	}

	if ( $query && ! empty( $query->query['post_type'] ) && $query->query['post_type'] == 'fooditem' && is_singular( 'fooditem' ) && $query->is_main_query() ) {
		$microdata_open = true;
		echo '<span itemscope itemtype="http://schema.org/Product">';
	}

}
add_action( 'loop_start', 'rpress_microdata_wrapper_open', 10 );

/**
 * End Microdata to wrapper fooditem
 *
 * @since 1.0
 * @author RestroPress
 *
 * @return void
 */
function rpress_microdata_wrapper_close() {
	global $post;

	static $microdata_close = NULL;

	if( ! rpress_add_schema_microdata() || true === $microdata_close || ! is_object( $post ) ) {
		return;
	}

	if ( $post && $post->post_type == 'fooditem' && is_singular( 'fooditem' ) && is_main_query() ) {
		$microdata_close = true;
		echo '</span>';
	}
}
add_action( 'loop_end', 'rpress_microdata_wrapper_close', 10 );

/**
 * Add Microdata to fooditem description
 *
 * @since 1.0
 * @author RestroPress
 *
 * @param $content
 * @return mixed|void New title
 */
function rpress_microdata_description( $content ) {
	global $post;

	static $microdata_description = NULL;

	if( ! rpress_add_schema_microdata() || true === $microdata_description || ! is_object( $post ) ) {
		return $content;
	}

	if ( $post && $post->post_type == 'fooditem' && is_singular( 'fooditem' ) && is_main_query() ) {
		$microdata_description = true;
		$content = apply_filters( 'rpress_microdata_wrapper', '<div itemprop="description">' . $content . '</div>' );
	}
	return $content;
}
add_filter( 'the_content', 'rpress_microdata_description', 10 );

/**
 * Add no-index and no-follow to RPRESS checkout and purchase confirmation pages
 *
 * @since  1.0.0
 *
 * @return void
 */
function rpress_checkout_meta_tags() {

	$pages   = array();
	$pages[] = rpress_get_option( 'success_page' );
	$pages[] = rpress_get_option( 'failure_page' );
	$pages[] = rpress_get_option( 'order_history_page' );

	if( ! rpress_is_checkout() && ! is_page( $pages ) ) {
		return;
	}

	echo '<meta name="rpress-chosen-gateway" content="' . rpress_get_chosen_gateway() . '"/>' . "\n";
	echo '<meta name="robots" content="noindex,nofollow" />' . "\n";
}
add_action( 'wp_head', 'rpress_checkout_meta_tags' );

/**
 * Adds RPRESS Version to the <head> tag
 *
 * @since 1.0.0
 * @return void
*/
function rpress_version_in_header(){
	echo '<meta name="generator" content="RestroPress v' . RP_VERSION . '" />' . "\n";
}
add_action( 'wp_head', 'rpress_version_in_header' );

/**
 * Determines if we're currently on the Order History page.
 *
 * @since  1.0.0
 * @return bool True if on the Order History page, false otherwise.
 */
function rpress_is_order_history_page() {
	$ret = rpress_get_option( 'order_history_page', false );
	$ret = $ret ? is_page( $ret ) : false;
	return apply_filters( 'rpress_is_order_history_page', $ret );
}

/**
 * Adds body classes for RPRESS pages
 *
 * @since  1.0.0
 * @param array $class current classes
 * @return array Modified array of classes
 */
function rpress_add_body_classes( $class ) {
	$classes = (array) $class;

	switch ( true ) {
		case rpress_is_checkout():
			$classes[] = 'rpress-checkout';
			$classes[] = 'rpress-page';
			break;

		case rpress_is_success_page():
			$classes[] = 'rpress-success';
			$classes[] = 'rpress-page';
			break;

		case rpress_is_failed_transaction_page():
			$classes[] = 'rpress-failed-transaction';
			$classes[] = 'rpress-page';
			break;

		case rpress_is_order_history_page():
			$classes[] = 'rpress-payment-history';
			$classes[] = 'rpress-page';
			break;

		case rpress_is_test_mode():
			$classes[] = 'rpress-test-mode';
			break;

		default:
			$classes[] = 'rpress';
			break;
	}

	return array_unique( $classes );
}
add_filter( 'body_class', 'rpress_add_body_classes' );

/**
 * Adds post classes for fooditems
 *
 * @since  1.0.0
 * @param array $classes Current classes
 * @param string|array $class
 * @param int $post_id The ID of the current post
 * @return array Modified array of classes
 */
function rpress_add_fooditem_post_classes( $classes, $class = '', $post_id = false ) {
	if( ! $post_id || get_post_type( $post_id ) !== 'fooditem' || is_admin() ) {
		return $classes;
	}

	$fooditem = rpress_get_fooditem( $post_id );

	if( $fooditem ) {
		$classes[] = 'rpress-fooditem';

		// Add category slugs
		$categories = get_the_terms( $post_id, 'addon_category' );
		if( ! empty( $categories ) ) {
			foreach( $categories as $key => $value ) {
				$classes[] = 'rpress-fooditem-cat-' . $value->slug;
			}
		}

		// Add tag slugs
		$tags = get_the_terms( $post_id, 'fooditem_tag' );
		if( ! empty( $tags ) ) {
			foreach( $tags as $key => $value ) {
				$classes[] = 'rpress-fooditem-tag-' . $value->slug;
			}
		}

		// Add rpress-fooditem
		if( is_singular( 'fooditem' ) ) {
			$classes[] = 'rpress-fooditem';
		}
	}

	return $classes;
}
add_filter( 'post_class', 'rpress_add_fooditem_post_classes', 20, 3 );

/**
 * Adds item product price to oembed display
 *
 * @since 1.0.0
 * @return void
 */
function rpress_add_oembed_price() {

	if( 'fooditem' !== get_post_type( get_the_ID() ) ) {
		return;
	}

	$show = ! get_post_meta( get_the_ID(), '_rpress_hide_purchase_link', true );

	if ( apply_filters( 'rpress_show_oembed_purchase_links', $show ) ) {
		echo '<style>.wp-embed-rpress-price { margin: 20px 0 0 0; }</style>';
		echo '<div class="wp-embed-rpress-price">';
			if ( rpress_has_variable_prices( get_the_ID() ) ) {
				echo rpress_price_range( get_the_ID() );
			} else {
				rpress_price( get_the_ID(), true );
			}

		echo '</div>';
	}
}
add_action( 'embed_content', 'rpress_add_oembed_price' );

/**
 * Remove comments button for fooditem embeds
 *
 * @since 1.0.0
 * @return  void
 */
function rpress_remove_embed_comments_button() {
	global $post;

	$hide_comments = apply_filters( 'rpress_embed_hide_comments', true, $post );

	if ( ! empty( $post ) && $post->post_type == 'fooditem' && true === $hide_comments ) {
		remove_action( 'embed_content_meta', 'print_embed_comments_button' );
	}
}
add_action( 'embed_content_meta', 'rpress_remove_embed_comments_button', 5 );

/**
 * Get a fully formatted title of a bundle item
 *
 * @since 1.0
 *
 * @param array $bundle_item Bundle item.
 * @return string Bundle item title.
 */
function rpress_get_bundle_item_title( $bundle_item ) {
	$bundle_item_pieces = explode( '_', $bundle_item );
	$bundle_item_id = $bundle_item_pieces[0];
	$bundle_price_id = isset( $bundle_item_pieces[1] ) ? $bundle_item_pieces[1] : null;

	$prices = rpress_get_variable_prices( $bundle_item_id );
	$bundle_title = get_the_title( $bundle_item_id );

	if ( null !== $bundle_price_id ) {
		$bundle_title .= ' - ' . $prices[ $bundle_price_id ]['name'];
	}

	return $bundle_title;
}

/**
 * Retrieve the ID of an item in a bundle.
 *
 * @since 1.0
 *
 * @param array $bundle_item Bundle item.
 * @return string Bundle item ID.
 */
function rpress_get_bundle_item_id( $bundle_item ) {
	$bundle_item_pieces = explode( '_', $bundle_item );
	$bundle_item_id = $bundle_item_pieces[0];
	return $bundle_item_id;
}

/**
 * Retrieve the price ID of a bundle item.
 *
 * @since 1.0
 *
 * @param array $bundle_item Bundle item.
 * @return string Bundle item ID.
 */
function rpress_get_bundle_item_price_id( $bundle_item ) {

	$bundle_item_pieces = explode( '_', $bundle_item );
	$bundle_item_id = $bundle_item_pieces[0];
	$bundle_price_id = isset( $bundle_item_pieces[1] ) ? $bundle_item_pieces[1] : null;

	return $bundle_price_id;
}

/**
 * Load a template file for a single fooditem item.
 *
 * This is a wrapper function for backwards compatibility so the
 * shortcode's attributes can be passed to the template file via
 * a global variable.
 *
 * @since 1.0.0
 *
 * @param array $atts The [fooditems] shortcode attributes.
 * @param int   $i The current item count.
 */
function rpress_fooditem_shortcode_item( $atts, $i ) {

	global $rpress_fooditem_shortcode_item_atts, $rpress_fooditem_shortcode_item_i;

	$rpress_fooditem_shortcode_item_atts = $atts;
	$rpress_fooditem_shortcode_item_i = $i;

	rpress_get_template_part( 'fooditem/single' );
}
add_action( 'rpress_fooditem_shortcode_item', 'rpress_fooditem_shortcode_item', 10, 2 );

/**
 * Get category title
 *
 * @since 2.7.2
 *
 * @param string $term_slug
 * @param int $id
 * @param array $var
 */
function rpress_get_category_title( $term_slug, $id, $var ) {

  	global $fooditem_term_slug, $rpress_fooditem_id, $curr_cat_var;

  	$fooditem_term_slug = $term_slug;
  	$rpress_fooditem_id = $id;

  	rpress_get_template_part( 'fooditem/category' );
}
add_action('rpress_fooditems_category_title', 'rpress_get_category_title', 10, 3);

/**
 * Get Cart Items
 *
 * @since 2.7.2
 */
function rpress_get_cart_items() {
	rpress_shopping_cart();
}
add_action( 'rpress_get_cart', 'rpress_get_cart_items' );

/**
 * Get Delivery steps 
 *
 * @since 2.7.2
 *
 * @param int $fooditem_id
 */
function rpress_get_delivery_steps( $fooditem_id ) {

  ob_start();
  rpress_get_template_part( 'rpress', 'delivery-steps' );
  $data = ob_get_clean();
  $data = str_replace( '{fooditem_id}', $fooditem_id, $data );
  return $data;
}

/**
 * Add delivery steps
 *
 * @since 2.7.2
 *
 * @param int $fooditem_id
 */
add_action( 'rpress_get_delivery_steps', 'rpress_add_delivery_steps' );
function rpress_add_delivery_steps() {

 	echo rpress_get_delivery_steps('');
}