<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Add custom metabox to the new/edit page
 */
 	function store_add_variations(){
 		global $post;
	 	add_meta_box("store_price_meta", "Stock/Price", "store_price_meta", "product", "side", "default");
	 	if ( $post->post_parent != 0 ) {
		 	add_meta_box("store_enable_meta", "Enable Editing", "store_enable_meta", "product", "side", "high");
		} else {
		 	add_meta_box("store_options_meta", "Options/Variations", "store_options_meta", "product", "normal", "low");
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
			'post_type'			=> 'product',
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
							<th scope="col" id="cb" class="manage-column column-cb check-column">
							</th>
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

							<tr id="post-<?php $post->ID; ?>" class="post-<?php $post->ID; ?> <?php echo $i % 2 == 0 ? 'alternate' : '';?>">
								<th scope="row" class="check-column">
									<?php if ( $post->_store_enable_variant ) : ?>
										<div class="dashicons dashicons-yes store-variant-enabled" style="margin-left: 5px;"></div>
									<?php endif; ?>
								</th>
								<td class="post-title page-title column-title">
									<strong>
										<?php the_title(); ?>
									</strong>
								</td>
								<td>-</td>
								<td><?php echo $post->_store_qty ? $post->_store_qty : '-'; ?></td>
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

				<?php $quantity = store_get_quantity($post->ID); ?>
				<input <?php echo ( $quantity && ! $post->post_parent ) ? 'disabled' : ''; ?> id="store-qty" class="short" title="" size="3" name="_store_qty" type="text" value="<?php echo $quantity; ?>">
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
			<label class="screen-reader-text" for="store-enable-variant">Enable Editing</label>
			<input id="store-enable-variant" class="short" title="" name="_store_enable_variant" type="checkbox" <?php checked($post->_store_enable_variant); ?> value="1"> Override defaults
			<br/>

		</div>
		<div class="custom-meta">
			<p>
				<strong>SKU</strong>
			</p>
			<label class="screen-reader-text" for="store-sku">SKU</label>
			<input id="store-sku" class="short" title="" size="8" name="_store_sku" type="text" value="<?php echo $post->_store_sku; ?>">
			<br/>

		</div>
		<p style="text-align: right;">
			<span class="back">
				<a href="<?php echo get_edit_post_link( $post->post_parent ); ?>">Back to Parent</a>
			</span>
		</p>

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
		if( isset($_POST["_store_sku"]) ) {
			update_post_meta($post->ID, "_store_sku", $_POST["_store_sku"]);
		}
		update_post_meta($post->ID, "_store_enable_variant", $_POST["_store_enable_variant"]);

	}
 	add_action('save_post', 'store_save_meta');


/*
 * Hide children of products in admin
 */
	function store_hide_children( $query ) {
	    if ( $query->is_admin && $query->query_vars['post_type'] == 'product' && ! $query->query_vars['post_parent'] ) {
	        $query->set( 'post_parent', 0 );
	    }
	}
	add_action( 'pre_get_posts', 'store_hide_children' );




/*
 * Cart Meta Boxes
 */
 	function store_add_cart_meta(){
	 	add_meta_box("store_cart_list_products", "Attached Products", "store_cart_list_products", "orders", "normal", "low");
	}
 	add_action("add_meta_boxes", "store_add_cart_meta");

    // List products in a cart
    function store_cart_list_products() {
        global $post;

		$products = get_post_meta($post->ID, '_store_cart_products', true);

		// List out created options as a table
		if ( $products ) : ?>

			<div id="store-attached-table-wrapper">
				<table class="wp-list-table widefat fixed pages">
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column">
							</th>
							<th scope="col" id="title" class="manage-column column-title" style="">
								<span>Product</span>
							</th>
							<th scope="col" class="manage-column">Options</th>
							<th scope="col" class="manage-column">
								<span>Qty.</span>
							</th>
							<th scope="col" class="manage-column">
								<span>Price</span>
							</th>
						</tr>
					</thead>
					<tbody>

						<?php $count = 0;
							foreach ( $products as $prod_id => $prod_qty ) : 
							$post = get_post($prod_id); setup_postdata($post); $count++; ?>

							<tr id="post-<?php $post->ID; ?>" class="post-<?php $post->ID; ?> <?php echo $count % 2 == 0 ? 'alternate' : '';?>">
								<th scope="row" class="check-column">
									<?php if ( true ) : ?>
										<div class="dashicons dashicons-yes store-variant-enabled" style="margin-left: 5px;"></div>
									<?php endif; ?>
								</th>
								<td class="post-title page-title column-title">
									<strong>
										<?php echo get_the_title($post->post_parent); ?>
									</strong>
								</td>
								<td><?php the_title(); ?></td>
								<td><?php echo $prod_qty; ?></td>
								<td><?php echo $post->_store_price; ?></td>
							</tr>

						<?php endforeach; ?>

					</tbody>
				</table>
			</div>

		<?php endif;

		//print_r($products);

    }

?>