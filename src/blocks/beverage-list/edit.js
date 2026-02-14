import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, SelectControl, Spinner, ButtonGroup, Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { postsPerPage, beverageType, showFilters, displayMode, showSearch, showPagination, itemsPerPage } = attributes;
	const isDetailed = displayMode === 'detailed';
	const blockProps = useBlockProps( {
		className: `wp-block-beer-list-beverage-list is-mode-${ displayMode }`,
	} );

	const { beverages, types, styles, availabilities, servingFormats, isLoading } = useSelect(
		( select ) => {
			const query = {
				per_page: postsPerPage,
				_embed: true,
				status: 'publish',
			};

			if ( beverageType ) {
				query.beverage_type = beverageType;
			}

			const data = {
				beverages: select( coreStore ).getEntityRecords( 'postType', 'beverage', query ) || [],
				types: select( coreStore ).getEntityRecords( 'taxonomy', 'beverage_type', { per_page: -1 } ) || [],
				isLoading: select( coreStore ).isResolving( 'getEntityRecords', [ 'postType', 'beverage', query ] ),
				styles: [],
				availabilities: [],
				servingFormats: [],
			};

			if ( isDetailed ) {
				data.styles = select( coreStore ).getEntityRecords( 'taxonomy', 'beverage_style', { per_page: -1 } ) || [];
				data.availabilities = select( coreStore ).getEntityRecords( 'taxonomy', 'beverage_availability', { per_page: -1 } ) || [];
				data.servingFormats = select( coreStore ).getEntityRecords( 'taxonomy', 'serving_format', { per_page: -1 } ) || [];
			}

			return data;
		},
		[ postsPerPage, beverageType, isDetailed ]
	);

	const typeOptions = [
		{ label: __( 'All Types', 'beer-list' ), value: '' },
		...types.map( ( type ) => ( {
			label: type.name,
			value: String( type.id ),
		} ) ),
	];

	const getTermNames = ( termIds, allTerms ) => {
		if ( ! termIds || ! allTerms.length ) {
			return [];
		}
		return termIds
			.map( ( id ) => allTerms.find( ( t ) => t.id === id ) )
			.filter( Boolean )
			.map( ( t ) => t.name );
	};

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
					<ToggleControl
						label={ __( 'Show search bar', 'beer-list' ) }
						checked={ showSearch }
						onChange={ ( val ) => setAttributes( { showSearch: val } ) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Display Mode', 'beer-list' ) }>
					<ButtonGroup>
						<Button
							variant={ displayMode === 'simple' ? 'primary' : 'secondary' }
							onClick={ () => setAttributes( { displayMode: 'simple' } ) }
						>
							{ __( 'Simple', 'beer-list' ) }
						</Button>
						<Button
							variant={ displayMode === 'detailed' ? 'primary' : 'secondary' }
							onClick={ () => setAttributes( { displayMode: 'detailed' } ) }
						>
							{ __( 'Detailed', 'beer-list' ) }
						</Button>
					</ButtonGroup>
					<p className="components-base-control__help" style={ { marginTop: '8px' } }>
						{ displayMode === 'simple'
							? __( 'Shows name, ABV, IBU, and price.', 'beer-list' )
							: __( 'Shows all fields including style, availability, serving format, and tasting notes.', 'beer-list' )
						}
					</p>
				</PanelBody>
				<PanelBody title={ __( 'Pagination', 'beer-list' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Enable pagination', 'beer-list' ) }
						checked={ showPagination }
						onChange={ ( val ) => setAttributes( { showPagination: val } ) }
					/>
					{ showPagination && (
						<RangeControl
							label={ __( 'Items per page', 'beer-list' ) }
							value={ itemsPerPage }
							onChange={ ( val ) => setAttributes( { itemsPerPage: val } ) }
							min={ 1 }
							max={ 50 }
						/>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ showSearch && (
					<div className="beverage-list__search">
						<input
							type="search"
							className="beverage-list__search-input"
							placeholder={ __( 'Search beverages\u2026', 'beer-list' ) }
							disabled
						/>
					</div>
				) }

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
						{ beverages.slice( 0, showPagination ? itemsPerPage : undefined ).map( ( beverage ) => {
							const typeNames = getTermNames( beverage.beverage_type, types );
							const styleNames = isDetailed ? getTermNames( beverage.beverage_style, styles ) : [];
							const availabilityNames = isDetailed ? getTermNames( beverage.beverage_availability, availabilities ) : [];
							const formatNames = isDetailed ? getTermNames( beverage.serving_format, servingFormats ) : [];

							return (
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

										{ isDetailed && typeNames.length > 0 && (
											<div className="beverage-list__field">
												<span className="beverage-list__label">{ __( 'Type', 'beer-list' ) }</span>
												<span>{ typeNames.join( ', ' ) }</span>
											</div>
										) }

										{ isDetailed && styleNames.length > 0 && (
											<div className="beverage-list__field">
												<span className="beverage-list__label">{ __( 'Style', 'beer-list' ) }</span>
												<span>{ styleNames.join( ', ' ) }</span>
											</div>
										) }

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

										{ isDetailed && availabilityNames.length > 0 && (
											<div className="beverage-list__field">
												<span className="beverage-list__label">{ __( 'Availability', 'beer-list' ) }</span>
												<span>{ availabilityNames.join( ', ' ) }</span>
											</div>
										) }

										{ isDetailed && formatNames.length > 0 && (
											<div className="beverage-list__field">
												<span className="beverage-list__label">{ __( 'Serving', 'beer-list' ) }</span>
												<span>{ formatNames.join( ', ' ) }</span>
											</div>
										) }

										{ beverage.meta?._beverage_tasting_notes && (
											<p className="beverage-list__notes">
												{ beverage.meta._beverage_tasting_notes }
											</p>
										) }
									</div>
								</div>
							);
						} ) }
					</div>
				) }

				{ showPagination && ! isLoading && beverages.length > itemsPerPage && (
					<div className="beverage-list__pagination">
						<span className="beverage-list__page-btn is-disabled">&laquo;</span>
						<span className="beverage-list__page-btn is-active">1</span>
						<span className="beverage-list__page-btn">2</span>
						<span className="beverage-list__page-btn">&raquo;</span>
					</div>
				) }
			</div>
		</>
	);
}
