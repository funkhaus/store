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
					<input type="text" name="active_cart_id" id="active-cart-id" value="<?php echo esc_attr( get_user_meta( $user->ID, 'store_active_cart_id', true ) ); ?>" class="regular-text" ><br />
					<span class="description">The ID of the last active cart this user had.</span>
				</td>
			</tr>		

		</table>

	<?php }
	
/*
 * Save the metabox vaules
 */		
	add_action( 'personal_options_update', 'store_save_custom_user_meta' );
	add_action( 'edit_user_profile_update', 'store_save_custom_user_meta' );
	
	function store_save_custom_user_meta( $user_id ) {
		
		// Abort if user not allowed to edit
		if ( !current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		//Update active cart ID
		if( isset($_POST['store_active_cart_id']) ) {
			update_user_meta( $user_id, 'store_active_cart_id', $_POST['store_active_cart_id']);								
		}

	}
?>