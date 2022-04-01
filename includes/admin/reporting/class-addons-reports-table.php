<?php

/**
 * Earnings by Category Reports Table Class
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) )
    exit;

// Load WP_List_Table if not loaded
if ( !class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * RPRESS_Addons_Reports_Table Class
 *
 * Renders the Reports table
 *
 * @since 2.4
 */
class RPRESS_Addons_Reports_Table extends WP_List_Table {

    /**
     * Get things started
     *
     * @since 2.4
     * @see WP_List_Table::__construct()
     */
    public function __construct() {
        global $status, $page;

        // Set parent defaults
        parent::__construct( array(
            'singular'  => rpress_get_label_singular(),    // Singular name of the listed records
            'plural'    => rpress_get_label_plural(),       // Plural name of the listed records
            'ajax'      => false                        // Does this table support ajax?
        ) );
    }

    /**
     * Gets the name of the primary column.
     *
     * @since  1.0.0
     * @access protected
     *
     * @return string Name of the primary column.
     */
    protected function get_primary_column_name() {
        return 'label';
    }

    /**
     * This function renders most of the columns in the list table.
     *
     * @since 2.4
     *
     * @param array $item Contains all the data of the fooditems
     * @param string $column_name The name of the column
     *
     * @return string Column Name
     */
    public function column_default( $item, $column_name ) {
        return $item[ $column_name ];
    }

    /**
     * Retrieve the table columns
     *
     * @since 1.0
     * @return array $columns Array of all the list table columns
     */
    public function get_columns() {
        $columns = array(
            'label'          => __( 'Addon', 'restropress' ),
            'total_sales'    => __( 'Total Sales', 'restropress' ),
            'total_earnings' => __( 'Total Earnings', 'restropress' ),
            'avg_sales'      => __( 'Monthly Sales Avg', 'restropress' ),
            'avg_earnings'   => __( 'Monthly Earnings Avg', 'restropress' ),
        );

        return $columns;
    }

    /**
     * Outputs the reporting views
     *
     * @since 1.0
     * @return void
     */
    public function display_tablenav( $which = '' ) {
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
            <div class="alignleft actions bulkactions">
                <?php
                if ( 'top' === $which ) {
                    rpress_report_views();
                    rpress_reports_graph_controls();
                }
                ?>
            </div>
        </div>
    <?php
    }

    /**
     * Retrieve the current page number
     *
     * @since 1.0
     * @return int Current page number
     */
    public function get_paged() {
        return isset( $_GET[ 'paged' ] ) ? absint( $_GET[ 'paged' ] ) : 1;
    }

