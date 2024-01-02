<?php

class RP_REST_Food_Categories_V1_Controller extends RP_REST_Terms_Controller {


	/**
	 * Taxonomy.
	 *
	 * @var string
	 */
	protected $taxonomy = 'food-category';

	public function __construct() {
		$category               = get_taxonomy( $this->taxonomy );
		$category->show_in_rest = true;
		parent::__construct( $this->taxonomy );
		$this->namespace = 'rp/v1';
		$this->rest_base = 'fooditem/categories';
	}

}
