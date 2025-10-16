const path = require('path');

module.exports = {
  entry: {
    'pdf-builder-admin': './src/main.js',
    'pdf-builder-nonce-fix': './src/pdf-builder-nonce-fix.js'
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'assets/js/dist'),
  },
  mode: 'development',
  optimization: {
    usedExports: false, // Désactiver l'élimination des exports non utilisés
    sideEffects: false   // Désactiver complètement l'analyse des effets de bord
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
  }
};