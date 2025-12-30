# 📡 SYSTÈME AJAX UNIFIÉ - PDF Builder Pro

> **Phase 1 Terminée** - Système AJAX centralisé et documenté

---

## 🎯 OBJECTIF

Créer un système AJAX unifié qui élimine la fragmentation et centralise la gestion de tous les endpoints AJAX du plugin PDF Builder Pro.

---

## 🏗️ ARCHITECTURE

### Dispatcher Principal
**Fichier** : `plugin/src/AJAX/Ajax_Dispatcher.php`
**Pattern** : Singleton
**Responsabilités** :
- Routage automatique des requêtes AJAX
- Validation centralisée (permissions, nonces)
- Gestion d'erreurs standardisée
- Logging unifié

### Structure des Handlers
```php
$handler_config = [
    'handler' => $instance,           // Instance du handler
    'method' => 'handle_method',      // Méthode à appeler
    'capability' => 'manage_options'  // Permission requise
];
```

---

## 📋 ENDPOINTS AJAX DOCUMENTÉS

### ⚙️ Paramètres (Settings)
| Action | Handler | Description |
|--------|---------|-------------|
| `pdf_builder_save_all_settings` | `PDF_Builder_Settings_Ajax_Handler::handle` | Sauvegarde tous les paramètres |
| `pdf_builder_save_template` | `PDF_Builder_Template_Ajax_Handler::handle_save` | Sauvegarde un template |
| `pdf_builder_load_template` | `PDF_Builder_Template_Ajax_Handler::handle_load` | Charge un template |
| `pdf_builder_delete_template` | `PDF_Builder_Template_Ajax_Handler::handle_delete` | Supprime un template |

### 🎨 Aperçu (Preview)
| Action | Handler | Description |
|--------|---------|-------------|
| `pdf_builder_generate_preview` | `PdfBuilderPreviewAjax::generatePreview` | Génère l'aperçu PDF |
| `pdf_builder_get_preview_data` | `PdfBuilderPreviewAjax::get_preview_data` | Récupère les données d'aperçu |

### 📄 Templates
| Action | Handler | Description |
|--------|---------|-------------|
| `pdf_builder_create_from_predefined` | `PdfBuilderTemplatesAjax::createFromPredefined` | Crée depuis template prédéfini |
| `pdf_builder_load_predefined_into_editor` | `PdfBuilderTemplatesAjax::loadPredefinedIntoEditor` | Charge template prédéfini |
| `pdf_builder_load_template_settings` | `PdfBuilderTemplatesAjax::loadTemplateSettings` | Charge paramètres template |
| `pdf_builder_save_template_settings` | `PdfBuilderTemplatesAjax::saveTemplateSettings` | Sauvegarde paramètres template |
| `pdf_builder_set_default_template` | `PdfBuilderTemplatesAjax::setDefaultTemplate` | Définit template par défaut |
| `pdf_builder_delete_template` | `PdfBuilderTemplatesAjax::deleteTemplate` | Supprime template |
| `pdf_builder_save_order_status_templates` | `PdfBuilderTemplatesAjax::saveOrderStatusTemplates` | Sauvegarde templates par statut |

### 🛠️ Maintenance
| Action | Handler | Description |
|--------|---------|-------------|
| `pdf_builder_clear_cache` | `Ajax_Dispatcher::handle_clear_cache` | Vide le cache |
| `pdf_builder_clear_all_cache` | `Ajax_Dispatcher::handle_clear_all_cache` | Vide tout le cache |
| `pdf_builder_optimize_database` | `Ajax_Dispatcher::handle_optimize_database` | Optimise la base de données |

---

## 🔧 UTILISATION

### Pour les Développeurs
```php
// Ajouter un nouvel endpoint
$this->handlers['my_custom_action'] = [
    'handler' => new My_Custom_Handler(),
    'method' => 'handle_request',
    'capability' => 'manage_options'
];
```

### Pour les Intégrateurs Frontend
```javascript
// Exemple d'appel AJAX
jQuery.post(ajaxurl, {
    action: 'pdf_builder_save_all_settings',
    nonce: pdf_builder_ajax.nonce,
    settings: settingsData
}, function(response) {
    if (response.success) {
        console.log('Paramètres sauvegardés');
    }
});
```

---

## 📊 RÉPONSES STANDARDISÉES

### Succès
```json
{
    "success": true,
    "data": {
        "message": "Opération réussie",
        "timestamp": 1735320000,
        "custom_data": "..."
    }
}
```

### Erreur
```json
{
    "success": false,
    "data": {
        "message": "Description de l'erreur",
        "code": 400,
        "timestamp": 1735320000
    }
}
```

---

## 🔒 SÉCURITÉ

- **Permissions** : Vérifiées automatiquement par le dispatcher
- **Nonces** : Validés si fournis dans la requête
- **Sanitisation** : À la charge de chaque handler
- **Logging** : Erreurs automatiquement loggées en debug mode

---

## 📈 BÉNÉFICES

✅ **Centralisation** : Un seul point d'entrée pour tous les AJAX
✅ **Maintenance** : Plus facile d'ajouter/modifier des endpoints
✅ **Débogage** : Logging et erreurs standardisées
✅ **Sécurité** : Validation automatique des permissions
✅ **Performance** : Réduction de la duplication de code
✅ **Évolutivité** : Architecture extensible pour futures fonctionnalités

---

## 🚀 PROCHAINES ÉTAPES

1. **Phase 2** : Refactoring Bootstrap (diviser en modules)
2. **Tests AJAX** : Créer suite de tests pour tous les endpoints
3. **Documentation API** : Générer documentation automatique
4. **Monitoring** : Ajouter métriques de performance AJAX

---

*Document mis à jour le 30 décembre 2025 - Phase 1 terminée*
   - Parsing des données (JSON → array aplati)
   - Validation et sanitisation par type de champ
   - Sauvegarde dans les options WordPress appropriées

3. **Confirmation**
   - Réponse JSON avec statut de succès
   - Mise à jour de l'interface utilisateur

## Options WordPress utilisées

| Option | Contenu | Handler responsable |
|--------|---------|-------------------|
| `pdf_builder_settings` | Paramètres principaux (debug, cache, etc.) | Settings_Ajax_Handler |
| `pdf_builder_canvas_settings` | Paramètres canvas et debug | Settings_Ajax_Handler |
| `pdf_builder_cache_*` | Paramètres de cache | cache-handlers.php |
| `pdf_builder_last_maintenance` | Timestamp dernière maintenance | cache-handlers.php |
| `wp_pdf_builder_templates` | Table des templates | Templates_Ajax.php |

## Évolution et Maintenance

### Code déprécié
- Le dispatcher dans `pdf-builder-pro.php` est déprécié
- Éviter d'ajouter de nouveaux handlers dans l'ancien système
- Préférer le système unifié dans `Ajax_Handlers.php`

### Bonnes pratiques
- Tous les nouveaux paramètres doivent passer par le handler principal
- Utiliser les types de champs définis (`text_fields`, `bool_fields`, etc.)
- Documenter les nouvelles options dans ce fichier

## Debugging

Pour déboguer les problèmes de sauvegarde :
1. Vérifier les logs PHP pour les messages `PDF BUILDER AJAX HANDLER`
2. Vérifier les logs JavaScript dans la console du navigateur
3. Vérifier les options WordPress via phpMyAdmin ou WP-CLI

## Tests

- Tests unitaires dans `tests/AjaxTestCase.php`
- Tests manuels via `tests/manual-test.php`
- Validation des données sauvegardées</content>
<parameter name="filePath">i:\wp-pdf-builder-pro\docs\AJAX_SYSTEM.md