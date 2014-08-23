<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Get a product's inventory 
 *
 * @Param: MIXED, ID of product to retrieve quantity for, or $post object
 * @Returns: MIXED, integer value of quantity on success, bool false on failure
 */
	function store_get_shipwire_quantity( ){

		// Set URL to send request to
		$url = 'https://api.shipwire.com/exec/InventoryServices.php';

		// Set XML request
		$_ = array('<?xml version="1.0" encoding="UTF-8"?>');
		$_[] = '<!DOCTYPE InventoryUpdate SYSTEM "http://www.shipwire.com/exec/download/InventoryUpdate.dtd">';
		$_[] = '<InventoryUpdate>';
		$_[] = '<Username>john@funkhaus.us</Username>';
		$_[] = '<Password>funkhaus</Password>';
		$_[] = '<Server>Test</Server>';
		$_[] = '</InventoryUpdate>';
		$request = join( "\n", $_ );

		// Send request
		$response = wp_remote_post(
		    $url,
		    array(
		        'method' => 'POST',
		        'timeout' => 45,
		        'redirection' => 5,
		        'httpversion' => '1.0',
		        'headers' => array(
					'Content-Type' => 'application/xml',
		        ),
		        'body' => trim( $request ),
		        'sslverify' => false
		    )
		);
		$body = wp_remote_retrieve_body( $response );

		// Parse XML into usable object
		$obj = simplexml_load_string( $body );

	};


?>