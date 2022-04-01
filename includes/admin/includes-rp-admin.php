<?php
/**
 * RestroPress Admin
 *
 * @package  RestroPress/Admin
 * @version  3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

require_once dirname( __FILE__ ) . '/rp-meta-box-functions.php';

//Add fooditem metaboxes
require_once dirname( __FILE__ ) . '/fooditems/class-rp-fooditem-metaboxes.php';
require_once dirname( __FILE__ ) . '/class-rp-admin-assets.php';