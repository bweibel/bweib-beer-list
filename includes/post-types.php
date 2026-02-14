<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'beer_list_register_post_types' );

function beer_list_register_post_types() {
	$labels = array(
		'name'                  => __( 'Beverages', 'beer-list' ),
		'singular_name'         => __( 'Beverage', 'beer-list' ),
		'add_new'               => __( 'Add New', 'beer-list' ),
		'add_new_item'          => __( 'Add New Beverage', 'beer-list' ),
		'edit_item'             => __( 'Edit Beverage', 'beer-list' ),
		'new_item'              => __( 'New Beverage', 'beer-list' ),
		'view_item'             => __( 'View Beverage', 'beer-list' ),
		'view_items'            => __( 'View Beverages', 'beer-list' ),
		'search_items'          => __( 'Search Beverages', 'beer-list' ),
		'not_found'             => __( 'No beverages found.', 'beer-list' ),
		'not_found_in_trash'    => __( 'No beverages found in Trash.', 'beer-list' ),
		'all_items'             => __( 'All Beverages', 'beer-list' ),
		'archives'              => __( 'Beverage Archives', 'beer-list' ),
		'attributes'            => __( 'Beverage Attributes', 'beer-list' ),
		'insert_into_item'      => __( 'Insert into beverage', 'beer-list' ),
		'uploaded_to_this_item' => __( 'Uploaded to this beverage', 'beer-list' ),
		'menu_name'             => __( 'Beverages', 'beer-list' ),
	);

	$args = array(
		'labels'       => $labels,
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-beer',
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'rewrite'      => array( 'slug' => 'beverages' ),
	);

	register_post_type( 'beverage', $args );
}