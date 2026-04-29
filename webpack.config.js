// webpack.config.js — extends @wordpress/scripts default config
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
    ...defaultConfig,
    entry: {
        // Admin Settings SPA
        settings: path.resolve( __dirname, 'src/js/admin/settings/index.js' ),
        // Product Metabox
        'product-metabox': path.resolve( __dirname, 'src/js/admin/product-metabox/index.js' ),
        // FAQ Gutenberg Block
        'blocks/faq-block/index': path.resolve( __dirname, 'src/js/blocks/faq-block/index.js' ),
        // Frontend accordion
        frontend: path.resolve( __dirname, 'src/js/frontend/index.js' ),
    },
    output: {
        ...defaultConfig.output,
        path: path.resolve( __dirname, 'assets' ),
        filename: 'js/[name].js',
    },
};
