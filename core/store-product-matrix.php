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

		$output['id'] = $post->ID;

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

			// init sizes
			$sizes = false;

			// if arg is a string, treat it as a size and get the featured image in that size
			if ( is_string($args['images']) ) $sizes = array($args['images']);

			// if arg is set to all or true, do all sizes
			if ( $args['images'] === true || $args['images'] === 'all' ) $sizes = get_intermediate_image_sizes();

			// if attachemnts were requested, query them BEFORE we loop through sizes
			if ( $args['attached'] ) {

				// query all attachments
				$attached_args = array(
					'posts_per_page'	=> -1,
					'orderby'			=> 'menu_order',
					'exclude'			=> $featured_id,
					'order'				=> 'ASC',
					'post_type'			=> 'attachment',
					'post_mime_type'	=> 'image',
					'post_parent'		=> $post->ID,
					'fields'			=> 'ids'
				);
				$attachments = get_posts($attached_args);
			}

			// if sizes are set...
			if ( $sizes ) {
				foreach ( $sizes as $size ) {

					// if featured image requested
					if ( $args['featured'] ) {

						// Get image src atts
						$featured_url = wp_get_attachment_image_src( $featured_id, $size );
	
						// if src atts...
						if ( $featured_url ) {

							// Set url into array
							$output['images']['featured'][$size] = $featured_url[0];

						}
					}

					// if attached images requested
					if ( $args['attached'] ) {

						// Loop through attachments
						if ( $attachments ) {
							foreach ( $attachments as $i => $attached ) {

								// Get src URL
								$image_url = wp_get_attachment_image_src( $attached, $size );

								// add url to output
								$output['images']['attached'][$i][$size] = $image_url[0];

							}
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


	function store_product_matrix( $args = null ){

		// Get matrix as array, forward args
		$matrix = store_get_product_matrix($args);

		// Set output to be full script tag
		$output = '<script type="text/javascript">
		/* <![CDATA[ */
		storeAPI.matrix.data[' . $matrix['id'] . '] = ' . json_encode( $matrix ) . ';
		/* ]]> */
		</script>';

		echo $output;
	}

/*
 * @Description:
 *
 * @Param: MIXED,
 * @Returns: MIXED, 
 */
	function store_get_product_matrix( $args = null ){

		// Set args defaults
		$args_master = array(
			'product'	=> false,
			'return'	=> 'array',
			'options'	=> true,
			'title'		=> false,
			'price'		=> false,
			'content'	=> false,
			'excerpt'	=> false,
			'images'	=> 'full',
			'featured'	=> true,
			'attached'	=> true,
			'sku'		=> false,
			'slug'		=> false
		);

		// Loop through master args
		foreach ( $args_master as $param => $val ) {

			// If arg is set, use it to override default
			if ( isset($args[$param]) ) $args_master[$param] = $args[$param];

		}

		// get product object
		$product = store_get_product($args_master['product']);

		// no product? return empty string
		if ( ! $product ) return '';

		// keep this format just in case (formatted for multiple products)
		$products = array($product->ID => $product);

		// loop through target products
		foreach ( $products as $parent_id => $parent_product ) {

			// Add properties to this ID
			$output = store_set_product_matrix_properties($args_master, $parent_product);

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
				$output['variants'][$variant->ID] = store_set_product_matrix_properties($args_master, $variant);

				// if options arg is set...
				if ( $args_master['options'] ) {

					// get options for this variant
					$options = store_get_options($variant);

					// variant has options...
					if ( $options ) {

						// loop through options
						foreach ( $options as $option => $val ) {

							// add options to this variant
							$output['variants'][$variant->ID]['options'][$option] = $val;

						}
					}

				}

			}

		}

		// Set output accordingly
		if ( $args_master['return'] === 'array' ) {

			$output = (array) $output;

		} elseif ( $args_master['return'] === 'json' ) {

			$output = json_encode($output);

		}

		return $output;
	};
