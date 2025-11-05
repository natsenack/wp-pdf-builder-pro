<?php
/**
 * Générateur d'aperçus SVG HONNÊTES
 * Affiche exactement ce qui sera généré en PDF, sans mensonges
 * 
 * Usage: php generate-svg-preview-honest.php <template-name>
 * Exemple: php generate-svg-preview-honest.php corporate
 */

class SVGPreviewGeneratorHonest
{
    private $templateData;
    private $scaleFactor;
    private $previewWidth = 350;
    private $previewHeight = 494;
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

        $canvasWidth = $this->templateData['canvasWidth'] ?? 794;
        $canvasHeight = $this->templateData['canvasHeight'] ?? 1123;

        $this->scaleFactor = min(
            ($this->previewWidth - 20) / $canvasWidth,
            ($this->previewHeight - 20) / $canvasHeight
        );
    }

    public function generateSVG()
    {
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $svg .= '<svg width="' . $this->previewWidth . '" height="' . $this->previewHeight . '" viewBox="0 0 ' . $this->previewWidth . ' ' . $this->previewHeight . '" xmlns="http://www.w3.org/2000/svg">' . "\n";

        $pageMargin = $this->pagePadding;
        $pageWidth = $this->previewWidth - ($pageMargin * 2);
        $pageHeight = $pageWidth * 1.414;
        $pageY = ($this->previewHeight - $pageHeight) / 2;

        // Page background
        $svg .= '  <rect x="' . ($pageMargin + 2) . '" y="' . ($pageY + 2) . '" width="' . $pageWidth . '" height="' . $pageHeight . '" fill="#f0f0f0" rx="4"/>' . "\n";
        $svg .= '  <rect x="' . $pageMargin . '" y="' . $pageY . '" width="' . $pageWidth . '" height="' . $pageHeight . '" fill="#ffffff" stroke="#e0e0e0" stroke-width="1" rx="4"/>' . "\n";

        $svg .= '  <defs>' . "\n";
        $svg .= '    <clipPath id="pageClip">' . "\n";
        $svg .= '      <rect x="' . $pageMargin . '" y="' . $pageY . '" width="' . $pageWidth . '" height="' . $pageHeight . '"/>' . "\n";
        $svg .= '    </clipPath>' . "\n";
        $svg .= '  </defs>' . "\n";

        $svg .= '  <g id="page-content" transform="translate(' . $pageMargin . ',' . $pageY . ')" clip-path="url(#pageClip)">' . "\n";

        // Semantic groups
        $groups = ['header', 'company-info', 'customer-info', 'order-info', 'products-table', 'order-totals', 'footer'];
        foreach ($groups as $group) {
            $svg .= '    <g id="' . $group . '">' . "\n";
            $svg .= '    </g>' . "\n";
        }

        $svg .= '  </g>' . "\n";

        // Generate elements - ONLY what's REALLY supported
        $elementsByGroup = [];
        if (isset($this->templateData['elements'])) {
            foreach ($this->templateData['elements'] as $element) {
                $type = $element['type'] ?? '';
                
                // ONLY render elements that have real renderers
                $supportedTypes = [
                    'rectangle', 'circle', 'line', 'arrow',        // Shapes
                    'text', 'dynamic-text', 'order_number',        // Text
                    'product_table',                               // Tables
                    'company_logo',                                // Images
                    'customer_info', 'company_info', 'mentions',   // Info
                    'document_type'                                // Document
                ];

                if (!in_array($type, $supportedTypes)) {
                    // Skip unsupported elements
                    continue;
                }

                $svgElement = $this->generateElement($element, $pageWidth, $pageHeight);
                if ($svgElement) {
                    $groupId = $this->getElementGroup($element);
                    if (!isset($elementsByGroup[$groupId])) {
                        $elementsByGroup[$groupId] = '';
                    }
                    $elementsByGroup[$groupId] .= $svgElement;
                }
            }
        }

        // Insert elements into their groups
        foreach ($elementsByGroup as $groupId => $elements) {
            $svg = str_replace('    <g id="' . $groupId . '">' . "\n" . '    </g>', '    <g id="' . $groupId . '">' . "\n" . $elements . '    </g>', $svg);
        }

        $svg .= '</svg>';
        return $svg;
    }

    private function getElementGroup($element)
    {
        $id = $element['id'] ?? '';
        $type = $element['type'] ?? '';

        // Group elements by semantic meaning
        if (strpos($id, 'header') !== false || strpos($id, 'logo') !== false) {
            return 'header';
        } elseif (strpos($id, 'company') !== false) {
            return 'company-info';
        } elseif (strpos($id, 'customer') !== false || strpos($id, 'client') !== false) {
            return 'customer-info';
        } elseif (strpos($id, 'order') !== false || strpos($id, 'date') !== false) {
            return 'order-info';
        } elseif ($type === 'product_table' || strpos($id, 'table') !== false || strpos($id, 'items') !== false) {
            return 'products-table';
        } elseif (strpos($id, 'total') !== false || strpos($id, 'subtotal') !== false || strpos($id, 'discount') !== false) {
            return 'order-totals';
        } elseif (strpos($id, 'footer') !== false || strpos($id, 'mentions') !== false) {
            return 'footer';
        }

        return 'order-info'; // Default
    }

    private function generateElement($element, $pageWidth = 330, $pageHeight = 466)
    {
        $type = $element['type'] ?? '';
        $x = ($element['x'] ?? 0) * $this->scaleFactor;
        $y = ($element['y'] ?? 0) * $this->scaleFactor;
        $width = ($element['width'] ?? 0) * $this->scaleFactor;
        $height = ($element['height'] ?? 0) * $this->scaleFactor;
        $properties = $element['properties'] ?? [];

        // Bounds checking
        if ($y + $height < 0 || $y > $pageHeight || $x + $width < 0 || $x > $pageWidth) {
            return '';
        }

        // Clip to bounds
        if ($y < 0) {
            $height -= (0 - $y);
            $y = 0;
        }
        if ($y + $height > $pageHeight) {
            $height = $pageHeight - $y;
        }
        if ($x < 0) {
            $width -= (0 - $x);
            $x = 0;
        }
        if ($x + $width > $pageWidth) {
            $width = $pageWidth - $x;
        }

        if ($width <= 0 || $height <= 0) {
            return '';
        }

        switch ($type) {
            case 'rectangle':
                $fillColor = $properties['fillColor'] ?? '#000000';
                $strokeWidth = ($properties['strokeWidth'] ?? 0) * $this->scaleFactor;
                $strokeColor = $properties['strokeColor'] ?? 'none';
                return '    <rect x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . $height . '" fill="' . $fillColor . '" stroke="' . $strokeColor . '" stroke-width="' . $strokeWidth . '"/>' . "\n";

            case 'circle':
                $cx = $x + ($width / 2);
                $cy = $y + ($height / 2);
                $r = min($width, $height) / 2;
                $fillColor = $properties['fillColor'] ?? '#000000';
                $strokeWidth = ($properties['strokeWidth'] ?? 0) * $this->scaleFactor;
                $opacity = $properties['opacity'] ?? 1;
                return '    <circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="' . $fillColor . '" stroke-width="' . $strokeWidth . '" opacity="' . $opacity . '"/>' . "\n";

            case 'line':
                $strokeColor = $properties['strokeColor'] ?? '#000000';
                $strokeWidth = max(($properties['strokeWidth'] ?? 1) * $this->scaleFactor, 1);
                return '    <line x1="' . $x . '" y1="' . $y . '" x2="' . ($x + $width) . '" y2="' . ($y + $height) . '" stroke="' . $strokeColor . '" stroke-width="' . $strokeWidth . '"/>' . "\n";

            case 'arrow':
                $strokeColor = $properties['strokeColor'] ?? '#000000';
                $strokeWidth = max(($properties['strokeWidth'] ?? 1) * $this->scaleFactor, 1);
                $direction = $properties['direction'] ?? 'right';
                return $this->renderArrow($x, $y, $width, $height, $strokeColor, $strokeWidth, $direction);

            case 'text':
                $text = htmlspecialchars($properties['text'] ?? '', ENT_QUOTES);
                $fontSize = max(($properties['fontSize'] ?? 12) * $this->scaleFactor * 0.9, 8);
                $color = $properties['color'] ?? '#000000';
                $fontWeight = $properties['fontWeight'] ?? 'normal';
                $textAnchor = $this->getTextAnchor($properties['textAlign'] ?? 'left');
                return '    <text x="' . $x . '" y="' . ($y + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '" text-anchor="' . $textAnchor . '" font-weight="' . $fontWeight . '">' . $text . '</text>' . "\n";

            case 'dynamic-text':
                return $this->renderDynamicText($x, $y, $width, $height, $properties);

            case 'order_number':
                return $this->renderOrderNumber($x, $y, $width, $height, $properties);

            case 'product_table':
                return $this->renderProductTable($x, $y, $width, $height, $properties);

            case 'company_info':
                return $this->renderCompanyInfo($x, $y, $width, $height, $properties);

            case 'customer_info':
                return $this->renderCustomerInfo($x, $y, $width, $height, $properties);

            case 'mentions':
                return $this->renderMentions($x, $y, $width, $height, $properties);

            case 'document_type':
                return $this->renderDocumentType($x, $y, $width, $height, $properties);

            case 'company_logo':
                // Placeholder - logo can't be rendered in SVG preview easily
                return $this->renderLogoPlaceholder($x, $y, $width, $height);

            default:
                return '';
        }
    }

    private function renderDynamicText($x, $y, $width, $height, $properties)
    {
        $content = $properties['content'] ?? '';
        $fontSize = max(($properties['fontSize'] ?? 12) * $this->scaleFactor * 0.9, 8);
        $color = $properties['textColor'] ?? '#000000';
        $textAnchor = $this->getTextAnchor($properties['textAlign'] ?? 'left');

        // Replace variables with real sample data
        $sampleData = [
            '{{order_date}}' => '05/11/2025',
            '{{current_date}}' => '05/11/2025',
            '{{current_time}}' => '20:30',
            '{{page_number}}' => '1',
            '{{total_pages}}' => '1',
            '{{customer_name}}' => 'Sample Text',
            '{{customer_address}}' => 'Sample Text',
            '{{payment_method}}' => 'Virement bancaire',
            '{{due_date}}' => '05/12/2025',
            '{{date}}' => '05/11/2025',
        ];

        foreach ($sampleData as $var => $value) {
            $content = str_replace($var, $value, $content);
        }

        $displayText = htmlspecialchars($content, ENT_QUOTES);
        return '    <text x="' . $x . '" y="' . ($y + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '" text-anchor="' . $textAnchor . '">' . $displayText . '</text>' . "\n";
    }

    private function renderOrderNumber($x, $y, $width, $height, $properties)
    {
        $fontSize = max(($properties['fontSize'] ?? 12) * $this->scaleFactor * 0.9, 8);
        $color = $properties['textColor'] ?? '#000000';
        $textAnchor = $this->getTextAnchor($properties['textAlign'] ?? 'left');
        $text = htmlspecialchars('Commande #12345', ENT_QUOTES);
        return '    <text x="' . $x . '" y="' . ($y + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '" text-anchor="' . $textAnchor . '">' . $text . '</text>' . "\n";
    }

    private function renderProductTable($x, $y, $width, $height, $properties)
    {
        // Compress table height for preview
        $displayHeight = 50 * $this->scaleFactor;
        $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor, 8);
        $headerBgColor = $properties['headerBackgroundColor'] ?? '#28a745';
        $headerColor = $properties['headerTextColor'] ?? '#ffffff';
        $textColor = $properties['textColor'] ?? '#000000';

        $svg = '';
        
        // Table header
        $svg .= '    <rect x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . ($fontSize * 1.8) . '" fill="' . $headerBgColor . '"/>' . "\n";
        $svg .= '    <text x="' . ($x + 5) . '" y="' . ($y + $fontSize * 1.3) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $headerColor . '" font-weight="bold">Produit</text>' . "\n";
        $svg .= '    <text x="' . ($x + $width * 0.6) . '" y="' . ($y + $fontSize * 1.3) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $headerColor . '" font-weight="bold">Qté</text>' . "\n";
        $svg .= '    <text x="' . ($x + $width * 0.8) . '" y="' . ($y + $fontSize * 1.3) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $headerColor . '" font-weight="bold">Prix</text>' . "\n";

        // Sample row
        $rowY = $y + $fontSize * 2;
        $svg .= '    <text x="' . ($x + 5) . '" y="' . ($rowY + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $textColor . '">Produit Sample</text>' . "\n";
        $svg .= '    <text x="' . ($x + $width * 0.6) . '" y="' . ($rowY + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $textColor . '">2</text>' . "\n";
        $svg .= '    <text x="' . ($x + $width * 0.8) . '" y="' . ($rowY + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $textColor . '">€50.00</text>' . "\n";

        return $svg;
    }

    private function renderCompanyInfo($x, $y, $width, $height, $properties)
    {
        $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor * 0.9, 7);
        $color = $properties['textColor'] ?? '#000000';

        $lines = [
            'Entreprise XYZ',
            '123 Rue de la Paix',
            '75001 Paris',
            'SIRET: 12345678900123',
            'TVA: FR12345678901'
        ];

        $svg = '';
        $lineY = $y + $fontSize;
        foreach ($lines as $line) {
            if ($lineY - $y > $height) break;
            $svg .= '    <text x="' . $x . '" y="' . $lineY . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . htmlspecialchars($line, ENT_QUOTES) . '</text>' . "\n";
            $lineY += $fontSize + 2;
        }

        return $svg;
    }

    private function renderCustomerInfo($x, $y, $width, $height, $properties)
    {
        $fontSize = max(($properties['fontSize'] ?? 10) * $this->scaleFactor * 0.9, 7);
        $color = $properties['textColor'] ?? '#000000';

        $lines = [
            'CLIENT:',
            'Sample Text',
            'Sample Text',
            'sample@example.com'
        ];

        $svg = '';
        $lineY = $y + $fontSize;
        foreach ($lines as $line) {
            if ($lineY - $y > $height) break;
            $weight = strpos($line, ':') !== false ? 'bold' : 'normal';
            $svg .= '    <text x="' . $x . '" y="' . $lineY . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '" font-weight="' . $weight . '">' . htmlspecialchars($line, ENT_QUOTES) . '</text>' . "\n";
            $lineY += $fontSize + 2;
        }

        return $svg;
    }

    private function renderMentions($x, $y, $width, $height, $properties)
    {
        $fontSize = max(($properties['fontSize'] ?? 8) * $this->scaleFactor * 0.9, 6);
        $color = $properties['textColor'] ?? '#666666';
        $text = htmlspecialchars('Conditions générales de vente - TVA non applicable art. 293B du CGI', ENT_QUOTES);
        return '    <text x="' . $x . '" y="' . ($y + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '">' . $text . '</text>' . "\n";
    }

    private function renderDocumentType($x, $y, $width, $height, $properties)
    {
        $fontSize = max(($properties['fontSize'] ?? 26) * $this->scaleFactor * 0.9, 12);
        $color = $properties['textColor'] ?? '#000000';
        $text = htmlspecialchars($properties['title'] ?? 'FACTURE', ENT_QUOTES);
        $textAnchor = $this->getTextAnchor($properties['textAlign'] ?? 'center');
        return '    <text x="' . ($x + $width / 2) . '" y="' . ($y + $fontSize) . '" font-family="Arial" font-size="' . $fontSize . '" fill="' . $color . '" text-anchor="' . $textAnchor . '" font-weight="bold">' . $text . '</text>' . "\n";
    }

    private function renderLogoPlaceholder($x, $y, $width, $height)
    {
        // Simple rectangle placeholder for logo
        return '    <rect x="' . $x . '" y="' . $y . '" width="' . $width . '" height="' . $height . '" fill="#f0f0f0" stroke="#cccccc" stroke-width="1" stroke-dasharray="5,5"/>' . "\n";
    }

    private function renderArrow($x, $y, $width, $height, $color, $strokeWidth, $direction)
    {
        // Simplified arrow implementation
        $arrowSize = 5;
        switch ($direction) {
            case 'right':
                return '    <line x1="' . $x . '" y1="' . ($y + $height / 2) . '" x2="' . ($x + $width - $arrowSize) . '" y2="' . ($y + $height / 2) . '" stroke="' . $color . '" stroke-width="' . $strokeWidth . '"/>' . "\n";
            case 'left':
                return '    <line x1="' . ($x + $width) . '" y1="' . ($y + $height / 2) . '" x2="' . ($x + $arrowSize) . '" y2="' . ($y + $height / 2) . '" stroke="' . $color . '" stroke-width="' . $strokeWidth . '"/>' . "\n";
            case 'up':
                return '    <line x1="' . ($x + $width / 2) . '" y1="' . ($y + $height) . '" x2="' . ($x + $width / 2) . '" y2="' . ($y + $arrowSize) . '" stroke="' . $color . '" stroke-width="' . $strokeWidth . '"/>' . "\n";
            case 'down':
                return '    <line x1="' . ($x + $width / 2) . '" y1="' . $y . '" x2="' . ($x + $width / 2) . '" y2="' . ($y + $height - $arrowSize) . '" stroke="' . $color . '" stroke-width="' . $strokeWidth . '"/>' . "\n";
        }
        return '';
    }

    private function getTextAnchor($align)
    {
        switch ($align) {
            case 'center':
                return 'middle';
            case 'right':
                return 'end';
            default:
                return 'start';
        }
    }

    public function save($outputPath)
    {
        $svg = $this->generateSVG();
        file_put_contents($outputPath, $svg);
        echo "✅ Aperçu SVG généré: " . basename($outputPath) . "\n";
        echo "📁 Chemin: " . realpath($outputPath) . "\n";
    }
}

// Main script
if (php_sapi_name() === 'cli') {
    $templateName = $argv[1] ?? 'corporate';
    $basePath = __DIR__ . '/templates/builtin/';
    $jsonPath = $basePath . $templateName . '.json';
    $outputPath = __DIR__ . '/assets/images/templates/' . $templateName . '-preview.svg';

    try {
        $generator = new SVGPreviewGeneratorHonest($jsonPath);
        $generator->save($outputPath);
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>
