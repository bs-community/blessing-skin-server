import path from 'path'
import webpack from 'webpack'
import 'webpack-dev-server'
import { execSync } from 'child_process'
import MiniCssExtractPlugin from 'mini-css-extract-plugin'
import CssMinimizerPlugin from 'css-minimizer-webpack-plugin'
import HtmlWebpackPlugin from 'html-webpack-plugin'
import HtmlWebpackEnhancementPlugin from './tools/HtmlWebpackEnhancementPlugin'

interface Env {
  production?: boolean
}

export default function (env?: Env): webpack.Configuration {
  const isDev = !env?.production
  const isGitpod = 'GITPOD_REPO_ROOT' in process.env
  const htmlPublicPath = isDev
    ? isGitpod
      ? `${execSync('gp url 8080')}/app/`
      : '//localhost:8080/app/'
    : '{{ cdn_base }}/app/'

  return {
    name: 'app',
    mode: isDev ? 'development' : 'production',
    entry: {
      app: ['react-hot-loader/patch', '@/index.tsx'],
      style: [
        '@/styles/common.css',
        'admin-lte/dist/css/alt/adminlte.components.min.css',
        'admin-lte/dist/css/alt/adminlte.core.min.css',
        'admin-lte/dist/css/alt/adminlte.pages.min.css',
        'admin-lte/dist/css/alt/adminlte.light.min.css',
        '@fortawesome/fontawesome-free/css/all.min.css',
      ],
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
          test: /\.(png|webp|svg|woff2?|eot|ttf)$/,
          type: 'asset',
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: isDev ? '[name].css' : '[name].[contenthash:7].css',
        chunkFilename: isDev ? '[id].css' : '[id].[contenthash:7].css',
      }),
      new HtmlWebpackPlugin({
        templateContent: '',
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
        __blessing_public_path__: JSON.stringify(htmlPublicPath),
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
    optimization: {
      // @ts-ignore
      minimizer: [new CssMinimizerPlugin({}), '...'],
    },
    experiments: {
      syncWebAssembly: true,
    },
    devtool: isDev ? 'eval-source-map' : 'source-map',
    devServer: {
      headers: {
        'Access-Control-Allow-Origin': '*',
      },
      host: '0.0.0.0',
      hot: 'only',
      allowedHosts: ['localhost'].concat(
        isDev && isGitpod ? ['.gitpod.io'] : [],
      ),
    },
    stats: 'errors-warnings',
    ignoreWarnings: [/size limit/i],
  }
}
