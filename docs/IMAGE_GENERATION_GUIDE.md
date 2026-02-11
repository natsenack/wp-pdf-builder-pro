# Génération d'Images PNG/JPG - Guide d'Installation

## 📋 Vue d'ensemble

PDF Builder Pro supporte **deux méthodes** pour générer des images PNG/JPG à partir des templates :

1. **Browsershot (Puppeteer/Chrome)** ⭐ **RECOMMANDÉ**
2. **wkhtmltoimage** (Fallback)

Le système essaie automatiquement Browsershot en premier, puis bascule sur wkhtmltoimage si nécessaire.

---

## 🎯 Option 1: Browsershot (Recommandé)

### Avantages
- ✅ Meilleure qualité de rendu (moteur Chrome/Chromium)
- ✅ Support complet CSS3, animations, fonts web
- ✅ Screenshots haute résolution
- ✅ Installation via Composer (déjà fait)
- ✅ Pas besoin de privilèges système root
- ✅ Fonctionne partout où Node.js est disponible

### Pré-requis
- Node.js 14+ installé
- npm ou yarn

### Installation

#### Linux (Debian/Ubuntu)
```bash
# 1. Installer Node.js si nécessaire
curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
sudo apt-get install -y nodejs

# 2. Installer Puppeteer globalement
sudo npm install -g puppeteer

# 3. Installer les dépendances Chrome (si manquantes)
sudo apt-get install -y \
  ca-certificates \
  fonts-liberation \
  libappindicator3-1 \
  libasound2 \
  libatk-bridge2.0-0 \
  libatk1.0-0 \
  libc6 \
  libcairo2 \
  libcups2 \
  libdbus-1-3 \
  libexpat1 \
  libfontconfig1 \
  libgbm1 \
  libgcc1 \
  libglib2.0-0 \
  libgtk-3-0 \
  libnspr4 \
  libnss3 \
  libpango-1.0-0 \
  libpangocairo-1.0-0 \
  libstdc++6 \
  libx11-6 \
  libx11-xcb1 \
  libxcb1 \
  libxcomposite1 \
  libxcursor1 \
  libxdamage1 \
  libxext6 \
  libxfixes3 \
  libxi6 \
  libxrandr2 \
  libxrender1 \
  libxss1 \
  libxtst6 \
  lsb-release \
  wget \
  xdg-utils
```

#### Linux (CentOS/RHEL/Fedora)
```bash
# 1. Installer Node.js
curl -fsSL https://rpm.nodesource.com/setup_lts.x | sudo bash -
sudo yum install -y nodejs

# 2. Installer Puppeteer
sudo npm install -g puppeteer

# 3. Installer les dépendances
sudo yum install -y \
  pango.x86_64 \
  libXcomposite.x86_64 \
  libXcursor.x86_64 \
  libXdamage.x86_64 \
  libXext.x86_64 \
  libXi.x86_64 \
  libXtst.x86_64 \
  cups-libs.x86_64 \
  libXScrnSaver.x86_64 \
  libXrandr.x86_64 \
  GConf2.x86_64 \
  alsa-lib.x86_64 \
  atk.x86_64 \
  gtk3.x86_64 \
  nss \
  libdrm \
  libgbm
```

#### macOS
```bash
# 1. Installer Node.js via Homebrew
brew install node

# 2. Installer Puppeteer
npm install -g puppeteer
```

#### Windows
```powershell
# 1. Installer Node.js depuis nodejs.org
# Téléchargez et exécutez l'installateur: https://nodejs.org/

# 2. Installer Puppeteer (PowerShell en admin)
npm install -g puppeteer
```

#### Docker
```dockerfile
FROM php:8.1-apache

# Installer Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x | bash -
RUN apt-get install -y nodejs

# Installer Puppeteer et dépendances Chrome
RUN npm install -g puppeteer
RUN apt-get install -y \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libgbm1 \
    libgtk-3-0 \
    libnspr4 \
    libnss3 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    fonts-liberation

# Configurer Puppeteer pour utiliser Chrome installé
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
```

### Vérification
```bash
# Tester Puppeteer
node -e "require('puppeteer').launch().then(b => b.close())"
```

