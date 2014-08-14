<div class="wrap">
	<h2>Store Settings</h2>
	<form action="options.php" method="post" id="store_settings">
		<?php settings_fields('store_settings'); ?>
		<?php $set = get_option('store_sw_settings'); ?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label>Enable Shipwire:</label></th>
					<td>
						<input name="store_sw_settings[enabled]" type="checkbox" id="store_sw_enabled" <?php checked( $set['enabled'] ); ?>  value="1">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="store_sw_usnm">Shipwire Username:</label></th>
					<td>
						<input name="store_sw_settings[usnm]" type="text" title="Client ID" id="store_sw_usnm" value="<?php echo $set['usnm']; ?>">
						<p class="description"></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="store_sw_pswd">Shipwire Password:</label></th>
					<td>
						<input name="store_sw_settings[pswd]" type="password" id="store_sw_pswd" value="<?php echo $set['pswd']; ?>">
						<p class="description"></p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div><!-- END Wrap -->