# 📄 PDF Builder Pro - Documentation Complète

## � Structure Organisée du Plugin

Le plugin suit une structure organisée pour séparer clairement les fichiers de production des outils de développement :

```
📂 wp-pdf-builder-pro/
├── 📄 .htaccess                    ← Sécurité
├── 📄 bootstrap.php               ← Démarrage du plugin
├── 📄 pdf-builder-pro.php         ← Fichier principal WordPress
├── 📄 README.md                   ← Cette documentation
├── 📄 settings-page.php           ← Page de paramètres
├── 📄 template-editor.php         ← Éditeur de templates
├── 📄 woocommerce-elements.css    ← Styles WooCommerce
├── 📁 assets/                     ← CSS/JS/Images (production)
├── 📁 includes/                   ← Code PHP modulaire
├── 📁 languages/                  ← Traductions i18n
├── 📁 uploads/                    ← Fichiers uploadés
├── 📁 vendor/                     ← Dépendances PHP (Composer)
├── 📁 tools/                      ← Scripts de déploiement
├── 📁 docs/                       ← Documentation détaillée
├── 📁 build-tools/                ← Outils de build (webpack, etc.)
├── 📁 dev-tools/                  ← Outils de développement
├── 📁 src/                        ← Code source TypeScript/React
├── 📁 dist/                       ← Assets compilés (non déployés)
├── 📁 archive/                    ← Sauvegardes automatiques
└── 📁 node_modules/               ← Dépendances JS (non déployés)
```

### 🚀 Fichiers de Production (Déployés)
Seuls ces fichiers sont déployés sur le serveur WordPress :
- `.htaccess`, `bootstrap.php`, `pdf-builder-pro.php`, `README.md`
- `settings-page.php`, `template-editor.php`, `woocommerce-elements.css`
- Dossiers : `assets/`, `includes/`, `languages/`, `uploads/`, `vendor/`

### 🛠️ Fichiers de Développement (Locaux uniquement)
Ces dossiers ne sont **jamais** déployés :
- `tools/` - Scripts de déploiement FTP
- `docs/` - Documentation développeur
- `build-tools/` - Webpack, TypeScript, etc.
- `dev-tools/` - Outils de diagnostic
- `src/` - Code source non compilé
- `dist/` - Assets compilés (générés automatiquement)
- `archive/` - Sauvegardes locales
- `node_modules/` - Dépendances JavaScript

## �🚀 Déploiement et Installation

### 📦 Installation des dépendances PHP

**Important :** Ce plugin nécessite TCPDF pour générer les PDFs. Installez-le via Composer :

```bash
# Depuis le dossier du plugin
composer install

# Ou globalement si Composer n'est pas dans le dossier
cd wp-content/plugins/wp-pdf-builder-pro
composer install
```

Si vous n'avez pas Composer installé :
```bash
# Installation de Composer (Linux/Mac)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Installation de Composer (Windows avec Chocolatey)
choco install composer
```

### Déploiement automatique (recommandé) :

### Déploiement automatique (recommandé) :
```bash
# Depuis le dossier tools/
cd tools/
./ftp-deploy-fixed.ps1

# Ou avec PowerShell directement
.\tools\ftp-deploy-fixed.ps1
```

### ⚡ DÉPLOIEMENT ULTRA-RAPIDE

**Commande principale :**
```powershell
# Déployer directement (recommandé)
.\tools\ftp-deploy-fixed.ps1
```

**Configuration FTP :**
- Modifiez `tools/ftp-config.env` pour vos paramètres FTP
- Le script utilise des connexions parallèles pour la vitesse maximale
- Déploie automatiquement uniquement les fichiers de production

**Fichiers déployés automatiquement :**
- ✅ Tous les `*.php` du répertoire racine
- ✅ `assets/css/**`, `assets/js/**`, `assets/images/**`
- ✅ `includes/**`, `languages/**`, `vendor/**`
- ❌ `node_modules/**`, `src/**`, `tools/**`, `docs/**`, etc.

### Déploiement manuel :

#### Pour le développement :
```bash
# Cloner le repo
git clone <repository-url>
cd wp-pdf-builder-pro

# Installer les dépendances (côté frontend)
npm install

# Compiler pour le développement
npm run dev

# Compiler pour la production
npm run build
```