---

## 🔧 Option 2: wkhtmltoimage (Fallback)

Si Browsershot/Puppeteer n'est pas disponible, le système bascule automatiquement sur wkhtmltoimage.

### Installation

Consultez le fichier [`WKHTMLTOIMAGE_INSTALLATION.md`](./WKHTMLTOIMAGE_INSTALLATION.md) pour les instructions complètes.

**Installation rapide:**

```bash
# Debian/Ubuntu
sudo apt-get install -y wkhtmltopdf

# CentOS/RHEL
sudo yum install -y wkhtmltopdf

# macOS
brew install wkhtmltopdf

# Windows
choco install wkhtmltopdf
# OU télécharger depuis: https://wkhtmltopdf.org/downloads.html
```

---

## 📊 Comparaison des Méthodes

| Critère | Browsershot | wkhtmltoimage |
|---------|-------------|---------------|
| **Qualité rendu** | ⭐⭐⭐⭐⭐ (Chrome) | ⭐⭐⭐⭐ (WebKit) |
| **Support CSS3** | ✅ Complet | ⚠️ Partiel |
| **Fonts web** | ✅ Excellent | ⚠️ Limité |
| **Performance** | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Installation** | npm (facile) | apt/yum (varies) |
| **Dépendances** | Node.js | Bibliothèques système |
| **Portabilité** | ✅ Multi-plateforme | ⚠️ Dépend de l'OS |
| **Hébergement partagé** | ✅ Si Node.js dispo | ❌ Souvent impossible |
| **Mémoire** | ~200-300MB | ~50MB |

---

## 🧪 Tester Votre Installation

### Via WordPress Admin

1. Allez dans **PDF Builder → Vérification Système**
2. Cliquez sur "Lancer la vérification"
3. Le système testera automatiquement:
   - Browsershot/Puppeteer
   - wkhtmltoimage
   - Génération d'image test

### Via Script (Linux/macOS)
```bash
cd /path/to/wp-pdf-builder-pro-V2
./check-wkhtmltoimage.sh
```

### Via PowerShell (Windows)
```powershell
cd I:\wp-pdf-builder-pro-V2
.\check-wkhtmltoimage.ps1
```

### Test Manuel

#### Test Browsershot
```bash
cd /path/to/plugin
php -r "
require 'vendor/autoload.php';
\Spatie\Browsershot\Browsershot::html('<h1>Test</h1>')
    ->save('/tmp/test-browsershot.png');
echo 'OK: /tmp/test-browsershot.png';
"
```

#### Test wkhtmltoimage
```bash
echo '<h1>Test</h1>' > /tmp/test.html
wkhtmltoimage /tmp/test.html /tmp/test.png
ls -lh /tmp/test.png
```

---

## 🔍 Diagnostic des Erreurs

### Erreur: "Browsershot échoué"

**Cause possible:** Puppeteer non installé ou Chrome manquant

**Solution:**
```bash
# Réinstaller Puppeteer
npm install -g puppeteer --unsafe-perm=true

# Forcer le téléchargement de Chrome
PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=false npm install -g puppeteer
```

### Erreur: "Protocol error (Target.setDiscoverTargets)"

**Cause:** Version incompatible de Puppeteer/Chrome

**Solution:**
```bash
# Installer version spécifique compatible
npm install -g puppeteer@19.0.0
```

### Erreur: "Failed to launch the browser process"

**Cause:** Dépendances système manquantes (Linux)

**Solution:**
```bash
# Ubuntu/Debian - installer toutes les dépendances
sudo apt-get install -y \
  libatk-bridge2.0-0 \
  libcups2 \
  libdrm2 \
  libgbm1 \
  libgtk-3-0 \
  libnss3 \
  libxss1
```

### Erreur: "Running as root without --no-sandbox is not supported"

**Solution:** Ajouter l'option no-sandbox (pour les environnements Docker/root)

Modifier temporairement le code:
```php
\Spatie\Browsershot\Browsershot::html($html)
    ->setOption('args', ['--no-sandbox', '--disable-setuid-sandbox'])
    ->save($temp_image);
```

---

## 🌐 Compatibilité Hébergement

