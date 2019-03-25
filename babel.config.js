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
      '@babel/plugin-proposal-optional-catch-binding',
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
      '@babel/plugin-proposal-optional-catch-binding',
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
      '@babel/plugin-proposal-optional-catch-binding',
    ],
  },
})[api.env()]
