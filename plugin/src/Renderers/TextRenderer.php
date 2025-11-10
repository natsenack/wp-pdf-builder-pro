<?php

/**
 * PDF Builder Pro - TextRenderer
 * Phase 3.3.1 - Renderer spécialisé pour les éléments texte
 *
 * Gère le rendu des éléments texte avec variables dynamiques :
 * - dynamic-text : Texte avec variables {{variable}}
 * - order_number : Numéros de commande formatés
 */

namespace PDF_Builder\Renderers;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Import du système de cache
use PDF_Builder\Cache\RendererCache;
use PDF_Builder\Performance\PerformanceMonitor;

class TextRenderer
{
    /**
     * Types d'éléments supportés par ce renderer
     */
    const SUPPORTED_TYPES = ['dynamic-text', 'order_number'];
/**
     * Styles CSS par défaut pour le texte
     */
    const DEFAULT_STYLES = [
        'font-family' => 'Arial, sans-serif',
        'font-size' => '14px',
        'font-weight' => 'normal',
        'font-style' => 'normal',
        'color' => '#000000',
        'text-align' => 'left',
        'line-height' => '1.4',
        'text-decoration' => 'none'
    ];
/**
     * Variables système disponibles pour le remplacement
     */
    const SYSTEM_VARIABLES = [
        'current_date' => 'date',
        'current_time' => 'time',
        'page_number' => 'page',
        'total_pages' => 'total_pages'
    ];

    /**
     * Rend un élément texte
     *
     * @param array $elementData Données de l'élément
     * @param array $context Contexte de rendu (données du provider)
     * @return array Résultat du rendu HTML/CSS
     */
    public function render(array $elementData, array $context = []): array
    {
        return PerformanceMonitor::measure(function () use ($elementData, $context) {

            // Validation des données d'entrée
            if (!$this->validateElementData($elementData)) {
                return [
                    'html' => '<!-- Erreur: Données élément invalides -->',
                    'css' => '',
                    'error' => 'Données élément invalides'
                ];
            }

            $type = $elementData['type'] ?? 'dynamic-text';
            $content = $elementData['content'] ?? '';
            $properties = $elementData['properties'] ?? [];
// Rendu selon le type d'élément
            switch ($type) {
                case 'dynamic-text':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       $result = $this->renderDynamicText($content, $properties, $context);

                    break;
                case 'order_number':
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       $result = $this->renderOrderNumber($properties, $context);

                    break;
                default:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       $result = [
                        'html' => '<!-- Erreur: Type d\'élément non supporté -->',
                        'css' => '',
                        'error' => 'Type d\'élément non supporté: ' . $type
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       ];

                    break;
            }

            // Enregistrement des métriques de performance
            PerformanceMonitor::recordRendererCall('TextRenderer', $type, 0);
// Le temps est mesuré par measure()

            return $result;
        }, [], 'TextRenderer::render_' . ($elementData['type'] ?? 'unknown'));
    }

    /**
     * Rend un élément dynamic-text avec variables
     *
     * @param string $content Contenu avec variables {{variable}}
     * @param array $properties Propriétés de style
     * @param array $context Données du contexte
     * @return array Résultat du rendu
     */
    private function renderDynamicText(string $content, array $properties, array $context): array
    {
        // Remplacement des variables (avec cache)
        $processedContent = $this->replaceVariables($content, $context);
// Génération des styles CSS (avec cache)
        $styleKey = RendererCache::generateStyleKey($properties, 'text');
        $css = RendererCache::get($styleKey);
        if ($css === null) {
            $css = $this->generateTextStyles($properties);
            RendererCache::set($styleKey, $css, 600);
        // Cache 10 minutes pour les styles
        }

        // Génération du HTML
        $html = $this->generateTextHtml($processedContent, $properties);
        return [
            'html' => $html,
            'css' => $css,
            'content' => $processedContent,
            'variables_replaced' => count($this->extractVariables($content))
        ];
    }

    /**
     * Rend un élément order_number
     *
     * @param array $properties Propriétés de formatage
     * @param array $context Données du contexte
     * @return array Résultat du rendu
     */
    private function renderOrderNumber(array $properties, array $context): array
    {
        $orderData = $context['order'] ?? [];
// Formatage du numéro de commande
        $formattedNumber = $this->formatOrderNumber($orderData, $properties);
// Génération des styles CSS
        $css = $this->generateTextStyles($properties);
// Génération du HTML
        $html = $this->generateTextHtml($formattedNumber, $properties);
        return [
            'html' => $html,
            'css' => $css,
            'formatted_number' => $formattedNumber
        ];
    }

