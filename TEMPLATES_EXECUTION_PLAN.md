# 🎯 PLAN D'EXÉCUTION - Amélioration des Templates

## 📋 Résumé de l'analyse

✅ **Analyse complète effectuée**

Tous les templates ont été:
- Analysés visuellement et structurellement
- Évalués selon des critères de qualité
- Documentés avec recommandations spécifiques

**Documents créés:**
1. `ELEMENTS_INVENTORY.md` - Liste des éléments implémentés
2. `TEMPLATES_STYLE_ANALYSIS.md` - Analyse détaillée
3. `TEMPLATES_WORK_GUIDE.md` - Guide de travail précis
4. `TEMPLATES_ANALYSIS_SUMMARY.txt` - Résumé

---

## 🎬 Démarrage du travail

### Option 1: Via script (Windows)

```powershell
# Pour Corporate
edit-template.bat corporate

# Pour Minimal
edit-template.bat minimal

# Pour Classic
edit-template.bat classic

# Pour Modern
edit-template.bat modern
```

Le script:
1. Affiche les informations du template
2. Ouvre le fichier pour édition (manuel)
3. Attend validation
4. Régénère l'aperçu SVG
5. Affiche le résultat

### Option 2: Manuel (tous OS)

```bash
# 1. Éditer le template
vim plugin/templates/builtin/corporate.json

# 2. Régénérer l'aperçu
php plugin/generate-svg-preview.php corporate

# 3. Vérifier le rendu
# Ouvrir: plugin/assets/images/templates/corporate-preview.svg

# 4. Déployer
cd build && ./deploy-simple.ps1
```

---

## 🔨 Travail par template

### 1️⃣ CORPORATE (Priorité: HAUTE)

**État:** ⚠️ Bon mais manque de contenu

**Travail à faire:**

```json
// Fichier: plugin/templates/builtin/corporate.json

// À AJOUTER au début (après line 1, avant header-background):
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

// À REMPLACER la section "client-label":
// Transformer les éléments client-label, client-name, client-address
// en une section customer-info unique

// À MODIFIER les Y positions pour compacter:
// subtotal-label: 420 → 380
// discount-label: 440 → 400
// total-background: 460 → 420
// total-label: 465 → 425
// total-value: 465 → 425

// À AJOUTER à la fin (après total-value):
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
```

**Checklist:**
- [ ] Document-type ajouté
- [ ] Customer-info intégrée
- [ ] Y positions compactées
- [ ] Footer mentions ajouté
- [ ] Aperçu régénéré
- [ ] Visuellement cohérent

**Commandes:**
```bash
# Après édition
php plugin/generate-svg-preview.php corporate
# Vérifier: plugin/assets/images/templates/corporate-preview.svg
```

---

### 2️⃣ MINIMAL (Priorité: MOYENNE-HAUTE)

**État:** ⚠️ Bon design mais désorganisé

**Travail à faire:**

```json
// Fichier: plugin/templates/builtin/minimal.json

// À MODIFIER: Standardiser toutes les tailles de police
// Chercher et remplacer PARTOUT:
// "fontSize": 13 → "fontSize": 12
// "fontSize": 16 → "fontSize": 14
// "fontSize": 18 → "fontSize": 24 (garder titre)
// "fontSize": 11 → "fontSize": 12

// À MODIFIER: Logo
// De: "text": "L"
// À: "text": "LOGO" (ou company name)
// Aussi: "fontSize": 18 → "fontSize": 14

// À AJOUTER après le logo (y=25):
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
    "showEmail": false,
    "showPhone": false,
    "fontSize": 11,
    "fontFamily": "Arial",
    "textColor": "#212529"
  }
}

// À AJOUTER pour détailler les totaux (après subtotal-value):
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

{
  "id": "tax-value",
  "type": "dynamic-text",
  "x": 350,
  "y": 660,
  "width": 100,
  "height": 20,
  "properties": {
    "content": "{{tax}}",
    "fontSize": 12,
    "fontFamily": "Arial",
    "textColor": "#212529",
    "textAlign": "right"
  }
}
```

**Checklist:**
- [ ] Tailles police standardisées
- [ ] Logo amélioré
- [ ] Company-info ajoutée
- [ ] Totaux détaillés (HT, TVA, TTC)
- [ ] Aperçu régénéré
- [ ] Design épuré préservé

**Commandes:**
```bash
php plugin/generate-svg-preview.php minimal
```

---

### 3️⃣ CLASSIC (Priorité: BASSE)

**État:** ✅ Excellent, améliorations mineures

**Travail à faire:**

