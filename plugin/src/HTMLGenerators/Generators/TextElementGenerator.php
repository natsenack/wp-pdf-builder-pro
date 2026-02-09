<?php
/**
 * Text element generator
 */

namespace PDF_Builder\HTMLGenerators\Generators;

use PDF_Builder\HTMLGenerators\ElementGeneratorBase;

class TextElementGenerator extends ElementGeneratorBase
{
    public function generateHTML()
    {
        $content = $this->getProperty('content', '');
        $styles = $this->getElementStyles();
        $styleAttr = $this->createStyleString($styles);

        $html = '<div class="pdf-element pdf-text" ' . $styleAttr . '>';
        $html .= '<p style="margin:0; padding:0;">' . htmlspecialchars($content) . '</p>';
        $html .= '</div>';

        return $html;
    }
}
