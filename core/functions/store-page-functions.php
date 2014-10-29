<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
 * @Description: Get the ID of a store page by slug
 *
 * @Param: STRING,
 * @Returns: MIXED,
 */
	function store_get_page_id( $page = false ){

		if ( ! is_string($page) || ! $page ) return false;

	    $args = array(
			'posts_per_page'	=> 1,
			'orderby'			=> 'post_date',
			'order'				=> 'DESC',
			'post_type'			=> 'store',
			'name'				=> $page,
			'fields'			=> 'ids'
		);
		$result = get_posts($args);

		if ( ! $result )
			return false;

		return reset($result);
	};