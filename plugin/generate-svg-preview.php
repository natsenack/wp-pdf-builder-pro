<?php
/**
 * Générateur automatique d'aperçus SVG depuis les templates JSON
 * Usage: php generate-svg-preview.php <template-name>
 * Exemple: php generate-svg-preview.php corporate
 */

class SVGPreviewGenerator
{
    private $templateData;
    private $scaleFactor;
    private $previewWidth = 350;  // Canvas proportions: 794x1123 ratio
    private $previewHeight = 494; // 350 * (1123/794) ≈ 494

    public function __construct($jsonPath)
    {
        if (!file_exists($jsonPath)) {
            throw new Exception("Template JSON not found: $jsonPath");
        }

        $this->templateData = json_decode(file_get_contents($jsonPath), true);
        if (!$this->templateData) {
            throw new Exception("Invalid JSON file: $jsonPath");
        }

        // Calculate scale factor to fit canvas into preview dimensions
        // More conservative scaling to keep elements visible and proportional
        $canvasWidth = $this->templateData['canvasWidth'] ?? 794;
        $canvasHeight = $this->templateData['canvasHeight'] ?? 1123;

        $this->scaleFactor = min(
            $this->previewWidth / $canvasWidth * 1.8,  // More conservative scaling
            $this->previewHeight / $canvasHeight * 1.8
        );
    }

    public function generateSVG()
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg width="' . $this->previewWidth . '" height="' . $this->previewHeight . '" viewBox="0 0 ' . $this->previewWidth . ' ' . $this->previewHeight . '" xmlns="http://www.w3.org/2000/svg">' . "\n";

        // Add white A4 page background
        $pageMargin = 10; // Margin around the page
        $pageWidth = $this->previewWidth - ($pageMargin * 2);
        $pageHeight = $pageWidth * 1.414; // A4 ratio (√2)

        // Center the page vertically
        $pageY = ($this->previewHeight - $pageHeight) / 2;

        // White page background with subtle shadow
        $svg .= '  <!-- A4 Page Background -->' . "\n";
        $svg .= '  <rect x="' . ($pageMargin + 2) . '" y="' . ($pageY + 2) . '" width="' . $pageWidth . '" height="' . $pageHeight . '" fill="#f0f0f0" rx="4"/>' . "\n";
        $svg .= '  <rect x="' . $pageMargin . '" y="' . $pageY . '" width="' . $pageWidth . '" height="' . $pageHeight . '" fill="#ffffff" stroke="#e0e0e0" stroke-width="1" rx="4"/>' . "\n";

        // Add semantic groups inside the page
        $svg .= '  <g id="page-content" transform="translate(' . $pageMargin . ',' . $pageY . ')">' . "\n";

        $svg .= '    <g id="header">' . "\n";
        $svg .= '    </g>' . "\n";

        $svg .= '    <g id="company-info">' . "\n";
        $svg .= '    </g>' . "\n";

        $svg .= '    <g id="customer-info">' . "\n";
        $svg .= '    </g>' . "\n";

        $svg .= '    <g id="order-info">' . "\n";
        $svg .= '    </g>' . "\n";

        $svg .= '    <g id="products-table">' . "\n";
        $svg .= '    </g>' . "\n";

        $svg .= '    <g id="order-totals">' . "\n";
        $svg .= '    </g>' . "\n";

        $svg .= '    <g id="footer">' . "\n";
        $svg .= '    </g>' . "\n";

        $svg .= '  </g>' . "\n";

        // Generate elements
        if (isset($this->templateData['elements'])) {
            foreach ($this->templateData['elements'] as $element) {
                $svgElement = $this->generateElement($element, $pageMargin, $pageY);
                if ($svgElement) {
                    $groupId = $this->getElementGroup($element);
                    $svg = str_replace('    <g id="' . $groupId . '">' . "\n" . '    </g>', '    <g id="' . $groupId . '">' . "\n" . $svgElement . '    </g>', $svg);
                }
            }
        }

        $svg .= '</svg>';

