const VueLoaderPlugin = require('vue-loader/lib/plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const UglifyJsWebpackPlugin = require('uglifyjs-webpack-plugin');
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');
const WebpackBar = require('webpackbar');

const devMode = !process.argv.includes('-p');

module.exports = [{
    entry: {
        index: './resources/assets/src/index.js',
        polyfill: './resources/assets/src/js/polyfill.js',
        style: [
            'bootstrap/dist/css/bootstrap.min.css',
            'admin-lte/dist/css/AdminLTE.min.css',
            'bootstrap-fileinput/css/fileinput.min.css',
            '@fortawesome/fontawesome-free/css/fontawesome.min.css',
            '@fortawesome/fontawesome-free/css/regular.min.css',
            '@fortawesome/fontawesome-free/css/solid.min.css',
            'icheck/skins/square/blue.css',
            'toastr/build/toastr.min.css',
            'sweetalert2/dist/sweetalert2.min.css',
            './resources/assets/src/stylus/common.styl',
        ],
        home: './resources/assets/src/stylus/home.styl'
    },
    output: {
        path: __dirname + '/public',
        filename: '[name].js',
        chunkFilename: devMode
            ? '[id].js'
            : '[id].[chunkhash:7].js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: [
                    'cache-loader',
                    'babel-loader'
                ]
            },
            {
                test: /\.vue$/,
                use: [
                    'cache-loader',
                    'vue-loader'
                ]
            },
            {
                test: /\.(css|styl(us)?)$/,
                exclude: /node_modules/,
                use: [
                    'vue-style-loader',
                    { loader: 'css-loader', options: { importLoaders: 2 } },
                    'postcss-loader',
                    'stylus-loader'
                ]
            },
            {
                test: /node_modules.*\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    { loader: 'css-loader', options: { importLoaders: 1 } },
                    'csso-loader?-comments',
                ]
            },
            {
                test: /(common|home)\.styl$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    { loader: 'css-loader', options: { importLoaders: 3 } },
                    'csso-loader',
                    'postcss-loader',
                    'stylus-loader'
                ]
            },
            {
                test: /\.(png|jpg|gif)$/,
                loader: 'url-loader',
                options: {
                    limit: 8192
                }
            },
            {
                test: /\.(svg|woff2?|eot|ttf)$/,
                loader: 'file-loader'
            }
        ],
        noParse: /^(vue|jquery)$/
    },
    plugins: [
        new VueLoaderPlugin(),
        new WebpackBar(),
        new MiniCssExtractPlugin({
            filename: '[name].css',
            chunkFilename: '[id].css'
        }),
        new CopyWebpackPlugin([
            {
                from: 'node_modules/admin-lte/dist/css/skins/skin-*.min.css',
                to: 'skins',
                flatten: true
            },
            'node_modules/chart.js/dist/Chart.min.js',
        ]),
        new BundleAnalyzerPlugin({
            openAnalyzer: false,
            analyzerMode: devMode ? 'server' : 'static'
        }),
    ],
    optimization: {
        minimizer: [
            new UglifyJsWebpackPlugin({
                parallel: true,
              cache: true,
                sourceMap: false,
                extractComments: {
                    filename: 'LICENSES'
                },
                uglifyOptions: {
                    output: {
                        comments: /^\**!|@preserve|@license|@cc_on/
                    }
                }
          })
        ]
    },
    resolve: {
        extensions: ['.js', '.vue', '.json']
    },
    devtool: devMode ? 'cheap-module-eval-source-map' : false,
    stats: 'errors-only'
}, {
    entry: {
        en: './resources/lang/en/front-end.yml',
        zh_CN: './resources/lang/zh_CN/front-end.yml',
    },
    output: {
        path: __dirname + '/public/langs/',
        filename: '[name].js',
        library: '__bs_i18n__'
    },
    module: {
        rules: [
            {
                test: /\.yml$/,
                use: [
                    'json-loader',
                    'yaml-loader'
                ]
            },
        ]
    },
    stats: 'errors-only'
}];
