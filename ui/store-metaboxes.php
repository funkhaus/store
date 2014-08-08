<?php

/*
 * Add custom metabox to the new/edit page
 */
 	function store_add_variations(){
 		global $post;
	 	add_meta_box("store_price_meta", "Stock/Price", "store_price_meta", "products", "side", "default");
	 	if ( $post->post_parent != 0 ) {
		 	add_meta_box("store_enable_meta", "Enable Editing", "store_enable_meta", "products", "side", "high");
		} else {
		 	add_meta_box("store_options_meta", "Options/Variations", "store_options_meta", "products", "normal", "low");
		}
	}
 	add_action("add_meta_boxes", "store_add_variations");

    function store_options_meta(){
		global $post;

		wp_reset_query();
		// Query for children (variations)
	    $args = array(
			'posts_per_page'	=> -1,
			'orderby'			=> 'title',
			'post_type'			=> 'products',
			'post_parent'		=> $post->ID
		);
		$variations = get_posts($args);

		$meta = get_post_meta($post->ID);
		
		// Build empty options meta
		?>
			<div id="store-edit-options" class="<?php echo $variations ? 'hidden' : ''; ?>">

			<strong>Add New Option</strong>
		 	<?php for ( $i = 0; $i < 1; $i++ ) : ?>

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
 		if ( $options ) {
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

			</div>

 		<?php
	 	}

		// List out created options as a table
		if ( $variations ) : ?>

			<div id="store-variation-table-wrapper">
				<table class="wp-list-table widefat fixed pages">
					<thead>
						<tr>
							<th scope="col" id="title" class="manage-column column-title" style="">
								<span>Variation</span>
							</th>
							<th scope="col" class="manage-column">SKU</th>
							<th scope="col" class="manage-column">
								<span>Qty.</span>
							</th>
							<th scope="col" class="manage-column">
								<span>Price</span>
							</th>
						</tr>
					</thead>
					<tbody>
	
						<?php foreach ( $variations as $i => $post ) : setup_postdata($post); ?>
	
							<tr id="post-<?php $post->ID; ?>" class="post-<?php $post->ID; ?> type-products <?php echo $i % 2 == 0 ? 'alternate' : '';?>">
								<td class="post-title page-title column-title">
									<strong>
										<?php the_title(); ?>
									</strong>
								</td>
								<td>—</td>
								<td>—</td>
								<td>
									<?php echo $post->_store_price; ?>
									<?php edit_post_link( 'edit', '<span class="edit" style="float: right;">', '</span>' ); ?>
								</td>
							</tr>
	
						<?php endforeach; ?>
	
					</tbody>
				</table>
				<p style="text-align: right; margin-bottom: 0;">
					<a id="store-toggle-options" href="#">Change Options</a>
				</p>
			</div>

		<?php endif;

    }

    function store_price_meta(){
		global $post; ?>

			<div class="custom-meta">
				<p>
					<strong>Quantity</strong>
				</p>
				<label class="screen-reader-text" for="store-qty">Quantity</label>
				
				<?php // Get quantity function, and then save it ?>
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

	function store_enable_meta(){
		global $post; ?>

		<div class="custom-meta">
			<label class="screen-reader-text" for="store-qty">Enable Editing</label>
			<input id="store-qty" class="short" title="" name="_store_enable" type="checkbox" <?php checked($post->_store_enable); ?> value="1"> Override defaults
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
			
			// Update a parent (if it has one)
		}
		if( isset($_POST["_store_price"]) ) {
			update_post_meta($post->ID, "_store_price", $_POST["_store_price"]);
		}
		update_post_meta($post->ID, "_store_enable", $_POST["_store_enable"]);

	}
 	add_action('save_post', 'store_save_meta');


/*
 * Hide the children
 */
	function store_hide_children( $query ) {
	    if ( $query->is_admin && $query->query_vars['post_type'] == 'products' && ! $query->query_vars['post_parent'] ) {
	        $query->set( 'post_parent', 0 );
	    }
	}
	add_action( 'pre_get_posts', 'store_hide_children' );

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

		// Set args to query existing posts
		$existing;
	    $existing_args = array(
			'posts_per_page'	=> 1,
			'post_type'			=> 'products',
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

			 	// Create/Update post info, if successful...
			 	if ( $ID = wp_insert_post( $post_template ) ) {

				 	// Update store meta
				 	if ( $_POST['_store_price'] ) update_post_meta( $ID, '_store_price', $_POST['_store_price'] );
				 	if ( $_POST['_store_qty'] ) update_post_meta( $ID, '_store_qty', $_POST['_store_qty'] );

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

?>