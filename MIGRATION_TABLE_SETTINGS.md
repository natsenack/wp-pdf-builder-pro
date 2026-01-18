# Migration vers Table Personnalisée `wp_pdf_builder_settings`

## ✅ Changements Complétés

### 1. Création de la Table Personnalisée
- **Fichier** : `plugin/src/Database/Settings_Table_Manager.php`
- **Table** : `wp_pdf_builder_settings`
- **Colonnes** : `option_id`, `option_name`, `option_value`, `autoload`
- **Structure** : Identique à `wp_options` pour simplifier la migration

### 2. Fonctions Wrapper Globales
Ajoutées dans `bootstrap.php` :
- `pdf_builder_get_option($option_name, $default)` - Récupère une option
- `pdf_builder_update_option($option_name, $value, $autoload)` - Met à jour une option
- `pdf_builder_delete_option($option_name)` - Supprime une option  
- `pdf_builder_get_all_options()` - Récupère tous les paramètres

### 3. Intégration Automatique à l'Activation
Dans `pdf-builder-pro.php` - Fonction `pdf_builder_activate()` :
```php
// Créer la table de paramètres personnalisée
\PDF_Builder\Database\Settings_Table_Manager::create_table();

// Migrer les données existantes depuis wp_options
$migrated = \PDF_Builder\Database\Settings_Table_Manager::is_migrated();
if (!$migrated) {
    \PDF_Builder\Database\Settings_Table_Manager::migrate_data();
}
```

### 4. Migration du Code
Remplaçage systématique dans `AjaxHandler.php` :
- `get_option('pdf_builder_settings')` → `pdf_builder_get_option('pdf_builder_settings')`
- `update_option('pdf_builder_settings')` → `pdf_builder_update_option('pdf_builder_settings')`
- `get_option('pdf_builder_order_status_templates')` → `pdf_builder_get_option('pdf_builder_order_status_templates')`
- `update_option('pdf_builder_order_status_templates')` → `pdf_builder_update_option('pdf_builder_order_status_templates')`

## 📊 Détails Techniques

### Structure de la Table
```sql
CREATE TABLE wp_pdf_builder_settings (
    option_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    option_name varchar(191) NOT NULL DEFAULT '',
    option_value longtext NOT NULL,
    autoload varchar(20) NOT NULL DEFAULT 'yes',
    PRIMARY KEY (option_id),
    UNIQUE KEY option_name (option_name)
);
```

### Classe Settings_Table_Manager
- `create_table()` - Crée la table lors de l'activation
- `migrate_data()` - Migre les données de wp_options
- `get_option()` - Récupère une option (avec désérialisation)
- `update_option()` - Sauvegarde une option (avec sérialisation)
- `delete_option()` - Supprime une option
- `get_all_options()` - Récupère tous les paramètres
- `is_migrated()` - Vérifie si la migration est complète

## 🔄 Flux de Migration

1. **À l'activation du plugin** :
   - La table `wp_pdf_builder_settings` est créée
   - Tous les paramètres `pdf_builder_*` depuis `wp_options` sont migrés
   - Les backups sont créés avec la clé `pdf_builder_backup_*`

2. **En opération normale** :
   - Les appels `pdf_builder_get_option()` récupèrent depuis la table personnalisée
   - Les appels `pdf_builder_update_option()` sauvegardent dans la table personnalisée
   - Fallback automatique vers `wp_options` si nécessaire

3. **Sérialisation** :
   - Les arrays sont sérialisés automatiquement via `maybe_serialize()`
   - Les données sont désérialisées via `maybe_unserialize()`

## 🎯 Avantages

✅ **Séparation des données** : Les paramètres PDF Builder ne polluent pas wp_options
✅ **Performance** : Requête directe sur une petite table dédiée
✅ **Maintenance** : Plus facile à gérer et nettoyer
✅ **Scalabilité** : Préparé pour de futures optimisations
✅ **Compatibilité** : Fallback automatique en cas de problème

## 📋 Checklist de Vérification

- [x] Table créée à l'activation
- [x] Données migrées automatiquement
- [x] Fonctions wrapper implémentées
- [x] AjaxHandler migrée
- [x] Sérialisation/Désérialisation fonctionnelle
- [x] Déploiement réussi
- [x] Tests de validation passés

## 🔍 Commandes SQL de Vérification

```sql
-- Voir la table
SELECT * FROM wp_pdf_builder_settings;

-- Compter les paramètres
SELECT COUNT(*) FROM wp_pdf_builder_settings;

-- Chercher une option spécifique
SELECT option_value FROM wp_pdf_builder_settings WHERE option_name = 'pdf_builder_settings';

-- Voir le contenu d'une option
SELECT option_name, LENGTH(option_value) as value_length 
FROM wp_pdf_builder_settings 
ORDER BY option_name;
```

## 📝 Notes Important

- La migration est **idempotente** : Relancer l'activation n'affecte pas les données existantes
- Les données sont sérialisées comme dans wp_options pour la compatibilité
- Le système de nonce et permissions reste inchangé
- Les backups automatiques sont créés avant chaque modification
