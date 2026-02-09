<?php
/**
 * Image element generator
 */

namespace PDF_Builder\HTMLGenerators\Generators;

use PDF_Builder\HTMLGenerators\ElementGeneratorBase;

class ImageElementGenerator extends ElementGeneratorBase
{
    public function generateHTML()
    {
        $styles = $this->getElementStyles();
        $styleAttr = $this->createStyleString($styles);
        
        $imageUrl = $this->getProperty('imageUrl', '');
        $altText = $this->getProperty('altText', 'Image');

        $html = '<img class="pdf-element pdf-image" ' . $styleAttr . ' ';
        $html .= 'src="' . esc_url($imageUrl) . '" ';
        $html .= 'alt="' . htmlspecialchars($altText) . '" />';

        return $html;
    }
}
