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
            drop_console: true,  // ✅ ACTIVÉ pour production
            drop_debugger: true,
            pure_funcs: ['console.log', 'console.info', 'console.debug'] // Supprimer les logs de debug
          },
          mangle: {
            safari10: true
          }
        }
      })
    ],
    // Activer le code splitting intelligent avec chunks dynamiques
    runtimeChunk: 'single',
    splitChunks: {
      chunks: 'all',
      maxSize: 150000, // 150KB max par chunk
      minSize: 10000,  // 10KB min par chunk
      cacheGroups: {
        // Chunk pour React et ses dépendances (si pas externe)
        react: {
          test: /[\\/]node_modules[\\/](react|react-dom|react-router)[\\/]/,
          name: 'react-vendor',
          chunks: 'all',
          priority: 20,
          enforce: true
        },
        // Chunk pour les utilitaires lourds
        heavy: {
          test: /[\\/]node_modules[\\/](jsbarcode|qrcode|@fortawesome)[\\/]/,
          name: 'heavy-utils',
          chunks: 'all',
          priority: 25,
          enforce: true
        },
        // Chunk pour les composants de l'application
        components: {
          test: /[\\/]assets[\\/]js[\\/]pdf-builder-react[\\/]components[\\/]/,
          name: 'components',
          chunks: 'async', // Uniquement les imports dynamiques
          priority: 15,
          maxSize: 100000, // 100KB max pour les composants
          minSize: 20000   // 20KB min
        },
        // Chunk pour les hooks et contextes
        hooks: {
          test: /[\\/]assets[\\/]js[\\/]pdf-builder-react[\\/](hooks|contexts)[\\/]/,
          name: 'hooks-contexts',
          chunks: 'all',
          priority: 12,
          maxSize: 80000, // 80KB max
          minSize: 15000  // 15KB min
        },
        // Chunk pour les utilitaires
        utils: {
          test: /[\\/]assets[\\/]js[\\/]pdf-builder-react[\\/]utils[\\/]/,
          name: 'utils',
          chunks: 'all',
          priority: 10,
          maxSize: 50000, // 50KB max
          minSize: 10000  // 10KB min
        },
        // Vendor par défaut
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendor',
          chunks: 'all',
          priority: 5
        }
      }
    },
    usedExports: true,
    sideEffects: true
  },
  performance: {
    hints: 'error', // Plus strict que 'warning'
    maxEntrypointSize: 150 * 1024, // 150 KiB (réduit de 200)
    maxAssetSize: 150 * 1024,     // 150 KiB (réduit de 200)
    assetFilter: function(assetFilename) {
      return !assetFilename.endsWith('.map');
    }
  }
};
