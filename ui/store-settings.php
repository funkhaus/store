<div class="wrap">
	<h2>Store Settings</h2>
	<form action="options.php" method="post" id="store_settings">
		<?php settings_fields('store_settings'); ?>
		<?php $sw_set = get_option('store_sw_settings'); ?>
		<?php $st_set = get_option('store_st_settings'); ?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label>Enable Shipwire:</label></th>
					<td>
						<input name="store_sw_settings[enabled]" type="checkbox" id="store_sw_enabled" <?php checked( $sw_set['enabled'] ); ?>  value="1">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="store_sw_usnm">Shipwire Username:</label></th>
					<td>
						<input name="store_sw_settings[usnm]" type="text" title="Client ID" id="store_sw_usnm" value="<?php echo $sw_set['usnm']; ?>">
						<p class="description"></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="store_sw_pswd">Shipwire Password:</label></th>
					<td>
						<input name="store_sw_settings[pswd]" type="password" id="store_sw_pswd" value="<?php echo $sw_set['pswd']; ?>">
						<p class="description"></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label>Enable Stripe:</label></th>
					<td>
						<input name="store_st_settings[enabled]" type="checkbox" id="store_st_settings" <?php checked( $st_set['enabled'] ); ?>  value="1">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="store_st_scrt">Stripe Secret Key:</label></th>
					<td>
						<input name="store_st_settings[scrt]" type="text" title="Secret Key" id="store_st_scrt" value="<?php echo $st_set['scrt']; ?>">
						<p class="description"></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="store_st_pblsh">Stripe Publishable Key:</label></th>
					<td>
						<input name="store_st_settings[pblsh]" type="text" title="Publishable Key" id="store_st_pblsh" value="<?php echo $st_set['pblsh']; ?>">
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div><!-- END Wrap -->