    /**
     * Build all the reports data
     *
     * @since 1.0
     * @return array $reports_data All the data for customer reports
     */
    public function reports_data() {

        /*
         * Date filtering
         */
        $dates = rpress_get_report_dates();

        $include_taxes = empty( $_GET['exclude_taxes'] ) ? true : false;

        if ( !empty( $dates[ 'year' ] ) ) {
            $date = new DateTime();
            $date->setDate( $dates[ 'year' ], $dates[ 'm_start' ], $dates[ 'day' ] );
            $start_date = $date->format( 'Y-m-d' );

            $date->setDate( $dates[ 'year_end' ], $dates[ 'm_end' ], $dates[ 'day_end' ] );
            $end_date          = $date->format( 'Y-m-d' );
            $cached_report_key = 'rpress_earnings_by_category_data' . $start_date . '_' . $end_date;
        } else {
            $start_date        = false;
            $end_date          = false;
            $cached_report_key = 'rpress_earnings_by_category_data';
        }

        $cached_reports = get_transient( $cached_report_key );

        if ( false !== $cached_reports ) {
            $reports_data = $cached_reports;
        } else {

            $reports_data = array();
            $term_args    = array(
                'parent'       => 0,
                'hierarchical' => 0,
                'hide_empty'   => false
            );

            $categories = get_terms( 'addon_category', $term_args );

            foreach ( $categories as $category_id => $category ) {

                $category_slugs = array( $category->slug );

                $child_args = array(
                    'parent'       => $category->term_id,
                    'hierarchical' => 0,
                );

                $child_terms = get_terms( 'addon_category', $child_args );
                if ( !empty( $child_terms ) ) {

                    foreach ( $child_terms as $child_term ) {
                        $category_slugs[] = $child_term->slug;
                    }
                }

                $fooditem_args = array(
                    'post_type'      => 'fooditem',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'addon_category',
                            'field'    => 'slug',
                            'terms'    => $category_slugs,
                        ),
                    ),
                );

                $fooditems = get_posts( $fooditem_args );

                $sales        = 0;
                $earnings     = 0.00;
                $avg_sales    = 0;
                $avg_earnings = 0.00;

                foreach ( $fooditems as $fooditem ) {
                    $current_sales    = RPRESS()->payment_stats->get_sales( $fooditem, $start_date, $end_date );
                    $current_earnings = RPRESS()->payment_stats->get_earnings( $fooditem, $start_date, $end_date, $include_taxes );

                    $current_average_sales = rpress_get_average_monthly_fooditem_sales( $fooditem );
                    $current_average_earnings = rpress_get_average_monthly_fooditem_earnings( $fooditem );

                    $sales        += $current_sales;
                    $earnings     += $current_earnings;
                    $avg_sales    += $current_average_sales;
                    $avg_earnings += $current_average_earnings;
                }

                $avg_earnings = round( $avg_earnings, rpress_currency_decimal_filter() );
                if ( ! empty( $avg_earnings ) && $avg_sales < 1 ) {
                    $avg_sales = __( 'Less than 1', 'restropress' );
                } else {
                    $avg_sales = round( rpress_format_amount( $avg_sales, false ) );
                }

                $reports_data[] = array(
                    'ID'                 => $category->term_id,
                    'label'              => $category->name,
                    'total_sales'        => rpress_format_amount( $sales, false ),
                    'total_sales_raw'    => $sales,
                    'total_earnings'     => rpress_currency_filter( rpress_format_amount( $earnings ) ),
                    'total_earnings_raw' => $earnings,
                    'avg_sales'          => $avg_sales,
                    'avg_earnings'       => rpress_currency_filter( rpress_format_amount( $avg_earnings ) ),
                    'is_child'           => false,
                );

                if ( !empty( $child_terms ) ) {

                    foreach ( $child_terms as $child_term ) {
                        $child_args = array(
                            'post_type'      => 'fooditem',
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                            'tax_query'      => array(
                                array(
                                    'taxonomy' => 'addon_category',
                                    'field'    => 'slug',
                                    'terms'    => $child_term->slug,
                                ),
                            ),
                        );

                        $child_fooditems = get_posts( $child_args );

                        $child_sales        = 0;
                        $child_earnings     = 0.00;
                        $child_avg_sales    = 0;
                        $child_avg_earnings = 0.00;

                        foreach ( $child_fooditems as $child_fooditem ) {
                            $current_average_sales    = $current_sales    = RPRESS()->payment_stats->get_sales( $child_fooditem, $start_date, $end_date );
                            $current_average_earnings = $current_earnings = RPRESS()->payment_stats->get_earnings( $child_fooditem, $start_date, $end_date );

                            $release_date = get_post_field( 'post_date', $child_fooditem );
                            $diff         = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );
                            $months       = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

                            if ( $months > 0 ) {
                                $current_average_sales    = ( $current_sales / $months );
                                $current_average_earnings = ( $current_earnings / $months );
                            }

                            $child_sales        += $current_sales;
                            $child_earnings     += $current_earnings;
                            $child_avg_sales    += $current_average_sales;
                            $child_avg_earnings += $current_average_earnings;
                        }

                        $child_avg_sales    = round( $child_avg_sales / count( $child_fooditems ) );
                        $child_avg_earnings = round( $child_avg_earnings / count( $child_fooditems ), rpress_currency_decimal_filter() );

                        $reports_data[] = array(
                            'ID'                 => $child_term->term_id,
                            'label'              => '&#8212; ' . $child_term->name,
                            'total_sales'        => rpress_format_amount( $child_sales, false ),
                            'total_sales_raw'    => $child_sales,
                            'total_earnings'     => rpress_currency_filter( rpress_format_amount( $child_earnings ) ),
                            'total_earnings_raw' => $child_earnings,
                            'avg_sales'          => rpress_format_amount( $child_avg_sales, false ),
                            'avg_earnings'       => rpress_currency_filter( rpress_format_amount( $child_avg_earnings ) ),
                            'is_child'           => true,
                        );
                    }
                }
            }
        }

        return $reports_data;
    }

    /**
     * Output the Category Sales Mix Pie Chart
     *
     * @since 1.0
     * @return string The HTML for the outputted graph
     */
    public function output_sales_graph() {
        if ( empty( $this->items ) ) {
            return;
        }

        $data        = array();
        $total_sales = 0;

        foreach ( $this->items as $item ) {
            $total_sales += $item['total_sales_raw'];

            if ( !empty( $item[ 'is_child' ] ) || empty( $item[ 'total_sales_raw' ] ) ) {
                continue;
            }

            $data[ $item[ 'label' ] ] = $item[ 'total_sales_raw' ];
        }


        if ( empty( $total_sales ) ) {
            echo '<p><em>' . __( 'No sales for dates provided.', 'restropress' ) . '</em></p>';
        }

        // Sort High to Low, prior to filter so people can reorder if they please
        arsort( $data );
        $data = apply_filters( 'rpress_category_sales_graph_data', $data );

        $options = apply_filters( 'rpress_category_sales_graph_options', array(
            'legend_formatter' => 'rpressLegendFormatterSales',
        ), $data );

        $pie_graph = new RPRESS_Pie_Graph( $data, $options );
        $pie_graph->display();
    }

    /**
     * Output the Category Earnings Mix Pie Chart
     *
     * @since 1.0
     * @return string The HTML for the outputted graph
     */
    public function output_earnings_graph() {
        if ( empty( $this->items ) ) {
            return;
        }

        $data           = array();
        $total_earnings = 0;

        foreach ( $this->items as $item ) {
            $total_earnings += $item['total_earnings_raw'];

            if ( ! empty( $item[ 'is_child' ] ) || empty( $item[ 'total_earnings_raw' ] ) ) {
                continue;
            }

            $data[ $item[ 'label' ] ] = $item[ 'total_earnings_raw' ];

        }

        if ( empty( $total_earnings ) ) {
            echo '<p><em>' . __( 'No earnings for dates provided.', 'restropress' ) . '</em></p>';
        }

        // Sort High to Low, prior to filter so people can reorder if they please
        arsort( $data );
        $data = apply_filters( 'rpress_category_earnings_graph_data', $data );

        $options = apply_filters( 'rpress_category_earnings_graph_options', array(
            'legend_formatter' => 'rpressLegendFormatterEarnings',
        ), $data );

        $pie_graph = new RPRESS_Pie_Graph( $data, $options );
        $pie_graph->display();
    }

    /**
     * Setup the final data for the table
     *
     * @since 2.4
     * @uses RPRESS_Categories_Reports_Table::get_columns()
     * @uses RPRESS_Categories_Reports_Table::get_sortable_columns()
     * @uses RPRESS_Categories_Reports_Table::reports_data()
     * @return void
     */
    public function prepare_items() {
        $columns               = $this->get_columns();
        $hidden                = array(); // No hidden columns
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        $this->items           = $this->reports_data();
    }
}
