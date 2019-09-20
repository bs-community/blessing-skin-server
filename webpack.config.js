/* eslint-disable import/no-extraneous-dependencies */
const fs = require('fs')
const webpack = require('webpack')
const VueLoaderPlugin = require('vue-loader/lib/plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const CopyWebpackPlugin = require('copy-webpack-plugin')
const WebpackBar = require('webpackbar')
const ManifestPlugin = require('webpack-manifest-plugin')

const devMode = !process.argv.includes('-p')

/** @type {import('webpack').Configuration} */
const config = {
  mode: devMode ? 'development' : 'production',
  entry: {
    index: './resources/assets/src/index.ts',
    'check-updates': './resources/assets/src/scripts/check-updates.ts',
    'language-chooser': './resources/assets/src/scripts/language-chooser.ts',
    style: [
      'bootstrap/dist/css/bootstrap.min.css',
      'admin-lte/dist/css/alt/AdminLTE-without-plugins.min.css',
      'element-ui/lib/theme-chalk/base.css',
      './resources/assets/src/styles/element.scss',
      '@fortawesome/fontawesome-free/css/fontawesome.min.css',
      '@fortawesome/fontawesome-free/css/regular.min.css',
      '@fortawesome/fontawesome-free/css/solid.min.css',
      './resources/assets/src/styles/common.styl',
    ],
    setup: './resources/assets/src/styles/setup.styl',
  },
  output: {
    path: `${__dirname}/public/app`,
    filename: devMode ? '[name].js' : '[name].[contenthash:7].js',
    chunkFilename: devMode ? '[id].js' : '[id].[contenthash:7].js',
  },
  module: {
    rules: [
      {
        test: /\.(t|j)s$/,
        exclude: /node_modules/,
        use: [
          'cache-loader',
          {
            loader: 'babel-loader',
            options: {
              plugins: [
                ['babel-plugin-import', {
                  libraryName: 'element-ui',
                  libraryDirectory: 'lib',
                  camel2DashComponentName: true,
                  style: name => {
                    if (name.includes('locale')) {
                      return false
                    }
                    return `${name.replace('lib/', 'lib/theme-chalk/')}.css`
                  },
                }],
              ],
            },
          },
        ],
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
          { loader: 'css-loader', options: { importLoaders: 2 } },
          'postcss-loader',
          'stylus-loader',
        ],
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
      filename: devMode ? '[name].css' : '[name].[contenthash:7].css',
      chunkFilename: devMode ? '[id].css' : '[id].[contenthash:7].css',
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
    hotOnly: true,
    public: getDevServerUrl(),
    stats: 'errors-only',
  },
  stats: 'errors-only',
}

if (devMode) {
  config.plugins.push(new webpack.NamedModulesPlugin())
  config.plugins.push(new webpack.HotModuleReplacementPlugin())
} else {
  config.plugins.push(new WebpackBar())
  config.plugins.push(new ManifestPlugin())
}

module.exports = config

function getDevServerUrl() {
  const matches = /ASSET_URL=(.*)/.exec(fs.readFileSync('.env', 'utf8'))
  if (!matches) {
    return
  }

  const url = new URL(matches[1])
  return `${url.host}:8080`
}
