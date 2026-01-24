# 🔧 Scripts Déploiement CI/CD - Automatisation complète

Ce guide couvre l'automatisation complète du déploiement de WP PDF Builder Pro avec des pipelines CI/CD modernes et des scripts de déploiement zero-downtime.

## 🚀 Vue d'ensemble CI/CD

### Pipeline complet

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  DÉVELOPPEMENT │ -> │     TESTS      │ -> │   STAGING     │ -> │ PRODUCTION   │
│               │    │               │    │               │    │              │
│ • Code review  │    │ • Unit tests   │    │ • Integration  │    │ • Deploy      │
│ • Linting      │    │ • E2E tests    │    │ • Performance  │    │ • Monitoring  │
│ • Build        │    │ • Security     │    │ • User tests   │    │ • Rollback    │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
```

### Outils supportés

- **GitLab CI/CD** : Pipeline intégré
- **GitHub Actions** : Workflows cloud
- **Jenkins** : Automatisation legacy
- **Azure DevOps** : Enterprise

## 🐧 Configuration GitLab CI/CD

### Fichier `.gitlab-ci.yml` complet

```yaml
stages:
  - lint
  - test
  - build
  - deploy_staging
  - deploy_production

variables:
  DOCKER_IMAGE: wp-pdf-builder:$CI_COMMIT_REF_SLUG
  STAGING_HOST: staging.pdf-builder.com
  PRODUCTION_HOST: pdf-builder.com

# Linting et validation code
lint:
  stage: lint
  image: php:8.2-cli
  before_script:
    - apt-get update && apt-get install -y git unzip
    - curl -sS https://getcomposer.org/installer | php
    - mv composer.phar /usr/local/bin/composer
    - composer install --no-interaction --prefer-dist --optimize-autoloader
  script:
    - composer run lint
    - composer run phpcs
    - composer run phpstan
  only:
    - merge_requests
    - main

# Tests unitaires et intégration
test:
  stage: test
  image: php:8.2-cli
  services:
    - mysql:8.0
  variables:
    MYSQL_DATABASE: wp_pdf_test
    MYSQL_ROOT_PASSWORD: test_password
  before_script:
    - apt-get update && apt-get install -y git unzip mysql-client
    - curl -sS https://getcomposer.org/installer | php
    - mv composer.phar /usr/local/bin/composer
    - composer install --no-interaction --prefer-dist --optimize-autoloader
    - cp .env.testing .env
    - php artisan key:generate
    - php artisan migrate --seed
  script:
    - composer run test
    - composer run test:e2e
  coverage: '/coverage: \d+\.\d+/'
  artifacts:
    reports:
      coverage_report:
        coverage_format: cobertura
        path: coverage/cobertura-coverage.xml
    expire_in: 1 week
  only:
    - merge_requests
    - main

# Construction image Docker
build:
  stage: build
  image: docker:latest
  services:
    - docker:dind
  script:
    - docker build -t $DOCKER_IMAGE .
    - docker push $DOCKER_IMAGE
  only:
    - main

# Déploiement staging
deploy_staging:
  stage: deploy_staging
  image: alpine:latest
  before_script:
    - apk add --no-cache openssh-client rsync
    - eval $(ssh-agent -s)
    - echo "$SSH_PRIVATE_KEY" | tr -d '\r' | ssh-add -
    - mkdir -p ~/.ssh
    - chmod 700 ~/.ssh
    - ssh-keyscan -H $STAGING_HOST >> ~/.ssh/known_hosts
  script:
    - rsync -avz --delete --exclude='.git' ./ staging@$STAGING_HOST:/var/www/html/
    - ssh staging@$STAGING_HOST "cd /var/www/html && ./deploy-staging.sh"
  environment:
    name: staging
    url: https://staging.pdf-builder.com
  only:
    - main

