# Installation de wkhtmltoimage pour PDF Builder Pro

## Prérequis
La fonctionnalité **premium** de génération d'images (PNG/JPG) nécessite l'installation de `wkhtmltoimage` sur votre serveur.

`wkhtmltoimage` est inclus dans le package **wkhtmltopdf** qui contient à la fois wkhtmltopdf et wkhtmltoimage.

---

## Installation par plateforme

### 🐧 Linux (Debian/Ubuntu)

```bash
# Mise à jour du système
sudo apt-get update

# Installation de wkhtmltopdf (inclut wkhtmltoimage)
sudo apt-get install -y wkhtmltopdf

# Vérification de l'installation
which wkhtmltoimage
wkhtmltoimage --version
```

**Alternative avec version plus récente :**

```bash
# Télécharger la dernière version depuis le site officiel
wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-2/wkhtmltox_0.12.6.1-2.jammy_amd64.deb

# Installer le package
sudo dpkg -i wkhtmltox_0.12.6.1-2.jammy_amd64.deb

# Résoudre les dépendances si nécessaire
sudo apt-get install -f
```

---

### 🔴 Linux (CentOS/RHEL/Fedora)

```bash
# CentOS/RHEL 7
sudo yum install -y wkhtmltopdf

# CentOS/RHEL 8+
sudo dnf install -y wkhtmltopdf

# Ou télécharger la version officielle
wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-2/wkhtmltox-0.12.6.1-2.almalinux9.x86_64.rpm
sudo rpm -ivh wkhtmltox-0.12.6.1-2.almalinux9.x86_64.rpm
```

---

### 🍎 macOS

```bash
# Avec Homebrew
brew install wkhtmltopdf

# Vérification
which wkhtmltoimage
wkhtmltoimage --version
```

---

### 🪟 Windows

#### Option 1: Installation avec l'installateur officiel

1. Téléchargez l'installateur depuis : https://wkhtmltopdf.org/downloads.html
2. Choisissez la version **64-bit** pour Windows
3. Exécutez l'installateur (`wkhtmltox-0.12.6-1.msvc2015-win64.exe`)
4. Installez dans `C:\Program Files\wkhtmltopdf`

#### Option 2: Installation manuelle

1. Téléchargez l'archive ZIP
2. Extrayez dans `C:\Program Files\wkhtmltopdf\`
3. Ajoutez au PATH système :
   - Ouvrez "Variables d'environnement système"
   - Éditez la variable `Path`
   - Ajoutez : `C:\Program Files\wkhtmltopdf\bin`

#### Vérification Windows

```powershell
# PowerShell
where.exe wkhtmltoimage
wkhtmltoimage --version
```

---

## Vérification de l'installation

Une fois installé, testez depuis PHP :

```php
<?php
// Vérifier la disponibilité
$output = shell_exec('which wkhtmltoimage 2>&1');
echo "Chemin wkhtmltoimage: " . trim($output) . "\n";

// Vérifier la version
$version = shell_exec('wkhtmltoimage --version 2>&1');
echo "Version: " . $version;
?>
```

Ou testez directement dans PDF Builder Pro :
1. Allez dans **PDF Builder → Paramètres → Système**
2. Vérifiez la section "Commandes système disponibles"
3. `wkhtmltoimage` doit apparaître comme ✅ Disponible

---

## Configuration des permissions

### Linux/macOS

Assurez-vous que le binaire est exécutable :

```bash
sudo chmod +x /usr/local/bin/wkhtmltoimage

# Vérifier les permissions
ls -la $(which wkhtmltoimage)
```

### Permissions utilisateur web

L'utilisateur web (généralement `www-data`, `nginx`, ou `apache`) doit avoir accès :

```bash
# Vérifier l'utilisateur web
ps aux | grep -E 'apache|nginx|php-fpm' | head -1

# Tester avec l'utilisateur web
sudo -u www-data wkhtmltoimage --version
```

---

## Dépendances système

`wkhtmltoimage` nécessite certaines bibliothèques graphiques :

### Ubuntu/Debian

```bash
sudo apt-get install -y \
    libxrender1 \
    libfontconfig1 \
    libxext6 \
    libx11-6
```

### CentOS/RHEL

```bash
sudo yum install -y \
    libXrender \
    libXext \
    fontconfig \
    freetype
```

---

## Résolution de problèmes

### Erreur : "wkhtmltoimage: command not found"

**Solution :** Le binaire n'est pas dans le PATH

```bash
# Trouver le chemin d'installation
find / -name wkhtmltoimage 2>/dev/null

# Créer un lien symbolique
sudo ln -s /usr/local/bin/wkhtmltoimage /usr/bin/wkhtmltoimage
```

### Erreur : "QXcbConnection: Could not connect to display"

**Solution :** Installer Xvfb pour exécuter sans affichage graphique

```bash
# Installer Xvfb
sudo apt-get install xvfb

# Utiliser avec Xvfb (pour tests manuels)
xvfb-run wkhtmltoimage https://example.com output.png
```

PDF Builder Pro gère automatiquement Xvfb si nécessaire.

### Erreur : "Access denied" (Windows)

**Solution :** Exécutez l'installateur en tant qu'administrateur et ajoutez le dossier `bin` au PATH système.

---

## Environnements d'hébergement

### Hébergement mutualisé

⚠️ La plupart des hébergements mutualisés **ne permettent pas** l'installation de binaires système.

**Alternatives :**
- Passer à un VPS ou serveur dédié
- Utiliser une solution cloud (AWS Lambda, Google Cloud Functions)
- Demander à l'hébergeur s'il peut installer wkhtmltoimage

### Serveurs VPS (DigitalOcean, Linode, Vultr)

✅ Installation complète possible avec les commandes ci-dessus

### Docker

```dockerfile
# Dans votre Dockerfile
FROM php:8.1-apache

# Installer wkhtmltopdf
RUN apt-get update && apt-get install -y \
    wkhtmltopdf \
    libxrender1 \
    libfontconfig1 \
    && apt-get clean
```

### WordPress sur WP Engine, Kinsta, Flywheel

⚠️ Ces hébergements gérés peuvent avoir des restrictions.

**Contact support** pour demander l'installation de wkhtmltoimage.

---

## Test de génération d'images

Une fois installé, testez dans PDF Builder Pro :

1. Ouvrez un template dans l'éditeur
2. Cliquez sur **Aperçu**
3. Entrez un numéro de commande
4. Cliquez sur **PNG** ou **JPG**

Si l'installation est correcte :
- ✅ L'image se télécharge automatiquement
- ✅ Le format correspond au choix (PNG transparent ou JPG compressé)
- ✅ Les dimensions respectent le template

---

## Support

Si vous rencontrez des problèmes d'installation :

1. Vérifiez les logs PHP : `wp-content/debug.log`
2. Recherchez `[SECURE_SHELL]` et `[PDF Builder]`
3. Consultez la documentation : https://wkhtmltopdf.org/usage/wkhtmltoimage.html
4. Contactez le support PDF Builder Pro avec les logs

---

## Licence

wkhtmltopdf/wkhtmltoimage est distribué sous licence **LGPLv3** (open source et gratuit).

Installation recommandée : https://wkhtmltopdf.org/downloads.html
