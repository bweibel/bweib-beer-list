<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_search      = $attributes['showSearch'] ?? true;
$show_type_filter = $attributes['showTypeFilter'] ?? true;
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'wp-block-beer-list-beverage-filter' ) ); ?>>
	<?php if ( $show_search ) : ?>
		<div class="beverage-filter__search">
			<input
				type="search"
				class="beverage-filter__search-input"
				placeholder="<?php esc_attr_e( 'Search beverages…', 'beer-list' ); ?>"
			/>
		</div>
	<?php endif; ?>

	<?php if ( $show_type_filter ) : ?>
		<?php
		$types = get_terms( array(
			'taxonomy'   => 'beverage_type',
			'hide_empty' => true,
		) );
		?>
		<?php if ( ! empty( $types ) && ! is_wp_error( $types ) ) : ?>
			<div class="beverage-filter__buttons">
				<button class="beverage-filter__btn is-active" data-type=""><?php esc_html_e( 'All', 'beer-list' ); ?></button>
				<?php foreach ( $types as $type ) : ?>
					<button class="beverage-filter__btn" data-type="<?php echo esc_attr( $type->slug ); ?>">
						<?php echo esc_html( $type->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