#### Pour le déploiement WordPress :
```bash
# 1. Compiler les assets frontend
npm run build

# 2. Le dossier dist/ contient les fichiers compilés
# 3. Copier UNIQUEMENT ces fichiers sur votre serveur :
#    - dist/
#    - includes/
#    - languages/
#    - assets/ (CSS/JS compilés seulement)
#    - *.php
#    - *.md (README, etc.)

# ❌ NE PAS copier :
#    - node_modules/
#    - src/
#    - package.json
#    - webpack.config.js
#    - tsconfig.json
```

### Déploiement automatique complet :
#### Configuration FTP (fichier `tools/ftp-config.env`) :
```env
FTP_HOST=votre-serveur-ftp.com
FTP_USER=votre-username
FTP_PASSWORD=votre-mot-de-passe
```

💡 **Astuce** : Le fichier `tools/ftp-config.env` est automatiquement exclu de Git.

#### Test de configuration :
```powershell
# Vérifier que la configuration FTP fonctionne
.\tools\ftp-deploy-fixed.ps1 -TestConnection
```
# Vérifier que la configuration FTP fonctionne
./test-ftp.bat
```

⚠️ **Sécurité** : Le fichier `ftp-config.env` est automatiquement exclu de Git pour éviter l'exposition des identifiants.

### Structure de déploiement (Production) :
```
📂 wp-content/plugins/wp-pdf-builder-pro/
├── � .htaccess                    ← Sécurité
├── 📄 bootstrap.php               ← Démarrage
├── 📄 pdf-builder-pro.php         ← Plugin principal
├── 📄 README.md                   ← Documentation
├── 📄 settings-page.php           ← Paramètres
├── 📄 template-editor.php         ← Éditeur
├── 📄 woocommerce-elements.css    ← Styles WC
├── 📁 assets/                     ← CSS/JS/Images
├── 📁 includes/                   ← Code PHP
├── 📁 languages/                  ← Traductions
├── 📁 uploads/                    ← Uploads utilisateur
└── 📁 vendor/                     ← Dépendances PHP
```

**Note :** Les dossiers `tools/`, `docs/`, `build-tools/`, `dev-tools/`, `src/`, `dist/`, `archive/` et `node_modules/` restent locaux et ne sont jamais déployés.

## ⚠️ Important : Gestion des dépendances

- **`node_modules/`** : Jamais sur le serveur (500MB+)
- **`.gitignore`** : Exclut automatiquement les fichiers inutiles
- **`npm install`** : À faire uniquement en développement

---

# PDF Builder Pro - Constructeur de PDF WordPress Premium



> **Plugin WordPress Premium** pour créer des PDFs personnalisés avec WooCommerce> **Plugin WordPress professionnel** pour créer des PDFs personnalisés avec une interface drag & drop moderne. Idéal pour WooCommerce - automatisez vos factures, devis et reçus.

>

> Créer des factures, devis, reçus et documents personnalisés avec un éditeur drag & drop intuitif.[![Version](https://img.shields.io/badge/version-5.0.0-blue.svg)](https://github.com)

[![WordPress](https://img.shields.io/badge/wordpress-5.0+-blue.svg)](https://wordpress.org)

[![Version](https://img.shields.io/badge/version-5.0.0-blue.svg)](https://pdfbuilderpro.com)[![WooCommerce](https://img.shields.io/badge/woocommerce-5.0+-red.svg)](https://woocommerce.com)

[![WordPress](https://img.shields.io/badge/wordpress-5.0+-blue.svg)](https://wordpress.org)[![PHP](https://img.shields.io/badge/php-7.4+-purple.svg)](https://php.net)

[![WooCommerce](https://img.shields.io/badge/woocommerce-5.0+-purple.svg)](https://woocommerce.com)[![License](https://img.shields.io/badge/license-GPL--3.0-green.svg)](LICENSE)

[![PHP](https://img.shields.io/badge/php-7.4+-red.svg)](https://php.net)

[![License](https://img.shields.io/badge/license-GPL--3.0-green.svg)](LICENSE)## 🎯 Vue d'ensemble



## 🚀 Démarrage Rapide**PDF Builder Pro** est un plugin WordPress premium qui révolutionne la création de documents PDF. Avec son interface drag & drop intuitive, créez des factures, devis, reçus et tout type de document professionnel en quelques clics.



### Installation en 3 Minutes### ✨ Fonctionnalités Clés



1. **Télécharger** le plugin depuis [pdfbuilderpro.com](https://pdfbuilderpro.com)- 🎨 **Interface Drag & Drop** ultra-moderne

2. **Installer** via `wp-admin > Plugins > Ajouter nouveau`- 🛒 **Intégration WooCommerce** complète (HPOS compatible)

3. **Activer** WooCommerce et PDF Builder Pro- 📄 **4 Templates Professionnels** prédéfinis

4. **Créer** votre premier template dans `WooCommerce > PDF Builder`- 🔄 **Détection Automatique** des types de documents

- ⚙️ **Mapping Statuts → Templates** configurable

### Premier PDF- 📱 **Responsive Design** pour tous appareils

- 🚀 **Performance Optimisée** avec cache intelligent

```php- �️ **Sécurité Renforcée** avec nonces et validation

