<?php header('Content-Type: application/xml'); ?>
<?php ob_start(); ?>

	<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE InventoryUpdate SYSTEM "http://www.shipwire.com/exec/download/InventoryUpdate.dtd">
	<InventoryUpdate>
		<Username>api_user@example.com</Username>
		<Password>yourpassword</Password>
		<Server>Test</Server>
	</InventoryUpdate>

<?php ob_get_clean(); ?>