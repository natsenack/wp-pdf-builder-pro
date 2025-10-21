# Test Cases - Renderers Validation

## Objectif
Valider chaque renderer individuellement pour s'assurer que les corrections appliquées fonctionnent correctement en production.

---

## Test 1: TextRenderer
### Description
Affichage du texte simple avec dimensions et styling

### Étapes de test
1. Créer un élément `text` avec:
   - Contenu: "Bienvenue"
   - Position: (50, 50)
   - Dimensions: (200, 50)
   - Font: "Arial", 18px
   - Couleur: "#000000"

2. Vérifier le rendu:
   - [ ] Le texte s'affiche dans les dimensions correctes
   - [ ] Le texte ne déborde pas de la boîte
   - [ ] La police et couleur sont correctes
   - [ ] Les sauts de ligne sont préservés

### Résultat attendu
✅ Texte affiché correctement dans la zone définie

---

## Test 2: RectangleRenderer
### Description
Rendu des formes rectangulaires avec styling

### Étapes de test
1. Créer un élément `rectangle` avec:
   - Position: (100, 100)
   - Dimensions: (300, 150)
   - backgroundColor: "#e0e0e0"
   - borderWidth: 2
   - borderColor: "#333333"
   - opacity: 80

2. Vérifier le rendu:
   - [ ] Le rectangle s'affiche aux bonnes dimensions
   - [ ] La couleur de fond est correcte
   - [ ] La bordure est visible et au bon épaisseur
   - [ ] L'opacité est appliquée (semi-transparent)

### Résultat attendu
✅ Rectangle affiché avec tous les styles appliqués

---

## Test 3: ImageRenderer - Image Valide
### Description
Affichage d'une image avec fallback placeholder

### Étapes de test
1. Créer un élément `image` avec:
   - Position: (50, 200)
   - Dimensions: (150, 100)
   - imageUrl: "https://via.placeholder.com/150x100"
   - objectFit: "contain"

2. Vérifier le rendu:
   - [ ] L'image s'affiche correctement
   - [ ] L'image remplit la zone sans distorsion
   - [ ] Pas de placeholder affiché

### Résultat attendu
✅ Image chargée et affichée sans erreur

---

## Test 4: ImageRenderer - Erreur Chargement
### Description
Gestion d'erreur quand l'image ne peut pas être chargée

### Étapes de test
1. Créer un élément `image` avec:
   - imageUrl: "https://invalid-url.com/image.png" (URL invalide)

2. Vérifier le rendu:
   - [ ] Le placeholder s'affiche
   - [ ] Le message "Erreur de chargement" apparaît en rouge
   - [ ] Pas d'erreur JavaScript dans la console

### Résultat attendu
✅ Placeholder affichage avec message d'erreur approprié

---

## Test 5: BarcodeRenderer - Code-barres
### Description
Génération d'un vrai code-barres lisible

### Étapes de test
1. Créer un élément `barcode` avec:
   - Position: (300, 50)
   - Dimensions: (200, 80)
   - content: "123456789"
   - format: "CODE128"

2. Vérifier le rendu:
   - [ ] Un code-barres SVG s'affiche (pas juste du texte)
   - [ ] Le code-barres est scannnable (visuel)
   - [ ] Les chiffres s'affichent en bas du code

### Résultat attendu
✅ Code-barres réel généré avec JsBarcode

---

## Test 6: BarcodeRenderer - QR Code
### Description
Génération d'un vrai QR code lisible

### Étapes de test
1. Créer un élément `qrcode` avec:
   - Position: (300, 150)
   - Dimensions: (150, 150)
   - content: "https://example.com"

2. Vérifier le rendu:
   - [ ] Un QR code s'affiche (pattern carrés blanc/noir)
   - [ ] Le QR code est scannable avec un lecteur
   - [ ] Pas juste du texte "QR CODE"

### Résultat attendu
✅ QR code réel généré avec qrcode.js

---

## Test 7: DynamicTextRenderer
### Description
Affichage du texte avec variables de template interpolées

### Étapes de test
1. Créer un élément `dynamic-text` avec:
   - content: "Commande #{orderId} - Client: {customerName}"
   - Position: (50, 300)
   - Dimensions: (400, 30)

2. Fournir templateData:
   ```
   {
     orderId: "12345",
     customerName: "Jean Dupont"
   }
   ```

3. Vérifier le rendu:
   - [ ] Le texte affiche "Commande #12345 - Client: Jean Dupont"
   - [ ] Les variables sont correctement remplacées
   - [ ] Le texte ne déborde pas

### Résultat attendu
✅ Variables interpolées correctement dans le texte

---

## Test 8: ProgressBarRenderer
### Description
Affichage d'une barre de progression

### Étapes de test
1. Créer un élément `progress-bar` avec:
   - Position: (100, 400)
   - Dimensions: (300, 20)
   - progressValue: 65
   - progressColor: "#4caf50"
   - backgroundColor: "#e0e0e0"

