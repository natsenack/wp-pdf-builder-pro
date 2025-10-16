const path = require('path');

module.exports = {
  entry: {
    'pdf-builder-admin': './src/index.js',
    'pdf-builder-nonce-fix': './src/pdf-builder-nonce-fix.js'
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'assets/js/dist'),
  },
  // Plus d'externals - WordPress fournit React via wp-element
  externals: {
    'react': 'React',
    'react-dom': 'ReactDOM'
  },
  mode: 'production',
  optimization: {
    usedExports: true, // Activer l'élimination des exports non utilisés
    sideEffects: true,  // Activer l'analyse des effets de bord pour optimisation
    minimize: true,     // Activer la minification
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all',
          enforce: true, // Forcer la séparation des vendors
          priority: 10
        }
      },
    },
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