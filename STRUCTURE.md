# 📦 PDF Builder Pro V2 - Structure Complète

## 🎯 Vue d'ensemble

**V2** est une refonte complète du PDF Builder Pro qui combine:
- **Frontend moderne** (React 18 + TypeScript) dans `src/`
- **Backend WordPress** (PHP) dans `plugin/`
- **Outils de build** (Webpack, npm) dans la racine
- **Documentation** dans `docs/`

---

## 📁 Structure de répertoires

```
wp-pdf-builder-pro-V2/
│
├── 📂 src/                              # Code source React/TypeScript
│   ├── js/react/
│   │   ├── index.tsx                    ⭐ Entry point
│   │   ├── components/
│   │   │   ├── PDFBuilderApp.tsx
│   │   │   ├── ErrorFallback.tsx
│   │   │   └── index.ts
│   │   ├── hooks/
│   │   │   ├── usePDFEditor.ts
│   │   │   └── index.ts
│   │   └── utils/
│   │       ├── logger.ts
│   │       ├── errorBoundary.ts
│   │       ├── dom.ts
│   │       └── index.ts
│   └── css/
│       └── main.css
│
├── 📂 plugin/                           # Plugin WordPress
│   ├── 🔵 pdf-builder-pro.php           # Fichier principal
│   ├── bootstrap.php                    # Initialisation
│   ├── composer.json
│   ├── woocommerce-stubs.php
│   │
│   ├── 📂 includes/                     # Classes utilitaires
│   │   ├── AdminPages.php               # Enregistrement pages
│   │   ├── ReactAssetsV2.php            # Enregistrement assets
│   │   └── ...
│   │
│   ├── 📂 pages/                        # Pages d'admin
│   │   ├── welcome.php                  # Accueil
│   │   ├── admin-editor.php             # Éditeur
│   │   └── settings.php                 # Paramètres
│   │
│   ├── 📂 assets/                       # Assets compilés
│   │   ├── js/
│   │   │   ├── pdf-builder-react.min.js
│   │   │   ├── vendors.min.js
│   │   │   ├── vendors.min.js.gz
│   │   │   └── pdf-builder-react-wrapper.js
│   │   ├── css/
│   │   │   └── pdf-builder-react.min.css
│   │   ├── images/
│   │   └── templates/
│   │
│   ├── 📂 src/                          # Code PHP backend
│   ├── 📂 api/
│   ├── 📂 config/
│   ├── 📂 core/
│   ├── 📂 analytics/
│   └── 📂 languages/
│
├── 📂 build/                            # Scripts de déploiement
│   ├── deploy-simple.ps1
│   ├── deploy-all.ps1
│   ├── clean-remote.ps1
│   └── DEPLOYMENT.md
│
├── 📂 docs/                             # Documentation
│   ├── deployment/
│   ├── developer/
│   ├── user/
│   ├── reports/
│   └── migration/
│
├── 📂 dist/                             # Build output (peut être ignoré)
│   └── ...
│
├── ⚙️ Configuration
│   ├── webpack.config.cjs               # Build config
│   ├── tsconfig.json                    # TypeScript config
│   ├── package.json                     # Dépendances npm
│   ├── babel.config.js
│   ├── .eslintrc.json
│   └── workspace.code-workspace         # VS Code workspace
│
├── 📖 Documentation
│   ├── README.md                        # Ce fichier
│   ├── STATUS.md                        # État du projet
│   ├── DEPLOYMENT.md
│   └── CHANGELOG.md
│
└── 📝 Root files
    ├── .gitignore
    ├── .npmrc
    └── ...
```

---

## 🚀 Démarrer rapidement

### Installation

```bash
cd wp-pdf-builder-pro-V2
npm install --legacy-peer-deps
```

### Développement

```bash
# Mode watch (recompile à chaque modification)
npm run watch

# Build manual
npm run build

# Lint
npm run lint
```

### Assets générés

Après build, les assets sont générés dans:
```
plugin/assets/
├── js/
│   ├── pdf-builder-react.min.js          (8.97 KB)
│   ├── vendors.min.js                    (137 KB)
│   ├── vendors.min.js.gz                 (45 KB)
│   └── pdf-builder-react-wrapper.js      (2.8 KB)
└── css/
    └── pdf-builder-react.min.css         (1.2 KB)
```

