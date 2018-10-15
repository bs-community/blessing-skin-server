const webpack = require('webpack');
const webpackConfig = require('../webpack.config');

process.env.NODE_ENV = webpackConfig.mode = 'production';

webpack(webpackConfig, (err, stats) => {
    if (err || stats.hasErrors()) {
        err && console.log(err);
        process.exitCode = 1;
    }
});
