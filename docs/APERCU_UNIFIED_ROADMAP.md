# 🚀 Reconstruction Système d'Aperçu

**📅 Date** : 21 octobre 2025  
**🔄 Statut** : Phase 2.1.4 terminée (priorités d'implémentation définies)

---

## 🎯 Vue d'ensemble

Reconstruction complète du système d'aperçu PDF avec architecture moderne :
- **Canvas** : Éditeur avec données d'exemple
- **Metabox** : WooCommerce avec données réelles

---

## 📋 Spécifications fonctionnelles

### 🎯 **Vision générale**
- **Méthode d'affichage commune** : Même rendu visuel pour Canvas et Metabox, seules les données changent dynamiquement.
- **Respect du canvas** : Tout doit correspondre exactement au canvas (éléments, propriétés, outils).
- **Injection dynamique** : Variables `{{variable}}` (ex: `{{customer_name}}`) remplies selon le mode.

### 📋 **Modes d'aperçu**

#### **Mode Canvas (Éditeur)**
- **Données** : Fictives mais cohérentes (client "Jean Dupont", produits avec prix/totaux calculés).
- **Éléments entreprise** : Identiques au Metabox (logo, etc.).
- **Déclenchement** : Bouton dans l'éditeur ouvrant une modal.
- **Objectif** : Aperçu design sans commande spécifique, visuellement crédible.

#### **Mode Metabox (WooCommerce)**
- **Données** : Réelles depuis WooCommerce (client, produits, commande).
- **Éléments entreprise** : Depuis paramètres WooCommerce.
- **Déclenchement** : Deux boutons dans la modal :
  - 📸 **"Aperçu Image"** : Screenshot (HTML2Canvas côté client).
  - 📄 **"Aperçu PDF"** : TCPDF (rendu éditable/précis).
- **Gestion manquante** : Signaler les problèmes (placeholders rouges "Donnée manquante" + message d'erreur).

### 🛠️ **Fonctionnalités détaillées**

#### **Exports PNG/JPG**
- **Formats** : Choix PDF/PNG/JPG dans la modal Metabox.
- **Qualité** : Réglable dans paramètres (défaut 150 DPI).
- **Nom fichier** : `{{customer_lastname}}_{{order_number}}.png` (ex: `Dupont_2372.png`).

#### **Notifications**
- **Style** : Toasts discrets (pop-up en haut écran).
- **Déclenchement** : Alerte si génération >2s ("Génération lente...").
- **Erreurs** : Toast + message spécifique dans modal.

#### **Mode développeur**
- **Activation** : Setting admin (caché, devs only).
- **Fonctionnalités** : Hot-reload, logs console, variables de test.
- **Retrait** : Enlevé en production.

#### **Templates prédéfinis**
- **Emplacement** : Modal dans le menu "Template" existant.
- **Limite** : Freemium (quelques gratuits, plus payant).
- **Sélection** : Visuelle (aperçus des modèles).

### 🏗️ **Architecture technique**

#### **Séparation Canvas/Metabox**
- **Providers** : `CanvasDataProvider` (données fictives) et `MetaboxDataProvider` (données réelles).
- **Injection** : Système modulaire pour switcher entre modes.

#### **Sécurité & Logs**
- **Permissions** : Basé sur rôles plugin (admins/vendeurs autorisés).
- **Logs** : Tout (erreurs/warnings/info) dans `wp_debug.log` en mode dev.
- **Nonces** : Sécurisation AJAX.

#### **Compatibilité**
- **Navigateurs** : Tous modernes + anciens si possible (fallback serveur si HTML2Canvas problématique).

### 📅 **Priorités v1.1.0**
- **Implémentation** : Un par un (tester étape par étape).
- **Fonctionnalités** :
  1. Cache intelligent.
  2. Validation automatique.
  3. Templates prédéfinis (avec limite freemium).

### ✅ **Critères de succès**
- **Performance** : <2s génération.
- **Sécurité** : 0 vulnérabilités.
- **Ergonomie** : Aperçus visibles (modal 90% écran), téléchargement direct.

---

## � Phases de reconstruction

### ✅ Phase 1 : Nettoyage complet
- [x] Supprimer tous les composants React d'aperçu
- [x] Nettoyer le code PHP backend
- [x] Supprimer les styles CSS d'aperçu
- [x] Recompiler les assets
- [x] Valider la syntaxe PHP
- [x] **Nettoyer le serveur distant**
- [x] **Redéployer les fichiers nettoyés**

### 🔍 Phase 2 : Analyse & conception

**📋 Ordre logique d'implémentation** : Les étapes sont organisées séquentiellement pour une implémentation fluide. Commencer par l'analyse des éléments existants, puis les données, l'architecture et enfin les interfaces.

**🧪 Approche** : Tests intégrés à chaque étape pour valider au fur et à mesure (pas de tests groupés à la fin).

#### **2.1 Auditer les 7 types d'éléments**
- [x] **Étape 2.1.1 : Identifier et lister les 7 types d'éléments**  
  - Ouvrir le code source des éléments (probablement dans `src/` ou `resources/js/`)  
  - Chercher les classes ou composants pour chaque type (texte, image, rectangle, etc.)  
  - Créer une liste simple avec nom et description courte  
  - Vérifier dans les templates existants quels éléments sont utilisés  
  - **Test** : Liste complète validée avec exemples  
  - **✅ VALIDÉ** : Tests automatisés complets (11 tests Jest + validation PHP) - 7 éléments confirmés  
  - **➕ TESTS COMPLÉMENTAIRES** : Outils (12 outils validés) et propriétés (6 catégories, 10+ mappings) - Tests automatisés Jest (32 tests) et PHP validés

- [x] **Étape 2.1.2 : Analyser les propriétés actuelles de chaque élément**  
  - Pour chaque élément, lister ses propriétés (position, taille, couleur, etc.)  
  - Noter les valeurs par défaut et les limites (min/max)  
  - Identifier les propriétés dynamiques vs statiques  
  - Documenter comment elles sont stockées en JSON  
  - **Test** : Propriétés testées dans éditeur  
  - **✅ VALIDÉ** : Analyse complète documentée (ANALYSE_PROPRIETES_ELEMENTS.md) - 7 éléments analysés avec 150+ propriétés détaillées

- [x] **Étape 2.1.3 : Documenter les limitations et bugs connus**  
  - Tester chaque élément dans l'éditeur actuel  
  - Noter les problèmes (rendu incorrect, propriétés manquantes, etc.)  
  - Chercher dans les issues Git ou rapports de bugs  
  - Prioriser les problèmes critiques vs mineurs  
  - **Test** : Bugs documentés et reproduits  
  - **✅ VALIDÉ** : Analyse complète du code source - 7 bugs critiques et 10+ limitations identifiées (LIMITATIONS_BUGS_REPORT.md)  

- [x] **Étape 2.1.4 : Définir les priorités d'implémentation**  
  - Classer les éléments par fréquence d'usage (texte en premier)  
  - Identifier les dépendances entre éléments  
  - Estimer la complexité de recréation pour chacun  
  - Créer une matrice priorité/complexité  
  - **Test** : Valider liste complète avec exemples concrets
  - **✅ VALIDÉ** : Plan d'implémentation complet défini (PHASE_2.1.4_PRIORITES_IMPLEMENTATION.md) - 7 éléments classés par priorité avec plan 3 phases (fondamentaux → intermédiaires → critique)

#### **2.2 Implémenter les éléments fondamentaux**
- [x] **Étape 2.2.1 : Améliorer company_logo**  
  - Unifier gestion src/imageUrl pour compatibilité  
  - Implémenter redimensionnement automatique selon ratio d'aspect  
  - Ajouter validation formats d'image (JPG, PNG, WebP, SVG, GIF, BMP, TIFF, ICO)  
  - Étendre propriétés de bordure (borderWidth, borderStyle, borderColor)  
  - **Test** : 17 tests unitaires validés, build réussi  
  - **✅ VALIDÉ** : company_logo entièrement amélioré avec gestion unifiée, redimensionnement automatique, validation formats et propriétés complètes

- [ ] **Étape 2.2.2 : Améliorer order_number**  
  - Implémenter formatage configurable (#CMD-2025-XXX, FACT-XXXX, etc.)  
  - Ajouter validation format et prévisualisation  
  - Étendre propriétés de style (police, couleur, alignement)  
  - Gérer cas spéciaux (commandes sans numéro)  
  - **Test** : Formats validés, prévisualisation fonctionnelle

- [ ] **Étape 2.2.3 : Améliorer company_info**  
  - Mapping complet des champs société WooCommerce  
  - Templates prédéfinis pour différents secteurs  
  - Gestion des données manquantes avec fallbacks  
  - Optimisation mise en page responsive  
  - **Test** : Tous champs société affichés correctement

#### **2.3 Documenter les variables dynamiques**
- [ ] **Étape 2.3.1 : Collecter toutes les variables WooCommerce disponibles**  
  - Examiner la classe `PDF_Builder_WooCommerce_Integration.php`  
  - Lister toutes les méthodes qui récupèrent des données (get_order_items, etc.)  
  - Inclure les variables standard WooCommerce (prix, client, etc.)  
  - Ajouter les variables personnalisées du plugin  
  - **Test** : Vérifier récupération données exemple

- [ ] **Étape 2.3.2 : Classifier les variables par catégories**  
  - Grouper par type : client, produit, commande, entreprise, etc.  
  - Créer des sous-catégories (ex: client → nom, email, adresse)  
  - Noter les variables obligatoires vs optionnelles  
  - Identifier les variables qui nécessitent des calculs  
  - **Test** : Valider classification avec données réelles

- [ ] **Étape 2.3.3 : Documenter le format et les exemples de chaque variable**  
  - Pour chaque variable, donner le format (string, number, date)  
  - Fournir des exemples concrets (ex: {{customer_name}} → "Jean Dupont")  
  - Noter les cas spéciaux (valeurs nulles, formats multiples)  
  - Documenter les transformations possibles (majuscules, format date)  
  - **Test** : Tester exemples dans template simple

- [ ] **Étape 2.3.4 : Créer un guide d'utilisation pour les variables**  
  - Écrire des règles d'usage (quand utiliser chaque variable)  
  - Créer des exemples de templates avec variables  
  - Documenter les erreurs possibles et solutions  
  - Inclure une référence rapide pour les développeurs  
  - **Test** : Guide utilisable par testeur externe  

#### **2.4 Définir l'architecture modulaire**  

#### **2.4 Définir l'architecture modulaire**
- [ ] **Étape 2.4.1 : Définir les endpoints AJAX internes nécessaires**  
  - Lister les actions AJAX (generate_preview, get_variables, validate_license, etc.)  
  - Spécifier les URLs et méthodes (wp_ajax_* hooks)  
  - Vérifier systèmes existants et les recréer si nécessaire  
  - Définir les paramètres requis pour chaque endpoint  
  - Planifier la gestion des erreurs et réponses  
  - **Test** : Endpoints testés avec Postman/cURL

- [ ] **Étape 2.4.2 : Spécifier les formats de données d'entrée/sortie**  
  - Définir le schéma JSON pour les requêtes AJAX  
  - Spécifier le format des réponses (succès/erreur)  
  - Documenter les types de données (string, array, object)  
  - Inclure des exemples de payloads  
  - **Test** : Schémas validés avec JSON Schema

- [ ] **Étape 2.4.3 : Documenter les méthodes de sécurité**  
  - Spécifier l'usage des nonces WordPress  
  - Définir la validation des données d'entrée  
  - Planifier les contrôles de permissions  
  - Documenter les mesures anti-injection  
  - **Test** : Tests de sécurité passés

- [ ] **Étape 2.4.4 : Créer des exemples d'utilisation des APIs**  
  - Fournir des exemples de code JavaScript pour appeler les endpoints  
  - Créer des scénarios d'usage courants  
  - Documenter les cas d'erreur et gestion  
  - Inclure des tests d'intégration simples  
  - **Test** : Exemples fonctionnels testés  

#### **2.5 Spécifier les APIs**
- [ ] **Étape 2.5.1 : Définir les endpoints AJAX internes nécessaires**  
  - Lister les actions AJAX (generate_preview, get_variables, validate_license, etc.)  
  - Spécifier les URLs et méthodes (wp_ajax_* hooks)  
  - Vérifier systèmes existants et les recréer si nécessaire  
  - Définir les paramètres requis pour chaque endpoint  
  - Planifier la gestion des erreurs et réponses  
  - **Test** : Endpoints testés avec Postman/cURL

- [ ] **Étape 2.5.2 : Spécifier les formats de données d'entrée/sortie**  
  - Définir le schéma JSON pour les requêtes AJAX  
  - Spécifier le format des réponses (succès/erreur)  
  - Documenter les types de données (string, array, object)  
  - Inclure des exemples de payloads  
  - **Test** : Schémas validés avec JSON Schema

- [ ] **Étape 2.5.3 : Documenter les méthodes de sécurité**  
  - Spécifier l'usage des nonces WordPress  
  - Définir la validation des données d'entrée  
  - Planifier les contrôles de permissions  
  - Documenter les mesures anti-injection  
  - **Test** : Tests de sécurité passés

- [ ] **Étape 2.5.4 : Créer des exemples d'utilisation des APIs**  
  - Fournir des exemples de code JavaScript pour appeler les endpoints  
  - Créer des scénarios d'usage courants  
  - Documenter les cas d'erreur et gestion  
  - Inclure des tests d'intégration simples  
  - **Test** : Exemples fonctionnels testés  

**🔄 Prochaines étapes** : Une fois la Phase 2 terminée, passer à la Phase 3 (Infrastructure) en s'appuyant sur cette analyse.

### 🏗️ Phase 3 : Infrastructure de base
- [ ] **Étape 3.1 : Créer PreviewRenderer avec canvas A4**
  - Implémenter classe PreviewRenderer avec dimensions A4 (210×297mm)
  - Configurer canvas HTML5 avec scaling approprié
  - Ajouter gestion responsive et zoom
  - Intégrer avec système de rendu existant
  - **Test** : Canvas A4 rendu correctement avec dimensions exactes

- [ ] **Étape 3.2 : Implémenter CanvasMode et MetaboxMode**
  - Créer classes CanvasModeProvider et MetaboxModeProvider
  - Implémenter injection de dépendances pour switcher entre modes
  - Définir interfaces communes et différences spécifiques
  - Tester basculement fluide entre modes
  - **Test** : Modes switchés sans erreurs, données injectées correctement

- [ ] **Étape 3.3 : Développer les 7 renderers spécialisés**
  - Créer TextRenderer, ImageRenderer, RectangleRenderer, etc.
  - Implémenter logique de rendu pour chaque type d'élément
  - Gérer propriétés spécifiques (position, taille, couleur, etc.)
  - Optimiser performance de rendu
  - **Test** : Chaque renderer affiche éléments correctement

- [ ] **Étape 3.4 : Configurer lazy loading**
  - Implémenter chargement différé des ressources lourdes
  - Ajouter gestion cache pour images et données
  - Optimiser chargement initial de la page
  - Précharger ressources critiques seulement
  - **Test** : Temps de chargement réduit, pas de blocage UI

### ✅ Phase 4 : Tests & optimisation
- [ ] **Étape 4.1 : Tests unitaires (100% couverture)**
  - Écrire tests unitaires pour toutes les classes PHP et JS
  - Atteindre couverture 100% des fonctions critiques
  - Implémenter tests automatisés dans CI/CD
  - Documenter cas limites et edge cases
  - **Test** : Couverture 100% validée, tests passent

- [ ] **Étape 4.2 : Tests d'intégration Canvas/Metabox**
  - Tester intégration complète Canvas ↔ Metabox
  - Valider basculement de données fictives/réelles
  - Vérifier cohérence rendu visuel entre modes
  - Tester scénarios complexes (multi-éléments, variables)
  - **Test** : Intégration fluide, pas de régressions

- [ ] **Étape 4.3 : Optimisation performance (< 2s)**
  - Mesurer et optimiser temps de génération (< 2s)
  - Implémenter cache intelligent et lazy loading
  - Optimiser requêtes DB et calculs
  - Réduire utilisation mémoire (< 100MB)
  - **Test** : Performance < 2s validée, mémoire optimisée

- [ ] **Étape 4.4 : Tests sécurité**
  - Audit complet sécurité (nonces, sanitization, permissions)
  - Tests de pénétration sur endpoints AJAX
  - Validation anti-injection et XSS
  - Audit rôles et permissions utilisateurs
  - **Test** : 0 vulnérabilités, sécurité renforcée

### 🚀 Phase 5 : Production
- [ ] **Étape 5.1 : Déploiement automatisé**
  - Configurer pipeline CI/CD complet
  - Automatiser build, tests et déploiement
  - Implémenter rollback automatique en cas d'erreur
  - Déployer sur environnements staging/production
  - **Test** : Déploiement automatique réussi, rollback fonctionnel

- [ ] **Étape 5.2 : Monitoring performance**
  - Implémenter monitoring temps réel (APM)
  - Configurer alertes sur métriques critiques
  - Dashboard performance et erreurs
  - Logs centralisés et analyse
  - **Test** : Monitoring opérationnel, alertes configurées

- [ ] **Étape 5.3 : Documentation complète**
  - Rédiger guides développeur et utilisateur
  - Documenter API et architecture technique
  - Créer tutoriels et exemples d'usage
  - Mettre à jour README et wiki
  - **Test** : Documentation complète et à jour

- [ ] **Étape 5.4 : Support production**
  - Configurer système de support (tickets, email)
  - Implémenter logging avancé pour debug
  - Préparer procédures maintenance et mises à jour
  - Former équipe support si nécessaire
  - **Test** : Support opérationnel, procédures validées

---

## 📊 État actuel

**Phase active** : 2/7  
**Progression** : 28% (Phase 2.1 complète + company_logo amélioré - éléments validés, propriétés analysées, bugs corrigés, priorités définies, premier élément fondamental implémenté)  
**Prochaine action** : Phase 2.2.2 - Implémentation order_number (formatage configurable)

---

## ✅ Critères de succès

- ⚡ **Performance** : < 2s génération, < 100MB RAM
- 🔒 **Sécurité** : 0 vulnérabilités
- 🧪 **Qualité** : Tests 100% succès
- 📚 **Maintenabilité** : Code modulaire

---

## �️ Phase 6 : Améliorations techniques
- [ ] **Étape 6.1** : Migrer JavaScript vers TypeScript (interfaces pour éléments, variables)
  - **Test** : Migration TypeScript réussie, interfaces validées
- [ ] **Étape 6.2** : Implémenter ESLint + Prettier pour qualité code
  - **Test** : Code formaté automatiquement, linting sans erreurs
- [ ] **Étape 6.3** : Ajouter tests unitaires (Jest pour JS, PHPUnit pour PHP)
  - **Test** : Tests unitaires opérationnels, couverture >80%
- [ ] **Étape 6.4** : Optimiser performance (code splitting, caching)
  - **Test** : Performance améliorée, bundle size réduit
- [ ] **Étape 6.5** : Audit sécurité (nonces, sanitization)
  - **Test** : Audit sécurité passé, vulnérabilités corrigées
- [ ] **Étape 6.6** : Améliorer UX (cercle chargement multicolore, gestion erreurs adaptée)
  - **Test** : UX améliorée, feedback utilisateur positif
- [ ] **Étape 6.7** : Optimiser performance DB (index, commentaires structurés)
  - **Test** : Requêtes DB optimisées, temps réponse réduit
- [ ] **Étape 6.8** : Audit sécurité ciblé (permissions rôles existants)
  - **Test** : Permissions sécurisées, accès contrôlé
- [ ] **Étape 6.9** : Internationaliser (anglais, allemand, français avec .po/.mo)
  - **Test** : Traductions complètes, interface localisée
- [ ] **Étape 6.10** : Refactoriser modulaire (nouveaux modules, supprimer anciens local + serveur)
  - **Test** : Refactorisation réussie, code modulaire

## 💰 Phase 7 : Monétisation Freemium
- [ ] **Étape 7.1** : Créer système de licences (activation clé premium - 69€ à vie, validation API/site externe, option multisite)
  - **Test** : Système de licences opérationnel, validation fonctionnelle
- [ ] **Étape 7.2** : Limiter à 1 template actif (choix style) + watermark sur PDFs gratuits
  - **Test** : Limitation template appliquée, watermark visible
- [ ] **Étape 7.3** : Désactiver exports PNG/JPG en gratuit (PDF seulement)
  - **Test** : Exports PNG/JPG bloqués en gratuit
- [ ] **Étape 7.4** : Restreindre à 15 variables dynamiques basiques (7 éléments primordiaux)
  - **Test** : Variables limitées en gratuit, premium débloquées
- [ ] **Étape 7.5** : Ajouter upsells dans interface (boutons "Upgrade to Premium" bloquants dans éditeur/metabox)
  - **Test** : Upsells visibles et bloquants
- [ ] **Étape 7.6** : Intégrer système de paiement (WooCommerce + site externe pour licences)
  - **Test** : Paiement intégré, licences délivrées
- [ ] **Étape 7.7** : Tester limitations freemium (vérifier blocages templates/exports/variables)
  - **Test** : Toutes limitations freemium validées

### 📝 **Historique et décisions Phase 7**

#### **Éléments importants de la discussion** :
- **Stratégie freemium** : Version gratuite attractive avec limitations claires, premium à 69€ à vie (1 site)
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

### 🔮 **Versions futures (v1.2.0+)**
- [ ] **API REST publique** : Endpoints pour intégrations tierces (générer PDF via API externe)
- [ ] **Mode hors ligne** : Aperçu basique sans connexion
- [ ] **Templates premium pack** : Collection de modèles payants
- [ ] **Intégrations tierces** : Support Zapier, Integromat
- [ ] **Analytics freemium** : Tableau de bord conversions gratuit→premium

---

## 📝 Note de progression - 22 octobre 2025

**✅ Phase 2.1.4 TERMINÉE** : Priorités d'implémentation définies avec plan détaillé en 3 phases :
- **Phase 2.2** (2-3 sem.) : Éléments fondamentaux (company_logo, order_number, company_info)
- **Phase 2.3** (3-4 sem.) : Éléments intermédiaires (customer_info, dynamic-text, mentions)  
- **Phase 2.4** (4-6 sem.) : Élément critique (product_table)

**🔧 Corrections appliquées** : 4 bugs de priorité moyenne corrigés (code mort nettoyé, propriétés texte unifiées, validation vérifiée).

**✅ Phase 2.2.1 TERMINÉE** : company_logo entièrement amélioré :
- **Gestion unifiée src/imageUrl** : Support des deux propriétés pour compatibilité maximale
- **Redimensionnement automatique** : Calcul intelligent des dimensions selon ratio d'aspect naturel
- **Validation formats d'image** : Support JPG, PNG, WebP, SVG, GIF, BMP, TIFF, ICO avec messages d'erreur explicites
- **Propriétés de bordure complètes** : borderWidth, borderStyle, borderColor pour personnalisation avancée
- **Tests complets** : 17 tests unitaires validés, build réussi sans régression

**🎯 Prochaine étape** : Phase 2.2.2 - order_number (formatage configurable, validation)

---

*Roadmap simplifiée - Reconstruction depuis zéro*
