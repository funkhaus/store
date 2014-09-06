<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description:
 *
 * @Param: MIXED,
 * @Returns: BOOL,
 */
	function store_set_product_matrix_properties($args, $post){

		// init output
		$output;

		// if title arg is set...
		if ( $args['title'] ) {
	
			// add title to variant
			$output['title'] = get_the_title($post);
	
		}
	
		// if price arg is set...
		if ( $args['price'] ) {
	
			// add price to variant
			$output['price'] = store_get_product_price($post);
	
		}
	
		// if content arg is set...
		if ( $args['content'] ) {
	
			// clean content
			$content = $post->post_content;
			$content = apply_filters( 'the_content', $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
	
			// add content to variant
			$output['content'] = $content;
	
		}
	
		// if excerpt arg is set...
		if ( $args['excerpt'] ) {
	
			// clean the excerpt
			$excerpt = $post->post_excerpt;
			$excerpt = apply_filters( 'get_the_excerpt', $excerpt );
			$excerpt = apply_filters( 'the_excerpt', $excerpt );
	
			// add excerpt to variant
			$output['excerpt'] = $excerpt;
	
		}
	
		// if images arg is set...
		if ( $args['images'] ) {

			// Identify this variant's featured image
			$featured_id = get_post_thumbnail_id( $post->ID );

			// set default for images prop
			$output['images'] = false;

			// if there is a featured image...
			if ( $featured_id ) {

				// init sizes
				$sizes = false;

				// if arg is a string, treat it as a size and get the featured image in that size
				if ( is_string($args['images']) ) $sizes = array($args['images']);

				// if arg is set to all or true, do all sizes
				if ( $args['images'] === true || $args['images'] === 'all' ) $sizes = get_intermediate_image_sizes();

				// if sizes are set...
				if ( $sizes ) {
					foreach ( $sizes as $size ) {
	
						// Get image src atts
						$featured_url = wp_get_attachment_image_src( $featured_id, $size );
	
						// if src atts...
						if ( $featured_url ) {
	
							// Set url into array
							$output['images'][$size] = $featured_url[0];
	
						}
					}
				}
			}
		}

		// if sku arg is set...
		if ( $args['sku'] ) {
	
			// set sku for this variant
			$output['sku'] = store_get_sku($post);
		}

		// if slug args is set...
		if ( $args['slug'] ) {
	
			// set slug for this variant
			$output['slug'] = $post->post_name;
		}

		return $output;
	}

/*
 * @Description:
 *
 * @Param: MIXED,
 * @Returns: BOOL,
 */
	function store_get_product_matrix( $args = null, $product = null, $return = 'script' ){

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

			// Add properties to this ID
			$output[$parent_id] = store_set_product_matrix_properties($args_master, $parent_product);

			// Get variants of this product
			$variants = store_get_product_variants($parent_product);

			// no variants? move on
			if ( ! $variants ) {
				continue;
			}

			// Loop through variants
			// @TODO: add support for individual options
			foreach ( $variants as $variant ) {

				// 
				$output[$parent_id]['variants'][$variant->ID] = store_set_product_matrix_properties($args_master, $variant);

				// if options arg is set...
				if ( $args_master['options'] ) {

					// get options for this variant
					$options = store_get_options($variant);

					// variant has options...
					if ( $options ) {

						// loop through options
						foreach ( $options as $option => $val ) {

							// add options to this variant
							$output[$parent_id]['variants'][$variant->ID]['options'][$option] = $val;

						}
					}

				}

			}

		}

		// Set output accordingly
		if ( $return === 'array' ) {
			
			$output = (array) $output;

		} elseif ( $return === 'json' ) {

			$output = json_encode($output);

		} else {
			$output = '<script type="text/javascript">
			/* <![CDATA[ */
			var store_product_matrix = ' . json_encode( $output ) . ';
			/* ]]> */
			</script>';
		}

		return $output;
	};
