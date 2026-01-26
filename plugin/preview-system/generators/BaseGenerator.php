<?php

namespace PDF_Builder\Generators;

use PDF_Builder\Interfaces\DataProviderInterface;
use PDF_Builder_Logger;

/**
 * Classe abstraite BaseGenerator
 * Définit la structure commune pour tous les générateurs d'aperçu
 */
abstract class BaseGenerator
{
    /** @var array Données du template */
    protected $template_data;
/** @var DataProviderInterface Fournisseur de données */
    protected $data_provider;
/** @var bool Indique si c'est un aperçu ou une génération finale */
    protected $is_preview;
/** @var array Configuration du générateur */
    protected $config;

    /**
     * Constructeur de base
     *
     * @param array $template_data Données du template
     * @param DataProviderInterface $data_provider Fournisseur de données
     * @param bool $is_preview Mode aperçu ou génération finale
     * @param array $config Configuration optionnelle
     */
    public function __construct(
        array $template_data,
        DataProviderInterface $data_provider,
        bool $is_preview = true,
        array $config = []
    ) {
        $this->template_data = $template_data;
        $this->data_provider = $data_provider;
        $this->is_preview = $is_preview;
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->initialize();
    }

    /**
     * Configuration par défaut du générateur
     *
     * @return array Configuration par défaut
     */
    protected function getDefaultConfig(): array
    {
        return [
            'format' => 'A4',
            'orientation' => 'portrait',
            'dpi' => 96,
            'quality' => 90,
            'enable_remote' => false,
            'temp_dir' => null
        ];
    }

    /**
     * Initialisation du générateur (à implémenter dans les classes enfants)
     */
    abstract protected function initialize(): void;
/**
     * Génère l'aperçu selon le type demandé
     *
     * @param string $output_type Type de sortie ('pdf', 'png', 'jpg')
     * @return mixed Résultat de la génération
     */
    abstract public function generate(string $output_type = 'pdf');

    /**
     * Valide les données du template
     *
     * @return bool true si valide, false sinon
     */
    public function validateTemplate(): bool
    {
        // Vérifier si les éléments sont directement dans template_data (nouvelle structure)
        // ou dans template_data['template'] (ancienne structure)
        $elements = null;
        if (isset($this->template_data['elements']) && is_array($this->template_data['elements'])) {
            $elements = $this->template_data['elements'];
        } elseif (isset($this->template_data['template']['elements']) && is_array($this->template_data['template']['elements'])) {
            $elements = $this->template_data['template']['elements'];
        } else {
            $this->logError('Template elements missing or not an array');
            return false;
        }

        if (empty($elements)) {
            $this->logError('Template elements array is empty');
            return false;
        }

        return true;
    }

    /**
     * Génère le HTML du template avec les données injectées
     *
     * @return string HTML généré
     */
    protected function generateHTML(): string
    {
        $html = $this->getBaseHTML();
// Injection des styles CSS
        $html = str_replace('{{CSS_STYLES}}', $this->generateCSS(), $html);
// Injection du contenu
        $html = str_replace('{{CONTENT}}', $this->generateContent(), $html);
        return $html;
    }

