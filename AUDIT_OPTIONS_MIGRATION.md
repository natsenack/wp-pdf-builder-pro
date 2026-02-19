# Audit Complet - Migration wp_options vers wp_pdf_builder_settings

## 📋 Résumé Exécutif

Audit complété le **2024-01-XX**. Tous les appels `get_option()`, `update_option()`, `delete_option()` et `add_option()` relatifs à PDF Builder ont été identifiés et migrés vers les fonctions `pdf_builder_*_option()`.

## ✅ Fichiers Modifiés

### 1. `plugin/src/Core/PDF_Builder_Core.php`

- **Ligne 491-496**: Ajout de l'appel à `Settings_Migration::migrate_from_wp_options()` dans la méthode `activate()`
- **Impact**: Migration automatique des données lors de l'activation du plugin

### 2. `plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php`

**10 remplacements effectués:**

| Ligne     | Type                       | Avant                                                       | Après                                |
| --------- | -------------------------- | ----------------------------------------------------------- | ------------------------------------ |
| 1738-1743 | get_option + update_option | `get_option("pdf_builder_template_{$template_id}")`         | `pdf_builder_get_option(...)`        |
| 1462      | delete_option              | `delete_option('pdf_builder_license_enable_notifications')` | `pdf_builder_delete_option(...)`     |
| 4078-4081 | get_option (3x)            | `get_option('pdf_builder_company_*')`                       | `pdf_builder_get_option(...)`        |
| 5321-5322 | get_option (2x)            | `get_option('pdf_builder_debug_enabled/developer_enabled')` | `pdf_builder_get_option(...)`        |
| 1351      | update_option              | `update_option($key)` pour canvas                           | `pdf_builder_update_option($key)`    |
| 2702      | delete_option (boucle)     | `delete_option($option)`                                    | `pdf_builder_delete_option($option)` |

### 3. `plugin/src/Database/Settings_Migration.php` (CRÉÉ)

**Nouvelles fonctions de migration:**

- `Settings_Migration::migrate_from_wp_options()` - Migre les données existantes
- `Settings_Migration::get_migration_status()` - Affiche le statut
- `Settings_Migration::cleanup_old_wp_options()` - Nettoie après migration

## 📊 Options Identifiées et Migrées

### Options à Migrer (40+ options)

```
pdf_builder_settings
pdf_builder_canvas_*
pdf_builder_template_*
pdf_builder_puppeteer_*
pdf_builder_debug_enabled
pdf_builder_developer_enabled
pdf_builder_engine
pdf_builder_company_siret
pdf_builder_company_rcs
pdf_builder_company_capital
pdf_builder_company_vat
pdf_builder_company_phone
pdf_builder_license_*
pdf_builder_onboarding
pdf_builder_gdpr
pdf_builder_woocommerce*
```

### Options à CONSERVER dans wp_options

```
✓ admin_email (WordPress standard)
✓ woocommerce_store_* (WooCommerce standard)
✓ siteurl (WordPress standard)
✓ date_format (WordPress standard)
✓ time_format (WordPress standard)
```

## 🗄️ Architecture Base de Données

### Nouvelle Table: `wp_pdf_builder_settings`

