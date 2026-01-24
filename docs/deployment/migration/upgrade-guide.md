# 🔄 Guide Migration Données - Transfert sécurisé

Ce guide couvre les procédures de migration des données pour WP PDF Builder Pro, des mises à jour mineures aux migrations majeures avec changement d'infrastructure.

## 📋 Vue d'ensemble des migrations

### Types de migration

#### Migration mineure (patch)
- **Version** : 2.1.0 → 2.1.1
- **Risque** : Faible
- **Downtime** : 0-5 minutes
- **Procédure** : Mise à jour directe

#### Migration majeure (minor)
- **Version** : 2.1.x → 2.2.0
- **Risque** : Moyen
- **Downtime** : 15-30 minutes
- **Procédure** : Migration avec backup

#### Migration breaking (major)
- **Version** : 2.x → 3.0
- **Risque** : Élevé
- **Downtime** : 1-4 heures
- **Procédure** : Migration planifiée

### Prérequis migration

#### Checklist pré-migration
- [ ] **Backup complet** : Base + fichiers
- [ ] **Tests en staging** : Validation complète
- [ ] **Plan de rollback** : Stratégie de retour arrière
- [ ] **Communication** : Information utilisateurs
- [ ] **Monitoring** : Alertes configurées

#### Environnement de test
```bash
# Création environnement de test
cp -r /var/www/production /var/www/migration-test
cd /var/www/migration-test

# Configuration base de test
mysql -e "CREATE DATABASE wp_pdf_migration_test"
mysqldump wp_pdf_production | mysql wp_pdf_migration_test

# Tests de migration
php artisan migrate --database=migration_test
```

## 🗄️ Migration base de données

### Structure des données WP PDF Builder Pro

#### Tables principales
```sql
-- Templates PDF
wp_pdf_templates (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    content LONGTEXT,
    settings JSON,
    created_at TIMESTAMP
);

-- Générations PDF
wp_pdf_generations (
    id INT PRIMARY KEY,
    template_id INT,
    data JSON,
    status ENUM('pending', 'processing', 'completed', 'failed'),
    file_path VARCHAR(500),
    created_at TIMESTAMP
);

-- Utilisateurs et permissions
wp_pdf_user_permissions (
    user_id INT,
    template_id INT,
    permissions JSON
);
```

### Scripts de migration

#### Migration 2.1 → 2.2 (ajout colonnes)

```sql
-- migration_2.1_to_2.2.sql
START TRANSACTION;

-- Ajout colonne métadonnées templates
ALTER TABLE wp_pdf_templates
ADD COLUMN metadata JSON DEFAULT NULL AFTER settings;

-- Ajout index pour performance
CREATE INDEX idx_template_status ON wp_pdf_templates(status);
CREATE INDEX idx_generation_created ON wp_pdf_generations(created_at);

-- Migration données existantes
UPDATE wp_pdf_templates
SET metadata = JSON_OBJECT(
    'version', '2.2',
    'migrated_at', NOW(),
    'legacy_id', id
)
WHERE metadata IS NULL;

COMMIT;
```

#### Migration 2.2 → 3.0 (refactorisation majeure)

```sql
-- migration_2.2_to_3.0.sql
START TRANSACTION;

-- Création nouvelles tables
CREATE TABLE wp_pdf_template_versions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT,
    version INT,
    content LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES wp_pdf_templates(id)
);

-- Migration données vers nouvelles tables
INSERT INTO wp_pdf_template_versions (template_id, version, content)
SELECT id, 1, content FROM wp_pdf_templates;

-- Ajout colonnes nouvelles
ALTER TABLE wp_pdf_templates
ADD COLUMN current_version INT DEFAULT 1,
ADD COLUMN is_archived BOOLEAN DEFAULT FALSE;

-- Mise à jour références
UPDATE wp_pdf_templates SET current_version = 1;

COMMIT;
```

### Outil de migration automatique

#### Script PHP de migration

