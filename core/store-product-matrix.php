<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description:
 *
 * @Param: MIXED,
 * @Returns: BOOL,
 */
	function store_get_product_matrix( $args = null, $product = null, $return = null ){

		// init array
		$products = array();

		// if several products provided in array, load them all
		if ( is_array($product) ) {

			// Loop through provided products
			foreach ( $product as $target_product ) {

				// get full object for product
				$target_product = store_get_product($target_product);

				// if product is not top level, get parent
				if ( $target_product->post_parent !== 0 ) $target_product = store_get_product($target_product->post_parent);
				if ( !isset($products[$target_product->ID]) )
					$products[$target_product->ID] = $target_product;

			}

		// not an array provided?
		} else {

			// get full product object
			$target_product = store_get_product($product);

			// if product is not top level, switch to parent
			if ( $target_product->post_parent !== 0 ) $target_product = store_get_product($target_product->post_parent);

			// set to array with single element, defaults to $post
			$products = array( $target_product->ID => $target_product );

		}

		// Set args defaults
		$args_master = array(
			'options'	=> true,
			'title'		=> false,
			'price'		=> false,
			'content'	=> false,
			'excerpt'	=> false,
			'images'	=> 'full',
			'sku'		=> false,
			'slug'		=> false
		);

		// Loop through master args
		foreach ( $args_master as $param => $val ) {

			// If arg is set, use it to override default
			if ( isset($args[$param]) ) $args_master[$param] = $args[$param];

		}

		// init output
		$output = array();

		// loop through target products
		foreach ( $products as $parent_id => $parent_product ) {

			// Get variants of this product
			$variants = store_get_product_variants($parent_product);

			// no variants? move on
			if ( ! $variants ) {
				$output[$parent_id] = false;
				continue;
			}

			// Loop through variants
			// @TODO: add support for individual options
			foreach ( $variants as $variant ) {

				// if options arg is set...
				if ( $args_master['options'] ) {

					// get options for this variant
					$options = store_get_options($variant);

					// variant has options...
					if ( $options ) {

						// loop through options
						foreach ( $options as $option => $val ) {

							// add options to this variant
							$output[$parent_id][$variant->ID]['options'][$option] = $val;

						}
					}

				}

				// if title arg is set...
				if ( $args_master['title'] ) {

					// add title to variant
					$output[$parent_id][$variant->ID]['title'] = get_the_title($variant);

				}

				// if price arg is set...
				if ( $args_master['price'] ) {

					// add price to variant
					$output[$parent_id][$variant->ID]['price'] = store_get_product_price($variant);

				}

				// if content arg is set...
				if ( $args_master['content'] ) {

					// clean content
					$content = $variant->post_content;
					$content = apply_filters( 'the_content', $content );
					$content = str_replace( ']]>', ']]&gt;', $content );

					// add content to variant
					$output[$parent_id][$variant->ID]['content'] = $content;

				}

				// if excerpt arg is set...
				if ( $args_master['excerpt'] ) {

					// clean the excerpt
					$excerpt = $variant->post_excerpt;
					$excerpt = apply_filters( 'get_the_excerpt', $excerpt );
					$excerpt = apply_filters( 'the_excerpt', $excerpt );

					// add excerpt to variant
					$output[$parent_id][$variant->ID]['excerpt'] = $excerpt;

				}

				// if images arg is set...
				if ( $args_master['images'] ) {

					// Identify this variant's featured image
					$featured_id = get_post_thumbnail_id( $variant->ID );

					// set default for images prop
					$output[$parent_id][$variant->ID]['images'] = false;

					// if there is a featured image...
					if ( $featured_id ) {

						// init sizes
						$sizes = false;

						// if arg is a string, treat it as a size and get the featured image in that size
						if ( is_string($args_master['images']) ) $sizes = array($args_master['images']);

						// if arg is set to all or true, do all sizes
						if ( $args_master['images'] === true || $args_master['images'] === 'all' ) $sizes = get_intermediate_image_sizes();

						// if sizes are set...
						if ( $sizes ) {
							foreach ( $sizes as $size ) {

								// Get image src atts
								$featured_url = wp_get_attachment_image_src( $featured_id, $size );
	
								// if src atts...
								if ( $featured_url ) {
	
									// Set url into array
									$output[$parent_id][$variant->ID]['images'][$size] = $featured_url[0];
	
								}

							}
						}

					}

				}

				// if sku arg is set...
				if ( $args_master['sku'] ) {

					// set sku for this variant
					$output[$parent_id][$variant->ID]['sku'] = store_get_sku($variant);

				}

				// if slug args is set...
				if ( $args_master['slug'] ) {

					// set slug for this variant
					$output[$parent_id][$variant->ID]['slug'] = $variant->post_name;

				}

			}

		}

		return $output;

	};
