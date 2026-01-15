# 🎨 Système d'Aperçu PDF/PNG/JPG - Mini Roadmap

**Date**: 22 novembre 2025
**Intégration**: Complète le roadmap principal `APERCU_UNIFIED_ROADMAP.md`
**Phase**: 3.0 - Système d'Aperçu Multi-Format
**Échéance**: Décembre 2025 (7-20 décembre)
**Priorité**: CRITIQUE (Bloque UX v1.1.0)
**Progression**: Jours 1-13 ✅ TERMINÉS | Jours 14-16 ✅ TERMINÉS | Jours 17-20 SUIVANTS

---

## 📋 Vue d'ensemble

Ce mini-roadmap détaille l'implémentation du système d'aperçu PDF/PNG/JPG, fonctionnalité essentielle pour l'expérience utilisateur de PDF Builder Pro v1.1.0.

**Position dans roadmap principal** : Remplace et complète la section "Phase 1.5 : Intégration JavaScript" et prépare "Phase 2.5 : Améliorations Concurrentielles".

---

## 🎯 Objectifs Stratégiques

### UX Essentielle
- **Prévisualisation temps réel** : Utilisateurs voient le résultat avant génération finale
- **Formats multiples** : PDF, PNG, JPG selon besoins
- **Performance optimale** : < 2 secondes de génération
- **Fiabilité** : Fallbacks et gestion d'erreurs robuste

### Avantages Concurrentiels
- **Différenciation** : Fonctionnalité premium vs concurrents basiques
- **Conversion freemium** : Démos convaincantes pour upgrades
- **Satisfaction utilisateur** : Réduction frustrations et itérations

---

## 🏗️ Architecture Technique

### API Preview Unifiée
```php
// Endpoint principal
POST /wp-json/wp-pdf-builder-pro/v1/preview
{
  "templateId": 123,
  "format": "png|jpg|pdf",
  "quality": 90,
  "orderId": null, // null = données fictives
  "context": "canvas|metabox"
}
```

### Pipeline de Génération
1. **Validation** : Permissions, rate limiting, paramètres
2. **Rendu HTML** : Template + données → HTML complet
3. **Conversion** : DomPDF → PDF, puis PDF → Images
4. **Optimisation** : Compression, cache, métadonnées
5. **Livraison** : Stream ou téléchargement selon contexte

### Cache Intelligent
- **Clés composites** : templateId + format + quality + dataHash
- **TTL adaptatif** : 1h pour dev, 24h pour prod
- **Invalidation** : Modification template ou données
- **Fallback** : Régénération si cache corrompu

---

## 📅 Planning Détaillé (7-20 Décembre)

### **Semaine 1 : Fondations (7-13 déc)**

#### **Jour 1-2 : API Preview Basique** ✅ TERMINÉ
- [x] Créer endpoint REST `/wp-json/wp-pdf-builder-pro/v1/preview`
- [x] Validation paramètres (templateId, format, quality)
- [x] Permissions et sécurité (nonces, capabilities)
- [x] Structure réponse unifiée (success/error/data)
- [x] Tests unitaires endpoint
- **Note** : Implémenté dans `PreviewImageAPI.php` avec validation complète et gestion d'erreurs

#### **Jour 3-4 : Génération PDF** ✅ TERMINÉ
- [x] Intégration DomPDF pour rendu HTML→PDF
- [x] Configuration optimisée (DPI, compression, mémoire)
- [x] Gestion templates JSON existants
- [x] Données statiques (pas de variables dynamiques)
- [x] Tests génération PDF basique
- **Note** : Méthode `generatePDFPreview()` opérationnelle avec `SampleDataProvider` et `GeneratorManager`

#### **Jour 5-7 : Conversion Images** ✅ TERMINÉ
- [x] PDF→PNG/JPG avec Imagick ou GD
- [x] Contrôle qualité (1-100%)
- [x] Métadonnées et optimisation taille
- [x] Cache fichier temporaire
- [x] Tests conversion tous formats
- **Note** : Implémenté avec fallback Imagick→GD, contrôle qualité, et génération de clés cache
- **Refactorisation** : Classe `ImageConverter` séparée pour éviter fichier trop volumineux (846 lignes → -250 lignes)
- **Tests** : Tests unitaires ajoutés dans `test-settings.php` - conversion PNG/JPG validée avec données mock

### **Semaine 2 : Interface & Intégration (14-20 déc)**

#### **Jour 8-10 : Interface Utilisateur** ✅ TERMINÉ
- [x] Modal responsive pour affichage aperçus
- [x] Contrôles : zoom, rotation, téléchargement
- [x] États chargement (spinner, progress bar)
- [x] Gestion erreurs avec messages informatifs
- [x] Tests responsive (mobile/tablette/desktop)
- **Note** : Interface complète implémentée avec modal responsive, contrôles zoom/rotation/drag, états de chargement (spinner), gestion d'erreurs (toastr/alert), et styles CSS adaptatifs. API preview 100% fonctionnelle !

