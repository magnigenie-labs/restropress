<?php

class RP_REST_Food_Addons_V1_Controller extends RP_REST_Terms_Controller {

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
    protected $rest_base = 'fooditem/addons';

    /**
     * Taxonomy.
     *
     * @var string
     */
    protected $taxonomy = 'addon_category';

    public function __construct() {
        $category = get_taxonomy( $this->taxonomy );
        $category->show_in_rest = true;
        $category->rest_base = $this->rest_base;
        $category->rest_namespace = $this->namespace;
        parent::__construct( $this->taxonomy );
    }

}