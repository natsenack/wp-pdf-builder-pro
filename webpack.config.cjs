const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
  mode: 'development',
  entry: {
    'pdf-builder-react': './assets/js/pdf-builder-react-wrapper.js',
    'pdf-builder-wrap': './assets/js/pdf-builder-wrap.js',
    'pdf-preview-api-client': './assets/js/pdf-preview-api-client.js',
    'pdf-preview-integration': './assets/js/pdf-preview-integration.js',
    'settings-global-save': './assets/js/settings-global-save.js',
    'tabs-root-monitor': './assets/js/tabs-root-monitor.js',
    'ajax-throttle': './assets/js/ajax-throttle.js'
    // Excluded: settings-tabs-improved.js, tabs-force.js (syntax errors)
  },
  output: {
    path: path.resolve(__dirname, 'assets/js/dist'),
    filename: '[name].js',
    clean: true
  },
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        use: 'ts-loader',
        exclude: /node_modules/
      },
      {
        test: /\.js$/,
        include: [
          /pdf-builder-react-wrapper\.js$/,
          /pdf-builder-wrap\.js$/
        ],
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react']
          }
        }
      },
      {
        test: /\.js$/,
        exclude: [
          /node_modules/,
          /pdf-builder-react-wrapper\.js$/,
          /pdf-builder-wrap\.js$/
        ],
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader']
      }
    ]
  },
  resolve: {
    extensions: ['.tsx', '.ts', '.js', '.jsx']
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '../css/[name].css'
    })
  ],
  externals: {
    'react': 'React',
    'react-dom': 'ReactDOM',
    'jquery': 'jQuery'
  }
};