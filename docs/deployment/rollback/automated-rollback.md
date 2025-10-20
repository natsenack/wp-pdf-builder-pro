# ↩️ Plan Rollback - Stratégie de récupération d'urgence

Ce guide détaille les stratégies de rollback pour WP PDF Builder Pro, permettant un retour arrière rapide et sécurisé en cas de problème post-déploiement.

## 🚨 Vue d'ensemble rollback

### Quand utiliser le rollback ?

#### Critères d'urgence
- **Défaillance critique** : Application inaccessible
- **Erreur fonctionnelle** : Fonctionnalité principale cassée
- **Performance dégradée** : Impact utilisateur significatif
- **Sécurité compromise** : Vulnérabilité exploitée

#### Niveaux de rollback

##### Rollback immédiat (0-5 min)
- **Déclencheur** : Erreur critique au démarrage
- **Scope** : Retour à version précédente
- **Downtime** : 1-2 minutes

##### Rollback planifié (15-60 min)
- **Déclencheur** : Problème découvert post-déploiement
- **Scope** : Analyse + retour contrôlé
- **Downtime** : 5-15 minutes

##### Rollback majeur (2-24h)
- **Déclencheur** : Corruption données ou infrastructure
- **Scope** : Reconstruction complète
- **Downtime** : 1-4 heures

## 🏗️ Architecture rollback

### Points de restauration

#### Releases versionnées
```
releases/
├── 20231020_143000/  # Release actuelle
├── 20231019_120000/  # Release précédente
├── 20231018_090000/  # Release -2
└── 20231017_160000/  # Release -3
```

#### Liens symboliques
```bash
# Structure production
/var/www/
├── current -> releases/20231020_143000/  # Version active
├── releases/                            # Toutes les releases
├── shared/                              # Données persistantes
└── backups/                             # Sauvegardes
```

### Base de données

#### Schéma versioning
```sql
CREATE TABLE schema_versions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    version VARCHAR(20) NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rollback_sql TEXT,
    checksum VARCHAR(64)
);

-- Exemple entrée
INSERT INTO schema_versions (version, rollback_sql, checksum)
VALUES (
    '2.5.0',
    'ALTER TABLE wp_pdf_templates DROP COLUMN metadata;',
    SHA2('ALTER TABLE wp_pdf_templates DROP COLUMN metadata;', 256)
);
```

## ⚡ Rollback automatisé

### Script rollback zero-touch

```bash
#!/bin/bash
# rollback.sh

set -e

# Configuration
APP_DIR="/var/www"
RELEASES_DIR="$APP_DIR/releases"
CURRENT_LINK="$APP_DIR/current"
ROLLBACK_TIMEOUT=300  # 5 minutes timeout

echo "🔄 Starting automated rollback..."

# Fonction de nettoyage
cleanup() {
    echo "🧹 Cleaning up rollback artifacts..."
    # Nettoyage spécifique au rollback
}

trap cleanup EXIT

# Identifier release actuelle
CURRENT_RELEASE=$(readlink $CURRENT_LINK | xargs basename)
echo "📍 Current release: $CURRENT_RELEASE"

# Identifier release précédente
PREVIOUS_RELEASE=$(ls -t $RELEASES_DIR | sed -n '2p')
if [ -z "$PREVIOUS_RELEASE" ]; then
    echo "❌ No previous release found"
    exit 1
fi
echo "🎯 Rolling back to: $PREVIOUS_RELEASE"

# Créer backup pre-rollback
BACKUP_DIR="$APP_DIR/backups/pre-rollback-$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR
cp -r $CURRENT_LINK/* $BACKUP_DIR/
echo "📦 Backup created: $BACKUP_DIR"

# Rollback base de données
echo "🗄️ Rolling back database..."
if [ -f "$RELEASES_DIR/$PREVIOUS_RELEASE/rollback.sql" ]; then
    mysql -u$DB_USER -p$DB_PASS $DB_NAME < "$RELEASES_DIR/$PREVIOUS_RELEASE/rollback.sql"
else
    echo "⚠️ No database rollback script found, skipping..."
fi

# Switch vers release précédente
echo "🔗 Switching release..."
ln -sfn "$RELEASES_DIR/$PREVIOUS_RELEASE" "$CURRENT_LINK.new"
mv -fT "$CURRENT_LINK.new" $CURRENT_LINK

# Attendre propagation
sleep 5

# Health check
echo "🏥 Running health checks..."
HEALTH_CHECK_URL="http://localhost/health"
if curl -f --max-time 30 $HEALTH_CHECK_URL > /dev/null 2>&1; then
    echo "✅ Health check passed"
else
    echo "❌ Health check failed, attempting emergency rollback..."
    emergency_rollback
    exit 1
fi

# Nettoyer anciennes releases (garder 3 dernières)
echo "🧹 Cleaning old releases..."
ls -t $RELEASES_DIR | tail -n +4 | xargs -r rm -rf

# Notifications
echo "📢 Sending rollback notifications..."
curl -X POST $SLACK_WEBHOOK \
     -H 'Content-type: application/json' \
     -d "{\"text\":\"✅ Rollback completed: $CURRENT_RELEASE → $PREVIOUS_RELEASE\"}"

echo "✅ Automated rollback completed successfully!"
```

