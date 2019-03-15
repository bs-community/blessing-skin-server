module.exports = api => ({
  production: {
    presets: [
      ['@babel/preset-env', {
        useBuiltIns: false,
        loose: true,
      }],
    ],
    plugins: [
      '@babel/plugin-syntax-dynamic-import',
      ['@babel/plugin-transform-runtime', {
        helpers: true,
        regenerator: true,
      }],
    ],
  },
  development: {
    presets: [
      ['@babel/preset-env', {
        targets: { esmodules: true },
      }],
    ],
    plugins: [
      '@babel/plugin-syntax-dynamic-import',
    ],
  },
  test: {
    presets: [
      ['@babel/preset-env', {
        targets: { node: 'current' },
      }],
    ],
    plugins: [
      'babel-plugin-dynamic-import-node',
    ],
  },
})[api.env()]
