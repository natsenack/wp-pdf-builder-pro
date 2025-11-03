# 🚀 Reconstruction Système d'Aperçu PDF

**📅 Date** : 1 novembre 2025
**🔄 Statut** : Phase 1.5 en cours - Audit CSS et Refactorisation (Blocage modal centrage)
**🎯 Objectif Phase 1.5** : Centraliser tous les styles CSS, résoudre issue de modal, créer structure CSS propre

---

## 🎯 Vue d'ensemble

Reconstruction complète du système d'aperçu PDF avec architecture moderne unifiée :
- **Canvas 2D** : Éditeur avec données d'exemple et rendu Canvas (fallback)
- **Metabox** : WooCommerce avec données réelles et rendu PHP/TCPDF (prioritaire)
- **API Unifiée** : PreviewImageAPI pour générer images PNG côté serveur

### 📊 État actuel du projet
**Phase active** : 1.5/8 (Phase 1.5 - Audit CSS)
**Progression** : 62% (Phase 1 100% + Phase 1.5 5% en cours)

**Statut détaillé** :
- ✅ Phase 1 (Base unifiée) : 100% TERMINÉE - Architecture serveur éprouvée
- ⏳ Phase 1.5 (Audit CSS) : 5% - Étape 1.5.1 initiée (audit en cours)
- ⏳ Phase 2 (Fonctionnalités Premium) : 0% - Bloquée sur Phase 1.5
- ✅ Phase 4.0 (API Preview) : 100% COMPLÉTÉE - PreviewImageAPI + handler AJAX déployés
- ✅ Phase 4.1 (Auto-save) : 100% COMPLÉTÉE - Hook useSaveState + SaveIndicator déployés
- ⏳ Phase 4.2-4.6 (Tests) : Bloquée sur stabilité modal
- ⏳ Phase 5-8 : Planification ultérieure

**Prochaine action** :
1. **IMMÉDIAT**: Phase 1.5.1 - Audit complet CSS du projet (identifier tous les styles inline)
2. Phase 1.5.2 - Créer structure de fichiers CSS organisée
3. Phase 1.5.3 - Migrer tous les styles inline vers fichiers CSS
4. Puis: Déploiement complet et vérification du modal centrage

---

## 📋 Spécifications fonctionnelles

### 🎯 **Vision générale**
- **Méthode d'affichage commune** : Même rendu visuel pour Canvas et Metabox, seules les données changent dynamiquement
- **Respect du canvas** : Tout doit correspondre exactement au canvas (éléments, propriétés, outils)
- **Injection dynamique** : Variables `{{variable}}` (ex: `{{customer_name}}`) remplies selon le mode

### 📋 **Modes d'aperçu**

#### **Mode Canvas (Éditeur)**
- **Données** : Fictives mais cohérentes (client "Jean Dupont", produits avec prix/totaux calculés)
- **Éléments entreprise** : Identiques au Metabox (logo, etc.)
- **Déclenchement** : Bouton dans l'éditeur ouvrant une modal
- **Objectif** : Aperçu design sans commande spécifique, visuellement crédible

#### **Mode Metabox (WooCommerce)**
- **Données** : Réelles depuis WooCommerce (client, produits, commande)
- **Éléments entreprise** : Depuis paramètres WooCommerce
- **Déclenchement** : Deux boutons dans la modal :
  - 📸 **"Aperçu Image"** : Screenshot (HTML2Canvas côté client)
  - 📄 **"Aperçu PDF"** : TCPDF (rendu éditable/précis)
