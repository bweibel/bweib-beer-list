<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Available CSS custom properties and their defaults.
 *
 * Each entry: variable name => array( label, default, type ).
 * Type is used for input rendering: 'text', 'color', or 'number'.
 */
function beer_list_get_css_variables() {
	return array(
		// Spacing.
		'--bl-gap-grid'       => array(
			'label'   => __( 'Grid gap', 'beer-list' ),
			'default' => '1.5rem',
			'type'    => 'text',
			'group'   => __( 'Spacing', 'beer-list' ),
		),
		'--bl-gap-filters'    => array(
			'label'   => __( 'Filter button gap', 'beer-list' ),
			'default' => '0.5rem',
			'type'    => 'text',
			'group'   => __( 'Spacing', 'beer-list' ),
		),
		'--bl-gap-meta'       => array(
			'label'   => __( 'Meta items gap', 'beer-list' ),
			'default' => '0.75rem',
			'type'    => 'text',
			'group'   => __( 'Spacing', 'beer-list' ),
		),
		'--bl-padding-card'   => array(
			'label'   => __( 'Card content padding', 'beer-list' ),
			'default' => '0.75rem',
			'type'    => 'text',
			'group'   => __( 'Spacing', 'beer-list' ),
		),
		'--bl-padding-btn'    => array(
			'label'   => __( 'Button padding', 'beer-list' ),
			'default' => '0.25rem 0.75rem',
			'type'    => 'text',
			'group'   => __( 'Spacing', 'beer-list' ),
		),

		// Typography.
		'--bl-font-size-sm'   => array(
			'label'   => __( 'Small font size', 'beer-list' ),
			'default' => '0.875em',
			'type'    => 'text',
			'group'   => __( 'Typography', 'beer-list' ),
		),

		// Colors.
		'--bl-color-border'   => array(
			'label'   => __( 'Border color', 'beer-list' ),
			'default' => 'currentColor',
			'type'    => 'text',
			'group'   => __( 'Colors', 'beer-list' ),
		),
		'--bl-color-text'     => array(
			'label'   => __( 'Text color', 'beer-list' ),
			'default' => 'inherit',
			'type'    => 'text',
			'group'   => __( 'Colors', 'beer-list' ),
		),
		'--bl-color-accent'   => array(
			'label'   => __( 'Accent color', 'beer-list' ),
			'default' => 'currentColor',
			'type'    => 'text',
			'group'   => __( 'Colors', 'beer-list' ),
		),

		// Opacity.
		'--bl-opacity-muted'    => array(
			'label'   => __( 'Muted opacity', 'beer-list' ),
			'default' => '0.6',
			'type'    => 'text',
			'group'   => __( 'Opacity', 'beer-list' ),
		),
		'--bl-opacity-meta'     => array(
			'label'   => __( 'Meta text opacity', 'beer-list' ),
			'default' => '0.7',
			'type'    => 'text',
			'group'   => __( 'Opacity', 'beer-list' ),
		),
		'--bl-opacity-disabled' => array(
			'label'   => __( 'Disabled opacity', 'beer-list' ),
			'default' => '0.3',
			'type'    => 'text',
			'group'   => __( 'Opacity', 'beer-list' ),
		),

		// Grid.
		'--bl-card-min-width'          => array(
			'label'   => __( 'Card minimum width', 'beer-list' ),
			'default' => '260px',
			'type'    => 'text',
			'group'   => __( 'Grid', 'beer-list' ),
		),
		'--bl-card-min-width-detailed' => array(
			'label'   => __( 'Card minimum width (detailed)', 'beer-list' ),
			'default' => '320px',
			'type'    => 'text',
			'group'   => __( 'Grid', 'beer-list' ),
		),

		// Images.
		'--bl-image-aspect-ratio' => array(
			'label'   => __( 'Image aspect ratio', 'beer-list' ),
			'default' => '4 / 3',
			'type'    => 'text',
			'group'   => __( 'Images', 'beer-list' ),
		),
	);
}

/* ── Admin menu ────────────────────────────────────────────────────── */

add_action( 'admin_menu', 'beer_list_add_settings_page' );

function beer_list_add_settings_page() {
	add_submenu_page(
		'edit.php?post_type=beverage',
		__( 'Beer List Settings', 'beer-list' ),
		__( 'Settings', 'beer-list' ),
		'manage_options',
		'beer-list-settings',
		'beer_list_render_settings_page'
	);
}

