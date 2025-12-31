# 🏗️ Architecture PDF Builder Pro - Version Simplifiée

## 🎯 Vue d'ensemble

Le plugin PDF Builder Pro suit une **architecture modulaire** organisée en couches :

## 📚 Les Couches Principales

### 1. **🟢 WordPress Core** (Base)
- **Rôle** : Fournit les fondations WordPress
- **Composants** :
  - Hooks système (`plugins_loaded`, `admin_menu`)
  - Base de données (tables `wp_*`)
  - Système AJAX

### 2. **🟠 Bootstrap** (Chargement)
- **Rôle** : Initialise le plugin de manière différée
- **Fichier** : `bootstrap.php`
- **Actions** :
  - Auto-loading des classes
  - Injection de dépendances
  - Configuration initiale

### 3. **🔵 Admin Layer** (Interface)
- **Dossier** : `src/Admin/`
- **Classes principales** :
  - `PdfBuilderAdmin` - Orchestrateur principal
  - `SettingsManager` - Gestion paramètres
  - `AjaxHandler` - Endpoints AJAX
  - `TemplateManager` - CRUD templates

### 4. **🟣 Core Layer** (Logique métier)
- **Dossier** : `src/Core/`
- **Responsabilités** :
  - Cache unifié
  - Planificateur de tâches
  - Monitoring système
  - Gestion AJAX avancée

### 5. **🟠 Managers Layer** (Spécialisés)
- **Dossier** : `src/Managers/`
- **Gestionnaires** :
  - `Template_Manager` - Gestion templates
  - `PDF_Generator` - Génération PDF
  - `Asset_Optimizer` - Optimisation assets
  - `Cache_Manager` - Cache unifié
  - `WooCommerce_Integration` - E-commerce

### 6. **🟢 Frontend Layer** (Utilisateur)
- **Dossier** : `assets/`
- **Technologies** : React + TypeScript
- **Composants** :
  - Éditeur Canvas visuel
  - Panneaux de propriétés
  - Bibliothèque de templates
  - AjaxCompat (communication)

### 7. **🟡 Build System** (Compilation)
- **Dossier** : `build/`
- **Outils** : npm + webpack
- **Scripts** :
  - `npm run build` - Production
  - `deploy-simple.ps1` - Déploiement FTP

### 8. **Stockage**
- **Base de données** : Tables `wp_pdf_builder_*`
- **Système fichiers** : `wp-content/uploads/pdf-builder/`
- **Services externes** : DomPDF, FTP, WooCommerce API

---

## 🔄 Flux de Données

### **1. Initialisation**
```
WordPress → Bootstrap → Admin Layer → Core Layer → Managers Layer
```

### **2. Interaction Utilisateur**
```
Frontend (React) → AJAX → AjaxHandler → Managers → Database/File System
```

### **3. Génération PDF**
```
Template → PDF Generator → DomPDF → File System → Téléchargement
```

### **4. Mise en Cache**
```
Toutes les couches → Cache Manager → Stockage persistant
```

---

## 📁 Structure des Fichiers

```
wp-pdf-builder-pro/
├── plugin/                    # Noyau
│   ├── pdf-builder-pro.php   # Point d'entrée
│   ├── bootstrap.php         # Chargement
│   ├── src/                  # Code PHP
│   ├── assets/               # Assets compilés
│   └── resources/            # Templates
├── assets/                   # Source React/TS
├── build/                    # Déploiement
├── docs/                     # Documentation
└── tests/                    # Tests
```

---

## 🔧 Points d'Entrée

### **PHP**
- `pdf-builder-pro.php` - Activation plugin
- `src/Admin/PdfBuilderAdmin.php` - Interface admin
- `src/Managers/PDF_Builder_Cache_Manager.php` - Cache

### **JavaScript**
- `assets/js/index.js` - Application React
- `assets/js/pdf-builder-utils.js` - AjaxCompat

### **Build**
- `build/deploy-simple.ps1` - Déploiement FTP
- `package.json` - Scripts npm

---

## 🎨 Schéma Visuel

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  WordPress Core │───▶│    Bootstrap    │───▶│   Admin Layer   │
│   (Hooks, DB)   │    │  (Auto-loading) │    │ (Menu, Settings)│
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                                          │
                                                          ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Core Layer    │───▶│ Managers Layer  │◄───│ Frontend Layer  │
│ (Cache, Tasks)  │    │ (Templates, PDF)│    │   (React/TS)    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                        │                        │
         ▼                        ▼                        ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Database      │    │  File System    │    │ Build System    │
│ (wp_* tables)   │    │  (PDFs, Cache)  │    │ (npm, webpack)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

---

## 🚀 Cycle de Vie

### **Chargement Page**
1. WordPress charge le plugin
2. Bootstrap initialise les classes
3. Admin Layer crée le menu
4. Settings sont chargés

### **Édition Template**
1. Utilisateur ouvre l'éditeur
2. React charge le canvas
3. AjaxCompat fait des appels AJAX
4. TemplateManager traite les données
5. Cache Manager optimise les performances

### **Génération PDF**
1. Utilisateur clique "Générer"
2. Canvas exporte les données
3. PDF Generator traite le template
4. DomPDF crée le fichier
5. File System stocke le PDF

---

*Cette architecture modulaire permet une maintenance facile et une évolutivité optimale du plugin.*
