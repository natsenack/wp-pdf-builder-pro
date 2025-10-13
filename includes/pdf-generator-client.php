<?php
/**
 * Alternative PDF : Génération côté client avec jsPDF
 * Plus fiable car évite les problèmes serveur PHP
 */

class PDF_Generator_Client {

    public function generate_client_script($elements) {
        // Générer un script JavaScript qui utilise jsPDF
        $script = $this->generate_jspdf_script($elements);

        // Retourner le script + HTML de base
        return [
            'script' => $script,
            'html' => $this->generate_preview_html($elements)
        ];
    }

    private function generate_jspdf_script($elements) {
        $js = "
// Inclure jsPDF (à ajouter via CDN ou localement)
// <script src='https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js'></script>

function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Configuration de base
    doc.setFont('helvetica');

";

        foreach ($elements as $element) {
            if (isset($element['text']) && !empty($element['text'])) {
                $x = $element['x'] * 0.264583; // px to mm
                $y = $element['y'] * 0.264583;
                $fontSize = $element['fontSize'] ?? 12;

                $js .= "    // Élément texte\n";
                $js .= "    doc.setFontSize({$fontSize});\n";

                if (isset($element['fontWeight']) && $element['fontWeight'] === 'bold') {
                    $js .= "    doc.setFont('helvetica', 'bold');\n";
                }

                if (isset($element['color'])) {
                    // Convertir couleur hex en RGB pour jsPDF
                    $color = $this->hex_to_rgb($element['color']);
                    $js .= "    doc.setTextColor({$color[0]}, {$color[1]}, {$color[2]});\n";
                }

                $text = addslashes($element['text']);
                $js .= "    doc.text('{$text}', {$x}, {$y});\n\n";
            }
        }

        $js .= "
    // Sauvegarder le PDF
    doc.save('document.pdf');
}

// Générer automatiquement ou sur clic bouton
document.addEventListener('DOMContentLoaded', function() {
    const generateBtn = document.getElementById('generate-pdf-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', generatePDF);
    }
});
";

        return $js;
    }

    private function generate_preview_html($elements) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Aperçu PDF</title>';
        $html .= '<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .pdf-preview { border: 1px solid #ccc; padding: 20px; background: #f9f9f9; position: relative; height: 297mm; width: 210mm; margin: 0 auto; }
            .element { position: absolute; }
            #generate-pdf-btn { margin: 20px; padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer; }
        </style>';
        $html .= '</head><body>';
        $html .= '<button id="generate-pdf-btn">Générer PDF</button>';
        $html .= '<div class="pdf-preview">';

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

        $html .= '</div></body></html>';
        return $html;
    }

    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }
}