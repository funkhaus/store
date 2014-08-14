<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Get store conditionals
	include_once( trailingslashit( pp() ) . 'core/functions/store-conditionals.php' );

	// Get cart functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-cart-functions.php' );

	// Get product functions
	include_once( trailingslashit( pp() ) . 'core/functions/store-product-functions.php' );