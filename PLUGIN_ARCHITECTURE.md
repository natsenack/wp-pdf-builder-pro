# 🏗️ Architecture du Plugin PDF Builder Pro

## Vue d'ensemble schématique

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                            PDF Builder Pro Plugin                               │
│                            ====================                               │
│                                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                        🟢 WordPress Core                                │   │
│  │                        ================                                │   │
│  │  • Hooks System (actions/filters)                                      │   │
│  │  • Database (wp_* tables)                                              │   │
│  │  • AJAX System                                                         │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                    │                                                            │
│                    ▼ 1. Initialisation                                         │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                        🟠 Bootstrap Layer                              │   │
│  │                        =================                              │   │
│  │  • bootstrap.php (chargement différé)                                 │   │
│  │  • Auto-loading des classes                                           │   │
│  │  • Injection de dépendances                                           │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                    │                                                            │
│                    ▼                                                            │
│         ┌──────────┴──────────┐                                                 │
│         ▼                     ▼                                                 │
│  ┌─────────────┐       ┌─────────────┐                                         │
│  │ 🟦 Admin    │       │ 🟣 Core      │                                         │
│  │ Layer       │       │ Layer       │                                         │
│  │             │       │             │                                         │
│  │ • Menu      │◄─────►│ • Cache     │                                         │
│  │ • Settings  │       │ • Tasks     │                                         │
│  │ • AJAX      │       │ • Monitor   │                                         │
│  │ • Templates │       │             │                                         │
│  └─────────────┘       └─────────────┘                                         │
│         │                     │                                                 │
│         ▼                     ▼                                                 │
│  ┌─────────────┐       ┌─────────────┐                                         │
│  │ 🟠 Managers │       │ 🟢 Frontend  │                                         │
│  │ Layer       │       │ Layer       │                                         │
│  │             │       │ (React/TS)  │                                         │
│  │ • Templates │       │             │                                         │
│  │ • PDF Gen   │       │ • Canvas    │                                         │
│  │ • Assets    │       │ • Editor    │                                         │
│  │ • Cache     │       │ • AjaxCompat│                                         │
│  │ • WooCommerce│      │             │                                         │
│  └─────────────┘       └─────────────┘                                         │
│         │                     │                                                 │
│         └─────────┬───────────┘                                                 │
│                   ▼ 2. User Interaction (AJAX)                                  │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │                        🟡 Build System                                 │   │
│  │                        ==============                                 │   │
│  │  • npm/webpack compilation                                            │   │
│  │  • Asset optimization                                                 │   │
│  │  • Production build                                                   │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                 │
│  ┌─────────────┐       ┌─────────────┐       ┌─────────────┐                   │
│  │ 🟢 Database │       │ 🟢 File     │       │ 🟠 External  │                   │
│  │ (MySQL)     │       │ System     │       │ Services    │                   │
│  │             │       │             │       │             │                   │
│  │ • Templates │       │ • PDFs      │       │ • DomPDF    │                   │
│  │ • Settings  │       │ • Images    │       │ • FTP       │                   │
│  │ • Logs      │       │ • Cache     │       │ • WooCommerce│                   │
│  └─────────────┘       └─────────────┘       └─────────────┘                   │
│         ▲                     ▲                     ▲                           │
│         │                     │                     │                           │
│         └─────────┬───────────┼───────────┬─────────┘                           │
│                   ▼ 3. Data Processing                                       │
│                   ▼ 4. Output Generation                                     │
└─────────────────────────────────────────────────────────────────────────────────┘
```

## 🔄 Flux de Données Détaillé

### 1. **Initialisation du Plugin**
```
WordPress Core → Bootstrap → Admin Layer → Core Layer → Managers Layer
```

### 2. **Interaction Utilisateur**
```
Frontend (React) → AJAX Call → AjaxHandler → Managers → Database/File System
```

### 3. **Génération PDF**
```
User Request → Template Manager → PDF Generator → DomPDF → File System
```

### 4. **Système de Cache**
```
Managers → Cache Manager → [Transient/File/Object Cache] → Database/File System
```

---

## 📦 Composants Détaillés

### 🟢 **Couche WordPress Core**
- **Actions/Filters** : `plugins_loaded`, `admin_menu`, `wp_ajax_*`
- **Base de données** : Tables `wp_pdf_builder_*`
- **AJAX System** : Communication client/serveur

### 🟠 **Couche Bootstrap**
```php
// bootstrap.php
add_action('plugins_loaded', function() {
    // Chargement différé
    require_once 'src/Managers/PDF_Builder_Cache_Manager.php';
    require_once 'src/Admin/PdfBuilderAdmin.php';
    // ...
});
```

### 🔵 **Couche Admin (src/Admin/)**
```
PdfBuilderAdmin (Classe principale)
├── SettingsManager (Paramètres)
├── AjaxHandler (AJAX endpoints)
├── TemplateManager (CRUD templates)
└── Permissions/Validation (Sécurité)
```

### 🟣 **Couche Core (src/Core/)**
```
PDF_Builder_Core (Noyau)
├── PDF_Builder_Cache_Manager (Cache unifié)
├── PDF_Builder_Ajax_Handler (Handler AJAX)
├── PDF_Builder_Task_Scheduler (Tâches)
└── PDF_Builder_Health_Monitor (Monitoring)
```

### 🟠 **Couche Managers (src/Managers/)**
```
Gestionnaires Spécialisés:
├── PDF_Builder_Template_Manager (Templates)
├── PDF_Builder_PDF_Generator (PDF)
├── PDF_Builder_Asset_Optimizer (Assets)
├── PDF_Builder_Cache_Manager (Cache)
├── PDF_Builder_WooCommerce_Integration (WooCommerce)
└── ...
```

### 🟢 **Couche Frontend (assets/)**
```
React/TypeScript Application:
├── Canvas Editor (Éditeur visuel)
├── Property Panels (Panneaux propriétés)
├── Template Library (Bibliothèque)
├── AjaxCompat (Communication)
└── Canvas API (Moteur de rendu)
```

### 🟡 **Système de Build (build/)**
```
Compilation et Déploiement:
├── npm run build (Production)
├── npm run dev (Développement)
├── deploy-simple.ps1 (FTP)
└── webpack.config.cjs (Configuration)
```

---

## 🔗 Interactions Entre Composants

### **Communication AJAX**
```
Frontend (React) ↔ AjaxHandler ↔ Managers ↔ Database
```

### **Système de Cache**
```
Toutes les couches → Cache Manager → Stockage (DB/Fichier/Mémoire)
```

### **Génération PDF**
```
Template Manager → PDF Generator → DomPDF → File System
```

### **Intégration WooCommerce**
```
Hooks WooCommerce → WC Integration → PDF Generator → Email/Order
```

---

## 🗂️ Structure des Fichiers

```
wp-pdf-builder-pro/
├── plugin/                          # Noyau plugin
│   ├── pdf-builder-pro.php         # Point d'entrée
│   ├── bootstrap.php               # Chargement différé
│   ├── src/                        # Code source PHP
│   │   ├── Admin/                  # Interface admin
│   │   ├── Core/                   # Logique métier
│   │   ├── Managers/               # Gestionnaires
│   │   └── ...
│   ├── assets/                     # Assets compilés
│   ├── resources/                  # Templates/statiques
│   └── vendor/                     # Dépendances externes
├── assets/                         # Source React/TypeScript
├── build/                          # Scripts déploiement
├── docs/                           # Documentation
└── tests/                          # Tests unitaires
```

---

## 🎯 Points d'Entrée Principaux

### **PHP**
- `pdf-builder-pro.php` : Activation/désactivation
- `bootstrap.php` : Initialisation des classes
- `src/Admin/PdfBuilderAdmin.php` : Interface admin
- `src/Managers/PDF_Builder_Cache_Manager.php` : Cache unifié

### **JavaScript**
- `assets/js/index.js` : Point d'entrée React
- `assets/js/pdf-builder-utils.js` : Utilitaires AjaxCompat
- `assets/js/canvas.js` : API Canvas

### **Build System**
- `build/deploy-simple.ps1` : Déploiement FTP
- `package.json` : Scripts npm
- `webpack.config.cjs` : Configuration compilation

---

## 🔄 Cycle de Vie d'une Requête

### **1. Chargement de la Page**
```
WordPress → Plugin Activation → Bootstrap → Admin Menu → Settings Page
```

### **2. Édition d'un Template**
```
User Clicks → React Component → AjaxCompat → AJAX Call → AjaxHandler → Template Manager → Database
```

### **3. Génération PDF**
```
User Action → Canvas Export → PDF Generator → DomPDF → File Storage → Download
```

### **4. Mise en Cache**
```
Data Request → Cache Manager → Check Cache → [Hit: Return Cached] / [Miss: Process + Cache]
```

---

## 🛠️ Outils et Utilitaires

### **Debugging**
- `pdfBuilderCheckCSS()` : Vérifier cache CSS
- `pdfBuilderCheckJSCache()` : Vérifier cache JS
- `canvasMemoryDebug.getCacheStats()` : Stats cache canvas

### **Maintenance**
- `wp pdf-builder check-integrity` : Vérifier intégrité
- `npm run build` : Compiler assets
- `./deploy-simple.ps1` : Déployer en production

### **Monitoring**
- Logs PHP : `wp-content/debug.log`
- Métriques cache : Interface admin onglet Cache
- Stats performance : Console développeur

---

## 📊 Métriques et Monitoring

### **Cache Performance**
- Hits/Misses ratio
- Utilisation mémoire
- Nombre d'éléments actifs
- Temps de réponse

### **Génération PDF**
- Temps de rendu
- Taille des fichiers
- Taux de succès
- Erreurs DomPDF

### **Utilisation Système**
- Requêtes AJAX/minute
- Templates actifs
- Stockage utilisé
- Performances générales

---

*Ce schéma représente l'architecture complète du plugin PDF Builder Pro, montrant comment chaque composant interagit avec les autres pour fournir une expérience utilisateur fluide et performante.*
