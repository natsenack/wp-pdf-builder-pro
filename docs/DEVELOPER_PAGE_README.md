# Page Développeur - PDF Builder Pro

## Vue d'ensemble
La page développeur est un outil de débogage avancé accessible uniquement au développeur principal (utilisateur ID 1) et uniquement en mode développement.

## Activation
Pour activer la page développeur, ajoutez cette ligne dans votre `wp-config.php` :

```php
define('PDF_BUILDER_DEV_MODE', true);
```

## Fonctionnalités

### 📊 Infos Système
- Version WordPress, PHP, MySQL
- Limites mémoire et exécution
- Constantes WordPress (WP_DEBUG, etc.)
- Chemins d'installation

### 📝 Logs & Erreurs
- Logs WordPress (`debug.log`)
- Logs PHP (`error_log`)
- Bouton pour nettoyer les logs

### ⚙️ Options Plugin
- Toutes les options WordPress du plugin
- Cache des options
- Valeurs actuelles et types de données

### 💻 Console PHP
- Exécution de code PHP en temps réel
- Accès aux variables globales WordPress
- ⚠️ **DANGER** : Utiliser avec précaution !

### 🗄️ Base de Données
- Informations de connexion
- Tables du plugin
- Statistiques des tables

## Sécurité
- Accessible uniquement à l'utilisateur ID 1 (premier admin)
- Nécessite la constante `PDF_BUILDER_DEV_MODE = true`
- Nonce de sécurité sur toutes les actions POST

## Production
À la fin du développement, vous pouvez :

1. **Supprimer complètement** :
   - Supprimer `includes/developer-page.php`
   - Supprimer les lignes correspondantes dans `class-pdf-builder-admin.php`

2. **Désactiver simplement** :
   - Supprimer ou commenter `define('PDF_BUILDER_DEV_MODE', true);` dans `wp-config.php`

3. **Réactiver facilement** :
   - La page reste dans le code, il suffit de remettre la constante pour la déboguer plus tard

## Utilisation recommandée
- Gardez la constante `PDF_BUILDER_DEV_MODE` dans votre environnement de développement
- Supprimez-la en production
- Si vous devez déboguer en production, activez-la temporairement puis désactivez-la