### Rollback d'urgence

```bash
#!/bin/bash
# emergency-rollback.sh

echo "🚨 EMERGENCY ROLLBACK INITIATED"

# Arrêt services
echo "🛑 Stopping services..."
sudo systemctl stop nginx
sudo systemctl stop php8.2-fpm

# Restauration backup le plus récent
LATEST_BACKUP=$(ls -t /var/www/backups/*.tar.gz | head -1)
if [ -n "$LATEST_BACKUP" ]; then
    echo "📦 Restoring from backup: $LATEST_BACKUP"
    cd /var/www
    tar -xzf $LATEST_BACKUP
else
    echo "❌ No backup found, attempting release rollback..."
    # Fallback vers rollback release
fi

# Restauration base de données
LATEST_DB_BACKUP=$(ls -t /var/www/backups/db_*.sql | head -1)
if [ -n "$LATEST_DB_BACKUP" ]; then
    echo "🗄️ Restoring database..."
    mysql -u$DB_USER -p$DB_PASS $DB_NAME < $LATEST_DB_BACKUP
fi

# Redémarrage services
echo "▶️ Restarting services..."
sudo systemctl start php8.2-fpm
sudo systemctl start nginx

# Vérification
curl -f http://localhost/health && echo "✅ Emergency rollback successful" || echo "❌ Emergency rollback failed"
```

## 📊 Rollback base de données

### Stratégies par type de changement

#### Rollback migration additive
```sql
-- rollback-additive.sql
-- Suppression colonne ajoutée
ALTER TABLE wp_pdf_templates DROP COLUMN metadata;

-- Suppression index
DROP INDEX idx_template_status ON wp_pdf_templates;

-- Suppression table
DROP TABLE IF EXISTS wp_pdf_template_versions;
```

#### Rollback migration destructive
```sql
-- rollback-destructive.sql
-- ATTENTION: Données potentiellement perdues

-- Recréer table supprimée depuis backup
CREATE TABLE wp_pdf_archived_templates (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    content LONGTEXT,
    deleted_at TIMESTAMP
);

-- Restaurer données depuis backup logique
INSERT INTO wp_pdf_archived_templates
SELECT id, name, content, NOW() FROM backup_pdf_templates;
```

### Outil rollback base de données

