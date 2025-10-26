const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');

module.exports = {
  mode: 'production', // Mode production pour l'optimisation
  entry: {
    'pdf-builder-admin': './assets/js/pdf-builder-vanilla-bundle.js',
    'pdf-builder-nonce-fix': './resources/js/pdf-builder-nonce-fix.js'
    // script-loader is copied directly without webpack processing
  },
  target: ['web', 'es5'], // Cibler ES5 pour la compatibilité maximale
  output: {
    filename: (chunkData) => {
      // Utiliser des noms fixes pour les entry points principaux
      const name = chunkData.chunk.name;
      if (name === 'pdf-builder-admin' || name === 'pdf-builder-admin-debug' || name === 'pdf-builder-nonce-fix') {
        return '[name].js';
      }
      // Pour le script-loader, ne pas inclure le runtime chunk
      if (name === 'pdf-builder-script-loader') {
        return '[name].js';
      }
      // Pour les chunks dynamiques, utiliser des noms numériques
      if (name && !isNaN(name)) {
        return '[id].js';
      }
      // Utiliser des content hashes pour les autres chunks
      return '[name].[contenthash].js';
    },
    path: path.resolve(__dirname, '../../assets/js/dist'),
    clean: true, // Nettoyer les anciens fichiers
    library: {
      name: 'pdfBuilderPro',
      type: 'umd',
      export: 'default'
    },
    globalObject: 'this'
  },
  plugins: [
    // ProvidePlugin retiré - on utilise l'import direct
  ],
  resolve: {
    extensions: ['.js', '.ts', '.json']
  },
  module: {
    rules: [
      {
        test: /\.(js|ts)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env',
              '@babel/preset-typescript'
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
    // Copier seulement le script-loader sans traitement webpack (il utilise les globals directement)
    new CopyPlugin({
      patterns: [
        {
          from: path.resolve(__dirname, '../../resources/js/ScriptLoader.js'),
          to: path.resolve(__dirname, '../../assets/js/dist/pdf-builder-script-loader.js')
        }
        // REMOVED: main.js is now processed by webpack as entry point
      ]
    }),
    // Plugin de compression pour les assets
    new CompressionPlugin({
      algorithm: 'gzip',
      test: /\.(js|css)$/,
      threshold: 10240, // Compresser les fichiers > 10KB
      minRatio: 0.8
    })
  ],
  optimization: {
    // Désactiver certaines optimisations qui pourraient supprimer les variables globales
    usedExports: false,
    sideEffects: false,
    // Séparation des chunks pour l'optimisation
    splitChunks: {
      cacheGroups: {
        // Chunk pour les vendors (bibliothèques externes) - nom numérique
        vendor: {
          name: false, // Utiliser des noms automatiques (numériques)
          test: /[\\/]node_modules[\\/]/,
          chunks: 'all',
          priority: 10,
          minSize: 30000, // 30KB minimum
          enforce: true
        },
        // Chunk pour les utilitaires partagés - nom numérique
        common: {
          name: false, // Utiliser des noms automatiques (numériques)
          minChunks: 2,
          chunks: 'all',
          minSize: 10000, // 10KB minimum
          enforce: true
        }
      }
    },
    // Configuration du runtime chunk
    runtimeChunk: {
      name: 'runtime'
    }
  },
  // Configuration de performance
  performance: {
    hints: 'warning',
    maxEntrypointSize: 512000, // 512KB max pour l'entrée
    maxAssetSize: 512000, // 512KB max par asset
    assetFilter: function(assetFilename) {
      // Ne pas appliquer les limites aux fichiers de runtime
      return !assetFilename.endsWith('.map');
    }
  }
};