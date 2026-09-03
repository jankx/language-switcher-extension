/**
 * webpack.config.js - language-switcher extension
 *
 * Builds the Language Switcher Gutenberg block from
 * `assets/blocks/language-switcher` into `build/`, producing the files
 * referenced by block.json:
 *   - build/index.js        (editorScript)
 *   - build/frontend.js     (viewScript)
 *   - build/style.css       (style)
 *   - build/editor.css      (editorStyle)
 *
 * Build:  npm run build   (in this extension directory)
 *
 * NOTE: webpack must NOT clean the output directory because the block
 * sources (index.tsx, style.scss, ...) live alongside the build output.
 */

const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

const EXTENSION_DIR = __dirname;
const BLOCK_DIR = path.resolve(EXTENSION_DIR, 'assets/blocks/language-switcher');

const filteredPlugins = (defaultConfig.plugins || []).filter((plugin) => {
    const name = plugin.constructor?.name ?? '';
    return name !== 'CopyPlugin' && name !== 'CleanWebpackPlugin';
});

module.exports = {
    ...defaultConfig,
    context: BLOCK_DIR,

    entry: {
        'build/index': './index.tsx',
        'build/frontend': './frontend.ts',
        'build/style': './style.scss',
        'build/editor': './editor.scss',
    },

    output: {
        ...defaultConfig.output,
        path: BLOCK_DIR,
        filename: '[name].js',
        clean: false,
    },

    plugins: filteredPlugins,
};
