# 🏗️ Guide Configuration Environnement Staging

Ce guide détaille la configuration complète de l'environnement staging pour WP PDF Builder Pro, réplique fidèle de la production pour des tests pré-production fiables.

## 🏛️ Architecture staging

### Vue d'ensemble

L'environnement staging est une **réplique exacte de production** utilisée pour :

```
Production (Source)    Staging (Cible)
├── Serveurs web       ├── Serveurs identiques
├── Base de données    ├── Clone anonymisé
├── Stockage fichiers  ├── Réplication partielle
├── Cache/Redis        ├── Instances dédiées
├── Load balancers     ├── Configuration miroir
└── Monitoring         └── Monitoring étendu
```

### Objectifs staging

#### Tests fonctionnels
- **Validation releases** : Tests avant déploiement production
- **Régression automatisée** : Détection changements cassants
- **Tests intégration** : Validation composants ensemble
- **Performance** : Métriques réelles vs production

#### Tests métier
- **Workflows complets** : Parcours utilisateur end-to-end
- **Données réalistes** : Tests avec volume production
- **Intégrations** : Validation APIs et services externes
- **Sécurité** : Tests vulnérabilités et conformité

## ⚙️ Configuration infrastructure

### Prérequis système

#### Serveurs et ressources
```yaml
# Configuration minimale staging
staging_environment:
  web_servers:
    - instance_type: "t3.medium"  # AWS
    - cpu: 2 vCPU
    - ram: 4GB
    - storage: 50GB SSD

  database_server:
    - instance_type: "db.t3.medium"
    - cpu: 2 vCPU
    - ram: 4GB
    - storage: 100GB SSD

  redis_cache:
    - instance_type: "cache.t3.micro"
    - ram: 1GB

  load_balancer:
    - type: "Application Load Balancer"
    - ssl_certificate: "staging.pdf-builder.com"
```

#### Logiciels et versions
```bash
# Versions identiques à production
OS: Ubuntu 22.04 LTS
Web Server: Nginx 1.24+
PHP: 8.2.12 (exactement comme prod)
Database: MySQL 8.0.34
Redis: 7.0.8
Node.js: 18.17.0 (pour assets)
```

### Configuration réseau

#### Domaines et DNS
```
staging.pdf-builder.com     -> Load Balancer staging
api-staging.pdf-builder.com -> API endpoints
admin-staging.pdf-builder.com -> Interface admin
```

#### Sécurité réseau
```bash
# Firewall rules (UFW)
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443

# IP restrictions (seulement équipe)
sudo ufw allow from 192.168.1.0/24 to any port 22,80,443
sudo ufw allow from 203.0.113.0/24 to any port 22,80,443
```

## 🗄️ Préparation base de données

### Clone production sécurisé

#### Script de clonage
```bash
#!/bin/bash
# clone-production-to-staging.sh

# Variables
PROD_DB_HOST="prod-db-server"
PROD_DB_USER="backup_user"
PROD_DB_PASS="secure_password"
PROD_DB_NAME="wp_pdf_production"

STAGING_DB_HOST="staging-db-server"
STAGING_DB_USER="staging_user"
STAGING_DB_PASS="staging_password"
STAGING_DB_NAME="wp_pdf_staging"

# Création dump production
echo "📦 Creating production dump..."
mysqldump \
  --single-transaction \
  --routines \
  --triggers \
  -h $PROD_DB_HOST \
  -u $PROD_DB_USER \
  -p$PROD_DB_PASS \
  $PROD_DB_NAME > production_dump.sql

# Anonymisation données sensibles
echo "🔒 Anonymizing sensitive data..."
sed -i 's/email@example\.com/anonymized@example.com/g' production_dump.sql
sed -i 's/[0-9]\{10,16\}/4111111111111111/g' production_dump.sql  # Cartes bancaires
sed -i 's/FR[0-9]\{11\}/FR99999999999/g' production_dump.sql     # TVA

# Import dans staging
echo "📥 Importing to staging..."
mysql \
  -h $STAGING_DB_HOST \
  -u $STAGING_DB_USER \
  -p$STAGING_DB_PASS \
  -e "DROP DATABASE IF EXISTS $STAGING_DB_NAME; CREATE DATABASE $STAGING_DB_NAME;"

mysql \
  -h $STAGING_DB_HOST \
  -u $STAGING_DB_USER \
  -p$STAGING_DB_PASS \
  $STAGING_DB_NAME < production_dump.sql

echo "✅ Staging database ready"
```

### Données de test supplémentaires

