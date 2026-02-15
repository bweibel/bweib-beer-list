<?php
/**
 * CSV Importer — admin page, import handler, and CSV download actions.
 *
 * Supported CSV columns:
 *   title, description, abv, ibu, price, tasting_notes,
 *   beverage_type, beverage_style, beverage_availability,
 *   serving_format, status
 *
 * Taxonomy columns accept comma-separated term names.
 * `status` defaults to "publish" when omitted.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── Admin menu ─────────────────────────────────────────────────────── */

add_action( 'admin_menu', 'beer_list_add_importer_page' );

function beer_list_add_importer_page() {
	add_submenu_page(
		'edit.php?post_type=beverage',
		__( 'Import Beverages', 'beer-list' ),
		__( 'Import CSV', 'beer-list' ),
		'manage_options',
		'beer-list-import',
		'beer_list_render_importer_page'
	);
}

/* ── Importer page renderer ─────────────────────────────────────────── */

function beer_list_render_importer_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$result = beer_list_maybe_run_import();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Beverages', 'beer-list' ); ?></h1>
		<p>
			<?php esc_html_e( 'Upload a CSV file to bulk-import beverages. Each row creates one beverage post. Taxonomy terms are created automatically if they do not already exist.', 'beer-list' ); ?>
		</p>

		<?php if ( null !== $result ) : ?>
			<?php beer_list_render_import_results( $result ); ?>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Upload CSV', 'beer-list' ); ?></h2>
		<form method="post" enctype="multipart/form-data" action="">
			<?php wp_nonce_field( 'beer_list_import_csv', 'beer_list_import_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="beer-list-csv-file"><?php esc_html_e( 'CSV File', 'beer-list' ); ?></label>
					</th>
					<td>
						<input type="file" id="beer-list-csv-file" name="beer_list_csv" accept=".csv,text/csv" required />
						<p class="description">
							<?php esc_html_e( 'File must be UTF-8 encoded and use comma delimiters. Download a template below to get started.', 'beer-list' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Duplicate Handling', 'beer-list' ); ?></th>
					<td>
						<fieldset>
							<label>
								<input type="radio" name="beer_list_duplicate" value="skip" checked />
								<?php esc_html_e( 'Skip rows where a beverage with the same title already exists', 'beer-list' ); ?>
							</label><br />
							<label>
								<input type="radio" name="beer_list_duplicate" value="update" />
								<?php esc_html_e( 'Update existing beverages with the same title', 'beer-list' ); ?>
							</label><br />
							<label>
								<input type="radio" name="beer_list_duplicate" value="create" />
								<?php esc_html_e( 'Always create a new beverage (may result in duplicates)', 'beer-list' ); ?>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Import Beverages', 'beer-list' ) ); ?>
		</form>

		<hr />

		<h2><?php esc_html_e( 'CSV Format', 'beer-list' ); ?></h2>
		<p><?php esc_html_e( 'The first row must be a header row containing these column names:', 'beer-list' ); ?></p>
		<table class="widefat striped" style="max-width: 700px; margin-bottom: 1.5em;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Column', 'beer-list' ); ?></th>
					<th><?php esc_html_e( 'Required', 'beer-list' ); ?></th>
					<th><?php esc_html_e( 'Notes', 'beer-list' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr><td><code>title</code></td><td><?php esc_html_e( 'Yes', 'beer-list' ); ?></td><td><?php esc_html_e( 'Beverage name.', 'beer-list' ); ?></td></tr>
				<tr><td><code>description</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'Main post content / description.', 'beer-list' ); ?></td></tr>
				<tr><td><code>abv</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'Alcohol by volume as a decimal (e.g. 6.5).', 'beer-list' ); ?></td></tr>
				<tr><td><code>ibu</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'International bitterness units, whole number.', 'beer-list' ); ?></td></tr>
				<tr><td><code>price</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'Price display text (e.g. $7 / $5.50).', 'beer-list' ); ?></td></tr>
				<tr><td><code>tasting_notes</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'Flavor and tasting description.', 'beer-list' ); ?></td></tr>
				<tr><td><code>beverage_type</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'Comma-separated term names (e.g. Beer, Cider).', 'beer-list' ); ?></td></tr>
				<tr><td><code>beverage_style</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'Comma-separated term names (e.g. IPA, Stout).', 'beer-list' ); ?></td></tr>
				<tr><td><code>beverage_availability</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'Comma-separated term names (e.g. Year-Round, Seasonal).', 'beer-list' ); ?></td></tr>
				<tr><td><code>serving_format</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'Comma-separated term names (e.g. Draft, Can, Bottle).', 'beer-list' ); ?></td></tr>
				<tr><td><code>status</code></td><td><?php esc_html_e( 'No', 'beer-list' ); ?></td><td><?php esc_html_e( 'publish or draft. Defaults to publish.', 'beer-list' ); ?></td></tr>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Download CSV Files', 'beer-list' ); ?></h2>
		<p><?php esc_html_e( 'Use these files to get started quickly.', 'beer-list' ); ?></p>
		<p>
			<a href="<?php echo esc_url( beer_list_download_url( 'template' ) ); ?>" class="button button-secondary">
				<?php esc_html_e( 'Download Blank Template', 'beer-list' ); ?>
			</a>
			&nbsp;
			<a href="<?php echo esc_url( beer_list_download_url( 'example' ) ); ?>" class="button button-secondary">
				<?php esc_html_e( 'Download Example (20 Beverages)', 'beer-list' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/* ── Import results display ─────────────────────────────────────────── */

function beer_list_render_import_results( $result ) {
	$created = count( $result['created'] );
	$updated = count( $result['updated'] );
	$skipped = count( $result['skipped'] );
	$errors  = $result['errors'];
	$total   = $created + $updated + $skipped + count( $errors );
	?>
	<div class="notice <?php echo ( empty( $errors ) ? 'notice-success' : 'notice-warning' ); ?> is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Import complete.', 'beer-list' ); ?></strong>
			<?php
			echo esc_html( sprintf(
				/* translators: 1: total 2: created 3: updated 4: skipped 5: errors */
				__( '%1$d rows processed — %2$d created, %3$d updated, %4$d skipped, %5$d errors.', 'beer-list' ),
				$total, $created, $updated, $skipped, count( $errors )
			) );
			?>
		</p>
		<?php if ( ! empty( $errors ) ) : ?>
			<ul>
				<?php foreach ( $errors as $error ) : ?>
					<li><?php echo esc_html( $error ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}

/* ── Import handler (runs on page load when form is submitted) ───────── */

function beer_list_maybe_run_import() {
	if ( ! isset( $_POST['beer_list_import_nonce'] ) ) {
		return null;
	}

	if ( ! wp_verify_nonce( $_POST['beer_list_import_nonce'], 'beer_list_import_csv' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'beer-list' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to import beverages.', 'beer-list' ) );
	}

	if ( empty( $_FILES['beer_list_csv']['tmp_name'] ) ) {
		return array(
			'created' => array(),
			'updated' => array(),
			'skipped' => array(),
			'errors'  => array( __( 'No file was uploaded.', 'beer-list' ) ),
		);
	}

	$file = $_FILES['beer_list_csv'];

	// Validate MIME type.
	$allowed_types = array( 'text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel' );
	$finfo         = finfo_open( FILEINFO_MIME_TYPE );
	$mime          = finfo_file( $finfo, $file['tmp_name'] );
	finfo_close( $finfo );
	if ( ! in_array( $mime, $allowed_types, true ) && ! str_ends_with( strtolower( $file['name'] ), '.csv' ) ) {
		return array(
			'created' => array(),
			'updated' => array(),
			'skipped' => array(),
			'errors'  => array( __( 'Uploaded file does not appear to be a valid CSV.', 'beer-list' ) ),
		);
	}

	$duplicate_handling = isset( $_POST['beer_list_duplicate'] )
		? sanitize_text_field( $_POST['beer_list_duplicate'] )
		: 'skip';

	if ( ! in_array( $duplicate_handling, array( 'skip', 'update', 'create' ), true ) ) {
		$duplicate_handling = 'skip';
	}

	return beer_list_process_csv( $file['tmp_name'], $duplicate_handling );
}

/* ── CSV processing ─────────────────────────────────────────────────── */

/**
 * Parse and import a CSV file.
 *
 * @param string $filepath        Absolute path to the uploaded CSV file.
 * @param string $duplicate_handling  'skip' | 'update' | 'create'
 * @return array { created: int[], updated: int[], skipped: int[], errors: string[] }
 */
function beer_list_process_csv( $filepath, $duplicate_handling = 'skip' ) {
	$result = array(
		'created' => array(),
		'updated' => array(),
		'skipped' => array(),
		'errors'  => array(),
	);

	$handle = fopen( $filepath, 'r' );
	if ( ! $handle ) {
		$result['errors'][] = __( 'Could not open the uploaded file for reading.', 'beer-list' );
		return $result;
	}

	// Auto-detect BOM (UTF-8 BOM from Excel).
	$bom = fread( $handle, 3 );
	if ( $bom !== "\xEF\xBB\xBF" ) {
		rewind( $handle );
	}

	// Read header row.
	$headers = fgetcsv( $handle );
	if ( ! $headers ) {
		fclose( $handle );
		$result['errors'][] = __( 'The file appears to be empty or has no header row.', 'beer-list' );
		return $result;
	}

	// Normalise headers (trim whitespace, lowercase).
	$headers = array_map( function ( $h ) {
		return strtolower( trim( $h ) );
	}, $headers );

	// Require at minimum a title column.
	if ( ! in_array( 'title', $headers, true ) ) {
		fclose( $handle );
		$result['errors'][] = __( 'CSV is missing a required "title" column.', 'beer-list' );
		return $result;
	}

	$row_number = 1;

	while ( ( $row = fgetcsv( $handle ) ) !== false ) {
		$row_number++;

		// Skip completely blank rows.
		if ( array_filter( $row ) === array() ) {
			continue;
		}

		// Map headers to values.
		$data = array();
		foreach ( $headers as $i => $header ) {
			$data[ $header ] = isset( $row[ $i ] ) ? trim( $row[ $i ] ) : '';
		}

		$import_result = beer_list_import_row( $data, $row_number, $duplicate_handling );

		if ( is_wp_error( $import_result ) ) {
			$result['errors'][] = sprintf(
				/* translators: 1: row number 2: error message */
				__( 'Row %1$d: %2$s', 'beer-list' ),
				$row_number,
				$import_result->get_error_message()
			);
		} elseif ( 'created' === $import_result['action'] ) {
			$result['created'][] = $import_result['post_id'];
		} elseif ( 'updated' === $import_result['action'] ) {
			$result['updated'][] = $import_result['post_id'];
		} elseif ( 'skipped' === $import_result['action'] ) {
			$result['skipped'][] = $row_number;
		}
	}

	fclose( $handle );
	return $result;
}

/**
 * Import a single CSV row.
 *
 * @param array  $data                 Associative array of column => value.
 * @param int    $row_number           Row number for error messages.
 * @param string $duplicate_handling   'skip' | 'update' | 'create'
 * @return array|WP_Error  Array with 'action' and 'post_id', or WP_Error.
 */
function beer_list_import_row( $data, $row_number, $duplicate_handling ) {
	$title = sanitize_text_field( $data['title'] ?? '' );

	if ( '' === $title ) {
		return new WP_Error( 'missing_title', __( 'Row is missing a title and was skipped.', 'beer-list' ) );
	}

	// Determine post status.
	$raw_status    = strtolower( $data['status'] ?? 'publish' );
	$post_status   = in_array( $raw_status, array( 'publish', 'draft' ), true ) ? $raw_status : 'publish';

	// Check for existing post with the same title.
	$existing_id = 0;
	if ( 'create' !== $duplicate_handling ) {
		$existing = get_page_by_title( $title, OBJECT, 'beverage' );
		if ( $existing ) {
			$existing_id = $existing->ID;
		}
	}

	if ( $existing_id && 'skip' === $duplicate_handling ) {
		return array( 'action' => 'skipped', 'post_id' => $existing_id );
	}

	$post_args = array(
		'post_type'    => 'beverage',
		'post_title'   => $title,
		'post_content' => wp_kses_post( $data['description'] ?? '' ),
		'post_status'  => $post_status,
	);

	if ( $existing_id ) {
		// Update existing post.
		$post_args['ID'] = $existing_id;
		$post_id         = wp_update_post( $post_args, true );
		$action          = 'updated';
	} else {
		// Insert new post.
		$post_id = wp_insert_post( $post_args, true );
		$action  = 'created';
	}

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// Meta fields.
	$meta_map = array(
		'abv'           => '_beverage_abv',
		'ibu'           => '_beverage_ibu',
		'price'         => '_beverage_price',
		'tasting_notes' => '_beverage_tasting_notes',
	);

	foreach ( $meta_map as $csv_col => $meta_key ) {
		if ( isset( $data[ $csv_col ] ) && '' !== $data[ $csv_col ] ) {
			$value = $data[ $csv_col ];
			if ( in_array( $csv_col, array( 'abv' ), true ) ) {
				$value = floatval( $value );
			} elseif ( 'ibu' === $csv_col ) {
				$value = intval( $value );
			} else {
				$value = sanitize_textarea_field( $value );
			}
			update_post_meta( $post_id, $meta_key, $value );
		}
	}

	// Taxonomy terms.
	$taxonomy_map = array(
		'beverage_type'         => 'beverage_type',
		'beverage_style'        => 'beverage_style',
		'beverage_availability' => 'beverage_availability',
		'serving_format'        => 'serving_format',
	);

	foreach ( $taxonomy_map as $csv_col => $taxonomy ) {
		if ( empty( $data[ $csv_col ] ) ) {
			continue;
		}

		$term_names = array_filter( array_map( 'trim', explode( ',', $data[ $csv_col ] ) ) );
		$term_ids   = array();

		foreach ( $term_names as $term_name ) {
			$term_name = sanitize_text_field( $term_name );
			if ( '' === $term_name ) {
				continue;
			}

			// Find or create the term.
			$term = term_exists( $term_name, $taxonomy );
			if ( ! $term ) {
				$term = wp_insert_term( $term_name, $taxonomy );
			}

			if ( ! is_wp_error( $term ) ) {
				$term_ids[] = (int) ( $term['term_id'] ?? $term );
			}
		}

		if ( ! empty( $term_ids ) ) {
			wp_set_post_terms( $post_id, $term_ids, $taxonomy );
		}
	}

	return array( 'action' => $action, 'post_id' => $post_id );
}

/* ── CSV download actions ───────────────────────────────────────────── */

/**
 * Build the URL for a CSV download action.
 *
 * @param string $file 'template' or 'example'
 * @return string
 */
function beer_list_download_url( $file ) {
	return wp_nonce_url(
		add_query_arg(
			array(
				'action'         => 'beer_list_download_csv',
				'beer_list_file' => $file,
			),
			admin_url( 'admin-post.php' )
		),
		'beer_list_download_csv'
	);
}

add_action( 'admin_post_beer_list_download_csv', 'beer_list_handle_csv_download' );

function beer_list_handle_csv_download() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to download this file.', 'beer-list' ) );
	}

	check_admin_referer( 'beer_list_download_csv' );

	$file = isset( $_GET['beer_list_file'] ) ? sanitize_key( $_GET['beer_list_file'] ) : '';

	if ( ! in_array( $file, array( 'template', 'example' ), true ) ) {
		wp_die( esc_html__( 'Invalid file requested.', 'beer-list' ) );
	}

	$filename = ( 'template' === $file )
		? 'beverages-template.csv'
		: 'beverages-example.csv';

	$filepath = BEER_LIST_PLUGIN_DIR . 'data/' . $filename;

	if ( ! file_exists( $filepath ) ) {
		wp_die( esc_html__( 'The requested file could not be found.', 'beer-list' ) );
	}

	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . filesize( $filepath ) );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
	readfile( $filepath );
	exit;
}