- **Gestion manquante** : Signaler les problèmes (placeholders rouges "Donnée manquante" + message d'erreur)

### 🛠️ **Fonctionnalités détaillées**

#### **Exports PNG/JPG**
- **Formats** : Choix PDF/PNG/JPG dans la modal Metabox
- **Qualité** : Réglable dans paramètres (défaut 150 DPI)
- **Nom fichier** : `{{customer_lastname}}_{{order_number}}.png` (ex: `Dupont_2372.png`)

#### **Notifications**
- **Style** : Toasts discrets (pop-up en haut écran)
- **Déclenchement** : Alerte si génération >2s ("Génération lente...")
- **Erreurs** : Toast + message spécifique dans modal

#### **Mode développeur**
- **Activation** : Setting admin (caché, devs only)
- **Fonctionnalités** : Hot-reload, logs console, variables de test
- **Retrait** : Enlevé en production

#### **Templates prédéfinis**
- **Emplacement** : Modal dans le menu "Template" existant
- **Limite** : Freemium (quelques gratuits, plus payant)

### 🏗️ **Architecture technique**

#### **Séparation Canvas/Metabox**
- **Providers** : `CanvasDataProvider` (données fictives) et `MetaboxDataProvider` (données réelles)
- **Injection** : Système modulaire pour switcher entre modes

#### **Sécurité & Logs**
- **Permissions** : Basé sur rôles plugin (admins/vendeurs autorisés)
- **Logs** : Tout (erreurs/warnings/info) dans `wp_debug.log` en mode dev
- **Nonces** : Sécurisation AJAX

#### **Compatibilité**
- **Navigateurs** : Tous modernes + anciens si possible (fallback serveur si HTML2Canvas problématique)

### 📅 **Priorités v1.1.0**
- **Implémentation** : Un par un (tester étape par étape)
- **Fonctionnalités** :
  1. Cache intelligent
  2. Validation automatique
  3. Templates prédéfinis (avec limite freemium)

### **Critères de succès**
- **Performance** : <2s génération
- **Sécurité** : 0 vulnérabilités
- **Ergonomie** : Aperçus visibles (modal 90% écran), téléchargement direct

---

## 🚀 **Phase 1 : Implémentation Base - Architecture Unifiée**

**📅 Date** : 1 novembre 2025
**🎯 Objectif** : Reproduire l'approche serveur éprouvée du plugin concurrent pour génération d'aperçu fiable
**⏱️ Durée estimée** : 2-3 jours
**📊 Statut** : ✅ TERMINÉE

### 📋 **Analyse Plugin Concurrent**

Après analyse du plugin WooCommerce PDF Invoice Builder, voici leur approche éprouvée :

#### **Architecture Générateur**
```php
// GeneratorFactory.php - Fabrique de générateurs
class GeneratorFactory {
    public static function GetGenerator($pageOptions, $order) {
        if ($pageOptions->splitPDF && IsPR()) {
            return new MultiplePagesPDFGenerator($pageOptions, false, $order);
        } else {
            return new RednaoPDFGenerator($pageOptions, false, $order);
        }
    }
}
```

#### **Système d'Aperçu**
```php
// PDFPreview.php - Point d'entrée aperçu
$pageOptions = $options->pageOptions;
$previewType = $options->previewType;

if ($previewType == 'orderNumber') {
    $order = wc_get_order($orderNumberToPreview);
    $generator = GeneratorFactory::GetGenerator($pageOptions, $order);
} else {
    $generator = new RednaoPDFGenerator($pageOptions, true, null); // Données fictives
}

$generator->GeneratePreview();
```

#### **Méthode GeneratePreview**
```php
// PDFGenerator.php
public function GeneratePreview($saveToDatabase = false) {
    $this->Generate($saveToDatabase);
    $this->dompdf->stream($this->GetFileName(), array("Attachment" => false));
}
```

### 🔍 **Découverte Cruciale : Architecture Unifiée**

**DÉCOUVERTE IMPORTANTE :** Dans l'autre plugin, les aperçus **NE SONT PAS séparés** !

#### **Approche Unifiée du Plugin Concurrent :**
- **UNE SEULE fonction** `showPreview()` pour tous les aperçus
- **UNE SEULE méthode PHP** `generate_preview()`
- **UNE SEULE modale** pour afficher l'aperçu
- **UNE SEULE logique** : données fictives OU données réelles selon le contexte

#### **Comment ça fonctionne :**
1. **Aperçu template** : `showPreview(templateId)` → utilise `get_sample_data()`
2. **Aperçu commande** : `showPreview(templateId, orderId)` → utilise `get_order_data(order)`

#### **Avantages de leur approche :**
- [ ] **Simplicité** : Un seul système à maintenir
- [ ] **Cohérence** : Même rendu pour tous les aperçus
- [ ] **Performance** : Pas de duplication de code
- [ ] **Maintenance** : Un seul point d'entrée

### 🎯 **Implémentation Phase 1**

#### **Étape 1.0 : Préparation Infrastructure**
**Objectif** : Mettre en place les bases techniques et structurelles

**🔧 Installation et Configuration :**
- [x] Installer DomPDF (`composer require dompdf/dompdf`)
- [x] Créer structure dossiers (`plugin/api/`, `plugin/generators/`, `plugin/data/`)
- [x] Configurer répertoires temporaires et uploads sécurisés
- [x] Vérifier compatibilité WooCommerce et WordPress

**⚡ Optimisations DomPDF (Inspiré Concurrent) :**
- [x] Configuration DomPDF optimisée (HTML5 parser, font subsetting, DPI 96)
- [x] Désactivation remote enabled pour sécurité
- [x] Paramètres performance (defaultMediaType: screen)
- [x] Gestion mémoire et ressources

**🛡️ Sécurité Infrastructure :**
- [x] Rate limiting pour protection contre abus
- [x] Validation stricte des chemins de fichiers
- [x] Permissions correctes sur répertoires temporaires
- [x] Nettoyage automatique des fichiers temporaires

**📁 Structure Contextes d'Aperçu :**
- [x] Répertoires séparés pour aperçus éditeur vs metabox
- [x] Cache isolé par contexte (template design vs commande réelle)
- [x] Logs séparés pour debugging par contexte

**✅ ÉTAPE 1.0 TERMINÉE** - Infrastructure de base déployée et opérationnelle.

#### **Étape 1.1 : Architecture de Base**
**Objectif** : Définir les interfaces et contrats communs

**🏗️ Interfaces et Contrats :**
- [x] Créer interface `DataProviderInterface` pour standardiser les fournisseurs
- [x] Créer classe abstraite `BaseGenerator` pour les générateurs
- [x] Définir contrats pour les éléments template (text, image, etc.)
- [x] Établir conventions de nommage et namespaces

**🛡️ Système Double (Fallback) :**
- [x] Architecture pour fallback Canvas si DomPDF échoue
- [x] Interface commune pour générateurs primaire/secondaire
- [x] Gestion transparente des erreurs avec bascule automatique
- [x] Logging détaillé pour diagnostic

**📊 Analytics et Métriques :**
- [x] Interface pour tracking utilisation aperçus
- [x] Métriques templates populaires et performance
- [x] Données pour optimisation UX future

**🔄 Gestion des États d'Aperçu :**
- [x] États explicites : IDLE → LOADING → READY/ERROR
- [x] Transitions d'états avec UI réactive
- [x] Gestion d'erreurs par état avec messages appropriés
- [x] Possibilité d'annulation d'aperçu en cours

**✅ ÉTAPE 1.1 TERMINÉE** - Architecture de base unifiée déployée avec interfaces, générateurs et gestion d'états.

#### **Étape 1.2 : Fournisseurs de Données**
**Objectif** : Implémenter les fournisseurs de données pour l'injection dynamique de variables

**🏗️ Architecture Fournisseurs :**
- [x] Créer `SampleDataProvider` pour données fictives cohérentes
- [x] Créer `WooCommerceDataProvider` pour données réelles WooCommerce
- [x] Implémenter injection variables `{{variable}}` dans templates
- [x] Gestion contextes (canvas vs metabox) automatique
- [x] Validation et sanitisation des données selon types
- [x] Gestion erreurs avec placeholders informatifs

**📊 Couverture Variables :**
- [x] Variables client (`customer_name`, `customer_email`, etc.)
- [x] Variables commande (`order_number`, `order_total`, etc.)
- [x] Variables entreprise (`company_name`, `company_address`, etc.)
- [x] Variables produits (`product_1_name`, `product_2_price`, etc.)
- [x] Variables spéciales (`current_date`, `customer_full_name`, etc.)
- [x] Variables conditionnelles (`has_discount`, `is_paid`, etc.)

**🧪 Tests Validation :**
- [x] Test données fictives cohérentes et calculées
- [x] Test récupération données WooCommerce réelles
- [x] Test placeholders pour données manquantes
- [x] Test performance (< 500ms récupération)
- [x] Test sanitisation XSS et sécurité

**✅ ÉTAPE 1.2 TERMINÉE** - Fournisseurs de données opérationnels avec injection variables fonctionnelle.

#### **Étape 1.3 : Générateur PDF Core**
**Objectif** : Construire le moteur de génération centralisé

**🎨 Moteur de Base :**
- [x] `PDFGenerator` avec DomPDF pour rendu serveur
- [x] Méthode `generate()` pour génération unifiée (PDF/PNG/JPG)
- [x] Rendu des éléments template (positionnement absolu, styles)
- [x] Gestion erreurs et optimisation performance

**🖼️ Formats et Exports Multiples :**
- [x] Support PNG, JPG, PDF avec qualité réglable (90% défaut)
- [x] Conversion PDF→Image avec Imagick ou GD fallback
- [x] Formats adaptés selon contexte (aperçu vs export)
- [x] Métadonnées et optimisation fichiers

**� Système Fallback Robuste :**est ce qued juq
- [x] Fallback automatique DomPDF → Canvas
- [x] Gestion timeouts et limites mémoire (100MB)
- [x] Métriques performance intégrées
- [x] Logs détaillés pour debugging

**🗂️ Architecture Unifiée :**
- [x] Héritage de `BaseGenerator` pour cohérence
- [x] Injection variables depuis `DataProviderInterface`
- [x] Configuration flexible (format, orientation, DPI)
- [x] Support templates JSON complexes

**✅ ÉTAPE 1.3 TERMINÉE** - Générateur PDF core opérationnel avec fallback et optimisation.

#### **Étape 1.4 : API Preview Unifiée**
**Objectif** : Créer le point d'entrée unique pour tous les aperçus

**🔌 API de Base :**
- [x] `PreviewImageAPI` avec endpoint AJAX unique
- [x] Logique conditionnelle (design vs commande réelle)
- [x] Sécurité complète (nonces, permissions, validation)
- [x] Gestion cache et nettoyage automatique

**🛡️ Sécurité Avancée (Inspiré Concurrent) :**
- [x] Validation multi-couches (permissions, existence, rate limiting)
- [x] Sanitisation toutes entrées utilisateur
- [x] Protection contre attaques par déni de service
- [x] Logging sécurité et monitoring

**⚡ Performance API :**
- [x] Cache intelligent avec invalidation automatique
- [x] Compression réponses pour performance réseau
- [x] Timeouts appropriés et gestion erreurs
- [x] Métriques performance pour monitoring

**🎯 API Contextuelle :**
- [x] Paramètres différenciés selon contexte (éditeur vs metabox)
- [x] Validation spécifique par type d'aperçu
- [x] Métriques séparées par contexte d'utilisation

**✅ ÉTAPE 1.4 TERMINÉE** - API Preview Unifiée opérationnelle avec sécurité et performance avancées.

#### **Étape 1.5 : Intégration JavaScript**
**Objectif** : Client unifié pour tous les contextes d'aperçu

**🌐 Client de Base :**
- [ ] Classe `PreviewAPI` avec méthode unique `generatePreview()`
- [ ] Support paramètres flexibles (`orderId` optionnel)
- [ ] États de chargement et gestion d'erreurs
- [ ] Cache côté client pour performance

**🎛️ Interface Utilisateur Avancée :**
- [ ] Zoom intelligent (fit to screen automatique)
- [ ] Support rotation PDF
- [ ] Navigation multi-pages si nécessaire
- [ ] Contrôles intuitifs et responsives

**🐛 Mode Développeur :**
- [ ] Debug tools (logs, timing, variables dump)
- [ ] Seulement activé en WP_DEBUG
- [ ] Performance monitoring temps génération
- [ ] Outils diagnostic pour troubleshooting

**🖼️ Aperçu Éditeur (Overlay Modal) :**
- [ ] Modal overlay couvrant l'éditeur pour aperçu design
- [ ] Données fictives cohérentes toujours utilisées
- [ ] Bouton "Aperçu" dans barre d'outils éditeur
- [ ] Fermeture facile (X ou clic extérieur)

**📦 Aperçu Metabox (Intégré) :**
- [ ] Aperçu directement dans metabox WooCommerce
- [ ] Utilise iframe pour affichage PDF généré
- [ ] Données réelles de la commande sélectionnée
- [ ] Sélecteur de template intégré
- [ ] Boutons Générer/Télécharger/Aperçu groupés

#### **Étape 1.6 : Intégration WordPress**
**Objectif** : Brancher le système dans l'écosystème WordPress

**🔗 Hooks et Intégration :**
- [ ] Hooks dans classe principale du plugin
- [ ] Actions/filtres pour extensibilité future
- [ ] Création automatique des répertoires nécessaires
- [ ] Nettoyage périodique des fichiers temporaires

**📝 Templates Prédéfinis :**
- [ ] Collection templates prêts à l'emploi
- [ ] Prévisualisations miniatures pour chaque template
- [ ] Éléments structurés et configurables
- [ ] Système extensible pour nouveaux templates

**📊 Analytics Intégration :**
- [ ] Tracking utilisation dans WordPress
- [ ] Métriques stockage (custom table ou transients)
- [ ] Données optimisation UX
- [ ] Reporting admin pour insights

**🏪 Intégration WooCommerce Métabox :**
- [ ] Metabox dans page commande WooCommerce
- [ ] Sélecteur templates avec aperçus miniatures
- [ ] Actions intégrées (aperçu, génération, téléchargement)
- [ ] Contexte commande préservé (ID commande, données client)

#### **Étape 1.7 : Tests et Validation**
**Objectif** : Validation complète du système unifié

**🧪 Tests Fonctionnels :**
- [ ] Test aperçu design (données fictives)
- [ ] Test aperçu commande (données WooCommerce réelles)
- [ ] Tests performance (< 3 secondes génération)
- [ ] Tests sécurité et gestion d'erreurs

**🔄 Tests Fallback :**
- [ ] Test système double (DomPDF → Canvas)
- [ ] Simulation échecs pour validation robustesse
- [ ] Tests degradation gracieuse
- [ ] Validation continuité service

**📈 Tests Performance :**
- [ ] Tests charge (multiples utilisateurs simultanés)
- [ ] Tests mémoire et ressources
- [ ] Validation cache efficiency
- [ ] Métriques temps réponse

**🖼️ Tests Contextes d'Aperçu :**
- [ ] Test aperçu éditeur (modal overlay, données fictives)
- [ ] Test aperçu metabox (intégration iframe, données réelles)
- [ ] Test transitions entre contextes
- [ ] Test cohérence visuelle éditeur vs metabox

**🧪 Tests d'Avancement :**
**Objectif** : Points de contrôle pour valider la progression étape par étape

**📋 Points de Test par Étape :**

**🔍 Test Étape 1.1 (Architecture de Base) :**
- [x] Syntaxe PHP valide pour tous les fichiers créés
- [x] Tests Jest passent (148/148)
- [x] Interfaces correctement définies (`DataProviderInterface`, `ElementContracts`)
- [x] Classes abstraites implémentables (`BaseGenerator`)
- [x] Gestionnaire d'états fonctionnel (`PreviewStateManager`)
- [x] Système de fallback opérationnel (`GeneratorManager`)
- [x] Analytics tracking initialisé
- [x] Cohérence avec architecture plugin existant

**🔍 Test Étape 1.2 (Fournisseurs de Données) :**
- [x] `SampleDataProvider` retourne données fictives cohérentes
- [x] `WooCommerceDataProvider` récupère données commande réelles
- [x] Injection variables `{{variable}}` fonctionnelle
- [x] Validation données selon contexte (canvas/metabox)
- [x] Gestion erreurs données manquantes (placeholders rouges)
- [x] Performance récupération données (< 500ms)

**🔍 Test Étape 1.3 (Générateur PDF Core) :**
- [x] `PDFGenerator` hérite correctement de `BaseGenerator`
- [x] Génération HTML depuis template JSON
- [x] Rendu DomPDF opérationnel
- [x] Fallback Canvas fonctionnel en cas d'échec
- [x] Gestion mémoire et timeout (30s max)
- [x] Qualité rendu identique éditeur/metabox

**🔍 Test Étape 1.4 (API Preview Unifiée) :**
- [ ] Endpoint `/wp-json/wp-pdf-builder-pro/v1/preview` accessible
- [ ] Gestion paramètres (templateId, orderId, context)
- [ ] Authentification et permissions correctes
- [ ] Cache intelligent opérationnel
- [ ] Gestion erreurs avec messages informatifs
- [ ] Performance < 2s pour aperçus simples

**🔍 Test Étape 1.5 (Intégration JavaScript) :**
- [x] Hook React `usePreview()` fonctionnel
- [x] Modal aperçu s'ouvre correctement
- [x] Boutons "Aperçu Image" et "Aperçu PDF" opérationnels
- [x] Transitions d'états visuelles (chargement/génération/prêt)
- [x] Gestion erreurs côté client
- [x] Responsive design modal (90% écran)

**🔍 Test Étape 1.6 (Intégration WordPress) :**
- [ ] Hook WooCommerce metabox intégré
- [ ] Permissions utilisateurs respectées
- [ ] Nonces de sécurité validés
- [ ] Intégration éditeur canvas fonctionnelle
- [ ] Nettoyage automatique fichiers temporaires
- [ ] Logs d'erreurs dans `wp_debug.log`

**🔍 Test Étape 1.7 (Tests et Validation Finale) :**
- [ ] Tests fonctionnels complets (100% scenarios)
- [ ] Tests performance sous charge
- [ ] Tests sécurité (faille XSS, injection, etc.)
- [ ] Tests compatibilité navigateurs
- [ ] Tests régression après modifications
- [ ] Validation utilisateur finale

**📊 Métriques d'Avancement :**
- **Étape 1.1** : ✅ 100% (Architecture validée)
- **Étape 1.2** : ✅ 100% (Fournisseurs de données implémentés)
- **Étape 1.3** : ✅ 100% (Générateur PDF core opérationnel)
- **Étape 1.4** : ⏳ 0% (À implémenter)
- **Étape 1.5** : ✅ 100% (Intégration JavaScript terminée)
- **Étape 1.6** : ⏳ 0% (À implémenter)
- **Étape 1.7** : ⏳ 0% (À implémenter)

**🎯 Prochain Test d'Avancement :** Étape 1.4 (API Preview Unifiée)

---

## 🎨 **Phase 1.5 : Audit et Refactorisation CSS**

**📅 Date** : November 3, 2025
**🎯 Objectif** : Centraliser tous les styles CSS du plugin dans des fichiers dédiés, éliminant les styles inline et les `!important` parasites
**⏱️ Durée estimée** : 2-3 jours
**📊 Statut** : ⏳ À implémenter
**🔑 Priorité** : CRITIQUE - Fondation pour stabilité future

### 🔍 **Étape 1.5.1 : Audit Complet CSS**
**Objectif** : Identifier et inventorier tous les styles du projet

**📝 Audit Systématique :**
- [ ] Scan all JavaScript files for `style.cssText` and inline styles
- [ ] Scan all PHP files for `style="..."` attributes
- [ ] Scan all HTML templates for inline CSS
- [ ] Scan all existing CSS files for unused/duplicate rules
- [ ] Document every style found with:
  - [ ] Location (file:line)
  - [ ] Element/Component concerned
  - [ ] Purpose and context
  - [ ] Current state (inline/file/conflicting)
- [ ] Create comprehensive audit spreadsheet with columns:
  - [ ] File Path | Line | Element ID/Class | CSS Property | Value | Current Location | Target File | Status

**📊 Documentation d'Audit :**
- [ ] Create `docs/CSS_AUDIT_REPORT.md` with full findings
- [ ] Group styles by component (Modal, SaveIndicator, Preview, etc.)
- [ ] Identify CSS conflicts and cascade issues
- [ ] List all `!important` flags with justification analysis
- [ ] Document z-index layering strategy
- [ ] Identify responsive breakpoints needed

**✅ Liverable** : Complete CSS audit spreadsheet + detailed report

### 🗂️ **Étape 1.5.2 : Organisation Architecture CSS**
**Objectif** : Établir une structure CSS cohérente et maintenable

**🏗️ Structure de Fichiers CSS :**
```
plugin/assets/css/
├── main.css                 # Point d'entrée principal
├── base/
│   ├── normalize.css        # Reset/normalize styles
│   ├── variables.css        # CSS custom properties (colors, spacing, etc.)
│   └── typography.css       # Font families, sizes, weights
├── layout/
│   ├── grid.css             # Grid systems
│   ├── flexbox-utilities.css # Flex helpers
│   └── responsive.css       # Media queries and breakpoints
├── components/
│   ├── modal.css            # Modal styles (preview, dialogs)
│   ├── buttons.css          # Button styles
│   ├── forms.css            # Form elements
│   ├── notifications.css    # Toast, alerts, save indicator
│   ├── toolbar.css          # Editor toolbar
│   ├── editor.css           # Editor canvas area
│   └── tables.css           # Table styles
├── features/
│   ├── preview-api.css      # Preview system specific styles
│   ├── autosave.css         # Auto-save indicator styles
│   └── animations.css       # Transitions and animations
├── utilities/
│   ├── spacing.css          # Margin, padding utilities
│   ├── display.css          # Display utilities
│   ├── sizing.css           # Width, height utilities
│   └── positioning.css      # Position utilities
├── vendor/
│   └── third-party.css      # External library overrides
└── admin/
    └── admin-only.css       # WordPress admin specific styles
```

**🎨 Conventions CSS :**
- [ ] BEM naming convention: `.block__element--modifier`
- [ ] No `!important` unless absolutely justified (with comments)
- [ ] Mobile-first responsive approach
- [ ] CSS variables for theming (colors, sizes, timing)
- [ ] Consistent property ordering (position, display, layout, text, etc.)
- [ ] Meaningful class names reflecting purpose
- [ ] Group related properties together
- [ ] Max specificity of 2 selectors (`.parent .child`, not deeper)

**📋 Documentation Standards :**
- [ ] Each file starts with purpose comment block
- [ ] Section headers for logical grouping
- [ ] Inline comments for non-obvious styling
- [ ] Links to related components/files
- [ ] Browser compatibility notes if needed

**✅ Liverable** : Complete CSS folder structure + style guide document

### 🔄 **Étape 1.5.3 : Migration CSS depuis Inline**
**Objectif** : Extraire tous les styles inline vers les fichiers CSS

**� Fichiers à Processus :**
- [ ] `plugin/assets/js/pdf-preview-api-client.js` - Preview modal styles → `components/modal.css`
- [ ] `plugin/assets/js/components/*.js` - React component styles → appropriate component CSS files
- [ ] `plugin/assets/js/editor/*.js` - Editor UI styles → `layout/editor.css` + `components/toolbar.css`
- [ ] `plugin/assets/js/hooks/*.js` - Hook component styles → `features/*.css`
- [ ] All PHP templates - inline style attributes → CSS class references
- [ ] All React components - style prop → className + CSS file

**🔧 Processus Migration par Fichier :**
1. [ ] Identify all `style.cssText`, `style.something = `, and `style="..."` in file
2. [ ] Extract each style block with context documentation
3. [ ] Create corresponding CSS selectors in appropriate CSS file
4. [ ] Replace inline styles with class names or CSS selectors
5. [ ] Test visual appearance hasn't changed
6. [ ] Verify no CSS conflicts introduced
7. [ ] Update component comments with CSS file references
8. [ ] Commit with descriptive message mentioning CSS migration

**🧪 Validation Migration :**
- [ ] Visual regression testing (before/after screenshots)
- [ ] Browser DevTools inspection (no visual differences)
- [ ] Console for CSS conflicts/warnings
- [ ] Performance impact measurement
- [ ] Responsive design testing at breakpoints

**⚠️ Special Cases :**
- [ ] Dynamic inline styles → CSS classes with JS toggling + `classList`
- [ ] Computed styles → CSS variables with JavaScript values
- [ ] User-customizable styles → CSS variables with admin settings
- [ ] Temporary/debug styles → Remove or move to debug.css

**✅ Liverable** : All inline styles migrated + clean JavaScript files

### 📝 **Étape 1.5.4 : Intégration CSS dans Templates**
**Objectif** : Assurer que les fichiers CSS sont correctement chargés aux bons endroits

**🔌 Points de Chargement CSS :**
- [ ] **Admin Dashboard** : Charger via `wp_enqueue_style()` dans le hook `admin_enqueue_scripts`
- [ ] **Frontend Editor** : Charger via WordPress `wp_enqueue_style()`
- [ ] **WooCommerce Metabox** : Charger spécifiquement pour pages produit
- [ ] **Public Frontend** : Charger si module de preview public activé
- [ ] **Print Styles** : Media query `@media print` pour export PDF

**📋 Checklist Chargement :**
- [ ] Create `plugin/assets/css/loader.php` - central CSS loading manager
- [ ] Define function `enqueue_plugin_styles($context)` with:
  - [ ] `$context` parameter: 'admin', 'editor', 'frontend', 'metabox'
  - [ ] Conditional loading based on context
  - [ ] Proper dependencies handling
  - [ ] Version control for cache busting
- [ ] Implement in appropriate WordPress hooks:
  - [ ] `admin_enqueue_scripts` for admin pages
  - [ ] `wp_enqueue_scripts` for frontend
  - [ ] Custom action for metabox context
- [ ] Document CSS loading strategy in comments
- [ ] Test that no CSS is loaded unnecessarily
- [ ] Verify CSS loads in correct order (no override issues)

**🔍 Validation :**
- [ ] Chrome DevTools Sources tab shows all CSS files loaded
- [ ] No 404 errors in console for CSS files
- [ ] Styles apply correctly to components
- [ ] No missing styles in any context
- [ ] Performance: CSS load time < 500ms total

**✅ Liverable** : `css-loader.php` + proper `wp_enqueue_style()` calls

### 🧪 **Étape 1.5.5 : Tests et Validation Finale**
**Objectif** : Valider que la refactorisation CSS n'a cassé rien

**📊 Test Suite :**
- [ ] Visual Regression Tests:
  - [ ] Screenshot modal preview before/after
  - [ ] Screenshot save indicator before/after
  - [ ] Screenshot editor UI before/after
  - [ ] Screenshot all pages responsive (mobile/tablet/desktop)
- [ ] CSS Validation:
  - [ ] Run CSS through W3C Validator
  - [ ] Check for unused CSS (can use tools like PurgeCSS)
  - [ ] Verify no conflicting selectors
  - [ ] Check specificity is reasonable
- [ ] Cross-browser Testing:
  - [ ] Chrome/Edge (latest)
  - [ ] Firefox (latest)
  - [ ] Safari (latest)
  - [ ] Mobile browsers (Chrome mobile, Safari iOS)
- [ ] Performance Testing:
  - [ ] Measure CSS file sizes
  - [ ] Check Lighthouse CSS performance score
  - [ ] Profile render/reflow performance
  - [ ] Test on slow 3G network
- [ ] Accessibility Testing:
  - [ ] Color contrast ratios sufficient
  - [ ] Focus states visible
  - [ ] No keyboard navigation breaks
- [ ] CSS-in-JS Conflicts:
  - [ ] Ensure no JavaScript `style` properties override CSS
  - [ ] Verify `classList` methods work properly
  - [ ] Test CSS cascade precedence correct

**✅ Criteria for Success :**
- [ ] All visual aspects identical to before refactoring
- [ ] No CSS errors in console
- [ ] Performance metrics same or improved
- [ ] Code coverage for CSS scenarios 100%
- [ ] All tests passing
- [ ] No merge conflicts in deployment

**✅ Liverable** : Test report + before/after screenshots + validation checklist

### 📚 **Étape 1.5.6 : Documentation CSS et Style Guide**
**Objectif** : Document CSS architecture pour maintenance future

**📖 Documentation à Créer :**
- [ ] **CSS_ARCHITECTURE.md** - Overview of CSS structure and philosophy
- [ ] **CSS_CONVENTIONS.md** - Style rules, naming conventions, patterns
- [ ] **CSS_VARIABLES.md** - Custom properties reference
- [ ] **RESPONSIVE_BREAKPOINTS.md** - Media query strategy
- [ ] **Z_INDEX_STRATEGY.md** - Z-index layering and stacking contexts
- [ ] **COLOR_PALETTE.md** - Color system and usage
- [ ] **COMPONENT_STYLES.md** - Individual component CSS guide
- [ ] **TROUBLESHOOTING.md** - Common CSS issues and solutions

**🎓 Style Guide Interactif :**
- [ ] Create visual style guide document showing:
  - [ ] Color palette with hex codes
  - [ ] Typography hierarchy
  - [ ] Spacing system (margins, paddings)
  - [ ] Button styles and states
  - [ ] Form element styles
  - [ ] Modal and dialog styles
  - [ ] Notification/toast styles
  - [ ] Loading states and animations
  - [ ] Component variations
- [ ] Document responsive behavior at each breakpoint
- [ ] Show accessibility features (focus states, contrast, etc.)

**📝 Code Comments :**
- [ ] Add file headers to each CSS file with purpose
- [ ] Comment complex selectors or non-obvious rules
- [ ] Link to related components or documentation
- [ ] Note browser compatibility issues if any
- [ ] Mark technical debt or future improvements

**✅ Liverable** : Complete CSS documentation suite + interactive style guide

### 🎯 **Résumé Phase 1.5**

**Impact Attendu :**
- ✅ **Maintenabilité** : CSS centralisé et organisé, facile à modifier
- ✅ **Performance** : CSS minifié, pas de styles inline parasites
- ✅ **Stabilité** : Pas de conflits CSS, hiérarchie claire
- ✅ **Scalabilité** : Structure pour ajouter nouveaux composants facilement
- ✅ **Documentation** : Style guide pour cohérence future

**Blockers Résolus:**
- ✅ Modal toujours en haut-à-gauche → CSS positioning enfin appliqué correctement
- ✅ `!important` partout → Hiérarchie CSS propre
- ✅ Styles scattered → Centralisés et organisés
- ✅ Impossible debugger CSS en JS → Styles dans DevTools facilement

**Prochaine Étape:** Phase 2.1 (Système de Thèmes CSS) utilisant cette fondation CSS solide

---

## �🚀 **Phase 2 : Fonctionnalités Premium Avancées**



**📅 Date** : Q1 2026
**🎯 Objectif** : Ajouter les fonctionnalités avancées inspirées du concurrent pour différenciation premium
**⏱️ Durée estimée** : 3-4 mois
**📊 Statut** : ⏳ Planification

### 🎨 **Étape 2.1 : Système de Thèmes CSS**
**Objectif** : Collections de styles prédéfinis pour personnalisation rapide

**🏗️ Architecture Thèmes :**
- [ ] Créer `ThemeManager` avec thèmes prédéfinis (Classic, Modern, Corporate, Minimal)
- [ ] Injection CSS dynamique dans les templates
- [ ] Variables CSS personnalisables (couleurs, polices, espacements)
- [ ] Aperçus miniatures pour chaque thème
- [ ] Thèmes freemium (3 gratuits, + payants)

**🎨 Interface Utilisateur :**
- [ ] Sélecteur de thèmes dans barre d'outils éditeur
- [ ] Aperçu temps réel lors changement thème
- [ ] Sauvegarde thème par template
- [ ] Export thème pour partage

### 🔧 **Étape 2.2 : Variables Conditionnelles Avancées**
**Objectif** : Variables intelligentes qui s'adaptent aux réglages WooCommerce

**📊 Variables Dynamiques :**
- [ ] Variables TTC/HT selon configuration WooCommerce
- [ ] Formatage dates selon locale WordPress (`date_i18n`)
- [ ] Symboles et codes devises dynamiques
- [ ] Variables conditionnelles selon statut commande

**🧮 Calculs Automatiques :**
- [ ] Adaptation automatique aux taxes et calculs
- [ ] Variables contextuelles (éditeur vs metabox)
- [ ] Validation données selon type variable
- [ ] Fallbacks pour données manquantes

### 🔤 **Étape 2.3 : Gestion Polices Avancée**
**Objectif** : Système de polices web-safe + premium avec embedding

**📝 Polices de Base :**
- [ ] Polices web-safe gratuites (Arial, Times, Helvetica, etc.)
- [ ] Aperçu polices dans sélecteur
- [ ] Performance optimisée chargement
- [ ] Fallbacks automatiques

**🔓 Polices Premium :**
- [ ] Google Fonts avec embedding automatique
- [ ] Cache de polices côté serveur
- [ ] Sélecteur de polices dans interface template
- [ ] Aperçu en temps réel changements police

### 📐 **Étape 2.4 : Mise en Page Automatique**
**Objectif** : Positionnement intelligent des éléments par zones

**🏗️ Zones Prédéfinies :**
- [ ] Zones logiques : Header, Content, Footer, Sidebar
- [ ] Calcul automatique positions selon format page (A4, A5, Letter)
- [ ] Marges et espacements intelligents
- [ ] Redimensionnement automatique éléments

**🎯 Templates Starters :**
- [ ] Templates pré-positionnés pour débutants
- [ ] Drag & drop zones configurables
- [ ] Aperçu temps réel repositionnement
- [ ] Sauvegarde layouts personnalisés

### 🔔 **Étape 2.5 : Notifications Intelligentes**
**Objectif** : Système de notifications contextuelles et non-intrusives

**📢 Types Notifications :**
- [ ] Toasts pour génération lente (>2s : "Génération en cours...")
- [ ] Alertes succès avec actions (télécharger, partager)
- [ ] Messages d'erreur contextuels et informatifs
- [ ] Notifications non-intrusives et dismissibles

**🎛️ Gestion UX :**
- [ ] File d'attente notifications intelligente
- [ ] Priorisation par importance/criticité
- [ ] Animations fluides et accessibles
- [ ] Paramètres utilisateur (fréquence, types)

### ⚡ **Étape 2.6 : Lazy Loading Intelligent**
**Objectif** : Optimisation performance pour gros templates

**🔄 Chargement Différé :**
- [ ] Priorisation éléments visibles d'abord
- [ ] Chargement progressif éléments hors écran
- [ ] Cache intelligent par visibilité
- [ ] Indicateurs chargement par section

**📊 Métriques Performance :**
- [ ] Monitoring temps chargement par élément
- [ ] Optimisation basée sur données réelles
- [ ] Cache prédictif éléments fréquents
- [ ] Performance optimisée mobiles

### 🛡️ **Étape 2.7 : Rate Limiting Intelligent**
**Objectif** : Protection anti-abus avec logique adaptative

**🔒 Limites Dynamiques :**
- [ ] Limites par utilisateur/IP/heure adaptatives
- [ ] Adaptation selon type requête (template vs commande)
- [ ] Récupération automatique quota
- [ ] Messages informatifs lors limitation

**📈 Analytics Sécurité :**
- [ ] Monitoring tentatives abusives
- [ ] Alertes administrateur anomalies
- [ ] Logs détaillés pour audit
- [ ] Adaptation automatique menaces

### 📊 **Étape 2.8 : Métriques Détaillées**
**Objectif** : Analytics complets pour optimisation UX

**📈 Métriques Performance :**
- [ ] Temps génération par template/contexte
- [ ] Taux succès/erreur détaillés
- [ ] Performance par navigateur/appareil
- [ ] Tendances utilisation temporelles

**👥 Métriques Utilisation :**
- [ ] Templates les plus populaires
- [ ] Fonctionnalités les plus utilisées
- [ ] Parcours utilisateur courants
- [ ] Points d'abandon et friction

### 📱 **Étape 2.9 : UI Responsive Avancée**
**Objectif** : Interface parfaitement adaptée mobile/desktop

**📱 Optimisation Mobile :**
- [ ] Interface adaptative tous écrans
- [ ] Touch gestures optimisés (pinch, swipe)
- [ ] Modal responsive et ergonomique
- [ ] Performance optimisée petits écrans

**🖥️ Optimisation Desktop :**
- [ ] Utilisation optimale grand écran
- [ ] Raccourcis clavier avancés
- [ ] Multi-fenêtrage intelligent
- [ ] Drag & drop fluide haute précision

### 📤 **Étape 2.10 : Export Avancé**
**Objectif** : Fonctionnalités premium d'export et distribution

**🔒 Protection & Sécurité :**
- [ ] Protection PDF par mot de passe
- [ ] Watermark personnalisable (texte, image)
- [ ] Métadonnées sécurisées
- [ ] Traçabilité exports

**📬 Partage & Distribution :**
- [ ] Export email direct intégré
- [ ] Intégration stockage cloud (Dropbox, Google Drive)
- [ ] Liens de partage temporaires
- [ ] Historique exports utilisateur

### 🎯 **Critères de Succès Phase 2**
- [ ] **Différenciation premium** : Fonctionnalités uniques vs concurrence
- [ ] **Performance maintenue** : < 2 secondes malgré features avancées
- [ ] **UX premium** : Interface sophistiquée et intuitive
- [ ] **Conversion freemium** : +15% taux conversion grâce aux features

#### **Étape 2.11 : Système de Stockage Avancé**
**Objectif** : Implémenter le système de stockage robuste comme le plugin concurrent

**🏗️ Architecture Stockage Double Format :**
- [ ] **JSON primaire** : Stockage moderne et lisible (`_pdf_template_data_json`)
- [ ] **Serialized fallback** : Compatibilité legacy (`_pdf_template_data`)
- [ ] **Migration automatique** : Conversion anciens templates vers JSON
- [ ] **Validation robuste** : Vérification intégrité données

**📁 Gestion Fichiers Gros Templates :**
- [ ] **Détection taille** : Templates > 100KB → fichiers séparés
- [ ] **Répertoire sécurisé** : `wp-content/uploads/pdf-templates/`
- [ ] **Nommage intelligent** : `template_{id}_{hash}.json`
- [ ] **Nettoyage automatique** : Suppression fichiers orphelins

**🔄 Logique Chargement/Sauvegarde :**
- [ ] **Chargement prioritaire** : JSON d'abord, fallback serialized
- [ ] **Sauvegarde double** : Toujours les deux formats
- [ ] **Gestion erreurs** : Recovery automatique corruption
- [ ] **Performance optimisée** : Cache métadonnées

**🛡️ Sécurité et Intégrité :**
- [ ] **Validation JSON** : Protection contre corruption
- [ ] **Sanitisation** : Nettoyage données avant stockage
- [ ] **Permissions** : Contrôle accès fichiers
- [ ] **Audit trail** : Logs modifications templates

**🎯 Avantages de cette approche :**
- [ ] **Modernité** : JSON standard pour compatibilité
- [ ] **Robustesse** : Double format anti-corruption
- [ ] **Performance** : Fichiers pour gros templates
- [ ] **Évolutivité** : Migration facile futures versions

---

## 🚀 **Phase 3 : Reconstruction Interface - Rendu PHP Prioritaire**

**📅 Date** : Octobre 2025
**🎯 Objectif** : Reconstruire l'interface avec rendu PHP/TCPDF pour aperçus haute précision
**⏱️ Durée estimée** : 1-2 semaines
**📊 Statut** : ✅ TERMINÉE

### 🎯 **Découvertes Clés Phase 2**

#### **Problèmes Identifiés :**
- **Canvas limité** : Rendu côté client imprécis pour PDF complexes
- **Performance variable** : Dépend du navigateur et puissance machine
- **Cohérence** : Différences entre navigateurs et appareils

#### **Solution Adoptée :**
- **Rendu serveur prioritaire** : TCPDF/DomPDF pour précision parfaite
- **Canvas fallback** : Seulement si serveur échoue
- **API unifiée** : PreviewImageAPI pour conversion PDF→PNG

### 🎯 **Implémentation Phase 2**

#### **Étape 2.1 : Architecture Rendu Serveur**
**Objectif** : Implémenter le système de rendu serveur haute précision

**🔧 Configuration TCPDF/DomPDF :**
- [ ] Installation et configuration DomPDF optimisée
- [ ] Paramètres performance (DPI, compression, mémoire)
- [ ] Gestion erreurs et timeouts appropriés
- [ ] Logs détaillés pour debugging

**🎨 Conversion PDF→Image :**
- [ ] PreviewImageAPI pour génération PNG/JPG côté serveur
- [ ] Qualité configurable (72-300 DPI)
- [ ] Formats multiples (PNG, JPG, WebP)
- [ ] Optimisation taille fichiers

**⚡ Cache Intelligent :**
- [ ] Cache transients WordPress pour métadonnées
- [ ] Cache fichiers pour images générées
- [ ] Invalidation automatique par modification
- [ ] Nettoyage périodique des anciens caches

#### **Étape 2.2 : Interface Utilisateur Modernisée**
**Objectif** : Reconstruire l'interface avec UX améliorée

**🎛️ Contrôles Aperçu Avancés :**
- [ ] Zoom fluide avec boutons et molette souris
- [ ] Rotation PDF (90°, 180°, 270°)
- [ ] Navigation multi-pages si nécessaire
- [ ] Boutons fullscreen et téléchargement

**📱 Responsive Design :**
- [ ] Interface adaptative mobile/desktop
- [ ] Modal optimisée pour tous écrans
- [ ] Touch gestures sur mobile
- [ ] Performance optimisée petits écrans

**🎨 États de Chargement :**
- [ ] Indicateurs visuels pendant génération
- [ ] Progress bars pour longs processus
- [ ] Messages d'état informatifs
- [ ] Gestion annulation en cours

#### **Étape 2.3 : Intégration Contextes Multiples**
**Objectif** : Unifier les aperçus éditeur et metabox

**🖼️ Aperçu Éditeur (Modal Overlay) :**
- [ ] Bouton aperçu dans barre d'outils éditeur
- [ ] Modal fullscreen avec contrôles complets
- [ ] Données fictives cohérentes
- [ ] Sauvegarde automatique avant aperçu

**📦 Aperçu Metabox WooCommerce :**
- [ ] Intégration directe dans page commande
- [ ] Données réelles de la commande sélectionnée
- [ ] Sélecteur template intégré
- [ ] Actions groupées (aperçu/génération/téléchargement)

**🔄 Transitions Fluides :**
- [ ] Changement seamless entre contextes
- [ ] Préservation état entre aperçus
- [ ] Cache partagé pour performance
- [ ] Historique navigation

#### **Étape 2.4 : Système de Cache Avancé**
**Objectif** : Optimiser performance avec cache intelligent

**💾 Cache Multi-Niveaux :**
- [ ] Cache mémoire pour sessions actives
- [ ] Cache fichiers pour images générées
- [ ] Cache base de données pour métadonnées
- [ ] Invalidation intelligente par changements

**🎯 Cache Contextuel :**
- [ ] Cache différencié éditeur vs metabox
- [ ] Clés composites (template + données + contexte)
- [ ] TTL adaptatif selon fréquence utilisation
- [ ] Préchargement cache intelligent

#### **Étape 2.5 : Gestion Erreurs Robuste**
**Objectif** : Système de fallback et récupération d'erreurs

**🔄 Fallback Automatique :**
- [ ] Bascule serveur → Canvas si échec
- [ ] Messages d'erreur informatifs utilisateur
- [ ] Logging détaillé pour développeurs
- [ ] Récupération automatique si possible

**📊 Monitoring et Alertes :**
- [ ] Métriques performance temps réel
- [ ] Alertes sur taux d'échec élevé
- [ ] Dashboard admin pour monitoring
- [ ] Logs structurés pour analyse

---

## 🚀 **Phase 2.5 : Améliorations Concurrentielles**

**📅 Date** : Décembre 2025
**🎯 Objectif** : Combler les écarts critiques avec le plugin concurrent pour atteindre la parité fonctionnelle
**⏱️ Durée estimée** : 3-4 semaines
**📊 Statut** : ⏳ Planification
**💡 Justification** : Analyse comparative révèle 8 gaps critiques à combler pour la compétitivité

### 📊 **Analyse des Écarts Concurrentiels**

Après analyse approfondie du plugin WooCommerce PDF Invoice Builder, voici les **8 gaps critiques** identifiés :

#### **Gap 1 : Système Double Format** ⭐⭐⭐⭐⭐
**Écart** : Leur système JSON + serialized éprouvé vs notre JSON uniquement
**Impact** : Risque de perte données, migration difficile
**Priorité** : Critique

#### **Gap 2 : Thèmes CSS Prédéfinis** ⭐⭐⭐⭐☆
**Écart** : 4 thèmes prédéfinis vs aucun thème
**Impact** : Adoption lente par utilisateurs non-designers
**Priorité** : Haute

#### **Gap 3 : Variables Conditionnelles** ⭐⭐⭐⭐☆
**Écart** : TTC/HT automatique, devises dynamiques vs variables basiques
**Impact** : Limitation internationale
**Priorité** : Moyenne-Haute

#### **Gap 4 : Gestion Polices Avancée** ⭐⭐⭐☆☆
**Écart** : Google Fonts + web-safe vs polices limitées
**Impact** : Typographie professionnelle absente
**Priorité** : Moyenne

#### **Gap 5 : Mise en Page Automatique** ⭐⭐⭐☆☆
**Écart** : Zones prédéfinies intelligentes vs layout manuel
**Impact** : Complexité pour débutants
**Priorité** : Moyenne

#### **Gap 6 : Export Professionnel** ⭐⭐⭐☆☆
**Écart** : Protection PDF, watermark, cloud storage vs export basique
**Impact** : Usage entreprise limité
**Priorité** : Moyenne

#### **Gap 7 : Analytics Détaillées** ⭐⭐☆☆☆
**Écart** : Métriques complètes vs analytics basiques
**Impact** : Optimisation produit difficile
**Priorité** : Basse

#### **Gap 8 : Mode Développeur** ⭐⭐☆☆☆
**Écart** : Debug tools avancés vs mode développeur basique
**Impact** : Support développeur dégradé
**Priorité** : Basse

### 🎯 **Implémentation Phase 2.5**

#### **Étape 2.5.1 : Système Double Format (Priorité Critique)**
**Objectif** : Implémenter le système de stockage robuste du concurrent

**🏗️ Architecture Stockage Double Format :**
- [ ] **JSON primaire** : Stockage moderne (`_pdf_template_data_json`)
- [ ] **Serialized fallback** : Compatibilité legacy (`_pdf_template_data`)
- [ ] **Migration automatique** : Conversion anciens templates
- [ ] **Validation robuste** : Protection contre corruption

**📁 Gestion Fichiers Gros Templates :**
- [ ] **Détection taille** : Templates > 100KB → fichiers séparés
- [ ] **Répertoire sécurisé** : `wp-content/uploads/pdf-templates/`
- [ ] **Nommage intelligent** : `template_{id}_{hash}.json`
- [ ] **Nettoyage automatique** : Suppression fichiers orphelins

**🔄 Logique Chargement/Sauvegarde :**
- [ ] **Chargement prioritaire** : JSON d'abord, fallback serialized
- [ ] **Sauvegarde double** : Toujours les deux formats
- [ ] **Gestion erreurs** : Recovery automatique corruption
- [ ] **Performance optimisée** : Cache métadonnées

#### **Étape 2.5.2 : Thèmes CSS Prédéfinis (Priorité Haute)**
**Objectif** : Offrir 4 thèmes professionnels prédéfinis

**🎨 Création Thèmes :**
- [ ] **Classic** : Style traditionnel, sobre et professionnel
- [ ] **Modern** : Design épuré, minimaliste et contemporain
- [ ] **Corporate** : Style entreprise avec branding fort
- [ ] **Minimal** : Ultra-simple, focus sur le contenu

**⚙️ Injection CSS Dynamique :**
- [ ] **Variables CSS** : Couleurs, polices, espacements personnalisables
- [ ] **Aperçus miniatures** : Prévisualisation rapide des thèmes
- [ ] **Application instantanée** : Changement thème sans rechargement
- [ ] **Sauvegarde préférences** : Mémorisation choix utilisateur

**🖼️ Interface Sélecteur Thèmes :**
- [ ] **Galerie visuelle** : Aperçus côte à côte
- [ ] **Filtres recherche** : Par style, couleur, usage
- [ ] **Personnalisation** : Ajustements fins par thème
- [ ] **Import/Export** : Partage thèmes entre installations

#### **Étape 2.5.3 : Variables Conditionnelles Avancées (Priorité Haute)**
**Objectif** : Variables intelligentes comme le concurrent

**💰 Variables Financières :**
- [ ] **TTC/HT automatique** : Selon configuration WooCommerce
- [ ] **Devises dynamiques** : Symboles locaux (€, $, £, etc.)
- [ ] **Formatage nombres** : Séparateurs milliers, décimales
- [ ] **Calculs automatiques** : Taxes, remises, totaux

**📅 Variables Temporelles :**
- [ ] **Dates formatées** : Selon locale WordPress
- [ ] **Formats multiples** : Court, long, relatif ("il y a 2 jours")
- [ ] **Fuseaux horaires** : Gestion automatique
- [ ] **Périodes** : "Ce mois", "Cette année"

**🌐 Variables Internationales :**
- [ ] **Adresses formatées** : Selon pays (US vs Europe)
- [ ] **Noms complets** : Ordre prénom/nom selon culture
- [ ] **Numéros téléphone** : Formatage local
- [ ] **Codes postaux** : Validation par pays

#### **Étape 2.5.4 : Gestion Polices Professionnelle (Priorité Moyenne)**
**Objectif** : Système de polices avancé comme le concurrent

**🔤 Polices Web-Safe :**
- [ ] **Familles complètes** : Arial, Times, Courier, etc.
- [ ] **Tous styles** : Normal, gras, italique, etc.
- [ ] **Aperçu temps réel** : Changement instantané
- [ ] **Fallbacks** : Gestion polices manquantes

**🎨 Google Fonts Premium :**
- [ ] **Catalogue complet** : 1000+ polices Google
- [ ] **Catégorisation** : Serif, sans-serif, monospace, etc.
- [ ] **Préchargements** : Optimisation performance
- [ ] **Sous-ensembles** : Caractères latins uniquement

**⚡ Cache et Performance :**
- [ ] **Cache serveur** : Polices fréquemment utilisées
- [ ] **Préchargement** : Polices template au chargement
- [ ] **Compression** : Optimisation taille fichiers
- [ ] **CDN** : Livraison rapide mondiale

#### **Étape 2.5.5 : Mise en Page Automatique (Priorité Moyenne)**
**Objectif** : Zones prédéfinies intelligentes

**📐 Zones Prédéfinies :**
- [ ] **Header/Footer** : Automatique avec logo, adresse
- [ ] **Corps document** : Produits, totaux, notes
- [ ] **Sidebar** : Informations complémentaires
- [ ] **Signatures** : Zones dédiées signatures

**🎯 Détection Intelligente :**
- [ ] **Analyse contenu** : Détection automatique zones
- [ ] **Suggestions layout** : Recommandations par type document
- [ ] **Redimensionnement** : Adaptation contenu dynamique
- [ ] **Validation** : Contrôle chevauchements

**🔧 Éditeur Visuel :**
- [ ] **Drag & drop zones** : Réarrangement intuitif
- [ ] **Snap guides** : Alignement automatique
- [ ] **Responsive preview** : Adaptation formats papier
- [ ] **Templates secteurs** : Facture, devis, bon livraison

#### **Étape 2.5.6 : Export Professionnel (Priorité Moyenne)**
**Objectif** : Fonctionnalités export avancées

**🔒 Protection PDF :**
- [ ] **Mot de passe** : Protection ouverture/édition
- [ ] **Permissions** : Impression, copie, modification
- [ ] **Watermark** : Texte/image personnalisable
- [ ] **Métadonnées** : Informations document sécurisées

**☁️ Cloud Storage :**
- [ ] **Dropbox intégration** : Sauvegarde automatique
- [ ] **Google Drive** : Partage et stockage
- [ ] **OneDrive** : Support Microsoft
- [ ] **FTP/SFTP** : Serveurs personnalisés

**📧 Distribution Email :**
- [ ] **Envoi direct** : Depuis interface WooCommerce
- [ ] **Templates email** : Personnalisation messages
- [ ] **Pièces jointes** : PDF + documents complémentaires
- [ ] **Tracking** : Confirmation livraison

#### **Étape 2.5.7 : Analytics Détaillées (Priorité Basse)**
**Objectif** : Métriques complètes pour optimisation

**📊 Métriques Performance :**
- [ ] **Temps génération** : Par template et contexte
- [ ] **Taux succès/erreur** : Suivi fiabilité
- [ ] **Templates populaires** : Usage par template
- [ ] **Performance appareil** : Desktop vs mobile

**👥 Métriques Utilisation :**
- [ ] **Fréquence usage** : Par utilisateur/type
- [ ] **Fonctionnalités utilisées** : Tracking adoption
- [ ] **Erreurs utilisateur** : Points de friction
- [ ] **Temps session** : Engagement utilisateur

**💰 Métriques Business :**
- [ ] **Conversion freemium** : Taux upgrade
- [ ] **Templates créés** : Productivité utilisateurs
- [ ] **Support tickets** : Volume et résolution
- [ ] **Satisfaction** : Feedback et ratings

#### **Étape 2.5.8 : Mode Développeur Avancé (Priorité Basse)**
**Objectif** : Outils debug professionnels

**🐛 Panneau Debug Flottant :**
- [ ] **Métriques temps réel** : Performance, mémoire, CPU
- [ ] **Variables dump** : État actuel template/données
- [ ] **Logs console** : Messages structurés
- [ ] **Timestamps** : Chronologie événements

**🔍 Outils Diagnostic :**
- [ ] **Inspecteur éléments** : Analyse structure PDF
- [ ] **Validateur données** : Contrôle intégrité
- [ ] **Testeur variables** : Simulation différents contextes
- [ ] **Profileur performance** : Identification goulots

**📋 Logs et Historique :**
- [ ] **Historique actions** : Dernières 100 opérations
- [ ] **Export logs** : Pour partage support
- [ ] **Filtrage avancé** : Par type, niveau, période
- [ ] **Recherche** : Mots-clés dans logs

### 🎯 **Critères de Succès Phase 2.5**
- [ ] **Parité concurrentielle** : 8/8 gaps comblés
- [ ] **Performance maintenue** : < 2 secondes malgré features
- [ ] **UX améliorée** : Adoption +30% grâce aux thèmes
- [ ] **Stabilité** : 0 régression fonctionnalités existantes
- [ ] **Tests complets** : Couverture >90% nouvelles features

---

## 🚀 **Phase 4 : Tests et Validation Complète**

**📅 Date** : Novembre 2025
**🎯 Objectif** : Validation complète du système unifié avant déploiement
**⏱️ Durée estimée** : 1 semaine
**📊 Statut** : Phase 4.1 terminée, 4.2-4.6 en attente

#### **Étape 4.2 : Tests Intégration Canvas/Metabox**
**Objectif** : Valider l'intégration parfaite entre les deux contextes

**🔗 Tests Transitions :**
- [ ] Test navigation fluide éditeur ↔ metabox
- [ ] Validation cohérence données entre contextes
- [ ] Test cache partagé et synchronisation
- [ ] Performance transitions < 500ms

**📊 Tests Données :**
- [ ] Validation mapping variables dynamiques
- [ ] Test données fictives vs réelles cohérentes
- [ ] Gestion erreurs données manquantes
- [ ] Sanitisation et sécurité données

#### **Étape 4.3 : Tests Performance et Charge**
**Objectif** : Validation performance en conditions réelles

**⚡ Tests Performance :**
- [ ] Génération < 2 secondes en conditions normales
- [ ] Cache hit ratio > 80%
- [ ] Mémoire < 100MB par génération
- [ ] CPU optimisé pour tous serveurs

**📈 Tests Charge :**
- [ ] Support 10+ utilisateurs simultanés
- [ ] Gestion queue et timeouts appropriés
- [ ] Monitoring ressources serveur
- [ ] Auto-scaling si nécessaire

#### **Étape 4.4 : Tests Sécurité et Robustesse**
**Objectif** : Validation sécurité et gestion d'erreurs

**🔒 Tests Sécurité :**
- [ ] Validation toutes permissions utilisateur
- [ ] Protection contre injection et XSS
- [ ] Rate limiting et anti-abus
- [ ] Audit sécurité complet

**🛡️ Tests Robustesse :**
- [ ] Simulation pannes serveur/base de données
- [ ] Test récupération automatique erreurs
- [ ] Validation fallback Canvas opérationnel
- [ ] Gestion timeouts et annulations

#### **Étape 4.5 : Tests Utilisateur et UX**
**Objectif** : Validation expérience utilisateur finale

**👥 Tests UX Éditeur :**
- [ ] Workflow création template intuitif
- [ ] Aperçu responsive et fluide
- [ ] Gestion erreurs user-friendly
- [ ] Performance perçue optimale

**🛒 Tests UX Metabox :**
- [ ] Intégration WooCommerce seamless
- [ ] Workflow génération PDF rapide
- [ ] Gestion états de chargement
- [ ] Erreurs contextuelles compréhensibles

#### **Étape 4.6 : Tests Compatibilité et Navigateurs**
**Objectif** : Validation cross-browser et cross-device

**🌐 Tests Navigateurs :**
- [ ] Chrome, Firefox, Safari, Edge derniers
- [ ] Versions mobiles (iOS Safari, Chrome Android)
- [ ] Fallbacks pour anciens navigateurs
- [ ] Performance équivalente tous navigateurs

**📱 Tests Appareils :**
- [ ] Desktop, tablette, mobile
- [ ] Résolutions diverses (HD, 4K, mobile)
- [ ] Orientation portrait/paysage
- [ ] Performance sur appareils modestes

---

## 🚀 **Phase 5 : Déploiement et Production**

**📅 Date** : Décembre 2025
**🎯 Objectif** : Déploiement en production avec monitoring complet
**⏱️ Durée estimée** : 2 semaines
**📊 Statut** : ⏳ Planification

#### **Étape 4.1 : Déploiement Automatisé**
**Objectif** : Pipeline CI/CD complet pour déploiements fiables

**🔧 Configuration CI/CD :**
- [ ] Pipeline GitHub Actions ou équivalent
- [ ] Tests automatisés avant déploiement
- [ ] Déploiement staging → production
- [ ] Rollback automatique en cas d'erreur

**📊 Monitoring Production :**
- [ ] Dashboard performance temps réel
- [ ] Alertes sur métriques critiques
- [ ] Logs centralisés et recherche
- [ ] Métriques utilisateur (usage, erreurs)

#### **Étape 4.2 : Documentation et Support**
**Objectif** : Documentation complète pour utilisateurs et développeurs

**📚 Documentation Utilisateur :**
- [ ] Guides d'utilisation détaillés
- [ ] FAQ et troubleshooting
- [ ] Vidéos tutoriels
- [ ] Support communauté/forum

**🛠️ Documentation Technique :**
- [ ] API documentation complète
- [ ] Guide développeur pour extensions
- [ ] Architecture et décisions techniques
- [ ] Procédures maintenance et debug

#### **Étape 4.3 : Support Production**
**Objectif** : Équipe support opérationnelle

**🎧 Configuration Support :**
- [ ] Système tickets (Zendesk, Intercom)
- [ ] Base connaissances et auto-help
- [ ] SLA garantis (< 24h réponse)
- [ ] Formation équipe support

**📈 Métriques Support :**
- [ ] Taux résolution premier contact
- [ ] Temps réponse moyen
- [ ] Satisfaction utilisateur
- [ ] Issues récurrentes tracking

---

## 🚀 **Phase 6 : Améliorations Techniques**

**📅 Date** : Q1 2026
**🎯 Objectif** : Optimisations techniques et qualité code
**⏱️ Durée estimée** : 1-2 mois
**📊 Statut** : ⏳ Planification

#### **Étape 5.1 : Migration TypeScript**
**Objectif** : Améliorer la maintenabilité du code JavaScript

**🔧 Migration Progressive :**
- [ ] Configuration TypeScript dans le projet
- [ ] Interfaces pour éléments template et API
- [ ] Migration fichiers critiques en premier
- [ ] Tests TypeScript opérationnels

#### **Étape 5.2 : Qualité Code**
**Objectif** : Outils de qualité et standards élevés

**🛠️ Outils Développement :**
- [ ] ESLint + Prettier configurés
- [ ] Règles coding standards strictes
- [ ] Hooks pre-commit pour qualité
- [ ] Tests automatisés qualité code

#### **Étape 5.3 : Tests Unitaires Complets**
**Objectif** : Couverture tests >80% pour fiabilité

**🧪 Tests JavaScript :**
- [ ] Tests PreviewAPI et gestion états
- [ ] Tests utilitaires et helpers
- [ ] Tests intégration composants
- [ ] Tests composants React

**🧪 Tests PHP :**
- [ ] Tests DataProviders et Generators
- [ ] Tests API endpoints
- [ ] Tests sécurité et validation
- [ ] Tests classes utilitaires

#### **Étape 5.4 : Optimisations Performance**
**Objectif** : Performance optimale pour tous utilisateurs

**⚡ Optimisations Frontend :**
- [ ] Code splitting et lazy loading
- [ ] Bundle size optimisé (< 500KB gzipped)
- [ ] Images et assets optimisés
- [ ] Caching intelligent navigateur

**⚡ Optimisations Backend :**
- [ ] Requêtes DB optimisées (index, cache)
- [ ] Cache objets WordPress utilisé
- [ ] Compression réponses API
- [ ] CDN pour assets statiques

#### **Étape 5.5 : Audit Sécurité**
**Objectif** : Sécurité renforcée et conformité

**🔒 Audit Complet :**
- [ ] Nonces et permissions vérifiées
- [ ] Sanitisation toutes entrées/sorties
- [ ] Protection CSRF et XSS
- [ ] Audit tiers parties (DomPDF, etc.)

#### **Étape 5.6 : UX Améliorations**
**Objectif** : Interface utilisateur exceptionnelle

**🎨 Améliorations UI :**
- [ ] Cercle chargement multicolore
- [ ] Animations fluides et micro-interactions
- [ ] Gestion erreurs améliorée
- [ ] Feedback visuel intelligent

#### **Étape 5.7 : Base de Données**
**Objectif** : Optimisation stockage et récupération

**🗄️ Optimisations DB :**
- [ ] Index sur colonnes fréquemment requêtées
- [ ] Cache objets WordPress étendu
- [ ] Requêtes optimisées et préparées
- [ ] Cleanup données obsolètes

#### **Étape 5.8 : Sécurité Avancée**
**Objectif** : Permissions et accès contrôlés

**🔐 Gestion Permissions :**
- [ ] Rôles WordPress existants respectés
- [ ] Permissions granulaire par fonctionnalité
- [ ] Audit trails pour actions sensibles
- [ ] Multi-tenant si multisite

#### **Étape 5.9 : Internationalisation**
**Objectif** : Support multi-langues complet

**🌍 i18n Complet :**
- [ ] Extraction chaînes traduisibles
- [ ] Fichiers .po/.mo pour langues cibles
- [ ] Interface admin traduite
- [ ] Dates et monnaies localisées

#### **Étape 5.10 : Refactorisation Modulaire**
**Objectif** : Architecture propre et maintenable

**🏗️ Refactorisation :**
- [ ] Modules indépendants et réutilisables
- [ ] Suppression code legacy (local + serveur)
- [ ] Interfaces claires entre modules
- [ ] Tests refactoring validés

---

## 🚀 **Phase 7 : Monétisation Freemium**

**📅 Date** : Q2 2026
**🎯 Objectif** : Implémentation modèle freemium rentable
**⏱️ Durée estimée** : 1 mois
**📊 Statut** : ⏳ Planification

### 📝 **Historique et décisions Phase 7**

#### **Éléments importants de la discussion** :
- **Stratégie freemium** : Version gratuite attractive avec limitations claires, premium débloquant tout
- **Prix** : 69€ justifié par complexité IA + concurrence WooCommerce (30% commission compensée)
- **Promo possible** : 59€ sur site externe vs 69€ sur WooCommerce
- **Limitations gratuites** : 1 template, PDF only, 15 variables basiques, 3 dynamic-text models, watermark
- **Transition bloquante** : Pas de douceur excessive - freemium ne doit pas accéder au premium
- **Focus initial** : Tester sur dynamic-text models (3 prédéfinis gratuits)
- **Support** : Email seulement (pas de forum)
- **Mises à jour** : Mineures (bug fixes) en gratuit, features en premium
- **Licences** : Stockage à définir (plugin existant ou tiers), rappels email avant expiration
- **Upsells** : Boutons bloquants avec messages "Upgrade required"
- **Watermark** : Texte discret "WP PDF Builder Free" sur PDFs gratuits
- **Variables** : 7 éléments primordiaux inclus (nom, adresse, email, téléphone, prix, date, entreprise)
- **Éléments à affiner** : Paramètres canvas, types d'éléments exclus (cercles, formes complexes)

#### **Décisions finales** :
- **Modèle** : Freemium strict avec blocage des features premium
- **Monétisation** : 69€/site à vie, extensible à multisite plus tard
- **Implémentation** : Un par un, tester limitations au fur et à mesure
- **Compatibilité** : WooCommerce + site externe pour licences et promos
- **Objectif** : 20% taux conversion gratuit→premium

#### **Étape 6.1 : Architecture Freemium**
**Objectif** : Infrastructure de base pour modèle freemium

**🏗️ Système Licences :**
- [ ] Intégration système licences (plugin existant ou tiers)
- [ ] Validation licences côté serveur
- [ ] Cache licences pour performance
- [ ] Gestion renouvellements et rappels

**🎛️ Interface Admin Licences :**
- [ ] Page admin pour gestion licences
- [ ] Activation/désactivation fonctionnalités
- [ ] Métriques utilisation et conversions
- [ ] Dashboard freemium insights

#### **Étape 6.2 : Limitations Freemium**
**Objectif** : Implémenter restrictions version gratuite

**📄 Restrictions Templates :**
- [ ] Limitation à 1 template en gratuit
- [ ] Templates premium verrouillés avec upsells
- [ ] Aperçus miniatures pour templates payants
- [ ] Messages "Upgrade required" bloquants

**🖼️ Restrictions Exports :**
- [ ] PDF seulement en gratuit (PNG/JPG premium)
- [ ] Watermark "WP PDF Builder Free" sur PDFs
- [ ] Qualité limitée (72 DPI vs 300 DPI premium)
- [ ] Métadonnées limitées

#### **Étape 6.3 : Variables Dynamiques Freemium**
**Objectif** : Système variables limité/freemium

**📊 Variables Gratuites :**
- [ ] 15 variables basiques incluses (7 éléments primordiaux)
- [ ] Variables dynamiques simples (nom, email, téléphone)
- [ ] Formatage basique dates/prix
- [ ] Validation basique données

**🔧 Variables Premium :**
- [ ] Variables avancées (TTC/HT, devises multiples)
- [ ] Variables conditionnelles et calculs
- [ ] Formatage avancé (locales, symboles)
- [ ] Variables WooCommerce étendues

#### **Étape 6.4 : Upsells et Conversion**
**Objectif** : Maximiser conversion gratuit→premium

**🎯 Upsells Contextuels :**
- [ ] Boutons "Upgrade to Premium" dans éditeur
- [ ] Messages bloquants pour features premium
- [ ] Tooltips explicatifs limitations
- [ ] Landing pages features premium

**💰 Intégration Paiement :**
- [ ] WooCommerce comme passerelle paiement
- [ ] Site externe pour licences (éviter 30% commission)
- [ ] Processus achat seamless
- [ ] Activation automatique post-achat

#### **Étape 6.5 : Analytics Freemium**
**Objectif** : Métriques pour optimiser conversion

**📊 Tracking Utilisation :**
- [ ] Événements freemium (limitations atteintes, upsells vus)
- [ ] Funnel conversion gratuit→premium
- [ ] Métriques engagement utilisateurs
- [ ] A/B tests pour optimiser upsells

**📈 Dashboard Insights :**
- [ ] Taux conversion par feature
- [ ] Revenus et LTV utilisateurs
- [ ] Churn et rétention freemium
- [ ] Optimisations basées données

#### **Étape 6.6 : Support Freemium**
**Objectif** : Support adapté au modèle freemium

**🎧 Support Gratuit :**
- [ ] Documentation complète auto-help
- [ ] FAQ et guides troubleshooting
- [ ] Forum communauté (optionnel)
- [ ] Support email basique

**💎 Support Premium :**
- [ ] Support prioritaire email/téléphone
- [ ] SLA garantis (< 24h réponse)
- [ ] Accès beta features
- [ ] Conseils personnalisés optimisation

#### **Étape 6.7 : Tests et Validation Freemium**
**Objectif** : Validation complète modèle freemium

**🧪 Tests Limitations :**
- [ ] Validation blocages fonctionnels
- [ ] Test upsells et messages utilisateur
- [ ] Performance avec limitations
- [ ] UX freemium vs premium

**📊 Tests Conversion :**
- [ ] A/B tests messages upsell
- [ ] Tracking funnel conversion
- [ ] Métriques engagement
- [ ] Optimisation taux conversion

---

## ✅ **Critères de Succès Globaux**

- ⚡ **Performance** : < 2s génération, < 100MB RAM
- 🔒 **Sécurité** : 0 vulnérabilités, audit passé
- 🧪 **Qualité** : Tests 100% succès, couverture >80%
- 📚 **Maintenabilité** : Code modulaire, bien documenté
- 💰 **Business** : 20% conversion freemium, satisfaction utilisateur
- 🎯 **Technique** : Architecture scalable, monitoring complet

---

**📅 Prochaine échéance** : Tests Phase 4.2 (décembre 2025)
**🎯 Focus actuel** : Validation autosave et cohérence JSON avec aperçu PHP