#### Génération données synthétiques
```php
<?php
// generate-test-data.php

class TestDataGenerator
{
    private $pdo;

    public function __construct($dsn, $username, $password)
    {
        $this->pdo = new PDO($dsn, $username, $password);
    }

    public function generateUsers($count = 1000)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO wp_users (user_login, user_email, user_registered)
            VALUES (?, ?, NOW())
        ");

        for ($i = 1; $i <= $count; $i++) {
            $login = "test_user_{$i}";
            $email = "test{$i}@example.com";
            $stmt->execute([$login, $email]);
        }
    }

    public function generateOrders($count = 5000)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO wp_pdf_orders (user_id, total, status, created_at)
            VALUES (?, ?, 'completed', NOW() - INTERVAL FLOOR(RAND() * 365) DAY)
        ");

        for ($i = 1; $i <= $count; $i++) {
            $userId = rand(1, 1000);
            $total = rand(10, 1000) + (rand(0, 99) / 100); // 10.00 - 1000.99
            $stmt->execute([$userId, $total]);
        }
    }

    public function generateTemplates($count = 50)
    {
        $templates = [
            'Facture Standard', 'Devis Commercial', 'Bon de Livraison',
            'Rapport Mensuel', 'Contrat de Service', 'Reçu de Paiement'
        ];

        $stmt = $this->pdo->prepare("
            INSERT INTO wp_pdf_templates (name, content, status, created_at)
            VALUES (?, ?, 'active', NOW())
        ");

        for ($i = 1; $i <= $count; $i++) {
            $name = $templates[array_rand($templates)] . " {$i}";
            $content = $this->generateTemplateContent();
            $stmt->execute([$name, $content]);
        }
    }

    private function generateTemplateContent()
    {
        return json_encode([
            'header' => 'Template généré automatiquement',
            'body' => 'Contenu de test pour validation',
            'footer' => 'Généré le ' . date('Y-m-d H:i:s'),
            'styles' => ['font-size' => '12px', 'color' => '#000000']
        ]);
    }
}

// Utilisation
$generator = new TestDataGenerator(
    "mysql:host=staging-db;dbname=wp_pdf_staging",
    "staging_user",
    "staging_password"
);

$generator->generateUsers(1000);
$generator->generateOrders(5000);
$generator->generateTemplates(50);
```

## 📁 Configuration stockage fichiers

### Structure fichiers staging
```
/var/www/staging/
├── html/                    # Code application
├── storage/                 # Fichiers uploadés
│   ├── app/                # Cache, logs
│   ├── logs/               # Logs application
│   └── pdfs/               # PDFs générés
├── backups/                # Sauvegardes
└── scripts/                # Scripts déploiement
```

### Synchronisation fichiers

#### Script rsync production → staging
```bash
#!/bin/bash
# sync-files-production-to-staging.sh

SOURCE_HOST="prod-web-server"
SOURCE_PATH="/var/www/html/storage"
DEST_PATH="/var/www/staging/storage"

# Exclusion données sensibles
EXCLUDE_PATTERNS=(
    --exclude='*.log'
    --exclude='cache/*'
    --exclude='sessions/*'
    --exclude='temp/*'
)

# Synchronisation
rsync -avz \
  --delete \
  "${EXCLUDE_PATTERNS[@]}" \
  -e "ssh -i /path/to/key" \
  $SOURCE_HOST:$SOURCE_PATH/ \
  $DEST_PATH/

# Permissions
chown -R www-data:www-data $DEST_PATH
chmod -R 755 $DEST_PATH
```

## 🔧 Configuration application

### Variables d'environnement staging

#### Fichier .env.staging
```env
# Application
APP_NAME="WP PDF Builder Pro - Staging"
APP_ENV=staging
APP_KEY=base64:your_app_key_here
APP_DEBUG=true
APP_URL=https://staging.pdf-builder.com

# Database
DB_CONNECTION=mysql
DB_HOST=staging-db-server
DB_PORT=3306
DB_DATABASE=wp_pdf_staging
DB_USERNAME=staging_user
DB_PASSWORD=staging_password

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=staging-redis-server
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (service de test)
MAIL_MAILER=smtp
MAIL_HOST=staging-smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=staging@example.com
MAIL_PASSWORD=staging_password
MAIL_ENCRYPTION=tls

# External APIs (clés de test)
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
HUBSPOT_API_KEY=test_hubspot_key
```

### Configuration spécifique staging

#### Désactivation features production
```php
// config/app.php - staging overrides
return [
    // Features désactivées en staging
    'features' => [
        'email_notifications' => false,    // Pas d'emails réels
        'payment_processing' => false,     // Paiements simulés
        'external_apis' => false,          // APIs mockées
        'analytics' => false,              // Analytics désactivés
    ],

    // Logging étendu
    'logging' => [
        'level' => 'debug',
        'channels' => ['single', 'slack', 'database'],
    ],

    // Debug tools
    'debug' => [
        'bar' => true,                     // Laravel Debugbar
        'queries' => true,                 // Log des requêtes SQL
        'mails' => true,                   // Capture emails
    ],
];
```