```php
<?php
// migrate.php

require_once 'vendor/autoload.php';

class DatabaseMigrator
{
    private $pdo;
    private $migrationsPath = __DIR__ . '/migrations/';

    public function __construct($dsn, $username, $password)
    {
        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function migrate($targetVersion = null)
    {
        $currentVersion = $this->getCurrentVersion();
        $migrations = $this->getAvailableMigrations();

        foreach ($migrations as $migration) {
            if (version_compare($migration['version'], $currentVersion, '>')) {
                if ($targetVersion && version_compare($migration['version'], $targetVersion, '>')) {
                    break;
                }

                $this->runMigration($migration);
                $this->updateVersion($migration['version']);
                echo "✅ Migration {$migration['version']} applied\n";
            }
        }
    }

    private function runMigration($migration)
    {
        $sql = file_get_contents($this->migrationsPath . $migration['file']);
        $this->pdo->exec($sql);
    }

    private function getCurrentVersion()
    {
        try {
            $stmt = $this->pdo->query("SELECT version FROM schema_version ORDER BY id DESC LIMIT 1");
            return $stmt->fetch()['version'] ?? '0.0.0';
        } catch (Exception $e) {
            return '0.0.0';
        }
    }

    private function getAvailableMigrations()
    {
        $migrations = [];
        $files = glob($this->migrationsPath . '*.sql');

        foreach ($files as $file) {
            preg_match('/migration_(\d+\.\d+)_to_(\d+\.\d+)\.sql/', basename($file), $matches);
            if ($matches) {
                $migrations[] = [
                    'from' => $matches[1],
                    'to' => $matches[2],
                    'version' => $matches[2],
                    'file' => basename($file)
                ];
            }
        }

        usort($migrations, function($a, $b) {
            return version_compare($a['version'], $b['version']);
        });

        return $migrations;
    }

    private function updateVersion($version)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO schema_version (version, applied_at)
            VALUES (?, NOW())
        ");
        $stmt->execute([$version]);
    }
}

// Utilisation
$migrator = new DatabaseMigrator(
    "mysql:host=localhost;dbname=wp_pdf_builder",
    "username",
    "password"
);

$migrator->migrate(); // Migre vers dernière version
// ou
$migrator->migrate('2.5.0'); // Migre vers version spécifique
```

## 📁 Migration fichiers et assets

### Structure des fichiers

#### Organisation actuelle
```
wp-content/uploads/wp-pdf-builder/
├── templates/
│   ├── template_1.json
│   └── template_2.json
├── generated/
│   ├── pdf_20231001_001.pdf
│   └── pdf_20231001_002.pdf
└── temp/
    ├── cache_001.tmp
    └── cache_002.tmp
```

#### Migration vers nouvelle structure
```bash
#!/bin/bash
# migrate-files.sh

OLD_PATH="/var/www/html/wp-content/uploads/wp-pdf-builder"
NEW_PATH="/var/www/html/storage/app/pdf-builder"

echo "🔄 Starting file migration..."

# Création nouvelle structure
mkdir -p $NEW_PATH/{templates,generated,temp,backups}

# Migration templates avec transformation
for template in $OLD_PATH/templates/*.json; do
    if [ -f "$template" ]; then
        template_name=$(basename "$template" .json)

        # Transformation JSON si nécessaire
        jq '.version = "3.0" | .migrated = true' "$template" > "$NEW_PATH/templates/$template_name.json"
    fi
done

# Migration PDFs générés
rsync -av $OLD_PATH/generated/ $NEW_PATH/generated/

# Migration cache (nettoyé)
find $OLD_PATH/temp -name "*.tmp" -mtime +7 -delete  # Supprimer vieux cache
rsync -av $OLD_PATH/temp/ $NEW_PATH/temp/

# Backup ancien répertoire
tar -czf $NEW_PATH/backups/pre-migration-$(date +%Y%m%d).tar.gz $OLD_PATH/

echo "✅ File migration completed"
```

### Validation intégrité fichiers

```php
<?php
// validate-files.php

class FileValidator
{
    public function validateTemplates($path)
    {
        $templates = glob($path . '/*.json');
        $errors = [];

        foreach ($templates as $template) {
            $content = json_decode(file_get_contents($template), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = "Invalid JSON in $template";
                continue;
            }

            if (!isset($content['name']) || !isset($content['content'])) {
                $errors[] = "Missing required fields in $template";
            }

            if (!isset($content['version'])) {
                $errors[] = "Missing version in $template";
            }
        }

        return $errors;
    }

    public function validatePdfs($path)
    {
        $pdfs = glob($path . '/*.pdf');
        $errors = [];

        foreach ($pdfs as $pdf) {
            if (!is_readable($pdf)) {
                $errors[] = "Unreadable PDF: $pdf";
                continue;
            }

            // Vérification en-tête PDF
            $handle = fopen($pdf, 'r');
            $header = fread($handle, 8);
            fclose($handle);

            if (strpos($header, '%PDF-') !== 0) {
                $errors[] = "Invalid PDF header in $pdf";
            }
        }

        return $errors;
    }
}

// Validation
$validator = new FileValidator();

$templateErrors = $validator->validateTemplates('/var/www/storage/app/pdf-builder/templates');
$pdfErrors = $validator->validatePdfs('/var/www/storage/app/pdf-builder/generated');

if (empty($templateErrors) && empty($pdfErrors)) {
    echo "✅ All files validated successfully\n";
} else {
    echo "❌ Validation errors found:\n";
    foreach (array_merge($templateErrors, $pdfErrors) as $error) {
        echo "  - $error\n";
    }
}
```

