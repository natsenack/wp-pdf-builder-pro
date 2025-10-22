# 📋 Analyse des Propriétés des Éléments - Phase 2.1.2

## 🎯 Objectif
Analyser en détail les propriétés de chaque type d'élément pour comprendre leur structure, valeurs par défaut, limites et stockage JSON.

## 📊 Structure des Propriétés

### 🏗️ Architecture Générale

Les propriétés sont organisées en **4 catégories principales** :
- **Appearance** : Couleurs, typographie, bordures, effets visuels
- **Layout** : Position, dimensions, transformation, calques
- **Content** : Contenu spécifique à chaque type d'élément
- **Effects** : Opacité, ombres, filtres

### 📋 Propriétés Communes à Tous les Éléments

#### Position & Dimensions (Layout)
- `x`, `y` : Position en pixels (number, 0-∞)
- `width`, `height` : Dimensions en pixels (number, 1-∞)
- `rotation` : Rotation en degrés (number, 0-360)
- `zIndex` : Ordre d'affichage (number, 0-∞)

#### Apparence (Appearance)
- `backgroundColor` : Couleur de fond (string, hex/rgb)
- `borderWidth` : Épaisseur bordure (number, 0-20px)
- `borderColor` : Couleur bordure (string, hex/rgb)
- `borderRadius` : Rayon bordure (number, 0-50px)
- `opacity` : Transparence (number, 0-1)

#### Effets (Effects)
- `shadow` : Ombre activée (boolean)
- `shadowColor` : Couleur ombre (string, hex/rgb)
- `shadowOffsetX`, `shadowOffsetY` : Décalage ombre (number, -50/+50px)

---

## 🔍 Analyse Détaillée par Élément

### 1. 📋 product_table (Tableau Produits)

#### Propriétés Spécifiques (Content)
```javascript
{
  showHeaders: true,        // Afficher en-têtes (boolean)
  showBorders: false,       // Afficher bordures (boolean)
  headers: ['Produit', 'Qté', 'Prix'], // En-têtes colonnes (array)
  dataSource: 'order_items', // Source données (string)
  tableStyle: 'default',    // Style tableau (string)
  columns: {                // Colonnes à afficher (object)
    image: true,            // Image produit (boolean)
    name: true,             // Nom produit (boolean)
    sku: false,             // SKU produit (boolean)
    quantity: true,         // Quantité (boolean)
    price: true,            // Prix (boolean)
    total: true             // Total (boolean)
  },
  showSubtotal: false,      // Afficher sous-total (boolean)
  showShipping: true,       // Afficher frais port (boolean)
  showTaxes: true,          // Afficher taxes (boolean)
  showDiscount: false,      // Afficher remises (boolean)
  showTotal: false          // Afficher total (boolean)
}
```

#### Limites & Contraintes
- **headers** : Array de strings, 1-10 éléments
- **columns** : Objet avec propriétés boolean uniquement
- **tableStyle** : Valeurs acceptées : 'default', 'minimal', 'bordered'

#### Propriétés Dynamiques vs Statiques
- **Dynamiques** : Colonnes (dépendent des données WooCommerce)
- **Statiques** : Styles, en-têtes, options d'affichage

---

### 2. 👤 customer_info (Fiche Client)

#### Propriétés Spécifiques (Content)
```javascript
{
  showHeaders: true,        // Afficher en-têtes (boolean)
  showBorders: false,       // Afficher bordures (boolean)
  fields: ['name', 'email', 'phone', 'address', 'company', 'vat', 'siret'], // Champs à afficher (array)
  layout: 'vertical',       // Disposition (string: 'vertical'|'horizontal')
  showLabels: true,         // Afficher libellés (boolean)
  labelStyle: 'bold',       // Style libellés (string: 'normal'|'bold'|'uppercase')
  spacing: 8                // Espacement (number, 0-50px)
}
```

#### Propriétés de Style (Appearance - Supplémentaires)
```javascript
{
  fontSize: 12,             // Taille police (number, 8-72px)
  fontFamily: 'Arial',      // Police (string)
  fontWeight: 'normal',     // Graisse (string: 'normal'|'bold')
  textAlign: 'left',        // Alignement (string: 'left'|'center'|'right')
  color: '#333333'          // Couleur texte (string, hex)
}
```

