# 🏗️ Guide des environnements - Dev, Staging, Production

Ce guide détaille la configuration et la gestion des différents environnements pour WP PDF Builder Pro, de l'environnement de développement à la production.

## 🏛️ Architecture des environnements

### Vue d'ensemble

WP PDF Builder Pro nécessite trois environnements principaux :

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   DÉVELOPPEMENT │ -> │     STAGING      │ -> │   PRODUCTION    │
│                 │    │                 │    │                 │
│ • Code en cours │    │ • Tests complets │    │ • Live          │
│ • Tests unitaires│   │ • Validation QA   │    │ • Utilisateurs  │
│ • Debugging      │    │ • Performance     │    │ • Monitoring    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Responsabilités par environnement

#### Développement (Dev)
- **Équipe** : Développeurs
- **Objectif** : Développement et tests unitaires
- **Données** : Fixtures et données de test
- **MàJ** : Continue, plusieurs fois par jour

#### Staging (Pré-production)
- **Équipe** : QA, DevOps, PO
- **Objectif** : Tests d'intégration et validation
- **Données** : Clone anonymisé de production
- **MàJ** : Avant chaque déploiement production

#### Production (Prod)
- **Équipe** : SRE, Support, Utilisateurs
- **Objectif** : Service live pour utilisateurs
- **Données** : Données réelles utilisateurs
- **MàJ** : Releases planifiées, maintenance

## ⚙️ Configuration technique

### Prérequis système

#### Serveur minimum
```
OS : Linux (Ubuntu 20.04+, CentOS 8+)
CPU : 2 vCPU (4+ recommandé)
RAM : 4GB (8GB+ recommandé)
Stockage : 20GB SSD (50GB+ recommandé)
```

#### Logiciels requis
- **Web serveur** : Apache 2.4+ / Nginx 1.18+
- **PHP** : 8.1+ (8.2 recommandé)
- **Base de données** : MySQL 8.0+ / MariaDB 10.6+
- **Cache** : Redis 6.0+ / Memcached 1.6+

### Configuration PHP

#### php.ini recommandé
```ini
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
post_max_size = 50M
upload_max_filesize = 50M
max_file_uploads = 20

; Extensions requises
extension = pdo_mysql
extension = mbstring
extension = xml
extension = curl
extension = zip
extension = gd
extension = imagick
```

#### Extensions spécifiques WP PDF Builder Pro
```ini
extension = tcpdf        ; Pour génération PDF
extension = dom          ; Pour parsing XML
extension = fileinfo     ; Pour validation fichiers
extension = openssl      ; Pour sécurité
```

## 🗄️ Configuration base de données

### Structure recommandée

#### Base développement
```sql
-- Base de développement
CREATE DATABASE wp_pdf_builder_dev
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisateur dédié
CREATE USER 'wp_pdf_dev'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON wp_pdf_builder_dev.* TO 'wp_pdf_dev'@'localhost';
```

#### Base staging
```sql
-- Base de staging (clone production)
CREATE DATABASE wp_pdf_builder_staging
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisateur avec permissions limitées
CREATE USER 'wp_pdf_staging'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON wp_pdf_builder_staging.* TO 'wp_pdf_staging'@'localhost';
```

#### Base production
```sql
-- Base de production
CREATE DATABASE wp_pdf_builder_prod
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utilisateur production (permissions minimales)
CREATE USER 'wp_pdf_prod'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE ON wp_pdf_builder_prod.* TO 'wp_pdf_prod'@'localhost';
```

### Réplication et sauvegarde

#### Configuration réplication (optionnel)
```sql
-- Configuration maître-esclave
CHANGE MASTER TO
  MASTER_HOST='prod-db-server',
  MASTER_USER='replication_user',
  MASTER_PASSWORD='secure_password',
  MASTER_LOG_FILE='mysql-bin.000001',
  MASTER_LOG_POS=0;

START SLAVE;
```

#### Plan de sauvegarde
- **Développement** : Sauvegarde quotidienne
- **Staging** : Sauvegarde avant chaque déploiement
- **Production** : Sauvegarde horaire + rétention 30 jours

## 🌐 Configuration réseau

### Domaines et sous-domaines

