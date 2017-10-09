const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
var BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
var ManifestPlugin = require('webpack-manifest-plugin');

const CleanWebpackPlugin = require('clean-webpack-plugin');

const uglifyPlugin = require('./webpack/uglifyPlugin');

console.log(`process.env.NODE_ENV = ${process.env.NODE_ENV}`);
const isProduction = (process.env.NODE_ENV === 'production');

const mainConfig = {
    name: 'main',
    entry: {
        index: './resources/assets/js/src/index.js',
        styles: './resources/assets/sass/main.scss',
        vendor: [
            'react',
            'react-dom',
        ]
    },
    output: {
        path: __dirname + '/public/dist/',
        filename: 'js/[name].[chunkhash].js',
        sourceMapFilename: 'js/[name].[chunkhash].map',
        publicPath: '/dist/'
    },
    module: {
        loaders: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: [{
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            'es2015',
                            'stage-2',
                            'react',
                        ],
                        plugins: ['transform-es2015-destructuring', 'transform-object-rest-spread'],
                    }
                }]
            },
            {
                test: /\.(less$)$/,
                include: /resources\/assets\/js\/src/,
                use: [
                    'style-loader',
                    'css-loader?importLoaders=1&modules&localIdentName=[hash:base64:5]',
                    'less-loader'
                ]
            },
            {
                test: /\.(less$)$/,
                exclude: /resources\/assets\/js\/src/,
                use: [
                    'style-loader',
                    'css-loader',
                    'less-loader'
                ]
            },
            {
                test: /\.scss$/,
                use: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: 'css-loader!sass-loader',
                }),
            },
            {
                test: /\.css$/,
                use: ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: 'css-loader',
                }),
            },
            {
                test: /\.(jpe?g|png|gif)$/i,
                use: 'file-loader?name=images/[sha512:hash:base64].[ext]'
            },
            {
                test: /\.(eot|svg|ttf|woff|woff2)$/,
                loader: 'file-loader?name=/fonts/[sha512:hash:base64].[ext]'
            },
        ]
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    devtool: false,
    plugins: [
        new CleanWebpackPlugin(['public/dist/*'], {
            root: __dirname,
            verbose: true,
            // dry: false,
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: Infinity
        }),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'runtime'
        }),
        new webpack.HashedModuleIdsPlugin(),
        new webpack.DefinePlugin({DEBUG: JSON.stringify(JSON.parse(process.env.DEBUG || 'false'))}),
        new ExtractTextPlugin('css/[contenthash].css'),
        //new BundleAnalyzerPlugin(),
        new ManifestPlugin(),
        new webpack.ContextReplacementPlugin(
          /moment[\/\\]locale$/,
          /ru/
        )
    ]
};

if (isProduction) {
    console.log('webpack in production mode');
    mainConfig.plugins.push(uglifyPlugin);
    mainConfig.devtool = false;
}

module.exports = [
    mainConfig
];
