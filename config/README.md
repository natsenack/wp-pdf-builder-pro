# 📁 config/ - Configuration du Projet

Ce dossier contient toute la configuration du projet, séparée par environnement et type.

## 📂 Structure

### `dev/`
Configuration des outils de développement
- `.eslintignore` - Fichiers ignorés par ESLint
- `.eslintrc.js` - Configuration ESLint pour JavaScript/TypeScript
- `.prettierignore` - Fichiers ignorés par Prettier
- `.prettierrc` - Configuration Prettier pour le formatage
- `tsconfig.json` - Configuration TypeScript
- `phpstan.neon` - Configuration PHPStan (analyse statique PHP)
- `phpunit.xml` - Configuration PHPUnit (tests unitaires PHP)

### `build/`
Configuration des outils de build
- `webpack.config.js` - Configuration Webpack pour la compilation

## 🚀 Utilisation

### Configuration ESLint + Prettier
```bash
# Vérifier le code
npx eslint src/ --ext .js,.jsx,.ts,.tsx

# Formater le code
npx prettier --write src/
```

### Analyse PHP
```bash
# Analyse statique PHP
vendor/bin/phpstan analyse

# Tests unitaires PHP
vendor/bin/phpunit
```

### Build des assets
```bash
# Compiler les assets
npx webpack --config config/build/webpack.config.js
```

## 📝 Notes

Ces fichiers sont utilisés uniquement en développement et ne sont **jamais** déployés en production.

---
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">d:\wp-pdf-builder-pro\config\README.md