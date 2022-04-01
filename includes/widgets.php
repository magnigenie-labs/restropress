<?php
/**
 * Widgets
 *
 * Widgets related funtions and widget registration.
 *
 * @package     RPRESS
 * @subpackage  Widgets
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
|--------------------------------------------------------------------------
| FRONT-END WIDGETS
|--------------------------------------------------------------------------
|
| - Cart Widget
| - Categories / Tags Widget
|
*/

/**
 * Cart Widget.
 *
 * RestroPress cart widget class.
 *
 * @since 1.0
 * @return void
*/
class rpress_cart_widget extends WP_Widget {
	/** Constructor */
	function __construct() {
		parent::__construct( 'rpress_cart_widget', __( 'RestroPress Cart', 'restropress' ), array( 'description' => __( 'Display the RestroPress order totals', 'restropress' ) ) );
		add_filter( 'dynamic_sidebar_params', array( $this, 'cart_widget_class' ), 10, 1 );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {

		if ( ! empty( $instance['hide_on_checkout'] ) && rpress_is_checkout() ) {
			return;
		}

		$args['id']        = ( isset( $args['id'] ) ) ? $args['id'] : 'rpress_cart_widget';
		$instance['title'] = ( isset( $instance['title'] ) ) ? $instance['title'] : '';

		$title = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );

		echo esc_html( $args['before_widget'] );

		if ( $title ) {
			echo esc_html( $args['before_title'] . $title . $args['after_title'] );
		}

		do_action( 'rpress_before_cart_widget' );

		rpress_shopping_cart( true );

		do_action( 'rpress_after_cart_widget' );

		echo esc_html( $args['after_widget'] );
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']            = strip_tags( $new_instance['title'] );
		$instance['hide_on_checkout'] = isset( $new_instance['hide_on_checkout'] );
		$instance['hide_on_empty']    = isset( $new_instance['hide_on_empty'] );

		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {

		$defaults = array(
			'title'            => '',
			'hide_on_checkout' => false,
			'hide_on_empty'    => false,
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'restropress' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_html( $instance['title'] ); ?>"/>
		</p>

		<!-- Hide on Checkout Page -->
		<p>
			<input <?php checked( $instance['hide_on_checkout'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_on_checkout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_on_checkout' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_on_checkout' ) ); ?>"><?php esc_html_e( 'Hide on Checkout Page', 'restropress' ); ?></label>
		</p>

		<!-- Hide when cart is empty -->
		<p>
			<input <?php checked( $instance['hide_on_empty'], true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_on_empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_on_empty' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_on_empty' ) ); ?>"><?php esc_html_e( 'Hide if cart is empty', 'restropress' ); ?></label>
		</p>

		<?php
	}

	/**
	 * Check if the widget needs to be hidden when empty.
	 *
	 * @since 2.7
	 * @param $params
	 *
	 * @return array
	 */
	public function cart_widget_class( $params ) {
		if ( strpos( $params[0]['widget_id'], 'rpress_cart_widget' ) !== false ) {
			$instance_id       = $params[1]['number'];
			$all_settings      = $this->get_settings();
			$instance_settings = $all_settings[ $instance_id ];

			if ( ! empty( $instance_settings['hide_on_empty'] ) ) {
				$cart_quantity = rpress_get_cart_quantity();
				$class         = empty( $cart_quantity ) ? 'cart-empty' : 'cart-not-empty';

				$params[0]['before_widget'] = preg_replace( '/class="(.*?)"/', 'class="$1 rpress-hide-on-empty ' . $class . '"', $params[0]['before_widget'] );
			}
		}

		return $params;
	}

}

/**
 * Categories / Tags Widget.
 *
 * RestroPress categories / tags widget class.
 *
 * @since 1.0
 * @return void
*/
class rpress_categories_tags_widget extends WP_Widget {
	/** Constructor */
	function __construct() {
		parent::__construct( 'rpress_categories_tags_widget', __( 'RestroPress Categories / Tags', 'restropress' ), array( 'description' => __( 'Display the fooditems categories or tags', 'restropress' ) ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		// Set defaults.
		$args['id']           = ( isset( $args['id'] ) ) ? $args['id'] : 'rpress_categories_tags_widget';
		$instance['title']    = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$instance['taxonomy'] = ( isset( $instance['taxonomy'] ) ) ? $instance['taxonomy'] : 'addon_category';

		$title      = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$tax        = $instance['taxonomy'];
		$count      = isset( $instance['count'] ) && $instance['count'] == 'on' ? 1 : 0;
		$hide_empty = isset( $instance['hide_empty'] ) && $instance['hide_empty'] == 'on' ? 1 : 0;

		echo esc_html( $args['before_widget'] );

		if ( $title ) {
			echo esc_html( $args['before_title'] . $title . $args['after_title'] );
		}

		do_action( 'rpress_before_taxonomy_widget' );

		echo "<ul class=\"rpress-taxonomy-widget\">\n";
			wp_list_categories( 'title_li=&taxonomy=' . $tax . '&show_count=' . $count . '&hide_empty=' . $hide_empty );
		echo "</ul>\n";

		do_action( 'rpress_after_taxonomy_widget' );

		echo esc_html( $args['after_widget'] );
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance               = $old_instance;
		$instance['title']      = strip_tags( $new_instance['title'] );
		$instance['taxonomy']   = strip_tags( $new_instance['taxonomy'] );
		$instance['count']      = isset( $new_instance['count'] ) ? $new_instance['count'] : '';
		$instance['hide_empty'] = isset( $new_instance['hide_empty'] ) ? $new_instance['hide_empty'] : '';
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		// Set up some default widget settings.
		$defaults = array(
			'title'         => '',
			'taxonomy'      => 'addon_category',
			'count'         => 'off',
			'hide_empty'    => 'off',
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'restropress' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_html( $instance['title'] ); ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>"><?php esc_html_e( 'Taxonomy:', 'restropress' ); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'taxonomy' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'taxonomy' ) ); ?>">
				<?php
				$category_labels = rpress_get_taxonomy_labels( 'addon_category' );
				$tag_labels      = rpress_get_taxonomy_labels( 'fooditem_tag' );
				?>
				<option value="addon_category" <?php selected( 'addon_category', $instance['taxonomy'] ); ?>><?php echo esc_html( $category_labels['name'] ); ?></option>
				<option value="fooditem_tag" <?php selected( 'fooditem_tag', $instance['taxonomy'] ); ?>><?php echo esc_html( $tag_labels['name'] ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Show Count:', 'restropress' ); ?></label>
			<input <?php checked( $instance['count'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="checkbox" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>"><?php esc_html_e( 'Hide Empty Categories:', 'restropress' ); ?></label>
			<input <?php checked( $instance['hide_empty'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'hide_empty' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'hide_empty' ) ); ?>" type="checkbox" />
		</p>
	<?php
	}
}


/**
 * Product Details Widget.
 *
 * Displays a product's details in a widget.
 *
 * @since 1.9
 * @return void
 */
class RPRESS_Product_Details_Widget extends WP_Widget {

	/** Constructor */
	public function __construct() {
		parent::__construct(
			'rpress_product_details',
			sprintf( __( '%s Details', 'restropress' ), rpress_get_label_singular() ),
			array(
				'description' => sprintf( __( 'Display the details of a specific %s', 'restropress' ), rpress_get_label_singular() ),
			)
		);
	}

	/** @see WP_Widget::widget */
	public function widget( $args, $instance ) {
		$args['id'] = ( isset( $args['id'] ) ) ? $args['id'] : 'rpress_fooditem_details_widget';

		if ( ! empty( $instance['fooditem_id'] ) ) {
			if ( 'current' === ( $instance['fooditem_id'] ) ) {
				$instance['display_type'] = 'current';
				unset( $instance['fooditem_id'] );
			} elseif ( is_numeric( $instance['fooditem_id'] ) ) {
				$instance['display_type'] = 'specific';
			}
		}

		if ( ! isset( $instance['display_type'] ) || ( 'specific' === $instance['display_type'] && ! isset( $instance['fooditem_id'] ) ) || ( 'current' == $instance['display_type'] && ! is_singular( 'fooditem' ) ) ) {
			return;
		}

		// set correct fooditem ID.
		if ( 'current' == $instance['display_type'] && is_singular( 'fooditem' ) ) {
			$fooditem_id = get_the_ID();
		} else {
			$fooditem_id = absint( $instance['fooditem_id'] );
		}

		// Since we can take a typed in value, make sure it's a fooditem we're looking for
		$fooditem = get_post( $fooditem_id );
		if ( ! is_object( $fooditem ) || 'fooditem' !== $fooditem->post_type ) {
			return;
		}

		// Variables from widget settings.
		$title           = apply_filters( 'widget_title', $instance['title'], $instance, $args['id'] );
		$fooditem_title  = $instance['fooditem_title'] ? apply_filters( 'rpress_product_details_widget_fooditem_title', '<h3>' . get_the_title( $fooditem_id ) . '</h3>', $fooditem_id ) : '';
		$purchase_button = $instance['purchase_button'] ? apply_filters( 'rpress_product_details_widget_purchase_button', rpress_get_purchase_link( array( 'fooditem_id' => $fooditem_id ) ), $fooditem_id ) : '';
		$categories      = $instance['categories'] ? $instance['categories'] : '';
		$tags            = $instance['tags'] ? $instance['tags'] : '';

		// Used by themes. Opens the widget.
		echo esc_html( $args['before_widget'] );

		// Display the widget title.
		if( $title ) {
			echo esc_html( $args['before_title'] . $title . $args['after_title'] );
		}

		do_action( 'rpress_product_details_widget_before_title' , $instance , $fooditem_id );

		// fooditem title.
		echo esc_html( $fooditem_title );

		do_action( 'rpress_product_details_widget_before_purchase_button' , $instance , $fooditem_id );
		// purchase button.
		echo esc_html( $purchase_button );

		// categories and tags.
		$category_list  = false;
		$category_label = '';
		if ( $categories ) {

			$category_terms = get_the_terms( $fooditem_id, 'addon_category' );

			if ( $category_terms && ! is_wp_error( $category_terms ) ) {
				$category_list     = get_the_term_list( $fooditem_id, 'addon_category', '', ', ' );
				$category_count    = count( $category_terms );
				$category_labels   = rpress_get_taxonomy_labels( 'addon_category' );
				$category_label    = $category_count > 1 ? $category_labels['name'] : $category_labels['singular_name'];
			}

		}

		$tag_list  = false;
		$tag_label = '';
		if ( $tags ) {

			$tag_terms = get_the_terms( $fooditem_id, 'fooditem_tag' );

			if ( $tag_terms && ! is_wp_error( $tag_terms ) ) {
				$tag_list     = get_the_term_list( $fooditem_id, 'fooditem_tag', '', ', ' );
				$tag_count    = count( $tag_terms );
				$tag_taxonomy = rpress_get_taxonomy_labels( 'fooditem_tag' );
				$tag_label    = $tag_count > 1 ? $tag_taxonomy['name'] : $tag_taxonomy['singular_name'];
			}

		}


		$text = '';

		if( $category_list || $tag_list ) {
			$text .= '<p class="rpress-meta">';

			if( $category_list ) {

				$text .= '<span class="categories">%1$s: %2$s</span><br/>';
			}

			if ( $tag_list ) {
				$text .= '<span class="tags">%3$s: %4$s</span>';
			}

			$text .= '</p>';
		}

		do_action( 'rpress_product_details_widget_before_categories_and_tags', $instance, $fooditem_id );

		printf( $text, $category_label, $category_list, $tag_label, $tag_list );

		do_action( 'rpress_product_details_widget_before_end', $instance, $fooditem_id );

		// Used by themes. Closes the widget.
		echo esc_html( $args['after_widget'] );
	}

	/** @see WP_Widget::form */
	public function form( $instance ) {
		// Set up some default widget settings.
		$defaults = array(
			'title'           => sprintf( __( '%s Details', 'restropress' ), rpress_get_label_singular() ),
			'display_type'    => 'current',
			'fooditem_id'     => false,
			'fooditem_title'  => 'on',
			'purchase_button' => 'on',
			'categories'      => 'on',
			'tags'            => 'on',
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<?php
		if ( 'current' === ( $instance['fooditem_id'] ) ) {
			$instance['display_type'] = 'current';
			$instance['fooditem_id']  = false;
		} elseif ( is_numeric( $instance['fooditem_id'] ) ) {
			$instance['display_type'] = 'specific';
		}

		?>

		<!-- Title -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'restropress' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_html( $instance['title'] ); ?>" />
		</p>

		<p>
			<?php esc_html_e( 'Display Type:', 'restropress' ); ?><br />
			<input type="radio" onchange="jQuery(this).parent().next('.fooditem-details-selector').hide();" <?php checked( 'current', $instance['display_type'], true ); ?> value="current" name="<?php echo esc_attr( $this->get_field_name( 'display_type' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-current"><label for="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-current"><?php esc_html_e( 'Current', 'restropress' ); ?></label>
			<input type="radio" onchange="jQuery(this).parent().next('.fooditem-details-selector').show();" <?php checked( 'specific', $instance['display_type'], true ); ?> value="specific" name="<?php echo esc_attr( $this->get_field_name( 'display_type' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-specific"><label for="<?php echo esc_attr( $this->get_field_id( 'display_type' ) ); ?>-specific"><?php esc_html_e( 'Specific', 'restropress' ); ?></label>
		</p>

		<!-- RestroPress -->
		<?php $display = 'current' === $instance['display_type'] ? ' style="display: none;"' : ''; ?>
		<p class="fooditem-details-selector" <?php echo esc_html( $display ); ?>>
		<label for="<?php echo esc_attr( $this->get_field_id( 'fooditem_id' ) ); ?>"><?php printf( __( '%s:', 'restropress' ), rpress_get_label_singular() ); ?></label>
		<?php $fooditem_count = wp_count_posts( 'fooditem' ); ?>
		<?php if ( $fooditem_count->publish < 1000 ) : ?>
			<?php
			$args = array(
				'post_type'      => 'fooditem',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			);
			$fooditems = get_posts( $args );
			?>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'fooditem_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'fooditem_id' ) ); ?>">
			<?php foreach ( $fooditems as $fooditem ) { ?>
				<option <?php selected( absint( $instance['fooditem_id'] ), $fooditem->ID ); ?> value="<?php echo esc_attr( $fooditem->ID ); ?>"><?php echo esc_html( $fooditem->post_title ); ?></option>
			<?php } ?>
			</select>
		<?php else: ?>
			<br />
			<input type="text" value="<?php echo esc_attr( $instance['fooditem_id'] ); ?>" placeholder="<?php printf( __( '%s ID', 'restropress' ), rpress_get_label_singular() ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'fooditem_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'fooditem_id' ) ); ?>">
		<?php endif; ?>
		</p>

		<!-- Download title -->
		<p>
			<input <?php checked( $instance['fooditem_title'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'fooditem_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'fooditem_title' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'fooditem_title' ) ); ?>"><?php printf( __( 'Show %s Title', 'restropress' ), rpress_get_label_singular() ); ?></label>
		</p>

		<!-- Show purchase button -->
		<p>
			<input <?php checked( $instance['purchase_button'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'purchase_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'purchase_button' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'purchase_button' ) ); ?>"><?php esc_html_e( 'Show Purchase Button', 'restropress' ); ?></label>
		</p>

		<!-- Show fooditem categories -->
		<p>
			<?php $category_labels = rpress_get_taxonomy_labels( 'addon_category' ); ?>
			<input <?php checked( $instance['categories'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'categories' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'categories' ) ); ?>"><?php printf( __( 'Show %s', 'restropress' ), $category_labels['name'] ); ?></label>
		</p>

		<!-- Show fooditem tags -->
		<p>
			<?php $tag_labels = rpress_get_taxonomy_labels( 'fooditem_tag' ); ?>
			<input <?php checked( $instance['tags'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'tags' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'tags' ) ); ?>"><?php printf( __( 'Show %s', 'restropress' ), $tag_labels['name'] ); ?></label>
		</p>

		<?php do_action( 'rpress_product_details_widget_form' , $instance ); ?>
	<?php }

	/** @see WP_Widget::update */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']           = strip_tags( $new_instance['title'] );
		$instance['fooditem_id']     = strip_tags( $new_instance['fooditem_id'] );
		$instance['display_type']    = isset( $new_instance['display_type'] )    ? strip_tags( $new_instance['display_type'] ) : '';
		$instance['fooditem_title']  = isset( $new_instance['fooditem_title'] )  ? $new_instance['fooditem_title']  : '';
		$instance['purchase_button'] = isset( $new_instance['purchase_button'] ) ? $new_instance['purchase_button'] : '';
		$instance['categories']      = isset( $new_instance['categories'] )      ? $new_instance['categories']      : '';
		$instance['tags']            = isset( $new_instance['tags'] )            ? $new_instance['tags']            : '';

		do_action( 'rpress_product_details_widget_update', $instance );

		// If the new view is 'current fooditem' then remove the specific fooditem ID
		if ( 'current' === $instance['display_type'] ) {
			unset( $instance['fooditem_id'] );
		}

		return $instance;
	}

}



/**
 * Register Widgets.
 *
 * Registers the RPRESS Widgets.
 *
 * @since 1.0
 * @return void
 */
function rpress_register_widgets() {
	register_widget( 'rpress_cart_widget' );
	register_widget( 'rpress_categories_tags_widget' );
	register_widget( 'rpress_product_details_widget' );
}
add_action( 'widgets_init', 'rpress_register_widgets' );
