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
            body { margin: 0; padding: 0; font-family: Arial, sans-serif; position: relative; }
            .pdf-element { 
                position: absolute; 
                margin: 0; 
                padding: 0; 
                box-sizing: border-box;
            }
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
        
        error_log('[PDF GENERATOR] ===== generateContent - STARTING CONTENT GENERATION =====');
        error_log('[PDF GENERATOR] generateContent - template_data keys: ' . implode(', ', array_keys($this->template_data)));
        error_log('[PDF GENERATOR] generateContent - isset elements directly: ' . (isset($this->template_data['elements']) ? 'YES' : 'NO'));
        error_log('[PDF GENERATOR] generateContent - isset template.elements: ' . (isset($this->template_data['template']['elements']) ? 'YES' : 'NO'));
        
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
            return $content;
        }

        error_log('[PDF GENERATOR] generateContent - FOUND ELEMENTS, processing ' . count($elements) . ' elements');

        foreach ($elements as $index => $element) {
            // Convert stdClass to array if necessary
            $elementArray = is_array($element) ? $element : (array) $element;
            error_log('[PDF GENERATOR] generateContent - Processing element ' . $index . ' type: ' . ($elementArray['type'] ?? 'unknown'));
            $html = $this->renderElement($elementArray);
            error_log('[PDF GENERATOR] generateContent - Element ' . $index . ' rendered HTML length: ' . strlen($html));
            $content .= $html;
        }

        error_log('[PDF GENERATOR] generateContent - FINAL CONTENT LENGTH: ' . strlen($content));
        
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
        
        error_log("[PDF] Rendering element type: {$type}");
        error_log("[PDF] Element keys: " . implode(', ', array_keys($element)));
        
        // Convert underscore notation to camelCase (e.g., customer_info -> CustomerInfo)
        $camelCaseType = $this->convertToCamelCase($type);
        $method = 'render' . $camelCaseType . 'Element';
        
        if (method_exists($this, $method)) {
            error_log("[PDF] Calling method: {$method}");
            $result = $this->$method($element);
            error_log("[PDF] Rendered HTML length: " . strlen($result));
            return $result;
        }

        error_log("[PDF] Unknown element type: {$type}");
        return '';
    }

    /**
     * Convertit une chaîne avec underscores en camelCase
     *
     * @param string $string Chaîne à convertir
     * @return string Chaîne en camelCase
     */
    protected function convertToCamelCase(string $string): string
    {
        $parts = explode('_', $string);
        $camelCase = '';
        foreach ($parts as $part) {
            $camelCase .= ucfirst($part);
        }
        return $camelCase;
    }

    /**
     * Rend un élément texte
     *
     * @param array $element Données de l'élément texte
     * @return string HTML de l'élément texte
     */
    protected function renderTextElement(array $element): string
    {
        error_log('[PDF] Text element - keys: ' . implode(', ', array_keys($element)) . ', text: ' . ($element['text'] ?? 'empty'));
        
        // Use real element data with flexible property names
        $text = $element['text'] ?? $element['content'] ?? $element['value'] ?? '';
        
        // Also check for nested properties
        if (empty($text) && isset($element['properties']['text'])) {
            $text = $element['properties']['text'];
        }
        if (empty($text) && isset($element['properties']['content'])) {
            $text = $element['properties']['content'];
        }
        if (empty($text) && isset($element['properties']['value'])) {
            $text = $element['properties']['value'];
        }
        
        $text = $this->injectVariables($text);
        $style = $this->buildElementStyle($element);
        error_log('[PDF] Text element - FINAL text: "' . $text . '", style length: ' . strlen($style));
        return "<div class=\"pdf-element text-element\" data-element-type=\"text\" style=\"{$style}\">{$text}</div>";
    }

    /**
     * Rend un élément image
     *
     * @param array $element Données de l'élément image
     * @return string HTML de l'élément image
     */
    protected function renderImageElement(array $element): string
    {
        error_log('[PDF] Image element - keys: ' . implode(', ', array_keys($element)) . ', src: ' . ($element['src'] ?? 'empty'));
        
        // Use real element data with flexible property names
        $src = $element['src'] ?? $element['url'] ?? $element['imageUrl'] ?? '';
        $alt = $element['alt'] ?? $element['altText'] ?? $element['title'] ?? '';
        
        // Also check for nested properties
        if (empty($src) && isset($element['properties']['src'])) {
            $src = $element['properties']['src'];
        }
        if (empty($src) && isset($element['properties']['url'])) {
            $src = $element['properties']['url'];
        }
        if (empty($src) && isset($element['properties']['imageUrl'])) {
            $src = $element['properties']['imageUrl'];
        }
        
        if (empty($alt) && isset($element['properties']['alt'])) {
            $alt = $element['properties']['alt'];
        }
        if (empty($alt) && isset($element['properties']['altText'])) {
            $alt = $element['properties']['altText'];
        }
        if (empty($alt) && isset($element['properties']['title'])) {
            $alt = $element['properties']['title'];
        }
        
        if (empty($src)) {
            return '';
        }

        $style = $this->buildElementStyle($element);
        error_log('[PDF] Image element - FINAL src: "' . $src . '", alt: "' . $alt . '", style length: ' . strlen($style));
        return "<img class=\"pdf-element image-element\" data-element-type=\"image\" src=\"{$src}\" alt=\"{$alt}\" style=\"{$style}\" />";
    }

    /**
     * Rend un élément rectangle
     *
     * @param array $element Données de l'élément rectangle
     * @return string HTML de l'élément rectangle
     */
    protected function renderRectangleElement(array $element): string
    {
        error_log('[PDF] Rectangle element - keys: ' . implode(', ', array_keys($element)));
        
        $style = $this->buildElementStyle($element);
        
        // Use real element data with flexible property names
        $borderColor = $element['borderColor'] ?? $element['strokeColor'] ?? $element['color'] ?? '#000000';
        $borderWidth = $element['borderWidth'] ?? $element['strokeWidth'] ?? $element['width'] ?? 1;
        $backgroundColor = $element['backgroundColor'] ?? $element['fillColor'] ?? $element['fill'] ?? 'transparent';
        
        // Also check for nested properties
        if (isset($element['properties'])) {
            $borderColor = $element['properties']['borderColor'] ?? $element['properties']['strokeColor'] ?? $element['properties']['color'] ?? $borderColor;
            $borderWidth = $element['properties']['borderWidth'] ?? $element['properties']['strokeWidth'] ?? $element['properties']['width'] ?? $borderWidth;
            $backgroundColor = $element['properties']['backgroundColor'] ?? $element['properties']['fillColor'] ?? $element['properties']['fill'] ?? $backgroundColor;
        }
        
        $style .= "border: {$borderWidth}px solid {$borderColor}; background-color: {$backgroundColor};";
        error_log('[PDF] Rectangle element - FINAL borderColor: "' . $borderColor . '", borderWidth: ' . $borderWidth . ', backgroundColor: "' . $backgroundColor . '"');
        return "<div class=\"pdf-element rectangle-element\" data-element-type=\"rectangle\" style=\"{$style}\"></div>";
    }

    /**
     * Rend un élément customer_info
     * Version: 2026-01-26 12:25
     */
    protected function renderCustomerInfoElement(array $element): string
    {
        error_log('[PDF] Customer info - keys: ' . implode(', ', array_keys($element)));
        
        $style = $this->buildElementStyle($element);
        
        // Récupérer les données depuis le data provider pour l'aperçu
        $customerName = $this->data_provider->getVariableValue('customer_full_name');
        $customerAddress = $this->data_provider->getVariableValue('billing_address_1') . ', ' . 
                          $this->data_provider->getVariableValue('billing_postcode') . ' ' . 
                          $this->data_provider->getVariableValue('billing_city') . ', ' . 
                          $this->data_provider->getVariableValue('billing_country');
        $customerEmail = $this->data_provider->getVariableValue('billing_email');
        $customerPhone = $this->data_provider->getVariableValue('billing_phone');
        
        // Fallback vers les propriétés de l'élément si data provider ne fournit rien
        if (empty($customerName) || $customerName === '{{customer_full_name}}') {
            $customerName = $element['customerName'] ?? $element['name'] ?? $element['customer_name'] ?? $element['fullName'] ?? 'Client';
        }
        if (empty($customerAddress) || strpos($customerAddress, '{{') !== false) {
            $customerAddress = $element['customerAddress'] ?? $element['address'] ?? $element['customer_address'] ?? '';
        }
        if (empty($customerEmail) || strpos($customerEmail, '{{') !== false) {
            $customerEmail = $element['customerEmail'] ?? $element['email'] ?? $element['customer_email'] ?? '';
        }
        if (empty($customerPhone) || strpos($customerPhone, '{{') !== false) {
            $customerPhone = $element['customerPhone'] ?? $element['phone'] ?? $element['customer_phone'] ?? '';
        }
        
        $content = $customerName;
        if (!empty($customerAddress)) $content .= "\n" . $customerAddress;
        if (!empty($customerEmail)) $content .= "\n" . $customerEmail;
        if (!empty($customerPhone)) $content .= "\n" . $customerPhone;
        
        error_log('[PDF] Customer info - FINAL name: "' . $customerName . '", address: "' . $customerAddress . '", email: "' . $customerEmail . '", phone: "' . $customerPhone . '"');
        return "<div class=\"pdf-element\" data-element-type=\"customer_info\" style=\"{$style}\">" . nl2br($content) . "</div>";
    }

    /**
     * Construit le style CSS d'un élément
     *
     * @param array $element Données de l'élément
     * @return string Style CSS
     */
    protected function buildElementStyle(array $element): string
    {
        $style = 'position: absolute; ';

        // Helper function to normalize CSS values
        $normalizeCssValue = function($value, $unit = '') {
            if (is_numeric($value) && !empty($unit)) {
                return $value . $unit;
            }
            if (is_string($value) && !empty($unit) && !preg_match('/\d+\s*' . preg_quote($unit, '/') . '$/', $value)) {
                return $value . $unit;
            }
            return $value;
        };

        // Adjust positioning to compensate for offset - subtract 20px from all values only for PDF generation
        // For HTML preview, use exact positions as defined in the canvas
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;

        if (!$this->is_preview) {
            // Apply offset compensation only for PDF generation
            $x -= 20;
            $y -= 20;
        }

        // Allow negative values for proper positioning
        $style .= "left: {$x}px; ";
        $style .= "top: {$y}px; ";

        // Debug log for positioning
        if (isset($element['x']) || isset($element['y'])) {
            error_log("[PDF] Element positioning - original x: " . ($element['x'] ?? 'not set') . ", y: " . ($element['y'] ?? 'not set') . " | adjusted x: {$x}, y: {$y} | is_preview: " . ($this->is_preview ? 'true' : 'false'));
        }
        if (isset($element['width'])) {
            $style .= "width: " . $normalizeCssValue($element['width'], 'px') . "; ";
        }
        if (isset($element['height'])) {
            $style .= "height: " . $normalizeCssValue($element['height'], 'px') . "; ";
        }
        if (isset($element['color']) && !empty($element['color'])) {
            $style .= "color: {$element['color']}; ";
        }
        if (isset($element['textColor']) && !empty($element['textColor'])) {
            $style .= "color: {$element['textColor']}; ";
        }
        // Fallback for hyphenated property names
        if (!isset($element['color']) && !isset($element['textColor']) && isset($element['text-color']) && !empty($element['text-color'])) {
            $style .= "color: {$element['text-color']}; ";
        }
        if (isset($element['fontSize']) && !empty($element['fontSize'])) {
            $style .= "font-size: " . $normalizeCssValue($element['fontSize'], 'px') . "; ";
        }
        // Fallback for hyphenated property names
        if (!isset($element['fontSize']) && isset($element['font-size']) && !empty($element['font-size'])) {
            $style .= "font-size: " . $normalizeCssValue($element['font-size'], 'px') . "; ";
        }
        if (isset($element['fontWeight']) && !empty($element['fontWeight'])) {
            $style .= "font-weight: {$element['fontWeight']}; ";
        }
        // Fallback for hyphenated property names
        if (!isset($element['fontWeight']) && isset($element['font-weight']) && !empty($element['font-weight'])) {
            $style .= "font-weight: {$element['font-weight']}; ";
        }
        if (isset($element['fontFamily']) && !empty($element['fontFamily'])) {
            $style .= "font-family: {$element['fontFamily']}; ";
        }
        // Fallback for hyphenated property names
        if (!isset($element['fontFamily']) && isset($element['font-family']) && !empty($element['font-family'])) {
            $style .= "font-family: {$element['font-family']}; ";
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
            $style .= "border-width: " . $normalizeCssValue($element['borderWidth'], 'px') . "; ";
        }
        if (isset($element['borderRadius'])) {
            $style .= "border-radius: " . $normalizeCssValue($element['borderRadius'], 'px') . "; ";
        }

        // Build combined transform property
        $transforms = [];
        if (isset($element['rotation'])) {
            $transforms[] = "rotate({$element['rotation']}deg)";
        }
        if (isset($element['scale'])) {
            $transforms[] = "scale({$element['scale']})";
        }
        if (isset($element['translateX']) || isset($element['translateY'])) {
            $translateX = $element['translateX'] ?? 0;
            $translateY = $element['translateY'] ?? 0;
            $transforms[] = "translate({$translateX}px, {$translateY}px)";
        }
        if (isset($element['skew'])) {
            $transforms[] = "skew({$element['skew']}deg)";
        }
        if (!empty($transforms)) {
            $style .= "transform: " . implode(' ', $transforms) . "; ";
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
        if (isset($element['fontStretch'])) {
            $style .= "font-stretch: {$element['fontStretch']}; ";
        }
        if (isset($element['fontVariant'])) {
            $style .= "font-variant: {$element['fontVariant']}; ";
        }
        if (isset($element['padding'])) {
            $style .= "padding: {$element['padding']}px; ";
        }
        if (isset($element['margin'])) {
            $style .= "margin: {$element['margin']}px; ";
        }
        if (isset($element['borderStyle'])) {
            $style .= "border-style: {$element['borderStyle']}; ";
        }
        if (isset($element['opacity'])) {
            $style .= "opacity: {$element['opacity']}; ";
        }
        if (isset($element['zIndex'])) {
            $style .= "z-index: {$element['zIndex']}; ";
        }
        if (isset($element['boxShadow'])) {
            $style .= "box-shadow: {$element['boxShadow']}; ";
        }
        if (isset($element['textShadow'])) {
            $style .= "text-shadow: {$element['textShadow']}; ";
        }
        if (isset($element['letterSpacing'])) {
            $style .= "letter-spacing: {$element['letterSpacing']}px; ";
        }
        if (isset($element['wordSpacing'])) {
            $style .= "word-spacing: {$element['wordSpacing']}px; ";
        }
        if (isset($element['verticalAlign'])) {
            $style .= "vertical-align: {$element['verticalAlign']}; ";
        }
        if (isset($element['display'])) {
            $style .= "display: {$element['display']}; ";
        }
        if (isset($element['overflow'])) {
            $style .= "overflow: {$element['overflow']}; ";
        }
        if (isset($element['whiteSpace'])) {
            $style .= "white-space: {$element['whiteSpace']}; ";
        }
        if (isset($element['backgroundImage'])) {
            $style .= "background-image: {$element['backgroundImage']}; ";
        }
        if (isset($element['backgroundSize'])) {
            $style .= "background-size: {$element['backgroundSize']}; ";
        }
        if (isset($element['backgroundPosition'])) {
            $style .= "background-position: {$element['backgroundPosition']}; ";
        }
        if (isset($element['backgroundRepeat'])) {
            $style .= "background-repeat: {$element['backgroundRepeat']}; ";
        }
        if (isset($element['borderTop'])) {
            $style .= "border-top: {$element['borderTop']}; ";
        }
        if (isset($element['borderRight'])) {
            $style .= "border-right: {$element['borderRight']}; ";
        }
        if (isset($element['borderBottom'])) {
            $style .= "border-bottom: {$element['borderBottom']}; ";
        }
        if (isset($element['borderLeft'])) {
            $style .= "border-left: {$element['borderLeft']}; ";
        }
        if (isset($element['outline'])) {
            $style .= "outline: {$element['outline']}; ";
        }
        if (isset($element['cursor'])) {
            $style .= "cursor: {$element['cursor']}; ";
        }
        if (isset($element['visibility'])) {
            $style .= "visibility: {$element['visibility']}; ";
        }
        if (isset($element['clipPath'])) {
            $style .= "clip-path: {$element['clipPath']}; ";
        }
        if (isset($element['filter'])) {
            $style .= "filter: {$element['filter']}; ";
        }
        if (isset($element['backdropFilter'])) {
            $style .= "backdrop-filter: {$element['backdropFilter']}; ";
        }
        if (isset($element['transformOrigin'])) {
            $style .= "transform-origin: {$element['transformOrigin']}; ";
        }
        if (isset($element['perspective'])) {
            $style .= "perspective: {$element['perspective']}; ";
        }
        if (isset($element['transition'])) {
            $style .= "transition: {$element['transition']}; ";
        }
        if (isset($element['animation'])) {
            $style .= "animation: {$element['animation']}; ";
        }
        if (isset($element['minWidth'])) {
            $style .= "min-width: {$element['minWidth']}px; ";
        }
        if (isset($element['maxWidth'])) {
            $style .= "max-width: {$element['maxWidth']}px; ";
        }
        if (isset($element['minHeight'])) {
            $style .= "min-height: {$element['minHeight']}px; ";
        }
        if (isset($element['maxHeight'])) {
            $style .= "max-height: {$element['maxHeight']}px; ";
        }
        if (isset($element['flexDirection'])) {
            $style .= "flex-direction: {$element['flexDirection']}; ";
        }
        if (isset($element['justifyContent'])) {
            $style .= "justify-content: {$element['justifyContent']}; ";
        }
        if (isset($element['alignItems'])) {
            $style .= "align-items: {$element['alignItems']}; ";
        }
        if (isset($element['flexWrap'])) {
            $style .= "flex-wrap: {$element['flexWrap']}; ";
        }
        if (isset($element['textAlignLast'])) {
            $style .= "text-align-last: {$element['textAlignLast']}; ";
        }
        if (isset($element['textDecorationLine'])) {
            $style .= "text-decoration-line: {$element['textDecorationLine']}; ";
        }
        if (isset($element['textDecorationStyle'])) {
            $style .= "text-decoration-style: {$element['textDecorationStyle']}; ";
        }
        if (isset($element['textDecorationColor'])) {
            $style .= "text-decoration-color: {$element['textDecorationColor']}; ";
        }
        if (isset($element['textTransform'])) {
            $style .= "text-transform: {$element['textTransform']}; ";
        }
        if (isset($element['textAlignLast'])) {
            $style .= "text-align-last: {$element['textAlignLast']}; ";
        }
        if (isset($element['backgroundBlendMode'])) {
            $style .= "background-blend-mode: {$element['backgroundBlendMode']}; ";
        }
        if (isset($element['mixBlendMode'])) {
            $style .= "mix-blend-mode: {$element['mixBlendMode']}; ";
        }
        if (isset($element['filter'])) {
            $style .= "filter: {$element['filter']}; ";
        }
        if (isset($element['backdropFilter'])) {
            $style .= "backdrop-filter: {$element['backdropFilter']}; ";
        }
        if (isset($element['mask'])) {
            $style .= "mask: {$element['mask']}; ";
        }
        if (isset($element['maskImage'])) {
            $style .= "mask-image: {$element['maskImage']}; ";
        }
        if (isset($element['transformStyle'])) {
            $style .= "transform-style: {$element['transformStyle']}; ";
        }
        if (isset($element['perspectiveOrigin'])) {
            $style .= "perspective-origin: {$element['perspectiveOrigin']}; ";
        }
        if (isset($element['animationFillMode'])) {
            $style .= "animation-fill-mode: {$element['animationFillMode']}; ";
        }
        if (isset($element['animationDirection'])) {
            $style .= "animation-direction: {$element['animationDirection']}; ";
        }
        if (isset($element['animationIterationCount'])) {
            $style .= "animation-iteration-count: {$element['animationIterationCount']}; ";
        }
        if (isset($element['animationPlayState'])) {
            $style .= "animation-play-state: {$element['animationPlayState']}; ";
        }
        if (isset($element['transitionProperty'])) {
            $style .= "transition-property: {$element['transitionProperty']}; ";
        }
        if (isset($element['transitionDuration'])) {
            $style .= "transition-duration: {$element['transitionDuration']}; ";
        }
        if (isset($element['transitionTimingFunction'])) {
            $style .= "transition-timing-function: {$element['transitionTimingFunction']}; ";
        }
        if (isset($element['transitionDelay'])) {
            $style .= "transition-delay: {$element['transitionDelay']}; ";
        }
        if (isset($element['writingMode'])) {
            $style .= "writing-mode: {$element['writingMode']}; ";
        }
        if (isset($element['direction'])) {
            $style .= "direction: {$element['direction']}; ";
        }
        if (isset($element['textOrientation'])) {
            $style .= "text-orientation: {$element['textOrientation']}; ";
        }
        if (isset($element['unicodeBidi'])) {
            $style .= "unicode-bidi: {$element['unicodeBidi']}; ";
        }
        if (isset($element['content'])) {
            $style .= "content: {$element['content']}; ";
        }
        if (isset($element['quotes'])) {
            $style .= "quotes: {$element['quotes']}; ";
        }
        if (isset($element['counterIncrement'])) {
            $style .= "counter-increment: {$element['counterIncrement']}; ";
        }
        if (isset($element['counterReset'])) {
            $style .= "counter-reset: {$element['counterReset']}; ";
        }
        if (isset($element['position'])) {
            $style .= "position: {$element['position']}; ";
        }
        if (isset($element['float'])) {
            $style .= "float: {$element['float']}; ";
        }
        if (isset($element['clear'])) {
            $style .= "clear: {$element['clear']}; ";
        }
        if (isset($element['textTransform'])) {
            $style .= "text-transform: {$element['textTransform']}; ";
        }
        if (isset($element['textIndent'])) {
            $style .= "text-indent: {$element['textIndent']}px; ";
        }
        if (isset($element['wordBreak'])) {
            $style .= "word-break: {$element['wordBreak']}; ";
        }
        if (isset($element['textOverflow'])) {
            $style .= "text-overflow: {$element['textOverflow']}; ";
        }
        if (isset($element['flexGrow'])) {
            $style .= "flex-grow: {$element['flexGrow']}; ";
        }
        if (isset($element['flexShrink'])) {
            $style .= "flex-shrink: {$element['flexShrink']}; ";
        }
        if (isset($element['flexBasis'])) {
            $style .= "flex-basis: {$element['flexBasis']}; ";
        }
        if (isset($element['alignSelf'])) {
            $style .= "align-self: {$element['alignSelf']}; ";
        }
        if (isset($element['alignContent'])) {
            $style .= "align-content: {$element['alignContent']}; ";
        }
        if (isset($element['backgroundAttachment'])) {
            $style .= "background-attachment: {$element['backgroundAttachment']}; ";
        }
        if (isset($element['backgroundClip'])) {
            $style .= "background-clip: {$element['backgroundClip']}; ";
        }
        if (isset($element['backgroundOrigin'])) {
            $style .= "background-origin: {$element['backgroundOrigin']}; ";
        }
        if (isset($element['borderImage'])) {
            $style .= "border-image: {$element['borderImage']}; ";
        }
        if (isset($element['objectFit'])) {
            $style .= "object-fit: {$element['objectFit']}; ";
        }
        if (isset($element['resize'])) {
            $style .= "resize: {$element['resize']}; ";
        }
        if (isset($element['userSelect'])) {
            $style .= "user-select: {$element['userSelect']}; ";
        }
        if (isset($element['pointerEvents'])) {
            $style .= "pointer-events: {$element['pointerEvents']}; ";
        }
        if (isset($element['borderCollapse'])) {
            $style .= "border-collapse: {$element['borderCollapse']}; ";
        }
        if (isset($element['tableLayout'])) {
            $style .= "table-layout: {$element['tableLayout']}; ";
        }
        if (isset($element['listStyle'])) {
            $style .= "list-style: {$element['listStyle']}; ";
        }
        if (isset($element['listStyleType'])) {
            $style .= "list-style-type: {$element['listStyleType']}; ";
        }
        if (isset($element['listStylePosition'])) {
            $style .= "list-style-position: {$element['listStylePosition']}; ";
        }
        if (isset($element['listStyleImage'])) {
            $style .= "list-style-image: {$element['listStyleImage']}; ";
        }

        // SVG/Shape properties
        if (isset($element['fill'])) {
            $style .= "background-color: {$element['fill']}; ";
        }
        if (isset($element['stroke'])) {
            $style .= "border-color: {$element['stroke']}; ";
        }
        if (isset($element['strokeWidth'])) {
            $style .= "border-width: " . $normalizeCssValue($element['strokeWidth'], 'px') . "; ";
        }
        if (isset($element['strokeColor'])) {
            $style .= "border-color: {$element['strokeColor']}; ";
        }

        // Additional CSS properties
        if (isset($element['boxSizing'])) {
            $style .= "box-sizing: {$element['boxSizing']}; ";
        }
        if (isset($element['textRendering'])) {
            $style .= "text-rendering: {$element['textRendering']}; ";
        }
        if (isset($element['fontKerning'])) {
            $style .= "font-kerning: {$element['fontKerning']}; ";
        }
        if (isset($element['fontVariantLigatures'])) {
            $style .= "font-variant-ligatures: {$element['fontVariantLigatures']}; ";
        }
        if (isset($element['fontFeatureSettings'])) {
            $style .= "font-feature-settings: {$element['fontFeatureSettings']}; ";
        }
        if (isset($element['textDecorationThickness'])) {
            $style .= "text-decoration-thickness: {$element['textDecorationThickness']}; ";
        }
        if (isset($element['textUnderlineOffset'])) {
            $style .= "text-underline-offset: {$element['textUnderlineOffset']}; ";
        }
        if (isset($element['borderSpacing'])) {
            $style .= "border-spacing: {$element['borderSpacing']}; ";
        }
        if (isset($element['captionSide'])) {
            $style .= "caption-side: {$element['captionSide']}; ";
        }
        if (isset($element['emptyCells'])) {
            $style .= "empty-cells: {$element['emptyCells']}; ";
        }
        if (isset($element['tableLayout'])) {
            $style .= "table-layout: {$element['tableLayout']}; ";
        }
        if (isset($element['verticalAlign'])) {
            $style .= "vertical-align: {$element['verticalAlign']}; ";
        }
        if (isset($element['textJustify'])) {
            $style .= "text-justify: {$element['textJustify']}; ";
        }
        if (isset($element['textAlignAll'])) {
            $style .= "text-align-all: {$element['textAlignAll']}; ";
        }
        if (isset($element['hangingPunctuation'])) {
            $style .= "hanging-punctuation: {$element['hangingPunctuation']}; ";
        }
        if (isset($element['hyphens'])) {
            $style .= "hyphens: {$element['hyphens']}; ";
        }
        if (isset($element['lineBreak'])) {
            $style .= "line-break: {$element['lineBreak']}; ";
        }
        if (isset($element['overflowWrap'])) {
            $style .= "overflow-wrap: {$element['overflowWrap']}; ";
        }
        if (isset($element['wordWrap'])) {
            $style .= "word-wrap: {$element['wordWrap']}; ";
        }
        if (isset($element['textEmphasis'])) {
            $style .= "text-emphasis: {$element['textEmphasis']}; ";
        }
        if (isset($element['textEmphasisColor'])) {
            $style .= "text-emphasis-color: {$element['textEmphasisColor']}; ";
        }
        if (isset($element['textEmphasisStyle'])) {
            $style .= "text-emphasis-style: {$element['textEmphasisStyle']}; ";
        }
        if (isset($element['textEmphasisPosition'])) {
            $style .= "text-emphasis-position: {$element['textEmphasisPosition']}; ";
        }
        if (isset($element['textShadow'])) {
            $style .= "text-shadow: {$element['textShadow']}; ";
        }
        if (isset($element['boxDecorationBreak'])) {
            $style .= "box-decoration-break: {$element['boxDecorationBreak']}; ";
        }
        if (isset($element['breakAfter'])) {
            $style .= "break-after: {$element['breakAfter']}; ";
        }
        if (isset($element['breakBefore'])) {
            $style .= "break-before: {$element['breakBefore']}; ";
        }
        if (isset($element['breakInside'])) {
            $style .= "break-inside: {$element['breakInside']}; ";
        }
        if (isset($element['orphans'])) {
            $style .= "orphans: {$element['orphans']}; ";
        }
        if (isset($element['widows'])) {
            $style .= "widows: {$element['widows']}; ";
        }
        if (isset($element['pageBreakAfter'])) {
            $style .= "page-break-after: {$element['pageBreakAfter']}; ";
        }
        if (isset($element['pageBreakBefore'])) {
            $style .= "page-break-before: {$element['pageBreakBefore']}; ";
        }
        if (isset($element['pageBreakInside'])) {
            $style .= "page-break-inside: {$element['pageBreakInside']}; ";
        }
        if (isset($element['columnCount'])) {
            $style .= "column-count: {$element['columnCount']}; ";
        }
        if (isset($element['columnGap'])) {
            $style .= "column-gap: {$element['columnGap']}; ";
        }
        if (isset($element['columnRule'])) {
            $style .= "column-rule: {$element['columnRule']}; ";
        }
        if (isset($element['columnRuleColor'])) {
            $style .= "column-rule-color: {$element['columnRuleColor']}; ";
        }
        if (isset($element['columnRuleStyle'])) {
            $style .= "column-rule-style: {$element['columnRuleStyle']}; ";
        }
        if (isset($element['columnRuleWidth'])) {
            $style .= "column-rule-width: {$element['columnRuleWidth']}; ";
        }
        if (isset($element['columnSpan'])) {
            $style .= "column-span: {$element['columnSpan']}; ";
        }
        if (isset($element['columnWidth'])) {
            $style .= "column-width: {$element['columnWidth']}; ";
        }
        if (isset($element['columns'])) {
            $style .= "columns: {$element['columns']}; ";
        }

        // Vérifier les propriétés imbriquées (properties) si les propriétés directes ne sont pas définies
        if (isset($element['properties']) && is_array($element['properties'])) {
            $props = $element['properties'];

            // Position et dimensions depuis properties si pas déjà définis
            if (!isset($element['x']) && isset($props['x'])) {
                $adjustedX = $props['x'];
                if (!$this->is_preview) {
                    $adjustedX -= 20;
                }
                $style .= "left: {$adjustedX}px; ";
                error_log("[PDF] Element positioning from properties - x: {$props['x']}, adjusted x: {$adjustedX} | is_preview: " . ($this->is_preview ? 'true' : 'false'));
            }
            if (!isset($element['y']) && isset($props['y'])) {
                $adjustedY = $props['y'];
                if (!$this->is_preview) {
                    $adjustedY -= 20;
                }
                $style .= "top: {$adjustedY}px; ";
                error_log("[PDF] Element positioning from properties - y: {$props['y']}, adjusted y: {$adjustedY} | is_preview: " . ($this->is_preview ? 'true' : 'false'));
            }
            if (!isset($element['width']) && isset($props['width'])) {
                $style .= "width: " . $normalizeCssValue($props['width'], 'px') . "; ";
            }
            if (!isset($element['height']) && isset($props['height'])) {
                $style .= "height: " . $normalizeCssValue($props['height'], 'px') . "; ";
            }

            // Autres propriétés depuis properties si pas déjà définis
            if (!isset($element['color']) && !isset($element['textColor']) && isset($props['color']) && !empty($props['color'])) {
                $style .= "color: {$props['color']}; ";
            }
            if (!isset($element['color']) && !isset($element['textColor']) && isset($props['textColor']) && !empty($props['textColor'])) {
                $style .= "color: {$props['textColor']}; ";
            }
            if (!isset($element['fontSize']) && isset($props['fontSize']) && !empty($props['fontSize'])) {
                $style .= "font-size: " . $normalizeCssValue($props['fontSize'], 'px') . "; ";
            }
            if (!isset($element['fontWeight']) && isset($props['fontWeight']) && !empty($props['fontWeight'])) {
                $style .= "font-weight: {$props['fontWeight']}; ";
            }
            // Fallback for hyphenated property names in properties
            if (!isset($element['fontWeight']) && !isset($props['fontWeight']) && isset($props['font-weight']) && !empty($props['font-weight'])) {
                $style .= "font-weight: {$props['font-weight']}; ";
            }
            if (!isset($element['fontFamily']) && isset($props['fontFamily']) && !empty($props['fontFamily'])) {
                $style .= "font-family: {$props['fontFamily']}; ";
            }
            if (!isset($element['textAlign']) && isset($props['textAlign'])) {
                $style .= "text-align: {$props['textAlign']}; ";
            }
            if (!isset($element['backgroundColor']) && isset($props['backgroundColor'])) {
                $style .= "background-color: {$props['backgroundColor']}; ";
            }
            if (!isset($element['borderColor']) && isset($props['borderColor'])) {
                $style .= "border-color: {$props['borderColor']}; ";
            }
            if (!isset($element['borderWidth']) && isset($props['borderWidth'])) {
                $style .= "border-width: " . $normalizeCssValue($props['borderWidth'], 'px') . "; ";
            }
            if (!isset($element['borderRadius']) && isset($props['borderRadius'])) {
                $style .= "border-radius: " . $normalizeCssValue($props['borderRadius'], 'px') . "; ";
            }

            // Build combined transform property from properties array
            $propTransforms = [];
            if (!isset($element['rotation']) && isset($props['rotation'])) {
                $propTransforms[] = "rotate({$props['rotation']}deg)";
            }
            if (!isset($element['scale']) && isset($props['scale'])) {
                $propTransforms[] = "scale({$props['scale']})";
            }
            if (!isset($element['translateX']) && (isset($props['translateX']) || isset($props['translateY']))) {
                $translateX = $props['translateX'] ?? 0;
                $translateY = $props['translateY'] ?? 0;
                $propTransforms[] = "translate({$translateX}px, {$translateY}px)";
            }
            if (!isset($element['skew']) && isset($props['skew'])) {
                $propTransforms[] = "skew({$props['skew']}deg)";
            }
            if (!empty($propTransforms)) {
                $style .= "transform: " . implode(' ', $propTransforms) . "; ";
            }
            if (!isset($element['lineHeight']) && isset($props['lineHeight'])) {
                $style .= "line-height: {$props['lineHeight']}; ";
            }
            if (!isset($element['textDecoration']) && isset($props['textDecoration'])) {
                $style .= "text-decoration: {$props['textDecoration']}; ";
            }
            if (!isset($element['fontStyle']) && isset($props['fontStyle'])) {
                $style .= "font-style: {$props['fontStyle']}; ";
            }
            if (!isset($element['fontStretch']) && isset($props['fontStretch'])) {
                $style .= "font-stretch: {$props['fontStretch']}; ";
            }
            if (!isset($element['fontVariant']) && isset($props['fontVariant'])) {
                $style .= "font-variant: {$props['fontVariant']}; ";
            }
            if (!isset($element['padding']) && isset($props['padding'])) {
                $style .= "padding: {$props['padding']}px; ";
            }
            if (!isset($element['margin']) && isset($props['margin'])) {
                $style .= "margin: {$props['margin']}px; ";
            }
            if (!isset($element['borderStyle']) && isset($props['borderStyle'])) {
                $style .= "border-style: {$props['borderStyle']}; ";
            }
            if (!isset($element['opacity']) && isset($props['opacity'])) {
                $style .= "opacity: {$props['opacity']}; ";
            }
            if (!isset($element['zIndex']) && isset($props['zIndex'])) {
                $style .= "z-index: {$props['zIndex']}; ";
            }
            if (!isset($element['boxShadow']) && isset($props['boxShadow'])) {
                $style .= "box-shadow: {$props['boxShadow']}; ";
            }
            if (!isset($element['textShadow']) && isset($props['textShadow'])) {
                $style .= "text-shadow: {$props['textShadow']}; ";
            }
            if (!isset($element['letterSpacing']) && isset($props['letterSpacing'])) {
                $style .= "letter-spacing: {$props['letterSpacing']}px; ";
            }
            if (!isset($element['wordSpacing']) && isset($props['wordSpacing'])) {
                $style .= "word-spacing: {$props['wordSpacing']}px; ";
            }
            if (!isset($element['verticalAlign']) && isset($props['verticalAlign'])) {
                $style .= "vertical-align: {$props['verticalAlign']}; ";
            }
            if (!isset($element['display']) && isset($props['display'])) {
                $style .= "display: {$props['display']}; ";
            }
            if (!isset($element['overflow']) && isset($props['overflow'])) {
                $style .= "overflow: {$props['overflow']}; ";
            }
            if (!isset($element['whiteSpace']) && isset($props['whiteSpace'])) {
                $style .= "white-space: {$props['whiteSpace']}; ";
            }
            if (!isset($element['backgroundImage']) && isset($props['backgroundImage'])) {
                $style .= "background-image: {$props['backgroundImage']}; ";
            }
            if (!isset($element['backgroundSize']) && isset($props['backgroundSize'])) {
                $style .= "background-size: {$props['backgroundSize']}; ";
            }
            if (!isset($element['backgroundPosition']) && isset($props['backgroundPosition'])) {
                $style .= "background-position: {$props['backgroundPosition']}; ";
            }
            if (!isset($element['backgroundRepeat']) && isset($props['backgroundRepeat'])) {
                $style .= "background-repeat: {$props['backgroundRepeat']}; ";
            }
            if (!isset($element['borderTop']) && isset($props['borderTop'])) {
                $style .= "border-top: {$props['borderTop']}; ";
            }
            if (!isset($element['borderRight']) && isset($props['borderRight'])) {
                $style .= "border-right: {$props['borderRight']}; ";
            }
            if (!isset($element['borderBottom']) && isset($props['borderBottom'])) {
                $style .= "border-bottom: {$props['borderBottom']}; ";
            }
            if (!isset($element['borderLeft']) && isset($props['borderLeft'])) {
                $style .= "border-left: {$props['borderLeft']}; ";
            }
            if (!isset($element['outline']) && isset($props['outline'])) {
                $style .= "outline: {$props['outline']}; ";
            }
            if (!isset($element['cursor']) && isset($props['cursor'])) {
                $style .= "cursor: {$props['cursor']}; ";
            }
            if (!isset($element['visibility']) && isset($props['visibility'])) {
                $style .= "visibility: {$props['visibility']}; ";
            }
            if (!isset($element['clipPath']) && isset($props['clipPath'])) {
                $style .= "clip-path: {$props['clipPath']}; ";
            }
            if (!isset($element['filter']) && isset($props['filter'])) {
                $style .= "filter: {$props['filter']}; ";
            }
            if (!isset($element['backdropFilter']) && isset($props['backdropFilter'])) {
                $style .= "backdrop-filter: {$props['backdropFilter']}; ";
            }
            if (!isset($element['transformOrigin']) && isset($props['transformOrigin'])) {
                $style .= "transform-origin: {$props['transformOrigin']}; ";
            }
            if (!isset($element['perspective']) && isset($props['perspective'])) {
                $style .= "perspective: {$props['perspective']}; ";
            }
            if (!isset($element['transition']) && isset($props['transition'])) {
                $style .= "transition: {$props['transition']}; ";
            }
            if (!isset($element['animation']) && isset($props['animation'])) {
                $style .= "animation: {$props['animation']}; ";
            }
            if (!isset($element['minWidth']) && isset($props['minWidth'])) {
                $style .= "min-width: {$props['minWidth']}px; ";
            }
            if (!isset($element['maxWidth']) && isset($props['maxWidth'])) {
                $style .= "max-width: {$props['maxWidth']}px; ";
            }
            if (!isset($element['minHeight']) && isset($props['minHeight'])) {
                $style .= "min-height: {$props['minHeight']}px; ";
            }
            if (!isset($element['maxHeight']) && isset($props['maxHeight'])) {
                $style .= "max-height: {$props['maxHeight']}px; ";
            }
            if (!isset($element['flexDirection']) && isset($props['flexDirection'])) {
                $style .= "flex-direction: {$props['flexDirection']}; ";
            }
            if (!isset($element['justifyContent']) && isset($props['justifyContent'])) {
                $style .= "justify-content: {$props['justifyContent']}; ";
            }
            if (!isset($element['alignItems']) && isset($props['alignItems'])) {
                $style .= "align-items: {$props['alignItems']}; ";
            }
            if (!isset($element['flexWrap']) && isset($props['flexWrap'])) {
                $style .= "flex-wrap: {$props['flexWrap']}; ";
            }
            if (!isset($element['position']) && isset($props['position'])) {
                $style .= "position: {$props['position']}; ";
            }
            if (!isset($element['float']) && isset($props['float'])) {
                $style .= "float: {$props['float']}; ";
            }
            if (!isset($element['clear']) && isset($props['clear'])) {
                $style .= "clear: {$props['clear']}; ";
            }
            if (!isset($element['textTransform']) && isset($props['textTransform'])) {
                $style .= "text-transform: {$props['textTransform']}; ";
            }
            if (!isset($element['textIndent']) && isset($props['textIndent'])) {
                $style .= "text-indent: {$props['textIndent']}px; ";
            }
            if (!isset($element['wordBreak']) && isset($props['wordBreak'])) {
                $style .= "word-break: {$props['wordBreak']}; ";
            }
            if (!isset($element['textOverflow']) && isset($props['textOverflow'])) {
                $style .= "text-overflow: {$props['textOverflow']}; ";
            }
            if (!isset($element['flexGrow']) && isset($props['flexGrow'])) {
                $style .= "flex-grow: {$props['flexGrow']}; ";
            }
            if (!isset($element['flexShrink']) && isset($props['flexShrink'])) {
                $style .= "flex-shrink: {$props['flexShrink']}; ";
            }
            if (!isset($element['flexBasis']) && isset($props['flexBasis'])) {
                $style .= "flex-basis: {$props['flexBasis']}; ";
            }
            if (!isset($element['alignSelf']) && isset($props['alignSelf'])) {
                $style .= "align-self: {$props['alignSelf']}; ";
            }
            if (!isset($element['alignContent']) && isset($props['alignContent'])) {
                $style .= "align-content: {$props['alignContent']}; ";
            }
            if (!isset($element['backgroundAttachment']) && isset($props['backgroundAttachment'])) {
                $style .= "background-attachment: {$props['backgroundAttachment']}; ";
            }
            if (!isset($element['backgroundClip']) && isset($props['backgroundClip'])) {
                $style .= "background-clip: {$props['backgroundClip']}; ";
            }
            if (!isset($element['backgroundOrigin']) && isset($props['backgroundOrigin'])) {
                $style .= "background-origin: {$props['backgroundOrigin']}; ";
            }
            if (!isset($element['borderImage']) && isset($props['borderImage'])) {
                $style .= "border-image: {$props['borderImage']}; ";
            }
            if (!isset($element['objectFit']) && isset($props['objectFit'])) {
                $style .= "object-fit: {$props['objectFit']}; ";
            }
            if (!isset($element['resize']) && isset($props['resize'])) {
                $style .= "resize: {$props['resize']}; ";
            }
            if (!isset($element['userSelect']) && isset($props['userSelect'])) {
                $style .= "user-select: {$props['userSelect']}; ";
            }
            if (!isset($element['pointerEvents']) && isset($props['pointerEvents'])) {
                $style .= "pointer-events: {$props['pointerEvents']}; ";
            }
            if (!isset($element['borderCollapse']) && isset($props['borderCollapse'])) {
                $style .= "border-collapse: {$props['borderCollapse']}; ";
            }
            if (!isset($element['tableLayout']) && isset($props['tableLayout'])) {
                $style .= "table-layout: {$props['tableLayout']}; ";
            }
            if (!isset($element['listStyle']) && isset($props['listStyle'])) {
                $style .= "list-style: {$props['listStyle']}; ";
            }
            if (!isset($element['listStyleType']) && isset($props['listStyleType'])) {
                $style .= "list-style-type: {$props['listStyleType']}; ";
            }
            if (!isset($element['listStylePosition']) && isset($props['listStylePosition'])) {
                $style .= "list-style-position: {$props['listStylePosition']}; ";
            }
            if (!isset($element['listStyleImage']) && isset($props['listStyleImage'])) {
                $style .= "list-style-image: {$props['listStyleImage']}; ";
            }
            if (!isset($element['textAlignLast']) && isset($props['textAlignLast'])) {
                $style .= "text-align-last: {$props['textAlignLast']}; ";
            }
            if (!isset($element['textDecorationLine']) && isset($props['textDecorationLine'])) {
                $style .= "text-decoration-line: {$props['textDecorationLine']}; ";
            }
            if (!isset($element['textDecorationStyle']) && isset($props['textDecorationStyle'])) {
                $style .= "text-decoration-style: {$props['textDecorationStyle']}; ";
            }
            if (!isset($element['textDecorationColor']) && isset($props['textDecorationColor'])) {
                $style .= "text-decoration-color: {$props['textDecorationColor']}; ";
            }
            if (!isset($element['textTransform']) && isset($props['textTransform'])) {
                $style .= "text-transform: {$props['textTransform']}; ";
            }
            if (!isset($element['textAlignLast']) && isset($props['textAlignLast'])) {
                $style .= "text-align-last: {$props['textAlignLast']}; ";
            }
            if (!isset($element['backgroundBlendMode']) && isset($props['backgroundBlendMode'])) {
                $style .= "background-blend-mode: {$props['backgroundBlendMode']}; ";
            }
            if (!isset($element['mixBlendMode']) && isset($props['mixBlendMode'])) {
                $style .= "mix-blend-mode: {$props['mixBlendMode']}; ";
            }
            if (!isset($element['filter']) && isset($props['filter'])) {
                $style .= "filter: {$props['filter']}; ";
            }
            if (!isset($element['backdropFilter']) && isset($props['backdropFilter'])) {
                $style .= "backdrop-filter: {$props['backdropFilter']}; ";
            }
            if (!isset($element['mask']) && isset($props['mask'])) {
                $style .= "mask: {$props['mask']}; ";
            }
            if (!isset($element['maskImage']) && isset($props['maskImage'])) {
                $style .= "mask-image: {$props['maskImage']}; ";
            }
            if (!isset($element['transformStyle']) && isset($props['transformStyle'])) {
                $style .= "transform-style: {$props['transformStyle']}; ";
            }
            if (!isset($element['perspectiveOrigin']) && isset($props['perspectiveOrigin'])) {
                $style .= "perspective-origin: {$props['perspectiveOrigin']}; ";
            }
            if (!isset($element['animationFillMode']) && isset($props['animationFillMode'])) {
                $style .= "animation-fill-mode: {$props['animationFillMode']}; ";
            }
            if (!isset($element['animationDirection']) && isset($props['animationDirection'])) {
                $style .= "animation-direction: {$props['animationDirection']}; ";
            }
            if (!isset($element['animationIterationCount']) && isset($props['animationIterationCount'])) {
                $style .= "animation-iteration-count: {$props['animationIterationCount']}; ";
            }
            if (!isset($element['animationPlayState']) && isset($props['animationPlayState'])) {
                $style .= "animation-play-state: {$props['animationPlayState']}; ";
            }
            if (!isset($element['transitionProperty']) && isset($props['transitionProperty'])) {
                $style .= "transition-property: {$props['transitionProperty']}; ";
            }
            if (!isset($element['transitionDuration']) && isset($props['transitionDuration'])) {
                $style .= "transition-duration: {$props['transitionDuration']}; ";
            }
            if (!isset($element['transitionTimingFunction']) && isset($props['transitionTimingFunction'])) {
                $style .= "transition-timing-function: {$props['transitionTimingFunction']}; ";
            }
            if (!isset($element['transitionDelay']) && isset($props['transitionDelay'])) {
                $style .= "transition-delay: {$props['transitionDelay']}; ";
            }
            if (!isset($element['writingMode']) && isset($props['writingMode'])) {
                $style .= "writing-mode: {$props['writingMode']}; ";
            }
            if (!isset($element['direction']) && isset($props['direction'])) {
                $style .= "direction: {$props['direction']}; ";
            }
            if (!isset($element['textOrientation']) && isset($props['textOrientation'])) {
                $style .= "text-orientation: {$props['textOrientation']}; ";
            }
            if (!isset($element['unicodeBidi']) && isset($props['unicodeBidi'])) {
                $style .= "unicode-bidi: {$props['unicodeBidi']}; ";
            }
            if (!isset($element['content']) && isset($props['content'])) {
                $style .= "content: {$props['content']}; ";
            }
            if (!isset($element['quotes']) && isset($props['quotes'])) {
                $style .= "quotes: {$props['quotes']}; ";
            }
            if (!isset($element['counterIncrement']) && isset($props['counterIncrement'])) {
                $style .= "counter-increment: {$props['counterIncrement']}; ";
            }
            if (!isset($element['listStyleImage']) && isset($props['listStyleImage'])) {
                $style .= "list-style-image: {$props['listStyleImage']}; ";
            }
            if (!isset($element['textAlignLast']) && isset($props['textAlignLast'])) {
                $style .= "text-align-last: {$props['textAlignLast']}; ";
            }
            if (!isset($element['textDecorationLine']) && isset($props['textDecorationLine'])) {
                $style .= "text-decoration-line: {$props['textDecorationLine']}; ";
            }
            if (!isset($element['textDecorationStyle']) && isset($props['textDecorationStyle'])) {
                $style .= "text-decoration-style: {$props['textDecorationStyle']}; ";
            }
            if (!isset($element['textDecorationColor']) && isset($props['textDecorationColor'])) {
                $style .= "text-decoration-color: {$props['textDecorationColor']}; ";
            }
            if (!isset($element['textTransform']) && isset($props['textTransform'])) {
                $style .= "text-transform: {$props['textTransform']}; ";
            }
            if (!isset($element['textAlignLast']) && isset($props['textAlignLast'])) {
                $style .= "text-align-last: {$props['textAlignLast']}; ";
            }
            if (!isset($element['backgroundBlendMode']) && isset($props['backgroundBlendMode'])) {
                $style .= "background-blend-mode: {$props['backgroundBlendMode']}; ";
            }
            if (!isset($element['mixBlendMode']) && isset($props['mixBlendMode'])) {
                $style .= "mix-blend-mode: {$props['mixBlendMode']}; ";
            }
            if (!isset($element['filter']) && isset($props['filter'])) {
                $style .= "filter: {$props['filter']}; ";
            }
            if (!isset($element['backdropFilter']) && isset($props['backdropFilter'])) {
                $style .= "backdrop-filter: {$props['backdropFilter']}; ";
            }
            if (!isset($element['mask']) && isset($props['mask'])) {
                $style .= "mask: {$props['mask']}; ";
            }
            if (!isset($element['maskImage']) && isset($props['maskImage'])) {
                $style .= "mask-image: {$props['maskImage']}; ";
            }
            if (!isset($element['transformStyle']) && isset($props['transformStyle'])) {
                $style .= "transform-style: {$props['transformStyle']}; ";
            }
            if (!isset($element['perspectiveOrigin']) && isset($props['perspectiveOrigin'])) {
                $style .= "perspective-origin: {$props['perspectiveOrigin']}; ";
            }
            if (!isset($element['animationFillMode']) && isset($props['animationFillMode'])) {
                $style .= "animation-fill-mode: {$props['animationFillMode']}; ";
            }
            if (!isset($element['animationDirection']) && isset($props['animationDirection'])) {
                $style .= "animation-direction: {$props['animationDirection']}; ";
            }
            if (!isset($element['animationIterationCount']) && isset($props['animationIterationCount'])) {
                $style .= "animation-iteration-count: {$props['animationIterationCount']}; ";
            }
            if (!isset($element['animationPlayState']) && isset($props['animationPlayState'])) {
                $style .= "animation-play-state: {$props['animationPlayState']}; ";
            }
            if (!isset($element['transitionProperty']) && isset($props['transitionProperty'])) {
                $style .= "transition-property: {$props['transitionProperty']}; ";
            }
            if (!isset($element['transitionDuration']) && isset($props['transitionDuration'])) {
                $style .= "transition-duration: {$props['transitionDuration']}; ";
            }
            if (!isset($element['transitionTimingFunction']) && isset($props['transitionTimingFunction'])) {
                $style .= "transition-timing-function: {$props['transitionTimingFunction']}; ";
            }
            if (!isset($element['transitionDelay']) && isset($props['transitionDelay'])) {
                $style .= "transition-delay: {$props['transitionDelay']}; ";
            }
            if (!isset($element['writingMode']) && isset($props['writingMode'])) {
                $style .= "writing-mode: {$props['writingMode']}; ";
            }
            if (!isset($element['direction']) && isset($props['direction'])) {
                $style .= "direction: {$props['direction']}; ";
            }
            if (!isset($element['textOrientation']) && isset($props['textOrientation'])) {
                $style .= "text-orientation: {$props['textOrientation']}; ";
            }
            if (!isset($element['unicodeBidi']) && isset($props['unicodeBidi'])) {
                $style .= "unicode-bidi: {$props['unicodeBidi']}; ";
            }
            if (!isset($element['content']) && isset($props['content'])) {
                $style .= "content: {$props['content']}; ";
            }
            if (!isset($element['quotes']) && isset($props['quotes'])) {
                $style .= "quotes: {$props['quotes']}; ";
            }
            if (!isset($element['counterIncrement']) && isset($props['counterIncrement'])) {
                $style .= "counter-increment: {$props['counterIncrement']}; ";
            }
            if (!isset($element['counterReset']) && isset($props['counterReset'])) {
                $style .= "counter-reset: {$props['counterReset']}; ";
            }

            // SVG/Shape properties from properties array
            if (!isset($element['fill']) && isset($props['fill'])) {
                $style .= "background-color: {$props['fill']}; ";
            }
            if (!isset($element['stroke']) && isset($props['stroke'])) {
                $style .= "border-color: {$props['stroke']}; ";
            }
            if (!isset($element['strokeWidth']) && isset($props['strokeWidth'])) {
                $style .= "border-width: " . $normalizeCssValue($props['strokeWidth'], 'px') . "; ";
            }
            if (!isset($element['strokeColor']) && isset($props['strokeColor'])) {
                $style .= "border-color: {$props['strokeColor']}; ";
            }

            // Additional CSS properties from properties array
            if (!isset($element['boxSizing']) && isset($props['boxSizing'])) {
                $style .= "box-sizing: {$props['boxSizing']}; ";
            }
            if (!isset($element['textRendering']) && isset($props['textRendering'])) {
                $style .= "text-rendering: {$props['textRendering']}; ";
            }
            if (!isset($element['fontKerning']) && isset($props['fontKerning'])) {
                $style .= "font-kerning: {$props['fontKerning']}; ";
            }
            if (!isset($element['fontVariantLigatures']) && isset($props['fontVariantLigatures'])) {
                $style .= "font-variant-ligatures: {$props['fontVariantLigatures']}; ";
            }
            if (!isset($element['fontFeatureSettings']) && isset($props['fontFeatureSettings'])) {
                $style .= "font-feature-settings: {$props['fontFeatureSettings']}; ";
            }
            if (!isset($element['textDecorationThickness']) && isset($props['textDecorationThickness'])) {
                $style .= "text-decoration-thickness: {$props['textDecorationThickness']}; ";
            }
            if (!isset($element['textUnderlineOffset']) && isset($props['textUnderlineOffset'])) {
                $style .= "text-underline-offset: {$props['textUnderlineOffset']}; ";
            }
            if (!isset($element['borderSpacing']) && isset($props['borderSpacing'])) {
                $style .= "border-spacing: {$props['borderSpacing']}; ";
            }
            if (!isset($element['captionSide']) && isset($props['captionSide'])) {
                $style .= "caption-side: {$props['captionSide']}; ";
            }
            if (!isset($element['emptyCells']) && isset($props['emptyCells'])) {
                $style .= "empty-cells: {$props['emptyCells']}; ";
            }
            if (!isset($element['tableLayout']) && isset($props['tableLayout'])) {
                $style .= "table-layout: {$props['tableLayout']}; ";
            }
            if (!isset($element['verticalAlign']) && isset($props['verticalAlign'])) {
                $style .= "vertical-align: {$props['verticalAlign']}; ";
            }
            if (!isset($element['textJustify']) && isset($props['textJustify'])) {
                $style .= "text-justify: {$props['textJustify']}; ";
            }
            if (!isset($element['textAlignAll']) && isset($props['textAlignAll'])) {
                $style .= "text-align-all: {$props['textAlignAll']}; ";
            }
            if (!isset($element['hangingPunctuation']) && isset($props['hangingPunctuation'])) {
                $style .= "hanging-punctuation: {$props['hangingPunctuation']}; ";
            }
            if (!isset($element['hyphens']) && isset($props['hyphens'])) {
                $style .= "hyphens: {$props['hyphens']}; ";
            }
            if (!isset($element['lineBreak']) && isset($props['lineBreak'])) {
                $style .= "line-break: {$props['lineBreak']}; ";
            }
            if (!isset($element['overflowWrap']) && isset($props['overflowWrap'])) {
                $style .= "overflow-wrap: {$props['overflowWrap']}; ";
            }
            if (!isset($element['wordWrap']) && isset($props['wordWrap'])) {
                $style .= "word-wrap: {$props['wordWrap']}; ";
            }
            if (!isset($element['textEmphasis']) && isset($props['textEmphasis'])) {
                $style .= "text-emphasis: {$props['textEmphasis']}; ";
            }
            if (!isset($element['textEmphasisColor']) && isset($props['textEmphasisColor'])) {
                $style .= "text-emphasis-color: {$props['textEmphasisColor']}; ";
            }
            if (!isset($element['textEmphasisStyle']) && isset($props['textEmphasisStyle'])) {
                $style .= "text-emphasis-style: {$props['textEmphasisStyle']}; ";
            }
            if (!isset($element['textEmphasisPosition']) && isset($props['textEmphasisPosition'])) {
                $style .= "text-emphasis-position: {$props['textEmphasisPosition']}; ";
            }
            if (!isset($element['textShadow']) && isset($props['textShadow'])) {
                $style .= "text-shadow: {$props['textShadow']}; ";
            }
            if (!isset($element['boxDecorationBreak']) && isset($props['boxDecorationBreak'])) {
                $style .= "box-decoration-break: {$props['boxDecorationBreak']}; ";
            }
            if (!isset($element['breakAfter']) && isset($props['breakAfter'])) {
                $style .= "break-after: {$props['breakAfter']}; ";
            }
            if (!isset($element['breakBefore']) && isset($props['breakBefore'])) {
                $style .= "break-before: {$props['breakBefore']}; ";
            }
            if (!isset($element['breakInside']) && isset($props['breakInside'])) {
                $style .= "break-inside: {$props['breakInside']}; ";
            }
            if (!isset($element['orphans']) && isset($props['orphans'])) {
                $style .= "orphans: {$props['orphans']}; ";
            }
            if (!isset($element['widows']) && isset($props['widows'])) {
                $style .= "widows: {$props['widows']}; ";
            }
            if (!isset($element['pageBreakAfter']) && isset($props['pageBreakAfter'])) {
                $style .= "page-break-after: {$props['pageBreakAfter']}; ";
            }
            if (!isset($element['pageBreakBefore']) && isset($props['pageBreakBefore'])) {
                $style .= "page-break-before: {$props['pageBreakBefore']}; ";
            }
            if (!isset($element['pageBreakInside']) && isset($props['pageBreakInside'])) {
                $style .= "page-break-inside: {$props['pageBreakInside']}; ";
            }
            if (!isset($element['columnCount']) && isset($props['columnCount'])) {
                $style .= "column-count: {$props['columnCount']}; ";
            }
            if (!isset($element['columnGap']) && isset($props['columnGap'])) {
                $style .= "column-gap: {$props['columnGap']}; ";
            }
            if (!isset($element['columnRule']) && isset($props['columnRule'])) {
                $style .= "column-rule: {$props['columnRule']}; ";
            }
            if (!isset($element['columnRuleColor']) && isset($props['columnRuleColor'])) {
                $style .= "column-rule-color: {$props['columnRuleColor']}; ";
            }
            if (!isset($element['columnRuleStyle']) && isset($props['columnRuleStyle'])) {
                $style .= "column-rule-style: {$props['columnRuleStyle']}; ";
            }
            if (!isset($element['columnRuleWidth']) && isset($props['columnRuleWidth'])) {
                $style .= "column-rule-width: {$props['columnRuleWidth']}; ";
            }
            if (!isset($element['columnSpan']) && isset($props['columnSpan'])) {
                $style .= "column-span: {$props['columnSpan']}; ";
            }
            if (!isset($element['columnWidth']) && isset($props['columnWidth'])) {
                $style .= "column-width: {$props['columnWidth']}; ";
            }
            if (!isset($element['columns']) && isset($props['columns'])) {
                $style .= "columns: {$props['columns']}; ";
            }
        }

        return $style;
    }

    /**
     * Rend un élément company_info
     */
    protected function renderCompanyInfoElement(array $element): string
    {
        error_log('[PDF] Company info - keys: ' . implode(', ', array_keys($element)));
        
        $style = $this->buildElementStyle($element);
        
        // Récupérer les données depuis le data provider pour l'aperçu
        $companyName = $this->data_provider->getVariableValue('company_name');
        $companyAddress = $this->data_provider->getVariableValue('company_full_address');
        $companyEmail = $this->data_provider->getVariableValue('company_email');
        $companyPhone = $this->data_provider->getVariableValue('company_phone');
        $companySiret = $this->data_provider->getVariableValue('company_siret');
        
        // Fallback vers les propriétés de l'élément si data provider ne fournit rien
        if (empty($companyName) || $companyName === '{{company_name}}') {
            $companyName = $element['companyName'] ?? $element['name'] ?? $element['company_name'] ?? 'Entreprise';
        }
        if (empty($companyAddress) || strpos($companyAddress, '{{') !== false) {
            $companyAddress = $element['companyAddress'] ?? $element['address'] ?? $element['company_address'] ?? '';
        }
        if (empty($companyEmail) || strpos($companyEmail, '{{') !== false) {
            $companyEmail = $element['companyEmail'] ?? $element['email'] ?? $element['company_email'] ?? '';
        }
        if (empty($companyPhone) || strpos($companyPhone, '{{') !== false) {
            $companyPhone = $element['companyPhone'] ?? $element['phone'] ?? $element['company_phone'] ?? '';
        }
        if (empty($companySiret) || strpos($companySiret, '{{') !== false) {
            $companySiret = $element['companySiret'] ?? $element['siret'] ?? $element['company_siret'] ?? '';
        }
        
        $content = $companyName;
        if (!empty($companyAddress)) $content .= "\n" . $companyAddress;
        if (!empty($companyEmail)) $content .= "\n" . $companyEmail;
        if (!empty($companyPhone)) $content .= "\n" . $companyPhone;
        if (!empty($companySiret)) $content .= "\nSIRET: " . $companySiret;
        
        error_log('[PDF] Company info - FINAL name: "' . $companyName . '", address: "' . $companyAddress . '", email: "' . $companyEmail . '", phone: "' . $companyPhone . '", siret: "' . $companySiret . '"');
        return "<div class=\"pdf-element\" data-element-type=\"company_info\" style=\"{$style}\">" . nl2br($content) . "</div>";
    }

    /**
     * Rend un élément order_number
     */
    protected function renderOrderNumberElement(array $element): string
    {
        error_log('[PDF] Order number - keys: ' . implode(', ', array_keys($element)) . ', number: ' . ($element['orderNumber'] ?? $element['number'] ?? 'empty'));
        
        $style = $this->buildElementStyle($element);
        
        // Use real element data with flexible property names
        $orderNumber = $element['orderNumber'] ?? $element['number'] ?? $element['value'] ?? $element['order_number'] ?? '#12345';
        
        // Also check for nested properties
        if ($orderNumber === '#12345' && isset($element['properties']['orderNumber'])) {
            $orderNumber = $element['properties']['orderNumber'];
        }
        
        error_log('[PDF] Order number - FINAL number: "' . $orderNumber . '"');
        return "<div class=\"pdf-element\" style=\"{$style}\">{$orderNumber}</div>";
    }

    /**
     * Rend un élément company_logo
     */
    protected function renderCompanyLogoElement(array $element): string
    {
        error_log('[PDF] Company logo - keys: ' . implode(', ', array_keys($element)) . ', src: ' . ($element['src'] ?? 'empty'));
        
        $style = $this->buildElementStyle($element);
        $src = $element['src'] ?? $element['logoUrl'] ?? $element['url'] ?? '';
        if (empty($src)) {
            error_log('[PDF] Company logo - FINAL src: empty, using placeholder');
            return "<div class=\"pdf-element image-element\" style=\"{$style}; background-color: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #666;\">🏢 Logo</div>";
        }
        error_log('[PDF] Company logo - FINAL src: "' . $src . '"');
        return "<img class=\"pdf-element image-element\" src=\"{$src}\" style=\"{$style}; max-width: 100%; height: auto;\" alt=\"Logo\" />";
    }

    /**
     * Rend un élément product_table
     */
    protected function renderProductTableElement(array $element): string
    {
        error_log('[PDF] Product table - keys: ' . implode(', ', array_keys($element)) . ', has products: ' . (isset($element['products']) || isset($element['items']) ? 'YES' : 'NO'));
        
        $style = $this->buildElementStyle($element);
        
        // Use real element data with flexible property names
        $products = $element['products'] ?? $element['items'] ?? $element['productList'] ?? [];
        $showHeaders = $element['showHeaders'] ?? $element['headers'] ?? $element['show_header'] ?? true;
        $showBorders = $element['showBorders'] ?? $element['borders'] ?? $element['show_border'] ?? true;
        $currency = $element['currency'] ?? $element['currencySymbol'] ?? '€';
        
        // Also check for nested properties
        if (empty($products) && isset($element['properties'])) {
            $products = $element['properties']['products'] ?? $element['properties']['items'] ?? $element['properties']['productList'] ?? [];
            $showHeaders = $element['properties']['showHeaders'] ?? $element['properties']['headers'] ?? $element['properties']['show_header'] ?? $showHeaders;
            $showBorders = $element['properties']['showBorders'] ?? $element['properties']['borders'] ?? $element['properties']['show_border'] ?? $showBorders;
            $currency = $element['properties']['currency'] ?? $element['properties']['currencySymbol'] ?? $currency;
        }
        
        $tableStyle = $showBorders ? 'border-collapse: collapse;' : 'border-collapse: separate;';
        $borderStyle = $showBorders ? 'border: 1px solid #ddd;' : '';
        
        $content = "<table style='width: 100%; {$tableStyle}'>";
        
        if ($showHeaders) {
            $content .= "<thead><tr style='background-color: #f5f5f5;'>";
            $content .= "<th style='{$borderStyle} padding: 8px; text-align: left;'>Produit</th>";
            $content .= "<th style='{$borderStyle} padding: 8px; text-align: center;'>Qté</th>";
            $content .= "<th style='{$borderStyle} padding: 8px; text-align: right;'>Prix</th>";
            $content .= "</tr></thead>";
        }
        
        $content .= "<tbody>";
        
        error_log('[PDF] Product table - processing ' . count($products) . ' products');
        
        if (empty($products)) {
            // Sample data if no products provided
            error_log('[PDF] Product table - using sample data (no products found)');
            $content .= "<tr>";
            $content .= "<td style='{$borderStyle} padding: 8px;'>Produit Exemple</td>";
            $content .= "<td style='{$borderStyle} padding: 8px; text-align: center;'>2</td>";
            $content .= "<td style='{$borderStyle} padding: 8px; text-align: right;'>{$currency}50.00</td>";
            $content .= "</tr>";
        } else {
            // Use real product data
            foreach ($products as $index => $product) {
                $rowStyle = ($index % 2 == 1) ? 'background-color: #fafafa;' : '';
                $content .= "<tr style='{$rowStyle}'>";
                $content .= "<td style='{$borderStyle} padding: 8px;'>" . ($product['name'] ?? 'Produit') . "</td>";
                $content .= "<td style='{$borderStyle} padding: 8px; text-align: center;'>" . ($product['quantity'] ?? 1) . "</td>";
                $content .= "<td style='{$borderStyle} padding: 8px; text-align: right;'>{$currency}" . ($product['price'] ?? '0.00') . "</td>";
                $content .= "</tr>";
            }
        }
        
        $content .= "</tbody></table>";
        
        error_log('[PDF] Product table - FINAL products count: ' . count($products) . ', showHeaders: ' . ($showHeaders ? 'YES' : 'NO') . ', showBorders: ' . ($showBorders ? 'YES' : 'NO') . ', currency: "' . $currency . '"');
        return "<div class=\"pdf-element table-element\" style=\"{$style}\">{$content}</div>";
    }

    /**
     * Rend un élément woocommerce_order_date
     */
    protected function renderWoocommerceOrderDateElement(array $element): string
    {
        error_log('[PDF] Order date - keys: ' . implode(', ', array_keys($element)) . ', date: ' . ($element['date'] ?? 'empty'));
        
        $style = $this->buildElementStyle($element);
        
        // Use real element data with flexible property names
        $date = $element['date'] ?? $element['orderDate'] ?? $element['order_date'] ?? date('d/m/Y');
        
        // Also check for nested properties
        if ($date === date('d/m/Y') && isset($element['properties']['date'])) {
            $date = $element['properties']['date'];
        }
        if ($date === date('d/m/Y') && isset($element['properties']['orderDate'])) {
            $date = $element['properties']['orderDate'];
        }
        if ($date === date('d/m/Y') && isset($element['properties']['order_date'])) {
            $date = $element['properties']['order_date'];
        }
        
        error_log('[PDF] Order date - FINAL date: "' . $date . '"');
        return "<div class=\"pdf-element\" style=\"{$style}\">{$date}</div>";
    }

    /**
     * Rend un élément document_type
     */
    protected function renderDocumentTypeElement(array $element): string
    {
        // Log all element properties for debugging
        error_log('[PDF] Document type - keys: ' . implode(', ', array_keys($element)) . ', title: ' . ($element['title'] ?? 'empty'));
        
        $style = $this->buildElementStyle($element);
        
        // Use real element data with flexible property names
        $title = $element['title'] ?? $element['documentType'] ?? $element['type'] ?? $element['document_type'] ?? 'Facture';
        
        // Also check for nested properties
        if ($title === 'Facture' && isset($element['properties']['title'])) {
            $title = $element['properties']['title'];
        }
        if ($title === 'Facture' && isset($element['properties']['documentType'])) {
            $title = $element['properties']['documentType'];
        }
        if ($title === 'Facture' && isset($element['properties']['type'])) {
            $title = $element['properties']['type'];
        }
        
        error_log('[PDF] Document type - FINAL title: "' . $title . '"');
        return "<div class=\"pdf-element\" style=\"{$style}\">{$title}</div>";
    }

    /**
     * Rend un élément dynamic_text
     */
    protected function renderDynamicTextElement(array $element): string
    {
        error_log('[PDF] Dynamic text - keys: ' . implode(', ', array_keys($element)) . ', text: ' . ($element['text'] ?? 'empty'));
        
        $style = $this->buildElementStyle($element);
        
        // Use real element data with flexible property names
        $text = $element['text'] ?? $element['textTemplate'] ?? $element['content'] ?? $element['dynamicText'] ?? 'Texte dynamique';
        
        // Also check for nested properties
        if ($text === 'Texte dynamique' && isset($element['properties']['text'])) {
            $text = $element['properties']['text'];
        }
        if ($text === 'Texte dynamique' && isset($element['properties']['textTemplate'])) {
            $text = $element['properties']['textTemplate'];
        }
        if ($text === 'Texte dynamique' && isset($element['properties']['content'])) {
            $text = $element['properties']['content'];
        }
        
        $text = $this->injectVariables($text);
        error_log('[PDF] Dynamic text - FINAL text: "' . $text . '"');
        return "<div class=\"pdf-element text-element\" style=\"{$style}\">{$text}</div>";
    }

    /**
     * Rend un élément mentions
     */
    protected function renderMentionsElement(array $element): string
    {
        error_log('[PDF] Mentions - keys: ' . implode(', ', array_keys($element)) . ', showEmail: ' . ($element['showEmail'] ?? 'false'));
        
        $style = $this->buildElementStyle($element);
        
        // Use real element data with flexible property names
        $showEmail = $element['showEmail'] ?? $element['email'] ?? $element['show_email'] ?? false;
        $showPhone = $element['showPhone'] ?? $element['phone'] ?? $element['show_phone'] ?? false;
        $showSiret = $element['showSiret'] ?? $element['siret'] ?? $element['show_siret'] ?? false;
        $showVat = $element['showVat'] ?? $element['vat'] ?? $element['show_vat'] ?? false;
        
        $email = $element['email'] ?? $element['emailAddress'] ?? 'contact@example.com';
        $phone = $element['phone'] ?? $element['phoneNumber'] ?? '+33 1 23 45 67 89';
        $siret = $element['siret'] ?? $element['siretNumber'] ?? '123 456 789 00012';
        $vat = $element['vat'] ?? $element['vatNumber'] ?? 'FR123456789';
        $separator = $element['separator'] ?? $element['separatorChar'] ?? ' • ';
        
        // Also check for nested properties
        if (isset($element['properties'])) {
            $showEmail = $element['properties']['showEmail'] ?? $element['properties']['email'] ?? $element['properties']['show_email'] ?? $showEmail;
            $showPhone = $element['properties']['showPhone'] ?? $element['properties']['phone'] ?? $element['properties']['show_phone'] ?? $showPhone;
            $showSiret = $element['properties']['showSiret'] ?? $element['properties']['siret'] ?? $element['properties']['show_siret'] ?? $showSiret;
            $showVat = $element['properties']['showVat'] ?? $element['properties']['vat'] ?? $element['properties']['show_vat'] ?? $showVat;
            
            $email = $element['properties']['email'] ?? $element['properties']['emailAddress'] ?? $email;
            $phone = $element['properties']['phone'] ?? $element['properties']['phoneNumber'] ?? $phone;
            $siret = $element['properties']['siret'] ?? $element['properties']['siretNumber'] ?? $siret;
            $vat = $element['properties']['vat'] ?? $element['properties']['vatNumber'] ?? $vat;
            $separator = $element['properties']['separator'] ?? $element['properties']['separatorChar'] ?? $separator;
        }
        
        $mentions = [];
        if ($showEmail) $mentions[] = $email;
        if ($showPhone) $mentions[] = $phone;
        if ($showSiret) $mentions[] = 'SIRET: ' . $siret;
        if ($showVat) $mentions[] = 'TVA: ' . $vat;
        
        $content = implode($separator, $mentions);
        error_log('[PDF] Mentions - FINAL content: "' . $content . '", shows: email=' . ($showEmail ? 'YES' : 'NO') . ', phone=' . ($showPhone ? 'YES' : 'NO') . ', siret=' . ($showSiret ? 'YES' : 'NO') . ', vat=' . ($showVat ? 'YES' : 'NO'));
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

        // For lines, we need to center the line vertically within the element
        // The canvas draws the line at y = height/2, so we adjust the top position
        $height = $element['height'] ?? 1;
        $centerOffset = $height / 2 - $strokeWidth / 2; // Center the line within the height

        // Remove the height from style and add centering
        $style = preg_replace('/height:\s*\d+px;\s*/', '', $style);
        $style .= "height: {$strokeWidth}px; ";
        $style .= "margin-top: {$centerOffset}px; ";
        $style .= "background-color: {$strokeColor}; ";

        error_log('[PDF] Line element - FINAL strokeColor: "' . $strokeColor . '", strokeWidth: ' . $strokeWidth . ', centerOffset: ' . $centerOffset);
        return "<div class=\"pdf-element line\" style=\"{$style}\"></div>";
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
     * Log une information
     *
     * @param string $message Message d'information
     */
    protected function logInfo(string $message): void
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
     * Récupère les éléments du template
     *
     * @return array|null Liste des éléments ou null si non trouvés
     */
    protected function getElements(): ?array
    {
        // Déterminer où sont les éléments (nouvelle ou ancienne structure)
        if (isset($this->template_data['elements']) && is_array($this->template_data['elements'])) {
            return $this->template_data['elements'];
        } elseif (isset($this->template_data['template']['elements']) && is_array($this->template_data['template']['elements'])) {
            return $this->template_data['template']['elements'];
        }

        return null;
    }

    /**
     * Génère un aperçu HTML pour débogage visuel
     *
     * @return string HTML complet pour visualisation dans le navigateur
     */
    public function generateHtmlPreview(): string
    {
        $this->logInfo('Starting HTML preview generation');

        // Récupérer les éléments
        $elements = $this->getElements();
        if (empty($elements)) {
            return '<html><body><h1>Erreur: Aucun élément trouvé</h1></body></html>';
        }

        // Récupérer les dimensions du canvas
        $canvasWidth = $this->template_data['canvasWidth'] ?? 800;
        $canvasHeight = $this->template_data['canvasHeight'] ?? 1100;

        // Générer le HTML des éléments
        $htmlContent = '';
        foreach ($elements as $index => $element) {
            $elementType = $element['type'] ?? 'unknown';
            $this->logInfo("Processing element {$index} type: {$elementType}");

            $htmlContent .= $this->renderElement($element);
        }

        // Générer le HTML complet
        $elementCount = count($elements);
        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu HTML - PDF Builder</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .canvas-container {
            position: relative;
            width: ' . $canvasWidth . 'px;
            height: ' . $canvasHeight . 'px;
            background-color: white;
            border: 2px solid #ccc;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .canvas-info {
            text-align: center;
            margin-bottom: 10px;
            color: #666;
            font-size: 12px;
        }
        .element-debug {
            position: absolute;
            background: rgba(255,0,0,0.1);
            border: 1px dashed red;
            pointer-events: none;
            z-index: 1000;
        }
        .element-debug::before {
            content: attr(data-type);
            position: absolute;
            top: -20px;
            left: 0;
            background: red;
            color: white;
            padding: 2px 4px;
            font-size: 10px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <div class="canvas-info">
        Canvas: ' . $canvasWidth . 'x' . $canvasHeight . 'px | Éléments: ' . $elementCount . '
    </div>
    <div class="canvas-container">
        ' . $htmlContent . '
    </div>
    <script>
        // Ajouter des overlays de débogage (sauf pour customer_info et company_info)
        document.addEventListener("DOMContentLoaded", function() {
            const elements = document.querySelectorAll("[data-element-type]");
            elements.forEach(function(el) {
                const elementType = el.getAttribute("data-element-type");
                // Exclure customer_info et company_info des indicateurs de débogage
                if (elementType === "customer_info" || elementType === "company_info") {
                    return;
                }

                const debug = document.createElement("div");
                debug.className = "element-debug";
                debug.setAttribute("data-type", elementType);
                debug.style.left = el.style.left;
                debug.style.top = el.style.top;
                debug.style.width = el.style.width;
                debug.style.height = el.style.height;
                document.querySelector(".canvas-container").appendChild(debug);
            });
        });
    </script>
</body>
</html>';

        $this->logInfo('HTML preview generation completed, length: ' . strlen($html));
        return $html;
    }
}
