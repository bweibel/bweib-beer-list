import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import Edit from './edit';

import './style.css';
import './editor.css';

registerBlockType( metadata.name, {
	edit: Edit,
	variations: [
		{
			name: 'simple',
			title: __( 'Beverage List — Simple', 'beer-list' ),
			description: __( 'Shows beverages with basic info: name, ABV, IBU, and price.', 'beer-list' ),
			attributes: { displayMode: 'simple' },
			scope: [ 'inserter', 'transform' ],
			isDefault: true,
			icon: 'grid-view',
		},
		{
			name: 'detailed',
			title: __( 'Beverage List — Detailed', 'beer-list' ),
			description: __( 'Shows beverages with all details: style, availability, serving format, tasting notes, and more.', 'beer-list' ),
			attributes: { displayMode: 'detailed' },
			scope: [ 'inserter', 'transform' ],
			icon: 'list-view',
		},
	],
} );
