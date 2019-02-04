<?php
/**
 * Dashboard Columns
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
 * RestroPress Columns
 *
 * Defines the custom columns and their order
 *
 * @since 1.0
 * @param array $fooditem_columns Array of fooditem columns
 * @return array $fooditem_columns Updated array of fooditem columns for RestroPress
 *  Post Type List Table
 */
function rpress_fooditem_columns( $fooditem_columns ) {
	$category_labels = rpress_get_taxonomy_labels( 'addon_category' );
	$tag_labels      = rpress_get_taxonomy_labels( 'fooditem_tag' );

	$fooditem_columns = array(
		'cb'                => '<input type="checkbox"/>',
		'title'             => __( 'Name', 'restro-press' ),
		'addon_category' => $category_labels['menu_name'],
		'fooditem_tag'      => $tag_labels['menu_name'],
		'price'             => __( 'Price', 'restro-press' ),
		'earnings'          => __( 'Earnings', 'restro-press' ),
		'date'              => __( 'Date', 'restro-press' )
	);

	return apply_filters( 'rpress_fooditem_columns', $fooditem_columns );
}
add_filter( 'manage_edit-fooditem_columns', 'rpress_fooditem_columns' );

/**
 * Render FoodItem Columns
 *
 * @since 1.0
 * @param string $column_name Column name
 * @param int $post_id FoodItem (Post) ID
 * @return void
 */
function rpress_render_fooditem_columns( $column_name, $post_id ) {
	if ( get_post_type( $post_id ) == 'fooditem' ) {
		switch ( $column_name ) {
			case 'addon_category':
				echo get_the_term_list( $post_id, 'addon_category', '', ', ', '');
				break;
			case 'fooditem_tag':
				echo get_the_term_list( $post_id, 'fooditem_tag', '', ', ', '');
				break;
			case 'price':
				if ( rpress_has_variable_prices( $post_id ) ) {
					echo rpress_price_range( $post_id );
				} else {
					echo rpress_price( $post_id, false );
					echo '<input type="hidden" class="fooditemprice-' . $post_id . '" value="' . rpress_get_fooditem_price( $post_id ) . '" />';
				}
				break;
			case 'sales':
				if ( current_user_can( 'view_product_stats', $post_id ) ) {
					echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=fooditem&page=rpress-reports&tab=logs&view=sales&fooditem=' . $post_id ) ) . '">';
						echo rpress_get_fooditem_sales_stats( $post_id );
					echo '</a>';
				} else {
					echo '-';
				}
				break;
			case 'earnings':
				if ( current_user_can( 'view_product_stats', $post_id ) ) {
					echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=fooditem&page=rpress-reports&view=fooditems&fooditem-id=' . $post_id ) ) . '">';
						echo rpress_currency_filter( rpress_format_amount( rpress_get_fooditem_earnings_stats( $post_id ) ) );
					echo '</a>';
				} else {
					echo '-';
				}
				break;
		}
	}
}
add_action( 'manage_posts_custom_column', 'rpress_render_fooditem_columns', 10, 2 );

/**
 * Registers the sortable columns in the list table
 *
 * @since 1.0
 * @param array $columns Array of the columns
 * @return array $columns Array of sortable columns
 */
function rpress_sortable_fooditem_columns( $columns ) {
	$columns['price']    = 'price';
	$columns['sales']    = 'sales';
	$columns['earnings'] = 'earnings';

	return $columns;
}
add_filter( 'manage_edit-fooditem_sortable_columns', 'rpress_sortable_fooditem_columns' );

/**
 * Sorts Columns in the RestroPress List Table
 *
 * @since 1.0
 * @param array $vars Array of all the sort variables
 * @return array $vars Array of all the sort variables
 */
function rpress_sort_fooditems( $vars ) {
	// Check if we're viewing the "fooditem" post type
	if ( isset( $vars['post_type'] ) && 'fooditem' == $vars['post_type'] ) {
		// Check if 'orderby' is set to "sales"
		if ( isset( $vars['orderby'] ) && 'sales' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_rpress_fooditem_sales',
					'orderby'  => 'meta_value_num'
				)
			);
		}

		// Check if "orderby" is set to "earnings"
		if ( isset( $vars['orderby'] ) && 'earnings' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_rpress_fooditem_earnings',
					'orderby'  => 'meta_value_num'
				)
			);
		}

		// Check if "orderby" is set to "earnings"
		if ( isset( $vars['orderby'] ) && 'price' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'rpress_price',
					'orderby'  => 'meta_value_num'
				)
			);
		}
	}

	return $vars;
}

/**
 * Sets restrictions on author of RestroPress List Table
 *
 * @since 1.0
 * @param  array $vars Array of all sort varialbes
 * @return array       Array of all sort variables
 */
function rpress_filter_fooditems( $vars ) {
	if ( isset( $vars['post_type'] ) && 'fooditem' == $vars['post_type'] ) {

		// If an author ID was passed, use it
		if ( isset( $_REQUEST['author'] ) && ! current_user_can( 'view_shop_reports' ) ) {

			$author_id = $_REQUEST['author'];
			if ( (int) $author_id !== get_current_user_id() ) {
				// Tried to view the products of another person, sorry
				wp_die( __( 'You do not have permission to view this data.', 'restro-press' ), __( 'Error', 'restro-press' ), array( 'response' => 403 ) );
			}
			$vars = array_merge(
				$vars,
				array(
					'author' => get_current_user_id()
				)
			);

		}

	}

	return $vars;
}

/**
 * RestroPress Load
 *
 * Sorts the fooditems.
 *
 * @since 1.0
 * @return void
 */
