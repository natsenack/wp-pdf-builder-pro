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
        error_log('CircleElementGenerator: Generating circle - ' . json_encode($this->element));
        
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
        
        $html = '<div class="pdf-element pdf-circle" ';
        $html .= 'style="position: absolute; ';
        $html .= 'left: ' . $x . 'px; ';
        $html .= 'top: ' . $y . 'px; ';
        $html .= 'width: ' . $width . 'px; ';
        $html .= 'height: ' . $height . 'px; ';
        $html .= 'background-color: ' . $backgroundColor . '; ';
        $html .= 'border-radius: 50%; ';
        
        if ($borderWidth > 0) {
            $html .= 'border: ' . $borderWidth . 'px solid ' . $borderColor . '; ';
        }
        
        $html .= 'box-sizing: border-box;">';
        $html .= '</div>';
        
        error_log('CircleElementGenerator: Generated HTML - ' . $html);

        return $html;
    }
}