2. Vérifier le rendu:
   - [ ] Une barre de progression s'affiche
   - [ ] La barre est remplie à 65% (environ 2/3)
   - [ ] La couleur verte est visible
   - [ ] La barre de fond grise est visible

### Résultat attendu
✅ Barre de progression affichée avec bon pourcentage

---

## Test 9: TableRenderer
### Description
Affichage d'un tableau de produits

### Étapes de test
1. Créer un élément `product_table` avec:
   - Position: (50, 450)
   - Dimensions: (500, 200)
   - headers: ["Produit", "Qté", "Prix"]
   - showHeaders: true

2. Fournir templateData:
   ```
   {
     product_table_<id>: {
       rows: [
         ["Produit A", "2", "50€"],
         ["Produit B", "1", "75€"]
       ]
     }
   }
   ```

3. Vérifier le rendu:
   - [ ] Les headers s'affichent
   - [ ] Les lignes de produits s'affichent
   - [ ] Le tableau est formaté correctement
   - [ ] Les couleurs alternées fonctionnent

### Résultat attendu
✅ Tableau affiché avec données correctes

---

## Test 10: Positionnement et Scaling
### Description
Vérifier que le scaling et le positionnement fonctionnent pour tous les éléments

### Étapes de test
1. Créer des éléments avec canvasScale = 0.5 (50%)

2. Vérifier le rendu:
   - [ ] Tous les éléments sont à 50% de leur taille
   - [ ] Les positions sont correctes (multipliées par 0.5)
   - [ ] Pas de décalage d'alignement

### Résultat attendu
✅ Scaling appliqué uniformément à tous les renderers

---

## Test 11: Transformations (Rotation & Scale)
### Description
Appliquer rotation et scale à différents éléments

### Étapes de test
1. Créer un élément avec:
   - rotation: 45
   - scale: 1.5

2. Vérifier le rendu:
   - [ ] L'élément est pivoté de 45°
   - [ ] L'élément est agrandit 1.5x
   - [ ] Le point de pivot est correct (top-left)

### Résultat attendu
✅ Transformations appliquées correctement

---

## Test 12: Shadow et Opacity
### Description
Tester les effets visuels

### Étapes de test
1. Créer un élément avec:
   - shadow: true
   - shadowOffsetX: 3
   - shadowOffsetY: 3
   - opacity: 70

2. Vérifier le rendu:
   - [ ] Une ombre est visible sous l'élément
   - [ ] L'élément est semi-transparent (70%)
   - [ ] L'ombre ne disparaît pas

### Résultat attendu
✅ Effets visuels appliqués correctement

---

## Test 13: Visibilité
### Description
Tester la propriété `visible`

### Étapes de test
1. Créer deux éléments identiques
2. Mettre le premier avec `visible: true`
3. Mettre le second avec `visible: false`

4. Vérifier le rendu:
   - [ ] Le premier élément s'affiche
   - [ ] Le second élément est caché
   - [ ] Espace blanc où le second était (pas recalcul du layout)

### Résultat attendu
✅ La propriété `visible` fonctionne correctement

---

## Test 14: Erreurs Console
### Description
Vérifier qu'il n'y a pas d'erreurs JavaScript

### Étapes de test
1. Ouvrir DevTools > Console
2. Charger la modale d'aperçu avec tous les éléments
3. Regarder la console

4. Vérifier:
   - [ ] Aucun erreur JavaScript (couleur rouge)
   - [ ] Pas de `Cannot read property of undefined`
   - [ ] Pas d'avertissements React

### Résultat attendu
✅ Console propre sans erreurs

---

## Test 15: Performance
### Description
Vérifier que le rendu reste performant

### Étapes de test
1. Créer ~50 éléments différents
2. Charger la modale d'aperçu

3. Vérifier:
   - [ ] La modale charge rapidement (< 2s)
   - [ ] Pas de lag lors du scroll
   - [ ] Performance acceptable dans DevTools

### Résultat attendu
✅ Rendu performant même avec plusieurs éléments

---

## Checklist de Validation Finale

- [ ] Test 1: TextRenderer ✅
- [ ] Test 2: RectangleRenderer ✅
- [ ] Test 3: ImageRenderer (valide) ✅
- [ ] Test 4: ImageRenderer (erreur) ✅
- [ ] Test 5: BarcodeRenderer (code-barres) ✅
- [ ] Test 6: BarcodeRenderer (QR code) ✅
- [ ] Test 7: DynamicTextRenderer ✅
- [ ] Test 8: ProgressBarRenderer ✅
- [ ] Test 9: TableRenderer ✅
- [ ] Test 10: Positionnement/Scaling ✅
- [ ] Test 11: Transformations ✅
- [ ] Test 12: Effets (shadow/opacity) ✅
- [ ] Test 13: Visibilité ✅
- [ ] Test 14: Erreurs Console ✅
- [ ] Test 15: Performance ✅

---

**Status:** Prêt pour test d'intégration
**Date:** 21 Octobre 2025
**Environnement:** Hetzner (65.108.242.181)
