const path = require('path');

module.exports = {
  entry: {
    'pdf-builder-admin': './src/index.js',
    'pdf-builder-nonce-fix': './src/pdf-builder-nonce-fix.js'
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'assets/js/dist'),
  },
  mode: 'production',
  resolve: {
    extensions: ['.js', '.jsx', '.json']
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react']
          }
        }
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader']
      }
    ]
  }
};