const path = require('path');

module.exports = {
  entry: './src/index.js',
  output: {
    filename: 'pdf-builder-admin.js',
    path: path.resolve(__dirname, 'assets/js/dist'),
  },
  mode: 'production',
};