<?php
namespace WP_PDF_Builder_Pro\Generators;

use WP_PDF_Builder_Pro\Interfaces\DataProviderInterface;

/**
 * Classe abstraite BaseGenerator
 * Définit la structure commune pour tous les générateurs d'aperçu
 */
abstract class BaseGenerator {

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
    public function __construct(array $template_data, DataProviderInterface $data_provider, bool $is_preview = true, array $config = []) {
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
    protected function getDefaultConfig(): array {
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
    public function validateTemplate(): bool {
        if (!isset($this->template_data['template'])) {
            $this->logError('Template data missing template key');
            return false;
        }

        if (!isset($this->template_data['template']['elements']) || !is_array($this->template_data['template']['elements'])) {
            $this->logError('Template elements missing or not an array');
            return false;
        }

        return true;
    }

    /**
     * Génère le HTML du template avec les données injectées
     *
     * @return string HTML généré
     */
    protected function generateHTML(): string {
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
    protected function getBaseHTML(): string {
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
    protected function generateCSS(): string {
        $css = '
            body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
            .pdf-element { position: absolute; }
            .text-element { white-space: pre-wrap; }
            .image-element { max-width: 100%; height: auto; }
        ';

        // Styles personnalisés du template si présents
        if (isset($this->template_data['template']['styles'])) {
            $css .= $this->template_data['template']['styles'];
        }

        return $css;
    }

    /**
     * Génère le contenu HTML des éléments
     *
     * @return string Contenu HTML
     */
    protected function generateContent(): string {
        $content = '';

        if (!isset($this->template_data['template']['elements'])) {
            return $content;
        }

        foreach ($this->template_data['template']['elements'] as $element) {
            $content .= $this->renderElement($element);
        }

        return $content;
    }

    /**
     * Rend un élément individuel
     *
     * @param array $element Données de l'élément
     * @return string HTML de l'élément
     */
    protected function renderElement(array $element): string {
        $type = $element['type'] ?? 'unknown';
        $method = 'render' . ucfirst($type) . 'Element';

        if (method_exists($this, $method)) {
            return $this->$method($element);
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
    protected function renderTextElement(array $element): string {
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
    protected function renderImageElement(array $element): string {
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
    protected function renderRectangleElement(array $element): string {
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
    protected function buildElementStyle(array $element): string {
        $style = '';

        if (isset($element['x'])) $style .= "left: {$element['x']}px; ";
        if (isset($element['y'])) $style .= "top: {$element['y']}px; ";
        if (isset($element['width'])) $style .= "width: {$element['width']}px; ";
        if (isset($element['height'])) $style .= "height: {$element['height']}px; ";
        if (isset($element['color'])) $style .= "color: {$element['color']}; ";
        if (isset($element['fontSize'])) $style .= "font-size: {$element['fontSize']}px; ";
        if (isset($element['fontWeight'])) $style .= "font-weight: {$element['fontWeight']}; ";
        if (isset($element['textAlign'])) $style .= "text-align: {$element['textAlign']}; ";

        return $style;
    }

    /**
     * Injecte les variables dynamiques dans le texte
     *
     * @param string $text Texte avec variables
     * @return string Texte avec variables remplacées
     */
    protected function injectVariables(string $text): string {
        // Recherche des variables {{variable}}
        preg_match_all('/\{\{([^}]+)\}\}/', $text, $matches);

        foreach ($matches[1] as $variable) {
            $value = $this->data_provider->getVariableValue(trim($variable));
            $text = str_replace("{{{$variable}}}", $value, $text);
        }

        return $text;
    }

    /**
     * Log une erreur
     *
     * @param string $message Message d'erreur
     */
    protected function logError(string $message): void {

    }

    /**
     * Log un avertissement
     *
     * @param string $message Message d'avertissement
     */
    protected function logWarning(string $message): void {

    }

    /**
     * Log une information
     *
     * @param string $message Message d'information
     */
    protected function logInfo(string $message): void {

    }
}