### VPS / Serveurs Dédiés
- ✅ Browsershot: OUI (installer Node.js)
- ✅ wkhtmltoimage: OUI

### Hébergement Mutualisé (Shared Hosting)
- ⚠️ Browsershot: Dépend si Node.js est disponible
- ❌ wkhtmltoimage: Rarement possible (pas root)

### Hébergement WordPress Managé

| Hébergeur | Browsershot | wkhtmltoimage | Notes |
|-----------|-------------|---------------|-------|
| **Kinsta** | ✅ | ❌ | Node.js disponible via shell |
| **WP Engine** | ❌ | ❌ | Environnement restreint |
| **Cloudways** | ✅ | ✅ | Accès SSH complet |
| **SiteGround** | ⚠️ | ❌ | Node.js limité |
| **Bluehost** | ❌ | ❌ | Partagé standard |

### Docker / Kubernetes
- ✅ Browsershot: OUI (installer dans l'image)
- ✅ wkhtmltoimage: OUI

---

## 📝 Configuration Avancée

### Browsershot - Options de Performance

Créer un fichier de configuration personnalisé:

```php
// Dans plugin/config/browsershot-config.php
return [
    'timeout' => 60, // Timeout en secondes
    'chromePath' => '/usr/bin/chromium-browser', // Chemin Chrome personnalisé
    'nodeModulePath' => '/usr/lib/node_modules', // Chemin modules Node
    'npmBinary' => '/usr/bin/npm',
    'additionalOptions' => [
        'args' => [
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--disable-software-rasterizer',
            '--no-sandbox'
        ]
    ]
];
```

### Optimisation Mémoire

Pour les serveurs avec peu de RAM:

```php
// Limiter la mémoire Chrome
\Spatie\Browsershot\Browsershot::html($html)
    ->setChromePath('/usr/bin/chromium-browser')
    ->setOption('args', [
        '--disable-dev-shm-usage',
        '--memory-pressure-off',
        '--max-old-space-size=512'
    ])
    ->save($temp_image);
```

---

## 🎓 Exemples d'Utilisation

### Générer PNG depuis l'éditeur

1. Ouvrir un template dans l'éditeur
2. Cliquer sur "Aperçu"
3. Entrer un numéro de commande WooCommerce
4. Cliquer sur "PNG" (nécessite licence Premium)
5. L'image se télécharge automatiquement

### Générer via Code PHP

```php
// Méthode 1: Browsershot (si disponible)
if (class_exists('\Spatie\Browsershot\Browsershot')) {
    \Spatie\Browsershot\Browsershot::html($html)
        ->windowSize(794, 1123)
        ->format('png')
        ->save('/path/to/output.png');
}

// Méthode 2: Via le handler AJAX
$ajax_handler = new \PDF_Builder\Core\PDF_Builder_Unified_Ajax_Handler();
$_POST = [
    'template_id' => '1',
    'order_id' => 12345,
    'format' => 'png'
];
$ajax_handler->handle_generate_image();
```

---

## 📚 Ressources

- **Browsershot Documentation:** https://github.com/spatie/browsershot
- **Puppeteer Documentation:** https://pptr.dev/
- **wkhtmltopdf Site Officiel:** https://wkhtmltopdf.org/
- **Node.js Downloads:** https://nodejs.org/

---

## 🆘 Support

Si vous rencontrez des difficultés:

1. Vérifiez les logs PHP: `/wp-content/debug.log`
2. Testez manuellement Puppeteer: `node -e "require('puppeteer').launch()"`
3. Consultez la page **PDF Builder → Vérification Système**
4. Activez le mode debug WordPress pour voir les erreurs détaillées

**Logs à surveiller:**
```
[PDF Builder] Tentative de génération avec Browsershot
[PDF Builder] ✅ Génération réussie avec Browsershot
[PDF Builder] Méthode utilisée: Browsershot (Puppeteer/Chrome)
```

Ou en cas de fallback:
```
[PDF Builder] ⚠️ Browsershot échoué: ...
[PDF Builder] Tentative avec wkhtmltoimage...
[PDF Builder] ✅ Génération réussie avec wkhtmltoimage
```
