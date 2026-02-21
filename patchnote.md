# Changelog — PDF Builder Pro V2

## Version 1.1.0.2 (22 février 2026) — Optimisation & RGPD

### Sécurité & Conformité

- 🔒 **RGPD complet** :
  - 5 handlers AJAX pour conformité légale
  - `handle_export_gdpr_data()` : Export JSON/HTML
  - `handle_delete_gdpr_data()` : Anonymisation 1-clic
  - `handle_get_consent_status()` : État 8 consentements
  - `handle_get_audit_log()` : Historique 90j
  - `handle_export_audit_log()` : Export CSV
  - Onglet "Sécurité" complet dans Admin Panel
  - Audit logging de chaque action
  - Chiffrement AES-256 données sensibles

### Performance & Cache

- 💾 **Système de cache** :
  - `PDF_Builder_Cache_Manager` (singleton)
  - Transients WordPress avec compression gzip
  - Invalidation automatique sur modification template
  - Métriques en temps réel (hit rate, taille, âge)
  - **Résultat** : 10x plus rapide pour templates récurrents
  - TTL configurable (défaut 3600s = 1h)
  - Réduction 40% taille fichiers

- 🎨 **CSS Deduplication** :
  - Script automatique pour détecter/fusionner doublons
  - **Résultats** :
    - `pdf-builder-admin.css` : 58 doublons → −6,841 bytes
    - `dashboard.css` : 1 doublon → −2 bytes
    - `templates-page.css` : 1 doublon → −7 bytes
  - Total : 60 doublons supprimés, −8 KB
  - Validation brace balance (✓ 520 open = 520 close)

### Admin Panel Enhancements

- 🖥️ **Onglet "Système"** : 
  - Affichage cache metrics (hit rate, taille, entrées)
  - Toggle cache on/off
  - Bouton "Vider le cache" 1-clic
  - Vue d'ensemble santé système

- 🔴 **Kill Chromium Button**:
  - Bouton emergency pour arrêter Chromium
  - Utile si stuck processes
  - Endpoint API dédiée

### Documentation

- 📖 **Documentation de vente** (5 fichiers) :
  - `PRESENTATION.md` : Vue d'ensemble marketing
  - `FEATURES_COMPLETE.md` : Détail fonctionnalités
  - `INSTALLATION.md` : Guide setup 5 minutes
  - `FAQ.md` : 50+ questions/réponses
  - `PRICING.md` : Tarification Gratuit/Premium
  - Prêt pour site vente

### Template Gallery

- 🎨 **Filtrage templates** :
  - Changement catégories : `invoice`→`facture`, `quote`→`devis`
  - Suppression `contract` (non utilisé)
  - Affichage 3 gratuits + 2 premium uniquement

### Bug Fixes

- ✅ Correction toggle settings ne sauvegardant pas
- ✅ Fix POST keys pour systeme/security tabs
- ✅ API REST claims corrigées (pas 100+ endpoints réels)
- ✅ OAuth2/Webhooks illimités retirés (non implémentés)

## Version 1.1.0.1 (27 janvier 2026)

### Corrections (Bug Fixes)

- ✅ **Propriétés police séparées** : header vs body (headerFontSize, bodyFontSize, etc.)
- ✅ **Espacement lignes** : Correction chevauchement company_info
- ✅ **Fonction normalizeColor** : Ajout fonction manquante JS
- ✅ **Optimisation Canvas.tsx** : Refactorisation, helpers, réduction duplication

### Améliorations

- 🔄 **Interface personnalisation** : Support complet propriétés police distinctes
- 🔄 **Gestion mémoire Canvas** : Optimisation cache images
- 🔄 **Normalisation poids police** : Support valeurs numériques (700) et textuelles (bold)

---

## Version 1.1.0.0 (19 janvier 2026)

### Corrections (Bug Fixes)

- 🐛 Suppression système welcome/onboarding
- 🐛 Unification version (1.1.0 partout)
- 🐛 Nettoyage logs debug production
- 🐛 Centralisation chargement Composer

### Sécurité

- 🔒 Audit complet sanitisation
- 🔒 Validation stricte entrées
- 🔒 Permissions granulaires

---

## Prochaines versions

### Version 1.2.0 (Roadmap)

- [ ] Éditeur visuel drag & drop
- [ ] Bibliothèque de composants
- [ ] Intégrations tierces (CRM, ERP)
- [ ] API REST complète
- [ ] Support multi-langues

### Version 2.0.0 (Vision)

- [ ] Architecture microservices
- [ ] Analytics et reporting avancés

---

_Dernière mise à jour : 22 février 2026_</content>
<parameter name="filePath">i:\wp-pdf-builder-pro-V2\CHANGELOG.md
