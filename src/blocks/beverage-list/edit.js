import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, SelectControl, Spinner, ButtonGroup, Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

function getTermNames( termIds, allTerms ) {
	if ( ! termIds || ! allTerms.length ) {
		return [];
	}
	return termIds
		.map( ( id ) => allTerms.find( ( t ) => t.id === id ) )
		.filter( Boolean )
		.map( ( t ) => t.name );
}

function DetailedField( { label, names } ) {
	if ( ! names.length ) {
		return null;
	}
	return (
		<div className="beverage-list__field">
			<span className="beverage-list__label">{ label }</span>
			<span>{ names.join( ', ' ) }</span>
		</div>
	);
}

function BeverageCard( { beverage, isDetailed, taxonomies } ) {
	const featuredImage = beverage._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.source_url;
	const meta = beverage.meta || {};

	const typeNames = getTermNames( beverage.beverage_type, taxonomies.types );
	const styleNames = isDetailed ? getTermNames( beverage.beverage_style, taxonomies.styles ) : [];
	const availabilityNames = isDetailed ? getTermNames( beverage.beverage_availability, taxonomies.availabilities ) : [];
	const formatNames = isDetailed ? getTermNames( beverage.serving_format, taxonomies.servingFormats ) : [];

	return (
		<div className="beverage-list__item">
			{ featuredImage && (
				<div className="beverage-list__image">
					<img src={ featuredImage } alt={ beverage.title.rendered } />
				</div>
			) }
			<div className="beverage-list__content">
				<h3
					className="beverage-list__title"
					dangerouslySetInnerHTML={ { __html: beverage.title.rendered } }
				/>

				{ isDetailed && <DetailedField label={ __( 'Type', 'beer-list' ) } names={ typeNames } /> }
				{ isDetailed && <DetailedField label={ __( 'Style', 'beer-list' ) } names={ styleNames } /> }

				<div className="beverage-list__meta">
					{ meta._beverage_abv > 0 && (
						<span className="beverage-list__abv">{ meta._beverage_abv }% ABV</span>
					) }
					{ meta._beverage_ibu > 0 && (
						<span className="beverage-list__ibu">{ meta._beverage_ibu } IBU</span>
					) }
					{ meta._beverage_price && (
						<span className="beverage-list__price">{ meta._beverage_price }</span>
					) }
				</div>

				{ isDetailed && <DetailedField label={ __( 'Availability', 'beer-list' ) } names={ availabilityNames } /> }
				{ isDetailed && <DetailedField label={ __( 'Serving', 'beer-list' ) } names={ formatNames } /> }

				{ meta._beverage_tasting_notes && (
					<p className="beverage-list__notes">{ meta._beverage_tasting_notes }</p>
				) }
			</div>
		</div>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const { postsPerPage, beverageType, showFilters, displayMode, showSearch, showPagination, itemsPerPage } = attributes;
	const isDetailed = displayMode === 'detailed';
	const blockProps = useBlockProps( {
		className: `wp-block-beer-list-beverage-list is-mode-${ displayMode }`,
	} );

	const { beverages, types, styles, availabilities, servingFormats, isLoading } = useSelect(
		( select ) => {
			const { getEntityRecords, isResolving } = select( coreStore );
			const allTermsQuery = { per_page: -1 };
			const query = {
				per_page: postsPerPage,
				_embed: true,
				status: 'publish',
			};

			if ( beverageType ) {
				query.beverage_type = beverageType;
			}

			return {
				beverages: getEntityRecords( 'postType', 'beverage', query ) || [],
				types: getEntityRecords( 'taxonomy', 'beverage_type', allTermsQuery ) || [],
				styles: isDetailed ? getEntityRecords( 'taxonomy', 'beverage_style', allTermsQuery ) || [] : [],
				availabilities: isDetailed ? getEntityRecords( 'taxonomy', 'beverage_availability', allTermsQuery ) || [] : [],
				servingFormats: isDetailed ? getEntityRecords( 'taxonomy', 'serving_format', allTermsQuery ) || [] : [],
				isLoading: isResolving( 'getEntityRecords', [ 'postType', 'beverage', query ] ),
			};
		},
		[ postsPerPage, beverageType, isDetailed ]
	);

	const taxonomies = { types, styles, availabilities, servingFormats };

	const typeOptions = [
		{ label: __( 'All Types', 'beer-list' ), value: '' },
		...types.map( ( type ) => ( {
			label: type.name,
			value: String( type.id ),
		} ) ),
	];

	const displayedBeverages = showPagination ? beverages.slice( 0, itemsPerPage ) : beverages;

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
						{ displayedBeverages.map( ( beverage ) => (
							<BeverageCard
								key={ beverage.id }
								beverage={ beverage }
								isDetailed={ isDetailed }
								taxonomies={ taxonomies }
							/>
						) ) }
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
