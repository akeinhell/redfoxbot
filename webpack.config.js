const path = require('path');
const glob = require('glob');
const webpack = require('webpack');
const ngAnnotatePlugin = require('ng-annotate-webpack-plugin');
const BowerWebpackPlugin = require('bower-webpack-plugin');
var CommonsChunkPlugin = require("webpack/lib/optimize/CommonsChunkPlugin");
var ExtractTextPlugin = require('extract-text-webpack-plugin');

const mainConfig = {
    name: 'main',
    debug: true,
    entry: {
        application: glob.sync('./resources/assets/coffee/**/*.coffee'),
        // react: glob.sync('./resources/assets/react-app/**/*.jsx'),
    },
    output: {
        path: path.join(__dirname, 'public/js/'),
        filename: "[name].bundle.js",
        chunkFilename: "[id].chunk.js"
    },
    module: {
        loaders: [
            { test: /\.coffee$/, loader: 'coffee-loader' },
            // {
            //     test: /\.jsx?$/,
            //     exclude: [/node_modules/],
            //     loader: "babel-loader",
            //     query: {
            //         presets: ['es2015', 'react', 'stage-0', 'stage-1']
            //     }
            // },
            // {
            //     test: /\.scss$/,
            //     loader: ExtractTextPlugin.extract('style-loader', 'css-loader!resolve-url!sass-loader?sourceMap')
            // },
            {
                test: /\.css$/,
                loader: ExtractTextPlugin.extract('style-loader', 'css-loader')
            },
            {
                test: /\.woff2?$|\.ttf$|\.eot$|\.svg$|\.png|\.jpe?g|\.gif$/,
                loader: 'file-loader'
            },
            {
                test: /\.less$/,
                loader: ExtractTextPlugin.extract("style-loader", "css-loader!less-loader")
            }
        ]
    },
    resolve: {
        extensions: ['', '.js', '.jsx', '.coffee']
    },
    devtool: '#cheap-module-source-map',
    plugins: [
        new ngAnnotatePlugin({
            add: true
        }),
        // Устанавливаем глобальную переменную в режиме разработки
        new webpack.DefinePlugin({
            DEBUG: JSON.stringify(JSON.parse(process.env.DEBUG || 'false'))
        }),
        new BowerWebpackPlugin({
            modulesDirectories: ['bower_components'],
            manifestFiles: ['bower.json', '.bower.json'],
            includes: /.*/,
            excludes: /.*\.less$/
        }),
        new CommonsChunkPlugin({
            filename: "commons.js",
            name: "commons"
        }),
        new ExtractTextPlugin('[name].css', {
            allChunks: true
        })
    ]
};

module.exports = [
    mainConfig
];
