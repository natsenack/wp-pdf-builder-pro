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
        
        // Logging détaillé de la structure du template
        $logger = PDF_Builder_Logger::get_instance();
        $logger->debug_log('generateContent - STARTING CONTENT GENERATION');
        $logger->debug_log('generateContent - template_data structure: ' . print_r($this->template_data, true));
        $logger->debug_log('generateContent - isset template: ' . isset($this->template_data['template']));
        $logger->debug_log('generateContent - isset elements directly: ' . isset($this->template_data['elements']));
        if (isset($this->template_data['elements'])) {
            $logger->debug_log('generateContent - elements count (direct): ' . count($this->template_data['elements']));
            $logger->debug_log('generateContent - elements sample: ' . print_r(array_slice($this->template_data['elements'], 0, 2), true));
        }
        if (isset($this->template_data['template']['elements'])) {
            $logger->debug_log('generateContent - elements count (nested): ' . count($this->template_data['template']['elements']));
            $logger->debug_log('generateContent - elements sample (nested): ' . print_r(array_slice($this->template_data['template']['elements'], 0, 2), true));
        }
        
        // Déterminer où sont les éléments (nouvelle ou ancienne structure)
        $elements = null;
        if (isset($this->template_data['elements']) && is_array($this->template_data['elements'])) {
            $elements = $this->template_data['elements'];
            $logger->debug_log('generateContent - USING DIRECT ELEMENTS');
        } elseif (isset($this->template_data['template']['elements']) && is_array($this->template_data['template']['elements'])) {
            $elements = $this->template_data['template']['elements'];
            $logger->debug_log('generateContent - USING NESTED ELEMENTS');
        }
        
        if (!$elements) {
            $logger->debug_log('generateContent - NO ELEMENTS FOUND, returning empty content');
            $logger->debug_log('generateContent - Full template_data: ' . print_r($this->template_data, true));
            return $content;
        }

        $logger->debug_log('generateContent - FOUND ELEMENTS, processing ' . count($elements) . ' elements');

        foreach ($elements as $index => $element) {
            // Convert stdClass to array if necessary
            $elementArray = is_array($element) ? $element : (array) $element;
            $logger->debug_log('generateContent - Processing element ' . $index . ': ' . print_r($elementArray, true));
            $html = $this->renderElement($elementArray);
            $logger->debug_log('generateContent - Element ' . $index . ' rendered HTML length: ' . strlen($html));
            $content .= $html;
        }

        $logger->debug_log('generateContent - FINAL CONTENT LENGTH: ' . strlen($content));
        $logger->debug_log('generateContent - FINAL CONTENT PREVIEW: ' . substr($content, 0, 200));
        
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
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $logger = PDF_Builder_Logger::get_instance();
            $logger->debug_log('renderElement - processing element type: ' . $type);
            $logger->debug_log('renderElement - element data: ' . print_r($element, true));
        }
        
        $method = 'render' . ucfirst($type) . 'Element';
        if (method_exists($this, $method)) {
            $result = $this->$method($element);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                $logger = PDF_Builder_Logger::get_instance();
                $logger->debug_log('renderElement - rendered HTML: ' . $result);
            }
            return $result;
        }

        $this->logWarning("Unknown element type: {$type}");
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
        if (isset($element['textAlign'])) {
            $style .= "text-align: {$element['textAlign']}; ";
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