```php
<?php
// DatabaseRollback.php

class DatabaseRollback
{
    private $pdo;
    private $rollbackPath;

    public function __construct($pdo, $rollbackPath = '/var/www/rollbacks/')
    {
        $this->pdo = $pdo;
        $this->rollbackPath = $rollbackPath;
    }

    public function rollbackToVersion($targetVersion)
    {
        $currentVersion = $this->getCurrentVersion();

        if (version_compare($targetVersion, $currentVersion, '>=')) {
            throw new Exception("Cannot rollback to newer version");
        }

        $rollbacks = $this->getRollbackScripts($currentVersion, $targetVersion);

        foreach (array_reverse($rollbacks) as $rollback) {
            $this->executeRollback($rollback);
            $this->updateVersion($rollback['from_version']);
        }
    }

    private function executeRollback($rollback)
    {
        $sql = file_get_contents($this->rollbackPath . $rollback['file']);

        // Validation checksum
        if (!$this->validateChecksum($sql, $rollback['checksum'])) {
            throw new Exception("Checksum validation failed for rollback script");
        }

        $this->pdo->exec($sql);
    }

    private function validateChecksum($sql, $expectedChecksum)
    {
        return hash('sha256', $sql) === $expectedChecksum;
    }

    private function getRollbackScripts($fromVersion, $toVersion)
    {
        // Récupération scripts depuis base ou fichiers
        return [
            [
                'from_version' => '2.5.0',
                'to_version' => '2.4.0',
                'file' => 'rollback_2.5.0_to_2.4.0.sql',
                'checksum' => 'abc123...'
            ]
        ];
    }
}
```

## 🔄 Rollback fichiers et configuration

### Rollback configuration

```bash
#!/bin/bash
# rollback-config.sh

CONFIG_DIR="/var/www/config"
BACKUP_DIR="/var/www/backups/config"

echo "🔧 Rolling back configuration..."

# Versions de configuration
CONFIG_VERSIONS=(
    "nginx.conf"
    "php.ini"
    "wp-config.php"
    ".env"
)

for config in "${CONFIG_VERSIONS[@]}"; do
    if [ -f "$BACKUP_DIR/$config.backup" ]; then
        echo "↩️ Rolling back $config..."
        cp "$BACKUP_DIR/$config.backup" "$CONFIG_DIR/$config"
    else
        echo "⚠️ No backup found for $config"
    fi
done

# Redémarrage services si nécessaire
echo "🔄 Reloading services..."
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm

echo "✅ Configuration rollback completed"
```

### Rollback assets statiques

```bash
#!/bin/bash
# rollback-assets.sh

ASSETS_DIR="/var/www/html/assets"
ROLLBACK_DIR="/var/www/rollbacks/assets"

echo "🎨 Rolling back static assets..."

# Identifier version assets
LATEST_ASSETS=$(ls -t $ROLLBACK_DIR | head -1)

if [ -n "$LATEST_ASSETS" ]; then
    echo "📦 Restoring assets from: $LATEST_ASSETS"

    # Backup assets actuels
    mv $ASSETS_DIR $ASSETS_DIR.backup.$(date +%s)

    # Restauration assets précédents
    cp -r $ROLLBACK_DIR/$LATEST_ASSETS $ASSETS_DIR

    # Nettoyage cache CDN si applicable
    # curl -X PURGE https://cdn.example.com/assets/*
else
    echo "❌ No asset rollback version found"
fi

echo "✅ Assets rollback completed"
```

## 📈 Monitoring et alertes rollback

### Métriques rollback

```php
<?php
// RollbackMetrics.php

class RollbackMetrics
{
    public function trackRollback($type, $duration, $success)
    {
        $metrics = [
            'rollback_type' => $type, // 'automated', 'manual', 'emergency'
            'duration_seconds' => $duration,
            'success' => $success,
            'timestamp' => time(),
            'environment' => getenv('APP_ENV'),
            'version_from' => $this->getCurrentVersion(),
            'version_to' => $this->getRollbackVersion()
        ];

        // Envoi vers système de métriques
        $this->sendToMetrics($metrics);

        // Log détaillé
        Log::info('Rollback executed', $metrics);
    }

    public function alertOnRollback($reason, $impact)
    {
        $alert = [
            'severity' => $this->calculateSeverity($impact),
            'message' => "Rollback executed: $reason",
            'impact' => $impact,
            'action_required' => $this->determineAction($impact)
        ];

        $this->sendAlert($alert);
    }

    private function calculateSeverity($impact)
    {
        $severities = [
            'low' => ['partial_rollback', 'feature_disabled'],
            'medium' => ['service_degraded', 'data_loss_minor'],
            'high' => ['service_down', 'data_loss_major'],
            'critical' => ['security_breach', 'complete_failure']
        ];

        foreach ($severities as $level => $impacts) {
            if (in_array($impact, $impacts)) {
                return $level;
            }
        }

        return 'unknown';
    }
}
```