// Exemple de génération programmatique

$pdf_url = pdf_builder_pro_generate_pdf($order_id, $template_id);## 📦 Installation

echo '<a href="' . $pdf_url . '" target="_blank">Télécharger PDF</a>';

```### Prérequis Système

- **WordPress** : 5.0 ou supérieur

## 📖 Documentation- **WooCommerce** : 5.0 ou supérieur (recommandé)

- **PHP** : 7.4 ou supérieur

### 🏠 **Pour Tous les Utilisateurs**- **MySQL** : 5.6 ou supérieur



| Guide | Description | Temps de lecture |### Installation Automatique

|-------|-------------|------------------|1. Téléchargez le fichier `pdf-builder-pro.zip`

| [**📋 Guide d'Installation**](docs/installation-guide.md) | Installation complète et configuration | 10 min |2. Dans WordPress Admin → **Extensions → Ajouter**

| [**🎯 Guide Utilisateur**](docs/user-guide.md) | Utilisation complète du plugin | 20 min |3. Cliquez **Téléverser une extension**

| [**❓ FAQ**](docs/faq.md) | Questions fréquemment posées | 5 min |4. Sélectionnez le fichier ZIP et cliquez **Installer**

5. Activez l'extension **PDF Builder Pro**

### 🛠️ **Pour Développeurs**

### Installation Manuelle

| Guide | Description | Niveau |```bash

|-------|-------------|---------|# Via FTP/SFTP

| [**👨‍💻 Guide Développeur**](docs/developer-guide.md) | API, hooks, extensions | Avancé |cd /wp-content/plugins/

| [**🔧 Guide Dépannage**](docs/troubleshooting-guide.md) | Résoudre les problèmes courants | Intermédiaire |unzip pdf-builder-pro.zip

| [**📈 Guide Mise à Jour**](docs/upgrade-guide.md) | Migration entre versions | Intermédiaire |# Activer dans WordPress Admin

```

### 📚 **Documentation Technique**

### Configuration Post-Installation

- **API REST** : Endpoints completsAprès activation, le plugin :

- **Hooks WordPress** : Points d'extension- ✅ Crée automatiquement les tables nécessaires

- **Base de données** : Structure et requêtes- ✅ Installe 4 templates professionnels

- **Sécurité** : Bonnes pratiques- ✅ Configure les permissions d'accès

- ✅ Met en place les hooks WooCommerce

## ✨ Fonctionnalités Principales

## 🚀 Utilisation Rapide

### 🎨 Éditeur Visuel

- **Drag & Drop** intuitif### 1. Accès à l'Interface

- **Éléments prédéfinis** : Texte, images, tableaux, codes-barres- Menu WordPress : **PDF Builder Pro → Templates**

- **Aperçu temps réel** du PDF- Création de templates personnalisés

- **Templates responsives**

### 2. Création d'un Template

### 🛒 Intégration WooCommerce1. Cliquez **"Nouveau Template"**

- **Détection automatique** des statuts de commande2. Choisissez le type (Facture, Devis, Reçu, Autre)

- **Mapping personnalisé** statut → template3. Utilisez le drag & drop pour ajouter des éléments

- **Données dynamiques** : Produits, client, totaux4. Personnalisez le contenu et les styles

- **Support HPOS** (High-Performance Order Storage)5. Sauvegardez votre template



### ⚙️ Administration Avancée### 3. Intégration WooCommerce

- **Interface moderne** avec onglets organisés- Les boutons PDF apparaissent automatiquement dans les commandes

- **Gestion templates** par type (facture, devis, reçu)- Configuration du mapping statuts → templates dans **Paramètres**

- **Paramètres globaux** personnalisables- Aperçu et génération PDF en un clic

- **Debug mode** intégré

## 🎨 Fonctionnalités Détaillées

### 🔌 Extensibilité

- **API REST** complète### Interface Utilisateur

- **Hooks WordPress** pour extensions- **Canvas A4** avec guides d'alignement

- **Filtres personnalisés** pour modification données- **Sidebar d'outils** organisée par catégories

- **Actions AJAX** pour interactions dynamiques- **Drag & Drop** fluide et intuitif

- **Édition en temps réel** des éléments

## 🛠️ Configuration Requise- **Aperçu PDF** intégré



| Composant | Minimum | Recommandé |### Éléments Disponibles (35+)

|-----------|---------|------------|- **📄 Texte** : Titres, paragraphes, listes

| **WordPress** | 5.0 | 6.0+ |- **🖼️ Médias** : Images, logos, icônes

| **WooCommerce** | 5.0 | 8.0+ |- **📊 Tableaux** : Données structurées

| **PHP** | 7.4 | 8.1+ |- **🏷️ WooCommerce** : Variables de commande

| **MySQL** | 5.7 | 8.0+ |- **⚙️ Formes** : Lignes, rectangles, barres

| **Mémoire** | 128MB | 256MB+ |

| **Espace disque** | 50MB | 100MB+ |### Templates Prédéfinis

- **📄 Facture** : En-tête, client, articles, totaux

## 📦 Installation Détaillée- **📝 Devis** : Validité, conditions, informations

- **🧾 Reçu** : Accusé de paiement

### Via WordPress Admin- **📋 Autre** : Template générique

1. Aller dans `wp-admin > Plugins > Ajouter nouveau`

2. Rechercher "PDF Builder Pro"### Variables WooCommerce

3. Cliquer "Installer maintenant"```

