<?php
/**
 * Rectangle element generator
 */

namespace PDF_Builder\HTMLGenerators\Generators;

use PDF_Builder\HTMLGenerators\ElementGeneratorBase;

class RectangleElementGenerator extends ElementGeneratorBase
{
    public function generateHTML()
    {
        $styles = $this->getElementStyles();
        $styleAttr = $this->createStyleString($styles);

        $html = '<div class="pdf-element pdf-rectangle" ' . $styleAttr . '></div>';

        return $html;
    }
}
