# 🚀 GUIDE DE TRAVAIL - AMÉLIORATION DES TEMPLATES

**Objectif:** Faire de beaux modèles cohérents et professionnels

## 📊 État Actuel vs Cible

### Corporate ❌ → ✅
```
Actuel:
  - Manque infos client
  - Énorme gap entre table et totaux (220px vide)
  - Pas de footer
  - Pas de titre de doc

À faire:
  1. Ajouter section "customer-info"
  2. Ajouter "document-type" au top
  3. Ajouter footer avec mentions
  4. Compacter verticalement
  5. Ajouter espacements réguliers
```

**Fichier à éditer:** `plugin/templates/builtin/corporate.json`

**Changements spécifiques:**

```json
// 1. Ajouter AVANT company-info (y=0-10):
{
  "id": "document-type",
  "type": "document_type",
  "x": 380,
  "y": 10,
  "width": 400,
  "height": 40,
  "properties": {
    "title": "FACTURE",
    "fontSize": 20,
    "fontFamily": "Arial",
    "fontWeight": "bold",
    "textColor": "#28a745",
    "textAlign": "right"
  }
}

// 2. Ajouter APRÈS client-address (y=135):
{
  "id": "customer-info",
  "type": "customer_info",
  "x": 67,
  "y": 100,
  "width": 300,
  "height": 60,
  "properties": {
    "showFullName": true,
    "showAddress": true,
    "showEmail": true,
    "showPhone": false,
    "layout": "vertical",
    "fontSize": 10,
    "fontFamily": "Arial",
    "textColor": "#212529"
  }
}

// 3. Ajouter À LA FIN (après total-value):
{
  "id": "footer-mentions",
  "type": "mentions",
  "x": 67,
  "y": 1050,
  "width": 661,
  "height": 30,
  "properties": {
    "text": "Conditions générales de vente - Document généré automatiquement",
    "fontSize": 8,
    "fontFamily": "Arial",
    "textColor": "#999999",
    "textAlign": "center"
  }
}

// 4. Compacter les Y positions entre 420-480:
// Réduire: subtotal-label y: 420 → 380
//          discount-label y: 440 → 400
//          total-background y: 460 → 420
//          total-label y: 465 → 425
//          total-value y: 465 → 425
```

---

### Classic ✅ → ⭐
```
Actuel: Déjà bon!
À améliorer (mineur):
  - Réduire espacements énormes
  - Ajouter couleur d'accent
  - Border-radius subtile
```

**Fichier à éditer:** `plugin/templates/builtin/classic.json`

**Changements spécifiques:**

```json
// 1. Réduire Y positions pour compacter:
// separator-line y: 300 → 270
// items-header y: 320 → 290
// items-table y: 350 → 320
// order-totals y: 580 → 520

// 2. Ajouter accent coloré au header:
{
  "id": "header-accent",
  "type": "rectangle",
  "x": 50,
  "y": 130,
  "width": 694,
  "height": 4,
  "properties": {
    "fillColor": "#007cba",
    "strokeWidth": 0
  }
}

// 3. Modifier couleur bordures:
// Changer strokeColor de #000000 → #cccccc
// Réduire strokeWidth de 2 → 1
```

---

### Minimal ⚠️ → ✅
```
Actuel:
  - Trop de tailles de police (6)
  - Manque company-info
  - Logo minimaliste
  - Totaux incomplets

À faire:
  1. Standardiser tailles (24, 12, 10 only)
  2. Ajouter company-info
  3. Améliorer logo
  4. Ajouter HT, TVA, TTC séparés
  5. Compacter espacements
```

**Fichier à éditer:** `plugin/templates/builtin/minimal.json`

**Changements spécifiques:**

```json
// 1. Remplacer logo:
// De: "text": "L"
// À:  "text": "LOGO" avec fontSize: 14

// 2. Ajouter company-info à y=25:
{
  "id": "company-header",
  "type": "company_info",
  "x": 50,
  "y": 25,
  "width": 250,
  "height": 40,
  "properties": {
    "showFullName": true,
    "showAddress": false,
    "fontSize": 11,
    "fontFamily": "Arial",
    "textColor": "#212529"
  }
}

// 3. Standardiser tailles:
// Trouver et remplacer fontSize: 13 → 12
// Trouver et remplacer fontSize: 16 → 14
// Trouver et remplacer fontSize: 18 → 24 (garder titre)
// Trouver et remplacer fontSize: 11 → 12

// 4. Ajouter lignes pour détailler totaux:
{
  "id": "tax-label",
  "type": "text",
  "x": 50,
  "y": 660,
  "width": 400,
  "height": 20,
  "properties": {
    "text": "TVA (20%):",
    "fontSize": 12,
    "fontFamily": "Arial",
    "color": "#6c757d"
  }
}
```

---

### Modern ✅ → ⭐
```
Actuel: Bon, réduction couleurs
À améliorer:
  - Trop de couleurs (7 → 4)
  - Espacements irréguliers
  - Logo peu clair
```

**Fichier à éditer:** `plugin/templates/builtin/modern.json`

**Changements spécifiques:**

```json
// 1. Simplifier palette (chercher/remplacer):
// #4a5568 → #6c757d (everywhere)
// #495057 → #212529 (everywhere)
// Garder: #007cba, #ffc107, #6c757d, #212529

// 2. Régulariser espacements:
// Tous les Y doivent être multiples de 5 ou 10
// Remplacer y: 28 → 30
// Remplacer y: 43 → 45
// Remplacer y: 50 → 50 (OK)
// Etc.

// 3. Améliorer logo:
// De: "text": "●"
// À:  "text": "◆" ou intégrer avec company_info
```

---

## 📋 CHECKLIST FINALE

Après édition de chaque template:

### Corporate
- [ ] Client-info section ajoutée
- [ ] Document-type (FACTURE) au top
- [ ] Footer mentions ajouté
- [ ] Y positions compactées
- [ ] SVG aperçu régénéré
- [ ] Visuellement cohérent

### Classic
- [ ] Espacements réduits
- [ ] Accent coloré ajouté
- [ ] Bordures #cccccc (1px)
- [ ] SVG aperçu régénéré
- [ ] Visuellement amélioré

### Minimal
- [ ] Tailles police standardisées
- [ ] Company-info ajoutée
- [ ] Totaux détaillés (HT, TVA, TTC)
- [ ] Espacements réguliers
- [ ] SVG aperçu régénéré
- [ ] Design épuré conservé

### Modern
- [ ] Palette réduite à 4 couleurs
- [ ] Espacements réguliers (multiples de 5)
- [ ] Logo clair
- [ ] SVG aperçu régénéré
- [ ] Design moderne préservé

---

## 🔄 PROCESS APRÈS ÉDITION

Pour chaque template édité:

```bash
# 1. Éditer le JSON
vi plugin/templates/builtin/TEMPLATE.json

# 2. Régénérer l'aperçu
php plugin/generate-svg-preview.php TEMPLATE

# 3. Vérifier le rendu
# Ouvrir plugin/assets/images/templates/TEMPLATE-preview.svg

# 4. Tester en WordPress
# Afficher la galerie de templates

# 5. Déployer
cd build && ./deploy-simple.ps1
```

---

## 🎯 PRIORITÉ

1. **Corporate** - Manque de contenu, beaucoup de travail
2. **Minimal** - Besoin standardisation
3. **Classic** - Ajustements mineurs
4. **Modern** - Ajustements mineurs

