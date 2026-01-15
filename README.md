# PDF Builder Pro V2

Refonte complète et propre du PDF Builder Pro pour WordPress avec architecture moderne.

## Structure

```
src/
├── js/react/
│   ├── index.tsx                 # Entry point principal
│   ├── components/
│   │   ├── PDFBuilderApp.tsx     # Composant principal
│   │   ├── ErrorFallback.tsx     # Composant d'erreur
│   │   └── index.ts
│   ├── hooks/
│   │   ├── usePDFEditor.ts       # Hook personnalisé
│   │   └── index.ts
│   └── utils/
│       ├── logger.ts             # Logging utility
│       ├── errorBoundary.ts      # Error handling
│       ├── dom.ts                # DOM utilities
│       └── index.ts
└── css/
    └── main.css                  # Styles principaux
```

## Avantages de V2

✅ **Architecture modulaire** - Séparation claire des responsabilités
✅ **TypeScript strict** - Type-safe tout au long
✅ **Logging propre** - Logger utility réutilisable
✅ **Gestion d'erreurs robuste** - Pas de try-catch enrobant tout
✅ **Webpack 5 moderne** - Configuration optimisée
✅ **Pas d'extensions bloquantes** - Module libre d'erreurs externes

## Installation

```bash
cd wp-pdf-builder-pro-V2
npm install
```

## Développement

```bash
npm run dev          # Build en development mode
npm run watch        # Watch mode
npm run build        # Build production
npm run lint         # Lint code
```

## API

```javascript
// Initialize in container
window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');

// Access logger
window.pdfBuilderReact.logger.info('Message');

// Check version
console.log(window.pdfBuilderReact.version); // "2.0.0"
```