## 🔄 Migration multisite WordPress

### Contexte multisite

#### Configuration multisite
```php
// wp-config.php
define('WP_ALLOW_MULTISITE', true);
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false); // ou true
define('DOMAIN_CURRENT_SITE', 'example.com');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);
```

### Migration données multisite

```sql
-- migration-multisite.sql

-- Identification sites
SELECT blog_id, domain, path FROM wp_blogs;

-- Migration templates par site
INSERT INTO wp_pdf_templates (blog_id, name, content, settings)
SELECT
    b.blog_id,
    CONCAT('Template Site ', b.blog_id),
    t.content,
    t.settings
FROM wp_blogs b
CROSS JOIN wp_pdf_templates_global t
WHERE b.blog_id > 1;

-- Migration permissions par site
INSERT INTO wp_pdf_user_permissions (blog_id, user_id, template_id, permissions)
SELECT
    b.blog_id,
    p.user_id,
    p.template_id + (b.blog_id - 1) * 1000, -- Offset pour éviter conflits
    p.permissions
FROM wp_blogs b
CROSS JOIN wp_pdf_user_permissions_global p
WHERE b.blog_id > 1;
```

### Script migration multisite

```php
<?php
// migrate-multisite.php

class MultisiteMigrator
{
    private $sites;

    public function __construct()
    {
        global $wpdb;
        $this->sites = $wpdb->get_results("SELECT blog_id, domain FROM {$wpdb->blogs} WHERE blog_id > 1");
    }

    public function migrateTemplates()
    {
        foreach ($this->sites as $site) {
            switch_to_blog($site->blog_id);

            // Migration templates pour ce site
            $this->migrateSiteTemplates($site->blog_id);

            restore_current_blog();
        }
    }

    private function migrateSiteTemplates($blogId)
    {
        global $wpdb;

        // Récupération templates globaux
        $globalTemplates = get_option('wp_pdf_global_templates', []);

        foreach ($globalTemplates as $template) {
            $localTemplate = [
                'name' => $template['name'] . " (Site $blogId)",
                'content' => $template['content'],
                'settings' => array_merge($template['settings'], [
                    'site_id' => $blogId,
                    'migrated' => true
                ])
            ];

            // Insertion template local
            $wpdb->insert($wpdb->prefix . 'pdf_templates', $localTemplate);
        }
    }
}

// Exécution
$migrator = new MultisiteMigrator();
$migrator->migrateTemplates();
```

## 🌐 Migration internationale

### Gestion encodage caractères

#### Détection encodage
```sql
-- check-encoding.sql

SELECT
    table_name,
    column_name,
    character_set_name,
    collation_name
FROM information_schema.columns
WHERE table_schema = 'wp_pdf_builder'
AND data_type IN ('varchar', 'text', 'longtext')
ORDER BY table_name, ordinal_position;
```

#### Migration UTF8MB4
```sql
-- migrate-utf8mb4.sql

-- Conversion tables
ALTER TABLE wp_pdf_templates CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE wp_pdf_generations CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Conversion colonnes spécifiques
ALTER TABLE wp_pdf_templates
MODIFY COLUMN name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
MODIFY COLUMN content LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Gestion fuseaux horaires

```php
<?php
// migrate-timezones.php