4. Activer le plugin[order_number]     → Numéro de commande

[order_date]       → Date de commande

### Via FTP[order_total]      → Total TTC

```bash[billing_*]        → Infos client (name, address, email)

# Télécharger et extraire[order_items_table] → Tableau des articles

wget https://downloads.pdfbuilderpro.com/pdf-builder-pro.zip[payment_method]   → Mode de paiement

## 🛒 Éléments WooCommerce Intégrés

### 📄 Éléments de Facturation

| Élément | Description | Données d'exemple |
|---------|-------------|-------------------|
| **Numéro de Facture** | Numéro unique de facture | `INV-001` |
| **Date de Facture** | Date de création de la facture | `2024-01-15` |
| **Numéro de Commande** | Référence WooCommerce | `#1234` |
| **Date de Commande** | Date de création de la commande | `2024-01-15 10:30` |

### 👤 Informations Client

| Élément | Description | Données d'exemple |
|---------|-------------|-------------------|
| **Adresse de Facturation** | Adresse complète du client | `John Doe`<br>`123 Main St`<br>`City, State 12345` |
| **Adresse de Livraison** | Adresse de livraison | `John Doe`<br>`456 Shipping Ave`<br>`City, State 12345` |
| **Nom du Client** | Nom complet du client | `John Doe` |
| **Email du Client** | Adresse email | `john.doe@example.com` |

### 💳 Informations de Paiement

| Élément | Description | Données d'exemple |
|---------|-------------|-------------------|
| **Méthode de Paiement** | Moyen de paiement utilisé | `Carte de crédit (Stripe)` |
| **Statut de Commande** | État actuel de la commande | `Traitée` |

### 📊 Produits et Prix

| Élément | Description | Données d'exemple |
|---------|-------------|-------------------|
| **Tableau des Produits** | Liste détaillée des articles | `- Produit 1 x1 $10.00`<br>`- Produit 2 x2 $20.00` |
| **Sous-total** | Total HT | `$45.00` |
| **Remise** | Montant de la remise | `-$5.00` |
| **Frais de Port** | Coûts de livraison | `$5.00` |
| **Taxes** | Montant des taxes | `$2.25` |
| **Total** | Montant TTC | `$47.25` |
| **Remboursement** | Montant remboursé | `-$10.00` |
| **Frais Supplémentaires** | Frais divers | `$1.50` |

### 📝 Éléments de Devis

| Élément | Description | Données d'exemple |
|---------|-------------|-------------------|
| **Numéro de Devis** | Numéro unique du devis | `QUO-001` |
| **Date de Devis** | Date de création du devis | `2024-01-15` |
| **Validité du Devis** | Période de validité | `30 jours` |
| **Notes du Devis** | Conditions spéciales | `Conditions spéciales du devis` |

### 🎨 Utilisation dans l'Éditeur

1. **Accès aux éléments** : Dans la bibliothèque d'éléments, ouvrez l'onglet "WooCommerce - Factures", "WooCommerce - Produits" ou "WooCommerce - Devis"

2. **Drag & Drop** : Glissez-déposez les éléments sur le canvas

3. **Personnalisation** : Ajustez la taille, la police, les couleurs via le panneau de propriétés

