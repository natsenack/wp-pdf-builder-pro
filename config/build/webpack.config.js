const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');

module.exports = {
  entry: {
    'pdf-builder-script-loader': './resources/js/script-loader.js',
    'pdf-builder-admin': './resources/js/main.js',
    'pdf-builder-nonce-fix': './resources/js/pdf-builder-nonce-fix.js'
  },
  target: ['web', 'es5'], // Cibler ES5 pour la compatibilité maximale
  output: {
    filename: (chunkData) => {
      // Utiliser des noms fixes pour les entry points principaux
      const name = chunkData.chunk.name;
      if (name === 'pdf-builder-admin' || name === 'pdf-builder-nonce-fix') {
        return '[name].js';
      }
      // Pour le script-loader, ne pas inclure le runtime chunk
      if (name === 'pdf-builder-script-loader') {
        return '[name].js';
      }
      // Utiliser des content hashes pour les chunks dynamiques
      return '[name].[contenthash].js';
    },
    path: path.resolve(__dirname, '../../assets/js/dist'),
    clean: true // Nettoyer les anciens fichiers
  },
  plugins: [
    // ProvidePlugin retiré - on utilise l'import direct
  ],
  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx', '.json']
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx|ts|tsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env',
              '@babel/preset-react',
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
  externals: {
    // React fourni par WordPress
    'react': 'React',
    'react-dom': 'ReactDOM'
  },
  plugins: [
    // Plugin de compression pour les assets
    new CompressionPlugin({
      algorithm: 'gzip',
      test: /\.(js|css)$/,
      threshold: 10240, // Compresser les fichiers > 10KB
      minRatio: 0.8
    })
  ],
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