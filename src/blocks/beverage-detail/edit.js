import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ComboboxControl, Button, Spinner, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { useState, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

function LabeledField( { label, value } ) {
	if ( ! value ) {
		return null;
	}
	return (
		<div className="beverage-detail__field">
			<span className="beverage-detail__label">{ label }</span>
			<span>{ value }</span>
		</div>
	);
}

function BeveragePreview( { beverage, taxonomies } ) {
	const featuredImage = beverage._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.source_url;
	const meta = beverage.meta || {};

	function termNames( ids, terms ) {
		if ( ! ids || ! terms?.length ) {
			return '';
		}
		return ids
			.map( ( id ) => terms.find( ( t ) => t.id === id ) )
			.filter( Boolean )
			.map( ( t ) => t.name )
			.join( ', ' );
	}

	return (
		<div className="beverage-detail__card">
			{ featuredImage && (
				<div className="beverage-detail__image">
					<img src={ featuredImage } alt={ beverage.title.rendered } />
				</div>
			) }
			<div className="beverage-detail__body">
				<h2
					className="beverage-detail__title"
					dangerouslySetInnerHTML={ { __html: beverage.title.rendered } }
				/>

				<LabeledField label={ __( 'Type', 'beer-list' ) } value={ termNames( beverage.beverage_type, taxonomies.types ) } />
				<LabeledField label={ __( 'Style', 'beer-list' ) } value={ termNames( beverage.beverage_style, taxonomies.styles ) } />

				<div className="beverage-detail__meta">
					{ meta._beverage_abv > 0 && (
						<span className="beverage-detail__abv">{ meta._beverage_abv }% ABV</span>
					) }
					{ meta._beverage_ibu > 0 && (
						<span className="beverage-detail__ibu">{ meta._beverage_ibu } IBU</span>
					) }
					{ meta._beverage_price && (
						<span className="beverage-detail__price">{ meta._beverage_price }</span>
					) }
				</div>

				<LabeledField label={ __( 'Availability', 'beer-list' ) } value={ termNames( beverage.beverage_availability, taxonomies.availabilities ) } />
				<LabeledField label={ __( 'Serving', 'beer-list' ) } value={ termNames( beverage.serving_format, taxonomies.servingFormats ) } />

				{ meta._beverage_tasting_notes && (
					<p className="beverage-detail__notes">{ meta._beverage_tasting_notes }</p>
				) }
			</div>
		</div>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const { beverageId } = attributes;
	const blockProps = useBlockProps( { className: 'wp-block-beer-list-beverage-detail' } );
	const [ searchInput, setSearchInput ] = useState( '' );

	const allTermsQuery = { per_page: -1 };

	// Fetch search results for the picker.
	const { searchResults } = useSelect(
		( select ) => {
			if ( ! searchInput || searchInput.length < 2 ) {
				return { searchResults: [] };
			}
			return {
				searchResults: select( coreStore ).getEntityRecords( 'postType', 'beverage', {
					search: searchInput,
					per_page: 10,
					status: 'publish',
				} ) || [],
			};
		},
		[ searchInput ]
	);

	// Fetch the selected beverage + taxonomy terms.
	const { beverage, taxonomies, isLoading } = useSelect(
		( select ) => {
			const { getEntityRecord, getEntityRecords, isResolving } = select( coreStore );

			if ( ! beverageId ) {
				return { beverage: null, taxonomies: {}, isLoading: false };
			}

			const record = getEntityRecord( 'postType', 'beverage', beverageId, { _embed: true } );

			return {
				beverage: record || null,
				taxonomies: {
					types: getEntityRecords( 'taxonomy', 'beverage_type', allTermsQuery ) || [],
					styles: getEntityRecords( 'taxonomy', 'beverage_style', allTermsQuery ) || [],
					availabilities: getEntityRecords( 'taxonomy', 'beverage_availability', allTermsQuery ) || [],
					servingFormats: getEntityRecords( 'taxonomy', 'serving_format', allTermsQuery ) || [],
				},
				isLoading: isResolving( 'getEntityRecord', [ 'postType', 'beverage', beverageId, { _embed: true } ] ),
			};
		},
		[ beverageId ]
	);

	const pickerOptions = useMemo( () => {
		return searchResults.map( ( post ) => ( {
			label: post.title.rendered,
			value: post.id,
		} ) );
	}, [ searchResults ] );

	// Also include the currently-selected beverage in the options so the
	// ComboboxControl can display its label when nothing is being searched.
	const selectedOption = beverage
		? [ { label: beverage.title.rendered, value: beverage.id } ]
		: [];

	const displayOptions = pickerOptions.length > 0 ? pickerOptions : selectedOption;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Beverage', 'beer-list' ) }>
					<ComboboxControl
						label={ __( 'Select a beverage', 'beer-list' ) }
						value={ beverageId || null }
						options={ displayOptions }
						onFilterValueChange={ setSearchInput }
						onChange={ ( val ) => setAttributes( { beverageId: val || 0 } ) }
						help={ __( 'Search by name. Leave empty to use the current post.', 'beer-list' ) }
					/>
					{ beverageId > 0 && (
						<Button
							variant="link"
							isDestructive
							onClick={ () => setAttributes( { beverageId: 0 } ) }
						>
							{ __( 'Clear selection (use current post)', 'beer-list' ) }
						</Button>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ isLoading && (
					<div className="beverage-detail__loading">
						<Spinner />
					</div>
				) }

				{ ! isLoading && ! beverageId && (
					<Placeholder
						icon="beer"
						label={ __( 'Beverage Detail', 'beer-list' ) }
						instructions={ __( 'This block will display the current beverage post. Use the sidebar to pick a specific beverage instead.', 'beer-list' ) }
					/>
				) }

				{ ! isLoading && beverageId > 0 && ! beverage && (
					<Placeholder
						icon="beer"
						label={ __( 'Beverage Detail', 'beer-list' ) }
						instructions={ __( 'The selected beverage could not be found.', 'beer-list' ) }
					/>
				) }

				{ ! isLoading && beverage && (
					<BeveragePreview beverage={ beverage } taxonomies={ taxonomies } />
				) }
			</div>
		</>
	);
}
