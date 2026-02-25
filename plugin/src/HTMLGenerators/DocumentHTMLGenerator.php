<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * Main document HTML generator
 * Orchestrates the generation of complete HTML from template data
 */

namespace PDF_Builder\HTMLGenerators;

class DocumentHTMLGenerator
{
    private $templateData;
    private $orderData;
    private $companyData;

    public function __construct($templateData = [], $orderData = [], $companyData = [])
    {
        $this->templateData = $templateData;
        $this->orderData = $orderData;
        $this->companyData = $companyData;
    }

    /**
     * Generate complete HTML document
     */
    public function generate()
    {
        $html = '<!DOCTYPE html>';
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<title>Document PDF</title>';
        $html .= $this->generateStyles();
        $html .= '</head>';
        $html .= '<body>';
        $html .= $this->generateContent();
        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }

    /**
     * Generate only content (for preview/modal)
     */
    public function generateContent()
    {
        $canvasWidth = $this->templateData['canvasWidth'] ?? 794;
        $canvasHeight = $this->templateData['canvasHeight'] ?? 1123;

        $html = '<div class="pdf-canvas" style="width:' . $canvasWidth . 'px; height:' . $canvasHeight . 'px; position:relative; background:white; margin:0 auto;">';

        $elements = $this->templateData['elements'] ?? [];
        $html .= ElementGeneratorFactory::generateMultiple($elements, $this->orderData, $this->companyData);

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate CSS styles
     */
    private function generateStyles()
    {
        $css  = '<style>';
        $css .= '* { box-sizing: border-box; }';
        $css .= 'body { margin: 0; padding: 20px; font-family: Arial, sans-serif; background: #f5f5f5; }';
        $css .= '.pdf-canvas { box-shadow: 0 2px 8px rgba(0,0,0,0.1); font-family: Arial, sans-serif; }';
        $css .= '.pdf-element { box-sizing: border-box; }';
        $css .= '.pdf-text { word-wrap: break-word; white-space: pre-wrap; }';
        $css .= '.pdf-company-info { display: flex; flex-direction: column; justify-content: flex-start; overflow: hidden; }';
        $css .= '.pdf-product-table table { font-size: 12px; }';
        $css .= '.pdf-product-table table th, .pdf-product-table table td { text-align: left; }';
        $css .= '.pdf-customer-info { line-height: 1.6; }';
        $css .= '</style>';

        return $css;
    }
}
