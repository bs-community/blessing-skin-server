const devMode = !process.argv.includes('-p')

module.exports = {
  mode: devMode ? 'development' : 'production',
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
  resolve: {
    extensions: ['.js', '.ts', '.tsx', '.json'],
  },
  stats: 'errors-only',
}
