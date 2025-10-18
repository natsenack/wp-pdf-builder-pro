# Plan d'Implémentation - Propriétés Manquantes

## Code à ajouter/modifier

### 1. Ajouter des utilitaires helpers pour les effets

À ajouter après les autres fonctions utilitaires (après `get_text_alignment`):

```php
    /**
     * Appliquer les effets visuels à un élément (ombres, etc.)
     * Les effets comme opacity, brightness, contrast, saturate ne sont pas supportés par TCPDF
     * Cette fonction enregistre des logs pour avertir l'utilisateur
     */
    private function apply_element_effects($element, $px_to_mm) {
        // Vérifier les effets non supportés
        if (!empty($element['opacity']) && $element['opacity'] < 100) {
            $this->log_warning("Propriété 'opacity' non supportée par TCPDF. L'élément s'affichera à 100%");
        }
        
        if (!empty($element['brightness']) && $element['brightness'] !== 100) {
            $this->log_warning("Propriété 'brightness' non supportée par TCPDF");
        }
        
        if (!empty($element['contrast']) && $element['contrast'] !== 100) {
            $this->log_warning("Propriété 'contrast' non supportée par TCPDF");
        }
        
        if (!empty($element['saturate']) && $element['saturate'] !== 100) {
            $this->log_warning("Propriété 'saturate' non supportée par TCPDF");
        }
        
        // Rotation (partiellement supportée)
        if (!empty($element['rotation']) && $element['rotation'] !== 0) {
            $this->log_info("Rotation de {$element['rotation']}° - Support limité");
        }
        
        // Scale (partiellement supportée)
        if (!empty($element['scale']) && $element['scale'] !== 100) {
            $this->log_info("Scale {$element['scale']}% - Appliqué comme ajustement de taille");
        }
    }

    /**
     * Appliquer la décoration du texte (underline, line-through)
     * Utilisé pour render_text_element, render_mentions, etc.
     */
    private function get_text_decoration_style($element) {
        $textDecoration = $element['textDecoration'] ?? 'none';
        
        // TCPDF supporte U pour underline dans le 2ème param de SetFont
        // mais pas line-through directement, donc on retourne les infos
        return [
            'underline' => $textDecoration === 'underline',
            'line_through' => $textDecoration === 'line-through'
        ];
    }

    /**
     * Obtenir la hauteur de ligne calculée
     * Utilisé pour tous les éléments texte multi-ligne
     */
    private function calculate_line_height($fontSize, $element) {
        $lineHeight = floatval($element['lineHeight'] ?? 1.2);
        
        // lineHeight peut être numérique (1.2) ou en pixels/mm
        if ($lineHeight > 10) {
            // Probablement en pixels, convertir en ratio
            $lineHeight = $lineHeight / $fontSize;
        }
        
        return $fontSize * $lineHeight;
    }

    /**
     * Appliquer le style de bordure (solid, dashed, dotted)
     */
    private function apply_border_style($element) {
        $borderStyle = $element['borderStyle'] ?? 'solid';
        
        switch ($borderStyle) {
            case 'dashed':
                $this->pdf->SetDash(3, 2);
                break;
            case 'dotted':
                $this->pdf->SetDash(0.5, 1);
                break;
            case 'solid':
            default:
                $this->pdf->SetDash(0, 0);
                break;
        }
    }

    /**
     * Obtenir les dimensions ajustées pour scale
     */
    private function apply_scale_to_dimensions($width, $height, $element) {
        $scale = floatval($element['scale'] ?? 100) / 100;
        
        if ($scale !== 1) {
            return [
                'width' => $width * $scale,
                'height' => $height * $scale
            ];
        }
        
        return [
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     * Tracer une ombre pour un élément
     */
    private function draw_element_shadow($element, $px_to_mm, $x, $y, $width, $height) {
        $shadow = isset($element['shadow']) ? (bool)$element['shadow'] : false;
        
        if (!$shadow) {
            return; // Pas d'ombre à tracer
        }
        
        $shadowOffsetX = floatval($element['shadowOffsetX'] ?? 2) * $px_to_mm;
        $shadowOffsetY = floatval($element['shadowOffsetY'] ?? 2) * $px_to_mm;
        $shadowColor = $element['shadowColor'] ?? '#000000';
        
        // Parser la couleur
        $color = $this->parse_color($shadowColor);
        
        // Réduire l'intensité de la couleur pour l'ombre (50% d'opacité en gris)
        $grayValue = intval(($color['r'] + $color['g'] + $color['b']) / 3 * 0.5);
        
        $this->pdf->SetDrawColor($grayValue, $grayValue, $grayValue);
        $this->pdf->SetFillColor($grayValue, $grayValue, $grayValue);
        
        // Tracer un rectangle légèrement décalé
        $this->pdf->Rect(
            $x + $shadowOffsetX,
            $y + $shadowOffsetY,
            $width,
            $height,
            'F' // Remplir
        );
        
        // Restaurer la couleur de dessin
        $this->pdf->SetDrawColor(0, 0, 0);
    }

    /**
     * Enregistrer un avertissement dans les logs
     */
    private function log_warning($message) {
        $logger = new \PDF_Builder\Managers\PDF_Builder_Logger();
        $logger->log("⚠️ AVERTISSEMENT PDF Builder: $message", 'warning');
    }

    /**
     * Enregistrer une information dans les logs
     */
    private function log_info($message) {
        $logger = new \PDF_Builder\Managers\PDF_Builder_Logger();
        $logger->log("ℹ️ INFO PDF Builder: $message", 'info');
    }
```