# Déploiement production (manuel)
deploy_production:
  stage: deploy_production
  image: alpine:latest
  before_script:
    - apk add --no-cache openssh-client rsync
    - eval $(ssh-agent -s)
    - echo "$SSH_PRIVATE_KEY_PROD" | tr -d '\r' | ssh-add -
    - mkdir -p ~/.ssh
    - chmod 700 ~/.ssh
    - ssh-keyscan -H $PRODUCTION_HOST >> ~/.ssh/known_hosts
  script:
    - rsync -avz --delete --exclude='.git' ./ prod@$PRODUCTION_HOST:/var/www/html/
    - ssh prod@$PRODUCTION_HOST "cd /var/www/html && ./deploy-production.sh"
  environment:
    name: production
    url: https://pdf-builder.com
  when: manual
  only:
    - main
```

## 🚀 Configuration GitHub Actions

### Workflow principal

```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pdo, mbstring, xml, curl, zip, gd
      - name: Install dependencies
        run: composer install --no-progress --prefer-dist --optimize-autoloader
      - name: Run linter
        run: composer run lint
      - name: Run PHPStan
        run: composer run phpstan

  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: test
          MYSQL_DATABASE: wp_pdf_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pdo, mbstring, xml, curl, zip, gd
      - name: Install dependencies
        run: composer install --no-progress --prefer-dist --optimize-autoloader
      - name: Copy environment file
        run: cp .env.ci .env
      - name: Generate key
        run: php artisan key:generate
      - name: Run migrations
        run: php artisan migrate --seed
      - name: Run tests
        run: composer run test
      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          file: ./coverage/clover.xml

  deploy_staging:
    needs: [lint, test]
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to staging
        uses: easingthemes/ssh-deploy@main
        env:
          SSH_PRIVATE_KEY: ${{ secrets.STAGING_SSH_KEY }}
          ARGS: "-rltgoDzvO --delete"
          SOURCE: "./"
          REMOTE_HOST: ${{ secrets.STAGING_HOST }}
          REMOTE_USER: ${{ secrets.STAGING_USER }}
          TARGET: "/var/www/html/"
          EXCLUDE: "/.git/, /.github/, /tests/"
      - name: Run deployment script
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.STAGING_HOST }}
          username: ${{ secrets.STAGING_USER }}
          key: ${{ secrets.STAGING_SSH_KEY }}
          script: |
            cd /var/www/html
            ./deploy-staging.sh

  deploy_production:
    needs: [deploy_staging]
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    environment: production
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to production
        uses: easingthemes/ssh-deploy@main
        env:
          SSH_PRIVATE_KEY: ${{ secrets.PRODUCTION_SSH_KEY }}
          ARGS: "-rltgoDzvO --delete"
          SOURCE: "./"
          REMOTE_HOST: ${{ secrets.PRODUCTION_HOST }}
          REMOTE_USER: ${{ secrets.PRODUCTION_USER }}
          TARGET: "/var/www/html/"
          EXCLUDE: "/.git/, /.github/, /tests/"
      - name: Run deployment script
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.PRODUCTION_HOST }}
          username: ${{ secrets.PRODUCTION_USER }}
          key: ${{ secrets.PRODUCTION_SSH_KEY }}
          script: |
            cd /var/www/html
            ./deploy-production.sh
```

## 📜 Scripts de déploiement

### Script déploiement staging

```bash
#!/bin/bash
# deploy-staging.sh

set -e  # Exit on any error

echo "🚀 Starting staging deployment..."

