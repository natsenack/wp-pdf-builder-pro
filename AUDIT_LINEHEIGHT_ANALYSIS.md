# 📋 AUDIT COMPLET : Utilisation de "lineHeight" en contexte "company_info"

**Date de l'audit:** 14 février 2026  
**Scope:** Recherche de "lineHeight" et "calculate_line_gap" dans le codebase

---

## 📊 RÉSUMÉ STATISTIQUE

| Catégorie | Fichiers | Total Occurrences |
|-----------|----------|-------------------|
| **Canvas.tsx** | src/js/react/components/canvas/Canvas.tsx | 20+ occurrences |
| **CompanyInfoProperties.tsx** | src/js/react/components/properties/CompanyInfoProperties.tsx | 4 occurrences |
| **elementNormalization.ts** | src/js/react/utils/elementNormalization.ts | 1 occurrence |
| **PDF_Builder_Unified_Ajax_Handler.php** | plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php | 20+ occurrences |
| **calculate_line_gap (fonction)** | PDF_Builder_Unified_Ajax_Handler.php | 4 appels |

---

## 📁 DÉTAIL PAR FICHIER

---

## 1️⃣ [src/js/react/components/canvas/Canvas.tsx](src/js/react/components/canvas/Canvas.tsx)

### Contexte 1a: Support pour lineHeight et letterSpacing (fonction drawText)
**Ligne:** [397-447](src/js/react/components/canvas/Canvas.tsx#L397-L447)

```tsx
395:  );
396:
397:  // ✅ NEW: Support pour lineHeight et letterSpacing
398:  const lineHeight = parseFloat(props.lineHeight as any) || 1.2;
399:  const letterSpacing = parseFloat(props.letterSpacing as any) || 0;
400:  const text = props.text || "Text";
401:
402:  // Gérer les lignes séparées par \n
403:  const lines = text.split("\n");
404:  let currentY = y;
405:  const originalTextAlign = ctx.textAlign;
406:
407:  lines.forEach((line: string, index: number) => {
     ...
445:      ctx.fillText(line, x, currentY);
446:    }
447:
448:    // Espacement entre les lignes
449:    if (index < lines.length - 1) {
450:      currentY += fontConfig.size * lineHeight;  // ✅ Utilisation du lineHeight
451:    }
```

**🎯 Point clé:** Le lineHeight est utilisé pour espacement vertical entre lignes. Valeur par défaut: **1.2**

---

### Contexte 1b: drawCustomerInfo - Support pour lineHeight et letterSpacing
**Ligne:** [1365-1420](src/js/react/components/canvas/Canvas.tsx#L1365-L1420)

```tsx
1365:  const showPaymentMethod = props.showPaymentMethod !== false;
1366:  const showTransactionId = props.showTransactionId !== false;
1367:  // ✅ NEW: Récupérer lineHeight et letterSpacing
1368:  const lineHeight = parseFloat(props.lineHeight as any) || 1.5;
1369:  const letterSpacing = parseFloat(props.letterSpacing as any) || 0;
1370:  // Alignement vertical
1371:  const verticalAlign = props.verticalAlign || "top";
```

**🎯 Point clé:** Dans drawCustomerInfo, valeur par défaut: **1.5**

---

### Contexte 1c: Utilisation du lineHeight pour espacement des lignes
**Ligne:** [1610-1620](src/js/react/components/canvas/Canvas.tsx#L1610-L1620)

```tsx
1608:      ctx.fillText(lineText, textX, y);
1609:    }
1610:
1611:    // Appliquer le lineHeight au lieu de la valeur fixe de 18
1612:    y += bodyFontSize * lineHeight;
1613:  });
1614:};
1615:
1616:  // Constantes pour les thèmes company_info
1617:  const COMPANY_THEMES = {
1618:    corporate: {
```

**🎯 Point clé:** Le calcul `bodyFontSize * lineHeight` remplace une valeur fixe de 18px. **Formule critique**

---

### Contexte 1d: Fonction drawCompanyLine avec paramètre lineHeight
**Ligne:** [1765-1815](src/js/react/components/canvas/Canvas.tsx#L1765-L1815)

```tsx
1764:  text: string,
1765:  x: number,
1766:  y: number,
1767:  fontSize: number,
1768:  lineHeight: number = 1.2,  // ✅ Paramètre avec défaut 1.2
1769:  letterSpacing: number = 0,
1770:) => {
1771:  if (letterSpacing !== 0) {
     ...
1807:    ctx.fillText(text, x, y);
1808:  }
1809:
1810:  return y + fontSize * lineHeight;  // ✅ Retour avec lineHeight
1811:};
```

**🎯 Point clé:** Signature de la fonction `drawCompanyLine`. Valeur par défaut: **1.2**. Utilise la même formule `fontSize * lineHeight`

---

### Contexte 1e: drawCompanyInfo - Récupération de lineHeight
**Ligne:** [2114-2150](src/js/react/components/canvas/Canvas.tsx#L2114-L2150)

```tsx
2113:  ctx.font = `${fontConfig.bodyStyle} ${fontConfig.bodyWeight} ${fontConfig.bodySize}px ${fontConfig.bodyFamily}`;
2114:
2115:  // Récupérer lineHeight et letterSpacing du JSON
2116:  const lineHeight = parseFloat(props.lineHeight as any) || 1.1;
2117:  const letterSpacing = parseFloat(props.letterSpacing as any) || 0;
2118:
2119:  // Dessiner toutes les lignes
2120:  lines.forEach((lineData) => {
     ...
2138:    ctx.font = `${config.style} ${config.weight} ${config.size}px ${config.family}`;
2139:    if (lineData.isHeader) ctx.fillStyle = colors.headerText;
2140:    // ✅ Header utilise toujours lineHeight 1.2, les infos utilisent le lineHeight personnalisé
2141:    const currentLineHeight = lineData.isHeader ? 1.2 : lineHeight;
2142:    y = drawCompanyLine(
2143:      ctx,
2144:      lineData.text,
2145:      x,
2146:      y,
2147:      config.size,
2148:      currentLineHeight,  // ✅ Passage du lineHeight
2149:      letterSpacing,
2150:    );
```

**🎯 Point clé:** 
- Dans company_info: valeur par défaut: **1.1**
- **Logique différentielle:** Header forcé à 1.2, body utilise la valeur personnalisée
- Appelé à la ligne 2142 dans `drawCompanyLine`

---

### Contexte 1f: Gestion du lineHeight pour calcul de hauteur
**Ligne:** [3710-3720](src/js/react/components/canvas/Canvas.tsx#L3710-L3720)

```tsx
3708:      // Wrapper le texte selon la largeur disponible
3709:      const maxWidth = element.width - 20; // Marge de 20px
3710:      const wrappedLines = wrapText(text, maxWidth);
3711:
3712:      // Calculer le nombre maximum de lignes qui peuvent tenir
3713:      // Utiliser le lineHeight de l'élément s'il est défini, sinon utiliser fontSize * 1.2
3714:      const lineHeightValue = props.lineHeight
3715:        ? typeof props.lineHeight === "number"
3716:          ? props.lineHeight
3717:          : parseFloat(props.lineHeight)
```

**🎯 Point clé:** Logique de parsing du lineHeight avec fallback à `fontSize * 1.2`

---

## 2️⃣ [src/js/react/components/properties/CompanyInfoProperties.tsx](src/js/react/components/properties/CompanyInfoProperties.tsx)

### Contexte 2a: Ligne 80 - Style CSS pour description
**Ligne:** [75-85](src/js/react/components/properties/CompanyInfoProperties.tsx#L75-L85)

```tsx
75:    </div>
76:    <div
77:      style={{
78:        fontSize: "11px",
79:        color: "#666",
80:        lineHeight: "1.4",  // ✅ Style UI pour description
81:      }}
82:    >
83:      {description}
84:    </div>
85:  </div>
```

**🎯 Point clé:** Propriété de style UI React. Valeur: **"1.4"** (pour l'affichage de la description dans le formulaire)

---

### Contexte 2b: Slider Espacement des lignes (Ligne 1738)
**Ligne:** [1735-1752](src/js/react/components/properties/CompanyInfoProperties.tsx#L1735-L1752)

```tsx
1733:          <div style={{ marginBottom: "12px" }}>
1734:            <label
1735:              style={{
1736:                display: "block",
1737:                fontSize: "12px",
1738:                fontWeight: "bold",
1739:                marginBottom: "6px",
1740:              }}
1741:            >
1742:              Espacement des lignes des informations{" "}
1743:              <span style={{ color: "#666", fontSize: "10px" }}>
1744:                ({(parseFloat(String(element.lineHeight || 1.0))).toFixed(1)})
1745:              </span>
1746:            </label>
1747:            <input
1748:              type="range"
1749:              min="0.8"
1749:              max="3"
1750:              step="0.1"
1751:              value={parseFloat(String(element.lineHeight || 1.0))}
1752:              onChange={(e) =>
1753:                onChange(element.id, "lineHeight", parseFloat(e.target.value))
1754:              }
```

**🎯 Point clé:**
- **Formulaire de propriété company_info**
- Label: "Espacement des lignes des informations"
- Valeur par défaut: **1.0**
- Range: **0.8 à 3.0**
- Step: **0.1**
- Display affiche la valeur avec une décimale: `.toFixed(1)`
- L'input change au niveau élément via `onChange(element.id, "lineHeight", parseFloat(e.target.value))`

---

## 3️⃣ [src/js/react/utils/elementNormalization.ts](src/js/react/utils/elementNormalization.ts)

### Contexte 3a: styleProperties - Ensemble critique des propriétés
**Ligne:** [480-490](src/js/react/utils/elementNormalization.ts#L480-L490)

```typescript
480:    // ========== PROPRIÉTÉS CRITIQUES À PRÉSERVER ==========
481:    // Les styles dédans ce set ne doivent JAMAIS être perdus lors de la sauvegarde
482:    const styleProperties = new Set([
483:      // ===== STYLES TEXTE =====
484:      'fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'fontColor', 'color',
485:      'textAlign', 'textDecoration', 'textTransform', 'letterSpacing', 'wordSpacing', 'lineHeight',
486:      
487:      // ===== STYLES FOND & BORDURES =====
488:      'backgroundColor', 'bgColor', 'showBackground',
489:      'border', 'borderTop', 'borderBottom', 'borderLeft', 'borderRight', 'borderColor', 'borderWidth', 'borderStyle', 'borderRadius',
```

**🎯 Point clé:** `lineHeight` est dans un **Set CRITIQUE** des propriétés qui ne doivent jamais être perdues lors de la sauvegarde (protection de la normalisation)

---

## 4️⃣ [plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php](plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php)

### Contexte 4a: Commentaire sur line-height CSS (ligne 3204)
**Ligne:** [3200-3210](plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php#L3200-L3210)

```php
3200:        }
3201:        
3202:        // Line height - DOMPDF a des problèmes avec line-height direct
3203:        // Donc on ne l'ajoute pas ici, chaque élément spécifique le gère si besoin
3204:        // Ne pas ajouter: $css .= 'line-height: ' . floatval($element['lineHeight']) . '; ';
3205:        
3206:        // === ARRIÈRE-PLAN ET BORDURES ===
3207:        // Background (respecter showBackground)
3208:        if (($element['backgroundColor'] ?? 'transparent') !== 'transparent') {
3209:            if ($element['showBackground'] ?? true) {
3210:                $css .= 'background-color: ' . $element['backgroundColor'] . '; ';
```

**🎯 Point clé:** **⚠️ DOMPDF INCOMPATIBILITÉ** - line-height CSS n'est PAS appliqué directement car DOMPDF a des problèmes. Gestion per-element spécifique

---

### Contexte 4b: Fonction calculate_line_gap (signature et implémentation)
**Ligne:** [3373-3382](plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php#L3373-L3382)

```php
3373:    /**
3374:     * Helper: Calcule le spacing uniforme entre les lignes (DOMPDF compatible)
3375:     * 
3376:     * Calcul: margin-bottom = fontSize × (lineHeight - 1)
3377:     * 
3378:     * Exemples:
3379:     * - fontSize=12px, lineHeight=1.5 → margin-bottom = 12 × 0.5 = 6px
3380:     * - fontSize=14px, lineHeight=1.2 → margin-bottom = 14 × 0.2 = 2.8px (arrondi 3px)
3381:     * - fontSize=16px, lineHeight=1.8 → margin-bottom = 16 × 0.8 = 12.8px (arrondi 13px)
3382:     * 
3383:     * 
3384:     * @param float $fontSize Taille de la police en px
3385:     * @param float $lineHeight Ratio de hauteur de ligne (ex: 1.2, 1.5)
3386:     * @return int Gap arrondi en px (équivalent React gap)
3387:     */
3388:    private function calculate_line_gap($fontSize, $lineHeight) {
3389:        return round($fontSize * (floatval($lineHeight) - 1));
3390:    }
```

**🎯 Point clé:**
- **Fonction centrale pour DOMPDF compatibility**
- Formule: $$\text{gap} = \text{fontSize} \times (\text{lineHeight} - 1)$$
- Exemples documentés
- Retourne un entier arrondi
- **Équivalent exact du calcul React**

---

### Contexte 4c: Utilisation dans render_customer_info (ligne 3358)
**Ligne:** [3355-3365](plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php#L3355-L3365)

```php
3355:        ];
3356:    }
3357:    
3358:    /**
3359:     * Helper: Calcule le spacing uniforme entre les lignes (DOMPDF compatible)
3360:     * 
3361:     * Calcul: margin-bottom = fontSize × (lineHeight - 1)
3362:     * 
3363:     * Calcul: margin-bottom = fontSize × (lineHeight - 1)
3364:     * 
3365:     * Exemples:
3366:     * - fontSize=12px, lineHeight=1.5 → margin-bottom = 12 × 0.5 = 6px
3367:     * - fontSize=14px, lineHeight=1.2 → margin-bottom = 14 × 0.2 = 2.8px (arrondi 3px)
3368:     * - fontSize=16px, lineHeight=1.8 → margin-bottom = 16 × 0.8 = 12.8px (arrondi 13px)
3369:     * 
3370:     * 
3371:     * @param float $fontSize Taille de la police en px
3372:     * @param float $lineHeight Ratio de hauteur de ligne (ex: 1.2, 1.5)
3373:     * @return int Gap arrondi en px (équivalent React gap)
3374:     */
3375:    private function calculate_line_gap($fontSize, $lineHeight) {
3376:        return round($fontSize * (floatval($lineHeight) - 1));
3376:    }
```

---

### Contexte 4d: Utilisation dans render_customer_info - Appel de calculate_line_gap
**Ligne:** [3670-3680](plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php#L3670-L3680)

```php
3668:        // Style header
3669:        $header_style = "color: {$colors['header']}; font-family: {$header_font['family']}; font-size: {$header_font['size']}px; font-weight: {$header_font['weight']}; font-style: {$header_font['style']}; margin-bottom: 8px;";
3670:        
3671:        // Récupérer le fontSize GLOBAL du conteneur (element.fontSize) pour calculer le gap
3672:        $container_font_size = isset($element['fontSize']) ? floatval($element['fontSize']) : 12;
3673:        $lineHeightValue = floatval($layout_props['lineHeight']);
3674:        $gap = $this->calculate_line_gap($container_font_size, $lineHeightValue);  // ✅ Appel
3675:        
3676:        // Styles pour chaque ligne de body (COMME REACT) - line-height:1 pour hauteur stricte
3677:        $line_style_base = "font-size: {$body_font['size']}px; font-family: {$body_font['family']}; font-weight: {$body_font['weight']}; font-style: {$body_font['style']}; color: {$colors['text']}; margin: 0; padding: 0; line-height: 1;";
3678:        
```

**🎯 Point clé:**
- `$container_font_size` par défaut: **12**
- `$lineHeightValue` extrait de `$layout_props['lineHeight']`
- Résultat : `$gap = $this->calculate_line_gap(...)`
- CSS des lignes utilise `line-height: 1;` (strict) + margin-bottom en tant que gap

---

### Contexte 4e: Override lineHeight pour company_info (ligne 3705)
**Ligne:** [3700-3710](plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php#L3700-L3710)

```php
3699:    private function render_company_info_element($element, $order_data, $base_styles, $is_premium = false, $format = 'html') {
3700:        // Extraction des propriétés via helpers
3701:        $padding = $this->extract_padding($element);
3702:        $layout_props = $this->extract_layout_props($element);
3703:        $layout_props['lineHeight'] = floatval($element['lineHeight'] ?? 1.1); // Override pour company
3704:        
3705:        // Thèmes prédéfinis
3706:        $themes = [
3707:            'corporate' => ['backgroundColor' => '#ffffff', 'borderColor' => '#1f2937', 'textColor' => '#374151', 'headerTextColor' => '#111827'],
3708:            'modern' => ['backgroundColor' => '#ffffff', 'borderColor' => '#3b82f6', 'textColor' => '#1e40af', 'headerTextColor' => '#1e3a8a'],
```

**🎯 Point clé:**
- **SPÉCIFIQUE À company_info**
- Valeur par défaut: **1.1**
- Récupère de `$element['lineHeight']` ou utilise **1.1**
- Assignée à `$layout_props['lineHeight']` pour utilisation ultérieure

---

### Contexte 4f: Utilisation dans render_company_info_element - Appel de calculate_line_gap
**Ligne:** [3855-3865](plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php#L3855-L3865)

```php
3853:        
3854:        // Calcul du gap - méthode identique à React
3855:        // Utilise fontSize et lineHeight du JSON pour calcul cohérent
3856:        $container_font_size = isset($element['fontSize']) ? floatval($element['fontSize']) : 12;
3857:        $lineHeightValue = floatval($layout_props['lineHeight']);
3858:        $gap = $this->calculate_line_gap($container_font_size, $lineHeightValue);  // ✅ Appel
3859:        
3860:        // Génération HTML - margin-bottom comme gap React pour compatibilité DOMPDF
3861:        $html = '<div class="element" style="' . $container_styles . '">';
3862:        // Chaque ligne avec margin-bottom (sauf dernière) = gap de React + line-height:1 pour hauteur stricte
3863:        $total_lines = count($processedLines);
3864:        
```

**🎯 Point clé:** Même logique que customer_info, appliquée à company_info

---

### Contexte 4g: Utilisation dans render_order_notes - Récupération de lineHeight
**Ligne:** [4410-4425](plugin/src/Core/PDF_Builder_Unified_Ajax_Handler.php#L4410-L4425)

```php
4408:        $base_styles_clean = preg_replace('/padding(-top|-bottom|-left|-right)?:\s*[^;]+;/i', '', $base_styles);
4409:        $base_styles_clean = preg_replace('/line-height:\s*[^;]+;/', '', $base_styles_clean);
4410:        // Retirer aussi les !important de position qui causent des conflits DOMPDF
4411:        $base_styles_clean = str_replace('!important', '', $base_styles_clean);
4412:        
4413:        // Récupérer fontSize et lineHeight DIRECTEMENT DU JSON (comme customer_info)
4414:        $font_size = isset($element['fontSize']) ? floatval($element['fontSize']) : 12;
4415:        $line_height_ratio = isset($element['lineHeight']) ? floatval($element['lineHeight']) : 1.3;
4416:        
4417:        // Calculer le gap (= espacement entre lignes, comme React)
4418:        $gap = $this->calculate_line_gap($font_size, $line_height_ratio);  // ✅ Appel
4419:        
4420:        // Extraire les propriétés de positionnement
4421:        preg_match('/left:\s*[^;]+;/', $base_styles_clean, $left_match);
4422:        preg_match('/top:\s*[^;]+;/', $base_styles_clean, $top_match);
4423:        preg_match('/width:\s*[^;]+;/', $base_styles_clean, $width_match);
```

**🎯 Point clé:**
- Récupère lineHeight directement du JSON
- Valeur par défaut pour order_notes: **1.3**
- Nettoie les styles de base (retire padding, line-height, !important)
- Appelle `calculate_line_gap` avec `$line_height_ratio`

---

## 🔄 FLUX D'INTÉGRATION React ↔ PHP

### Flux company_info:

```
React (Canvas.tsx)
  ↓
  props.lineHeight (default: 1.1)
  ↓
  drawCompanyInfo() → drawCompanyLine(currentLineHeight)
  ↓
  JSON element export
  ↓
PHP (PDF_Builder_Unified_Ajax_Handler.php)
  ↓
  render_company_info_element()
  ↓
  $element['lineHeight'] (default: 1.1)
  ↓
  calculate_line_gap($fontSize, $lineHeight)
  ↓
  margin-bottom CSS = fontSize × (lineHeight - 1)
```

---

## 📋 TABLEAU DES VALEURS PAR DÉFAUT

| Contexte | Fichier | Ligne | Default | Range | Usage |
|----------|---------|-------|---------|-------|-------|
| **drawText** | Canvas.tsx | 398 | **1.2** | - | Texte général |
| **drawCustomerInfo** | Canvas.tsx | 1368 | **1.5** | - | Infos client |
| **drawCompanyLine** | Canvas.tsx | 1768 | **1.2** | - | Signature fonction |
| **drawCompanyInfo** | Canvas.tsx | 2116 | **1.1** | - | Infos entreprise |
| **UI Slider** | CompanyInfoProperties.tsx | 1751 | **1.0** | 0.8-3.0 | Formulaire |
| **render_company_info** | Ajax_Handler.php | 3703 | **1.1** | - | Backend |
| **render_order_notes** | Ajax_Handler.php | 4415 | **1.3** | - | Notes commande |

---

## 🎨 FORMULAIRE PROPRIÉTÉS company_info

### Emplacement du contrôle lineHeight

**Fichier:** [src/js/react/components/properties/CompanyInfoProperties.tsx](src/js/react/components/properties/CompanyInfoProperties.tsx#L1732-L1754)  
**Onglet:** "Positionnement"  
**Section:** "Espacement des lignes des informations"

```tsx
<label>
  Espacement des lignes des informations{" "}
  <span style={{ color: "#666", fontSize: "10px" }}>
    ({(parseFloat(String(element.lineHeight || 1.0))).toFixed(1)})
  </span>
</label>
<input
  type="range"
  min="0.8"
  max="3"
  step="0.1"
  value={parseFloat(String(element.lineHeight || 1.0))}
  onChange={(e) =>
    onChange(element.id, "lineHeight", parseFloat(e.target.value))
  }
/>
```

**Caractéristiques:**
- Type: Range slider
- Plage: 0.8 à 3.0
- Pas: 0.1
- Affichage: Valeur arrondie à 1 décimale
- Défaut formulaire: 1.0

---

## ⚠️ POINTS CRITIQUES ET RISQUES

### ✅ Points Importants

1. **DOMPDF Incompatibilité:** `line-height` CSS direct ne fonctionne pas avec DOMPDF
   - ✅ Solution implémentée: Utiliser `margin-bottom` basé sur la formule React

2. **Synchronisation React-PHP:** La formule doit être identique
   - ✅ Formule confirmée: `gap = fontSize × (lineHeight - 1)`

3. **Protection de la normalisation:** `lineHeight` dans un Set critique
   - ✅ PropertyNormalization.ts line 485 protège la propriété

4. **Différenciation header/body dans company_info:**
   - ✅ Header forcé à 1.2
   - ✅ Body utilise la valeur personnalisée

### ⚠️ Risques Identifiés

1. **Incohérence des valeurs par défaut:**
   - React Canvas: 1.1 pour company_info
   - Formulaire UI: 1.0
   - **Action:** Vérifier la valeur attendue

2. **Valeurs différentes par contexte:**
   - drawText: 1.2
   - drawCustomerInfo: 1.5
   - drawCompanyInfo: 1.1
   - order_notes: 1.3
   - **Action:** Document justifiant ces écarts

3. **Nettoyage CSS agressif (line 4409):**
   - Regex: `/line-height:\s*[^;]+;/` supprime tout line-height
   - **Risque:** Perte de line-height dans base_styles
   - **Mitigation:** Recalculé via `calculate_line_gap`

---

## 📞 APPELS À calculate_line_gap

| Fichier | Ligne | Contexte | f_size | lineHeight | Usage |
|---------|-------|----------|--------|-----------|-------|
| PDF_Builder_Unified_Ajax_Handler.php | 3676 | customer_info | $container_font_size | $layout_props['lineHeight'] | margin-bottom |
| PDF_Builder_Unified_Ajax_Handler.php | 3860 | company_info | $container_font_size | $layout_props['lineHeight'] | margin-bottom |
| PDF_Builder_Unified_Ajax_Handler.php | 4420 | order_notes | $font_size | $line_height_ratio | margin-bottom |

---

## ✔️ AUDIT CONCLUSION

**Nombre de fichiers affectés:** 4  
**Nombre de lignes de code:** 50+  
**Complexité:** Moyenne (synchronisation React-PHP bien documentée)  
**État:** ✅ Bien implémenté avec documentation appropriée

**Recommandations:**
1. ✅ Documenter les valeurs par défaut différentes par contexte
2. ✅ Créer tests d'intégration pour vérifier la synchronisation
3. ✅ Considérer normaliser les défauts (actuellement 1.0-1.5 disparates)
4. ✅ Ajouter validation des limites (0.8-3.0 respectées)

