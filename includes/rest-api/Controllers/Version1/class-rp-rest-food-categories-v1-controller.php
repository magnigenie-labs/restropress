<?php

class RP_REST_Food_Categories_V1_Controller extends RP_REST_Terms_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'rp/v1';

    /**
     * Route base.
     *
     * @var string
     */
    protected $rest_base = 'fooditem/categories';

    /**
     * Taxonomy.
     *
     * @var string
     */
    protected $taxonomy = 'food-category';

    public function __construct() {
        $category = get_taxonomy( $this->taxonomy );
        $category->show_in_rest = true;
        $category->rest_base = $this->rest_base;
        $category->rest_namespace = $this->namespace;
        parent::__construct( $this->taxonomy );
    }

}
