<?php

/*
 * Add custom metabox to the new/edit page
 */
 	function store_add_variations(){
	 	add_meta_box("store_options_meta", "Options", "store_options_meta", "products", "normal", "low");
	 	add_meta_box("store_price_meta", "Stock/Price", "store_price_meta", "products", "side", "low");
	}
 	add_action("add_meta_boxes", "store_add_variations");

    function store_options_meta(){
		global $post;

		$meta = get_post_meta($post->ID);
		
		// Build empty options meta
		?>
			<strong>Add New Option</strong>
		 	<?php for ( $i = 0; $i < 2; $i++ ) : ?>
				
				<div class="store-options-meta store-create-option">
					<label for="option-<?php echo $i +1; ?>-key">Option <?php echo $i +1; ?>:</label>
					<input id="option-<?php echo $i +1; ?>-key" class="short store-option" title="" placeholder="size" type="text" value="">
					<input id="option-<?php echo $i +1; ?>-value" class="short store-option-variant" title="" name="" placeholder="small, medium, large" type="text" value="">
					<br/>
				</div>
				
			<?php endfor; ?>
		
		<?php
						
		// Build already saved options meta
 		$options = store_sort_options($meta);

 		?>
 			<p></p>
			<strong>Edit Existing Option</strong> 		
		 	<?php foreach($options as $key => $value) :  ?>
				<?php $i++; ?>
				
				<div class="store-options-meta">
					<label for="option-<?php echo $key; ?>">Option <?php echo $i +1; ?>:</label>
					<input id="option-<?php echo $i +1; ?>-key" class="short" title="" name="" type="text" disabled value="<?php echo store_format_option_key($key); ?>">
					<input id="option-<?php echo $i +1; ?>-value" class="short" title="" name="<?php echo $key; ?>" type="text" value="<?php echo $value ; ?>">
					<br/>
				</div>

			<?php endforeach; ?>

 		<?php

		// List out created options as a table 

    }

    function store_price_meta(){
		global $post; ?>

			<div class="custom-meta">
				<p>
					<strong>Quantity</strong>
				</p>
				<label class="screen-reader-text" for="store-qty">Quantity</label>
				<input <?php echo empty($post->_store_qty) ? '' : 'disabled'; ?> id="store-qty" class="short" title="" size="4" name="_store_qty" type="text" value="<?php echo $post->_store_qty; ?>">
				<br/>

			</div>
			<div class="custom-meta">
				<p>
					<strong>Price</strong>
				</p>
				<label class="screen-reader-text" for="store-price">Price</label>
				<input id="store-price" class="short" title="" size="4" name="_store_price" type="text" value="<?php echo $post->_store_price; ?>">
				<br/>

			</div>

		<?php
	}


/*
 * Save the metabox vaule
 */
 	function store_save_meta(){
	 	global $post;

	 	// check autosave
	 	if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
		 	return $post->ID;
		}

		// Meta for variation data
		$options = store_sort_options($_POST);
		foreach( $options as $key => $value ) {
			update_post_meta($post->ID, $key, $value);			
		}

		// Meta for stock/price
		if( isset($_POST["_store_qty"]) ) {
			update_post_meta($post->ID, "_store_qty", $_POST["_store_qty"]);
		}
		if( isset($_POST["_store_price"]) ) {
			update_post_meta($post->ID, "_store_price", $_POST["_store_price"]);
		}

	}
 	add_action('save_post', 'store_save_meta');


/*
 * Hide the children
 */
	function store_hide_children( $query ) {
	    if ( $query->is_admin && $query->query_vars['post_type'] == 'products' ) {
	        $query->set( 'post_parent', 0 );
	    }
	}
	//add_action( 'pre_get_posts', 'store_hide_children' );

/*
 * Make all possible combinations of array values,
 * return false on failure
 */
 	function store_get_combinations( $arrays ){

	 	$count = 0;
	 	$output = false;
	 	foreach ( $arrays as $array ) {

		 	// Convert string to array and clean
		 	$array = explode(', ', $array);
		 	$array = array_filter($array);

		 	// If no values, skip
		 	if ( empty($array) ) continue;

		 	// If this is the first array with values...
		 	if ( $count === 0 ) {

			 	// Save values to output and increment
			 	$output = $array;
			 	$count++;

			// If not the first array...
		 	} else {

			 	// Loop through each value
			 	$temp = array();
			 	foreach ( $array as $array_val ) {

				 	// Loop through each existing values in output
				 	foreach ( $output as $output_val ) {

					 	// Make combinations of values and output
					 	$temp[] = $output_val . '-' . $array_val;

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
 * Make variation children on save of product
 */
 	function store_variation_children( $post_id ){

	 	// Get post that's being saved
	 	$post = get_post($post_id);

 		// If post parent == 0, abort
 		if ( $post->post_parent !== 0 ) return;
 		
 		// Get any option keys
 		$options = store_sort_options($_POST);

	 	// Give variantions to a function that returns an array containing all combinations 
	 	$combinations = store_get_combinations($options);

	 	$all_children = get_children('post_parent=' . $post_id );
	 	foreach ( $all_children as $child ) {
		 	if ( ! in_array($child->post_name, $combinations) ) {
			 	wp_delete_post($child->ID, true);
		 	}
	 	}

		// Unhook this function to prevent inf. loop
		remove_action( 'save_post', 'store_variation_children' );

	 	// Set template for child posts
		$post_template = array(
			'post_content'   => $post->post_content,
			'post_status'    => 'publish',
			'post_type'      => 'products',
			'ping_status'    => 'closed',
			'post_parent'    => $post_id,
			'post_excerpt'   => $post->post_excerpt,
			'comment_status' => 'closed',
			'tax_input'      => array()
		);

		$existing;
	    $existing_args = array(
			'posts_per_page'	=> 1,
			'post_type'			=> 'products',
			'post_parent'		=> $post_id,
			'post_status'		=> 'any',
			'fields'			=> 'ids'
		);

	 	// Create posts for all combos
		if ( $combinations ) {

			foreach ( $combinations as $combination ) {

				unset($post_template['ID']);
				$existing_args['name'] = $combination;
				$existing = get_posts($existing_args);

				if ( $existing ) {
					$post_template['ID'] = reset($existing);
				}

			 	$post_template['post_name'] = $combination;
			 	$post_template['post_title'] = $combination;

			 	wp_insert_post( $post_template );

			}

		}

		// Re-hook function
		add_action( 'save_post', 'store_variation_children' );

	}
	add_action('save_post', 'store_variation_children');


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
		 		$meta_keys[$post_key] = $post_value;
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

?>