---

### 2. Modifier render_text_element pour ajouter textDecoration et lineHeight

À modifier dans la méthode `render_text_element` (lignes ~491-580):

**Avant:**
```php
    // Remplacement des variables dans le texte
    $processed_text = $this->replace_variables_in_text($text);

    // Rendu du texte
    $this->pdf->Cell($adjusted_width, $adjusted_height, $processed_text, $border, 0, $align, $fill);
```

**Après:**
```php
    // Remplacement des variables dans le texte
    $processed_text = $this->replace_variables_in_text($text);

    // Appliquer les effets visuels
    $this->apply_element_effects($element, $px_to_mm);
    
    // Appliquer la décoration de texte
    $textDecoration = $this->get_text_decoration_style($element);
    $fontStyle = $this->get_font_style($element);
    if ($textDecoration['underline']) {
        $fontStyle .= 'U';
    }
    
    // Réappliquer la police avec underline si nécessaire
    if ($textDecoration['underline']) {
        $this->pdf->SetFont($font_family, $fontStyle, $font_size_pt);
    }

    // Calculer la hauteur de ligne correcte
    $lineHeight = floatval($element['lineHeight'] ?? 1.2);
    $adjustedCellHeight = $this->calculate_line_height($font_size_pt, $element) * $px_to_mm;

    // Rendu du texte
    $this->pdf->Cell($adjusted_width, $adjustedCellHeight, $processed_text, $border, 0, $align, $fill);
    
    // Tracer une ligne de biffure si line-through
    if ($textDecoration['line_through']) {
        $textWidth = $this->pdf->GetStringWidth($processed_text);
        $lineY = $final_y + ($adjustedCellHeight / 2);
        $this->pdf->SetDrawColor($color['r'], $color['g'], $color['b']);
        $this->pdf->Line($final_x, $lineY, $final_x + $textWidth, $lineY);
    }
```

---

### 3. Modifier render_rectangle_element pour ajouter rotation, scale, shadow, borderStyle

À modifier dans `render_rectangle_element` (lignes ~627-670):

