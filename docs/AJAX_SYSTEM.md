# Système de Sauvegarde Centralisé - PDF Builder Pro

## Vue d'ensemble

Le système de sauvegarde des paramètres est **centralisé et unifié** autour de quelques handlers principaux, avec une séparation claire des responsabilités.

## Architecture

### 1. Handler Principal - Paramètres Généraux
**Fichier**: `plugin/src/AJAX/Ajax_Handlers.php`
**Classe**: `PDF_Builder_Settings_Ajax_Handler`
**Action AJAX**: `wp_ajax_pdf_builder_save_all_settings`
**Déclencheur**: Bouton flottant de sauvegarde (settings-tabs.js)

#### Responsabilités
- Sauvegarde de tous les paramètres principaux
- Collecte des données de tous les formulaires de paramètres
- Validation et sanitisation des données
- Stockage dans les options WordPress appropriées

#### Stockage
- `pdf_builder_settings`: Array des paramètres principaux
- `pdf_builder_canvas_settings`: Array des paramètres canvas et debug

### 2. Handlers Spécialisés

#### Templates
**Fichier**: `plugin/src/AJAX/PDF_Builder_Templates_Ajax.php`
**Actions**: `pdf_builder_save_template`, `pdf_builder_load_template`, `pdf_builder_delete_template`
**Stockage**: Table `wp_pdf_builder_templates`

#### Cache et Maintenance
**Fichier**: `plugin/src/AJAX/cache-handlers.php`
**Actions**: `pdf_builder_clear_all_cache`, `pdf_builder_optimize_database`, etc.
**Stockage**: Options spécifiques (`pdf_builder_cache_*`, `pdf_builder_last_maintenance`, etc.)

#### Autres domaines
- GDPR: `PDF_Builder_GDPR_Manager.php`
- Licences: Gestionnaire de licences dédié
- Diagnostics: Outil de diagnostic

## Flux de Sauvegarde

1. **Frontend** (settings-tabs.js)
   - Bouton flottant cliqué
   - Collecte des données de tous les formulaires
   - Envoi via AJAX avec action `pdf_builder_save_all_settings`

2. **Backend** (Ajax_Handlers.php)
   - Validation de la requête et nonce
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