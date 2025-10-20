const path = require('path');

module.exports = {
  entry: './resources/js/simple-bundle.js',
  output: {
    filename: 'simple-bundle.js',
    path: path.resolve(__dirname, '../assets/js/dist')
  },
  mode: 'development',
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react']
          }
        }
      }
    ]
  },
  resolve: {
    extensions: ['.js', '.jsx']
  }
};