#### Limites & Contraintes
- **fields** : Array limité aux champs disponibles : 'name', 'email', 'phone', 'address', 'company', 'vat', 'siret'
- **layout** : Seulement 'vertical' ou 'horizontal'
- **labelStyle** : 'normal', 'bold', 'uppercase'
- **spacing** : 0-50px

#### Propriétés Dynamiques vs Statiques
- **Dynamiques** : Contenu des champs (rempli depuis WooCommerce)
- **Statiques** : Layout, styles, options d'affichage

---

### 3. 🏢 company_logo (Logo Entreprise)

#### Propriétés Spécifiques (Content)
```javascript
{
  imageUrl: '',             // URL de l'image (string)
  width: 150,               // Largeur (number, 10-1000px)
  height: 80,               // Hauteur (number, 10-1000px)
  alignment: 'left',        // Alignement (string: 'left'|'center'|'right')
  fit: 'contain',           // Ajustement (string: 'contain'|'cover'|'fill')
  showBorder: false,        // Afficher bordure (boolean)
  borderRadius: 0           // Rayon bordure (number, 0-100px)
}
```

#### Limites & Contraintes
- **width/height** : 10-1000px
- **alignment** : 'left', 'center', 'right'
- **fit** : 'contain', 'cover', 'fill'
- **borderRadius** : 0-100px (lié à showBorder)

#### Propriétés Dynamiques vs Statiques
- **Dynamiques** : imageUrl (chargé depuis paramètres WooCommerce)
- **Statiques** : Dimensions, alignement, style

---

### 4. [D] company_info (Informations Entreprise)

#### Propriétés Spécifiques (Content)
```javascript
{
  showHeaders: false,       // Afficher en-têtes (boolean)
  showBorders: false,       // Afficher bordures (boolean)
  fields: ['name', 'address', 'phone', 'email', 'website', 'vat', 'rcs', 'siret'], // Champs entreprise (array)
  layout: 'vertical',       // Disposition (string)
  showLabels: false,        // Afficher libellés (boolean)
  labelStyle: 'normal',     // Style libellés (string)
  spacing: 4                // Espacement (number, 0-50px)
}
```

#### Propriétés de Style (Appearance)
```javascript
{
  fontSize: 12,             // Taille police (number, 8-72px)
  fontFamily: 'Arial',      // Police (string)
  fontWeight: 'normal',     // Graisse (string)
  textAlign: 'left'         // Alignement (string)
}
```

#### Limites & Contraintes
- **fields** : Array limité aux champs entreprise disponibles
- **layout** : 'vertical' ou 'horizontal'
- **spacing** : 0-50px

#### Propriétés Dynamiques vs Statiques
- **Dynamiques** : Contenu des champs (depuis paramètres WooCommerce)
- **Statiques** : Layout, styles d'affichage

---

### 5. 🔢 order_number (Numéro de Commande)

#### Propriétés Spécifiques (Content)
```javascript
{
  showHeaders: false,       // Afficher en-têtes (boolean)
  showBorders: false,       // Afficher bordures (boolean)
  format: 'Commande #{order_number} - {order_date}', // Format d'affichage (string)
  showLabel: true,          // Afficher libellé (boolean)
  labelText: 'N° de commande:' // Texte libellé (string)
}
```

#### Propriétés de Style (Appearance)
```javascript
{
  fontSize: 14,             // Taille police (number, 8-72px)
  fontFamily: 'Arial',      // Police (string)
  fontWeight: 'bold',       // Graisse (string)
  textAlign: 'right',       // Alignement (string)
  color: '#333333'          // Couleur texte (string)
}
```

#### Limites & Contraintes
- **format** : String avec variables {order_number}, {order_date}
- **fontSize** : 8-72px
- **textAlign** : 'left', 'center', 'right'

#### Propriétés Dynamiques vs Statiques
- **Dynamiques** : format (rempli avec variables réelles)
- **Statiques** : Styles, libellé

---

### 6. � dynamic-text (Texte Dynamique)

#### Propriétés Spécifiques (Content)
```javascript
{
  template: 'total_only',   // Template prédéfini (string)
  customContent: '{{order_total}} €', // Contenu personnalisé (string)
  variables: []             // Variables utilisées (array - calculé automatiquement)
}
```

#### Propriétés de Style (Appearance)
```javascript
{
  fontSize: 14,             // Taille police (number, 8-72px)
  fontFamily: 'Arial',      // Police (string)
  fontWeight: 'normal',     // Graisse (string)
  textAlign: 'left',        // Alignement (string)
  color: '#333333',         // Couleur texte (string)
  lineHeight: 1.2,          // Interligne (number, 0.5-3)
  letterSpacing: 0          // Espacement lettres (number, -5/+5px)
}
```

