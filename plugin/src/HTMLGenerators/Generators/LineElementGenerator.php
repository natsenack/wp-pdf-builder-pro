<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
/**
 * Line element generator
 */

namespace PDF_Builder\HTMLGenerators\Generators;

use PDF_Builder\HTMLGenerators\ElementGeneratorBase;

class LineElementGenerator extends ElementGeneratorBase
{
    public function generateHTML()
    {
        $x = $this->getProperty('x', 0);
        $y = $this->getProperty('y', 0);
        $width = $this->getProperty('width', 100);
        $height = $this->getProperty('height', 1);
        $color = $this->normalizeColor($this->getProperty('color', '#000000'));
        $lineWidth = $this->getProperty('lineWidth', 1);

        $html = '<div class="pdf-element pdf-line" ';
        $html .= 'style="position:absolute; left:' . $x . 'px; top:' . $y . 'px; ';
        $html .= 'width:' . $width . 'px; height:' . $height . 'px; ';
        $html .= 'background-color:' . $color . ';">';
        $html .= '</div>';

        return $html;
    }
}