---

## 🔌 Pages WordPress

### Enregistrement automatique

Les pages d'admin sont enregistrées automatiquement via les classes:
- `AdminPages.php` - Ajoute les pages dans le menu
- `ReactAssetsV2.php` - Charge les assets React

### Pages disponibles

| URL | Description |
|-----|-------------|
| `/wp-admin/admin.php?page=pdf-builder` | 🏠 Accueil |
| `/wp-admin/admin.php?page=pdf-builder-react-editor` | 📝 Éditeur |
| `/wp-admin/admin.php?page=pdf-builder-settings` | ⚙️ Paramètres |

---

## 📊 Architecture React

### Entry Point (`src/js/react/index.tsx`)

```typescript
// Module level logging
const logger = createLogger('PDFBuilderReact');

// Only function is protected with try-catch
function initPDFBuilderReact(containerId: string): boolean {
  try {
    // Initialization logic
    return true;
  } catch (error) {
    logger.error('Error:', error);
    return false;
  }
}

// Export to window
window.pdfBuilderReact = { initPDFBuilderReact, version: '2.0.0', logger };
```

### Composants

- **PDFBuilderApp** - Composant principal
- **ErrorFallback** - Gestion des erreurs
- **usePDFEditor** - Hook personnalisé

### Utils

- **logger** - Logging unifié
- **dom** - Utilities DOM
- **errorBoundary** - Gestion erreurs

---

## 🛠️ Build & Déploiement

### Build local

```bash
npm run build
```

Génère automatiquement:
- Assets React compilés dans `plugin/assets/`
- CSS dans `plugin/assets/css/`
- JS dans `plugin/assets/js/`

### Déploiement WordPress

```bash
# Via PowerShell script
cd build
./deploy-simple.ps1
```

Voir [DEPLOYMENT.md](./DEPLOYMENT.md) pour les détails.

---

## 📈 Différences V1 vs V2

| Aspect | V1 | V2 |
|--------|----|----|
| Structure | Monolithique | Modulaire |
| React | Version 16 | Version 18 |
| TypeScript | Minimal | Strict |
| Bundle size | 584 KB | 147 KB |
| Try-catch global | ✗ | ✓ (Fixed) |
| Error handling | Enrobé | Localisé |
| CSS | Inline | Modules |
| Webpack | Simple | Optimisé |
| Admin pages | Aucune | 3 pages |

---

## 📝 Tâches courantes

### Ajouter un nouveau composant

```bash
# 1. Créer le fichier
touch src/js/react/components/MonComposant.tsx

# 2. Implémenter
# Voir PDFBuilderApp.tsx comme exemple

# 3. Exporter dans index.ts
echo "export { MonComposant } from './MonComposant';" >> src/js/react/components/index.ts

# 4. Builder
npm run build
```

### Modifier les styles

Éditer `src/css/main.css` et relancer le build:

```bash
npm run build
```

### Déboguer en dev

```bash
npm run watch
# Ouvrir http://localhost:8000/plugin/pages/test.html
```

---

## 🐛 Dépannage

### Build échoue

```bash
# Réinstaller les dépendances
rm -r node_modules package-lock.json
npm install --legacy-peer-deps
npm run build
```

### Assets non trouvés

Vérifier que les chemins dans `ReactAssetsV2.php` correspondent aux chemins réels.

### React ne se charge pas

1. Ouvrir la console F12
2. Vérifier `window.pdfBuilderReact`
3. Consulter les logs avec `window.pdfBuilderReact.logger`

---

## 📞 Support

- **Documentation**: Voir `docs/` 
- **Déploiement**: Voir [DEPLOYMENT.md](./DEPLOYMENT.md)
- **État**: Voir [STATUS.md](./STATUS.md)

---

## 🎯 Prochaines étapes

- [ ] Intégrer les composants Canvas
- [ ] Implémenter l'API client
- [ ] Ajouter les tests (Jest)
- [ ] Intégrer l'authentification
- [ ] Ajouter les templates utilisateur

---

**Version**: 2.0.0  
**React**: 18.3.1  
**TypeScript**: 5.3  
**Webpack**: 5.104  
**Status**: ✅ Prêt au déploiement
