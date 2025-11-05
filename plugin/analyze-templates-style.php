<?php
/**
 * Analyseur de Templates - Style et Contenu
 * Évalue la qualité visuelle et les améliorations possibles
 */

$templates = ['corporate', 'classic', 'minimal', 'modern'];
$basePath = __DIR__ . '/templates/builtin/';

echo "═══════════════════════════════════════════════════════════════\n";
echo "🔍 ANALYSE DÉTAILLÉE DES TEMPLATES\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

foreach ($templates as $templateName) {
    $jsonPath = $basePath . $templateName . '.json';
    $data = json_decode(file_get_contents($jsonPath), true);
    
    echo "📄 TEMPLATE: " . strtoupper($templateName) . "\n";
    echo "───────────────────────────────────────────────────────────────\n";
    echo "Nom: " . $data['name'] . "\n";
    echo "Description: " . $data['description'] . "\n";
    echo "Catégorie: " . $data['category'] . "\n";
    echo "Canvas: " . $data['canvasWidth'] . "x" . $data['canvasHeight'] . " px (A4)\n";
    echo "Nombre d'éléments: " . count($data['elements']) . "\n";
    echo "\n✨ ANALYSE VISUELLE:\n";
    
    // Analyze colors
    $colors = [];
    $fonts = [];
    $fontSizes = [];
    
    foreach ($data['elements'] as $element) {
        $props = $element['properties'] ?? [];
        
        // Collect colors
        if (!empty($props['fillColor'])) $colors[] = $props['fillColor'];
        if (!empty($props['color'])) $colors[] = $props['color'];
        if (!empty($props['textColor'])) $colors[] = $props['textColor'];
        if (!empty($props['backgroundColor'])) $colors[] = $props['backgroundColor'];
        if (!empty($props['strokeColor'])) $colors[] = $props['strokeColor'];
        if (!empty($props['headerBackgroundColor'])) $colors[] = $props['headerBackgroundColor'];
        if (!empty($props['alternateRowColor'])) $colors[] = $props['alternateRowColor'];
        if (!empty($props['borderColor'])) $colors[] = $props['borderColor'];
        
        // Collect fonts
        if (!empty($props['fontFamily'])) $fonts[] = $props['fontFamily'];
        
        // Collect font sizes
        if (!empty($props['fontSize'])) $fontSizes[] = $props['fontSize'];
    }
    
    $uniqueColors = array_unique($colors);
    $uniqueFonts = array_unique($fonts);
    $uniqueSizes = array_unique($fontSizes);
    sort($uniqueSizes);
    
    echo "  Couleurs utilisées (" . count($uniqueColors) . "): " . implode(", ", $uniqueColors) . "\n";
    echo "  Polices utilisées (" . count($uniqueFonts) . "): " . implode(", ", $uniqueFonts) . "\n";
    echo "  Tailles de police: " . implode(", ", $uniqueSizes) . "\n";
    
    // Analyze structure
    echo "\n📐 STRUCTURE:\n";
    $elementTypes = [];
    foreach ($data['elements'] as $element) {
        $type = $element['type'];
        if (!isset($elementTypes[$type])) $elementTypes[$type] = 0;
        $elementTypes[$type]++;
    }
    
    foreach ($elementTypes as $type => $count) {
        echo "  • " . $type . ": " . $count . "x\n";
    }
    
    // Spacing analysis
    echo "\n📏 ESPACEMENT (Y positions):\n";
    $yPositions = [];
    foreach ($data['elements'] as $element) {
        $yPositions[$element['id']] = $element['y'];
    }
    asort($yPositions);
    
    $prevY = 0;
    $gaps = [];
    foreach ($yPositions as $id => $y) {
        $gap = $y - $prevY;
        if ($gap > 0 && $gap < 200) {
            $gaps[] = "  • " . str_pad($id, 20) . ": y=" . $y . " (gap=" . $gap . "px)";
        }
        $prevY = $y;
    }
    
    if (!empty($gaps)) {
        echo implode("\n", array_slice($gaps, 0, 10)) . "\n";
        if (count($gaps) > 10) echo "  ... et " . (count($gaps) - 10) . " autres\n";
    }
    
    // Content analysis
    echo "\n📝 CONTENU:\n";
    $staticTexts = [];
    $dynamicTexts = [];
    
    foreach ($data['elements'] as $element) {
        if ($element['type'] === 'text') {
            $text = $element['properties']['text'] ?? '';
            if (!empty($text)) $staticTexts[] = $text;
        }
        if ($element['type'] === 'dynamic-text') {
            $content = $element['properties']['content'] ?? '';
            if (!empty($content)) $dynamicTexts[] = $content;
        }
    }
    
    echo "  Textes statiques (" . count($staticTexts) . "):\n";
    foreach (array_slice($staticTexts, 0, 5) as $text) {
        echo "    - " . substr($text, 0, 50) . (strlen($text) > 50 ? "..." : "") . "\n";
    }
    
    echo "  Textes dynamiques (" . count($dynamicTexts) . "):\n";
    foreach ($dynamicTexts as $text) {
        echo "    - " . substr($text, 0, 50) . (strlen($text) > 50 ? "..." : "") . "\n";
    }
    
    echo "\n🎨 POINTS FORTS ET FAIBLES:\n";
    
    // Evaluate quality
    $strengths = [];
    $weaknesses = [];
    
    if (count($uniqueColors) >= 3) {
        $strengths[] = "✅ Bonne palette de couleurs (" . count($uniqueColors) . " couleurs)";
    } else {
        $weaknesses[] = "❌ Palette trop limitée (" . count($uniqueColors) . " couleur(s))";
    }
    
    if (count($uniqueFonts) == 1) {
        $strengths[] = "✅ Police cohérente (" . reset($uniqueFonts) . ")";
    } else if (count($uniqueFonts) <= 2) {
        $strengths[] = "✅ Polices bien choisies (" . count($uniqueFonts) . ")";
    } else {
        $weaknesses[] = "❌ Trop de polices différentes (" . count($uniqueFonts) . ")";
    }
    
    if (count($uniqueSizes) <= 5) {
        $strengths[] = "✅ Hiérarchie de tailles claire";
    } else {
        $weaknesses[] = "❌ Trop de tailles de police différentes (" . count($uniqueSizes) . ")";
    }
    
    if (!empty($dynamicTexts)) {
        $strengths[] = "✅ Contenu dynamique intégré";
    } else {
        $weaknesses[] = "❌ Pas de contenu dynamique";
    }
    
    if (!empty($elementTypes['product_table']) && $elementTypes['product_table'] > 0) {
        $strengths[] = "✅ Tableau de produits intégré";
    } else {
        $weaknesses[] = "⚠️ Pas de tableau de produits";
    }
    
    // Check for info elements
    $hasCustomerInfo = isset($elementTypes['customer_info']) && $elementTypes['customer_info'] > 0;
    $hasCompanyInfo = isset($elementTypes['company_info']) && $elementTypes['company_info'] > 0;
    
    if ($hasCustomerInfo && $hasCompanyInfo) {
        $strengths[] = "✅ Infos client ET entreprise";
    } else if ($hasCustomerInfo || $hasCompanyInfo) {
        $weaknesses[] = "⚠️ Infos client OU entreprise manquantes";
    }
    
    foreach ($strengths as $s) echo "  " . $s . "\n";
    foreach ($weaknesses as $w) echo "  " . $w . "\n";
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "📋 RECOMMANDATIONS GÉNÉRALES D'AMÉLIORATION\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

echo "1️⃣  STYLE & DESIGN:\n";
echo "   • Utiliser une palette de 3-4 couleurs cohérentes\n";
echo "   • Limiter les polices à 2 maximum (titres + corps)\n";
echo "   • Maintenir une hiérarchie claire (titre > sous-titre > corps)\n";
echo "   • Ajouter des espacements réguliers (20-30px)\n\n";

echo "2️⃣  CONTENU:\n";
echo "   • Ajouter un titre de document (FACTURE, DEVIS, etc.)\n";
echo "   • Inclure les informations client ET entreprise\n";
echo "   • Montrer le tableau des produits\n";
echo "   • Afficher les totaux clairement (HT, TVA, TTC)\n\n";

echo "3️⃣  LAYOUT:\n";
echo "   • En-tête: logo + infos entreprise (0-80px)\n";
echo "   • Infos: client + numéro commande (80-160px)\n";
echo "   • Tableau: produits avec en-tête (160-360px)\n";
echo "   • Totaux: récapitulatif (360-420px)\n";
echo "   • Pied: mentions légales (420-500px)\n\n";

echo "4️⃣  ÉLÉMENTS À VÉRIFIER:\n";
echo "   ✓ Contraste des couleurs (lisibilité)\n";
echo "   ✓ Espacements réguliers et cohérents\n";
echo "   ✓ Alignement des éléments\n";
echo "   ✓ Utilisation de toutes les propriétés disponibles\n";
echo "   ✓ Rendu identique à la sortie PDF\n\n";

?>
