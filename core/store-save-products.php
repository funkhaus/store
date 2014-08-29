<?php

/*
 * Make all possible combinations of array values,
 * return false on failure
 */
 	function store_get_combinations( $arrays ){

	 	$count = 0;
	 	$output = false;
	 	foreach ( $arrays as $meta_key => $array ) {

		 	// Convert string to array and clean
		 	$array = explode(', ', $array);
		 	$array = array_filter($array);

		 	// If no values, skip
		 	if ( empty($array) ) continue;

		 	// If this is the first array with values...
		 	if ( $count === 0 ) {

			 	foreach ( $array as $value ) {

				 	// Set output array
				 	$output[$value] = array($meta_key => $value);

			 	}

			 	// Increment
			 	$count++;

			// If not the first array...
		 	} else {

			 	// Loop through each value
			 	$temp = array();
			 	foreach ( $array as $array_val ) {

				 	// Loop through each existing values in output
				 	foreach ( $output as $output_key => $output_val ) {

					 	// Add meta property to output
					 	$output_val[$meta_key] = $array_val;

					 	// Assign output value to new key
					 	$temp[$output_key . '-' . $array_val] = $output_val;

				 	}

			 	}

			 	// overwrite output and increment
			 	$output = $temp;
			 	$count++;

		 	}
	 	}
	 	return $output;

 	}

/*
 * Get options/variations meta keys
 */
	function store_sort_options($meta_array) {

	 	// Figure out the keys for the created options
	 	$meta_keys = array();
	 	foreach ( $meta_array as $post_key => $post_value ) {
		 	if ( strstr($post_key, '_store_meta') ) {
		 		// Make sure the value isn't an array (WordPress
		 		if( is_array($post_value) ) {
			 		$post_value = reset($post_value);
		 		}
		 		$meta_keys[ sanitize_key($post_key) ] = $post_value;
		 	}
	 	}		

		return $meta_keys;
	}

/*
 * Format an option key to be human readable
 */
 	function store_format_option_key($option_key) {
	 	return str_replace('_store_meta_', '', $option_key);
	}

/*
 * Make variation children on save of product
 */
 	function store_variation_children( $post_id ){

	 	// Get post that's being saved
	 	$post = get_post($post_id);

	 	// If post has a parent, abort
 		if ( $post->post_parent != 0 ) return;

 		// Get any option keys
 		$options = store_sort_options($_POST);

	 	// Give variantions to a function that returns an array containing all possible combinations 
	 	$combinations = store_get_combinations($options);

	 	// Get any existing children of this post
	 	$all_children = get_children('post_parent=' . $post_id );

	 	// Loop through children
	 	foreach ( $all_children as $child ) {

	 		// If child name is not in possible combinations, delete it
		 	if ( ! array_key_exists($child->post_name, $combinations) ) {
			 	wp_delete_post($child->ID, true);
		 	}

	 	}

		// Unhook this function to prevent inf. loop
		remove_action( 'save_post', 'store_variation_children' );

	 	// Set template for child posts
		$post_template = array(
			'post_content'   => $post->post_content,
			'post_status'    => 'publish',
			'post_type'      => 'product',
			'ping_status'    => 'closed',
			'post_parent'    => $post_id,
			'post_excerpt'   => $post->post_excerpt,
			'comment_status' => 'closed',
			'tax_input'      => array()
		);

		// Set args to query existing posts
		$existing;
	    $existing_args = array(
			'posts_per_page'	=> 1,
			'post_type'			=> 'product',
			'post_parent'		=> $post_id,
			'post_status'		=> 'any',
			'fields'			=> 'ids'
		);

		// If we have a list of combos, begin creating them
		if ( $combinations ) {

			// Loop through each combo
			foreach ( $combinations as $combo_slug => $combo_vals ) {

				// Unsety ID from query
				unset($post_template['ID']);

				// Set existing query to look for this combo, then run query
				$existing_args['name'] = $combo_slug;
				$existing = get_posts($existing_args);

				// If post already exists...
				if ( $existing ) {

					if ( get_post_meta( reset($existing), '_store_enable_variant' ) ) continue;

					// Set args to update rather than create
					$post_template['ID'] = reset($existing);

				}

				// Make title
				$title = '';
				foreach ( $combo_vals as $combo_val ) $title .= $combo_val . ' ';

				// Set name and title in args
			 	$post_template['post_name'] = $combo_slug;
			 	$post_template['post_title'] = $title;

			 	// Set ID var
			 	$ID = false;

			 	// Set Qty if applicable
			 	$qty = false;
			 	if ( $_POST['_store_qty'] ) {

				 	// qty = Parent Qty / variations
				 	$qty = floor( intval($_POST['_store_qty']) / count($combinations) );

			 	}

			 	// Create/Update post info, if successful...
			 	if ( $ID = wp_insert_post( $post_template ) ) {

				 	// Update store meta
				 	if ( $_POST['_store_price'] ) update_post_meta( $ID, '_store_price', $_POST['_store_price'] );
				 	if ( $qty ) update_post_meta( $ID, '_store_qty', $qty );

				 	foreach ( $combo_vals as $combo_key => $combo_val ) {
					 	update_post_meta( $ID, $combo_key, $combo_val );
				 	}

			 	}

			}

		}

		// Re-hook function
		add_action( 'save_post', 'store_variation_children' );

	}
	add_action('save_post', 'store_variation_children');


/*
 * Attempt to sync shipwire inventory on save
 */
	function store_shipwire_inv_save( $post_id ){

		// Update all inventory for variants of this product
		store_update_shipwire_inventory_single($post_id);

		// If shipping is not enabled, don't continue
		if ( ! store_is_shipping_enabled() ) return false;

		// If sku is set, cross-check with shipwire
		if ( isset($_POST['_store_sku']) ){

			if ( $qty = store_get_shipwire_qty($_POST['_store_sku']) ) {
				update_post_meta($post_id, '_store_qty', $qty);
				update_post_meta($post_id, '_store_shipwire_synced', true);
			} else {
				update_post_meta($post_id, '_store_shipwire_synced', false);
			}

		}

	}
	add_action('save_post', 'store_shipwire_inv_save');

?>