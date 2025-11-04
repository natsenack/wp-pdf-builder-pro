<?php
// Script pour vérifier les proportions des éléments dans les templates
$templates = ['classic.json', 'modern.json', 'corporate.json', 'minimal.json'];
$ratio = 794/595; // ≈1.335

echo "=== VÉRIFICATION DES PROPORTIONS DES ÉLÉMENTS ===\n";
echo "Ratio de conversion: 794/595 ≈ " . number_format($ratio, 3) . "\n\n";

foreach ($templates as $template) {
    $file = __DIR__ . '/' . $template;
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if ($data && isset($data['elements'])) {
            echo "📄 " . strtoupper(str_replace('.json', '', $template)) . " (Canvas: {$data['canvasWidth']}x{$data['canvasHeight']})\n";

            // Analyser quelques éléments représentatifs
            $sampleElements = array_slice($data['elements'], 0, 3);

            foreach ($sampleElements as $element) {
                $x = $element['x'] ?? 0;
                $y = $element['y'] ?? 0;
                $width = $element['width'] ?? 0;
                $height = $element['height'] ?? 0;
                $id = $element['id'] ?? 'unknown';

                $rightEdge = $x + $width;
                $bottomEdge = $y + $height;
                $rightMargin = $data['canvasWidth'] - $rightEdge;
                $bottomMargin = $data['canvasHeight'] - $bottomEdge;

                echo "  🔹 $id:\n";
                echo "     Position: x=$x, y=$y\n";
                echo "     Dimensions: w=$width, h=$height\n";
                echo "     Bord droit: $rightEdge (marge droite: {$rightMargin}px)\n";
                echo "     Bord bas: $bottomEdge (marge basse: {$bottomMargin}px)\n";
            }
            echo "\n";
        }
    }
}

echo "=== ANALYSE DES PROPORTIONS ===\n";
echo "Ratio théorique: 1.335\n";
echo "Exemple avec header-border du template classic:\n";
echo "Ancien canvas: 595px, élément: x=40, width=515 → bord droit=555\n";
echo "Nouveau canvas: 794px, élément devrait avoir: x=53, width=687 → bord droit=740\n";
echo "Calcul: 40×1.335≈53, 515×1.335≈687 ✓\n";
?>