const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');

module.exports = {
  mode: 'production', // Mode production pour l'optimisation
  entry: {
    'pdf-builder-admin': './assets/js/src/pdf-builder-editor/pdf-builder-vanilla-bundle.js',
    'pdf-builder-admin-debug': './assets/js/src/pdf-builder-editor/pdf-builder-vanilla-bundle.js',
    'pdf-builder-react': './assets/js/src/pdf-builder-react/index.js',
    'pdf-builder-nonce-fix': './dev/resources/js/pdf-builder-nonce-fix.js'
  },
  target: ['web', 'es5'], // Cibler ES5 pour la compatibilité maximale
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, '../../../plugin/assets/js/dist'),
    clean: true,
    library: {
      name: 'pdfBuilderReact',
      type: 'umd',
      export: 'default'
    },
    globalObject: 'this'
  },
  resolve: {
    extensions: ['.js', '.ts', '.tsx', '.json']
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
    new CopyPlugin({
      patterns: [
        {
          from: path.resolve(__dirname, '../../../dev/resources/js/ScriptLoader.js'),
          to: path.resolve(__dirname, '../../../plugin/assets/js/dist/pdf-builder-script-loader.js')
        }
      ]
    }),
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