4. **Aperçu** : Les éléments affichent des données d'exemple en mode édition

5. **Génération PDF** : Lors de la génération, les vraies données WooCommerce remplacent les exemples

### 🔧 Configuration Avancée

#### Mode Test vs Production
- **Mode Test** : Affiche des données d'exemple pour la conception
- **Mode Production** : Utilise les vraies données de commande WooCommerce

#### Mapping Automatique
Le plugin détecte automatiquement :
- Le numéro de commande pour générer les numéros de facture/devis
- Les informations client depuis les métadonnées WooCommerce
- Les détails de produits et prix depuis l'ordre

#### Personnalisation des Formats
Les formats peuvent être personnalisés via des filtres WordPress :
```php
// Exemple de personnalisation du numéro de facture
add_filter('pdf_builder_invoice_number', function($number, $order_id) {
    return 'FACT-' . $order_id;
}, 10, 2);
```

unzip pdf-builder-pro.zip```



# Uploader vers wp-content/plugins/## ⚙️ Configuration Avancée

scp -r pdf-builder-pro user@server:/var/www/html/wp-content/plugins/

### Mapping Statuts → Templates

# Permissions et activationConfigurez dans **Paramètres → PDF Builder Pro → Détection automatique** :

ssh user@server

chown -R www-data:www-data /var/www/html/wp-content/plugins/pdf-builder-pro| Statut WooCommerce | Template Utilisé |

wp plugin activate pdf-builder-pro|-------------------|------------------|

```| `wc-pending` | Devis |

| `wc-completed` | Facture |

### Configuration Post-Installation| `wc-refunded` | Reçu |

```php| `wc-cancelled` | Autre |

// wp-config.php - Options recommandées

define('WP_MEMORY_LIMIT', '256M');### Paramètres PDF

define('WP_MAX_MEMORY_LIMIT', '512M');- **Format** : A4, Letter, Legal

- **Orientation** : Portrait, Paysage

// .htaccess - Optimisations- **Marges** : Personnalisables

<IfModule mod_expires.c>- **Qualité** : Haute résolution

    ExpiresByType application/pdf "access plus 1 month"

</IfModule>### Permissions Utilisateur

```- **Administrateur** : Accès complet

- **Éditeur** : Gestion templates

## 🎯 Cas d'Usage- **Auteur** : Utilisation uniquement



### E-commerce Standard## 🔧 Développement

- **Factures automatiques** lors de commandes complètes

- **Devis** avec conditions personnalisées### Architecture Modulaire

- **Reçus de paiement** pour confirmations```

assets/js/

### Business Avancé├── pdf-builder-config.js    # Configuration

- **Contrats de service** avec signatures numériques├── pdf-builder-utils.js     # Utilitaires

- **Bons de livraison** avec codes-barres├── pdf-builder-models.js    # Modèles données

- **Certificats** et documents officiels├── pdf-builder-events.js    # Gestion événements

├── pdf-builder-init.js      # Initialisation

### Intégrations Personnalisées└── pdf-builder-main.js      # Orchestration

- **CRM externe** synchronisation```

- **Stock management** notifications

- **Comptabilité** exports automatisés### Hooks WordPress

```php

## 🔒 Sécurité// Actions disponibles

do_action('pdf_builder_pro_template_saved', $template_id);

- ✅ **Nonces WordPress** pour toutes les requêtesdo_action('pdf_builder_pro_pdf_generated', $order_id, $template_id);

- ✅ **Sanitisation** complète des entrées

- ✅ **Permissions** basées sur rôles utilisateur// Filtres disponibles

- ✅ **Logs d'audit** pour actions sensiblesapply_filters('pdf_builder_pro_template_types', $types);

- ✅ ** Mises à jour régulières** de sécuritéapply_filters('pdf_builder_pro_pdf_settings', $settings);

```

## 🚀 Performance

### API REST

- ⚡ **Génération optimisée** avec cache intelligent```javascript

- ⚡ **Lazy loading** des ressources JavaScript// Génération PDF

- ⚡ **Base de données indexée** pour recherches rapidesPOST /wp-json/pdf-builder-pro/v1/generate

- ⚡ **Compression PDF** automatique{

  "template_id": 1,

## 💰 Tarification  "order_id": 123,

  "settings": {...}

| Plan | Prix | Fonctionnalités |}

|------|------|----------------|```

| **Basic** | 49€/an | 1 site, templates de base |

| **Pro** | 99€/an | 5 sites, API complète |## 🐛 Dépannage

