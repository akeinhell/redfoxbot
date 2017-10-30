const UglifyJSPlugin = require('uglifyjs-webpack-plugin');

module.exports = new UglifyJSPlugin({
    uglifyOptions: '',
    compress: {
        unused: true,
        warnings: true,
        drop_console: true,
    },
    warnings: true,
    mangle: false,
    warningsFilter: (src) => false
});