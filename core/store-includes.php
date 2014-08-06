<?php 

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Get setup
	include_once( trailingslashit( pp() ) . 'core/store-setup.php' );

	// Get functions
	include_once( trailingslashit( pp() ) . 'core/store-functions.php' );

	// Setup product post types
	include_once( trailingslashit( pp() ) . 'core/store-post-types.php' );

	// Setup taxonomies
	include_once( trailingslashit( pp() ) . 'core/store-taxonomy.php' );

?>