        return $svg;
    }

    private function generateElement($element, $pageMargin = 0, $pageY = 0)
    {
        $type = $element['type'] ?? '';
        $x = max(0, ($element['x'] ?? 0) * $this->scaleFactor) + $pageMargin; // Add page margin
        $y = max(0, ($element['y'] ?? 0) * $this->scaleFactor) + $pageY; // Add page Y offset
        $width = ($element['width'] ?? 0) * $this->scaleFactor;
        $height = ($element['height'] ?? 0) * $this->scaleFactor;

        // Ensure elements don't exceed page bounds
        $pageWidth = $this->previewWidth - ($pageMargin * 2);
        $pageHeight = $pageWidth * 1.414;

        if ($x + $width > $this->previewWidth - $pageMargin) {
            $width = $this->previewWidth - $pageMargin - $x;
        }
        if ($y + $height > $pageY + $pageHeight) {
            $height = $pageY + $pageHeight - $y;
        }

        $properties = $element['properties'] ?? [];

        switch ($type) {
            case 'rectangle':
                $fillColor = $properties['fillColor'] ?? '#000000';
                $strokeWidth = ($properties['strokeWidth'] ?? 0) * $this->scaleFactor;
                return '    <rect x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . $height . '" fill="' . $fillColor . '" stroke-width="' . $strokeWidth . '"/>' . "\n";

            case 'circle':
                $cx = $x + ($width / 2);
                $cy = $y + ($height / 2);
                $r = min($width, $height) / 2;
                $fillColor = $properties['fillColor'] ?? '#000000';
                $strokeWidth = ($properties['strokeWidth'] ?? 0) * $this->scaleFactor;
                $opacity = $properties['opacity'] ?? 1;
                return '    <circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="' . $fillColor . '" stroke-width="' . $strokeWidth . '" opacity="' . $opacity . '"/>' . "\n";

            case 'text':
                $text = $properties['text'] ?? '';
                $fontSize = max(($properties['fontSize'] ?? 12) * $this->scaleFactor * 0.8, 10); // Minimum readable font size
                $color = $properties['color'] ?? '#000000';
                $textAlign = $properties['textAlign'] ?? 'left';
                $fontWeight = $properties['fontWeight'] ?? 'normal';

                // Skip dynamic content for preview
                if (strpos($text, '{{') !== false) {
                    $text = 'Sample Text';
                }

                $textAnchor = 'start';
                if ($textAlign === 'center') {
                    $textAnchor = 'middle';
                    $x += $width / 2;
                } elseif ($textAlign === 'right') {
                    $textAnchor = 'end';
                    $x += $width;
                }

                // Ensure text stays within bounds
                $x = max(5, min($x, $this->previewWidth - 50));
                $y = max($fontSize, min($y + $fontSize, $this->previewHeight - 5));

                return '    <text x="' . $x . '" y="' . $y . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '" text-anchor="' . $textAnchor . '" font-weight="' . $fontWeight . '">' . htmlspecialchars($text) . '</text>' . "\n";

            case 'company_info':
                // Generate sample company info for preview
                $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor * 0.6, 8); // Smaller but readable
                $color = $properties['textColor'] ?? '#000000';
                $sampleText = "Entreprise XYZ\n123 Rue de la Paix\n75001 Paris";

                $lines = explode("\n", $sampleText);
                $svg = '';
                foreach ($lines as $i => $line) {
                    $lineY = $y + ($i + 1) * ($fontSize * 1.2);
                    // Ensure text stays within bounds
                    $lineY = max($fontSize, min($lineY, $this->previewHeight - 10));
                    $svg .= '    <text x="' . max(5, $x) . '" y="' . $lineY . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . htmlspecialchars($line) . '</text>' . "\n";
                }
                return $svg;

            case 'customer_info':
                // Generate sample customer info for preview
                $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor, 8);
                $color = $properties['textColor'] ?? '#000000';
                $sampleText = "Client ABC\n456 Avenue des Champs\n92000 Nanterre";

                $lines = explode("\n", $sampleText);
                $svg = '';
                foreach ($lines as $i => $line) {
                    $lineY = $y + ($i + 1) * ($fontSize * 1.2);
                    $svg .= '    <text x="' . $x . '" y="' . $lineY . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . htmlspecialchars($line) . '</text>' . "\n";
                }
                return $svg;

            case 'order_number':
                // Generate sample order number for preview
                $fontSize = max(($properties['fontSize'] ?? 12) * $this->scaleFactor, 10);
                $color = $properties['textColor'] ?? '#000000';
                $sampleText = "Commande #12345";
                return '    <text x="' . $x . '" y="' . ($y + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . htmlspecialchars($sampleText) . '</text>' . "\n";

            case 'dynamic-text':
                // Generate sample dynamic text for preview
                $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor, 8);
                $color = $properties['textColor'] ?? '#000000';
                $sampleText = "Date: 05/11/2025";
                return '    <text x="' . $x . '" y="' . ($y + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . htmlspecialchars($sampleText) . '</text>' . "\n";

            case 'product_table':
                // Generate sample product table for preview
                $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor, 8);
                $headerColor = $properties['headerTextColor'] ?? '#ffffff';
                $headerBgColor = $properties['headerBgColor'] ?? '#28a745';

                $svg = '';
                // Table header background
                $svg .= '    <rect x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . ($fontSize * 2) . '" fill="' . $headerBgColor . '"/>' . "\n";
                // Table headers
                $svg .= '    <text x="' . ($x + 5) . '" y="' . ($y + $fontSize * 1.5) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $headerColor . '" font-weight="bold">Produit</text>' . "\n";
                $svg .= '    <text x="' . ($x + $width * 0.6) . '" y="' . ($y + $fontSize * 1.5) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $headerColor . '" font-weight="bold">Qté</text>' . "\n";
                $svg .= '    <text x="' . ($x + $width * 0.8) . '" y="' . ($y + $fontSize * 1.5) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $headerColor . '" font-weight="bold">Prix</text>' . "\n";

                // Sample product row
                $rowY = $y + $fontSize * 2.5;
                $svg .= '    <text x="' . ($x + 5) . '" y="' . ($rowY + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="#000000">Produit Sample</text>' . "\n";
                $svg .= '    <text x="' . ($x + $width * 0.6) . '" y="' . ($rowY + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="#000000">2</text>' . "\n";
                $svg .= '    <text x="' . ($x + $width * 0.8) . '" y="' . ($rowY + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="#000000">€50.00</text>' . "\n";

                return $svg;

            case 'mentions':
                // Generate sample footer mentions for preview
                $fontSize = max(($properties['fontSize'] ?? 8) * $this->scaleFactor, 6);
                $color = $properties['textColor'] ?? '#666666';
                $sampleText = "Conditions générales de vente - TVA non applicable art. 293B du CGI";
                return '    <text x="' . $x . '" y="' . ($y + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . htmlspecialchars($sampleText) . '</text>' . "\n";

            case 'line':
                $strokeWidth = ($properties['strokeWidth'] ?? 1) * $this->scaleFactor;
                $strokeColor = $properties['strokeColor'] ?? '#000000';
                $x2 = $x + $width;
                $y2 = $y + $height;
                return '    <line x1="' . $x . '" y1="' . $y . '" x2="' . $x2 . '" y2="' . $y2 . '" stroke="' . $strokeColor . '" stroke-width="' . $strokeWidth . '"/>' . "\n";

            default:
                // Skip unknown element types for preview
                return '';
        }
    }

    private function getElementGroup($element)
    {
        $id = $element['id'] ?? '';
        $type = $element['type'] ?? '';

        // Map elements to semantic groups
        if (strpos($id, 'header') !== false || strpos($id, 'logo') !== false || $type === 'document_type') {
            return 'header';
        }
        if ($type === 'company_info' || strpos($id, 'company') !== false) {
            return 'company-info';
        }
        if (strpos($id, 'client') !== false || $type === 'customer_info') {
            return 'customer-info';
        }
        if (strpos($id, 'order') !== false || $type === 'order_number' || $type === 'dynamic-text') {
            return 'order-info';
        }
        if ($type === 'product_table' || strpos($id, 'table') !== false || strpos($id, 'product') !== false || strpos($id, 'items') !== false) {
            return 'products-table';
        }
        if (strpos($id, 'total') !== false || strpos($id, 'subtotal') !== false || strpos($id, 'discount') !== false || strpos($id, 'tax') !== false) {
            return 'order-totals';
        }
        if ($type === 'mentions' || strpos($id, 'footer') !== false) {
            return 'footer';
        }

        // Default to header for unclassified elements
        return 'header';
    }
}

// Main execution
if ($argc < 2) {
    echo "Usage: php generate-svg-preview.php <template-name>\n";
    echo "Example: php generate-svg-preview.php corporate\n";
    exit(1);
}

$templateName = $argv[1];
$jsonPath = __DIR__ . "/templates/builtin/{$templateName}.json";
$svgPath = __DIR__ . "/assets/images/templates/{$templateName}-preview.svg";

try {
    $generator = new SVGPreviewGenerator($jsonPath);
    $svgContent = $generator->generateSVG();

    file_put_contents($svgPath, $svgContent);
    echo "✅ Aperçu SVG généré: {$templateName}-preview.svg\n";
    echo "📁 Chemin: {$svgPath}\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>