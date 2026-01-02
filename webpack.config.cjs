const path = require('path');

module.exports = {
  mode: 'production',
  entry: {
    'pdf-builder-utils': './assets/js/pdf-builder-utils.js',
    'settings-tabs-improved': './assets/js/settings-tabs-improved.js'
  },
  output: {
    path: path.resolve(__dirname, 'plugin/resources/assets/js/dist'),
    filename: '[name].js',
    clean: true,
    libraryTarget: 'window'
  },
  module: {
    rules: [
      {
        test: /\.(ts|tsx)$/,
        use: 'ts-loader',
        exclude: /node_modules/
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      }
    ]
  },
  resolve: {
    extensions: ['.ts', '.tsx', '.js', '.jsx']
  },
  externals: {
    'react': 'React',
    'react-dom': 'ReactDOM',
    'jquery': 'jQuery'
  }
};