<div class="wrap">
	<h2>Store Settings</h2>
	<form action="options.php" method="post" id="store_settings">
		<?php settings_fields('store_settings'); ?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label>Enable auto-import:</label></th>
					<td>
						<input name="fgram_auto" type="checkbox" id="fgram_auto" <?php checked( get_option('fgram_auto') ); ?>  value="1">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="fgram_api_key">API Key (Client ID):</label></th>
					<td>
						<input name="fgram_api_key" type="text" title="Client ID" id="fgram_api_key" value="<?php echo get_option('fgram_api_key'); ?>">
						<p class="description">http://instagram.com/developer/register/</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="fgram_user_list">Users to import:</label></th>
					<td>
						<input name="fgram_user_list" type="text" id="fgram_user_list" value="<?php echo get_option('fgram_user_list'); ?>">
						<p class="description">If left empty, all posts for specified tags will be imported</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="fgram_tag_list">Filter by tags:</label></th>
					<td>
						<input name="fgram_tag_list" type="text" id="fgram_tag_list" value="<?php echo get_option('fgram_tag_list'); ?>">
						<p class="description">If left empty, all posts for specified users will be imported</p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</p>
	</form>
</div><!-- END Wrap -->