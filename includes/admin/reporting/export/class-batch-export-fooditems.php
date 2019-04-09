<?php
/**
 * Batch RestroPress Export Class
 *
 * This class handles fooditem products export
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Batch_RestroPress_Export Class
 *
 * @since  1.0.0
 */
class RPRESS_Batch_RestroPress_Export extends RPRESS_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = 'fooditems';

	/**
	 * Set the CSV columns
	 *
	 * @since  1.0.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'ID'                       => __( 'ID',   'restropress' ),
			'post_name'                => __( 'Slug',   'restropress' ),
			'post_title'               => __( 'Name',   'restropress' ),
			'post_date'                => __( 'Date Created',   'restropress' ),
			'post_author'              => __( 'Author',   'restropress' ),
			'post_content'             => __( 'Description',   'restropress' ),
			'post_excerpt'             => __( 'Excerpt',   'restropress' ),
			'post_status'              => __( 'Status',   'restropress' ),
			'categories'               => __( 'Categories',   'restropress' ),
			'tags'                     => __( 'Tags',   'restropress' ),
			'rpress_price'                => __( 'Price',   'restropress' ),
			'_rpress_files'               => __( 'Files',   'restropress' ),
			'_rpress_fooditem_limit'      => __( 'File RestroPress Limit',   'restropress' ),
			'_thumbnail_id'            => __( 'Featured Image',   'restropress' ),
			'rpress_sku'                  => __( 'SKU',   'restropress' ),
			'rpress_product_notes'        => __( 'Notes',   'restropress' ),
			'_rpress_fooditem_sales'      => __( 'Sales',   'restropress' ),
			'_rpress_fooditem_earnings'   => __( 'Earnings',   'restropress' ),
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since  1.0.0
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		$meta = array(
			'rpress_price',
			'_rpress_files',
			'_rpress_fooditem_limit',
			'_thumbnail_id',
			'rpress_sku',
			'rpress_product_notes',
			'_rpress_fooditem_sales',
			'_rpress_fooditem_earnings'
		);

		$args = array(
			'post_type'      => 'fooditem',
			'posts_per_page' => 30,
			'paged'          => $this->step,
			'orderby'        => 'ID',
			'order'          => 'ASC'
		);

		$fooditems = new WP_Query( $args );

		if ( $fooditems->posts ) {
			foreach ( $fooditems->posts as $fooditem ) {

				$row = array();

				foreach( $this->csv_cols() as $key => $value ) {

					// Setup default value
					$row[ $key ] = '';

					if( in_array( $key, $meta ) ) {

						switch( $key ) {

							case '_thumbnail_id' :

								$image_id    = get_post_thumbnail_id( $fooditem->ID );
								$row[ $key ] = wp_get_attachment_url( $image_id );

								break;

							case 'rpress_price' :

								if( rpress_has_variable_prices( $fooditem->ID ) ) {

									$prices = array();
									foreach( rpress_get_variable_prices( $fooditem->ID ) as $price ) {
										$prices[] = $price['name'] . ': ' . $price['amount'];
									}

									$row[ $key ] = implode( ' | ', $prices );

								} else {

									$row[ $key ] = rpress_get_fooditem_price( $fooditem->ID );

								}

								break;

							case '_rpress_files' :


								$files = array();
								foreach( rpress_get_fooditem_files( $fooditem->ID ) as $file ) {
									$f = $file['file'];

									if ( rpress_has_variable_prices( $fooditem->ID ) ) {
										$condition = isset( $file['condition'] ) ? $file['condition'] : 'all';
										$f .= ';' . $condition;
									}

									$files[] = $f;

									unset( $file );
								}

								$row[ $key ] = implode( ' | ', $files );

								break;

							default :

								$row[ $key ] = get_post_meta( $fooditem->ID, $key, true );

								break;

						}

					} elseif( isset( $fooditem->$key ) ) {

						switch( $key ) {

							case 'post_author' :

								$row[ $key ] = get_the_author_meta( 'user_login', $fooditem->post_author );

								break;

							default :

								$row[ $key ] = $fooditem->$key;

								break;
						}

					} elseif( 'tags' == $key ) {

						$terms = get_the_terms( $fooditem->ID, 'fooditem_tag' );
						if( $terms ) {
							$terms = wp_list_pluck( $terms, 'name' );
							$row[ $key ] = implode( ' | ', $terms );
						}


					} elseif( 'categories' == $key ) {

						$terms = get_the_terms( $fooditem->ID, 'addon_category' );
						if( $terms ) {
							$terms = wp_list_pluck( $terms, 'name' );
							$row[ $key ] = implode( ' | ', $terms );
						}

					}

				}

				$data[] = $row;

			}

			$data = apply_filters( 'rpress_export_get_data', $data );
			$data = apply_filters( 'rpress_export_get_data_' . $this->export_type, $data );

			return $data;
		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_percentage_complete() {

		$args = array(
			'post_type'		   => 'fooditem',
			'posts_per_page'   => -1,
			'post_status'	   => 'any',
			'fields'           => 'ids',
		);

		$fooditems  = new WP_Query( $args );
		$total      = (int) $fooditems->post_count;
		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}
}