```php
    private function render_rectangle_element($element, $px_to_mm) {
        // Extraction des coordonnées
        $coords = $this->extract_element_coordinates($element, $px_to_mm);
        $x = $coords['x'];
        $y = $coords['y'];
        $width = $coords['width'];
        $height = $coords['height'];

        // Appliquer l'ombre d'abord (elle sera en dessous)
        $this->draw_element_shadow($element, $px_to_mm, $x, $y, $width, $height);

        // Appliquer scale
        $scaled = $this->apply_scale_to_dimensions($width, $height, $element);
        $width = $scaled['width'];
        $height = $scaled['height'];

        // Centrer si scale a changé les dimensions
        $scale = floatval($element['scale'] ?? 100) / 100;
        if ($scale !== 1) {
            $scaleDiffX = ($coords['width'] - $width) / 2;
            $scaleDiffY = ($coords['height'] - $height) / 2;
            $x += $scaleDiffX;
            $y += $scaleDiffY;
        }

        // Couleur de remplissage
        $fill = false;
        $background_color = $element['backgroundColor'] ?? null;
        if ($this->should_render_background($background_color)) {
            $bg_color = $this->parse_color($background_color);
            $this->pdf->SetFillColor($bg_color['r'], $bg_color['g'], $bg_color['b']);
            $fill = true;
        }

        // Bordure
        $border_color = $element['borderColor'] ?? '#6b7280';
        $border_width = $element['borderWidth'] ?? 0;
        $border_color_rgb = $this->parse_color($border_color);
        $this->pdf->SetDrawColor($border_color_rgb['r'], $border_color_rgb['g'], $border_color_rgb['b']);

        // Appliquer le style de bordure
        $this->apply_border_style($element);

        // Épaisseur de bordure
        if ($border_width > 0) {
            $this->pdf->SetLineWidth($border_width * $px_to_mm);
        }

        // Rayon de bordure
        $border_radius = floatval($element['borderRadius'] ?? 0);
        
        // Gestion de la rotation
        $rotation = floatval($element['rotation'] ?? 0);
        if ($rotation !== 0) {
            // Sauvegarder l'état graphique
            $centerX = $x + $width / 2;
            $centerY = $y + $height / 2;
            
            // TCPDF ne supporte pas bien la rotation pour les formes simples
            // On va logguer un avertissement
            $this->log_info("Rotation de {$rotation}° - Support limité sur les rectangles");
        }

        // Rendre le rectangle
        if ($border_radius > 0) {
            // Rectangle arrondi
            $this->pdf->RoundedRect($x, $y, $width, $height, $border_radius * $px_to_mm, 'D', [], $this->parse_color($border_color));
            if ($fill) {
                // Remplir avec couleur de fond
                $this->pdf->RoundedRect($x, $y, $width, $height, $border_radius * $px_to_mm, 'F', []);
            }
        } else {
            // Rectangle simple
            $border_param = ($border_width > 0) ? 1 : 0;
            $this->pdf->Rect($x, $y, $width, $height, $border_param, [], [], $fill ? 'F' : 'D');
        }

        // Réinitialiser le style de bordure
        $this->pdf->SetDash(0, 0);
    }
```

---

### 4. Vérifier et compléter render_product_table_element

Vérifier que les propriétés suivantes sont utilisées:
- ✅ `columns.image, columns.name, columns.sku, columns.quantity, columns.price, columns.total`
- ✅ `showHeaders, showBorders`
- ❓ `evenRowBg, oddRowBg, evenRowTextColor, oddRowTextColor`

À vérifier: Ligne ~1888 et suivantes

---

## Résumé des Modifications

| Propriété | Méthode | Type de Modif | Priorité |
|-----------|---------|---------------|----------|
| textDecoration | render_text_element | Ajouter | HIGH |
| lineHeight | render_text_element | Ajouter | HIGH |
| borderStyle | render_rectangle_element | Ajouter | MEDIUM |
| shadow | render_rectangle_element | Ajouter | MEDIUM |
| scale | render_rectangle_element | Ajouter | MEDIUM |
| rotation | render_rectangle_element | Log seulement | MEDIUM |
| opacity | apply_element_effects | Log warning | LOW |
| brightness | apply_element_effects | Log warning | LOW |
| contrast | apply_element_effects | Log warning | LOW |
| saturate | apply_element_effects | Log warning | LOW |