```json
// Fichier: plugin/templates/builtin/classic.json

// À AJOUTER: Accent coloré (après header-border):
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

// À MODIFIER: Réduire espacements
// separator-line: y 300 → 270
// items-header: y 320 → 290
// items-table: y 350 → 320
// order-totals: y 580 → 520
// payment-method: y 700 → 620
// due-date: y 720 → 640
// footer-border: y 780 → 700
// footer-text: y 800 → 720

// À MODIFIER: Changer toutes les bordures noires en grises:
// Chercher: "strokeColor": "#000000"
// Remplacer par: "strokeColor": "#cccccc"
// Chercher: "strokeWidth": 2
// Remplacer par: "strokeWidth": 1
```

**Checklist:**
- [ ] Accent coloré bleu ajouté
- [ ] Espacements réduits
- [ ] Bordures grises (plus subtiles)
- [ ] Aperçu régénéré
- [ ] Style formel préservé

**Commandes:**
```bash
php plugin/generate-svg-preview.php classic
```

---

### 4️⃣ MODERN (Priorité: BASSE)

**État:** ✅ Excellent, simplification de palette

**Travail à faire:**

```json
// Fichier: plugin/templates/builtin/modern.json

// À MODIFIER: Simplifier palette de couleurs
// Chercher PARTOUT et remplacer:
// "#4a5568" → "#6c757d"
// "#495057" → "#212529"
// (garder: #007cba, #ffc107, #6c757d, #212529)

// À MODIFIER: Régulariser espacements
// Tous les Y doivent être multiples de 5-10:
// y: 28 → 30
// y: 43 → 45
// y: 58 → 60
// y: 90 → 90 (OK)
// y: 110 → 110 (OK)
// y: 130 → 130 (OK)
// y: 180 → 180 (OK)
// y: 210 → 210 (OK)

// À MODIFIER: Logo
// De: "text": "●"
// À: "text": "◆" ou autre caractère
// Aussi: "fontSize": 28 → "fontSize": 24
```

**Checklist:**
- [ ] Palette simplifiée (7 → 4 couleurs)
- [ ] Espacements réguliers (multiples de 5-10)
- [ ] Logo amélioré
- [ ] Aperçu régénéré
- [ ] Design moderne préservé

**Commandes:**
```bash
php plugin/generate-svg-preview.php modern
```

---

## ✅ Processus après chaque modification

```bash
# 1. Éditer le template (dans votre éditeur)
# plugin/templates/builtin/TEMPLATE.json

# 2. Régénérer l'aperçu
cd plugin
php generate-svg-preview.php TEMPLATE

# 3. Vérifier visuellement
# Ouvrir: plugin/assets/images/templates/TEMPLATE-preview.svg
# Comparer avec l'ancien

# 4. Si OK, déployer
cd ../build
./deploy-simple.ps1

# 5. Si NOK, rééditer et recommencer à l'étape 1
```

---

## 🎬 Ordre recommandé

1. **Corporate** (+ besoin de travail)
   ```bash
   # 30-45 minutes
   ```

2. **Minimal** (+ travail de standardisation)
   ```bash
   # 20-30 minutes
   ```

3. **Classic** (- travail mineur)
   ```bash
   # 10-15 minutes
   ```

4. **Modern** (- travail mineur)
   ```bash
   # 10-15 minutes
   ```

**Temps total estimé: 70-105 minutes**

---

## 📊 Validation finale

Après avoir édité tous les templates:

- [ ] Chaque template a un document-type
- [ ] Chaque template a company_info + customer_info
- [ ] Chaque template a un tableau de produits
- [ ] Chaque template a des totaux détaillés
- [ ] Chaque template a un footer
- [ ] Spacing régulier et cohérent
- [ ] Couleurs harmonisées
- [ ] Polices standardisées
- [ ] Aperçus SVG générés
- [ ] Déployés sur FTP

---

## 🚀 Déploiement final

Une fois tous les templates modifiés:

```powershell
cd d:\wp-pdf-builder-pro\build
.\deploy-simple.ps1
```

Cela va:
- Détecter tous les fichiers modifiés
- Les envoyer sur le serveur FTP
- Créer un commit Git
- Créer un tag de version

---

## 📝 Notes importantes

- **Sauvegardez** avant de commencer
- **Testez** l'aperçu après chaque modification
- **Comparez** visuellement avant et après
- **Ne modifiez pas** les canvasWidth/canvasHeight
- **Vérifiez** la validité JSON (pas de virgules en trop)
- **Respectez** l'indentation (2 espaces)

---

## 💡 En cas de problème

Si la génération d'aperçu échoue:

```bash
# 1. Vérifier la validité JSON
php -l plugin/templates/builtin/TEMPLATE.json

# 2. Vérifier les logs PHP
php generate-svg-preview.php TEMPLATE

# 3. Restaurer depuis git si problème grave
git checkout plugin/templates/builtin/TEMPLATE.json
```

---

**Bon travail! 🎨✨**

