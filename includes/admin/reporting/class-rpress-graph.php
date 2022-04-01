<?php
/**
 * Graphs
 *
 * This class handles building pretty report graphs
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Graph Class
 *
 * @since  1.0.0
 */
class RPRESS_Graph {

	/*

	Simple example:

	data format for each point: array( location on x, location on y )

	$data = array(

		'Label' => array(
			array( 1, 5 ),
			array( 3, 8 ),
			array( 10, 2 )
		),

		'Second Label' => array(
			array( 1, 7 ),
			array( 4, 5 ),
			array( 12, 8 )
		)
	);

	$graph = new RPRESS_Graph( $data );
	$graph->display();

	*/

	/**
	 * Data to graph
	 *
	 * @var array
	 * @since  1.0.0
	 */
	private $data;

	/**
	 * Unique ID for the graph
	 *
	 * @var string
	 * @since  1.0.0
	 */
	private $id = '';

	/**
	 * Graph options
	 *
	 * @var array
	 * @since  1.0.0
	 */
	private $options = array();

	/**
	 * Get things started
	 *
	 * @since  1.0.0
	 */
	public function __construct( $_data ) {

		$this->data = $_data;

		// Generate unique ID
		$this->id   = 'a' . md5( rand() );

		// Setup default options;
		$this->options = array(
			'y_mode'             => null,
			'x_mode'             => null,
			'y_decimals'         => 0,
			'x_decimals'         => 0,
			'y_position'         => 'right',
			'time_format'        => '%d/%b',
			'ticksize_unit'      => 'day',
			'ticksize_num'       => 1,
			'multiple_y_axes'    => false,
			'bgcolor'            => '#f9f9f9',
			'bordercolor'        => '#ccc',
			'color'              => '#bbb',
			'borderwidth'        => 2,
			'bars'               => false,
			'lines'              => true,
			'points'             => true,
			'additional_options' => '',
		);

	}

	/**
	 * Set an option
	 *
	 * @param $key The option key to set
	 * @param $value The value to assign to the key
	 * @since  1.0.0
	 */
	public function set( $key, $value ) {
		$this->options[ $key ] = $value;
	}

	/**
	 * Get an option
	 *
	 * @param $key The option key to get
	 * @since  1.0.0
	 */
	public function get( $key ) {
		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : false;
	}

	/**
	 * Get graph data
	 *
	 * @since  1.0.0
	 */
	public function get_data() {
		return apply_filters( 'rpress_get_graph_data', $this->data, $this );
	}

	/**
	 * Load the graphing library script
	 *
	 * @since  1.0.0
	 */
	public function load_scripts() {
		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_script( 'jquery-flot', RP_PLUGIN_URL . 'assets/js/jquery.flot' . $suffix . '.js' );

		do_action( 'rpress_graph_load_scripts' );
	}

