<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// filter main archive
	function store_filter_main_archive($query) {
		if ( $query->is_main_query() && $query->is_post_type_archive('store') ) {
			$query->set('post_type', 'product');
			$query->set('post_parent', 0);
		}
	}
	add_action('pre_get_posts', 'store_filter_main_archive');


	// Set redirects for files within store directory
	function store_set_template_redirects( $template ) {
		global $post;

		if ( is_post_type_archive( 'product' )  ) {
			$new_template = locate_template( array( 'store/store-front-page.php' ) );
			if ( '' != $new_template ) {
				return $new_template ;
			}

		} elseif ( is_single() && get_post_type() === 'store' ) {
			$new_template = locate_template( array( 'store/store-page-' . $post->post_name . '.php' ) );
			if ( '' != $new_template ) {
				return $new_template ;
			}

		} elseif ( is_single() && get_post_type() === 'product' ) {
			$new_template = locate_template( array( 'store/store-product-' . $post->post_name . '.php' ) );
			if ( ! $new_template ) $new_template = locate_template( array( 'store/store-product.php' ) );
			if ( '' != $new_template ) {
				return $new_template ;
			}

		}

		return $template;
	}
	add_filter( 'template_include', 'store_set_template_redirects', 99 );


	// Set store-specific body classes
	function store_add_bodyclasses($classes) {

		// if you are within the store hierarchy, add class to body
		if ( store_is_within_store() ) $classes[] = 'store';

		// if this is the product archive, add class to product
		if ( is_post_type_archive( 'product' ) ) $classes[] = 'store-front-page';

		// if this page is in the 'store' category, give it the store-page class
		if ( get_post_type() === 'store' ) $classes[] = 'store-page';

		// if this is a single product
		if ( get_post_type() === 'product' && is_single() ) $classes[] = 'store-product';

		return $classes;
	}
	add_filter('body_class', 'store_add_bodyclasses');