#### Templates Prédéfinis Disponibles
- `total_only` : Affiche seulement le total
- `order_info` : Informations commande
- `customer_info` : Infos client
- `full_header` : En-tête complet
- `invoice_header` : En-tête facture
- `order_summary` : Résumé commande
- `payment_info` : Infos paiement
- `thank_you` : Message remerciement
- Et plus...

#### Limites & Contraintes
- **template** : Liste prédéfinie de templates
- **customContent** : String avec variables {{variable}}
- **lineHeight** : 0.5-3
- **letterSpacing** : -5/+5px

#### Propriétés Dynamiques vs Statiques
- **Dynamiques** : customContent (rempli avec variables WooCommerce)
- **Statiques** : Template choisi, styles

---

### 7. 📄 mentions (Mentions légales)

#### Propriétés Spécifiques (Content)
```javascript
{
  showEmail: true,          // Afficher email (boolean)
  showPhone: true,          // Afficher téléphone (boolean)
  showSiret: true,          // Afficher SIRET (boolean)
  showVat: false,           // Afficher TVA (boolean)
  showAddress: false,       // Afficher adresse (boolean)
  showWebsite: false,       // Afficher site web (boolean)
  showCustomText: false,    // Afficher texte personnalisé (boolean)
  customText: '',           // Texte personnalisé (string)
  separator: ' • ',         // Séparateur (string)
  layout: 'horizontal'      // Disposition (string: 'horizontal'|'vertical')
}
```

#### Propriétés de Style (Appearance)
```javascript
{
  fontSize: 8,              // Taille police (number, 6-24px)
  fontFamily: 'Arial',      // Police (string)
  fontWeight: 'normal',     // Graisse (string)
  textAlign: 'center',      // Alignement (string)
  color: '#666666',         // Couleur texte (string)
  lineHeight: 1.2           // Interligne (number, 0.8-2)
}
```

#### Limites & Contraintes
- **fontSize** : 6-24px (plus petit que les autres éléments)
- **layout** : 'horizontal' ou 'vertical'
- **lineHeight** : 0.8-2 (plus serré)
- **separator** : String personnalisable

#### Propriétés Dynamiques vs Statiques
- **Dynamiques** : Contenu des mentions (depuis paramètres WooCommerce)
- **Statiques** : Options d'affichage, séparateur, layout

---

## 💾 Stockage JSON

### Format Général d'un Élément
```json
{
  "id": "element_123",
  "type": "product_table",
  "x": 50,
  "y": 100,
  "width": 400,
  "height": 200,
  "rotation": 0,
  "zIndex": 1,
  "backgroundColor": "transparent",
  "borderWidth": 0,
  "borderColor": "transparent",
  "borderRadius": 0,
  "opacity": 1,
  "shadow": false,
  "shadowColor": "#000000",
  "shadowOffsetX": 0,
  "shadowOffsetY": 0,
  // Propriétés spécifiques à chaque type...
  "showHeaders": true,
  "showBorders": false,
  "columns": {
    "image": true,
    "name": true,
    "quantity": true,
    "price": true,
    "total": true
  }
}
```

### Stockage WordPress
- **Meta key** : `pdf_builder_elements`
- **Format** : Array d'objets JSON
- **Sauvegarde** : Automatique lors de l'édition
- **Chargement** : Depuis `get_post_meta($template_id, 'pdf_builder_elements', true)`

---

## 🔍 Analyse Complète - Résumé

| Élément | Propriétés Totales | Propriétés Spécifiques | Dynamiques | Statiques |
|---------|-------------------|----------------------|------------|-----------|
| product_table | 25+ | 12 | Colonnes, données | Styles, layout |
| customer_info | 20+ | 7 | Contenu champs | Layout, styles |
| company_logo | 15+ | 7 | Image URL | Dimensions, style |
| company_info | 18+ | 7 | Contenu champs | Layout, styles |
| order_number | 15+ | 5 | Format avec variables | Styles, libellé |
| dynamic-text | 20+ | 3 | Contenu avec variables | Template, styles |
| mentions | 20+ | 10 | Contenu mentions | Options affichage |

**✅ Analyse terminée** - Toutes les propriétés documentées avec valeurs par défaut, limites et classification dynamique/statique.