/**
 * Webpack Configuration - PDF Builder Pro
 * 
 * Compiles Vanilla JS + TypeScript assets for WordPress plugin
 * Entry: assets/js/
 * Output: plugin/assets/
 */

const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');

const isProduction = process.env.NODE_ENV === 'production';
const projectRoot = path.resolve(__dirname, '../../..');

module.exports = {
  mode: isProduction ? 'production' : 'development',
  entry: {
    'pdf-canvas-vanilla': path.resolve(projectRoot, 'assets/js/pdf-canvas-vanilla.js'),
    'pdf-preview-api-client': path.resolve(projectRoot, 'assets/js/pdf-preview-api-client.js'),
    'pdf-preview-integration': path.resolve(projectRoot, 'assets/js/pdf-preview-integration.js'),
    'settings-global-save': path.resolve(projectRoot, 'assets/js/settings-global-save.js'),
    'settings-tabs-improved': path.resolve(projectRoot, 'assets/js/settings-tabs-improved.js'),
    'ajax-throttle': path.resolve(projectRoot, 'assets/js/ajax-throttle.js'),
    'tabs-force': path.resolve(projectRoot, 'assets/js/tabs-force.js'),
    'tabs-root-monitor': path.resolve(projectRoot, 'assets/js/tabs-root-monitor.js'),
  },
  output: {
    path: path.resolve(projectRoot, 'plugin/assets/'),
    filename: 'js/[name].bundle.js',
    chunkFilename: 'js/[name].[contenthash].js',
    publicPath: '/wp-content/plugins/pdf-builder-pro/assets/',
    library: {
      name: 'PDFBuilder',
      type: 'umd',
      export: 'default',
    },
    clean: false, // Ne pas nettoyer auto, on contrôle
  },
  devtool: isProduction ? 'source-map' : 'cheap-module-source-map',
  devServer: {
    static: {
      directory: path.resolve(projectRoot, 'plugin/assets/'),
    },
    compress: true,
    port: 8080,
    hot: true,
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
            plugins: ['@babel/plugin-transform-runtime'],
          },
        },
      },
      {
        test: /\.(ts|tsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'ts-loader',
          options: {
            transpileOnly: true,
          },
        },
      },
      {
        test: /\.css$/,
        use: [
          isProduction ? MiniCssExtractPlugin.loader : 'style-loader',
          {
            loader: 'css-loader',
            options: {
              sourceMap: !isProduction,
            },
          },
        ],
      },
      {
        test: /\.(png|jpg|jpeg|gif|svg)$/,
        type: 'asset',
        parser: {
          dataUrlCondition: {
            maxSize: 8 * 1024,
          },
        },
        generator: {
          filename: 'images/[name].[hash:8][ext]',
        },
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        type: 'asset/resource',
        generator: {
          filename: 'fonts/[name].[hash:8][ext]',
        },
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'css/[name].bundle.css',
      chunkFilename: 'css/[name].[contenthash].css',
    }),
    
    // Polyfills globaux (commentés - à installer si nécessaire)
    // new webpack.ProvidePlugin({
    //   Buffer: ['buffer', 'Buffer'],
    //   process: 'process/browser',
    // }),
    
    new webpack.DefinePlugin({
      'process.env.PLUGIN_VERSION': JSON.stringify(require(path.join(projectRoot, 'package.json')).version),
    }),

    // Copier les fichiers statiques
    new CopyPlugin({
      patterns: [
        {
          from: path.resolve(projectRoot, 'assets/shared/'),
          to: path.resolve(projectRoot, 'plugin/assets/shared/'),
          globOptions: {
            ignore: ['**/*.ts'],
          },
        },
      ],
    }),

    // Compression Gzip (optionnel, désactivé par défaut)
    ...(isProduction ? [
      new CompressionPlugin({
        algorithm: 'gzip',
        test: /\.(js|css|svg)$/,
        threshold: 10240,
        minRatio: 0.8,
      }),
    ] : []),
  ],
  resolve: {
    extensions: ['.ts', '.tsx', '.js', '.jsx', '.json'],
    alias: {
      '@': path.resolve(projectRoot, 'assets/'),
      '@shared': path.resolve(projectRoot, 'assets/shared/'),
      '@ts': path.resolve(projectRoot, 'assets/ts/'),
      '@js': path.resolve(projectRoot, 'assets/js/'),
    },
    // Fallbacks commentés - à installer si nécessaire
    // fallback: {
    //   buffer: require.resolve('buffer/'),
    //   process: require.resolve('process/browser'),
    // },
  },
  optimization: {
    minimize: isProduction,
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          compress: {
            drop_console: isProduction,
            drop_debugger: isProduction,
          },
          output: {
            comments: false,
          },
        },
        extractComments: false,
      }),
    ],
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          priority: 10,
          reuseExistingChunk: true,
        },
        common: {
          minChunks: 2,
          priority: 5,
          reuseExistingChunk: true,
        },
      },
    },
    runtimeChunk: {
      name: 'runtime',
    },
  },
  performance: {
    maxEntrypointSize: 512000,
    maxAssetSize: 512000,
    hints: isProduction ? 'warning' : false,
  },
  stats: {
    preset: isProduction ? 'normal' : 'detailed',
    colors: true,
    modules: false,
  },
};
