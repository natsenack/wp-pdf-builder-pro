<?php
/**
 * Alternative PDF : Utilisation de DomPDF (plus moderne que TCPDF)
 * Installation : composer require dompdf/dompdf
 */

// Inclure DomPDF si disponible
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use Dompdf\Dompdf;
use Dompdf\Options;

class PDF_Generator_DomPDF {

    public function generate_from_elements($elements) {
        // Générer d'abord le HTML
        $html = $this->generate_html_from_elements($elements);

        // Vérifier si DomPDF est disponible
        if (!class_exists('Dompdf\\Dompdf')) {
            error_log('PDF Builder: DomPDF non disponible, fallback HTML');
            return $html;
        }

        try {
            // Configuration DomPDF
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', false); // Sécurité
            $options->set('defaultFont', 'Arial');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return $dompdf->output();
        } catch (Exception $e) {
            error_log('PDF Builder: Erreur DomPDF: ' . $e->getMessage());
            return $html; // Fallback HTML
        }
    }

    private function generate_html_from_elements($elements) {
        // Même logique que notre générateur actuel
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Document PDF</title>';
        $html .= '<style>body { font-family: Arial, sans-serif; margin: 0; padding: 20px; } .element { position: absolute; }</style>';
        $html .= '</head><body>';

        foreach ($elements as $element) {
            $style = sprintf('left: %dpx; top: %dpx; width: %dpx; height: %dpx; font-size: %dpx;',
                $element['x'], $element['y'], $element['width'], $element['height'], $element['fontSize']);

            if (isset($element['color'])) {
                $style .= ' color: ' . $element['color'] . ';';
            }

            if (isset($element['fontWeight']) && $element['fontWeight'] === 'bold') {
                $style .= ' font-weight: bold;';
            }

            $content = isset($element['text']) ? htmlspecialchars($element['text']) : '';
            $html .= sprintf('<div class="element" style="%s">%s</div>', $style, $content);
        }

        $html .= '</body></html>';
        return $html;
    }
}