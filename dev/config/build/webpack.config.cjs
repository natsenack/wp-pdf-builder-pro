/**
 * Webpack Configuration - PDF Builder Pro
 * 
 * Compiles Vanilla JS + TypeScript assets for WordPress plugin
 * Entry: assets/js/
 * Output: plugin/assets/
 */

const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const CompressionPlugin = require('compression-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');

const isProduction = process.env.NODE_ENV === 'production';
const projectRoot = path.resolve(__dirname, '../../..');

module.exports = {
  mode: isProduction ? 'production' : 'development',
  entry: {
    'pdf-canvas-vanilla': path.resolve(projectRoot, 'assets/js/pdf-canvas-vanilla.js'),
    'pdf-preview-api-client': path.resolve(projectRoot, 'assets/js/pdf-preview-api-client.js'),
    'pdf-preview-integration': path.resolve(projectRoot, 'assets/js/pdf-preview-integration.js'),
    'settings-global-save': path.resolve(projectRoot, 'assets/js/settings-global-save.js'),
    'settings-tabs-improved': path.resolve(projectRoot, 'assets/js/settings-tabs-improved.js'),
    'ajax-throttle': path.resolve(projectRoot, 'assets/js/ajax-throttle.js'),
    'tabs-force': path.resolve(projectRoot, 'assets/js/tabs-force.js'),
    'tabs-root-monitor': path.resolve(projectRoot, 'assets/js/tabs-root-monitor.js'),
    'pdf-builder-react': path.resolve(projectRoot, 'assets/js/pdf-builder-react/index.js'),
  },
  output: {
    path: path.resolve(projectRoot, 'plugin/assets/'),
    filename: 'js/[name].bundle.js',
    chunkFilename: 'js/[name].[contenthash].js',
    publicPath: '/wp-content/plugins/pdf-builder-pro/assets/',
    library: {
      name: 'pdfBuilderReact',
      type: 'umd',
      umdNamedDefine: false,  // Set to false for direct execution
      export: 'default',  // Explicitly use the default export
    },
    clean: false, // Ne pas nettoyer auto, on contrôle
    globalObject: 'typeof self !== "undefined" ? self : this',
  },
  devtool: isProduction ? 'source-map' : 'cheap-module-source-map',
  devServer: {
    static: {
      directory: path.resolve(projectRoot, 'plugin/assets/'),
    },
    compress: true,
    port: 8080,
    hot: true,
  },
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: [
          /node_modules/
        ],
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              ['@babel/preset-env', {
                targets: {
                  browsers: ['> 0.25%', 'not dead', 'IE 11']
                },
                modules: false,
                useBuiltIns: false,
                forceAllTransforms: true
              }]
            ],
            plugins: []
          },
        },
      },
      {
        test: /\.(ts|tsx)$/,
        exclude: /node_modules/,
        use: {
          loader: 'ts-loader',
          options: {
            transpileOnly: true,
          },
        },
      },
      {
        test: /\.css$/,
        use: [
          isProduction ? MiniCssExtractPlugin.loader : 'style-loader',
          {
            loader: 'css-loader',
            options: {
              sourceMap: !isProduction,
            },
          },
        ],
      },
      {
        test: /\.(png|jpg|jpeg|gif|svg)$/,
        type: 'asset',
        parser: {
          dataUrlCondition: {
            maxSize: 8 * 1024,
          },
        },
        generator: {
          filename: 'images/[name].[hash:8][ext]',
        },
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        type: 'asset/resource',
        generator: {
          filename: 'fonts/[name].[hash:8][ext]',
        },
      },
    ],
  },
  plugins: [
    // Custom plugin to inject immediate execution code after UMD wrapper
    {
      apply: (compiler) => {
        compiler.hooks.emit.tapPromise('ImmediateExecutionPlugin', (compilation) => {
          return Promise.resolve().then(() => {
            // Get the pdf-builder-react.bundle.js file
            const bundleKey = Object.keys(compilation.assets).find(key => 
              key.includes('pdf-builder-react.bundle.js') && !key.includes('.map')
            );
            
            if (bundleKey) {
              const asset = compilation.assets[bundleKey];
              let source = asset.source().toString();
              
              // After the UMD wrapper completes, IMMEDIATELY call the initialization
              const initCode = `
(function() {
  if (typeof window === 'undefined') return;
  console.log('🔥 [WEBPACK UMD] Bundle executed, pdfBuilderReact type:', typeof window.pdfBuilderReact);
  
  if (window.pdfBuilderReact && typeof window.pdfBuilderReact.initPDFBuilderReact === 'function') {
    console.log('🔥 [WEBPACK UMD] Calling initPDFBuilderReact directly...');
    try {
      var result = window.pdfBuilderReact.initPDFBuilderReact();
      console.log('🔥 [WEBPACK UMD] Direct call result:', result);
    } catch (err) {
      console.error('🔥 [WEBPACK UMD] Direct call error:', err.message);
    }
  } else {
    console.warn('🔥 [WEBPACK UMD] initPDFBuilderReact not found!', {
      pdfBuilderReact: !!window.pdfBuilderReact,
      hasFunction: window.pdfBuilderReact ? typeof window.pdfBuilderReact.initPDFBuilderReact : 'N/A'
    });
  }
})();
`;
              
              source = source + initCode;
              compilation.assets[bundleKey] = {
                source: () => source,
                size: () => source.length,
              };
            }
          });
        });
      }
    },

    new MiniCssExtractPlugin({
      filename: 'css/[name].bundle.css',
      chunkFilename: 'css/[name].[contenthash].css',
    }),
    
    // Polyfills globaux (commentés - à installer si nécessaire)
    // new webpack.ProvidePlugin({
    //   Buffer: ['buffer', 'Buffer'],
    //   process: 'process/browser',
    // }),
    
    new webpack.DefinePlugin({
      'process.env.PLUGIN_VERSION': JSON.stringify(require(path.join(projectRoot, 'package.json')).version),
    }),

    // Copier les fichiers statiques
    new CopyPlugin({
      patterns: [
        {
          from: path.resolve(projectRoot, 'assets/shared/'),
          to: path.resolve(projectRoot, 'plugin/assets/shared/'),
          globOptions: {
            ignore: ['**/*.ts'],
          },
        },
      ],
    }),

    // Compression Gzip (optionnel, désactivé par défaut)
    ...(isProduction ? [
      new CompressionPlugin({
        algorithm: 'gzip',
        test: /\.(js|css|svg)$/,
        threshold: 10240,
        minRatio: 0.8,
      }),
    ] : []),
  ],
  resolve: {
    extensions: ['.ts', '.tsx', '.js', '.jsx', '.json'],
    alias: {
      '@': path.resolve(projectRoot, 'assets/'),
      '@shared': path.resolve(projectRoot, 'assets/shared/'),
      '@ts': path.resolve(projectRoot, 'assets/ts/'),
      '@js': path.resolve(projectRoot, 'assets/js/'),
    },
    // Fallbacks commentés - à installer si nécessaire
    // fallback: {
    //   buffer: require.resolve('buffer/'),
    //   process: require.resolve('process/browser'),
    // },
  },
  optimization: {
    minimize: isProduction,
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          compress: {
            drop_console: isProduction,
            drop_debugger: isProduction,
          },
          output: {
            comments: false,
          },
        },
        extractComments: false,
      }),
    ],
    splitChunks: {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          priority: 10,
          reuseExistingChunk: true,
        },
        common: {
          minChunks: 2,
          priority: 5,
          reuseExistingChunk: true,
        },
      },
    },
    runtimeChunk: {
      name: 'runtime',
    },
  },
  performance: {
    maxEntrypointSize: 512000,
    maxAssetSize: 512000,
    hints: isProduction ? 'warning' : false,
  },
  stats: {
    preset: isProduction ? 'normal' : 'detailed',
    colors: true,
    modules: false,
  },
};