| **Enterprise** | 199€/an | Sites illimités, support premium |

### Problèmes Courants

### Garantie Satisfait ou Remboursé

- **30 jours** pour tester#### Interface ne se charge pas

- **Support inclus** selon le plan```javascript

- **Mises à jour gratuites** pendant 1 an// Vérifiez la console pour ces erreurs :

"Uncaught ReferenceError: jQuery is not defined"

## 🆘 Support// Solution : Assurez-vous que jQuery est chargé

```

### Niveaux de Support

#### Drag & Drop ne fonctionne pas

#### 🟢 **Gratuit**```javascript

- Documentation complète// Vérifiez que ces attributs sont présents :

- Forum communauté<button draggable="true" data-type="text">

- Issues GitHub// Solution : Vérifier les permissions JavaScript

```

#### 🟡 **Premium** (Inclus Pro+)

- Email prioritaire (< 24h)#### PDFs ne se génèrent pas

- Chat en direct```php

- Téléphone// Vérifiez les logs PHP :

tail -f /wp-content/debug.log

#### 🔴 **Enterprise**// Erreurs communes : Permissions, mémoire PHP

- Support dédié 24/7```

- Consultant technique

- Développement personnalisé### Debug Mode

Activez le mode debug dans `wp-config.php` :

### Contact Support```php

- 📧 **Email** : support@pdfbuilderpro.comdefine('WP_DEBUG', true);

- 💬 **Chat** : En ligne 9h-18h CETdefine('WP_DEBUG_LOG', true);

