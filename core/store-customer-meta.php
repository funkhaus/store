<?php
/*
 * Add custom metabox to the user profile page in WordPress
 */

	add_action( 'show_user_profile', 'store_custom_user_meta' );
	add_action( 'edit_user_profile', 'store_custom_user_meta' );

	function store_custom_user_meta( $user ) { ?>

		<h3>Store User Meta</h3>

		<table class="form-table">

			<tr>
				<th><label for="active-cart-id">Active Cart ID</label></th>

				<td>
					<input type="text" name="_store_active_cart_id" id="active-cart-id" value="<?php echo get_user_meta( $user->ID, '_store_active_cart_id', true ); ?>" class="regular-text" ><br />
					<span class="description">The ID of the last active cart this user had.</span>
				</td>
			</tr>

		</table>

		<?php $addresses = store_get_customer_addresses( $user->ID ); ?>

		<?php if ( $addresses ) : $count = 0; ?>
			<?php foreach ( $addresses as $id => $address ) : $count++;
					$title = 'Address ' . $count;
					if ( store_is_shipping_address($id) ) $title = 'Shipping Address';
					if ( store_is_billing_address($id) ) $title = 'Billing Address';
					if ( store_is_billing_address($id) && store_is_shipping_address($id) ) $title = 'Shipping/Billing Address'; ?>

				<table class="form-table">
					<tbody>
						<tr>
							<th><?php echo $title; ?></th>
							<td>
								<table class="widefat">
									<tbody>
										<?php $i = 0; ?>
										<?php foreach ( $address as $address_field => $value ) : $i++; ?>
											<tr class="<?php echo $i % 2 == 0 ? 'alternate': ''; ?>">
												<td class="row-title"><?php echo $address_field; ?></td>
												<td class="desc"><?php echo $value; ?></td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
								<a href="<?php echo get_edit_post_link( $id ); ?>" style="float: right; margin-top: 10px;"><span class="edit">edit</span></a>
								<a href="<?php echo get_delete_post_link( $id, null, true ); ?>" style="float: right; margin-top: 10px; margin-right: 15px;"><span class="edit">delete</span></a>
							</td>
						</tr>
					</tbody>
				</table>
			<?php endforeach; ?>
		<?php endif; ?>

	<?php }

/*
 * Save the metabox vaules
 */		
	add_action( 'personal_options_update', 'store_save_custom_user_meta' );
	add_action( 'edit_user_profile_update', 'store_save_custom_user_meta' );

	function store_save_custom_user_meta( $user_id ) {

		// Abort if user not allowed to edit
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		//Update active cart ID
		if( isset($_POST['_store_active_cart_id']) ) {
			update_user_meta( $user_id, '_store_active_cart_id', $_POST['_store_active_cart_id']);								
		}

	}
?>