    /**
     * Génère le HTML de base
     *
     * @return string HTML de base
     */
    protected function getBaseHTML(): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>{{CSS_STYLES}}</style>
        </head>
        <body>
            {{CONTENT}}
        </body>
        </html>';
    }

    /**
     * Génère les styles CSS du template
     *
     * @return string CSS généré
     */
    protected function generateCSS(): string
    {
        $css = '
            body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
            .pdf-element { position: absolute; }
            .text-element { white-space: pre-wrap; }
            .image-element { max-width: 100%; height: auto; }
        ';
        // Styles personnalisés du template si présents (nouvelle ou ancienne structure)
        $styles = '';
        if (isset($this->template_data['styles'])) {
            $styles = $this->template_data['styles'];
        } elseif (isset($this->template_data['template']['styles'])) {
            $styles = $this->template_data['template']['styles'];
        }
        
        if (!empty($styles)) {
            $css .= $styles;
        }

        return $css;
    }

    /**
     * Génère le contenu HTML des éléments
     *
     * @return string Contenu HTML
     */
    protected function generateContent(): string
    {
        $content = '';
        
        // Logging détaillé de la structure du template - TOUJOURS ACTIF
        error_log('[PDF GENERATOR] generateContent - STARTING CONTENT GENERATION');
        error_log('[PDF GENERATOR] generateContent - template_data keys: ' . implode(', ', array_keys($this->template_data)));
        error_log('[PDF GENERATOR] generateContent - isset elements directly: ' . (isset($this->template_data['elements']) ? 'YES' : 'NO'));
        error_log('[PDF GENERATOR] generateContent - isset template.elements: ' . (isset($this->template_data['template']['elements']) ? 'YES' : 'NO'));
        
        if (isset($this->template_data['elements'])) {
            error_log('[PDF GENERATOR] generateContent - elements count (direct): ' . count($this->template_data['elements']));
            if (!empty($this->template_data['elements'])) {
                error_log('[PDF GENERATOR] generateContent - first element: ' . print_r($this->template_data['elements'][0], true));
            }
        }
        
        // Déterminer où sont les éléments (nouvelle ou ancienne structure)
        $elements = null;
        if (isset($this->template_data['elements']) && is_array($this->template_data['elements'])) {
            $elements = $this->template_data['elements'];
            error_log('[PDF GENERATOR] generateContent - USING DIRECT ELEMENTS');
        } elseif (isset($this->template_data['template']['elements']) && is_array($this->template_data['template']['elements'])) {
            $elements = $this->template_data['template']['elements'];
            error_log('[PDF GENERATOR] generateContent - USING NESTED ELEMENTS');
        }
        
        if (!$elements) {
            error_log('[PDF GENERATOR] generateContent - NO ELEMENTS FOUND, returning empty content');
            error_log('[PDF GENERATOR] generateContent - Full template_data: ' . print_r($this->template_data, true));
            return $content;
        }

        error_log('[PDF GENERATOR] generateContent - FOUND ELEMENTS, processing ' . count($elements) . ' elements');

        foreach ($elements as $index => $element) {
            // Convert stdClass to array if necessary
            $elementArray = is_array($element) ? $element : (array) $element;
            error_log('[PDF GENERATOR] generateContent - Processing element ' . $index . ' type: ' . ($elementArray['type'] ?? 'unknown'));
            $html = $this->renderElement($elementArray);
            error_log('[PDF GENERATOR] generateContent - Element ' . $index . ' rendered HTML length: ' . strlen($html));
            if (strlen($html) > 0) {
                error_log('[PDF GENERATOR] generateContent - Element ' . $index . ' HTML preview: ' . substr($html, 0, 100));
            }
            $content .= $html;
        }

        error_log('[PDF GENERATOR] generateContent - FINAL CONTENT LENGTH: ' . strlen($content));
        if (strlen($content) > 0) {
            error_log('[PDF GENERATOR] generateContent - FINAL CONTENT PREVIEW: ' . substr($content, 0, 200));
        } else {
            error_log('[PDF GENERATOR] generateContent - FINAL CONTENT IS EMPTY!');
        }
        
        return $content;
    }

    /**
     * Rend un élément individuel
     *
     * @param array $element Données de l'élément
     * @return string HTML de l'élément
     */
    protected function renderElement(array $element): string
    {
        $type = $element['type'] ?? 'unknown';
        
        error_log('[PDF GENERATOR] renderElement - processing element type: ' . $type);
        error_log('[PDF GENERATOR] renderElement - element data keys: ' . implode(', ', array_keys($element)));
        
        $method = 'render' . ucfirst($type) . 'Element';
        if (method_exists($this, $method)) {
            error_log('[PDF GENERATOR] renderElement - calling method: ' . $method);
            $result = $this->$method($element);
            error_log('[PDF GENERATOR] renderElement - rendered HTML length: ' . strlen($result));
            if (strlen($result) > 0) {
                error_log('[PDF GENERATOR] renderElement - HTML preview: ' . substr($result, 0, 100));
            }
            return $result;
        }

        error_log('[PDF GENERATOR] renderElement - Unknown element type: ' . $type);
        return '';
    }

    /**
     * Rend un élément texte
     *
     * @param array $element Données de l'élément texte
     * @return string HTML de l'élément texte
     */
    protected function renderTextElement(array $element): string
    {
        $text = $element['text'] ?? '';
        $text = $this->injectVariables($text);
        $style = $this->buildElementStyle($element);
        return "<div class=\"pdf-element text-element\" style=\"{$style}\">{$text}</div>";
    }

    /**
     * Rend un élément image
     *
     * @param array $element Données de l'élément image
     * @return string HTML de l'élément image
     */
    protected function renderImageElement(array $element): string
    {
        $src = $element['src'] ?? '';
        $alt = $element['alt'] ?? '';
        if (empty($src)) {
            return '';
        }

        $style = $this->buildElementStyle($element);
        return "<img class=\"pdf-element image-element\" src=\"{$src}\" alt=\"{$alt}\" style=\"{$style}\" />";
    }

    /**
     * Rend un élément rectangle
     *
     * @param array $element Données de l'élément rectangle
     * @return string HTML de l'élément rectangle
     */
    protected function renderRectangleElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $style .= 'border: 1px solid #000;';
        return "<div class=\"pdf-element rectangle-element\" style=\"{$style}\"></div>";
    }

    /**
     * Rend un élément customer_info
     */
    protected function renderCustomerInfoElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $content = "John Doe\n123 Main Street\nParis, France\njohn@example.com\n+33 1 23 45 67 89";
        return "<div class=\"pdf-element\" style=\"{$style}\">{$content}</div>";
    }

    /**
     * Rend un élément company_info
     */
    protected function renderCompanyInfoElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $content = "Ma Société SARL\n123 Avenue des Champs\n75008 Paris, France\nSIRET: 123 456 789 00012\ncontact@masociete.fr";
        return "<div class=\"pdf-element\" style=\"{$style}\">{$content}</div>";
    }

    /**
     * Rend un élément order_number
     */
    protected function renderOrderNumberElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $content = "#12345";
        return "<div class=\"pdf-element\" style=\"{$style}\">{$content}</div>";
    }

    /**
     * Rend un élément company_logo
     */
    protected function renderCompanyLogoElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $src = $element['src'] ?? '';
        if (empty($src)) {
            return "<div class=\"pdf-element image-element\" style=\"{$style}; background-color: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #666;\">🏢 Logo</div>";
        }
        return "<img class=\"pdf-element image-element\" src=\"{$src}\" style=\"{$style}; max-width: 100%; height: auto;\" alt=\"Logo\" />";
    }

    /**
     * Rend un élément product_table
     */
    protected function renderProductTableElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $content = "<table style='width: 100%; border-collapse: collapse;'>
            <thead>
                <tr style='background-color: #f5f5f5;'>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Produit</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: center;'>Qté</th>
                    <th style='border: 1px solid #ddd; padding: 8px; text-align: right;'>Prix</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style='border: 1px solid #ddd; padding: 8px;'>Produit Exemple</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>2</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>€50.00</td>
                </tr>
                <tr style='background-color: #fafafa;'>
                    <td style='border: 1px solid #ddd; padding: 8px;'>Autre Produit</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: center;'>1</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>€25.00</td>
                </tr>
            </tbody>
            <tfoot>
                <tr style='background-color: #f5f5f5; font-weight: bold;'>
                    <td colspan='2' style='border: 1px solid #ddd; padding: 8px; text-align: right;'>Total:</td>
                    <td style='border: 1px solid #ddd; padding: 8px; text-align: right;'>€75.00</td>
                </tr>
            </tfoot>
        </table>";
        return "<div class=\"pdf-element table-element\" style=\"{$style}\">{$content}</div>";
    }

    /**
     * Rend un élément woocommerce_order_date
     */
    protected function renderWoocommerceOrderDateElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $content = date('d/m/Y');
        return "<div class=\"pdf-element\" style=\"{$style}\">{$content}</div>";
    }

    /**
     * Rend un élément document_type
     */
    protected function renderDocumentTypeElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $title = $element['title'] ?? 'Facture';
        return "<div class=\"pdf-element\" style=\"{$style}\">{$title}</div>";
    }

    /**
     * Rend un élément dynamic_text
     */
    protected function renderDynamicTextElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $text = $element['text'] ?? $element['textTemplate'] ?? 'Texte dynamique';
        $text = $this->injectVariables($text);
        return "<div class=\"pdf-element text-element\" style=\"{$style}\">{$text}</div>";
    }

    /**
     * Rend un élément mentions
     */
    protected function renderMentionsElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $mentions = [];
        if ($element['showEmail'] ?? false) $mentions[] = 'contact@example.com';
        if ($element['showPhone'] ?? false) $mentions[] = '+33 1 23 45 67 89';
        if ($element['showSiret'] ?? false) $mentions[] = 'SIRET: 123 456 789 00012';
        if ($element['showVat'] ?? false) $mentions[] = 'TVA: FR123456789';
        $separator = $element['separator'] ?? ' • ';
        $content = implode($separator, $mentions);
        return "<div class=\"pdf-element\" style=\"{$style}\">{$content}</div>";
    }

    /**
     * Rend un élément line
     */
    protected function renderLineElement(array $element): string
    {
        $style = $this->buildElementStyle($element);
        $strokeColor = $element['strokeColor'] ?? '#000000';
        $strokeWidth = $element['strokeWidth'] ?? 2;
        $style .= "border-top: {$strokeWidth}px solid {$strokeColor}; height: 0;";
        return "<div class=\"pdf-element line\" style=\"{$style}\"></div>";
    }

    /**
     * Construit le style CSS d'un élément
     *
     * @param array $element Données de l'élément
     * @return string Style CSS
     */
    protected function buildElementStyle(array $element): string
    {
        $style = '';
        if (isset($element['x'])) {
            $style .= "left: {$element['x']}px; ";
        }
        if (isset($element['y'])) {
            $style .= "top: {$element['y']}px; ";
        }
        if (isset($element['width'])) {
            $style .= "width: {$element['width']}px; ";
        }
        if (isset($element['height'])) {
            $style .= "height: {$element['height']}px; ";
        }
        if (isset($element['color'])) {
            $style .= "color: {$element['color']}; ";
        }
        if (isset($element['fontSize'])) {
            $style .= "font-size: {$element['fontSize']}px; ";
        }
        if (isset($element['fontWeight'])) {
            $style .= "font-weight: {$element['fontWeight']}; ";
        }
        if (isset($element['fontFamily'])) {
            $style .= "font-family: {$element['fontFamily']}; ";
        }
        if (isset($element['textAlign'])) {
            $style .= "text-align: {$element['textAlign']}; ";
        }
        if (isset($element['backgroundColor'])) {
            $style .= "background-color: {$element['backgroundColor']}; ";
        }
        if (isset($element['borderColor'])) {
            $style .= "border-color: {$element['borderColor']}; ";
        }
        if (isset($element['borderWidth'])) {
            $style .= "border-width: {$element['borderWidth']}px; ";
        }
        if (isset($element['borderRadius'])) {
            $style .= "border-radius: {$element['borderRadius']}px; ";
        }
        if (isset($element['rotation'])) {
            $style .= "transform: rotate({$element['rotation']}deg); ";
        }
        if (isset($element['lineHeight'])) {
            $style .= "line-height: {$element['lineHeight']}; ";
        }
        if (isset($element['textDecoration'])) {
            $style .= "text-decoration: {$element['textDecoration']}; ";
        }
        if (isset($element['fontStyle'])) {
            $style .= "font-style: {$element['fontStyle']}; ";
        }
        return $style;
    }

    /**
     * Injecte les variables dynamiques dans le texte
     *
     * @param string $text Texte avec variables
     * @return string Texte avec variables remplacées
     */
    protected function injectVariables(string $text): string
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $logger = PDF_Builder_Logger::get_instance();
            // $logger->debug_log('injectVariables - input text: ' . $text);
        }
        
        // Recherche des variables {{variable}}
        preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $logger = PDF_Builder_Logger::get_instance();
            // $logger->debug_log('injectVariables - found variables: ' . print_r($matches[1], true));
        }
        
        foreach ($matches[1] as $variable) {
            $value = $this->data_provider->getVariableValue(trim($variable));
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $logger = PDF_Builder_Logger::get_instance();
                // $logger->debug_log('injectVariables - variable: ' . $variable . ' -> value: ' . $value);
            }
            
            $text = str_replace("{{{$variable}}}", $value, $text);
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $logger = PDF_Builder_Logger::get_instance();
            // $logger->debug_log('injectVariables - output text: ' . $text);
        }
        
        return $text;
    }

    /**
     * Log une erreur
     *
     * @param string $message Message d'erreur
     */
    protected function logError(string $message): void
    {
    }

    /**
     * Log un avertissement
     *
     * @param string $message Message d'avertissement
     */
    protected function logWarning(string $message): void
    {
    }

    /**
     * Log une information
     *
     * @param string $message Message d'information
     */
    protected function logInfo(string $message): void
    {
    }
}
