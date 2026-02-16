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
        error_log('RectangleElementGenerator: Generating rectangle - ' . json_encode($this->element));
        
        $x = $this->getProperty('x', 0);
        $y = $this->getProperty('y', 0);
        $width = $this->getProperty('width', 100);
        $height = $this->getProperty('height', 100);
        
        // Background color (fillColor or backgroundColor)
        $backgroundColor = $this->getProperty('fillColor', $this->getProperty('backgroundColor', 'transparent'));
        $backgroundColor = $this->normalizeColor($backgroundColor);
        
        // Border
        $borderColor = $this->getProperty('strokeColor', $this->getProperty('borderColor', '#000000'));
        $borderColor = $this->normalizeColor($borderColor);
        $borderWidth = $this->getProperty('strokeWidth', $this->getProperty('borderWidth', 0));
        
        // Border radius
        $borderRadius = $this->getProperty('borderRadius', 0);
        
        $html = '<div class="pdf-element pdf-rectangle" ';
        $html .= 'style="position: absolute; ';
        $html .= 'left: ' . $x . 'px; ';
        $html .= 'top: ' . $y . 'px; ';
        $html .= 'width: ' . $width . 'px; ';
        $html .= 'height: ' . $height . 'px; ';
        $html .= 'background-color: ' . $backgroundColor . '; ';
        
        if ($borderWidth > 0) {
            $html .= 'border: ' . $borderWidth . 'px solid ' . $borderColor . '; ';
        }
        
        if ($borderRadius > 0) {
            $html .= 'border-radius: ' . $borderRadius . 'px; ';
        }
        
        $html .= 'box-sizing: border-box;">';
        $html .= '</div>';
        
        error_log('RectangleElementGenerator: Generated HTML - ' . $html);

        return $html;
    }
}
