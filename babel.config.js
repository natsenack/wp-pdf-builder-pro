export default {
  presets: [
    [
      '@babel/preset-env',
      {
        targets: {
          node: 'current',
          browsers: ['last 2 versions']
        }
      }
    ]
  ],
  plugins: [
    // Plugins pour les tests
    '@babel/plugin-transform-modules-commonjs'
  ],
  env: {
    test: {
      plugins: [
        '@babel/plugin-transform-runtime'
      ]
    }
  },
  // Disable strict mode to avoid issues with window assignments
  sourceType: 'script'
};