	/**
	 * Build the graph and return it as a string
	 *
	 * @var array
	 * @since  1.0.0
	 * @return string
	 */
	public function build_graph() {

		$yaxis_count = 1;

		$this->load_scripts();
		ob_start();
?>
		<script type="text/javascript">
			jQuery( document ).ready( function($) {
				$.plot(
					$("#rpress-graph-<?php echo esc_attr( $this->id ); ?>"),
					[
						<?php foreach( $this->get_data() as $label => $data ) : ?>
						{
							label: "<?php echo esc_attr( $label ); ?>",
							id: "<?php echo sanitize_text_field( $label ); ?>",
							// data format is: [ point on x, value on y ]
							data: [<?php foreach( $data as $point ) { echo '[' . implode( ',', $point ) . '],'; } ?>],
							points: {
								show: <?php echo $this->options['points'] ? 'true' : 'false'; ?>,
							},
							bars: {
								show: <?php echo $this->options['bars'] ? 'true' : 'false'; ?>,
								barWidth: 12,
								aling: 'center'
							},
							lines: {
								show: <?php echo $this->options['lines'] ? 'true' : 'false'; ?>
							},
							<?php if( $this->options['multiple_y_axes'] ) : ?>
							yaxis: <?php echo esc_html( $yaxis_count ); ?>
							<?php endif; ?>
						},
						<?php $yaxis_count++; endforeach; ?>
					],
					{
						// Options
						grid: {
							show: true,
							aboveData: false,
							color: "<?php echo wp_kses_post( ( $this->options['color'] ) ) ; ?>",
							backgroundColor: "<?php echo wp_kses_post( $this->options['bgcolor'] ); ?>",
							borderColor: "<?php echo wp_kses_post( $this->options['bordercolor'] ); ?>",
							borderWidth: <?php echo absint( $this->options['borderwidth'] ); ?>,
							clickable: false,
							hoverable: true
						},
						xaxis: {
							mode: "<?php echo wp_kses_post( $this->options['x_mode'] ); ?>",
							timeFormat: "<?php echo $this->options['x_mode'] == 'time' ? wp_kses_post( $this->options['time_format'] ) : ''; ?>",
							tickSize: "<?php echo $this->options['x_mode'] == 'time' ? '' : wp_kses_post( $this->options['ticksize_num'] ); ?>",
							<?php if( $this->options['x_mode'] != 'time' ) : ?>
							tickDecimals: <?php echo wp_kses_post( $this->options['x_decimals'] ) ; ?>
							<?php endif; ?>
						},
						yaxis: {
							position: 'right',
							min: 0,
							mode: "<?php echo wp_kses_post( $this->options['y_mode'] ); ?>",
							timeFormat: "<?php echo $this->options['y_mode'] == 'time' ? wp_kses_post( $this->options['time_format'] ) : ''; ?>",
							<?php if( $this->options['y_mode'] != 'time' ) : ?>
							tickDecimals: <?php echo wp_kses_post( $this->options['y_decimals'] ); ?>
							<?php endif; ?>
						},
						<?php echo wp_kses_post( $this->options['additional_options'] ); ?>
					}

				);

				function rpress_flot_tooltip(x, y, contents) {
					$('<div id="rpress-flot-tooltip">' + contents + '</div>').css( {
						position: 'absolute',
						display: 'none',
						top: y + 5,
						left: x + 5,
						border: '1px solid #fdd',
						padding: '2px',
						'background-color': '#fee',
						opacity: 0.80
					}).appendTo("body").fadeIn(200);
				}

				var previousPoint = null;
				$("#rpress-graph-<?php echo esc_html( $this->id ); ?>").bind("plothover", function (event, pos, item) {
					$("#x").text(pos.x.toFixed(2));
					$("#y").text(pos.y.toFixed(2));
					if (item) {
						if (previousPoint != item.dataIndex) {
							previousPoint = item.dataIndex;
							$("#rpress-flot-tooltip").remove();
							var x = item.datapoint[0].toFixed(2),
							y = item.datapoint[1].toFixed(2);
							if( item.series.id.includes('earnings') ) {
								if( rpress_vars.currency_pos == 'before' ) {
									rpress_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + rpress_vars.currency_sign + y );
								} else {
									rpress_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y + rpress_vars.currency_sign );
								}
							} else {
								rpress_flot_tooltip( item.pageX, item.pageY, item.series.label + ' ' + y.replace( '.00', '' ) );
							}
						}
					} else {
						$("#rpress-flot-tooltip").remove();
						previousPoint = null;
					}
				});

			});

		</script>
		<div id="rpress-graph-<?php echo esc_html( $this->id ); ?>" class="rpress-graph" style="height: 300px;"></div>
<?php
		return ob_get_clean();
	}

	/**
	 * Output the final graph
	 *
	 * @since  1.0.0
	 */
	public function display() {
		do_action( 'rpress_before_graph', $this );
		echo $this->build_graph();
		do_action( 'rpress_after_graph', $this );
	}

}
