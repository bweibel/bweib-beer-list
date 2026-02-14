const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		'blocks/beverage-list/index': path.resolve( __dirname, 'src/blocks/beverage-list/index.js' ),
		'blocks/beverage-list/view': path.resolve( __dirname, 'src/blocks/beverage-list/view.js' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'build' ),
	},
};
