const path = require('path');
const webpack = require('webpack');

module.exports = {
  entry: {
    'pdf-builder-admin': './resources/js/index.js',
    'pdf-builder-nonce-fix': './resources/js/pdf-builder-nonce-fix.js'
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, '../../assets/js/dist'),
    library: {
      name: 'PDFBuilderPro',
      type: 'window',
      export: 'default'
    }
  },
  plugins: [
    // ProvidePlugin retiré - on utilise l'import direct
  ],
  // React est partagé entre les chunks
  mode: 'production',
  optimization: {
    usedExports: false, // DÉSACTIVÉ pour éviter l'optimisation des globals
    sideEffects: false,  // DÉSACTIVÉ pour éviter l'optimisation
    minimize: false,     // Désactiver la minification pour le debug
    // Réactiver le code splitting pour les composants dynamiques
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all',
        },
        react: {
          test: /[\\/]node_modules[\\/](react|react-dom|scheduler)[\\/]/,
          name: 'react-vendor',
          chunks: 'all',
        }
      }
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
  ]
};