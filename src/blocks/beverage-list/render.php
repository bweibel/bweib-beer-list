<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts_per_page = $attributes['postsPerPage'] ?? 12;
$beverage_type  = $attributes['beverageType'] ?? '';
$show_filters   = $attributes['showFilters'] ?? true;
$display_mode   = $attributes['displayMode'] ?? 'simple';
$is_detailed    = 'detailed' === $display_mode;

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
				$abv   = get_post_meta( get_the_ID(), '_beverage_abv', true );
				$ibu   = get_post_meta( get_the_ID(), '_beverage_ibu', true );
				$price = get_post_meta( get_the_ID(), '_beverage_price', true );
				$notes = get_post_meta( get_the_ID(), '_beverage_tasting_notes', true );

				$type_terms = get_the_terms( get_the_ID(), 'beverage_type' );
				$type_slugs = $type_terms && ! is_wp_error( $type_terms )
					? implode( ' ', wp_list_pluck( $type_terms, 'slug' ) )
					: '';

				if ( $is_detailed ) {
					$type_names        = $type_terms && ! is_wp_error( $type_terms )
						? wp_list_pluck( $type_terms, 'name' )
						: array();
					$style_terms       = get_the_terms( get_the_ID(), 'beverage_style' );
					$style_names       = $style_terms && ! is_wp_error( $style_terms )
						? wp_list_pluck( $style_terms, 'name' )
						: array();
					$availability_terms = get_the_terms( get_the_ID(), 'beverage_availability' );
					$availability_names = $availability_terms && ! is_wp_error( $availability_terms )
						? wp_list_pluck( $availability_terms, 'name' )
						: array();
					$format_terms      = get_the_terms( get_the_ID(), 'serving_format' );
					$format_names      = $format_terms && ! is_wp_error( $format_terms )
						? wp_list_pluck( $format_terms, 'name' )
						: array();
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

						<?php if ( $is_detailed && ! empty( $type_names ) ) : ?>
							<div class="beverage-list__field">
								<span class="beverage-list__label"><?php esc_html_e( 'Type', 'beer-list' ); ?></span>
								<span><?php echo esc_html( implode( ', ', $type_names ) ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( $is_detailed && ! empty( $style_names ) ) : ?>
							<div class="beverage-list__field">
								<span class="beverage-list__label"><?php esc_html_e( 'Style', 'beer-list' ); ?></span>
								<span><?php echo esc_html( implode( ', ', $style_names ) ); ?></span>
							</div>
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

						<?php if ( $is_detailed && ! empty( $availability_names ) ) : ?>
							<div class="beverage-list__field">
								<span class="beverage-list__label"><?php esc_html_e( 'Availability', 'beer-list' ); ?></span>
								<span><?php echo esc_html( implode( ', ', $availability_names ) ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( $is_detailed && ! empty( $format_names ) ) : ?>
							<div class="beverage-list__field">
								<span class="beverage-list__label"><?php esc_html_e( 'Serving', 'beer-list' ); ?></span>
								<span><?php echo esc_html( implode( ', ', $format_names ) ); ?></span>
							</div>
						<?php endif; ?>

						<?php if ( $notes ) : ?>
							<p class="beverage-list__notes"><?php echo esc_html( $notes ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	<?php else : ?>
		<p class="beverage-list__empty"><?php esc_html_e( 'No beverages found.', 'beer-list' ); ?></p>
	<?php endif; ?>
</div>
