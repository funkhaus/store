<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * Add custom metabox to the new/edit page
 */
 	function store_add_variations(){
 		global $post;
 		$variants = store_get_product_variants();
 		add_meta_box("store_price_meta", "Stock/Price", "store_price_meta", "product", "side", "default");
 		if ( $post->post_parent != 0 ) {
 			add_meta_box("store_enable_meta", "Enable Editing", "store_enable_meta", "product", "side", "high");
		} else {
			if ( empty($post->_store_sku) ) {
				add_meta_box("store_options_meta", "Options/Variations", "store_options_meta", "product", "normal", "low");
			}
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
					<label for="option-<?php echo $i +1; ?>-key">Option <?php echo $i + 1; ?>:</label>
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
										<?php $color = $post->_store_shipwire_synced ? 'green' : ''; ?>
										<div class="dashicons dashicons-yes store-variant-enabled" style="margin-left: 5px; color: <?php echo $color; ?>;"></div>
									<?php endif; ?>
								</th>
								<td class="post-title page-title column-title">
									<strong>
										<?php the_title(); ?>
									</strong>
								</td>
								<td><?php echo store_get_sku() ? store_get_sku() : '-'; ?></td>
								<td><?php echo $post->_store_qty ? $post->_store_qty : '-'; ?></td>
								<td>
									<?php echo number_format(($post->_store_price / 100), 2, '.', ''); ?>
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
		global $post;
		$has_variants = store_has_variants(); ?>

			<?php if ( ! $has_variants ) : ?>
				<div class="custom-meta">
					<p>
						<strong>Quantity</strong>
					</p>
					<label class="screen-reader-text" for="store-qty">Quantity</label>
	
					<?php $quantity = store_get_quantity($post); ?>
					<input disabled id="store-qty" class="short" title="" size="3" name="_store_qty" type="text" value="<?php echo $quantity; ?>">
					<br/>
	
				</div>
			<?php endif; ?>
			<div class="custom-meta">
				<p>
					<strong>Price</strong>
				</p>
				<label class="screen-reader-text" for="store-price">Price</label>
				<input id="store-price" class="short" title="" size="4" name="_store_price" type="text" value="<?php echo number_format( ($post->_store_price / 100), 2, '.', '') ; ?>">
				<br/>

			</div>
			<?php if ( ! $has_variants ) : ?>
				<div class="custom-meta">
					<p>
						<strong>SKU</strong>
					</p>
					<label class="screen-reader-text" for="store-sku">SKU</label>
					<input id="store-sku" class="short" title="" size="10" name="_store_sku" type="text" value="<?php echo store_get_sku(); ?>">
					<br/>
	
				</div>
			<?php endif; ?>

		<?php
	}

	function store_enable_meta(){
		global $post; ?>

		<div class="custom-meta">
			<label class="screen-reader-text" for="store-enable-variant">Enable Editing</label>
			<input id="store-enable-variant" class="short" title="" name="_store_enable_variant" type="checkbox" <?php checked($post->_store_enable_variant); ?> value="1"> Override defaults
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
 		global $post;

	 	add_meta_box("store_cart_list_products", "Attached Products", "store_cart_list_products", "orders", "normal", "low");
	 	add_meta_box("store_cart_list_products", "Attached Products", "store_cart_list_products", "cart", "normal", "low");
	 	add_meta_box("store_order_show_status", "Order Status", "store_order_show_status", "orders", "side", "low");
	 	if ( store_get_order_shipping_address($post->ID) && store_get_order_billing_address($post->ID) ) {
		 	add_meta_box("store_cart_list_addresses", "Addresses", "store_cart_list_addresses", "orders", "normal", "low");
		}
		add_meta_box("store_list_order_history", "History", "store_list_order_history", "orders", "side", "low");
		add_meta_box("store_transaction_info", "Transaction Info", "store_transaction_info", "orders", "side", "low");
	}
 	add_action("add_meta_boxes", "store_add_cart_meta");

    // List products in a cart
    function store_cart_list_products() {
        global $post;

		$products = get_post_meta($post->ID, '_store_cart_products', true);

		// List out created options as a table
		if ( is_array($products) ) : ?>

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
							$product = get_post($prod_id); $count++; ?>

							<tr id="post-<?php $product->ID; ?>" class="post-<?php $product->ID; ?> <?php echo $count % 2 == 0 ? 'alternate' : '';?>">
								<th scope="row" class="check-column">
									<?php if ( true ) : ?>
										<div class="dashicons dashicons-yes store-variant-enabled" style="margin-left: 5px;"></div>
									<?php endif; ?>
								</th>
								<td class="post-title page-title column-title">
									<?php $root_id = store_get_product_id($product); ?>
									<strong>
										<?php echo get_the_title($root_id); ?>
									</strong>
								</td>
								<td><?php echo get_the_title($product->ID); ?></td>
								<td><?php echo $prod_qty; ?></td>
								<td><?php echo number_format( ($product->_store_price / 100), 2, '.', '') ; ?></td>
							</tr>

						<?php endforeach; ?>

					</tbody>
				</table>
			</div>

		<?php endif;

    }

	function store_cart_list_addresses() {
		global $post;

		$shipping = store_get_order_shipping_address( $post->ID );
		$billing = store_get_order_billing_address( $post->ID ); ?>

			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" colspan="2" id="title" class="manage-column column-title" style="">
							<span>Shipping Address</span>
						</th>
						<th scope="col" colspan="2" id="title" class="manage-column column-title" style="">
							<span>Billing Address</span>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php $count = 0; ?>
					<?php foreach ( $shipping as $i => $field ) : $count++; ?>
						<tr class="<?php echo $count % 2 === 0 ? 'alternate' : ''; ?>">
							<td class="row-title">
								<?php echo $i; ?>
							</td>
							<td class="desc">
								<?php echo $field; ?>
							</td>
							<td class="row-title">
								<?php echo $i; ?>
							</td>
							<td class="desc">
								<?php echo $billing[$i]; ?>
							</td>
						</tr>
					<?php endforeach; ?>

				</tbody>
			</table>

		<?php
	}

	function store_list_order_history(){
		global $post;

		$order_history = get_post_meta($post->ID, '_store_order_history', true); ?>

			<?php if ($order_history) : ?>

				<table class="widefat">

				<?php foreach ( $order_history as $i => $event ) : ?>

					<tr class="<?php echo $i % 2 === 0 ? 'alternate' : ''; ?>">
						<th scope="row" style="text-align: center;">
							<span><?php echo ( $i + 1 ) . '.'; ?></span>
						</th>
						<td class="desc">
							<?php echo $event; ?>
						</td>
					</tr>

				<?php endforeach; ?>

				</table>

			<?php endif;
	}

	function store_transaction_info(){
		global $post;

		$transaction_info = get_post_meta($post->ID, '_store_transaction_info', true);
		$shipping_info = get_post_meta($post->ID, '_store_shipping_option', true); 
		$tracking = get_post_meta($post->ID, '_store_order_tracking', true); ?>

			<div class="custom-meta">
				<p>
					<strong>Stripe ID:</strong>
				</p>
				<p style="margin-left: 20px;">
					<span><?php echo $transaction_info['stripe_id']; ?></span>
				</p>

				<p>
					<strong>Shipwire ID:</strong>
				</p>
				<p style="margin-left: 20px;">
					<span><?php echo $transaction_info['shipwire_id']; ?></span>
				</p>

				<p>
					<strong>Token:</strong>
				</p>
				<p style="margin-left: 20px;">
					<span><?php echo $transaction_info['token']; ?></span>
				</p>

				<p>
					<strong>Shipping Cost:</strong>
				</p>
				<p style="margin-left: 20px;">
					<span><?php echo $shipping_info['cost']; ?></span>
				</p>

				<?php if ( $tracking ) : ?>
					<p>
						<strong>Package Tracking:</strong>
					</p>
					<p style="margin-left: 20px;">
						<span><?php echo $tracking['id']; ?></span>
					</p>
				<?php endif; ?>

			</div>

		<?php
	}

	function store_order_show_status() {

		$current_status = store_get_order_status();
		$statuses = store_get_registered_statuses(); ?>

		<?php if ( is_array($statuses) ) : ?>

				<label class="screen-reader-text" for="store_order_status_select">Order Status</label>
				<select name="store_order_status_select" id="store_order_status_select" class="">

					<?php foreach ( $statuses as $status ) : ?>
						<option value="<?php echo $status->name; ?>" <?php selected( $status->term_id, $current_status->term_id ); ?>><?php echo $status->name; ?></option>
					<?php endforeach; ?>

				</select>

		<?php endif;

	}


/*
 * Cart Meta Boxes
 */
 	function store_add_address_meta(){
	 	add_meta_box("store_address_meta_fields", "Address", "store_address_meta_fields", "address", "normal", "low");
	}
 	add_action("add_meta_boxes", "store_add_address_meta");

 	// List products in a cart
 	function store_address_meta_fields() {
	 	global $post;

		$fields = store_get_address_fields(); ?>

		<table class="form-table"><tbody>

			<?php foreach ( $fields as $field ) :
				$meta = get_post_meta($post->ID, '_store_address_' . $field, true); ?>

				<tr>
					<th scope="row"><label for="store-address-<?php echo $field; ?>"><?php echo $field; ?></label></th>
					<td><input name="_store_address_<?php echo $field; ?>" type="text" id="store-address-<?php echo $field; ?>" value="<?php echo $meta; ?>" class="regular-text"></td>
				</tr>

			<?php endforeach; ?>

	        <tr>
	            <th scope="row">Address Type</th>
	            <td>
	                <fieldset>
	                    <legend class="screen-reader-text"><span>Address Type</span></legend>
	                    <label for="store_address_is_shipping"><input name="_store_address_is_shipping" type="checkbox" id="store_address_is_shipping" value="1" <?php checked($post->_store_address_is_shipping); ?>>Shipping Address</label><br>
	                    <label for="store_address_is_billing"><input name="_store_address_is_billing" type="checkbox" id="store_address_is_billing" value="1" <?php checked($post->_store_address_is_billing); ?>>Billing Address</label>
	                </fieldset>
	            </td>
	        </tr>

		</tbody></table>

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

		if( isset($_POST["_store_price"]) ) {
			update_post_meta($post->ID, "_store_price", intval(($_POST["_store_price"] * 100)) );
		}
		if( isset($_POST["_store_sku"]) ) {
			update_post_meta($post->ID, "_store_sku", $_POST["_store_sku"]);
		}
		if( isset($_POST["_store_enable_variant"]) ) {
			update_post_meta($post->ID, "_store_enable_variant", $_POST["_store_enable_variant"]);			
		}

		// Save address meta fields
		$address_fields = store_get_address_fields();
		foreach ( $address_fields as $address_field ) {

			if( isset($_POST["_store_address_" . $address_field]) ) {
				update_post_meta($post->ID, "_store_address_" . $address_field, $_POST["_store_address_" . $address_field]);
			}
			if( isset($_POST["_store_address_" . $address_field]) ) {
				update_post_meta($post->ID, "_store_address_" . $address_field, $_POST["_store_address_" . $address_field]);	
			}

		}
		if( isset($_POST["_store_address_is_shipping"]) ) {
			update_post_meta($post->ID, "_store_address_is_shipping", $_POST["_store_address_is_shipping"]);			
		}
		if( isset($_POST["_store_address_is_billing"]) ) {
			update_post_meta($post->ID, "_store_address_is_billing", $_POST["_store_address_is_billing"]);			
		}

		if ( isset( $_POST["_store_address_is_shipping"] ) && $_POST["_store_address_is_shipping"] ) {
			$addresses = store_get_customer_addresses( $post->post_author );
			if ( $addresses ) {
				foreach ( $addresses as $id => $address ) {
					if ( $id == $post->ID ) continue;
					update_post_meta($id, '_store_address_is_shipping', 0);
				}
			}
		}
		if ( isset( $_POST["_store_address_is_billing"] ) && $_POST["_store_address_is_billing"] ) {
			$addresses = store_get_customer_addresses( $post->post_author );
			if ( $addresses ) {
				foreach ( $addresses as $id => $address ) {
					if ( $id == $post->ID ) continue;
					update_post_meta($id, '_store_address_is_billing', 0);
				}
			}			
		}

		if( isset($_POST["store_order_status_select"]) ) {
			wp_set_post_terms( $post->ID, $_POST["store_order_status_select"], 'store_status' );
		}

	}
 	add_action('save_post', 'store_save_meta');

?>