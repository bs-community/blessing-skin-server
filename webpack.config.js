/* eslint-disable import/no-extraneous-dependencies */
const webpack = require('webpack')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const WebpackBar = require('webpackbar')

const devMode = !process.argv.includes('-p')

/** @type {import('webpack').Configuration} */
const config = {
  mode: devMode ? 'development' : 'production',
  entry: {
    index: './resources/assets/src/index.js',
    style: [
      'bootstrap/dist/css/bootstrap.min.css',
      'admin-lte/dist/css/alt/AdminLTE-without-plugins.min.css',
      'element-ui/lib/theme-chalk/base.css',
      './resources/assets/src/element.scss',
      '@fortawesome/fontawesome-free/css/fontawesome.min.css',
      '@fortawesome/fontawesome-free/css/regular.min.css',
      '@fortawesome/fontawesome-free/css/solid.min.css',
      './resources/assets/src/stylus/common.styl',
    ],
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
        test: /\.(t|j)s$/,
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
        test: /((node_modules.*)|element)\.(sa|sc|c)ss$/,
        use: [
          devMode ? 'style-loader?hmr=true' : MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { importLoaders: 2 } },
          'postcss-loader',
          'sass-loader',
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
        test: /\.ya?ml$/,
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
        loader: devMode ? 'url-loader' : 'file-loader',
      },
      {
        test: require.resolve('jquery'),
        use: [
          {
            loader: 'expose-loader',
            options: 'jQuery',
          },
          {
            loader: 'expose-loader',
            options: '$',
          },
        ],
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
        from: 'node_modules/admin-lte/dist/css/skins/_all-skins.min.css',
        to: 'skins',
        flatten: true,
      },
      'resources/assets/src/images/bg.jpg',
      'resources/assets/src/images/favicon.ico',
    ]),
  ],
  resolve: {
    extensions: ['.js', '.ts', '.vue', '.json'],
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
  config.plugins.push(new WebpackBar())
}

module.exports = config
