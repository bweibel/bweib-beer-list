<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'beer_list_register_blocks' );

function beer_list_register_blocks() {
	register_block_type( BEER_LIST_PLUGIN_DIR . 'src/blocks/beverage-list' );

	wp_register_script(
		'beer-list-beverage-list-view',
		BEER_LIST_PLUGIN_URL . 'build/blocks/beverage-list/view.js',
		array(),
		BEER_LIST_VERSION,
		true
	);
}

add_action( 'wp_enqueue_scripts', 'beer_list_enqueue_view_scripts' );

function beer_list_enqueue_view_scripts() {
	if ( has_block( 'beer-list/beverage-list' ) ) {
		wp_enqueue_script( 'beer-list-beverage-list-view' );
	}
}
