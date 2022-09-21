const TerserJSPlugin = require('terser-webpack-plugin');
const path = require('path');

module.exports = {
  optimization: {
    minimizer: [new TerserJSPlugin({})],
  },
  entry: ['./app.js'],
  output: {
    path: path.resolve(__dirname, 'public'),
    filename: './index.js',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader',
          },
        ],
      },
    ],
  },
};