- 📞 **Téléphone** : +33 1 23 45 67 89```

- 🐛 **Bug reports** : [GitHub Issues](https://github.com/pdfbuilderpro/issues)

## 📋 Changelog

## 🤝 Contribution

### Version 5.0.0 (03/10/2025)

Nous accueillons les contributions ! Voir [CONTRIBUTING.md](CONTRIBUTING.md) pour les détails.- ✅ **Interface drag & drop complète** avec 35+ éléments

- ✅ **Intégration WooCommerce HPOS** compatible

### Types de Contributions- ✅ **Système de mapping statuts** configurable

- 🐛 **Bug fixes**- ✅ **4 templates professionnels** prédéfinis

- ✨ **Nouvelles fonctionnalités**- ✅ **Architecture modulaire** optimisée

- 📖 **Documentation**- ✅ **Performance améliorée** avec cache

- 🧪 **Tests**- ✅ **Sécurité renforcée** avec validation

- 🎨 **UI/UX améliorations**- ✅ **Documentation complète** et guides



## 📄 Licence### Version 4.2.0

- ✅ Interface utilisateur modernisée

Ce plugin est sous licence **GPL-3.0**. Voir [LICENSE](LICENSE) pour les détails complets.- ✅ Support WooCommerce étendu

- ✅ Optimisations de performance

### Droits et Restrictions

- ✅ Utilisation personnelle et commerciale### Version 4.0.0

- ✅ Modification du code- ✅ Refonte complète de l'architecture

- ✅ Redistribution- ✅ Nouveaux éléments et templates

- ❌ Suppression des crédits- ✅ Compatibilité WordPress 6.0+

- ❌ Vente sans licence

## 🤝 Support & Communauté

## 🗺️ Roadmap

### Support Inclus

### Version 5.1 (Q1 2025)- 📖 **Documentation complète** en ligne

- [ ] Éditeur collaboratif- 🎥 **Tutoriels vidéo** détaillés

- [ ] Templates IA générés- 💬 **Forum communautaire** actif

- [ ] Intégration Zapier- 📧 **Support email** (24-48h)



### Version 6.0 (Q3 2025)### Support Premium

- [ ] Multi-langues complet- 🚀 **Chat en direct** avec experts

- [ ] API GraphQL- 📞 **Support téléphonique** prioritaire

- [ ] Analytics intégrés- 👨‍💻 **Développement personnalisé**

- 🔧 **Maintenance et mises à jour**

### Contributions Communauté

- [ ] Plugin Elementor### Ressources

- [ ] Intégration Mailchimp- 📚 **Base de connaissances** : [docs.pdfbuilderpro.com](https://docs.pdfbuilderpro.com)

- [ ] Templates prédéfinis- 🎥 **Chaîne YouTube** : [youtube.com/pdfbuilderpro](https://youtube.com/pdfbuilderpro)

- 💬 **Discord** : [discord.gg/pdfbuilderpro](https://discord.gg/pdfbuilderpro)

## 🙏 Remerciements

## 📄 Licence

- **Équipe WooCommerce** pour l'intégration seamless

- **Communauté WordPress** pour le support continuCe plugin est distribué sous licence **GPL-3.0**. Voir le fichier `LICENSE` pour plus de détails.

- **Contributeurs** pour les améliorations

- **Utilisateurs** pour les retours précieux### Conditions d'Utilisation

- ✅ Utilisation personnelle et commerciale autorisée

---- ✅ Modification du code permise

- ✅ Redistribution autorisée sous GPL

## 📞 Restons en Contact- ❌ Suppression des crédits interdite



- 🌐 **Site web** : [pdfbuilderpro.com](https://pdfbuilderpro.com)## 🙏 Crédits & Remerciements

- 📧 **Newsletter** : Mises à jour et conseils

- 📱 **Réseaux sociaux** : Nouvelles et astucesDéveloppé avec ❤️ par l'équipe PDF Builder Pro

- 🎥 **YouTube** : Tutoriels vidéo

### Technologies Utilisées

---- **WordPress** - CMS robuste et extensible

- **WooCommerce** - Plateforme e-commerce leader

**Prêt à créer des PDFs professionnels ?** [Commencer maintenant !](docs/installation-guide.md) 🚀- **jQuery UI** - Interface utilisateur moderne

- **TCPDF** - Bibliothèque PDF professionnelle

*Documentation version 5.0.0 - Décembre 2024*
### Contributeurs
- **Équipe de développement** : Interface et fonctionnalités
- **Communauté WordPress** : Tests et feedback
- **Utilisateurs beta** : Validation et suggestions

---

## 🎉 Prêt à Révolutionner vos PDFs ?

**PDF Builder Pro** transforme la création de documents PDF en une expérience fluide et professionnelle. Que vous gériez une boutique WooCommerce ou créiez des documents d'entreprise, notre plugin s'adapte à vos besoins.

### 🚀 Commencez Maintenant
1. [Téléchargez PDF Builder Pro](https://pdfbuilderpro.com/download)
2. [Installez en 2 minutes](https://docs.pdfbuilderpro.com/installation)
3. [Créez votre premier PDF](https://docs.pdfbuilderpro.com/premier-template)

**Questions ?** Contactez-nous : [support@pdfbuilderpro.com](mailto:support@pdfbuilderpro.com)

---

*PDF Builder Pro - L'avenir de la génération PDF WordPress* ⚡

## 🛠️ Architecture Technique

### Structure Modulaire Optimisée
```
assets/js/
├── pdf-builder-config.js    → Constantes et configuration
├── pdf-builder-utils.js     → Fonctions utilitaires  
├── pdf-builder-models.js    → Modèles de données + création éléments
├── pdf-builder-events.js    → Gestionnaires événements + drag & drop
├── pdf-builder-init.js      → Initialisation + génération interface
└── pdf-builder-main.js      → Orchestration principale
```

### Fichiers PHP
```
includes/
├── class-pdf-builder-config.php  → Configuration PHP
├── class-pdf-builder-admin.php   → Interface d'administration
└── class-pdf-builder-ajax.php    → Requêtes AJAX
```

### Organisation des Responsabilités
- **CONFIG** → Constantes, couleurs, dimensions PDF
- **UTILS** → Fonctions réutilisables (ID, formats, localStorage)
- **MODELS** → Création/rendu éléments, gestion state, cache
- **EVENTS** → Drag & drop, clics, keyboard shortcuts
- **INIT** → Setup DOM, génération HTML interface
- **MAIN** → Point d'entrée, délégation vers modules

## 🧪 Tests et Validation

### Fichiers de Test Disponibles
- `test-drag-drop.html` → Test complet drag & drop
- `test-interface-reparee.html` → Test interface générale
- `test-modules-corrected.html` → Test modules JavaScript

### Tests Effectués
- ✅ Chargement de tous les modules JS
- ✅ Génération correcte de l'interface
- ✅ Drag & drop fonctionnel
- ✅ Création/sélection/suppression éléments
- ✅ Syntaxe JavaScript valide (tous fichiers)
- ✅ Compatibilité navigateurs modernes

## 📖 Installation et Utilisation

### Installation WordPress
1. Télécharger le dossier complet du plugin
2. Le placer dans `/wp-content/plugins/`
3. Activer "PDF Builder Pro" dans l'admin WordPress
4. Aller dans le menu "PDF Builder Pro"

### Utilisation de Base
1. **Ajouter un élément** : Glisser depuis la sidebar → canvas
2. **Déplacer** : Utiliser la poignée ⋮⋮ qui apparaît au survol
3. **Éditer** : Cliquer sur l'élément puis bouton ✏️
4. **Supprimer** : Bouton 🗑️ dans la toolbar
5. **Générer PDF** : Bouton 📄 dans le header

### Raccourcis Clavier
- `Ctrl+G` → Afficher/masquer grille
- `Ctrl+M` → Afficher/masquer marges  
- `Ctrl+Z` → Annuler
- `Ctrl+Y` → Rétablir
- `Échap` → Fermer modales

## 🔧 Configuration

### Paramètres Modifiables
Éditer `pdf-builder-config.js` pour ajuster :
- Dimensions PDF (A4, Letter)
- Couleurs de l'interface
- Comportements par défaut
- Messages système

### Styles Personnalisables
Le fichier CSS `pdf-builder-pro-admin.css` contient :
- Variables CSS pour cohérence
- Styles drag & drop complets
- Animations et transitions
- Responsive design

## 🚀 Évolutions Futures

### À Implémenter
- 🔄 Redimensionnement par poignées
- 📐 Guides d'alignement automatiques
- 📋 Copier/coller éléments
- 🎯 Sélection multiple
- 💾 Auto-sauvegarde temps réel
- 📱 Support tactile mobile

### Optimisations Possibles
- Lazy loading des éléments lourds
- Cache intelligent
- Compression des templates
- Export formats multiples

## 📋 Changelog

### v5.0.0 (03/10/2025)
- ✅ **Interface ultra-moderne v5.0** avec aperçu temps réel avancé
- ✅ **Configuration avancée** avec contrôles de zoom, raccourcis clavier
- ✅ **Mode plein écran immersif** avec contrôles flottants
- ✅ **Outils professionnels** : grille aimantée, règles, mesures
- ✅ **Optimisations de performance** avec cache intelligent
- ✅ **Architecture modulaire complète** et système de canvas A4
- ✅ **Drag & drop entièrement fonctionnel** avec 35+ éléments
- ✅ **Système de sélection/édition** avancé
- ✅ **Animations et feedback visuel** améliorés
- ✅ **4 templates par défaut professionnels** (Facture, Devis, Reçu, Autre)
- ✅ **Variables WooCommerce intégrées** dans tous les templates
- ✅ **Permissions système supprimé** pour compatibilité maximale
- ✅ **Système de prévisualisation WooCommerce** contrôlable
- ✅ **Installation automatique** des templates lors de l'activation

## � Leçons Apprises - Structure WordPress

### ⚠️ Règle d'Or : Structure HTML WordPress
**Dans WordPress, TOUS les éléments HTML doivent être à l'intérieur du `div.wrap`** pour respecter la structure d'administration.

**❌ Mauvaise pratique :**
```php
// Dans settings-page.php - AFFICHAGE EN DEHORS DU DIV.WRAPP
if ($error) {
    echo '<div class="notice notice-error">Erreur !</div>'; // ❌ Casse la structure
}
?>
<div class="wrap"> <!-- OUVERTURE TARDIVE -->
    <h1>Titre</h1>
    <!-- Contenu -->
