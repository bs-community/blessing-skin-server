import * as path from 'path'
import * as webpack from 'webpack'
import MiniCssExtractPlugin from 'mini-css-extract-plugin'
import TerserJSPlugin from 'terser-webpack-plugin'
import HtmlWebpackPlugin from 'html-webpack-plugin'
import HtmlWebpackEnhancementPlugin from './tools/HtmlWebpackEnhancementPlugin'

const devMode = !process.argv.includes('-p')

const htmlPublicPath = devMode ? '//localhost:8080/' : '{{ cdn_base }}/app/'

const config: webpack.Configuration = {
  mode: devMode ? 'development' : 'production',
  entry: {
    app: ['react-hot-loader/patch', '@/index.tsx'],
    style: ['@/styles/common.css'],
    home: '@/scripts/homePage.ts',
    'home-css': '@/styles/home.css',
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
        test: /\.css$/,
        use: [
          devMode ? 'style-loader' : MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              importLoaders: 1,
            },
          },
          'postcss-loader',
        ],
      },
      {
        test: /\.(png|webp)$/,
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
    new HtmlWebpackPlugin({
      templateContent: devMode
        ? ''
        : `
<script src="https://cdn.jsdelivr.net/npm/react@17.0.1/umd/react.production.min.js" integrity="sha256-Ag0WTc8xFszCJo1qbkTKp3wBMdjpjogsZDAhnSge744=" crossorigin></script>
<script src="https://cdn.jsdelivr.net/npm/react-dom@17.0.1/umd/react-dom.production.min.js" integrity="sha256-k8tzaSH8ucPwbsHEO4Wk5szE9zERNVz3XQynfyT66O0=" crossorigin></script>`,
      templateParameters: ['app'],
      filename: 'app.twig',
      publicPath: htmlPublicPath,
    }),
    new HtmlWebpackPlugin({
      templateContent: '',
      templateParameters: ['style'],
      filename: 'style.twig',
      publicPath: htmlPublicPath,
    }),
    new HtmlWebpackPlugin({
      templateContent: '',
      templateParameters: ['home'],
      filename: 'home.twig',
      publicPath: htmlPublicPath,
    }),
    new HtmlWebpackPlugin({
      templateContent: '',
      templateParameters: ['home-css'],
      filename: 'home-css.twig',
      publicPath: htmlPublicPath,
    }),
    new HtmlWebpackPlugin({
      templateContent: '',
      templateParameters: ['spectre'],
      filename: 'spectre.twig',
      publicPath: htmlPublicPath,
    }),
    new HtmlWebpackEnhancementPlugin(),
  ],
  resolve: {
    extensions: ['.js', '.ts', '.tsx', '.json'],
    alias: {
      'react-dom': '@hot-loader/react-dom',
      '@': path.resolve(__dirname, 'resources/assets/src'),
      readline: '@/scripts/cli/readline.ts',
    },
  },
  externals: Object.assign(
    { jquery: 'jQuery', bootstrap: 'bootstrap', 'admin-lte': 'adminlte' },
    devMode
      ? {}
      : {
          react: 'React',
          'react-dom': 'ReactDOM',
        },
  ) as Record<string, string>,
  optimization: {
    minimizer: [new TerserJSPlugin({})],
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
  config.plugins!.push(new webpack.NamedModulesPlugin())
  config.plugins!.push(new webpack.HotModuleReplacementPlugin())
}

export default config
