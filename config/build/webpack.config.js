const path = require('path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');

module.exports = {
  entry: {
    'pdf-builder-admin': './resources/js/index.js',
    'pdf-builder-nonce-fix': './resources/js/pdf-builder-nonce-fix.js',
    'pdf-builder-script-loader': './resources/js/ScriptLoader.js'
  },
  output: {
    filename: '[name].[contenthash].js',
    path: path.resolve(__dirname, '../../assets/js/dist'),
    library: {
      name: 'PDFBuilderPro',
      type: 'window',
      export: 'default'
    },
    clean: true // Nettoyer les anciens fichiers
  },
  plugins: [
    // ProvidePlugin retiré - on utilise l'import direct
  ],
  // React est partagé entre les chunks
  mode: 'production',
  optimization: {
    usedExports: true, // RÉACTIVÉ pour optimiser les exports
    sideEffects: true,  // RÉACTIVÉ pour supprimer les effets secondaires inutiles
    minimize: true,     // RÉACTIVÉ pour la production
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          compress: {
            drop_console: true, // Supprimer les console.log en production
            drop_debugger: true,
            pure_funcs: ['console.log', 'console.info', 'console.debug']
          },
          mangle: {
            safari10: true
          }
        }
      })
    ],
    // Optimisation avancée du code splitting
    splitChunks: {
      chunks: 'all',
      minSize: 20000, // Taille minimale pour le splitting (20KB)
      maxSize: 244000, // Taille maximale recommandée (244KB)
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all',
          priority: 10,
          enforce: true
        },
        react: {
          test: /[\\/]node_modules[\\/](react|react-dom|scheduler)[\\/]/,
          name: 'react-vendor',
          chunks: 'all',
          priority: 20,
          enforce: true
        },
        // Nouveaux chunks pour optimiser le chargement
        ui: {
          test: /[\\/]src[\\/](Admin|Controllers)[\\/]/,
          name: 'pdf-builder-ui',
          chunks: 'all',
          priority: 5
        },
        renderers: {
          test: /[\\/]src[\\/]Renderers[\\/]/,
          name: 'pdf-builder-renderers',
          chunks: 'all',
          priority: 5
        },
        utilities: {
          test: /[\\/]src[\\/]utilities[\\/]/,
          name: 'pdf-builder-utils',
          chunks: 'all',
          priority: 3
        }
      }
    },
    // Runtime chunk séparé pour améliorer le cache
    runtimeChunk: {
      name: 'runtime'
    }
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
    // ProvidePlugin retiré - on expose React globalement dans le code
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