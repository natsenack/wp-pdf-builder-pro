export default [
  {
    files: ['**/*.js'],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'module',
      globals: {
        // Browser globals
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        fetch: 'readonly',
        setTimeout: 'readonly',
        clearTimeout: 'readonly',
        setInterval: 'readonly',
        clearInterval: 'readonly',
        HTMLElement: 'readonly',
        HTMLCanvasElement: 'readonly',
        HTMLImageElement: 'readonly',
        CanvasRenderingContext2D: 'readonly',
        Event: 'readonly',
        CustomEvent: 'readonly',
        FileReader: 'readonly',
        FormData: 'readonly',
        URL: 'readonly',
        Blob: 'readonly',
        ArrayBuffer: 'readonly',
        Uint8Array: 'readonly',
        URLSearchParams: 'readonly',
        EventListener: 'readonly',
        AbortController: 'readonly',
        alert: 'readonly',
        navigator: 'readonly',
        confirm: 'readonly',
        location: 'readonly',
        jQuery: 'readonly',
        $: 'readonly',
        CodeMirror: 'readonly',
        pdfBuilderAjax: 'readonly',
        pdfBuilderPredefined: 'readonly',
        refreshNonceAndRetry: 'readonly',
        MutationObserver: 'readonly',
        btoa: 'readonly',
        localStorage: 'readonly',
        sessionStorage: 'readonly',
        // Node.js globals for config files
        require: 'readonly',
        module: 'readonly',
        __dirname: 'readonly',
        process: 'readonly'
      }
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': 'error',
      'no-case-declarations': 'error'
    }
  },
  {
    ignores: [
      '**/dist/**',
      '**/build/**',
      '**/*.min.js',
      '**/vendor/**',
      '**/node_modules/**',
      'plugin/assets/js/pdf-builder-react-wrapper.min.js'
    ]
  }
];