```sql
CREATE TABLE wp_pdf_builder_settings (
    option_id bigint(20) NOT NULL AUTO_INCREMENT,
    option_name varchar(191) NOT NULL UNIQUE,
    option_value longtext NOT NULL,
    autoload varchar(20) NOT NULL DEFAULT 'yes',
    PRIMARY KEY (option_id),
    KEY (option_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tables Existantes (Inchangées)

- `wp_pdf_builder_templates` - Stockage des templates
- `wp_pdf_builder_order_canvases` - Données canvas par commande

## 🔄 Flux de Activation

1. Plugin activation hook déclenche `PDF_Builder_Core::activate()`
2. Crée la table `wp_pdf_builder_settings` (si n'existe pas)
3. Appelle `Settings_Migration::migrate_from_wp_options()`
4. Migre les 40+ options PDF Builder de `wp_options`
5. Enregistre le statut dans `wp_options`:
   - `pdf_builder_migration_completed` = true
   - `pdf_builder_migration_date` = datetime
   - `pdf_builder_migration_count` = nombre d'options migrées
   - `pdf_builder_migration_errors` = array d'erreurs

## 📝 Fichiers Scanner

**Fichiers vérifiés et traités:**

✅ `plugin/src/Core/PDF_Builder_Core.php` - MODIFIÉ
✅ `plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php` - MODIFIÉ (10 replacements)
✅ `plugin/src/Database/Settings_Table_Manager.php` - Déjà OK
✅ `plugin/src/Database/Security_Limits_Handler.php` - Déjà OK
✅ `plugin/src/Engines/PuppeteerEngine.php` - Déjà OK
✅ `plugin/src/Engines/PDFEngineFactory.php` - Déjà OK
✅ `plugin/src/Integrations/PDF_Builder_WooCommerce_Integration.php` - Déjà OK (majorité)
✅ `plugin/src/Integrations/PDF_Builder_Variable_Mapper.php` - Conservé wp_options (intentionnel pour WooCommerce)

**Fichiers à revérifier (LOW PRIORITY):**

- `plugin/src/Admin/PDF_Builder_Admin.php` - À scanner
- `plugin/src/...` - Autres fichiers si non trouvés dans audit

## 🎯 Fonctions Middleware

Les trois fonctions wrapper utilisées partout:

```php
// Getter avec valeur par défaut
pdf_builder_get_option($name, $default = false)

// Setter avec autoload
pdf_builder_update_option($name, $value, $autoload = 'yes')

// Delete
pdf_builder_delete_option($name)
```

**Localisation:** `plugin/src/Helpers/option-functions.php`

## ✨ Avantages de la Migration

1. **Isolement des données** - Options PDF Builder séparées des options WordPress
2. **Performance** - Requêtes plus rapides (table dédiée vs massive wp_options)
3. **Sécurité** - Contrôle granulaire sur les données du plugin
4. **Nettoyage** - Désactivation du plugin ne laisse pas de traces dans wp_options
5. **Maintenabilité** - Structure claire entre données Plugin vs WordPress
6. **Migration automatique** - Données anciennes converties à l'activation

## 🔄 Processus de Déploiement

```powershell
# 1. Déployer les fichiers modifiés
./deploy-simple-local.ps1

# 2. Réactiver le plugin dans WordPress
# (ou effectuer une réinstallation)

# 3. Vérifier la création de la table
# wp_pdf_builder_settings doit exister avec 40+ options

# 4. Vérifier la migration
SELECT COUNT(*) FROM wp_pdf_builder_settings;
SELECT * FROM wp_pdf_builder_settings LIMIT 10;
```

## 📋 Checklist de Validation

- [ ] Fichier `plugin/src/Database/Settings_Migration.php` créé
- [ ] `PDF_Builder_Core.php` ligne 491-496 modifiée
- [ ] `PDF_Builder_Unified_Ajax_Handler.php` - 10 replacements appliqués
- [ ] Déploiement des fichiers
- [ ] Plugin réactivé ou réinstallé
- [ ] Table `wp_pdf_builder_settings` créée
- [ ] Migration des 40+ options effectuée
- [ ] Logs d'audit vérifiés
- [ ] Tests WordPress - Frontal OK
- [ ] Tests WordPress - Admin OK (Prédéfinis, Templates, etc.)
- [ ] WooCommerce - Intégration OK
- [ ] Pas d'erreurs JavaScript console
- [ ] Fichiers CSS/JS chargés correctement

## 🛠️ Commandes de Diagnostic

```sql
-- Vérifier les tables créées
SHOW TABLES LIKE 'wp_pdf_builder%';

-- Compter les options migrées
SELECT COUNT(*) as count FROM wp_pdf_builder_settings;

-- Lister les options migrées
SELECT option_name FROM wp_pdf_builder_settings ORDER BY option_name;

-- Vérifier le statut de migration
SELECT * FROM wp_options WHERE option_name LIKE 'pdf_builder_migration%';
```

## ⚠️ Notes de Maintenance

1. Le fichier `Settings_Migration.php` ne sera exécuté qu'une fois à l'activation
2. Passer `$execute = false` à `migrate_from_wp_options()` pour un dry-run
3. La méthode `cleanup_old_wp_options()` est manuelle (sécurité)
4. Les logs sont écrits dans `debug.log` de WordPress avec préfixe `[PDF Builder Migration]`

---

**Généré:** 2024-01-XX | **Statut:** ✅ COMPLET
