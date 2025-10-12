# 🎯 Fonctions du Système de Sélection et Redimensionnement

## Vue d'ensemble

Ce document détaille les fonctions principales du système de sélection et de redimensionnement basé sur les bordures, implémenté dans PDF Builder Pro.

## 🔧 Fonctions principales

### 1. Smart Element Selector (`useCanvasState.js`)
**Emplacement** : `src/hooks/useCanvasState.js`
**Responsabilités** :
- Gestion de l'état de sélection des éléments
- Sélection simple et multiple
- Coordination avec l'interface utilisateur

**Méthodes clés** :
```javascript
selectElement(elementId, addToSelection = false)
// Sélectionne un élément, avec option d'ajout à la sélection existante
```

### 2. Border-Based Resizer (`useResize.js`)
**Emplacement** : `src/hooks/useResize.js`
**Responsabilités** :
- Gestion des opérations de redimensionnement
- Calcul des nouvelles dimensions
- Coordination avec le drag & drop

**Méthodes clés** :
```javascript
handleResizeStart(e, direction, elementBounds)
// Initie le redimensionnement dans une direction donnée
```

### 3. Interactive Cursor Manager (CSS + React)
**Emplacement** : `src/styles/editor.css` + `CanvasElement.jsx`
**Responsabilités** :
- Affichage des curseurs contextuels
- Gestion des zones de redimensionnement
- Feedback visuel pour l'utilisateur

**Classes CSS clés** :
```css
.resize-zone-n, .resize-zone-s, .resize-zone-w, .resize-zone-e
/* Zones de redimensionnement avec curseurs directionnels */
```

### 4. Reactive Canvas Editor (`PDFCanvasEditor.jsx`)
**Emplacement** : `src/components/PDFCanvasEditor.jsx`
**Responsabilités** :
- Orchestration des interactions utilisateur
- Gestion des événements de souris
- Coordination entre sélection et redimensionnement

**Gestionnaires d'événements** :
```javascript
handleElementSelect(elementId, addToSelection)
// Gestionnaire principal de sélection d'éléments
```

### 5. Conditional Style System (`CanvasElement.jsx`)
**Emplacement** : `src/components/CanvasElement.jsx`
**Responsabilités** :
- Application des styles selon le type d'élément
- Gestion des propriétés visuelles
- Adaptation au zoom et à la sélection

**Logique de style** :
```javascript
// Styles conditionnels selon element.type
...(element.type === 'text' ? { fontSize, color, ... } : ...)
```

## 🔄 Flux de fonctionnement

### Sélection d'un élément :
1. `PDFCanvasEditor` détecte le clic
2. `handleElementSelect` appelle `useCanvasState.selectElement`
3. `CanvasElement` reçoit `isSelected = true`
4. Classe CSS `.selected` appliquée → bordures bleues visibles

### Redimensionnement :
1. Utilisateur survole une bordure de l'élément sélectionné
2. Curseur change selon la direction (n-resize, s-resize, etc.)
3. Clic déclenche `handleResizeStart` dans `useResize`
4. `useResize` gère le drag jusqu'au relâchement

## 🎨 États visuels

### État normal :
- Curseur : `grab`
- Styles : Défaut selon le type d'élément

### État sélectionné :
- Bordures : Bleues avec outline/outline-offset
- Z-index : 1000 (au-dessus des autres éléments)
- Curseur : `grab` (prêt pour déplacement)

### État redimensionnement :
- Curseur : `n-resize`, `s-resize`, `w-resize`, `e-resize`
- Zones : Visibles avec background subtil
- Drag : Suivi par `useResize`

## 🔧 Configuration

### Paramètres CSS personnalisables :
```css
--selection-border-width: 2px;
--selection-border-color: #2563eb;
--selection-border-spacing: 2px;
--resize-zone-size: 8px;
```

### Paramètres WordPress :
- `canvas_element_borders_enabled` : Active/désactive les bordures
- `canvas_resize_handles_enabled` : Ancien système de poignées (désactivé)

## 🐛 Dépannage

### Problème : Les curseurs ne changent pas
**Cause** : Zones de redimensionnement mal positionnées
**Solution** : Vérifier les calculs CSS des `.resize-zone-*`

### Problème : La sélection ne fonctionne pas
**Cause** : État `selectedElements` non synchronisé
**Solution** : Vérifier `useCanvasState.js` et les props `isSelected`

### Problème : Fond change lors de la sélection
**Cause** : Styles inline trop complexes
**Solution** : Simplifier la logique `backgroundColor` dans `CanvasElement.jsx`</content>
<parameter name="filePath">g:\wp-pdf-builder-pro\docs\SELECTION_RESIZE_SYSTEM.md