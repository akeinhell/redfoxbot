const path = require('path');
const glob = require('glob');
const webpack = require('webpack');
const ngAnnotatePlugin = require('ng-annotate-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

const mainConfig = {
    name: 'main',
    entry: {
        application: glob.sync('./resources/assets/coffee/**/*.coffee'),
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
            {test: /\.coffee$/, use: 'coffee-loader'},
            {
                test: /\.(eot|svg|ttf|woff|woff2)$/,
                use: 'file-loader?name=../fonts/[name].[ext]&outputPath=../dist/fonts/'
            },
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
        extensions: ['.js', '.coffee']
    },
    devtool: '#cheap-module-source-map',
    plugins: [
        new ngAnnotatePlugin({add: true}),
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
