<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$beverage_id = $attributes['beverageId'] ?? 0;

// Fall back to the current post if no specific beverage is chosen and we are on a beverage.
if ( ! $beverage_id ) {
	if ( 'beverage' === get_post_type() ) {
		$beverage_id = get_the_ID();
	}
}

if ( ! $beverage_id ) {
	return;
}

$beverage = get_post( $beverage_id );

if ( ! $beverage || 'publish' !== $beverage->post_status ) {
	return;
}

$abv   = get_post_meta( $beverage_id, '_beverage_abv', true );
$ibu   = get_post_meta( $beverage_id, '_beverage_ibu', true );
$price = get_post_meta( $beverage_id, '_beverage_price', true );
$notes = get_post_meta( $beverage_id, '_beverage_tasting_notes', true );

$fields = array(
	__( 'Type', 'beer-list' )         => beer_list_get_term_names( $beverage_id, 'beverage_type' ),
	__( 'Style', 'beer-list' )        => beer_list_get_term_names( $beverage_id, 'beverage_style' ),
	__( 'Availability', 'beer-list' ) => beer_list_get_term_names( $beverage_id, 'beverage_availability' ),
	__( 'Serving', 'beer-list' )      => beer_list_get_term_names( $beverage_id, 'serving_format' ),
);
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wp-block-beer-list-beverage-detail' ) ); ?>>
	<div class="beverage-detail__card">
		<?php if ( has_post_thumbnail( $beverage_id ) ) : ?>
			<div class="beverage-detail__image">
				<?php echo get_the_post_thumbnail( $beverage_id, 'large' ); ?>
			</div>
		<?php endif; ?>

		<div class="beverage-detail__body">
			<h2 class="beverage-detail__title"><?php echo esc_html( get_the_title( $beverage_id ) ); ?></h2>

			<?php foreach ( $fields as $label => $names ) : ?>
				<?php if ( ! empty( $names ) ) : ?>
					<div class="beverage-detail__field">
						<span class="beverage-detail__label"><?php echo esc_html( $label ); ?></span>
						<span><?php echo esc_html( implode( ', ', $names ) ); ?></span>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>

			<div class="beverage-detail__meta">
				<?php if ( $abv ) : ?>
					<span class="beverage-detail__abv"><?php echo esc_html( $abv ); ?>% ABV</span>
				<?php endif; ?>
				<?php if ( $ibu ) : ?>
					<span class="beverage-detail__ibu"><?php echo esc_html( $ibu ); ?> IBU</span>
				<?php endif; ?>
				<?php if ( $price ) : ?>
					<span class="beverage-detail__price"><?php echo esc_html( $price ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( $notes ) : ?>
				<p class="beverage-detail__notes"><?php echo esc_html( $notes ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
