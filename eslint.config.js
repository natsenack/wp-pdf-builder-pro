export default [
  {
    ignores: [
      "node_modules/**",
      "dist/**",
      "build/**",
      "**/*.min.js",
      "plugin/vendor/**",
      "plugin/assets/**",
      ".next/**",
      "coverage/**",
      "*.config.js",
      "webpack.config.cjs",
      "phpstan-bootstrap.php"
    ]
  },
  {
    files: ["src/**/*.{js,jsx,ts,tsx}"],
    languageOptions: {
      ecmaVersion: 2021,
      sourceType: "module",
      parserOptions: {
        ecmaFeatures: {
          jsx: true
        }
      },
      globals: {
        window: "readonly",
        document: "readonly",
        navigator: "readonly",
        console: "readonly",
        HTMLImageElement: "readonly",
        HTMLElement: "readonly",
        fetch: "readonly",
        performance: "readonly"
      }
    },
    rules: {
      "no-unused-vars": ["warn", { argsIgnorePattern: "^_" }],
      "no-console": ["warn", { allow: ["warn", "error", "info"] }]
    }
  }
];
