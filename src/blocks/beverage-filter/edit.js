import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

export default function Edit( { attributes, setAttributes } ) {
	const { showSearch, showTypeFilter } = attributes;
	const blockProps = useBlockProps( {
		className: 'wp-block-beer-list-beverage-filter',
	} );

	const { types, isLoading } = useSelect( ( select ) => {
		const { getEntityRecords, isResolving } = select( coreStore );
		const query = { per_page: -1, hide_empty: true };

		return {
			types: getEntityRecords( 'taxonomy', 'beverage_type', query ) || [],
			isLoading: isResolving( 'getEntityRecords', [ 'taxonomy', 'beverage_type', query ] ),
		};
	}, [] );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'beer-list' ) }>
					<ToggleControl
						label={ __( 'Show search bar', 'beer-list' ) }
						checked={ showSearch }
						onChange={ ( val ) => setAttributes( { showSearch: val } ) }
					/>
					<ToggleControl
						label={ __( 'Show type filter buttons', 'beer-list' ) }
						checked={ showTypeFilter }
						onChange={ ( val ) => setAttributes( { showTypeFilter: val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ showSearch && (
					<div className="beverage-filter__search">
						<input
							type="search"
							className="beverage-filter__search-input"
							placeholder={ __( 'Search beverages\u2026', 'beer-list' ) }
							disabled
						/>
					</div>
				) }

				{ isLoading && (
					<div className="beverage-filter__loading">
						<Spinner />
					</div>
				) }

				{ showTypeFilter && ! isLoading && types.length > 0 && (
					<div className="beverage-filter__buttons">
						<button className="beverage-filter__btn is-active" disabled>
							{ __( 'All', 'beer-list' ) }
						</button>
						{ types.map( ( type ) => (
							<button key={ type.id } className="beverage-filter__btn" disabled>
								{ type.name }
							</button>
						) ) }
					</div>
				) }

				{ ! showSearch && ! showTypeFilter && (
					<p className="beverage-filter__empty">
						{ __( 'Enable at least one filter option in the block settings.', 'beer-list' ) }
					</p>
				) }
			</div>
		</>
	);
}