function rpress_fooditem_load() {
	add_filter( 'request', 'rpress_sort_fooditems' );
	add_filter( 'request', 'rpress_filter_fooditems' );
}
add_action( 'load-edit.php', 'rpress_fooditem_load', 9999 );

/**
 * Add RestroPress Filters
 *
 * Adds taxonomy drop down filters for fooditems.
 *
 * @since 1.0
 * @return void
 */
function rpress_add_fooditem_filters() {
	global $typenow;

	// Checks if the current post type is 'fooditem'
	if ( $typenow == 'fooditem') {
		$terms = get_terms( 'addon_category' );
		if ( count( $terms ) > 0 ) {
			echo "<select name='addon_category' id='addon_category' class='postform'>";
				$category_labels = rpress_get_taxonomy_labels( 'addon_category' );
				echo "<option value=''>" . sprintf( __( 'Show all %s', 'restro-press' ), strtolower( $category_labels['name'] ) ) . "</option>";
				foreach ( $terms as $term ) {
					$selected = isset( $_GET['addon_category'] ) && $_GET['addon_category'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}
			echo "</select>";
		}

		$terms = get_terms( 'fooditem_tag' );
		if ( count( $terms ) > 0) {
			echo "<select name='fooditem_tag' id='fooditem_tag' class='postform'>";
				$tag_labels = rpress_get_taxonomy_labels( 'fooditem_tag' );
				echo "<option value=''>" . sprintf( __( 'Show all %s', 'restro-press' ), strtolower( $tag_labels['name'] ) ) . "</option>";
				foreach ( $terms as $term ) {
					$selected = isset( $_GET['fooditem_tag']) && $_GET['fooditem_tag'] == $term->slug ? ' selected="selected"' : '';
					echo '<option value="' . esc_attr( $term->slug ) . '"' . $selected . '>' . esc_html( $term->name ) .' (' . $term->count .')</option>';
				}
			echo "</select>";
		}

		if ( isset( $_REQUEST['all_posts'] ) && '1' === $_REQUEST['all_posts'] ) {
			echo '<input type="hidden" name="all_posts" value="1" />';
		} else if ( ! current_user_can( 'view_shop_reports' ) ) {
			$author_id = get_current_user_id();
			echo '<input type="hidden" name="author" value="' . esc_attr( $author_id ) . '" />';
		}
	}

}
add_action( 'restrict_manage_posts', 'rpress_add_fooditem_filters', 100 );

/**
 * Remove RestroPress Month Filter
 *
 * Removes the drop down filter for fooditems by date.
 *
 * @author RestroPress
 * @since  1.0.0
 * @param array $dates The preset array of dates
 * @global $typenow The post type we are viewing
 * @return array Empty array disables the dropdown
 */
function rpress_remove_month_filter( $dates ) {
	global $typenow;

	if ( $typenow == 'fooditem' ) {
		$dates = array();
	}

	return $dates;
}
add_filter( 'months_dropdown_results', 'rpress_remove_month_filter', 99 );

/**
 * Adds price field to Quick Edit options
 *
 * @since  1.0.0
 * @param string $column_name Name of the column
 * @param string $post_type Current Post Type (i.e. fooditem)
 * @return void
 */
function rpress_price_field_quick_edit( $column_name, $post_type ) {
	if ( $column_name != 'price' || $post_type != 'fooditem' ) return;
	?>
	<fieldset class="inline-edit-col-left">
		<div id="rpress-fooditem-data" class="inline-edit-col">
			<h4><?php echo sprintf( __( '%s Configuration', 'restro-press' ), rpress_get_label_singular() ); ?></h4>
			<label>
				<span class="title"><?php _e( 'Price', 'restro-press' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_rpress_regprice" class="text regprice" />
				</span>
			</label>
			<br class="clear" />
		</div>
	</fieldset>
	<?php
}
add_action( 'quick_edit_custom_box', 'rpress_price_field_quick_edit', 10, 2 );
add_action( 'bulk_edit_custom_box', 'rpress_price_field_quick_edit', 10, 2 );

/**
 * Updates price when saving post
 *
 * @since  1.0.0
 * @param int $post_id RestroPress (Post) ID
 * @return void
 */
function rpress_price_save_quick_edit( $post_id ) {
	if ( ! isset( $_POST['post_type']) || 'fooditem' !== $_POST['post_type'] ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

	if ( isset( $_REQUEST['_rpress_regprice'] ) ) {
		update_post_meta( $post_id, 'rpress_price', strip_tags( stripslashes( $_REQUEST['_rpress_regprice'] ) ) );
	}
}
add_action( 'save_post', 'rpress_price_save_quick_edit' );

/**
 * Process bulk edit actions via AJAX
 *
 * @since  1.0.0
 * @return void
 */
function rpress_save_bulk_edit() {

	$post_ids = ( isset( $_POST['post_ids'] ) && ! empty( $_POST['post_ids'] ) ) ? $_POST['post_ids'] : array();

	if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
		$price = isset( $_POST['price'] ) ? strip_tags( stripslashes( $_POST['price'] ) ) : 0;
		foreach ( $post_ids as $post_id ) {

			if( ! current_user_can( 'edit_post', $post_id ) ) {
				continue;
			}

			if ( ! empty( $price ) ) {
				update_post_meta( $post_id, 'rpress_price', rpress_sanitize_amount( $price ) );
			}
		}
	}

	die();
}
add_action( 'wp_ajax_rpress_save_bulk_edit', 'rpress_save_bulk_edit' );
