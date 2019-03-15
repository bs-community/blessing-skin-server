/* eslint-disable import/no-extraneous-dependencies */
const webpack = require('webpack')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const WebpackBar = require('webpackbar')

const devMode = !process.argv.includes('--mode=production')

/** @type {import('webpack').Configuration} */
const config = {
  mode: devMode ? 'development' : 'production',
  entry: {
    index: './resources/assets/src/index.js',
    style: [
      'bootstrap/dist/css/bootstrap.min.css',
      'admin-lte/dist/css/AdminLTE.min.css',
      '@fortawesome/fontawesome-free/css/fontawesome.min.css',
      '@fortawesome/fontawesome-free/css/regular.min.css',
      '@fortawesome/fontawesome-free/css/solid.min.css',
      'icheck/skins/square/blue.css',
      'toastr/build/toastr.min.css',
      'sweetalert2/dist/sweetalert2.min.css',
      './resources/assets/src/stylus/common.styl',
    ],
    home: './resources/assets/src/stylus/home.styl',
    setup: './resources/assets/src/stylus/setup.styl',
    'langs/en': './resources/lang/en/front-end.js',
    'langs/zh_CN': './resources/lang/zh_CN/front-end.js',
  },
  output: {
    path: `${__dirname}/public/app`,
    filename: '[name].js',
    chunkFilename: devMode ? '[id].js' : '[id].[contenthash:7].js',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: ['cache-loader', 'babel-loader'],
      },
      {
        test: /\.vue$/,
        use: ['cache-loader', 'vue-loader'],
      },
      {
        test: /\.(css|styl(us)?)$/,
        exclude: /node_modules/,
        use: [
          'vue-style-loader',
          { loader: 'css-loader', options: { importLoaders: 2 } },
          'postcss-loader',
          'stylus-loader',
        ],
      },
      {
        test: /node_modules.*\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { importLoaders: 1 } },
          'csso-loader?-comments',
        ],
      },
      {
        test: /(common|home|setup)\.styl$/,
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { importLoaders: 3 } },
          'csso-loader',
          'postcss-loader',
          'stylus-loader',
        ],
      },
      {
        test: /\.yml$/,
        use: ['json-loader', 'yaml-loader'],
      },
      {
        test: /\.(png|jpg|gif)$/,
        loader: 'url-loader',
        options: {
          limit: 8192,
        },
      },
      {
        test: /\.(svg|woff2?|eot|ttf)$/,
        loader: 'file-loader',
      },
    ],
    noParse: /^(vue|jquery)$/,
  },
  plugins: [
    new VueLoaderPlugin(),
    new MiniCssExtractPlugin({
      filename: '[name].css',
      chunkFilename: '[id].css',
    }),
    new CopyWebpackPlugin([
      {
        from: 'node_modules/admin-lte/dist/css/skins/skin-*.min.css',
        to: 'skins',
        flatten: true,
      },
      {
        from:
                    'node_modules/admin-lte/dist/css/skins/_all-skins.min.css',
        to: 'skins',
        flatten: true,
      },
      'node_modules/chart.js/dist/Chart.min.js',
      'resources/assets/src/images/bg.jpg',
      'resources/assets/src/images/favicon.ico',
    ]),
  ],
  resolve: {
    extensions: ['.js', '.vue', '.json'],
  },
  devtool: devMode ? 'cheap-module-eval-source-map' : false,
  devServer: {
    headers: {
      'Access-Control-Allow-Origin': '*',
    },
    host: '0.0.0.0',
    hot: true,
    publicPath: '/public/',
    stats: 'errors-only',
  },
  stats: 'errors-only',
}

if (devMode) {
  config.plugins.push(new webpack.HotModuleReplacementPlugin())
} else {
  process.env.NODE_ENV = 'production'
  config.plugins.push(new WebpackBar())
  config.plugins.push(
    new webpack.DefinePlugin({
      'process.env.NODE_ENV': JSON.stringify('production'),
    })
  )
}

module.exports = config
