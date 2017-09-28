const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

const mainConfig = {
    name: 'main',
    entry: {
        index: './',
        styles: './resources/assets/sass/main.scss',
    },
    output: {
        path: __dirname + '/public/dist/',
        filename: "js/[name].js",
        sourceMapFilename: "js/[name].map",
        publicPath: '/dist/'
    },
    module: {
        loaders: [
            {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: 'css-loader!sass-loader',
                }),
            },
            {
                test: /\.(jpe?g|png|gif)$/i,
                use: 'file-loader?name=images/[name].[ext]'
            },

        ]
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    devtool: '#cheap-module-source-map',
    plugins: [
        new webpack.DefinePlugin({DEBUG: JSON.stringify(JSON.parse(process.env.DEBUG || 'false'))}),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: Infinity
        }),
        new ExtractTextPlugin('css/style.css')
    ]
};

module.exports = [
    mainConfig
];
