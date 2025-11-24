const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');

module.exports = {
  mode: 'production', // Mode production pour l'optimisation
  entry: {
    'pdf-builder-react': './assets/js/pdf-builder-react/index.js'
  },
  target: ['web', 'es5'], // Cibler ES5 pour la compatibilité maximale
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, '../../../plugin/assets/js/dist'),
    clean: true,
    globalObject: 'this'
  },
  externals: {
    'react': 'React',
    'react-dom': 'ReactDOM',
    'react-dom/client': 'ReactDOM'
  },
  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx', '.json']
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
              ['@babel/preset-react', { runtime: 'automatic' }]
            ]
          }
        }
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader']
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
    minimize: true,
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          compress: {
            drop_console: false,  // ✅ TEMPORAIREMENT DÉSACTIVÉ pour debug
            drop_debugger: true,
            pure_funcs: [] // Ne pas supprimer les logs
          },
          mangle: {
            safari10: true
          }
        }
      })
    ],
    usedExports: true,
    sideEffects: true
  },
  performance: {
    hints: false, // TEMPORAIREMENT DÉSACTIVÉ pour debug
    maxEntrypointSize: 150 * 1024, // 150 KiB (réduit de 200)
    maxAssetSize: 150 * 1024,     // 150 KiB (réduit de 200)
    assetFilter: function(assetFilename) {
      return !assetFilename.endsWith('.map');
    }
  }
};