#### Recommandations DNS
```
dev.pdf-builder.com     -> Serveur développement
staging.pdf-builder.com -> Serveur staging
pdf-builder.com         -> Serveur production
api.pdf-builder.com     -> API production
```

#### Configuration SSL/TLS

##### Let's Encrypt (recommandé)
```bash
# Installation certbot
sudo apt install certbot python3-certbot-apache

# Génération certificat
sudo certbot --apache -d pdf-builder.com -d www.pdf-builder.com

# Renouvellement automatique
sudo crontab -e
# Ajouter : 0 12 * * * /usr/bin/certbot renew --quiet
```

##### Configuration Nginx
```nginx
server {
    listen 443 ssl http2;
    server_name pdf-builder.com;

    ssl_certificate /etc/letsencrypt/live/pdf-builder.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pdf-builder.com/privkey.pem;

    # Configuration SSL optimale
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
}
```

## 🔒 Sécurité par environnement

### Développement
```bash
# Firewall restrictif
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443

# Fail2Ban pour protection SSH
sudo apt install fail2ban
sudo systemctl enable fail2ban
```

### Staging
```bash
# Accès restreint par IP
# Dans .htaccess ou nginx.conf
allow 192.168.1.0/24;  # Réseau interne
allow 203.0.113.0/24;  # IPs équipe
deny all;
```

### Production
```bash
# WAF (Web Application Firewall)
sudo apt install modsecurity-crs

# Rate limiting
# Configuration Nginx
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
limit_req zone=api burst=20 nodelay;
```

## 📊 Monitoring et logging

### Métriques à surveiller

#### Performance
- **Response time** : < 500ms API, < 2s génération PDF
- **CPU usage** : < 70% moyenne
- **Memory usage** : < 80% disponible
- **Disk I/O** : < 1000 IOPS

#### Application
- **Error rate** : < 1% des requêtes
- **PDF generation success** : > 99.5%
- **Database connections** : Pool de 10-50 connexions
- **Cache hit rate** : > 85%

### Configuration logging

#### PHP error logging
```ini
error_reporting = E_ALL & ~E_DEPRECATED
log_errors = On
error_log = /var/log/php/wp-pdf-builder.log
```

#### Application logging
```php
// Configuration Monolog
return [
    'handlers' => [
        'file' => [
            'level' => 'DEBUG',
            'path' => '/var/log/wp-pdf-builder/app.log',
        ],
        'slack' => [
            'level' => 'ERROR',
            'webhook' => env('SLACK_WEBHOOK'),
        ],
    ],
];
```

## 🔄 Synchronisation inter-environnements

### Stratégie de déploiement

#### Flux de code
```
Développement -> Commit -> Push -> CI/CD -> Tests -> Staging -> Validation -> Production
```

#### Synchronisation données
```bash
# Script de synchronisation staging -> prod
#!/bin/bash
mysqldump --single-transaction wp_pdf_builder_staging > staging_backup.sql
mysql wp_pdf_builder_prod < staging_backup.sql

# Anonymisation des données sensibles
sed -i 's/email@example\.com/test@example.com/g' staging_backup.sql
```

### Automatisation

#### GitHub Actions workflow
```yaml
name: Deploy to Production
on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to production
        run: |
          echo "Déploiement en production"
          # Scripts de déploiement
```

## 🚨 Plan de continuité

### Haute disponibilité

#### Load balancing
```nginx
upstream backend {
    server 10.0.0.1:80;
    server 10.0.0.2:80;
    server 10.0.0.3:80 backup;
}

server {
    listen 80;
    location / {
        proxy_pass http://backend;
        proxy_set_header Host $host;
    }
}
```

#### Base de données répliquée
- **Master** : Écriture seule
- **Slaves** : Lecture seule (2-3 instances)
- **Failover automatique** : Basculement transparent

### Récupération d'urgence

#### RTO/RPO objectifs
- **RTO** (Recovery Time Objective) : 4 heures max
- **RPO** (Recovery Point Objective) : 1 heure max de données perdues

#### Plan de sauvegarde
- **Sauvegardes complètes** : Hebdomadaires
- **Sauvegardes incrémentielles** : Quotidiennes
- **Sauvegardes logs** : Horaires
- **Test de restauration** : Mensuel

---

*Guide des environnements - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\deployment\environments\setup.md