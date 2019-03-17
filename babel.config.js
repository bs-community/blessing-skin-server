module.exports = api => ({
  production: {
    presets: [
      ['@babel/preset-env', {
        useBuiltIns: false,
        loose: true,
      }],
      '@babel/preset-typescript',
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
      '@babel/preset-typescript',
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
      '@babel/preset-typescript',
    ],
    plugins: [
      'babel-plugin-dynamic-import-node',
    ],
  },
})[api.env()]
