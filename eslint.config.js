import js from '@eslint/js';
import tseslint from '@typescript-eslint/eslint-plugin';
import tsparser from '@typescript-eslint/parser';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';

export default [
  js.configs.recommended,
  {
    files: ['assets/js/src/**/*.{js,ts,tsx}'],
    languageOptions: {
      parser: tsparser,
      parserOptions: {
        ecmaVersion: 2020,
        sourceType: 'module',
        ecmaFeatures: {
          jsx: true
        }
      },
      globals: {
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
        CanvasTextAlign: 'readonly',
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
        process: 'readonly',
        queueMicrotask: 'readonly',
        NodeJS: 'readonly'
      }
    },
    plugins: {
      '@typescript-eslint': tseslint,
      'react': react,
      'react-hooks': reactHooks
    },
    rules: {
      ...tseslint.configs.recommended.rules,
      ...react.configs.recommended.rules,
      ...reactHooks.configs.recommended.rules,
      'react/react-in-jsx-scope': 'off',
      '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
      '@typescript-eslint/no-explicit-any': 'warn',
      'react/prop-types': 'off'
    },
    settings: {
      react: {
        version: 'detect'
      }
    }
  },
  // Configuration pour les fichiers JavaScript du navigateur
  {
    files: ['plugin/assets/js/*.js', 'plugin/templates/admin/js/*.js'],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'script',
      globals: {
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
        toastr: 'readonly',
        refreshNonceAndRetry: 'readonly',
        MutationObserver: 'readonly',
        btoa: 'readonly',
        localStorage: 'readonly'
      }
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': 'error'
    }
  },
  // Configuration pour les fichiers Node.js (configs de build)
  {
    files: ['*.config.js', 'babel.config.js', 'dev/config/**/*.js'],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'module',
      globals: {
        require: 'readonly',
        module: 'readonly',
        __dirname: 'readonly',
        process: 'readonly',
        console: 'readonly'
      }
    },
    rules: {
      'no-undef': 'off' // Désactiver car les globals Node.js sont définis
    }
  },
  // Configuration pour les fichiers de test
  {
    files: ['**/*.test.js', 'test-*.js'],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'module',
      globals: {
        window: 'readonly',
        document: 'readonly',
        console: 'readonly',
        setTimeout: 'readonly',
        clearTimeout: 'readonly'
      }
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': 'error'
    }
  },
  // Exclure les fichiers buildés et générés
  {
    ignores: [
      '**/dist/**',
      '**/build/**',
      '**/*.min.js',
      '**/vendor/**',
      '**/node_modules/**',
      'plugin/assets/js/pdf-builder-react.js'
    ]
  }
];