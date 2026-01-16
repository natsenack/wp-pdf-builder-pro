# Architecture JavaScript - PDF Builder Pro V2

## Structure des fichiers

### Sources (`src/js/`)
Tous les fichiers JavaScript source sont maintenant dans `src/js/` et compilés automatiquement par Webpack.

```
src/js/
├── react/           # Composants React (TypeScript)
│   ├── index.tsx
│   ├── PDFBuilder.tsx
│   └── ...
└── admin/           # Scripts d'administration (JavaScript)
    ├── settings-tabs.js
    ├── settings-main.js
    ├── canvas-settings.js
    ├── pdf-builder-react-init.js
    └── pdf-builder-react-wrapper.js
```

### Builds (`plugin/assets/js/`)
Les fichiers compilés et minifiés sont générés automatiquement dans `plugin/assets/js/`.

```
plugin/assets/js/
├── pdf-builder-react.min.js      # Bundle React principal
├── react-vendor.min.js           # Dépendances React
├── runtime.min.js                # Runtime Webpack
├── settings-tabs.min.js          # Onglets paramètres
├── settings-main.min.js          # Paramètres principaux
├── canvas-settings.min.js        # Paramètres canvas
├── pdf-builder-react-init.min.js # Initialisation React
└── pdf-builder-react-wrapper.min.js # Wrapper React
```

## Workflow de développement

### 1. Modification des sources
- Éditez les fichiers dans `src/js/admin/` ou `src/js/react/`
- Pour React : utilisez TypeScript dans `src/js/react/`
- Pour Admin : utilisez JavaScript dans `src/js/admin/`

### 2. Build automatique
```bash
npm run build  # Compile tous les JS avec Webpack
```

### 3. Déploiement
```bash
cd build
.\deploy-simple.ps1  # Déploie automatiquement
```

## Avantages de cette architecture

✅ **Plus de copies manuelles** - Tout est automatisé
✅ **Source maps** - Debug facilité en développement
✅ **Minification** - Optimisation production
✅ **Intégration VS Code** - Extension Webpack installée
✅ **Versioning automatique** - Cache busting
✅ **Git propre** - Sources séparées des builds

## Extension VS Code

L'extension **Webpack** (`jeremyrajan.webpack`) est installée pour :
- Créer des configurations Webpack
- Support ES6 avec Babel
- Intégration transparente avec VS Code