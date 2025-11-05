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
    private $pagePadding = 10;

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
        // Use fixed ratio to match A4 proportions
        $canvasWidth = $this->templateData['canvasWidth'] ?? 794;
        $canvasHeight = $this->templateData['canvasHeight'] ?? 1123;

        // Scale to fit within preview while maintaining A4 aspect ratio
        // 794x1123 should scale to fit in 350x494
        $this->scaleFactor = min(
            ($this->previewWidth - 20) / $canvasWidth,    // Leave margins
            ($this->previewHeight - 20) / $canvasHeight
        );
    }

    public function generateSVG()
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg width="' . $this->previewWidth . '" height="' . $this->previewHeight . '" viewBox="0 0 ' . $this->previewWidth . ' ' . $this->previewHeight . '" xmlns="http://www.w3.org/2000/svg">' . "\n";

        // Add white A4 page background
        $pageMargin = $this->pagePadding; 
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
        $elementsByGroup = [];
        if (isset($this->templateData['elements'])) {
            foreach ($this->templateData['elements'] as $element) {
                $svgElement = $this->generateElement($element, $pageMargin, $pageY);
                if ($svgElement) {
                    $groupId = $this->getElementGroup($element);
                    if (!isset($elementsByGroup[$groupId])) {
                        $elementsByGroup[$groupId] = '';
                    }
                    $elementsByGroup[$groupId] .= $svgElement;
                }
            }
        }

        // Insert elements into their respective groups
        foreach ($elementsByGroup as $groupId => $elements) {
            $svg = str_replace('    <g id="' . $groupId . '">' . "\n" . '    </g>', '    <g id="' . $groupId . '">' . "\n" . $elements . '    </g>', $svg);
        }

        $svg .= '</svg>';

        return $svg;
    }

    private function generateElement($element, $pageMargin = 0, $pageY = 0)
    {
        $type = $element['type'] ?? '';
        $x = ($element['x'] ?? 0) * $this->scaleFactor + $pageMargin;
        $y = ($element['y'] ?? 0) * $this->scaleFactor + $pageY;
        $width = ($element['width'] ?? 0) * $this->scaleFactor;
        $height = ($element['height'] ?? 0) * $this->scaleFactor;

        // Calculate page bounds
        $pageWidth = $this->previewWidth - ($pageMargin * 2);
        $pageHeight = $pageWidth * 1.414; // A4 ratio

        // Clip elements to stay within page bounds
        // Don't render if element is completely outside bounds
        if ($y + $height < $pageY || $y > $pageY + $pageHeight) {
            return ''; // Element is outside visible area
        }

        // Clip width
        if ($x + $width > $this->previewWidth - $pageMargin) {
            $width = $this->previewWidth - $pageMargin - $x;
        }
        if ($x < $pageMargin) {
            $width -= $pageMargin - $x;
            $x = $pageMargin;
        }

        // Clip height
        if ($y + $height > $pageY + $pageHeight) {
            $height = $pageY + $pageHeight - $y;
        }
        if ($y < $pageY) {
            $height -= $pageY - $y;
            $y = $pageY;
        }

        // Skip if element has no visible dimensions
        if ($width <= 0 || $height <= 0) {
            return '';
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
                $fontSize = max(($properties['fontSize'] ?? 12) * $this->scaleFactor * 0.9, 8); // Better size
                $color = $properties['color'] ?? '#000000';
                $textAlign = $properties['textAlign'] ?? 'left';
                $fontWeight = $properties['fontWeight'] ?? 'normal';

                // Skip dynamic content for preview - replace with sample data
                if (strpos($text, '{{') !== false) {
                    $elementId = $element['id'] ?? '';
                    
                    if (strpos($elementId, 'subtotal-label') !== false) {
                        $text = 'Sous-total:';
                    } elseif (strpos($elementId, 'subtotal-value') !== false) {
                        $text = '€2500.00';
                    } elseif (strpos($elementId, 'discount-label') !== false) {
                        $text = 'Coupon:';
                    } elseif (strpos($elementId, 'discount-value') !== false) {
                        $text = '-€250.00';
                    } elseif (strpos($elementId, 'total-label') !== false) {
                        $text = 'TOTAL:';
                    } elseif (strpos($elementId, 'total-value') !== false) {
                        $text = '€2250.00';
                    } elseif (strpos($elementId, 'customer') !== false || strpos($elementId, 'client') !== false) {
                        $text = 'Sample Text';
                    } elseif (strpos($elementId, 'address') !== false) {
                        $text = 'Sample Address';
                    } else {
                        $text = 'Sample Text';
                    }
                }

                $textAnchor = 'start';
                $textX = $x;
                if ($textAlign === 'center') {
                    $textAnchor = 'middle';
                    $textX = $x + ($width / 2);
                } elseif ($textAlign === 'right') {
                    $textAnchor = 'end';
                    $textX = $x + $width;
                }

                // Constrain to visible area
                $textX = max($this->pagePadding + 2, min($textX, $this->previewWidth - $this->pagePadding - 2));
                $textY = max($y + $fontSize, min($y + $fontSize, $this->previewHeight - 2));

                return '    <text x="' . $textX . '" y="' . $textY . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '" text-anchor="' . $textAnchor . '" font-weight="' . $fontWeight . '">' . htmlspecialchars(substr($text, 0, 60)) . '</text>' . "\n";

            case 'company_info':
                // Generate sample company info for preview
                $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor * 0.85, 8);
                $color = $properties['textColor'] ?? '#ffffff';
                $sampleText = "Entreprise XYZ\n123 Rue de la Paix\n75001 Paris";

                $lines = explode("\n", $sampleText);
                $svg = '';
                foreach ($lines as $i => $line) {
                    $lineY = $y + ($i + 1) * ($fontSize * 1.4); // More spacing between lines
                    // Ensure text stays within bounds
                    $lineY = max($fontSize, min($lineY, $this->previewHeight - 10));
                    $svg .= '    <text x="' . max($this->pagePadding + 2, $x) . '" y="' . $lineY . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . htmlspecialchars($line) . '</text>' . "\n";
                }
                return $svg;

            case 'customer_info':
                // Generate sample customer info for preview
                $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor * 0.85, 8);
                $color = $properties['textColor'] ?? '#000000';
                $sampleText = "Client ABC\n456 Avenue des Champs\n92000 Nanterre";

                $lines = explode("\n", $sampleText);
                $svg = '';
                foreach ($lines as $i => $line) {
                    $lineY = $y + ($i + 1) * ($fontSize * 1.4);
                    $svg .= '    <text x="' . $x . '" y="' . $lineY . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . htmlspecialchars($line) . '</text>' . "\n";
                }
                return $svg;

            case 'order_number':
                // Generate sample order number for preview
                $fontSize = max(($properties['fontSize'] ?? 12) * $this->scaleFactor, 10);
                $color = $properties['textColor'] ?? '#000000';
                $sampleText = "Commande #12345";
                return '    <text x="' . $x . '" y="' . ($y + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . htmlspecialchars($sampleText) . '</text>' . "\n";

            case 'document_type':
                // Generate sample document type for preview
                $fontSize = max(($properties['fontSize'] ?? 24) * $this->scaleFactor * 0.8, 16);
                $color = $properties['textColor'] ?? '#000000';
                $fontFamily = $properties['fontFamily'] ?? 'Georgia';
                $fontWeight = $properties['fontWeight'] ?? 'bold';
                $textDecoration = $properties['textDecoration'] ?? 'underline';
                $sampleText = $properties['title'] ?? 'FACTURE';

                $textAnchor = 'middle';
                $textX = $x + ($width / 2);
                $textY = $y + $fontSize;

                $svg = '    <text x="' . $textX . '" y="' . $textY . '" font-family="' . $fontFamily . '" font-size="' . $fontSize . '" fill="' . $color . '" text-anchor="' . $textAnchor . '" font-weight="' . $fontWeight . '"';
                if ($textDecoration === 'underline') {
                    $svg .= ' text-decoration="underline"';
                }
                $svg .= '>' . htmlspecialchars($sampleText) . '</text>' . "\n";
                return $svg;

            case 'dynamic-text':
                // Generate sample dynamic text for preview based on element ID
                $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor, 8);
                $color = $properties['textColor'] ?? '#000000';
                $elementId = $element['id'] ?? '';

                // Provide appropriate sample content based on element ID
                $sampleText = 'Sample Text';
                if (strpos($elementId, 'due') !== false) {
                    $sampleText = 'Date d\'échéance: 15/11/2025';
                } elseif (strpos($elementId, 'payment') !== false) {
                    $sampleText = 'Conditions de règlement: Virement bancaire';
                } elseif (strpos($elementId, 'footer') !== false) {
                    $sampleText = 'Merci de votre confiance - Document généré automatiquement le 05/11/2025';
                } elseif (strpos($elementId, 'date') !== false) {
                    $sampleText = 'Date: 05/11/2025';
                } elseif (isset($properties['content'])) {
                    $content = $properties['content'];
                    // Replace variables with sample data
                    $content = str_replace('{{order_date}}', '05/11/2025', $content);
                    $content = str_replace('{{date}}', '05/11/2025', $content);
                    $content = str_replace('{{payment_method}}', 'Virement bancaire', $content);
                    $content = str_replace('{{due_date}}', '15/11/2025', $content);
                    $sampleText = $content;
                }

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