    /**
     * Remplace les variables {{variable}} dans le contenu
     *
     * @param string $content Contenu avec variables
     * @param array $context Données du contexte
     * @return string Contenu avec variables remplacées
     */
    private function replaceVariables(string $content, array $context): string
    {
        // Extraction des variables du contenu (avec cache)
        $contentKey = 'content_vars_' . md5($content);
        $variables = RendererCache::get($contentKey);
        if ($variables === null) {
            $variables = $this->extractVariables($content);
            RendererCache::set($contentKey, $variables, 3600);
        // Cache 1 heure pour l'extraction
        }

        $result = $content;
        foreach ($variables as $variable) {
            $value = $this->getVariableValue($variable, $context);
        // Remplacement sécurisé (échappement HTML)
            $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $result = str_replace('{{' . $variable . '}}', $escapedValue, $result);
        }

        return $result;
    }

    /**
     * Extrait les variables {{variable}} du contenu
     *
     * @param string $content Contenu à analyser
     * @return array Liste des variables trouvées
     */
    private function extractVariables(string $content): array
    {
        $variables = [];
// Regex pour trouver {{variable}}
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        if (!empty($matches[1])) {
            $variables = array_unique($matches[1]);
        }

        return $variables;
    }

    /**
     * Récupère la valeur d'une variable depuis le contexte
     *
     * @param string $variable Nom de la variable
     * @param array $context Données du contexte
     * @return string Valeur de la variable ou placeholder
     */
    private function getVariableValue(string $variable, array $context): string
    {
        // Variables système (avec cache)
        if (isset(self::SYSTEM_VARIABLES[$variable])) {
            $cacheKey = RendererCache::generateVariableKey($variable, []);
            $cachedValue = RendererCache::get($cacheKey);
            if ($cachedValue !== null) {
                return $cachedValue;
            }

            $value = $this->getSystemVariableValue($variable);
            RendererCache::set($cacheKey, $value, 60);
// Cache 1 minute pour les variables système
            return $value;
        }

        // Variables depuis le contexte (provider)
        $contextValue = $this->getContextValue($variable, $context);
        if ($contextValue !== null) {
            return $contextValue;
        }

        // Variable non trouvée - placeholder
        return '[Variable manquante: ' . $variable . ']';
    }

    /**
     * Récupère la valeur d'une variable système
     *
     * @param string $variable Nom de la variable système
     * @return string Valeur de la variable
     */
    private function getSystemVariableValue(string $variable): string
    {
        switch ($variable) {
            case 'current_date':
                return date('d/m/Y');
            case 'current_time':
                return date('H:i:s');
            case 'page_number':
                return '1';
// Sera géré par le système de pagination
            case 'total_pages':
                return '1';
// Sera géré par le système de pagination
            default:
                return '[Variable système inconnue: ' . $variable . ']';
        }
    }

    /**
     * Récupère la valeur d'une variable depuis le contexte
     *
     * @param string $variable Nom de la variable
     * @param array $context Données du contexte
     * @return string|null Valeur trouvée ou null
     */
    private function getContextValue(string $variable, array $context): ?string
    {
        // Recherche dans les différentes sections du contexte
        $sections = ['customer', 'order', 'company', 'variables'];
        foreach ($sections as $section) {
            if (isset($context[$section]) && is_array($context[$section])) {
        // Recherche directe dans la section
                if (isset($context[$section][$variable])) {
                    return (string) $context[$section][$variable];
                }

                // Mapping spécial pour certaines variables
                $mappedValue = $this->mapVariableToContext($variable, $context[$section]);
                if ($mappedValue !== null) {
                    return $mappedValue;
                }
            }
        }

        // Recherche directe dans le contexte racine
        if (isset($context[$variable])) {
            return (string) $context[$variable];
        }

        return null;
    }

    /**
     * Mappe une variable à sa valeur dans le contexte (gestion des alias)
     *
     * @param string $variable Nom de la variable
     * @param array $sectionData Données de la section
     * @return string|null Valeur mappée ou null
     */
    private function mapVariableToContext(string $variable, array $sectionData): ?string
    {
        $mappings = [
            // Customer mappings
            'customer_name' => ['first_name', 'last_name'], // Combine first + last name
            'customer_full_name' => 'full_name',
            'customer_firstname' => 'first_name',
            'customer_lastname' => 'last_name',
            'customer_email' => 'email',
            'customer_phone' => 'phone',
            'customer_address_street' => 'address_street',
            'customer_address_city' => 'address_city',
            'customer_address_postcode' => 'address_postcode',
            'customer_address_country' => 'address_country',

            // Order mappings
            'order_number' => 'number',
            'order_date' => 'date',
            'order_total' => 'total',
            'order_subtotal' => 'subtotal',
            'order_tax_total' => 'tax_total',
            'order_shipping_total' => 'shipping_total',
            'order_payment_method' => 'payment_method',
            'order_transaction_id' => 'transaction_id',
            'order_status' => 'status',

            // Company mappings
            'company_name' => 'name',
            'company_email' => 'email',
            'company_phone' => 'phone',
            'company_address' => 'address',
        ];
        if (isset($mappings[$variable])) {
            $mapping = $mappings[$variable];
            if (is_array($mapping)) {
        // Combine multiple fields (ex: first_name + last_name)
                $parts = [];
                foreach ($mapping as $field) {
                    if (isset($sectionData[$field])) {
                        $parts[] = $sectionData[$field];
                    }
                }
                return !empty($parts) ? implode(' ', $parts) : null;
            } else {
    // Mapping direct
                return isset($sectionData[$mapping]) ? (string) $sectionData[$mapping] : null;
            }
        }

        return null;
    }

