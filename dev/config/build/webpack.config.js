const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');

module.exports = {
  mode: 'production', // Mode production pour l'optimisation
  entry: {
    'pdf-builder-react': './assets/js/pdf-builder-react-wrapper.js'
  },
  target: ['web', 'es6'], // Cibler ES6 pour de meilleures performances
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, '../../../plugin/assets/js/dist'),
    clean: true,
    globalObject: 'window',
    library: {
      name: 'pdfBuilderReact',
      type: 'self'
    }
  },
  externals: {
    'react-dom': 'ReactDOM'
  },
  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx', '.json'],
    alias: {
      // Override react to use our shim wrapper instead of external
      // Path from dev/config/build/ -> ../../../assets/js/pdf-builder-react/react-shim-wrapper.js
      'react': path.resolve(__dirname, '../../../assets/js/pdf-builder-react/react-shim-wrapper.js')
    }
  },
  module: {
    rules: [
      {
        test: /\.(js|ts|tsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env',
              '@babel/preset-typescript',
              ['@babel/preset-react', { runtime: 'classic' }]
            ]
          }
        }
      },
      {
        test: /\.css$/,
        use: [
          {
            loader: 'style-loader',
            options: {
              injectType: 'singletonStyleTag'
            }
          }, 
          'css-loader'
        ]
      }
    ]
  },
  plugins: [
    new CompressionPlugin({
      algorithm: 'gzip',
      test: /\.(js|css)$/,
      threshold: 10240,
      minRatio: 0.8
    })
  ],
  optimization: {
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all',
          priority: 10
        },
        common: {
          minChunks: 2,
          priority: 5,
          reuseExistingChunk: true
        }
      }
    },
    minimize: true,
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          compress: {
            drop_console: false,
            drop_debugger: true,
            pure_funcs: [],
            pure_getters: true,
            unsafe: false,
            unsafe_comps: false,
            warnings: false,
            passes: 1
          },
          mangle: {
            safari10: true,
            // Keep all hook names intact - don't mangle at all
            keep_fnames: true
          },
          output: {
            comments: false,
            beautify: false
          }
        },
        // Exclude react shim from minification entirely
        exclude: [/react-shim-wrapper/]
      })
    ],
    usedExports: true,
    sideEffects: false, // Optimiser le tree shaking
    runtimeChunk: false // Éviter un chunk séparé pour le runtime
  },
  performance: {
    hints: 'warning',
    maxEntrypointSize: 250 * 1024, // 250 KiB
    maxAssetSize: 250 * 1024,     // 250 KiB
    assetFilter: function(assetFilename) {
      return !assetFilename.endsWith('.map');
    }
  }
};
