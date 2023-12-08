<?php

/**
 * Deprecated notice: This class is deprecated as of version 4.5.0. WooCommerce API is now part of core and not packaged separately.
 *
 * Returns information about the package and handles init.
 *
 * @package WooCommerce\RestApi
 */

namespace Restropress\RestApi;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 *
 * @deprecated Use \Restropress\RestApi\Server directly.
 */
class Package {

    /**
     * Version.
     *
     * @deprecated since 4.5.0. This tracks WooCommerce version now.
     * @var string
     */
    const VERSION = '3.0.0';

    // TODO:: will manage

    /**
     * Init the package - load the REST API Server class.
     *
     * @deprecated since 4.5.0. Directly call Restropress\RestApi\Server::instance()->init()
     */
    public static function init() {
        \Restropress\RestApi\Server::instance()->init();
    }

    /**
     * Return the version of the package.
     *
     * @deprecated since 4.5.0. This tracks WooCommerce version now.
     * @return string
     */
    public static function get_version() {

        return VERSION;
    }

    /**
     * Return the path to the package.
     *
     * @deprecated since 4.5.0. Directly call Restropress\RestApi\Server::get_path()
     * @return string
     */
    public static function get_path() {
        return \Restropress\RestApi\Server::get_path();
    }

}
