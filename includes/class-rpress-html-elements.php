<?php
/**
 * HTML elements
 *
 * A helper class for outputting common HTML elements, such as product drop downs
 *
 * @package     RPRESS
 * @subpackage  Classes/HTML
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_HTML_Elements Class
 *
 * @since 1.0
 */
class RPRESS_HTML_Elements {

	/**
	 * Renders an HTML Dropdown of all the Products
	 *
	 * @since 1.0
	 * @param array $args Arguments for the dropdown
	 * @return string $output Product dropdown
	 */
	public function product_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'products',
			'id'          => 'products',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => false,
			'number'      => -1,
			'bundles'     => true,
			'variations'  => false,
			'placeholder' => sprintf( __( 'Choose a %s', 'restropress' ), rpress_get_label_singular() ),
			'data'        => array(
				'search-type'        => 'fooditem',
				'search-placeholder' => sprintf( __( 'Type to search all %s', 'restropress' ), rpress_get_label_plural() )
			),
		);

		$args = wp_parse_args( $args, $defaults );

		$product_args = array(
			'post_type'      => 'fooditem',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'posts_per_page' => $args['number'],
		);

		if ( ! current_user_can( 'edit_products' ) ) {
			$product_args['post_status'] = apply_filters( 'rpress_product_dropdown_status_nopriv', array( 'publish' ) );
		} else {
			$product_args['post_status'] = apply_filters( 'rpress_product_dropdown_status', array( 'publish', 'draft', 'private', 'future' ) );
		}

		if ( is_array( $product_args['post_status'] ) ) {

			// Given the array, sanitize them.
			$product_args['post_status'] = array_map( 'sanitize_text_field', $product_args['post_status'] );

		} else {

			// If we didn't get an array, fallback to 'publish'.
			$product_args['post_status'] = array( 'publish' );

		}

		// Maybe disable bundles
		if( ! $args['bundles'] ) {
			$product_args['meta_query'] = array(
				'relation'       => 'AND',
				array(
					'key'        => '_rpress_product_type',
					'value'      => 'bundle',
					'compare'    => 'NOT EXISTS'
				)
			);
		}

		$product_args = apply_filters( 'rpress_product_dropdown_args', $product_args );

		// Since it's possible to have selected items not within the queried limit, we need to include the selected items.
		$products     = get_posts( $product_args );
		$existing_ids = wp_list_pluck( $products, 'ID' );
		if ( ! empty( $args['selected'] ) ) {

			$selected_items = $args['selected'];
			if ( ! is_array( $selected_items ) ) {
				$selected_items = array( $selected_items );
			}

			foreach ( $selected_items as $selected_item ) {
				if ( ! in_array( $selected_item, $existing_ids ) ) {

					// If the selected item has a variation, we just need the product ID.
					$has_variation = strpos( $selected_item, '_' );
					if ( false !== $has_variation ) {
						$selected_item = substr( $selected_item, 0, $has_variation );
					}

					$post       = get_post( $selected_item );
					if ( ! is_null( $post ) ) {
						$products[] = $post;
					}
				}
			}

		}

		$options    = array();
		$options[0] = '';
		if ( $products ) {
			foreach ( $products as $product ) {
				$options[ absint( $product->ID ) ] = esc_html( $product->post_title );
				if ( $args['variations'] && rpress_has_variable_prices( $product->ID ) ) {
 					$prices = rpress_get_variable_prices( $product->ID );
 					foreach ( $prices as $key => $value ) {
 						$name = ! empty( $value['name'] )   ? $value['name']   : '';
 						if ( $name ) {
 							$options[ absint( $product->ID ) . '_' . $key ] = esc_html( $product->post_title . ': ' . $name );
 						}
 					}
 				}
			}
		}

		// This ensures that any selected products are included in the drop down
		if ( is_array( $args['selected'] ) ) {

			foreach( $args['selected'] as $item ) {

				if ( ! array_key_exists( $item, $options ) ) {

					$parsed_item = rpress_parse_product_dropdown_value( $item );

 					if ( $parsed_item['price_id'] !== false ) {

						$prices = rpress_get_variable_prices( (int) $parsed_item['fooditem_id'] );
						foreach ( $prices as $key => $value ) {

							$name = ( isset( $value['name'] ) && ! empty( $value['name'] ) ) ? $value['name']   : '';

							if ( $name && (int) $parsed_item['price_id'] === (int) $key  ) {

								$options[ absint( $product->ID ) . '_' . $key ] = esc_html( get_the_title( (int) $parsed_item['fooditem_id'] ) . ': ' . $name );

						    }

 						}

 					} else {

 						$options[ $parsed_item['fooditem_id'] ] = get_the_title( $parsed_item['fooditem_id'] );

 					}
 				}

			}

		} elseif ( false !== $args['selected'] && $args['selected'] !== 0 ) {

			if ( ! array_key_exists( $args['selected'], $options ) ) {

				$parsed_item = rpress_parse_product_dropdown_value( $args['selected'] );
				if ( $parsed_item['price_id'] !== false ) {

					$prices = rpress_get_variable_prices( (int) $parsed_item['fooditem_id'] );

					foreach ( $prices as $key => $value ) {

						$name = ( isset( $value['name'] ) && ! empty( $value['name'] ) ) ? $value['name']   : '';

						if ( $name && (int) $parsed_item['price_id'] === (int) $key  ) {

							$options[ absint( $product->ID ) . '_' . $key ] = esc_html( get_the_title( (int) $parsed_item['fooditem_id'] ) . ': ' . $name );

						}

					}

				} else {

					$options[ $parsed_item['fooditem_id'] ] = get_the_title( $parsed_item['fooditem_id'] );

				}

			}

		}

		if ( ! $args['bundles'] ) {
			$args['class'] .= ' no-bundles';
		}

		if ( $args['variations'] ) {
			$args['class'] .= ' variations';
		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'],
			'options'          => $options,
			'chosen'           => $args['chosen'],
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'show_option_all'  => false,
			'show_option_none' => false,
			'data'             => $args['data'],
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all customers
	 *
	 * @since  1.0.0
	 * @param array $args
	 * @return string $output Customer dropdown
	 */
	public function customer_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'customers',
			'id'          => 'customers',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => true,
			'placeholder' => __( 'Select a Customer', 'restropress' ),
			'number'      => 30,
			'data'        => array(
				'search-type'        => 'customer',
				'search-placeholder' => __( 'Type to search all Customers', 'restropress' )
			),
		);

		$args = wp_parse_args( $args, $defaults );

		$customers = RPRESS()->customers->get_customers( array(
			'number' => $args['number']
		) );

		$options = array();

		if ( $customers ) {
			$options[0] = __( 'No customer attached', 'restropress' );
			foreach ( $customers as $customer ) {
				$options[ absint( $customer->id ) ] = esc_html( $customer->name . ' (' . $customer->email . ')' );
			}
		} else {
			$options[0] = __( 'No customers found', 'restropress' );
		}

		if( ! empty( $args['selected'] ) ) {

			// If a selected customer has been specified, we need to ensure it's in the initial list of customers displayed

			if( ! array_key_exists( $args['selected'], $options ) ) {

				$customer = new RPRESS_Customer( $args['selected'] );

				if( $customer ) {

					$options[ absint( $args['selected'] ) ] = esc_html( $customer->name . ' (' . $customer->email . ')' );

				}

			}

		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'] . ' rpress-customer-select',
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'chosen'           => $args['chosen'],
			'show_option_all'  => false,
			'show_option_none' => false,
			'data'             => $args['data'],
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Users
	 *
	 * @since 1.0.0.9
	 * @param array $args
	 * @return string $output User dropdown
	 */
	public function user_dropdown( $args = array() ) {

		$defaults = array(
			'name'        => 'users',
			'id'          => 'users',
			'class'       => '',
			'multiple'    => false,
			'selected'    => 0,
			'chosen'      => true,
			'placeholder' => __( 'Select a User', 'restropress' ),
			'number'      => 30,
			'data'        => array(
				'search-type'        => 'user',
				'search-placeholder' => __( 'Type to search all Users', 'restropress' ),
			),
		);

		$args = wp_parse_args( $args, $defaults );


		$user_args = array(
			'number' => $args['number'],
		);
		$users   = get_users( $user_args );
		$options = array();

		if ( $users ) {
			foreach ( $users as $user ) {
				$options[ $user->ID ] = esc_html( $user->display_name );
			}
		} else {
			$options[0] = __( 'No users found', 'restropress' );
		}

		// If a selected user has been specified, we need to ensure it's in the initial list of user displayed
		if( ! empty( $args['selected'] ) ) {

			if( ! array_key_exists( $args['selected'], $options ) ) {

				$user = get_userdata( $args['selected'] );

				if( $user ) {

					$options[ absint( $args['selected'] ) ] = esc_html( $user->display_name );

				}

			}

		}

		$output = $this->select( array(
			'name'             => $args['name'],
			'selected'         => $args['selected'],
			'id'               => $args['id'],
			'class'            => $args['class'] . ' rpress-user-select',
			'options'          => $options,
			'multiple'         => $args['multiple'],
			'placeholder'      => $args['placeholder'],
			'chosen'           => $args['chosen'],
			'show_option_all'  => false,
			'show_option_none' => false,
			'data'             => $args['data'],
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Discounts
	 *
	 * @since 1.0
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Discount to select automatically
	 * @param string $status Discount post_status to retrieve
	 * @return string $output Discount dropdown
	 */
	public function discount_dropdown( $name = 'rpress_discounts', $selected = 0, $status = '' ) {
		$args = array( 'nopaging' => true );

		if ( ! empty( $status ) )
			$args['post_status'] = $status;

		$discounts = rpress_get_discounts( $args );
		$options   = array();

		if ( $discounts ) {
			foreach ( $discounts as $discount ) {
				$options[ absint( $discount->ID ) ] = esc_html( get_the_title( $discount->ID ) );
			}
		} else {
			$options[0] = __( 'No discounts found', 'restropress' );
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => __( 'Select a discount', 'restropress' ),
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of all the Categories
	 *
	 * @since 1.0
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Category to select automatically
	 * @return string $output Category dropdown
	 */
	public function category_dropdown( $name = 'rpress_categories', $selected = 0 ) {
		$categories = get_terms( 'addon_category', apply_filters( 'rpress_category_dropdown', array() ) );
		$options    = array();

		foreach ( $categories as $category ) {
			$options[ absint( $category->term_id ) ] = esc_html( $category->name );
		}

		$category_labels = rpress_get_taxonomy_labels( 'addon_category' );
		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => sprintf( _x( 'All %s', 'plural: Example: "All Categories"', 'restropress' ), $category_labels['name'] ),
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of years
	 *
	 * @since 1.0
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Year to select automatically
	 * @param int    $years_before Number of years before the current year the dropdown should start with
	 * @param int    $years_after Number of years after the current year the dropdown should finish at
	 * @return string $output Year dropdown
	 */
	public function year_dropdown( $name = 'year', $selected = 0, $years_before = 5, $years_after = 0 ) {
		$current     = date( 'Y' );
		$start_year  = $current - absint( $years_before );
		$end_year    = $current + absint( $years_after );
		$selected    = empty( $selected ) ? date( 'Y' ) : $selected;
		$options     = array();

		while ( $start_year <= $end_year ) {
			$options[ absint( $start_year ) ] = $start_year;
			$start_year++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown of months
	 *
	 * @since 1.0
	 * @param string $name Name attribute of the dropdown
	 * @param int    $selected Month to select automatically
	 * @return string $output Month dropdown
	 */
	public function month_dropdown( $name = 'month', $selected = 0 ) {
		$month   = 1;
		$options = array();
		$selected = empty( $selected ) ? date( 'n' ) : $selected;

		while ( $month <= 12 ) {
			$options[ absint( $month ) ] = rpress_month_num_to_name( $month );
			$month++;
		}

		$output = $this->select( array(
			'name'             => $name,
			'selected'         => $selected,
			'options'          => $options,
			'show_option_all'  => false,
			'show_option_none' => false
		) );

		return $output;
	}

	/**
	 * Renders an HTML Dropdown
	 *
	 * @since  1.0.0
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function select( $args = array() ) {
		$defaults = array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => array(),
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'restropress' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'restropress' ),
			'data'             => array(),
			'readonly'         => false,
			'disabled'         => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$data_elements = '';
		foreach ( $args['data'] as $key => $value ) {
			$data_elements .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}

		if( $args['multiple'] ) {
			$multiple = ' MULTIPLE';
		} else {
			$multiple = '';
		}

		if( $args['chosen'] ) {
			$args['class'] .= ' rpress-select-chosen';
			if ( is_rtl() ) {
				$args['class'] .= ' chosen-rtl';
			}
		}

		if( $args['placeholder'] ) {
			$placeholder = $args['placeholder'];
		} else {
			$placeholder = '';
		}

		if ( isset( $args['readonly'] ) && $args['readonly'] ) {
			$readonly = ' readonly="readonly"';
		} else {
			$readonly = '';
		}

		if ( isset( $args['disabled'] ) && $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		} else {
			$disabled = '';
		}

		$class  = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$output = '<select' . $disabled . $readonly . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( rpress_sanitize_key( str_replace( '-', '_', $args['id'] ) ) ) . '" class="rpress-select ' . $class . '"' . $multiple . ' data-placeholder="' . $placeholder . '"'. $data_elements . '>';

		if ( ! isset( $args['selected'] ) || ( is_array( $args['selected'] ) && empty( $args['selected'] ) ) || ! $args['selected'] ) {
			$selected = "";
		}

		if ( $args['show_option_all'] ) {
			if ( $args['multiple'] && ! empty( $args['selected'] ) ) {
				$selected = selected( true, in_array( 0, $args['selected'] ), false );
			} else {
				$selected = selected( $args['selected'], 0, false );
			}
			$output .= '<option value="all"' . $selected . '>' . esc_html( $args['show_option_all'] ) . '</option>';
		}

		if ( ! empty( $args['options'] ) ) {
			if ( $args['show_option_none'] ) {
				if ( $args['multiple'] ) {
					$selected = selected( true, in_array( -1, $args['selected'] ), false );
				} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) && ! empty( $args['selected'] ) ) {
					$selected = selected( $args['selected'], -1, false );
				}
				$output .= '<option value="-1"' . $selected . '>' . esc_html( $args['show_option_none'] ) . '</option>';
			}

			foreach ( $args['options'] as $key => $option ) {
				if ( $args['multiple'] && is_array( $args['selected'] ) ) {
					$selected = selected( true, in_array( (string) $key, $args['selected'] ), false );
				} elseif ( isset( $args['selected'] ) && ! is_array( $args['selected'] ) ) {
					$selected = selected( $args['selected'], $key, false );
				}

				$output .= '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option ) . '</option>';
			}
		}

		$output .= '</select>';

		return $output;
	}

	/**
	 * Renders an HTML Checkbox
	 *
	 * @since  1.0.0
	 *
	 * @param array $args
	 *
	 * @return string Checkbox HTML code
	 */
	public function checkbox( $args = array() ) {
		$defaults = array(
			'name'     => null,
			'current'  => null,
			'class'    => 'rpress-checkbox',
			'options'  => array(
				'disabled' => false,
				'readonly' => false
			)
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$options = '';
		if ( ! empty( $args['options']['disabled'] ) ) {
			$options .= ' disabled="disabled"';
		} elseif ( ! empty( $args['options']['readonly'] ) ) {
			$options .= ' readonly';
		}

		$output = '<input type="checkbox"' . $options . ' name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['name'] ) . '" class="' . $class . ' ' . esc_attr( $args['name'] ) . '" ' . checked( 1, $args['current'], false ) . ' />';

		return $output;
	}

	/**
	 * Renders an HTML Text field
	 *
	 * @since 1.0
	 *
	 * @param array $args Arguments for the text field
	 * @return string Text field
	 */
	public function text( $args = array() ) {
		// Backwards compatibility
		if ( func_num_args() > 1 ) {
			$args = func_get_args();

			$name  = $args[0];
			$value = isset( $args[1] ) ? $args[1] : '';
			$label = isset( $args[2] ) ? $args[2] : '';
			$desc  = isset( $args[3] ) ? $args[3] : '';
		}

		$defaults = array(
			'id'           => '',
			'name'         => isset( $name )  ? $name  : 'text',
			'value'        => isset( $value ) ? $value : null,
			'label'        => isset( $label ) ? $label : null,
			'desc'         => isset( $desc )  ? $desc  : null,
			'placeholder'  => '',
			'class'        => 'regular-text',
			'disabled'     => false,
			'autocomplete' => '',
			'data'         => false
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		if( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$data = '';
		if ( ! empty( $args['data'] ) ) {
			foreach ( $args['data'] as $key => $value ) {
				$data .= 'data-' . rpress_sanitize_key( $key ) . '="' . esc_attr( $value ) . '" ';
			}
		}

		$output = '<span id="rpress-' . rpress_sanitize_key( $args['name'] ) . '-wrap">';
			if ( ! empty( $args['label'] ) ) {
				$output .= '<label class="rpress-label" for="' . rpress_sanitize_key( $args['id'] ) . '">' . esc_html( $args['label'] ) . '</label>';
			}

			if ( ! empty( $args['desc'] ) ) {
				$output .= '<span class="rpress-description">' . esc_html( $args['desc'] ) . '</span>';
			}

			$output .= '<input type="text" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] )  . '" autocomplete="' . esc_attr( $args['autocomplete'] )  . '" value="' . esc_attr( $args['value'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" class="' . $class . '" ' . $data . '' . $disabled . '/>';

		$output .= '</span>';

		return $output;
	}
	/**
	 * Renders a date picker
	 *
	 * @since 2.4
	 *
	 * @param array $args Arguments for the text field
	 * @return string Datepicker field
	 */
	public function date_field( $args = array() ) {

		if( empty( $args['class'] ) ) {
			$args['class'] = 'rpress_datepicker';
		} elseif( ! strpos( $args['class'], 'rpress_datepicker' ) ) {
			$args['class'] .= ' rpress_datepicker';
		}

		return $this->text( $args );
	}

	/**
	 * Renders an HTML textarea
	 *
	 * @since  1.0.0
	 *
	 * @param array $args Arguments for the textarea
	 * @return string textarea
	 */
	public function textarea( $args = array() ) {
		$defaults = array(
			'name'        => 'textarea',
			'value'       => null,
			'label'       => null,
			'desc'        => null,
			'class'       => 'large-text',
			'disabled'    => false
		);

		$args = wp_parse_args( $args, $defaults );

		$class = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', $args['class'] ) ) );
		$disabled = '';
		if( $args['disabled'] ) {
			$disabled = ' disabled="disabled"';
		}

		$output = '<span id="rpress-' . rpress_sanitize_key( $args['name'] ) . '-wrap">';

			if ( ! empty( $args['label'] ) ) {
				$output .= '<label class="rpress-label" for="' . rpress_sanitize_key( $args['name'] ) . '">' . esc_html( $args['label'] ) . '</label>';
			}

			$output .= '<textarea name="' . esc_attr( $args['name'] ) . '" id="' . rpress_sanitize_key( $args['name'] ) . '" class="' . $class . '"' . $disabled . '>' . esc_attr( $args['value'] ) . '</textarea>';

			if ( ! empty( $args['desc'] ) ) {
				$output .= '<span class="rpress-description">' . esc_html( $args['desc'] ) . '</span>';
			}

		$output .= '</span>';

		return $output;
	}

	/**
	 * Renders an ajax user search field
	 *
	 * @since  1.0.0
	 *
	 * @param array $args
	 * @return string text field with ajax search
	 */
	public function ajax_user_search( $args = array() ) {

		$defaults = array(
			'name'        => 'user_id',
			'value'       => null,
			'placeholder' => __( 'Enter username', 'restropress' ),
			'label'       => null,
			'desc'        => null,
			'class'       => '',
			'disabled'    => false,
			'autocomplete'=> 'off',
			'data'        => false
		);

		$args = wp_parse_args( $args, $defaults );

		$args['class'] = 'rpress-ajax-user-search ' . $args['class'];

		$output  = '<span class="rpress_user_search_wrap">';
			$output .= $this->text( $args );
			$output .= '<span class="rpress_user_search_results hidden"><a class="rpress-ajax-user-cancel" aria-label="' . __( 'Cancel', 'restropress' ) . '" href="#">x</a><span></span></span>';
		$output .= '</span>';

		return $output;
	}

}