# Variables
APP_DIR="/var/www/html"
BACKUP_DIR="/var/www/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Fonction de rollback
rollback() {
    echo "❌ Deployment failed, rolling back..."
    if [ -d "$BACKUP_DIR/staging_$TIMESTAMP" ]; then
        rm -rf $APP_DIR/*
        cp -r $BACKUP_DIR/staging_$TIMESTAMP/* $APP_DIR/
        echo "✅ Rollback completed"
    fi
    exit 1
}

# Trap pour rollback automatique
trap rollback ERR

# Création backup
echo "📦 Creating backup..."
mkdir -p $BACKUP_DIR
cp -r $APP_DIR $BACKUP_DIR/staging_$TIMESTAMP

# Installation dépendances
echo "📦 Installing dependencies..."
cd $APP_DIR
composer install --no-interaction --prefer-dist --optimize-autoloader

# Migrations base de données
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Cache clearing
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Permissions
echo "🔒 Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR/storage
chmod -R 755 $APP_DIR/bootstrap/cache

# Tests post-déploiement
echo "🧪 Running post-deployment tests..."
php artisan test --testsuite=post-deploy || rollback

# Health check
echo "🏥 Running health check..."
curl -f http://localhost/health || rollback

echo "✅ Staging deployment completed successfully!"
```

### Script déploiement production (zero-downtime)

```bash
#!/bin/bash
# deploy-production.sh

set -e

echo "🚀 Starting production deployment (zero-downtime)..."

# Variables
APP_DIR="/var/www/html"
RELEASE_DIR="/var/www/releases"
CURRENT_LINK="/var/www/current"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
RELEASE_PATH="$RELEASE_DIR/$TIMESTAMP"

# Fonction de rollback
rollback() {
    echo "❌ Deployment failed, rolling back..."
    rm -rf $RELEASE_PATH
    echo "✅ Cleanup completed"
    exit 1
}

trap rollback ERR

# Création répertoire release
echo "📁 Creating release directory..."
mkdir -p $RELEASE_PATH

# Copie fichiers (sans vendor pour optimisation)
echo "📋 Copying application files..."
rsync -a --exclude='.git' --exclude='vendor' --exclude='node_modules' ./ $RELEASE_PATH/

# Installation dépendances
echo "📦 Installing dependencies..."
cd $RELEASE_PATH
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Build assets (si applicable)
echo "🔨 Building assets..."
npm ci --production
npm run build

# Migrations base de données (optionnel, avec flag)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "🗄️ Running database migrations..."
    php artisan migrate --force
fi

# Tests pré-déploiement
echo "🧪 Running pre-deployment tests..."
php artisan test --testsuite=pre-deploy || rollback

# Changement lien symbolique (atomic)
echo "🔗 Switching to new release..."
ln -sfn $RELEASE_PATH $CURRENT_LINK.tmp
mv -fT $CURRENT_LINK.tmp $CURRENT_LINK

# Attendre que les requêtes en cours se terminent
echo "⏳ Waiting for ongoing requests to complete..."
sleep 10

# Nettoyage anciennes releases (garder 5 dernières)
echo "🧹 Cleaning old releases..."
cd $RELEASE_DIR
ls -t | tail -n +6 | xargs -r rm -rf

# Health check
echo "🏥 Running health check..."
curl -f --max-time 30 http://localhost/health || rollback

# Notifications
echo "📢 Sending notifications..."
curl -X POST -H 'Content-type: application/json' \
     --data '{"text":"✅ Production deployment completed successfully!"}' \
     $SLACK_WEBHOOK

echo "✅ Production deployment completed successfully!"
```

## 🔍 Tests et validation

### Tests post-déploiement

```php
// tests/Feature/PostDeployTest.php
class PostDeployTest extends TestCase
{
    public function testApplicationIsAccessible()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function testDatabaseConnection()
    {
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com'
        ]);
    }

    public function testPdfGeneration()
    {
        $response = $this->post('/api/pdf/generate', [
            'template_id' => 1,
            'data' => ['test' => 'data']
        ]);

        $response->assertStatus(200);
        $this->assertStringContains($response->getContent(), '%PDF-');
    }

    public function testApiEndpoints()
    {
        $endpoints = [
            '/api/health',
            '/api/templates',
            '/api/pdf/status'
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->get($endpoint);
            $response->assertStatus(200);
        }
    }
}
```

### Health checks

```php
// app/Http/Controllers/HealthController.php
class HealthController extends Controller
{
    public function check()
    {
        $checks = [];

        // Database
        try {
            DB::connection()->getPdo();
            $checks['database'] = 'OK';
        } catch (\Exception $e) {
            $checks['database'] = 'ERROR: ' . $e->getMessage();
        }

        // Cache
        try {
            Cache::store('redis')->put('health_check', 'OK', 10);
            $checks['cache'] = 'OK';
        } catch (\Exception $e) {
            $checks['cache'] = 'ERROR: ' . $e->getMessage();
        }

        // Storage
        try {
            Storage::disk('local')->put('health_check.txt', 'OK');
            Storage::disk('local')->delete('health_check.txt');
            $checks['storage'] = 'OK';
        } catch (\Exception $e) {
            $checks['storage'] = 'ERROR: ' . $e->getMessage();
        }

        // PDF generation
        try {
            $pdf = new TCPDF();
            $pdf->AddPage();
            $pdf->writeHTML('<h1>Health Check</h1>');
            $checks['pdf_generation'] = 'OK';
        } catch (\Exception $e) {
            $checks['pdf_generation'] = 'ERROR: ' . $e->getMessage();
        }

        $status = collect($checks)->contains('ERROR') ? 500 : 200;

        return response()->json([
            'status' => $status === 200 ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'checks' => $checks
        ], $status);
    }
}
```

## 🔐 Sécurité et bonnes pratiques

### Secrets management

```yaml
# .gitlab-ci.yml - Variables sécurisées
variables:
  DATABASE_URL: $DATABASE_URL  # Stocké dans CI/CD variables
  REDIS_URL: $REDIS_URL
  SLACK_WEBHOOK: $SLACK_WEBHOOK

# GitHub - Secrets
# Settings > Secrets and variables > Actions
# Ajouter : DATABASE_URL, REDIS_URL, etc.
```

### Rollback automatique

```bash
#!/bin/bash
# rollback.sh

echo "🔄 Starting rollback process..."

# Identifier dernière release stable
LAST_STABLE=$(ls -t /var/www/releases | head -2 | tail -1)

if [ -z "$LAST_STABLE" ]; then
    echo "❌ No stable release found for rollback"
    exit 1
fi

# Switch vers release stable
ln -sfn /var/www/releases/$LAST_STABLE /var/www/current

# Health check
curl -f http://localhost/health

echo "✅ Rollback to $LAST_STABLE completed"
```

## 📊 Monitoring et métriques

### Métriques de déploiement

```bash
#!/bin/bash
# deployment-metrics.sh

DEPLOY_START=$(date +%s)
DEPLOY_SUCCESS=0

# ... déploiement ...

DEPLOY_END=$(date +%s)
DEPLOY_DURATION=$((DEPLOY_END - DEPLOY_START))

# Envoi métriques
curl -X POST https://metrics-api.example.com/deploy \
     -H "Content-Type: application/json" \
     -d "{
       \"environment\": \"production\",
       \"duration\": $DEPLOY_DURATION,
       \"success\": $DEPLOY_SUCCESS,
       \"timestamp\": $(date +%s)
     }"
```

### Alertes et notifications

```bash
#!/bin/bash
# notify-deployment.sh

WEBHOOK_URL="https://hooks.slack.com/services/..."
CHANNEL="#deployments"

if [ $DEPLOY_SUCCESS -eq 1 ]; then
    COLOR="good"
    MESSAGE="✅ Production deployment successful"
else
    COLOR="danger"
    MESSAGE="❌ Production deployment failed"
fi

curl -X POST -H 'Content-type: application/json' \
     --data "{
       \"channel\": \"$CHANNEL\",
       \"attachments\": [{
         \"color\": \"$COLOR\",
         \"text\": \"$MESSAGE\",
         \"fields\": [
           {\"title\": \"Environment\", \"value\": \"Production\", \"short\": true},
           {\"title\": \"Duration\", \"value\": \"${DEPLOY_DURATION}s\", \"short\": true}
         ]
       }]
     }" $WEBHOOK_URL
```

---

*Scripts CI/CD - Version 1.0*
*Mis à jour le 20 octobre 2025*</content>
<parameter name="filePath">D:\wp-pdf-builder-pro\docs\deployment\scripts\automated-deployment.md