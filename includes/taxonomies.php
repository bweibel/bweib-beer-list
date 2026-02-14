<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'beer_list_register_taxonomies' );

function beer_list_register_taxonomies() {
	// Beverage Type — broad category (hierarchical).
	register_taxonomy( 'beverage_type', 'beverage', array(
		'labels'            => array(
			'name'              => __( 'Beverage Types', 'beer-list' ),
			'singular_name'     => __( 'Beverage Type', 'beer-list' ),
			'search_items'      => __( 'Search Beverage Types', 'beer-list' ),
			'all_items'         => __( 'All Beverage Types', 'beer-list' ),
			'parent_item'       => __( 'Parent Beverage Type', 'beer-list' ),
			'parent_item_colon' => __( 'Parent Beverage Type:', 'beer-list' ),
			'edit_item'         => __( 'Edit Beverage Type', 'beer-list' ),
			'update_item'       => __( 'Update Beverage Type', 'beer-list' ),
			'add_new_item'      => __( 'Add New Beverage Type', 'beer-list' ),
			'new_item_name'     => __( 'New Beverage Type Name', 'beer-list' ),
			'menu_name'         => __( 'Beverage Types', 'beer-list' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'beverage-type' ),
	) );

	// Style — specific style (hierarchical).
	register_taxonomy( 'beverage_style', 'beverage', array(
		'labels'            => array(
			'name'              => __( 'Styles', 'beer-list' ),
			'singular_name'     => __( 'Style', 'beer-list' ),
			'search_items'      => __( 'Search Styles', 'beer-list' ),
			'all_items'         => __( 'All Styles', 'beer-list' ),
			'parent_item'       => __( 'Parent Style', 'beer-list' ),
			'parent_item_colon' => __( 'Parent Style:', 'beer-list' ),
			'edit_item'         => __( 'Edit Style', 'beer-list' ),
			'update_item'       => __( 'Update Style', 'beer-list' ),
			'add_new_item'      => __( 'Add New Style', 'beer-list' ),
			'new_item_name'     => __( 'New Style Name', 'beer-list' ),
			'menu_name'         => __( 'Styles', 'beer-list' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'beverage-style' ),
	) );

	// Availability — flat tags.
	register_taxonomy( 'beverage_availability', 'beverage', array(
		'labels'            => array(
			'name'                       => __( 'Availability', 'beer-list' ),
			'singular_name'              => __( 'Availability', 'beer-list' ),
			'search_items'               => __( 'Search Availability', 'beer-list' ),
			'all_items'                  => __( 'All Availability', 'beer-list' ),
			'edit_item'                  => __( 'Edit Availability', 'beer-list' ),
			'update_item'                => __( 'Update Availability', 'beer-list' ),
			'add_new_item'               => __( 'Add New Availability', 'beer-list' ),
			'new_item_name'              => __( 'New Availability Name', 'beer-list' ),
			'separate_items_with_commas' => __( 'Separate with commas', 'beer-list' ),
			'add_or_remove_items'        => __( 'Add or remove availability', 'beer-list' ),
			'choose_from_most_used'      => __( 'Choose from most used', 'beer-list' ),
			'menu_name'                  => __( 'Availability', 'beer-list' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'beverage-availability' ),
	) );

	// Serving Format — flat tags.
	register_taxonomy( 'serving_format', 'beverage', array(
		'labels'            => array(
			'name'                       => __( 'Serving Formats', 'beer-list' ),
			'singular_name'              => __( 'Serving Format', 'beer-list' ),
			'search_items'               => __( 'Search Serving Formats', 'beer-list' ),
			'all_items'                  => __( 'All Serving Formats', 'beer-list' ),
			'edit_item'                  => __( 'Edit Serving Format', 'beer-list' ),
			'update_item'                => __( 'Update Serving Format', 'beer-list' ),
			'add_new_item'               => __( 'Add New Serving Format', 'beer-list' ),
			'new_item_name'              => __( 'New Serving Format Name', 'beer-list' ),
			'separate_items_with_commas' => __( 'Separate with commas', 'beer-list' ),
			'add_or_remove_items'        => __( 'Add or remove serving formats', 'beer-list' ),
			'choose_from_most_used'      => __( 'Choose from most used', 'beer-list' ),
			'menu_name'                  => __( 'Serving Formats', 'beer-list' ),
		),
		'hierarchical'      => false,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'serving-format' ),
	) );
}
