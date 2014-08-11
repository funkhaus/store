<?php
/*
 * This file contains all the PHP functions that get run using an AJAX request
 
	The difference between wp_ajax and wp_ajax_nopriv is as simple as being logged in vs not
	
	wp_ajax – Use when you require the user to be logged in.	
		add_action( 'wp_ajax_<ACTION NAME>', <YOUR FUNCTION> );
	
	wp_ajax_nopriv – Use when you do not require the user to be logged in.
		add_action( 'wp_ajax_nopriv_<ACTION NAME>', <YOUR FUNCTION> );
	
	The one trick to this is that if you want to handle BOTH cases (i.e. the user is logged in as well as not), you need to implement both action hooks.
 */
?>