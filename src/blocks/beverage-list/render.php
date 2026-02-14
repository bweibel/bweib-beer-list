<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts_per_page  = $attributes['postsPerPage'] ?? 12;
$beverage_type   = $attributes['beverageType'] ?? '';
$show_filters    = $attributes['showFilters'] ?? true;
$display_mode    = $attributes['displayMode'] ?? 'simple';
$show_search     = $attributes['showSearch'] ?? false;
$show_pagination = $attributes['showPagination'] ?? false;
$items_per_page  = $attributes['itemsPerPage'] ?? 6;
$is_detailed     = 'detailed' === $display_mode;

$query_args = array(
	'post_type'      => 'beverage',
	'posts_per_page' => $posts_per_page,
	'post_status'    => 'publish',
);

if ( $beverage_type ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => 'beverage_type',
			'field'    => 'term_id',
			'terms'    => intval( $beverage_type ),
		),
	);
}

$beverages = new WP_Query( $query_args );
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wp-block-beer-list-beverage-list is-mode-' . esc_attr( $display_mode ) ) ); ?>>
	<?php if ( $show_search ) : ?>
		<div class="beverage-list__search">
			<input
				type="search"
				class="beverage-list__search-input"
				placeholder="<?php esc_attr_e( 'Search beverages…', 'beer-list' ); ?>"
			/>
		</div>
	<?php endif; ?>

	<?php if ( $show_filters ) : ?>
		<?php
		$types = get_terms( array(
			'taxonomy'   => 'beverage_type',
			'hide_empty' => true,
		) );
		?>
		<?php if ( ! empty( $types ) && ! is_wp_error( $types ) ) : ?>
			<div class="beverage-list__filters">
				<button class="beverage-list__filter is-active" data-type=""><?php esc_html_e( 'All', 'beer-list' ); ?></button>
				<?php foreach ( $types as $type ) : ?>
					<button class="beverage-list__filter" data-type="<?php echo esc_attr( $type->slug ); ?>">
						<?php echo esc_html( $type->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( $beverages->have_posts() ) : ?>
		<div class="beverage-list__grid">
			<?php while ( $beverages->have_posts() ) : $beverages->the_post(); ?>
				<?php
				$post_id    = get_the_ID();
				$abv        = get_post_meta( $post_id, '_beverage_abv', true );
				$ibu        = get_post_meta( $post_id, '_beverage_ibu', true );
				$price      = get_post_meta( $post_id, '_beverage_price', true );
				$notes      = get_post_meta( $post_id, '_beverage_tasting_notes', true );
				$type_slugs = beer_list_get_term_slugs( $post_id, 'beverage_type' );

				$detailed_fields = array();
				if ( $is_detailed ) {
					$detailed_fields = array(
						__( 'Type', 'beer-list' )         => beer_list_get_term_names( $post_id, 'beverage_type' ),
						__( 'Style', 'beer-list' )        => beer_list_get_term_names( $post_id, 'beverage_style' ),
						__( 'Availability', 'beer-list' ) => beer_list_get_term_names( $post_id, 'beverage_availability' ),
						__( 'Serving', 'beer-list' )      => beer_list_get_term_names( $post_id, 'serving_format' ),
					);
				}
				?>
				<div class="beverage-list__item" data-types="<?php echo esc_attr( $type_slugs ); ?>">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="beverage-list__image">
							<?php the_post_thumbnail( 'medium' ); ?>
						</div>
					<?php endif; ?>

					<div class="beverage-list__content">
						<h3 class="beverage-list__title"><?php the_title(); ?></h3>

						<?php if ( $is_detailed ) : ?>
							<?php
							// Render Type and Style fields before meta.
							foreach ( array( __( 'Type', 'beer-list' ), __( 'Style', 'beer-list' ) ) as $field_label ) :
								$field_names = $detailed_fields[ $field_label ] ?? array();
								if ( ! empty( $field_names ) ) :
									?>
									<div class="beverage-list__field">
										<span class="beverage-list__label"><?php echo esc_html( $field_label ); ?></span>
										<span><?php echo esc_html( implode( ', ', $field_names ) ); ?></span>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>

						<div class="beverage-list__meta">
							<?php if ( $abv ) : ?>
								<span class="beverage-list__abv"><?php echo esc_html( $abv ); ?>% ABV</span>
							<?php endif; ?>
							<?php if ( $ibu ) : ?>
								<span class="beverage-list__ibu"><?php echo esc_html( $ibu ); ?> IBU</span>
							<?php endif; ?>
							<?php if ( $price ) : ?>
								<span class="beverage-list__price"><?php echo esc_html( $price ); ?></span>
							<?php endif; ?>
						</div>

						<?php if ( $is_detailed ) : ?>
							<?php
							// Render Availability and Serving fields after meta.
							foreach ( array( __( 'Availability', 'beer-list' ), __( 'Serving', 'beer-list' ) ) as $field_label ) :
								$field_names = $detailed_fields[ $field_label ] ?? array();
								if ( ! empty( $field_names ) ) :
									?>
									<div class="beverage-list__field">
										<span class="beverage-list__label"><?php echo esc_html( $field_label ); ?></span>
										<span><?php echo esc_html( implode( ', ', $field_names ) ); ?></span>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php if ( $notes ) : ?>
							<p class="beverage-list__notes"><?php echo esc_html( $notes ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>

		<?php if ( $show_pagination ) : ?>
			<div class="beverage-list__pagination" data-per-page="<?php echo esc_attr( $items_per_page ); ?>"></div>
		<?php endif; ?>
	<?php else : ?>
		<p class="beverage-list__empty"><?php esc_html_e( 'No beverages found.', 'beer-list' ); ?></p>
	<?php endif; ?>
</div>
