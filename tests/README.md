# 📁 Tests - Suite de Tests Automatisés

Ce dossier contient tous les scripts de test automatisés pour PDF Builder Pro.

## 📂 Structure

### `integration/`
Tests d'intégration système
- `test-phase5.7.php` - Tests d'intégration Phase 5.7

### `performance/`
Tests de performance et charge
- `test-performance-baseline.js` - Tests de performance baseline
- `test-load-artillery.js` - Tests de charge Artillery
- `artillery-config.yml` - Configuration Artillery complète
- `artillery-config-light.yml` - Configuration Artillery légère

### `security/`
Tests de sécurité
- `test-security.js` - Tests de sécurité de base
- `test-security-fixes-validation.js` - Validation des corrections sécurité

### `compatibility/`
Tests de compatibilité navigateur
- `test-cross-browser.js` - Tests compatibilité navigateurs
- `test-enhanced-browser-compatibility.js` - Tests compatibilité étendus

### `unit/`
Tests unitaires PHP (hérité)
- Tests unitaires existants pour les composants PHP

## 🚀 Exécution des Tests

```bash
# Tests performance
node tests/performance/test-performance-baseline.js

# Tests sécurité
node tests/security/test-security-fixes-validation.js

# Tests compatibilité
node tests/compatibility/test-enhanced-browser-compatibility.js

# Tests charge (nécessite Artillery)
cd tests/performance && artillery run artillery-config.yml

# Tests unitaires PHP
php tests/unit/PDF_Builder_Variable_Mapper_Standalone_Test.php
php tests/run-all-tests.php
```

## 📊 Rapports

Les rapports de test sont générés dans `docs/reports/`.

---
*Mis à jour le 20 octobre 2025*
└── unit/
    ├── PDF_Builder_Variable_Mapper_Test.php          # Tests originaux
    └── PDF_Builder_Variable_Mapper_Standalone_Test.php # Tests standalone
```

## Mocks implémentés

### Fonctions WordPress
- `get_option()` - Récupération options
- `date_i18n()` - Formatage dates
- `wp_date()` - Dates WordPress
- `wc_price()` - Formatage prix

### Fonctions WooCommerce
- `wc_get_order_statuses()` - Statuts commandes
- `get_woocommerce_currency()` - Devise
- `WC()` - Objet global WooCommerce

### Classes mock
- `MockWCOrder` - Commande WooCommerce simulée
- `MockOrderItem` - Élément de commande
- `MockProduct` - Produit WooCommerce

## Prochaines étapes

### 🔄 Tests d'intégration
- Tests avec commandes WooCommerce réelles
- Tests interface d'administration
- Tests composants React

### 🔄 Tests de performance
- Benchmarks temps de rendu
- Tests consommation mémoire
- Tests charge élevée

### 🔄 Tests de sécurité
- Protection XSS
- Validation CSRF
- Contrôle permissions

### 🔄 Tests end-to-end
- Workflows complets
- Tests multi-navigateurs
- Tests mobiles

## Métriques de qualité

- **Couverture actuelle** : Tests unitaires VariableMapper (100%)
- **Objectif global** : > 80% couverture code
- **Performance** : < 2s par test
- **Fiabilité** : 0 échec en conditions normales

## Validation Phase 5.6

- [x] Infrastructure de test créée
- [x] Tests unitaires VariableMapper implémentés
- [x] Mocks WordPress/WooCommerce fonctionnels
- [x] Gestion d'erreurs et robustesse ajoutée
- [ ] Tests d'intégration (prêts pour développement)
- [ ] Tests de performance (prêts pour développement)
- [ ] Tests de sécurité (prêts pour développement)
- [ ] Tests end-to-end (prêts pour développement)

## Améliorations Implémentées

### ✅ Support des Statuts de Commande Personnalisés
- **Extension des mocks** : Support des statuts ajoutés par plugins (wc-devis, etc.)
- **Statuts inclus** : devis, quotation, estimate, draft, partial, shipped, delivered, returned, backordered
- **Compatibilité** : Fonctionne avec tous les plugins WooCommerce ajoutant des statuts personnalisés

### ✅ Inclusion des Frais de Commande
- **Extension products_list** : Les frais sont maintenant inclus dans la liste des produits
- **Format uniforme** : Même formatage que les produits (nom x quantité - prix)
- **Gestion générique** : Support de tous types d'items de ligne (produits, frais, etc.)
- **Robustesse** : Gestion graceful des différents types d'objets