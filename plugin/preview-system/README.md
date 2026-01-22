# Preview System - PDF Builder Pro

## Structure

Le système d'aperçu a été centralisé dans ce dossier pour mieux organiser les fonctionnalités.

### Dossiers

- **php/** - Code serveur PHP
  - `PreviewSystem.php` - Système d'aperçu ultra-minimal
  - `PreviewImageAPI.php` - API d'image d'aperçu (stub)
  - `PreviewAjaxHandler.php` - Gestionnaire AJAX (deprecated)

- **js/** - Code client JavaScript/jQuery
  - `pdf-preview-api-client.js` - Client API d'aperçu (stub)

### État du système

⚠️ **Système d'aperçu désactivé**

- ✅ Boutons d'aperçu restent intacts (UI uniquement)
- ✅ Métaboxes d'aperçu restent intacts
- ✅ Modals d'aperçu restent intacts
- ❌ Génération d'aperçu supprimée
- ❌ Pas de compilation d'aperçu en backend
- ❌ Pas d'appels AJAX

### Fichiers legacy/deprecated

Les fichiers suivants sont toujours présents dans `plugin/api/` pour la rétrocompatibilité:
- `api/PreviewSystem.php` - Redirect vers preview-system
- `api/SimplePreviewGenerator.php` - Deprecated
- `api/PreviewImageAPI.php` - Redirect vers preview-system

## Utilisation

### Charger le système

```php
// Dans bootstrap.php ou loaders
require_once PDF_BUILDER_PLUGIN_DIR . 'preview-system/index.php';
```

### API JavaScript

```javascript
// Stub API - retourne une erreur
window.pdfPreviewAPI.generateEditorPreview(data)
  .catch(err => console.log('Preview generation disabled'));
```

## Évolution future

Pour réactiver la génération:
1. Ajouter la logique dans `php/PreviewAjaxHandler.php`
2. Implémenter le rendu dans `php/PreviewImageAPI.php`
3. Mettre à jour les appels AJAX dans `js/pdf-preview-api-client.js`
