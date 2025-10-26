# PDF Builder Pro - Structure du Projet

## 📁 Organisation des dossiers

Ce projet est maintenant organisé selon une séparation claire entre le développement et le plugin WordPress déployable.

### 🏗️ Structure générale

```
wp-pdf-builder-pro/
├── plugin/                 # 📦 Plugin WordPress (à déployer)
│   ├── pdf-builder-pro.php # Fichier principal du plugin
│   ├── src/               # Code source PHP
│   ├── templates/         # Templates du plugin
│   ├── assets/            # CSS/JS compilés
│   ├── languages/         # Fichiers de traduction
│   ├── core/              # Code core PHP
│   ├── database/          # Schémas base de données
│   ├── lib/               # Librairies tierces
│   ├── vendor/            # Dépendances Composer
│   ├── bootstrap.php      # Bootstrap du plugin
│   ├── stubs.php          # Stubs PHP
│   ├── composer.json      # Configuration Composer
│   └── composer.lock      # Lock Composer
├── dev/                   # 🔧 Outils de développement
│   ├── config/            # Configuration webpack, etc.
│   ├── resources/         # Ressources JavaScript
│   └── tools/             # Outils de développement
├── build/                 # 🚀 Scripts de déploiement
│   ├── deploy-plugin.ps1  # Déploiement du plugin
│   └── ftp-deploy-*.ps1   # Scripts FTP
├── assets/                # 📁 Assets source (ancien - conservé)
├── src/                   # 📁 Code PHP source (ancien - conservé)
├── node_modules/          # 📦 Dépendances Node.js
├── tests/                 # 🧪 Tests unitaires
├── docs/                  # 📚 Documentation
├── temp/                  # 🗂️ Fichiers temporaires
└── package.json           # 📋 Configuration Node.js
```

## 🚀 Workflow de développement

### 1. Développement
- Modifier les fichiers source dans `assets/js/src/` (JavaScript)
- Modifier les fichiers PHP dans `src/` ou `plugin/src/`
- Les assets compilés vont dans `plugin/assets/js/dist/`

### 2. Build
```bash
npm run build  # Compile les assets dans plugin/assets/
```

### 3. Test local
- Le dossier `plugin/` peut être copié dans `wp-content/plugins/` pour les tests

### 4. Déploiement
```powershell
.\build\deploy-plugin.ps1  # Déploie uniquement le contenu de plugin/
```

## 📦 Contenu du plugin déployable

Le dossier `plugin/` contient **uniquement** les fichiers nécessaires au fonctionnement du plugin WordPress :

- ✅ Code PHP (src/, core/, database/, lib/, vendor/)
- ✅ Templates (templates/)
- ✅ Assets compilés (assets/)
- ✅ Traductions (languages/)
- ✅ Configuration (composer.json/lock, bootstrap.php, stubs.php)

## 🔧 Scripts disponibles

- `npm run build` : Compilation des assets JavaScript
- `npm run dev` : Compilation en mode développement
- `npm run watch` : Surveillance et recompilation automatique
- `.\build\deploy-plugin.ps1` : Déploiement FTP du plugin

## 📋 Migration

Les anciens dossiers (`assets/`, `src/`) sont conservés pour compatibilité, mais le build génère maintenant dans `plugin/assets/`. Cette séparation permet de :

1. **Déployer uniquement le nécessaire** sur les serveurs WordPress
2. **Garder les outils de développement** locaux
3. **Faciliter la maintenance** et les déploiements
4. **Éviter les fichiers inutiles** en production