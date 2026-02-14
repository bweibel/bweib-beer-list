<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'beer_list_register_meta' );
add_action( 'add_meta_boxes', 'beer_list_add_meta_boxes' );
add_action( 'save_post_beverage', 'beer_list_save_meta' );

/**
 * Register meta fields for REST API and block editor.
 */
function beer_list_register_meta() {
	$fields = array(
		'_beverage_abv' => array(
			'type'    => 'number',
			'default' => 0,
		),
		'_beverage_ibu' => array(
			'type'    => 'number',
			'default' => 0,
		),
		'_beverage_price' => array(
			'type'    => 'string',
			'default' => '',
		),
		'_beverage_tasting_notes' => array(
			'type'    => 'string',
			'default' => '',
		),
	);

	foreach ( $fields as $key => $opts ) {
		register_post_meta( 'beverage', $key, array(
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => $opts['type'],
			'default'       => $opts['default'],
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
}

/**
 * Add the Beverage Details meta box.
 */
function beer_list_add_meta_boxes() {
	add_meta_box(
		'beer_list_details',
		__( 'Beverage Details', 'beer-list' ),
		'beer_list_details_meta_box',
		'beverage',
		'normal',
		'high'
	);
}

/**
 * Render the meta box.
 */
function beer_list_details_meta_box( $post ) {
	wp_nonce_field( 'beer_list_save_meta', 'beer_list_meta_nonce' );

	$abv            = get_post_meta( $post->ID, '_beverage_abv', true );
	$ibu            = get_post_meta( $post->ID, '_beverage_ibu', true );
	$price          = get_post_meta( $post->ID, '_beverage_price', true );
	$tasting_notes  = get_post_meta( $post->ID, '_beverage_tasting_notes', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="beverage_abv"><?php esc_html_e( 'ABV (%)', 'beer-list' ); ?></label></th>
			<td>
				<input type="number" id="beverage_abv" name="_beverage_abv"
					value="<?php echo esc_attr( $abv ); ?>" step="0.1" min="0" max="100" style="width:100px;" />
			</td>
		</tr>
		<tr>
			<th><label for="beverage_ibu"><?php esc_html_e( 'IBU', 'beer-list' ); ?></label></th>
			<td>
				<input type="number" id="beverage_ibu" name="_beverage_ibu"
					value="<?php echo esc_attr( $ibu ); ?>" step="1" min="0" max="200" style="width:100px;" />
			</td>
		</tr>
		<tr>
			<th><label for="beverage_price"><?php esc_html_e( 'Price', 'beer-list' ); ?></label></th>
			<td>
				<input type="text" id="beverage_price" name="_beverage_price"
					value="<?php echo esc_attr( $price ); ?>" placeholder="e.g. $7 / $5.50" style="width:200px;" />
			</td>
		</tr>
		<tr>
			<th><label for="beverage_tasting_notes"><?php esc_html_e( 'Tasting Notes', 'beer-list' ); ?></label></th>
			<td>
				<textarea id="beverage_tasting_notes" name="_beverage_tasting_notes"
					rows="4" style="width:100%;"><?php echo esc_textarea( $tasting_notes ); ?></textarea>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Save meta field values.
 */
function beer_list_save_meta( $post_id ) {
	if ( ! isset( $_POST['beer_list_meta_nonce'] ) ||
		! wp_verify_nonce( $_POST['beer_list_meta_nonce'], 'beer_list_save_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_beverage_abv'            => 'floatval',
		'_beverage_ibu'            => 'intval',
		'_beverage_price'          => 'sanitize_text_field',
		'_beverage_tasting_notes'  => 'sanitize_textarea_field',
	);

	foreach ( $fields as $key => $sanitize ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, $sanitize( $_POST[ $key ] ) );
		}
	}
}
