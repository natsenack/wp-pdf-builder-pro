const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CompressionPlugin = require('compression-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
  entry: {
    'pdf-builder-react-wrapper': './src/js/pdf-builder-react-wrapper.js',
    'pdf-preview-api-client': './src/js/pdf-preview-api-client.js',
    'pdf-preview-integration': './src/js/pdf-preview-integration.js',
  },
  output: {
    path: path.resolve(__dirname, 'plugin/assets/js'),
    filename: '[name].min.js',
    libraryTarget: 'window',
    clean: true,
  },
  target: 'web',
  externals: {
    // Prevent webpack from bundling any CommonJS/AMD modules
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx|ts|tsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react', '@babel/preset-typescript'],
          },
        },
      },
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
      },
    ],
  },
  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx'],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '../css/[name].min.css',
    }),
    new CompressionPlugin({
      algorithm: 'gzip',
      test: /\.(js|css)$/,
      threshold: 10240,
      minRatio: 0.8,
    }),
    new CopyPlugin({
      patterns: [
        {
          from: 'src/js/settings-global-save.js',
          to: 'settings-global-save.js',
        },
        {
          from: 'src/js/settings-tabs-improved.js',
          to: 'settings-tabs-improved.js',
        },
        {
          from: 'src/js/tabs-force.js',
          to: 'tabs-force.js',
        },
        {
          from: 'src/js/tabs-root-monitor.js',
          to: 'tabs-root-monitor.js',
        },
        {
          from: 'src/js/ajax-throttle.js',
          to: 'ajax-throttle.js',
        },
      ],
    }),
  ],
  optimization: {
    minimizer: [
      new TerserPlugin({
        exclude: /tabs-force\.js$|settings-tabs-improved\.js$|settings-global-save\.js$|tabs-root-monitor\.js$|ajax-throttle\.js$/,
      }),
      new CompressionPlugin({
        algorithm: 'gzip',
        test: /\.(js|css)$/,
        threshold: 10240,
        minRatio: 0.8,
      }),
    ],
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all',
        },
      },
    },
  },
  mode: 'production',
  devtool: false,
};