# Débug - Rendu Aperçu de l'Éditeur (Frontend)

**Date**: 30 octobre 2025  
**Problème**: L'aperçu de l'éditeur n'affiche pas correctement les éléments après chargement

---

## 🔍 Problème identifié

### Structure des données sauvegardées

**RÉEL FORMAT dans BDD** (template_data JSON):
```json
{
  "elements": [
    {
      "id": "element_xxx",
      "type": "text",
      "x": 50,         // ← Au niveau TOP
      "y": 100,        // ← Au niveau TOP
      "width": 200,    // ← Au niveau TOP
      "height": 30,    // ← Au niveau TOP
      "properties": {  // ← AUSSI dans properties !
        "color": "#000",
        "fontSize": 14
      }
    }
  ]
}
```

### Code qui charge les données

**Fichier**: `assets/js/src/pdf-canvas-vanilla-new.js` (ligne 276-307)

```javascript
loadTemplateData(templateData) {
    if (!templateData.elements) return;

    this.elements.clear();
    templateData.elements.forEach(elementData => {
        // ❌ PROBLÈME ICI
        const properties = { ...elementData.properties };  // ← properties peut être UNDEFINED
        
        // Les données x, y, width, height sont au TOP LEVEL
        // Mais on cherche dans elementData.properties !
        
        const factor = conversions[unit] || conversions['mm'];
        
        // Conversion pour x, y, width, height
        if (properties.x !== undefined) { ... }  // ← Ne fera RIEN si properties = {}
        
        this.addElement(elementData.type, properties);
    });
}
```

---

## ✅ Solution

Le problème c'est que les données sauvegardées sont structurées ainsi:

```json
{
  "elements": [
    {
      "id": "...",
      "type": "...",
      "x": 50,          // ← À fusionner avec properties
      "y": 100,
      "width": 200,
      "height": 30,
      "properties": { ... }  // ← Autres props
    }
  ]
}
```

Mais le code assume:

```json
{
  "elements": [
    {
      "id": "...",
      "type": "...",
      "properties": {
        "x": 50,       // ← Attendu ici
        "y": 100,
        "width": 200,
        "height": 30,
        ...
      }
    }
  ]
}
```

---

## 🔧 FIX À APPLIQUER

**File**: `assets/js/src/pdf-canvas-vanilla-new.js` (ligne 276-309)

**Avant**:
```javascript
loadTemplateData(templateData) {
    if (!templateData.elements) return;

    this.elements.clear();
    templateData.elements.forEach(elementData => {
        // Conversion des unités vers pixels si nécessaire
        const properties = { ...elementData.properties };
```

**Après**:
```javascript
loadTemplateData(templateData) {
    if (!templateData.elements) return;

    this.elements.clear();
    templateData.elements.forEach(elementData => {
        // ✅ FUSION: Fusionner les données du TOP LEVEL avec properties
        const properties = {
            ...elementData.properties,  // Properties spécifiques (color, fontSize, etc.)
            // Sauvegarder les positions/tailles du TOP LEVEL (car elles peuvent être là)
            x: elementData.x !== undefined ? elementData.x : elementData.properties?.x,
            y: elementData.y !== undefined ? elementData.y : elementData.properties?.y,
            width: elementData.width !== undefined ? elementData.width : elementData.properties?.width,
            height: elementData.height !== undefined ? elementData.height : elementData.properties?.height,
        };
```

---

## 📊 Conversions d'unités

**Éditorjs fonctionne en PIXELS**  
**BDD stocke en MM (par défaut)**

Facteurs de conversion (A4: 210mm = 595px):

```javascript
// De pixels vers mm (pour sauvegarde)
const saveFactor = 210 / 595;  // ≈ 0.353

// De mm vers pixels (pour chargement)
const loadFactor = 595 / 210;  // ≈ 2.833
```

---

## 🎯 Flux complet

1. **Création**: User crée élément → Position en pixels (ex: 50px)
2. **Sérialisation**: `serializeElements()` → Convertit en mm (50 × 0.353 ≈ 17.65mm)
3. **Sauvegarde**: Envoie JSON avec coords en mm à PHP
4. **Stockage BDD**: JSON sauvegardé avec coords en mm
5. **Chargement**: `loadTemplateData()` reçoit JSON en mm
6. **Conversion**: Doit reconvertir en pixels (17.65 × 2.833 ≈ 50px)
7. **Affichage**: Canvas affiche éléments à positions correctes en pixels

---

## 🧪 Test de vérification

1. Ouvrir un template dans l'éditeur
2. Ouvrir console du navigateur (F12)
3. Chercher logs concernant `loadTemplateData`
4. Vérifier:
   - Nombre d'éléments chargés
   - Valeurs de x, y, width, height après conversion
   - Si les conversions sont appliquées correctement

```javascript
// Dans loadTemplateData, ajouter logs:
console.log('[LOAD] elementData:', elementData);
console.log('[LOAD] properties après fusion:', properties);
console.log('[LOAD] conversion factor:', factor);
console.log('[LOAD] positions AVANT:', { x: properties.x, y: properties.y });
// Après conversion
console.log('[LOAD] positions APRÈS:', { 
  x: properties.x * factor, 
  y: properties.y * factor 
});
```

---

## 📝 Métadonnées

- **Unité éditeur**: Pixels (px)
- **Unité BDD**: Millimètres (mm) par défaut
- **A4 dimensions**: 210mm × 297mm = 595px × 842px
- **Facteur A4**: 210 / 595 ≈ 0.353
- **Code concerné**:
  - Chargement: `pdf-canvas-vanilla-new.js:272-309`
  - Sérialisation: `pdf-canvas-vanilla-new.js:744-775`
  - Customization: `pdf-canvas-customization.js:365-400`

