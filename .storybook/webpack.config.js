const ExtractTextPlugin = require('extract-text-webpack-plugin');
const mainConfig = require('../webpack.config')[0];


module.exports = {
    context: mainConfig.context,
    resolve: mainConfig.resolve,
    plugins: [
        new ExtractTextPlugin(`css/[contenthash:8].css`),
    ],
    module: {
        rules: mainConfig.module.loaders
    },
};
