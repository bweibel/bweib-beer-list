import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, SelectControl, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { postsPerPage, beverageType, showFilters } = attributes;
	const blockProps = useBlockProps( { className: 'wp-block-beer-list-beverage-list' } );

	const { beverages, types, isLoading } = useSelect(
		( select ) => {
			const query = {
				per_page: postsPerPage,
				_embed: true,
				status: 'publish',
			};

			if ( beverageType ) {
				query.beverage_type = beverageType;
			}

			return {
				beverages: select( coreStore ).getEntityRecords( 'postType', 'beverage', query ) || [],
				types: select( coreStore ).getEntityRecords( 'taxonomy', 'beverage_type', { per_page: -1 } ) || [],
				isLoading: select( coreStore ).isResolving( 'getEntityRecords', [ 'postType', 'beverage', query ] ),
			};
		},
		[ postsPerPage, beverageType ]
	);

	const typeOptions = [
		{ label: __( 'All Types', 'beer-list' ), value: '' },
		...types.map( ( type ) => ( {
			label: type.name,
			value: String( type.id ),
		} ) ),
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'beer-list' ) }>
					<RangeControl
						label={ __( 'Beverages per page', 'beer-list' ) }
						value={ postsPerPage }
						onChange={ ( val ) => setAttributes( { postsPerPage: val } ) }
						min={ 1 }
						max={ 50 }
					/>
					<SelectControl
						label={ __( 'Filter by type', 'beer-list' ) }
						value={ beverageType }
						options={ typeOptions }
						onChange={ ( val ) => setAttributes( { beverageType: val } ) }
					/>
					<ToggleControl
						label={ __( 'Show filter bar', 'beer-list' ) }
						checked={ showFilters }
						onChange={ ( val ) => setAttributes( { showFilters: val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ isLoading && (
					<div className="beverage-list__loading">
						<Spinner />
					</div>
				) }

				{ ! isLoading && beverages.length === 0 && (
					<p className="beverage-list__empty">
						{ __( 'No beverages found. Add some from the Beverages menu.', 'beer-list' ) }
					</p>
				) }

				{ ! isLoading && beverages.length > 0 && (
					<div className="beverage-list__grid">
						{ beverages.map( ( beverage ) => (
							<div key={ beverage.id } className="beverage-list__item">
								{ beverage._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.source_url && (
									<div className="beverage-list__image">
										<img
											src={ beverage._embedded[ 'wp:featuredmedia' ][ 0 ].source_url }
											alt={ beverage.title.rendered }
										/>
									</div>
								) }
								<div className="beverage-list__content">
									<h3
										className="beverage-list__title"
										dangerouslySetInnerHTML={ { __html: beverage.title.rendered } }
									/>
									<div className="beverage-list__meta">
										{ beverage.meta?._beverage_abv > 0 && (
											<span className="beverage-list__abv">
												{ beverage.meta._beverage_abv }% ABV
											</span>
										) }
										{ beverage.meta?._beverage_ibu > 0 && (
											<span className="beverage-list__ibu">
												{ beverage.meta._beverage_ibu } IBU
											</span>
										) }
										{ beverage.meta?._beverage_price && (
											<span className="beverage-list__price">
												{ beverage.meta._beverage_price }
											</span>
										) }
									</div>
									{ beverage.meta?._beverage_tasting_notes && (
										<p className="beverage-list__notes">
											{ beverage.meta._beverage_tasting_notes }
										</p>
									) }
								</div>
							</div>
						) ) }
					</div>
				) }
			</div>
		</>
	);
}
