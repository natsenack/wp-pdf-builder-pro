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
    ├── pdf-builder-react-wrapper.js
    ├── ajax-throttle.js
    ├── notifications.js
    ├── pdf-builder-wrap.js
    ├── pdf-builder-init.js
    ├── pdf-preview-api-client.js
    └── pdf-preview-integration.js
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
├── pdf-builder-react-wrapper.min.js # Wrapper React
├── ajax-throttle.min.js          # Limitation appels AJAX
├── notifications.min.js          # Système notifications
├── pdf-builder-wrap.min.js       # Utilitaires wrapper
├── pdf-builder-init.min.js       # Initialisation générale
├── pdf-preview-api-client.min.js # Client API preview
└── pdf-preview-integration.min.js # Intégration preview
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

## Fonctionnalités des modules

### Scripts d'administration
- **ajax-throttle.js** : Limitation des appels AJAX pour éviter la surcharge serveur
- **notifications.js** : Système de notifications utilisateur avec UI basique
- **pdf-builder-wrap.js** : Utilitaires généraux (debounce, throttle, DOM helpers)
- **pdf-builder-init.js** : Initialisation générale et détection de contexte
- **pdf-preview-api-client.js** : Client API pour les appels de génération de preview PDF
- **pdf-preview-integration.js** : Intégration des fonctionnalités de preview

### Scripts React
- **pdf-builder-react-init.js** : Initialisation de l'application React
- **pdf-builder-react-wrapper.js** : Wrapper et gestion du cycle de vie React

### Scripts paramètres
- **settings-tabs.js** : Navigation par onglets dans les paramètres
- **settings-main.js** : Logique principale des paramètres
- **canvas-settings.js** : Paramètres spécifiques au canvas d'édition