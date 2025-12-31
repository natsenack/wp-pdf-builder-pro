export default {
  presets: [
    [
      '@babel/preset-env',
      {
        targets: {
          browsers: ['> 0.25%', 'not dead', 'IE 11']
        },
        modules: false,
        useBuiltIns: false,
        forceAllTransforms: true
      }
    ]
  ],
  plugins: [
    // Force ES5 compatible code
    '@babel/plugin-transform-arrow-functions',
    '@babel/plugin-transform-block-scoped-functions',
    '@babel/plugin-transform-block-scoping',
    '@babel/plugin-transform-classes',
    '@babel/plugin-transform-computed-properties',
    '@babel/plugin-transform-destructuring',
    '@babel/plugin-transform-for-of',
    '@babel/plugin-transform-function-name',
    '@babel/plugin-transform-literals',
    '@babel/plugin-transform-parameters',
    '@babel/plugin-transform-shorthand-properties',
    '@babel/plugin-transform-spread',
    '@babel/plugin-transform-template-literals',
    ['@babel/plugin-transform-runtime', {
      helpers: false,
      regenerator: false
    }]
  ],
  env: {
    test: {
      plugins: [
        '@babel/plugin-transform-runtime'
      ]
    }
  }
};