## 📊 Configuration monitoring staging

### Métriques étendues staging

#### Configuration Prometheus
```yaml
# prometheus-staging.yml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'wp-pdf-builder-staging'
    static_configs:
      - targets: ['staging-web-01:9090', 'staging-web-02:9090']
    metrics_path: '/metrics'

  - job_name: 'staging-database'
    static_configs:
      - targets: ['staging-db:3306']
    params:
      collect[]:
        - global_status
        - global_variables
        - engine_innodb_status

  - job_name: 'staging-redis'
    static_configs:
      - targets: ['staging-redis:6379']
```

### Alertes spécifiques staging

#### Règles alertes staging
```yaml
# staging-alerts.yml
groups:
  - name: staging_alerts
    rules:

    # Alerte données de test
    - alert: StagingDataOutdated
      expr: time() - staging_data_last_refresh > 86400  # 24h
      for: 1h
      labels:
        severity: warning
      annotations:
        summary: "Staging data is outdated"
        description: "Staging database not refreshed in 24+ hours"

    # Alerte charge tests
    - alert: StagingUnderLoadTest
      expr: rate(http_requests_total{env="staging"}[5m]) > 100
      for: 5m
      labels:
        severity: info
      annotations:
        summary: "Staging under load testing"
        description: "High request rate detected - possible load testing"

    # Alerte ressources staging
    - alert: StagingHighResourceUsage
      expr: (1 - node_memory_MemAvailable_bytes / node_memory_MemTotal_bytes) > 0.9
      for: 10m
      labels:
        severity: warning
      annotations:
        summary: "Staging high memory usage"
        description: "Memory usage > 90% on staging servers"
```

## 🔒 Sécurité staging

### Accès contrôlé

#### Authentification SSH
```bash
# Configuration SSH keys seulement
sudo sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
sudo systemctl reload sshd

# Utilisateurs autorisés
sudo useradd -m -s /bin/bash staging_user
sudo mkdir /home/staging_user/.ssh
sudo cp ~/.ssh/authorized_keys /home/staging_user/.ssh/
sudo chown -R staging_user:staging_user /home/staging_user/.ssh
sudo chmod 700 /home/staging_user/.ssh
sudo chmod 600 /home/staging_user/.ssh/authorized_keys
```

#### VPN obligatoire
```bash
# OpenVPN configuration
sudo apt install openvpn
sudo cp staging-vpn.conf /etc/openvpn/
sudo systemctl enable openvpn@staging
sudo systemctl start openvpn@staging
```

### Audit et logging

#### Logs étendus staging
```php
// Logging configuration staging
'channels' => [
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/staging.log'),
        'level' => 'debug',
    ],
    'security' => [
        'driver' => 'single',
        'path' => storage_path('logs/security-staging.log'),
        'level' => 'info',
    ],
    'performance' => [
        'driver' => 'single',
        'path' => storage_path('logs/performance-staging.log'),
        'level' => 'info',
    ],
],
```

## 🚀 Procédures opérationnelles

### Démarrage environnement staging

#### Checklist démarrage
- [ ] Serveurs provisionnés et configurés
- [ ] Base de données clonée et anonymisée
- [ ] Code déployé dernière version
- [ ] Variables d'environnement configurées
- [ ] Services démarrés (web, db, cache)
- [ ] Monitoring opérationnel
- [ ] Accès équipe configuré
- [ ] Tests de santé passant

### Maintenance régulière

#### Rafraîchissement données (hebdomadaire)
```bash
#!/bin/bash
# refresh-staging-data.sh

# Arrêt services
sudo systemctl stop nginx php8.2-fpm

# Rafraîchissement base
./clone-production-to-staging.sh

# Redémarrage services
sudo systemctl start php8.2-fpm nginx

# Tests post-refresh
curl -f https://staging.pdf-builder.com/health
```

#### Nettoyage (quotidien)
```bash
#!/bin/bash
# cleanup-staging.sh

# Logs anciens
find /var/log/wp-pdf-builder -name "*.log" -mtime +7 -delete

# Cache expiré
find /var/www/staging/storage/cache -mtime +1 -delete

# PDFs de test
find /var/www/staging/storage/pdfs -name "test_*.pdf" -mtime +1 -delete

# Sessions expirées
php artisan session:clear
```

### Arrêt environnement staging

#### Procédure arrêt
1. **Notification équipe** : Avertir utilisateurs staging
2. **Arrêt services** : Services web et base de données
3. **Sauvegarde finale** : Dernier état pour analyse
4. **Archivage** : Données importantes sauvegardées
5. **Arrêt serveurs** : Instances cloud arrêtées

---

*Guide Configuration Environnement Staging - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\testing\staging\environment-setup.md