/* ── Register option ───────────────────────────────────────────────── */

add_action( 'admin_init', 'beer_list_register_settings' );

function beer_list_register_settings() {
	register_setting( 'beer_list_settings', 'beer_list_css_overrides', array(
		'type'              => 'object',
		'sanitize_callback' => 'beer_list_sanitize_css_overrides',
		'default'           => array(),
	) );
}

function beer_list_sanitize_css_overrides( $input ) {
	if ( ! is_array( $input ) ) {
		return array();
	}

	$variables = beer_list_get_css_variables();
	$clean     = array();

	foreach ( $input as $key => $value ) {
		// Only allow known variable names.
		if ( ! isset( $variables[ $key ] ) ) {
			continue;
		}

		$value = sanitize_text_field( $value );

		// Skip empty values — they mean "use default".
		if ( '' === $value ) {
			continue;
		}

		$clean[ $key ] = $value;
	}

	return $clean;
}

/* ── Settings page renderer ────────────────────────────────────────── */

function beer_list_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$variables = beer_list_get_css_variables();
	$overrides = get_option( 'beer_list_css_overrides', array() );

	// Group variables for display.
	$groups = array();
	foreach ( $variables as $var_name => $meta ) {
		$groups[ $meta['group'] ][ $var_name ] = $meta;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Beer List Settings', 'beer-list' ); ?></h1>
		<p><?php esc_html_e( 'Override the default CSS custom properties used by the Beverage List block. Leave a field empty to use the default value.', 'beer-list' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'beer_list_settings' ); ?>

			<?php foreach ( $groups as $group_label => $group_vars ) : ?>
				<h2><?php echo esc_html( $group_label ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( $group_vars as $var_name => $meta ) : ?>
						<?php $current = $overrides[ $var_name ] ?? ''; ?>
						<tr>
							<th scope="row">
								<label for="beer-list-<?php echo esc_attr( $var_name ); ?>">
									<?php echo esc_html( $meta['label'] ); ?>
								</label>
							</th>
							<td>
								<input
									type="text"
									id="beer-list-<?php echo esc_attr( $var_name ); ?>"
									name="beer_list_css_overrides[<?php echo esc_attr( $var_name ); ?>]"
									value="<?php echo esc_attr( $current ); ?>"
									class="regular-text"
									placeholder="<?php echo esc_attr( $meta['default'] ); ?>"
								/>
								<p class="description">
									<?php
									echo wp_kses(
										sprintf(
											/* translators: 1: CSS variable name, 2: default value */
											__( '%1$s — default: %2$s', 'beer-list' ),
											'<code>' . esc_html( $var_name ) . '</code>',
											'<code>' . esc_html( $meta['default'] ) . '</code>'
										),
										array( 'code' => array() )
									);
									?>
								</p>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>

			<?php submit_button(); ?>
		</form>

		<hr />
		<h2><?php esc_html_e( 'CSS Variable Reference', 'beer-list' ); ?></h2>
		<p><?php esc_html_e( 'You can also override these variables directly in your theme CSS:', 'beer-list' ); ?></p>
		<pre style="background: #f0f0f0; padding: 1em; overflow-x: auto;"><code>.wp-block-beer-list-beverage-list {
<?php
foreach ( $variables as $var_name => $meta ) {
	$current = $overrides[ $var_name ] ?? $meta['default'];
	echo "\t" . esc_html( $var_name ) . ': ' . esc_html( $current ) . ";\n";
}
?>}</code></pre>
	</div>
	<?php
}

/* ── Frontend CSS override output ──────────────────────────────────── */

add_action( 'wp_head', 'beer_list_output_css_overrides', 100 );

function beer_list_output_css_overrides() {
	$overrides = get_option( 'beer_list_css_overrides', array() );

	if ( empty( $overrides ) ) {
		return;
	}

	$variables = beer_list_get_css_variables();
	$rules     = '';

	foreach ( $overrides as $var_name => $value ) {
		// Only output known variables; strip anything that could break CSS context.
		if ( ! isset( $variables[ $var_name ] ) ) {
			continue;
		}
		$safe_value = wp_strip_all_tags( $value );
		$rules     .= "\t" . $var_name . ': ' . $safe_value . ";\n";
	}

	if ( '' === $rules ) {
		return;
	}

	echo "<style id=\"beer-list-css-overrides\">\n.wp-block-beer-list-beverage-list {\n" . $rules . "}\n</style>\n";
}
