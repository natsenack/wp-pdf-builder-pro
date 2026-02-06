const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CompressionPlugin = require("compression-webpack-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");

const isDev = process.env.NODE_ENV === "development";

// Output vers le dossier assets du plugin pour déploiement facile
const outputPath = path.resolve(__dirname, "plugin/assets/js");

module.exports = {
  mode: isDev ? "development" : "production",
  entry: {
    "pdf-builder-react": "./src/js/react/index.tsx",
    "pdf-builder-react-executor": "./src/js/pdf-builder-react-executor.js",
    "settings-tabs": "./src/js/admin/settings-tabs.js",
    "settings-main": "./src/js/admin/settings-main.js",
    "canvas-settings": "./src/js/admin/canvas-settings.js",
    "pdf-builder-react-init": "./src/js/admin/pdf-builder-react-init.js",
    "pdf-builder-react-wrapper": "./src/js/admin/pdf-builder-react-wrapper.js",
    "ajax-throttle": "./src/js/admin/ajax-throttle.js",
    "notifications": "./src/js/admin/notifications.js",
    "pdf-builder-wrap": "./src/js/admin/pdf-builder-wrap.js",
    "pdf-builder-init": "./src/js/admin/pdf-builder-init.js",
    "pdf-preview-api-client": "./plugin/preview-system/js/pdf-preview-api-client.js",
    "pdf-preview-integration": "./src/js/admin/pdf-preview-integration.js",
    "notifications-css": "./src/css/notifications.css",
  },
  output: {
    path: outputPath,
    filename: "[name].min.js",
    globalObject: 'typeof window !== "undefined" ? window : global',
    clean: true,
    assetModuleFilename: "../assets/[name][ext]",
  },
  devtool: isDev ? "eval-source-map" : false,
  performance: {
    maxEntrypointSize: 1024000, // 1MB
    maxAssetSize: 1024000, // 1MB
    hints: isDev ? false : "warning",
  },
  module: {
    rules: [
      {
        test: /\.(ts|tsx)$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            presets: [
              [
                "@babel/preset-env",
                {
                  useBuiltIns: "entry",
                  corejs: 3,
                  modules: false,
                },
              ],
              ["@babel/preset-react", { runtime: "automatic" }],
              "@babel/preset-typescript",
            ],
            cacheDirectory: true,
          },
        },
      },
      {
        test: /\.css$/,
        use: [
          isDev ? "style-loader" : MiniCssExtractPlugin.loader,
          {
            loader: "css-loader",
            options: {
              sourceMap: isDev,
            },
          },
        ],
      },
      {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        type: "asset/resource",
        generator: {
          filename: "../fonts/[name][ext]",
        },
      },
    ],
  },
  resolve: {
    extensions: [".ts", ".tsx", ".js", ".jsx", ".json"],
    alias: {
      "@components": path.resolve(__dirname, "src/js/react/components/"),
      "@hooks": path.resolve(__dirname, "src/js/react/hooks/"),
      "@utils": path.resolve(__dirname, "src/js/react/utils/"),
      "@contexts": path.resolve(__dirname, "src/js/react/contexts/"),
      "@types": path.resolve(__dirname, "src/js/react/types/"),
      "@styles": path.resolve(__dirname, "src/js/react/styles/"),
    },
  },
  plugins: [
    new CleanWebpackPlugin(),
    new MiniCssExtractPlugin({
      filename: "../css/[name].min.css",
      chunkFilename: "../css/[name].chunk.css",
    }),
    ...(!isDev
      ? [
          new CompressionPlugin({
            algorithm: "gzip",
            test: /\.(js|css)$/,
            threshold: 10240,
            minRatio: 0.8,
            filename: "[path][base].gz",
          }),
        ]
      : []),
  ],
  optimization: {
    minimize: !isDev,
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          compress: {
            drop_console: false,  // Keep console.log for debugging
            drop_debugger: true,
            passes: 2,
            pure_funcs: [],
          },
          mangle: {
            properties: false,
          },
          output: {
            comments: false,
          },
        },
        extractComments: false,
      }),
    ],
    runtimeChunk: false,
    splitChunks: {
      chunks: (chunk) => {
        // Don't split the pdf-builder-react entry - it needs to execute immediately
        return chunk.name !== 'pdf-builder-react' && chunk.name !== 'runtime';
      },
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: "vendors",
          priority: 10,
          reuseExistingChunk: true,
        },
        react: {
          test: /[\\/]node_modules[\\/](react|react-dom)[\\/]/,
          name: "react-vendor",
          priority: 20,
        },
        common: {
          minChunks: 2,
          priority: 5,
          reuseExistingChunk: true,
        },
      },
    },
  },
  cache: {
    type: "filesystem",
    cacheDirectory: path.resolve(__dirname, ".webpack_cache"),
  },
  stats: {
    colors: true,
    modules: false,
    children: false,
    chunks: false,
    chunkModules: false,
    errors: true,
    errorDetails: true,
    warnings: true,
    publicPath: true,
    performance: true,
    timings: true,
  },
};
