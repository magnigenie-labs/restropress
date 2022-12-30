<?php
/**
 * Metabox Functions
 *
 * @package     RPRESS
 * @subpackage  Admin/RestroPress
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns default RPRESS RestroPress meta fields.
 *
 * @since  1.0.0
 * @return array $fields Array of fields.
 */
function rpress_fooditem_metabox_fields() {

	$fields = array(
			'_rpress_product_type',
			'rpress_price',
			'_variable_pricing',
			'_rpress_price_options_mode',
			'rpress_variable_prices',
			'_rpress_purchase_text',
			'_rpress_purchase_style',
			'_rpress_purchase_color',
			'_rpress_bundled_products',
			'_rpress_hide_purchase_link',
			'_rpress_fooditem_tax_exclusive',
			'_rpress_button_behavior',
			'_rpress_quantities_disabled',
			'rpress_product_notes',
			'_rpress_default_price_id',
			'_rpress_bundled_products_conditions'
		);

	if ( rpress_use_skus() ) {
		$fields[] = 'rpress_sku';
	}

	return apply_filters( 'rpress_metabox_fields_save', $fields );
}

/**
 * Save post meta when the save_post action is called
 *
 * @since 1.0
 * @param int $post_id RestroPress (Post) ID
 * @global array $post All the data of the the current post
 * @return void
 */
function rpress_fooditem_meta_box_save( $post_id, $post ) {

	if ( ! isset( $_POST['rpress_fooditem_meta_box_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['rpress_fooditem_meta_box_nonce'] ), basename( __FILE__ ) ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	// The default fields that get saved
	$fields = rpress_fooditem_metabox_fields();

	foreach ( $fields as $field ) {

		if ( '_rpress_default_price_id' == $field && rpress_has_variable_prices( $post_id ) ) {

			if ( isset( $_POST[ $field ] ) ) {
				$new_default_price_id = ( ! empty( $_POST[ $field ] ) && is_numeric( $_POST[ $field ] ) ) || ( 0 === (int) $_POST[ $field ] ) ? (int) sanitize_text_field( $_POST[ $field ] ) : 1;
			} else {
				$new_default_price_id = 1;
			}

			update_post_meta( $post_id, $field, $new_default_price_id );

		} else {

			if ( ! empty( $_POST[ $field ] ) ) {
				$new = apply_filters( 'rpress_metabox_save_' . $field, sanitize_text_field( $_POST[ $field ] ) );
				update_post_meta( $post_id, $field, $new );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}
	}

	if ( rpress_has_variable_prices( $post_id ) ) {
		$lowest = rpress_get_lowest_price_option( $post_id );
		update_post_meta( $post_id, 'rpress_price', $lowest );
	}

	do_action( 'rpress_save_fooditem', $post_id, $post );
}
add_action( 'save_post', 'rpress_fooditem_meta_box_save', 10, 2 );

/**
 * Sanitize bundled products on save
 *
 * Ensures a user doesn't try and include a product's ID in the products bundled with that product
 *
 * @since  1.0.0
 *
 * @param array $products
 * @return array
 */
function rpress_sanitize_bundled_products_save( $products = array() ) {

	global $post;

	$self = array_search( $post->ID, $products );

	if( $self !== false )
		unset( $products[ $self ] );

	return array_values( array_unique( $products ) );
}
add_filter( 'rpress_metabox_save__rpress_bundled_products', 'rpress_sanitize_bundled_products_save' );

/**
 * Don't save blank rows.
 *
 * When saving, check the price and file table for blank rows.
 * If the name of the price or file is empty, that row should not
 * be saved.
 *
 * @since  1.0.0
 * @param array $new Array of all the meta values
 * @return array $new New meta value with empty keys removed
 */
function rpress_metabox_save_check_blank_rows( $new ) {

	foreach ( $new as $key => $value ) {
		if ( empty( $value['name'] ) && empty( $value['amount'] ) && empty( $value['file'] ) )
			unset( $new[ $key ] );
	}
	return $new;
}

/**
 * Alter the Add to post button in the media manager for fooditems
 *
 * @since 1.0
 * @param  array $strings Array of default strings for media manager
 * @return array          The altered array of strings for media manager
 */
function rpress_fooditem_media_strings( $strings ) {
	global $post;

	if ( ! $post || $post->post_type !== 'fooditem' ) {
		return $strings;
	}

	$fooditems_object = get_post_type_object( 'fooditem' );
	$labels = $fooditems_object->labels;

	$strings['insertIntoPost'] = sprintf( __( 'Insert into %s', 'restropress' ), strtolower( $labels->singular_name ) );

	return $strings;
}
add_filter( 'media_view_strings', 'rpress_fooditem_media_strings', 10, 1 );

/**
 * Internal use only
 *
 * This function takes any hooked functions for rpress_fooditem_price_table_head and re-registers them into the rpress_fooditem_price_table_row
 * action. It will also de-register any original table_row data, so that labels appear before their setting, then re-registers the table_row.
 *
 * @since 1.0.0
 *
 * @param $arg1
 * @param $arg2
 * @param $arg3
 *
 * @return void
 */
function rpress_hijack_rpress_fooditem_price_table_head( $arg1, $arg2, $arg3 ) {

	global $wp_filter;

	$found_fields  = isset( $wp_filter['rpress_fooditem_price_table_row'] )  ? $wp_filter['rpress_fooditem_price_table_row']  : false;
	$found_headers = isset( $wp_filter['rpress_fooditem_price_table_head'] ) ? $wp_filter['rpress_fooditem_price_table_head'] : false;

	$re_register = array();

	if ( ! $found_fields && ! $found_headers ) {
		return;
	}

	foreach ( $found_fields->callbacks as $priority => $callbacks ) {
		if ( -1 === $priority ) {
			continue; // Skip our -1 priority so we don't break the interwebs
		}

		if ( is_object( $found_headers ) && property_exists( $found_headers, 'callbacks' ) && array_key_exists( $priority, $found_headers->callbacks ) ) {

			// De-register any row data.
			foreach ( $callbacks as $callback ) {
				$re_register[ $priority ][] = $callback;
				remove_action( 'rpress_fooditem_price_table_row', $callback['function'], $priority, $callback['accepted_args'] );
			}

			// Register any header data.
			foreach( $found_headers->callbacks[ $priority ] as $callback ) {
				if ( is_callable( $callback['function'] ) ) {
					add_action( 'rpress_fooditem_price_table_row', $callback['function'], $priority, 1 );
				}
			}
		}
	}

	// Now that we've re-registered our headers first...re-register the inputs
	foreach ( $re_register as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			add_action( 'rpress_fooditem_price_table_row', $callback['function'], $priority, $callback['accepted_args'] );
		}
	}
}
add_action( 'rpress_fooditem_price_table_row', 'rpress_hijack_rpress_fooditem_price_table_head', -1, 3 );