</div>
```

**✅ Bonne pratique :**
```php
// Stocker les messages
$admin_notices = [];
if ($error) {
    $admin_notices[] = '<div class="notice notice-error">Erreur !</div>';
}

// Dans la méthode parente (ex: settings_page())
?>
<div class="wrap">
    <h1>Titre</h1>
    <?php
    // Afficher les messages stockés À L'INTÉRIEUR du div.wrap
    foreach ($admin_notices as $notice) {
        echo $notice;
    }
    ?>
    <!-- Contenu -->
</div>
<?php
```

**Impact :** Les `echo` prématurés peuvent casser complètement la mise en page, faire apparaître le footer WordPress au mauvais endroit, et briser la structure d'administration.

## �💡 Support Technique

### Dépannage Commun
- **Interface vide** → Vérifier console JS, s'assurer que jQuery UI est chargé
- **Drag ne fonctionne pas** → Vérifier attribut `draggable="true"` sur boutons
- **Éléments non sélectionnables** → Vérifier gestionnaires événements

### Logs de Debug
Le mode debug est activé dans `PDF_BUILDER_CONFIG.DEBUG = true`
Console JavaScript affiche toutes les étapes d'initialisation.

---

**Plugin PDF Builder Pro v5.0.0 - Interface Ultra-Moderne !** 🎉
#   F i x   c a n v a s   p r e v i e w   -   r e a d y   f o r   d e p l o y m e n t  
 