const path = require('path')
const webpack = require('webpack')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const TerserJSPlugin = require('terser-webpack-plugin')
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin')
const ManifestPlugin = require('webpack-manifest-plugin')

const devMode = !process.argv.includes('-p')

/** @type {import('webpack').Configuration} */
const config = {
  mode: devMode ? 'development' : 'production',
  entry: {
    app: ['react-hot-loader/patch', '@/index.tsx'],
    style: [
      '@/styles/admin-lte.scss',
      '@/styles/common.styl',
      '@/styles/common.scss',
    ],
    spectre: [
      'spectre.css/dist/spectre.min.css',
      '@/fonts/minecraft.css',
      '@/styles/spectre.css',
    ],
  },
  output: {
    path: `${__dirname}/public/app`,
    filename: devMode ? '[name].js' : '[name].[contenthash:7].js',
    chunkFilename: devMode ? '[id].js' : '[id].[contenthash:7].js',
  },
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        loader: 'ts-loader',
        options: {
          configFile: 'tsconfig.build.json',
          transpileOnly: true,
        },
      },
      {
        test: /\.scss$/,
        exclude: /\.module\.scss$/,
        use: [
          devMode ? 'style-loader' : MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              importLoaders: 2,
            },
          },
          'postcss-loader',
          'sass-loader',
        ],
      },
      {
        test: /\.module\.scss$/,
        use: [
          'style-loader',
          {
            loader: 'css-loader',
            options: {
              importLoaders: 2,
              modules: {
                localIdentName: devMode
                  ? '[name]__[local]'
                  : '[local]__[hash:base64:5]',
              },
              esModule: true,
            },
          },
          'postcss-loader',
          'sass-loader',
        ],
      },
      {
        test: /\.css$/,
        use: [
          devMode ? 'style-loader' : MiniCssExtractPlugin.loader,
          'css-loader',
        ],
      },
      {
        test: /(common|home)\.styl$/,
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
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: devMode ? '[name].css' : '[name].[contenthash:7].css',
      chunkFilename: devMode ? '[id].css' : '[id].[contenthash:7].css',
    }),
  ],
  resolve: {
    extensions: ['.js', '.ts', '.tsx', '.json'],
    alias: {
      'react-dom': '@hot-loader/react-dom',
      '@': path.resolve(__dirname, 'resources/assets/src'),
      'readline': '@/scripts/cli/readline.ts',
    },
  },
  externals: devMode
    ? {}
    : {
        react: 'React',
        'react-dom': 'ReactDOM',
        jquery: 'jQuery',
        bootstrap: 'bootstrap',
        'admin-lte': 'adminlte',
      },
  optimization: {
    minimizer: [new TerserJSPlugin({}), new OptimizeCSSAssetsPlugin({})],
  },
  devtool: devMode ? 'cheap-module-eval-source-map' : false,
  devServer: {
    headers: {
      'Access-Control-Allow-Origin': '*',
    },
    host: '0.0.0.0',
    hot: true,
    hotOnly: true,
    stats: 'errors-only',
  },
  stats: 'errors-only',
  node: {
    child_process: 'empty',
    fs: 'empty',
  },
}

if (devMode) {
  config.plugins.push(new webpack.NamedModulesPlugin())
  config.plugins.push(new webpack.HotModuleReplacementPlugin())
} else {
  config.plugins.push(new ManifestPlugin())
}

module.exports = config
