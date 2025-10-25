const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');

module.exports = {
  entry: {
    'pdf-builder-script-loader': './resources/js/script-loader.js',
    'pdf-builder-admin': './resources/js/index.js',
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
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              [
                '@babel/preset-env',
                {
                  targets: {
                    browsers: ['> 0.5%', 'last 2 versions', 'Firefox ESR', 'not dead', 'IE 11'],
                  },
                  modules: false,
                  useBuiltIns: 'usage',
                  corejs: 3,
                },
              ],
              '@babel/preset-react',
            ],
          },
        },
      },
    ],
  },
  // React est partagé entre les chunks
  mode: 'production',
  optimization: {
    runtimeChunk: false, // Désactiver complètement le runtime chunk pour éviter les opérateurs ES6+
    usedExports: false, // DÉSACTIVÉ pour éviter la suppression des exports globaux
    sideEffects: false,  // DÉSACTIVÉ pour éviter la suppression des effets secondaires
    minimize: true,     // Garder la minification
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          ecma: 5, // Générer du code ES5
          compress: {
            drop_console: false, // NE PAS supprimer les console.log pour le debug
            drop_debugger: true,
            pure_funcs: [] // NE PAS supprimer les fonctions console
          },
          mangle: {
            safari10: true
          },
          output: {
            ecma: 5, // Sortie ES5
            comments: false
          }
        }
      })
    ],
    // Code splitting DÉSACTIVÉ pour éviter les problèmes de runtime ES6+
    splitChunks: false
  },
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
    // Temporairement désactivé pour résoudre les problèmes de hooks React
    // 'react': 'React',
    // 'react-dom': 'ReactDOM'
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