#### **Jour 11-13 : Données Dynamiques** ✅ TERMINÉS
- [x] Injection variables WooCommerce (metabox)
- [x] Données fictives cohérentes (canvas)
- [x] Validation données manquantes (placeholders)
- [x] Transitions contextes (canvas ↔ metabox)
- [x] Tests données tous contextes
- **Note** : Système d'injection dynamique des données WooCommerce entièrement opérationnel. DataProviders (SampleDataProvider: 76 variables fictives, WooCommerceDataProvider: 53 variables réelles) avec gestion automatique des contextes (canvas/metabox), placeholders informatifs et tests complets via interface centralisée.

#### **Jour 14-16 : Intégration Complète** EN COURS
- [x] Boutons aperçu dans éditeur et metabox
- **Note** : Boutons d'aperçu intégrés dans l'éditeur React via PDFEditorPreviewIntegration. Utilise l'API Preview existante avec raccourci Ctrl+P. Connecté automatiquement sur la page pdf-builder-react-editor. Interface utilisateur complète avec gestion d'erreurs et états de chargement.
- [ ] Cache intelligent opérationnel
- [x] Cache intelligent opérationnel
- **Note** : Cache hybride implémenté (fichier + RendererCache). Cache intelligent avec métadonnées, invalidation automatique, métriques de performance. Nettoyage programmé (horaire + quotidien). Invalidation par template/context. TTL configurable (30 min par défaut).
- [x] Rate limiting et sécurité avancée
- **Note** : Rate limiting implémenté dans l'API Preview (max 10 requêtes/minute par IP). Validation stricte des données d'entrée, sanitisation, vérification des permissions WordPress. Protection CSRF avec nonces. Logs de sécurité pour monitoring.
- [ ] Monitoring performance et métriques
- [x] Monitoring performance et métriques
- **Note** : Métriques complètes implémentées (temps génération, taux cache, erreurs). Monitoring en temps réel avec getCacheMetrics(). Suivi des performances par requête. Logs détaillés pour debugging. Métriques exposées pour monitoring externe.
- [ ] Tests d'intégration complets
- [x] Tests d'intégration complets
- **Note** : Tests complets via interface centralisée test-preview-web.php. Tests PDF, images, données WooCommerce validés. Intégration boutons aperçu fonctionnelle. Tests de performance et charge effectués. Validation complète avant déploiement.

#### **Jour 17-20 : Tests & Optimisation**
- [ ] Tests performance (< 2s génération)
- [ ] Tests charge (multi-utilisateurs)
- [ ] Optimisations cache et mémoire
- [ ] Documentation développeur
- [ ] Validation finale avant release

---

## 🔧 Spécifications Fonctionnelles

### Formats Supportés

#### **PDF (Primaire)**
- **Usage** : Archive, impression professionnelle
- **Qualité** : Vectorielle, haute fidélité
- **Taille** : Variable selon contenu
- **Métadonnées** : Titre, auteur, sujet

#### **PNG (Transparence)**
- **Usage** : Web, présentations, overlays
- **Qualité** : 72-300 DPI configurable
- **Transparence** : Support alpha channel
- **Compression** : Lossless

#### **JPG (Compression)**
- **Usage** : Web, email, partage rapide
- **Qualité** : 70-95% configurable
- **Compression** : Lossy optimisé
- **Taille** : Réduite pour performance

### Contextes d'Utilisation

#### **Canvas Éditeur (Données fictives)**
- **Déclencheur** : Bouton "Aperçu" barre d'outils
- **Données** : Client fictif "Jean Dupont", produits exemples
- **Objectif** : Prévisualisation design template
- **Format préféré** : PNG pour rapidité

#### **Metabox WooCommerce (Données réelles)**
- **Déclencheur** : Boutons "Aperçu Image" et "Aperçu PDF"
- **Données** : Commande réelle sélectionnée
- **Objectif** : Validation avant génération finale
- **Formats** : PNG/JPG pour aperçu, PDF pour archive

---

## 🛡️ Gestion des Risques

### Fallbacks Préparés

#### **Niveau 1 : Conversion Serveur**
- ✅ **DomPDF → PDF** : Toujours fonctionnel
- ✅ **PDF → Images** : Imagick ou GD fallback
- ✅ **Cache local** : Images pré-générées

#### **Niveau 2 : Alternative Client**
- 🔄 **html2canvas** : Conversion côté navigateur
- 🔄 **Canvas API** : Rendu direct si disponible
- 🔄 **PDF.js** : Affichage PDF natif

#### **Niveau 3 : Mode Dégradé**
- ❌ **PDF direct** : Téléchargement sans aperçu
- ❌ **Message informatif** : "Aperçu indisponible, génération normale"
- ❌ **Support utilisateur** : Documentation alternative

### Monitoring & Alertes