class TimezoneMigrator
{
    public function migrateTimestamps()
    {
        global $wpdb;

        // Récupération timestamps à migrer
        $records = $wpdb->get_results("
            SELECT id, created_at, updated_at
            FROM {$wpdb->prefix}pdf_generations
            WHERE created_at NOT LIKE '%+%'
        ");

        foreach ($records as $record) {
            // Conversion timestamp vers datetime avec timezone
            $createdAt = new DateTime($record->created_at, new DateTimeZone('Europe/Paris'));
            $updatedAt = new DateTime($record->updated_at, new DateTimeZone('Europe/Paris'));

            $wpdb->update(
                $wpdb->prefix . 'pdf_generations',
                [
                    'created_at' => $createdAt->format('Y-m-d H:i:sP'),
                    'updated_at' => $updatedAt->format('Y-m-d H:i:sP')
                ],
                ['id' => $record->id]
            );
        }
    }
}
```

## 🧪 Tests et validation migration

### Suite de tests migration

```php
<?php
// tests/Feature/MigrationTest.php

class MigrationTest extends TestCase
{
    public function testDataIntegrityAfterMigration()
    {
        // Compter enregistrements avant
        $countBefore = DB::table('pdf_templates')->count();

        // Exécuter migration
        Artisan::call('migrate');

        // Vérifier intégrité
        $countAfter = DB::table('pdf_templates')->count();
        $this->assertEquals($countBefore, $countAfter);

        // Vérifier données spécifiques
        $template = DB::table('pdf_templates')->first();
        $this->assertNotNull($template->metadata);
        $this->assertEquals('3.0', json_decode($template->metadata)->version);
    }

    public function testFileMigration()
    {
        // Simuler migration fichiers
        Storage::fake('local');

        // Créer fichier test
        Storage::put('pdf-builder/templates/test.json', '{"name":"Test"}');

        // Exécuter migration
        Artisan::call('pdf:migrate-files');

        // Vérifier fichier migré
        $this->assertTrue(Storage::exists('pdf-builder/templates/test.json'));
        $content = json_decode(Storage::get('pdf-builder/templates/test.json'));
        $this->assertEquals('3.0', $content->version);
    }

    public function testRollbackCapability()
    {
        // Créer état initial
        DB::table('pdf_templates')->insert([
            'name' => 'Test Template',
            'content' => 'Test content'
        ]);

        $initialCount = DB::table('pdf_templates')->count();

        // Migration
        Artisan::call('migrate');

        // Rollback
        Artisan::call('migrate:rollback');

        // Vérifier rollback
        $finalCount = DB::table('pdf_templates')->count();
        $this->assertEquals($initialCount, $finalCount);
    }
}
```

### Validation post-migration

```bash
#!/bin/bash
# validate-migration.sh

echo "🔍 Starting migration validation..."

ERRORS=0

# Test connexion base
echo "Testing database connection..."
mysql -u$DB_USER -p$DB_PASS -e "SELECT 1" $DB_NAME || ((ERRORS++))

# Test intégrité données
echo "Testing data integrity..."
TEMPLATE_COUNT=$(mysql -u$DB_USER -p$DB_PASS -e "SELECT COUNT(*) FROM wp_pdf_templates" $DB_NAME -s -N)
if [ "$TEMPLATE_COUNT" -lt 1 ]; then
    echo "❌ No templates found after migration"
    ((ERRORS++))
fi

# Test génération PDF
echo "Testing PDF generation..."
curl -f -X POST http://localhost/api/pdf/generate \
     -H "Content-Type: application/json" \
     -d '{"template_id":1,"data":{"test":"value"}}' || ((ERRORS++))

# Test fichiers
echo "Testing file access..."
if [ ! -d "/var/www/storage/app/pdf-builder/templates" ]; then
    echo "❌ Template directory not found"
    ((ERRORS++))
fi

if [ $ERRORS -eq 0 ]; then
    echo "✅ Migration validation successful"
    exit 0
else
    echo "❌ Migration validation failed with $ERRORS errors"
    exit 1
fi
```

## 📞 Support et dépannage

### Problèmes courants

#### Migration base de données
- **Erreur foreign key** : Désactiver contraintes pendant migration
- **Timeout** : Augmenter `max_execution_time` PHP
- **Mémoire insuffisante** : Migrer par batches

#### Migration fichiers
- **Permissions** : Vérifier droits écriture serveur
- **Espace disque** : Prévoir 2x espace fichiers
- **Liens symboliques** : Recréer après migration

### Procédures d'urgence

#### Rollback base de données
```sql
-- emergency-rollback.sql

-- Restaurer depuis backup
DROP DATABASE wp_pdf_builder;
CREATE DATABASE wp_pdf_builder;
-- Restaurer dump pré-migration
source /var/www/backups/pre-migration.sql;
```

#### Rollback fichiers
```bash
# emergency-file-rollback.sh

# Restaurer backup fichiers
tar -xzf /var/www/backups/pre-migration-files.tar.gz -C /var/www/

# Restaurer liens symboliques
ln -sfn /var/www/html /var/www/current
```

---

*Guide Migration Données - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\deployment\migration\upgrade-guide.md