### Dashboard rollback

```php
<?php
// RollbackDashboard.php

class RollbackDashboard
{
    public function getRollbackStats()
    {
        return [
            'total_rollbacks' => $this->countRollbacks(),
            'success_rate' => $this->calculateSuccessRate(),
            'average_duration' => $this->getAverageDuration(),
            'rollback_reasons' => $this->getTopReasons(),
            'recent_rollbacks' => $this->getRecentRollbacks(10)
        ];
    }

    public function getRollbackReadiness()
    {
        $checks = [
            'backup_freshness' => $this->checkBackupAge() < 24, // heures
            'rollback_scripts' => $this->checkRollbackScripts(),
            'test_environment' => $this->checkTestEnvironment(),
            'documentation' => $this->checkDocumentation()
        ];

        return [
            'ready' => !in_array(false, $checks),
            'checks' => $checks
        ];
    }
}
```

## 🧪 Tests rollback

### Suite de tests rollback

```php
<?php
// tests/Feature/RollbackTest.php

class RollbackTest extends TestCase
{
    public function testAutomatedRollback()
    {
        // Simuler échec déploiement
        $this->mockDeploymentFailure();

        // Exécuter rollback
        $exitCode = Artisan::call('rollback:automated');

        // Vérifier succès
        $this->assertEquals(0, $exitCode);

        // Vérifier version précédente active
        $this->assertTrue($this->isPreviousVersionActive());

        // Vérifier données intactes
        $this->assertDatabaseIntegrity();
    }

    public function testDatabaseRollback()
    {
        // Appliquer migration
        Artisan::call('migrate');

        // Vérifier migration appliquée
        $this->assertTrue($this->isMigrationApplied());

        // Rollback
        Artisan::call('migrate:rollback');

        // Vérifier rollback
        $this->assertFalse($this->isMigrationApplied());
    }

    public function testEmergencyRollback()
    {
        // Simuler panne complète
        $this->simulateCompleteFailure();

        // Rollback d'urgence
        $exitCode = $this->runEmergencyRollback();

        // Vérifier récupération
        $this->assertEquals(0, $exitCode);
        $this->assertTrue($this->isServiceRestored());
    }

    public function testRollbackMetrics()
    {
        // Exécuter rollback avec métriques
        $startTime = microtime(true);
        Artisan::call('rollback:automated');
        $duration = microtime(true) - $startTime;

        // Vérifier métriques enregistrées
        $this->assertDatabaseHas('rollback_metrics', [
            'duration' => $duration,
            'success' => true
        ]);
    }
}
```

## 📋 Procédures opérationnelles

### Runbook rollback

#### Étapes rollback planifié
1. **Évaluation** : Analyser impact et cause
2. **Communication** : Informer équipes et utilisateurs
3. **Préparation** : Valider scripts et backups
4. **Exécution** : Rollback contrôlé
5. **Validation** : Tests post-rollback
6. **Communication** : Mise à jour statut

#### Checklists par scénario

##### Rollback fonctionnel
- [ ] Cause identifiée et documentée
- [ ] Impact évalué (utilisateurs affectés)
- [ ] Backup base de données disponible
- [ ] Scripts rollback testés
- [ ] Équipe disponible pour supervision
- [ ] Communication préparée

##### Rollback d'urgence
- [ ] Service complètement indisponible
- [ ] Backup < 1h disponible
- [ ] Procédure d'urgence validée
- [ ] Escalade automatique déclenchée
- [ ] Support client alerté

### Formation équipe

#### Compétences requises
- **DevOps** : Scripts rollback, infrastructure
- **DBA** : Rollback base de données, restauration
- **Développeurs** : Code rollback, débogage
- **Support** : Communication, gestion incident

#### Exercices réguliers
- **Rollback simulé** : Tous les 3 mois
- **Test urgence** : Tous les 6 mois
- **Formation équipe** : Annuelle

---

*Plan Rollback - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\deployment\rollback\automated-rollback.md