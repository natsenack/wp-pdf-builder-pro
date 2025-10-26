# PHASE 3: TESTS UTILISATEUR - PDF Builder Pro Vanilla JS
## Guide de Validation Production

### 🎯 OBJECTIF
Valider que la migration React → Vanilla JS fonctionne parfaitement en production sur threeaxe.fr

### 📋 PRÉREQUIS
- Accès administrateur WordPress sur threeaxe.fr
- Navigateur moderne avec support Canvas 2D
- Console développeur ouverte pour monitoring

---

## 🧪 TEST 1: ACCÈS À L'ÉDITEUR PDF

### Étapes:
1. **Connexion WordPress**
   - Aller sur: `https://threeaxe.fr/wp-admin/`
   - Se connecter avec identifiants admin

2. **Accès à l'éditeur**
   - Menu: `PDF Builder Pro > Éditeur PDF`
   - OU URL directe: `https://threeaxe.fr/wp-admin/admin.php?page=pdf-builder-editor`

3. **Validation initiale**
   - ✅ Page charge sans erreur 500
   - ✅ Canvas blanc A4 (595x842px) visible
   - ✅ Toolbar d'outils présente
   - ✅ Panneau propriétés visible
   - ✅ Console navigateur: pas d'erreurs JavaScript

### Résultat attendu:
```
Console: ✅ Modules Vanilla JS chargés avec succès
Canvas: ✅ PDFCanvasVanilla initialisé avec succès
```

---

## 🧪 TEST 2: AJOUT D'ÉLÉMENTS DE BASE

### 2.1 Élément Texte
1. **Ajouter un élément texte**
   - Cliquer bouton "Texte" dans la toolbar
   - Cliquer sur le canvas pour placer le texte

2. **Éditer le texte**
   - Double-cliquer sur l'élément texte
   - Taper: "TEST VANILLA JS MIGRATION"
   - Valider avec Entrée

3. **Modifier les propriétés**
   - Sélectionner l'élément
   - Panneau propriétés: changer police, taille, couleur
   - Vérifier que les changements s'appliquent

### 2.2 Élément Forme
1. **Ajouter un rectangle**
   - Bouton "Rectangle" → cliquer sur canvas
   - Redimensionner en glissant les coins

2. **Personnaliser**
   - Couleur de fond, bordure, opacité
   - Vérifier rendu Canvas 2D natif

### 2.3 Élément Image (si disponible)
1. **Ajouter une image**
   - Bouton "Image" → sélectionner fichier
   - Positionner et redimensionner

---

## 🧪 TEST 3: FONCTIONNALITÉS AVANCÉES

### 3.1 Sélection Multiple
1. **Sélectionner plusieurs éléments**
   - Maintenir Ctrl + cliquer sur éléments
   - Vérifier cadre de sélection groupée

2. **Opérations groupées**
   - Déplacer, redimensionner, supprimer en groupe

### 3.2 Calques (Layers)
1. **Gestion des calques**
   - Panneau calques visible
   - Changer ordre Z-index
   - Masquer/afficher calques

### 3.3 Propriétés Avancées
1. **Binding de propriétés**
   - Tester les watchers de propriétés
   - Validation automatique des valeurs

---

## 🧪 TEST 4: EXPORT PDF

### 4.1 Export Haute Qualité
1. **Lancer l'export**
   - Bouton "Exporter PDF"
   - Sélectionner qualité "Haute"

2. **Validation**
   - ✅ Fichier PDF généré
   - ✅ Taille raisonnable (< 2MB)
   - ✅ Éléments correctement rendus
   - ✅ Texte lisible et net

### 4.2 Test Multi-Format
1. **Formats disponibles**
   - PDF standard
   - PDF optimisé
   - Aperçu (si disponible)

---

## 🧪 TEST 5: INTÉGRATION WOOCOMMERCE

### 5.1 Éléments Dynamiques
1. **Accès aux données WooCommerce**
   - Bouton "WooCommerce" dans toolbar
   - Liste des éléments dynamiques disponibles

2. **Ajouter élément dynamique**
   - Produit: `[product_name]`
   - Prix: `[product_price]`
   - Variables disponibles dans la doc

### 5.2 Validation Données
1. **Test en conditions réelles**
   - Créer template avec éléments dynamiques
   - Tester génération avec données produit réelles

---

## 🧪 TEST 6: PERFORMANCE

### 6.1 Métriques de Chargement
1. **Temps de chargement initial**
   - Mesurer temps jusqu'à "éditeur prêt"
   - Bundle: ~127 KiB (vérifier Network tab)

2. **Performance d'édition**
   - Ajouter 10+ éléments
   - Mesurer fluidité du rendu Canvas
   - Vérifier utilisation mémoire

### 6.2 Optimisations
1. **Cache et optimisations**
   - Vérifier système de cache actif
   - Performance constante après rechargements

---

## 📊 CHECKLIST DE VALIDATION

### ✅ Fonctionnel
- [ ] Accès éditeur sans erreur
- [ ] Canvas s'affiche correctement
- [ ] Ajout éléments texte/fonctions
- [ ] Modification propriétés
- [ ] Sélection multiple
- [ ] Export PDF réussi
- [ ] Intégration WooCommerce

### ✅ Performance
- [ ] Chargement < 5 secondes
- [ ] Rendu fluide (60 FPS)
- [ ] Mémoire stable
- [ ] Bundle optimisé (127 KiB)

### ✅ Qualité
- [ ] Pas d'erreurs console
- [ ] Rendu Canvas net
- [ ] Export PDF haute qualité
- [ ] Interface responsive

---

## 🚨 GESTION DES ERREURS

### Si problème détecté:
1. **Console développeur**
   - Ouvrir F12 → Console
   - Noter les erreurs JavaScript
   - Vérifier chargement des modules

2. **Network tab**
   - Vérifier chargement des scripts
   - Status 200 pour tous les modules
   - Taille bundle correcte

3. **Solutions communes**
   - Vider cache navigateur
   - Tester autre navigateur
   - Vérifier connexion réseau

---

## 📈 RAPPORT FINAL

### Format du rapport:
```markdown
# RAPPORT VALIDATION PHASE 3
## Date: [DATE]
## Testeur: [NOM]

## Résumé
- ✅/❌ Tests réussis: X/Y
- Performance: [métriques]
- Issues détectés: [liste]

## Détails par test
### Test 1: Accès éditeur
- Résultat: ✅/❌
- Temps de chargement: X ms
- Erreurs: [liste]

### Test 2: Éléments de base
- Texte: ✅/❌
- Formes: ✅/❌
- Images: ✅/❌

[... continuer pour chaque test]

## Conclusion
[Validation finale - Prêt pour production OU Issues à corriger]
```

---

## 🎯 PROCHAINES ÉTAPES

Après validation Phase 3:
- **Phase 4**: Optimisations finales production
- **Mise en production complète**
- **Monitoring continu**

---

*Guide créé le 26 octobre 2025 - PDF Builder Pro Vanilla JS*