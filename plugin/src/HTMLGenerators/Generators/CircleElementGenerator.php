<?php
/**
 * Circle element generator
 */

namespace PDF_Builder\HTMLGenerators\Generators;

use PDF_Builder\HTMLGenerators\ElementGeneratorBase;

class CircleElementGenerator extends ElementGeneratorBase
{
    public function generateHTML()
    {
        $styles = $this->getElementStyles();
        
        // Circle needs border-radius 50%
        $styles['border-radius'] = '50%';
        
        // Background color (fillColor or backgroundColor)
        $backgroundColor = $this->getProperty('fillColor', $this->getProperty('backgroundColor', 'transparent'));
        if ($backgroundColor !== 'transparent') {
            $styles['background-color'] = $this->normalizeColor($backgroundColor);
        }
        
        // Border
        $borderColor = $this->getProperty('strokeColor', $this->getProperty('borderColor', '#000000'));
        $borderWidth = $this->getProperty('strokeWidth', $this->getProperty('borderWidth', 1));
        
        if ($borderWidth > 0) {
            $styles['border'] = $borderWidth . 'px solid ' . $this->normalizeColor($borderColor);
        }
        
        // Box sizing
        $styles['box-sizing'] = 'border-box';
        
        $styleAttr = $this->createStyleString($styles);

        $html = '<div class="pdf-element pdf-circle" ' . $styleAttr . '></div>';

        return $html;
    }
}
