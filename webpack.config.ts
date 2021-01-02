import * as path from 'path'
import * as webpack from 'webpack'
import MiniCssExtractPlugin from 'mini-css-extract-plugin'
import CssMinimizerPlugin from 'css-minimizer-webpack-plugin'
import HtmlWebpackPlugin from 'html-webpack-plugin'
import HtmlWebpackEnhancementPlugin from './tools/HtmlWebpackEnhancementPlugin'
import SyncMetaJsPlugin from './tools/SyncMetaJsPlugin'

interface Env {
  production?: boolean
}

export default function (env?: Env): webpack.Configuration[] {
  const isDev = !env?.production
  const htmlPublicPath = isDev ? '//localhost:8080/app/' : '{{ cdn_base }}/app/'

  return [
    {
      name: 'app',
      mode: isDev ? 'development' : 'production',
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
        publicPath: '/app/',
        filename: isDev ? '[name].js' : '[name].[contenthash:7].js',
        chunkFilename: isDev ? '[id].js' : '[id].[contenthash:7].js',
        crossOriginLoading: 'anonymous',
      },
      module: {
        rules: [
          {
            test: /\.tsx?$/,
            loader: 'ts-loader',
            options: {
              configFile: isDev ? 'tsconfig.dev.json' : 'tsconfig.build.json',
              transpileOnly: true,
            },
          },
          {
            test: /\.css$/,
            use: [
              isDev ? 'style-loader' : MiniCssExtractPlugin.loader,
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
            loader: isDev ? 'url-loader' : 'file-loader',
          },
        ],
      },
      plugins: [
        new MiniCssExtractPlugin({
          filename: isDev ? '[name].css' : '[name].[contenthash:7].css',
          chunkFilename: isDev ? '[id].css' : '[id].[contenthash:7].css',
        }),
        new HtmlWebpackPlugin({
          templateContent: isDev
            ? ''
            : `
<script src="https://cdn.jsdelivr.net/npm/react@17.0.1/umd/react.production.min.js" integrity="sha256-Ag0WTc8xFszCJo1qbkTKp3wBMdjpjogsZDAhnSge744=" crossorigin></script>
<script src="https://cdn.jsdelivr.net/npm/react-dom@17.0.1/umd/react-dom.production.min.js" integrity="sha256-k8tzaSH8ucPwbsHEO4Wk5szE9zERNVz3XQynfyT66O0=" crossorigin></script>`,
          chunks: ['app'],
          scriptLoading: 'blocking',
          filename: 'app.twig',
          publicPath: htmlPublicPath,
        }),
        new HtmlWebpackPlugin({
          templateContent: '',
          chunks: ['style'],
          filename: 'style.twig',
          publicPath: htmlPublicPath,
        }),
        new HtmlWebpackPlugin({
          templateContent: '',
          chunks: ['home'],
          scriptLoading: 'blocking',
          filename: 'home.twig',
          publicPath: htmlPublicPath,
        }),
        new HtmlWebpackPlugin({
          templateContent: '',
          chunks: ['home-css'],
          filename: 'home-css.twig',
          publicPath: htmlPublicPath,
        }),
        new HtmlWebpackPlugin({
          templateContent: '',
          chunks: ['spectre'],
          filename: 'spectre.twig',
          publicPath: htmlPublicPath,
        }),
        new HtmlWebpackEnhancementPlugin(),
        new webpack.DefinePlugin({
          'window.Deno': 'true',
          Deno: {
            args: [],
            build: {},
            version: {},
          },
          'process.platform': '"browser"',
        }),
      ].concat(isDev ? [new webpack.HotModuleReplacementPlugin()] : []),
      resolve: {
        extensions: ['.js', '.ts', '.tsx'],
        alias: {
          'react-dom': '@hot-loader/react-dom',
          '@': path.resolve(__dirname, 'resources/assets/src'),
          readline: '@/scripts/cli/readline.ts',
          prompts: 'prompts/lib/index.js',
          assert: false,
        },
      },
      externals: Object.assign(
        { jquery: 'jQuery', bootstrap: 'bootstrap', 'admin-lte': 'adminlte' },
        isDev
          ? {}
          : {
              react: 'React',
              'react-dom': 'ReactDOM',
            },
      ) as Record<string, string>,
      optimization: {
        // @ts-ignore
        minimizer: [new CssMinimizerPlugin({}), '...'],
      },
      experiments: {
        syncWebAssembly: true,
      },
      devtool: isDev ? 'eval-source-map' : false,
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
    },
    {
      name: 'meta',
      mode: isDev ? 'development' : 'production',
      entry: {
        meta: './resources/assets/src/scripts/meta.ts',
        sw: './resources/assets/src/scripts/sw.ts',
      },
      output: {
        path: `${__dirname}/public`,
        filename: '[name].js',
      },
      module: {
        rules: [
          {
            test: /\.ts$/,
            loader: 'ts-loader',
            options: {
              configFile: 'tsconfig.build.json',
              transpileOnly: true,
            },
          },
        ],
      },
      plugins: [new SyncMetaJsPlugin()],
      resolve: {
        extensions: ['.js', '.ts'],
      },
      stats: 'errors-only',
    },
  ]
}
