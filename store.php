<?php
/*
 *
 *	Plugin Name: Store
 *	Plugin URI: http://funkhaus.us
 *	Description: An e-commerce toolkit for Wordpress
 *	Author: Funkhaus
 *	Version: 1.0
 *	Author URI: http://funkhaus.us
 *	Requires at least: 3.8
 * 
 */

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Helper function to always reference this directory
	if ( ! function_exists( 'pp' ) ) {
	    function pp() {
	        return dirname( __FILE__ );
	    }
	}

	// Get core
	include_once( trailingslashit( pp() ) . 'core/store-includes.php');

	// Get UI
	include_once( trailingslashit( pp() ) . 'ui/store-ui.php');

?>