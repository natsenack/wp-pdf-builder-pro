const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');

module.exports = {
  mode: 'production', // Mode production pour l'optimisation
  entry: {
    'pdf-builder-react': './assets/js/src/pdf-builder-react/index.js'
  },
  target: ['web', 'es5'], // Cibler ES5 pour la compatibilité maximale
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, '../../../plugin/assets/js/dist'),
    clean: true,
    library: {
      name: 'pdfBuilderReact',
      type: 'var'
    },
    globalObject: 'this'
  },
  externals: {
    // 'react': 'React',  // Commenté pour bundler React
    // 'react-dom': 'ReactDOM'  // Commenté pour bundler ReactDOM
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
            drop_console: false,  // ✅ GARDÉ POUR DEBUGGING
            drop_debugger: true,
            pure_funcs: [] // ✅ VIDE POUR NE PAS SUPPRIMER console.log
          },
          mangle: {
            safari10: true
          }
        }
      })
    ],
    // Désactiver la séparation des chunks pour forcer un seul bundle
    runtimeChunk: false,
    splitChunks: false,
    usedExports: true, // Améliorer l'arbre des dépendances
    sideEffects: true   // Respecter les sideEffects du package.json
  },
  performance: {
    hints: 'warning',
    maxEntrypointSize: 204800, // 200 KiB limit
    maxAssetSize: 204800,     // 200 KiB limit
    assetFilter: function(assetFilename) {
      return !assetFilename.endsWith('.map');
    }
  }
};