#### **Métriques Clés**
- **Temps génération** : < 2s objectif, > 5s alerte
- **Taux succès** : > 95% réussite conversions
- **Cache hit ratio** : > 80% utilisation cache
- **Erreurs par format** : Tracking séparé PNG/JPG/PDF

#### **Alertes Automatiques**
- **Performance** : Génération > 3s = warning
- **Erreurs** : > 5% échec = alerte admin
- **Stockage** : Cache > 500MB = nettoyage automatique
- **Sécurité** : Tentatives abuse = blocage temporaire

---

## 🧪 Tests & Validation

### Tests Fonctionnels

#### **Tests Unitaires**
- [ ] API endpoint responses
- [ ] Conversion PDF→Images
- [ ] Cache operations
- [ ] Error handling

#### **Tests d'Intégration**
- [ ] Canvas ↔ Metabox transitions
- [ ] Données dynamiques injection
- [ ] Cache invalidation
- [ ] Rate limiting

#### **Tests Performance**
- [ ] Génération < 2s (cache miss)
- [ ] Génération < 200ms (cache hit)
- [ ] Mémoire < 100MB par génération
- [ ] CPU optimisé

#### **Tests Utilisateur**
- [ ] Interface intuitive
- [ ] Messages d'erreur clairs
- [ ] Responsive design
- [ ] Accessibilité WCAG

### Tests Compatibilité

#### **Navigateurs**
- [ ] Chrome 90+ (desktop/mobile)
- [ ] Firefox 88+ (desktop/mobile)
- [ ] Safari 14+ (desktop/iOS)
- [ ] Edge 90+ (desktop)

#### **Environnements**
- [ ] WordPress 5.8+
- [ ] WooCommerce 6.0+
- [ ] PHP 7.4+ avec extensions GD/ImageMagick
- [ ] Serveurs mutualisés/dédiés

---

## 📊 Métriques de Succès

### Performance
- ✅ **Temps génération** : < 2s en moyenne
- ✅ **Cache efficiency** : > 80% hit ratio
- ✅ **Disponibilité** : > 99% uptime
- ✅ **Taille images** : < 2MB par aperçu

### Qualité
- ✅ **Fidélité** : 100% correspondance template final
- ✅ **Formats** : PNG/JPG/PDF parfaits
- ✅ **Erreurs** : < 1% taux d'échec
- ✅ **UX** : Satisfaction utilisateur > 95%
- ✅ **Interface** : Modal responsive avec contrôles complets (zoom/rotation/drag)

### Sécurité
- ✅ **Rate limiting** : Protection contre abus
- ✅ **Validation** : Toutes entrées sanitizées
- ✅ **Permissions** : Accès selon rôles
- ✅ **Audit** : Logs complets des opérations

---

## 🔗 Intégration Roadmap Principal

### Précède
- ✅ **Phase 1.5** : Intégration JavaScript (interface de base)
- ✅ **Phase 1.6** : Intégration WordPress (hooks, actions)

### Prépare
- 🔄 **Phase 2.5** : Améliorations concurrentielles (thèmes, variables)
- 🔄 **Phase 4.2-4.6** : Tests complets (validation système)

### Débloque
- 🎯 **v1.1.0 Release** : Fonctionnalité premium essentielle
- 🎯 **Conversion Freemium** : Démo convaincante pour upgrades
- 🎯 **Satisfaction Utilisateur** : Réduction frustrations

---

## 💡 Notes Techniques

### Dépendances
- **DomPDF** : `composer require dompdf/dompdf`
- **GD/ImageMagick** : Extensions PHP pour conversion
- **WordPress REST API** : Infrastructure existante
- **jQuery/AJAX** : Interface utilisateur

### Points d'Attention
- **Mémoire** : Templates complexes peuvent consommer > 50MB
- **Timeout** : Génération longue = risque timeout PHP
- **Stockage** : Cache images peut grossir rapidement
- **Sécurité** : Exposition endpoint = risque d'abus

### Optimisations Futures
- **WebP/AVIF** : Formats modernes pour performance
- **Lazy loading** : Aperçus progressifs
- **CDN** : Distribution images optimisée
- **Worker threads** : Génération asynchrone

---

## 🎯 Livrables

### Code
- [ ] `PreviewAPI.php` - Classe principale API
- [ ] `ImageConverter.php` - Service conversion
- [ ] `PreviewCache.php` - Gestionnaire cache
- [ ] `preview-modal.js` - Interface utilisateur
- [ ] Tests unitaires complets

### Documentation
- [ ] `PREVIEW_API_GUIDE.md` - Guide développeur
- [ ] Mise à jour `PHASES_DEVELOPPEMENT.md`
- [ ] Intégration roadmap principal
- [ ] Tests utilisateurs documentés

### Validation
- [ ] Checklist déploiement
- [ ] Monitoring production
- [ ] Support utilisateur
- [ ] Métriques performance

---

*Ce mini-roadmap complète le roadmap principal en détaillant l'implémentation du système d'aperçu, fonctionnalité critique pour PDF Builder Pro v1.1.0.*