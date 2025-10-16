const path = require('path');
const webpack = require('webpack');

module.exports = {
  entry: {
    'pdf-builder-admin': './src/index.js',
    'pdf-builder-nonce-fix': './src/pdf-builder-nonce-fix.js'
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'assets/js/dist')
  },
  // React est partagé entre les chunks
  mode: 'production',
  optimization: {
    usedExports: true, // Activer l'élimination des exports non utilisés
    sideEffects: true,  // Activer l'analyse des effets de bord pour optimisation
    minimize: true,     // Activer la minification
    splitChunks: false, // Désactiver le split des chunks pour tout mettre dans un bundle
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