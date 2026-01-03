const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');

module.exports = {
  mode: 'production',
  entry: {
    'pdf-builder-utils': './plugin/assets/js/pdf-builder-utils.js',
    'settings-tabs-improved': './plugin/assets/js/settings-tabs-improved.js',
    'pdf-builder-react': './src/frontend/index.js'
  },
  output: {
    path: path.resolve(__dirname, 'plugin/assets/js/dist'),
    filename: '[name].js',
    clean: true,
    libraryTarget: 'window'
  },
  plugins: [
    new CopyPlugin({
      patterns: [
        {
          from: path.resolve(__dirname, 'plugin/assets/js/dist/pdf-builder-react.js'),
          to: path.resolve(__dirname, 'plugin/assets/js/pdf-builder-react.js'),
          noErrorOnMissing: true
        },
        {
          from: path.resolve(__dirname, 'plugin/assets/js/dist/pdf-builder-utils.js'),
          to: path.resolve(__dirname, 'plugin/assets/js/pdf-builder-utils.js'),
          noErrorOnMissing: true
        },
        {
          from: path.resolve(__dirname, 'plugin/assets/js/dist/settings-tabs-improved.js'),
          to: path.resolve(__dirname, 'plugin/assets/js/settings-tabs-improved.js'),
          noErrorOnMissing: true
        }
      ]
    })
  ],
  module: {
    rules: [
      {
        test: /\.(ts|tsx)$/,
        use: 'ts-loader',
        exclude: /node_modules/
      },
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
  },
  resolve: {
    extensions: ['.ts', '.tsx', '.js', '.jsx', '.css']
  },
  externals: {
    'react': 'React',
    'react-dom': 'ReactDOM',
    'jquery': 'jQuery'
  }
};