    /**
     * Formate un numéro de commande selon les propriétés
     *
     * @param array $orderData Données de la commande
     * @param array $properties Propriétés de formatage
     * @return string Numéro formaté
     */
    private function formatOrderNumber(array $orderData, array $properties): string
    {
        $number = $orderData['number'] ?? $orderData['id'] ?? 'CMD-0001';
        $format = $properties['format'] ?? 'CMD-{order_number}';
        $date = $orderData['date'] ?? date('Y-m-d');
// Remplacement des placeholders dans le format
        $formatted = str_replace('{order_number}', $number, $format);
        $formatted = str_replace('{order_year}', date('Y', strtotime($date)), $formatted);
        $formatted = str_replace('{order_month}', date('m', strtotime($date)), $formatted);
        $formatted = str_replace('{order_day}', date('d', strtotime($date)), $formatted);
        $formatted = str_replace('{order_date}', date('d/m/Y', strtotime($date)), $formatted);
        return $formatted;
    }

    /**
     * Génère les styles CSS pour le texte
     *
     * @param array $properties Propriétés de style
     * @return string CSS généré
     */
    private function generateTextStyles(array $properties): string
    {
        $styles = array_merge(self::DEFAULT_STYLES, $properties);
        $css = [];
// Police et taille
        if (!empty($styles['font-family'])) {
            $css[] = "font-family: {$styles['font-family']}";
        }
        if (!empty($styles['font-size'])) {
            $css[] = "font-size: {$styles['font-size']}";
        }

        // Style de police
        if (!empty($styles['font-weight']) && $styles['font-weight'] !== 'normal') {
            $css[] = "font-weight: {$styles['font-weight']}";
        }
        if (!empty($styles['font-style']) && $styles['font-style'] !== 'normal') {
            $css[] = "font-style: {$styles['font-style']}";
        }

        // Couleur et décoration
        if (!empty($styles['color'])) {
            $css[] = "color: {$styles['color']}";
        }
        if (!empty($styles['text-decoration']) && $styles['text-decoration'] !== 'none') {
            $css[] = "text-decoration: {$styles['text-decoration']}";
        }

        // Alignement et espacement
        if (!empty($styles['text-align'])) {
            $css[] = "text-align: {$styles['text-align']}";
        }
        if (!empty($styles['line-height'])) {
            $css[] = "line-height: {$styles['line-height']}";
        }

        return implode('; ', $css) . ';';
    }

    /**
     * Génère le HTML pour le texte
     *
     * @param string $content Contenu à afficher
     * @param array $properties Propriétés d'affichage
     * @return string HTML généré
     */
    private function generateTextHtml(string $content, array $properties): string
    {
        $tag = $properties['html-tag'] ?? 'div';
        $class = $properties['css-class'] ?? 'pdf-text-element';
// Gestion du multiligne
        $processedContent = nl2br($content);
        return "<{$tag} class=\"{$class}\">{$processedContent}</{$tag}>";
    }

    /**
     * Valide les données d'un élément avant rendu
     *
     * @param array $elementData Données à valider
     * @return bool True si valide
     */
    private function validateElementData(array $elementData): bool
    {
        // Vérification du type
        $type = $elementData['type'] ?? '';
        if (!in_array($type, self::SUPPORTED_TYPES)) {
            return false;
        }

        // Pour dynamic-text, vérification du contenu
        if ($type === 'dynamic-text' && empty($elementData['content'])) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie si ce renderer supporte un type d'élément
     *
     * @param string $elementType Type d'élément
     * @return bool True si supporté
     */
    public function supportsElementType(string $elementType): bool
    {
        return in_array($elementType, self::SUPPORTED_TYPES);
    }

    /**
     * Liste des variables disponibles dans un contexte
     *
     * @param array $context Contexte de données
     * @return array Liste des variables disponibles
     */
    public function getAvailableVariables(array $context = []): array
    {
        $variables = array_keys(self::SYSTEM_VARIABLES);
// Variables depuis le contexte
        foreach (['customer', 'order', 'company'] as $section) {
            if (isset($context[$section]) && is_array($context[$section])) {
                $variables = array_merge($variables, array_keys($context[$section]));
            }
        }

        return array_unique($variables);
    }
}
