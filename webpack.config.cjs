const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CompressionPlugin = require('compression-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

// Define different configurations for each entry point
const baseConfig = {
  target: 'web',
  module: {
    rules: [
      {
        test: /\.(js|jsx|ts|tsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env', '@babel/preset-react', '@babel/preset-typescript'],
          },
        },
      },
      {
        test: /\.css$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
      },
    ],
  },
  resolve: {
    extensions: ['.js', '.jsx', '.ts', '.tsx'],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '../css/[name].min.css',
    }),
    new CompressionPlugin({
      algorithm: 'gzip',
      test: /\.(js|css)$/,
      threshold: 10240,
      minRatio: 0.8,
    }),
    new CopyPlugin({
      patterns: [
        {
          from: 'src/js/settings-global-save.js',
          to: 'settings-global-save.js',
        },
        {
          from: 'src/js/settings-tabs-improved.js',
          to: 'settings-tabs-improved.js',
        },
        {
          from: 'src/js/tabs-force.js',
          to: 'tabs-force.js',
        },
        {
          from: 'src/js/tabs-root-monitor.js',
          to: 'tabs-root-monitor.js',
        },
        {
          from: 'src/js/ajax-throttle.js',
          to: 'ajax-throttle.js',
        },
        {
          from: 'src/js/pdf-builder-utils.js',
          to: 'pdf-builder-utils.js',
        },
        {
          from: 'src/js/notifications.js',
          to: 'notifications.js',
        },
        {
          from: 'src/js/developer-tools.js',
          to: 'developer-tools.js',
        },
        {
          from: 'src/js/pdf-builder-init.js',
          to: 'pdf-builder-init.js',
        },
        {
          from: 'src/js/pdf-builder-wrap.js',
          to: 'pdf-builder-wrap.js',
        },
      ],
    }),
  ],
  optimization: {
    minimizer: [
      new TerserPlugin({
        exclude: /tabs-force\.js$|settings-tabs-improved\.js$|settings-global-save\.js$|tabs-root-monitor\.js$|ajax-throttle\.js$|pdf-builder-utils\.js$|notifications\.js$|developer-tools\.js$|pdf-builder-init\.js$|pdf-builder-wrap\.js$/,
        terserOptions: {
          compress: {
            drop_console: false,  // KEEP console logs
            drop_debugger: true,
            passes: 2,
          },
          mangle: false,  // Keep function names readable for debugging
          output: {
            comments: false,
          },
        },
        extractComments: false,
      }),
      new CompressionPlugin({
        algorithm: 'gzip',
        test: /\.(js|css)$/,
        threshold: 10240,
        minRatio: 0.8,
      }),
    ],
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          priority: 10,
        },
      },
    },
  },
};

// Export individual configs for each entry point
module.exports = [
  {
    ...baseConfig,
    entry: { 'pdf-builder-react': './src/js/pdf-builder-react/wordpress-entry.tsx' },
    output: {
      path: path.resolve(__dirname, 'plugin/assets/js'),
      filename: '[name].min.js',
      library: 'pdfBuilderReact',
      libraryTarget: 'umd',
      globalObject: 'typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : {}',
      clean: false,
    },
  },
  {
    ...baseConfig,
    entry: { 'pdf-builder-react-wrapper': './src/js/pdf-builder-react-wrapper.js' },
    output: {
      path: path.resolve(__dirname, 'plugin/assets/js'),
      filename: '[name].min.js',
      library: 'pdfBuilderReactWrapper',
      libraryTarget: 'umd',
      globalObject: 'typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : {}',
      clean: false,
    },
  },
  {
    ...baseConfig,
    entry: { 'pdf-preview-api-client': './src/js/pdf-preview-api-client.js' },
    output: {
      path: path.resolve(__dirname, 'plugin/assets/js'),
      filename: '[name].min.js',
      library: 'pdfPreviewApiClient',
      libraryTarget: 'umd',
      globalObject: 'typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : {}',
      clean: false,
    },
  },
  {
    ...baseConfig,
    entry: { 'pdf-preview-integration': './src/js/pdf-preview-integration.js' },
    output: {
      path: path.resolve(__dirname, 'plugin/assets/js'),
      filename: '[name].min.js',
      library: 'pdfPreviewIntegration',
      libraryTarget: 'umd',
      globalObject: 'typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : {}',
      clean: false,
    },
  },
];