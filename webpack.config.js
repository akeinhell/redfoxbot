const path = require('path');
const glob = require('glob');
const webpack = require('webpack');
const ngAnnotatePlugin = require('ng-annotate-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

const mainConfig = {
    name: 'main',
    entry: {
        application: glob.sync('./resources/assets/coffee/**/*.coffee'),
        styles: glob.sync('./resources/assets/js/application.js'),
    },
    output: {
        path: path.join(__dirname, 'public/dist'),
        filename: "js/[name].js",
        sourceMapFilename: "js/[name].map"
    },
    module: {
        loaders: [
            { test: /\.coffee$/, loader: 'coffee-loader' },
            {
                test: /\.less$/,
                loader: ExtractTextPlugin.extract({
                    fallbackLoader: 'style-loader',
                    loader: 'css-loader!less-loader',
                })
            },
            {
                test: /\.(eot|svg|ttf|woff|woff2)$/,
                loader: 'file-loader?name=../fonts/[name].[ext]&outputPath=../dist/fonts/'
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
