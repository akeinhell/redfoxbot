let path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const UglifyJSPlugin      = require('uglifyjs-webpack-plugin');
let CleanWebpackPlugin = require('clean-webpack-plugin');

const uglifyPlugin = new UglifyJSPlugin({
    // beautify: true,
    // comments: true,
    compress: {
        // sequences: isProduction,
        // booleans: isProduction,
        // loops: isProduction,
        unused: true,
        warnings: true,
        drop_console: true,
        //     unsafe: false
    },
    warnings: true,
    mangle: false,
});

const mainConfig = {
    name: 'main',
    entry: {
        index: './resources/assets/js/src/index.js',
        styles: './resources/assets/sass/main.scss',
        vendor: [
            'react',
            'react-dom',
            // 'grommet',
        ]
    },
    output: {
        path: __dirname + '/public/dist/',
        // filename: 'js/[name]_[chunkhash:4].js',
        filename: 'js/[name].js',
        sourceMapFilename: 'js/[name].[chunkhash:4].map',
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
                        plugins: ["transform-es2015-destructuring", "transform-object-rest-spread"],
                    }
                }]
            },
            {
                test: /\.(less$)$/,
                use: [
                    'style-loader',
                    'css-loader?importLoaders=1&modules&localIdentName=[hash:base64:5]',
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
            // {
            //     test: /\.less$/,
            //     use: ExtractTextPlugin.extract({
            //         fallback: 'style-loader',
            //         use: 'css-loader!less-loader'
            //     })
            // },
            {
                test: /\.(jpe?g|png|gif)$/i,
                use: 'file-loader?name=images/[name].[ext]'
            },
            {
                test: /\.(eot|svg|ttf|woff|woff2)$/,
                loader: 'file-loader?name=/fonts/[name].[ext]'
            },
        ]
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    devtool: '#cheap-module-source-map',
    plugins: [
        function () {
            this.plugin('done', function (stats) {
                let data = stats.toJson().assetsByChunkName;

                let out = Object.keys(data).reduce((prev, current) => {
                    let files = Array.isArray(data[current]) ? data[current] : [data[current]];
                    prev[current] = files.reduce((res, current) => {
                        let ext = path.extname(current).slice(1);
                        res[ext] = '/' + ext + '/' + current.replace(`/${ext}`, '').replace('../', '');
                        return res;
                    }, {});


                    return prev;
                }, {});
                require('fs').writeFileSync(
                  path.join(__dirname, 'stats.json'),
                  JSON.stringify(out));
            });
        },
        new CleanWebpackPlugin(['public/dist/*'], {
            root: __dirname,
            verbose: true,
            // dry: false,
        }),
        // extractVersions.bind(this),
        new webpack.optimize.CommonsChunkPlugin({
            names: [
                'vendor',
            ],
            async: true,
            minChunks: Infinity
        }),
        new webpack.DefinePlugin({DEBUG: JSON.stringify(JSON.parse(process.env.DEBUG || 'false'))}),
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: Infinity
        }),
        new ExtractTextPlugin('css/style.css'),
        //uglifyPlugin
    ]
};

module.exports = [
    mainConfig
];
