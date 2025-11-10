<?php

/**
 * PDF Builder Pro - ImageRenderer
 * Phase 3.3.2 - Renderer spécialisé pour les éléments image
 *
 * Gère le rendu des éléments image avec redimensionnement et optimisation :
 * - company_logo : Logos d'entreprise avec redimensionnement automatique
 */

namespace PDF_Builder\Renderers;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class ImageRenderer
{
    /**
     * Types d'éléments supportés par ce renderer
     */
    const SUPPORTED_TYPES = ['company_logo'];
/**
     * Formats d'image supportés
     */
    const SUPPORTED_FORMATS = ['jpg', 'jpeg', 'png', 'svg', 'gif', 'bmp', 'tiff', 'ico', 'webp'];
/**
     * Dimensions par défaut pour les images
     */
    const DEFAULT_DIMENSIONS = [
        'width' => 200,
        'height' => 100,
        'maintain_aspect_ratio' => true
    ];
/**
     * Styles CSS par défaut pour les images
     */
    const DEFAULT_STYLES = [
        'border-width' => '0px',
        'border-style' => 'none',
        'border-color' => '#000000',
        'border-radius' => '0px',
        'object-fit' => 'contain',
        'opacity' => '1.0'
    ];

    /**
     * Rend un élément image
     *
     * @param array $element Propriétés de l'élément
     * @param array $context Contexte de rendu (données WooCommerce, etc.)
     * @return string HTML généré pour l'image
     */
    public function render(array $element, array $context = []): array
    {
        // Validation de base
        if (!$this->validateElement($element)) {
            return [
                'html' => $this->getErrorPlaceholder('Élément image invalide'),
                'css' => '',
                'error' => 'Élément image invalide'
            ];
        }

        // Récupération des propriétés
        $properties = $element['properties'] ?? [];
        $imageUrl = $this->getImageUrl($properties, $context);
        if (empty($imageUrl)) {
            return [
                'html' => $this->getErrorPlaceholder('Aucune image spécifiée'),
                'css' => '',
                'error' => 'Aucune image spécifiée'
            ];
        }

        // Validation du format d'image
        if (!$this->isValidImageFormat($imageUrl)) {
            return [
                'html' => $this->getErrorPlaceholder('Format d\'image non supporté'),
                'css' => '',
                'error' => 'Format d\'image non supporté'
            ];
        }

        // Calcul des dimensions
        $dimensions = $this->calculateDimensions($properties);
// Génération des styles CSS
        $styles = $this->generateImageStyles($properties);
// Génération du HTML et CSS
        $html = $this->generateImageHtml($imageUrl, $dimensions, $styles, $properties);
        $css = '';
// TODO: Implémenter la génération CSS pour les images si nécessaire

        return [
            'html' => $html,
            'css' => $css,
            'error' => null
        ];
    }

    /**
     * Valide les propriétés de l'élément
     *
     * @param array $element Élément à valider
     * @return bool True si valide
     */
    private function validateElement(array $element): bool
    {
        return isset($element['type']) &&
               in_array($element['type'], self::SUPPORTED_TYPES) &&
               isset($element['properties']);
    }

    /**
     * Récupère l'URL de l'image depuis les propriétés ou le contexte
     *
     * @param array $properties Propriétés de l'élément
     * @param array $context Contexte de données
     * @return string URL de l'image ou chaîne vide
     */
    private function getImageUrl(array $properties, array $context): string
    {
        // Priorité : src > imageUrl (pour compatibilité)
        $imageUrl = $properties['src'] ?? $properties['imageUrl'] ?? '';
// Si c'est une variable dynamique, la remplacer
        if (strpos($imageUrl, '{{') !== false && strpos($imageUrl, '}}') !== false) {
            $imageUrl = $this->replaceVariables($imageUrl, $context);
        }

        // Résoudre les URLs relatives
        if (!empty($imageUrl) && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $imageUrl = $this->resolveRelativeUrl($imageUrl);
        }

        return $imageUrl;
    }

    /**
     * Remplace les variables dynamiques dans l'URL
     *
     * @param string $url URL avec variables
     * @param array $context Contexte de données
     * @return string URL avec variables remplacées
     */
    private function replaceVariables(string $url, array $context): string
    {
        // Variables de contexte (logo entreprise depuis WooCommerce)
        $replacements = [
            '{{company_logo}}' => $context['company_logo'] ?? '',
            '{{store_logo}}' => $context['store_logo'] ?? '',
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $url);
    }

    /**
     * Résout les URLs relatives vers des URLs absolues
     *
     * @param string $url URL relative
     * @return string URL absolue
     */
    private function resolveRelativeUrl(string $url): string
    {
        // Si c'est déjà une URL absolue, la retourner telle quelle
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // Résoudre les URLs relatives du thème/plugin
        if (strpos($url, '/') === 0) {
// URL absolue du site
            return home_url($url);
        }

        // URL relative au thème actif
        return '/wp-content/themes/' . basename(getcwd()) . '/' . $url;
    }

    /**
     * Vérifie si le format d'image est supporté
     *
     * @param string $imageUrl URL de l'image
     * @return bool True si format supporté
     */
    private function isValidImageFormat(string $imageUrl): bool
    {
        $extension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
// Pour les URLs sans extension (comme les data URIs), accepter
        if (empty($extension)) {
            return true;
        }

        return in_array($extension, self::SUPPORTED_FORMATS);
    }

    /**
     * Calcule les dimensions finales de l'image
     *
     * @param array $properties Propriétés de l'élément
     * @return array Dimensions [width, height]
     */
    private function calculateDimensions(array $properties): array
    {
        $width = $properties['width'] ?? self::DEFAULT_DIMENSIONS['width'];
        $height = $properties['height'] ?? self::DEFAULT_DIMENSIONS['height'];
        $maintainAspectRatio = $properties['maintainAspectRatio'] ?? self::DEFAULT_DIMENSIONS['maintain_aspect_ratio'];
// Validation des dimensions
        $width = max(1, min(2000, (int)$width));
// 1px à 2000px
        $height = max(1, min(2000, (int)$height));
// TODO: Implémenter le redimensionnement automatique selon le ratio d'aspect
        // Cela nécessiterait de charger l'image et calculer ses dimensions réelles

        return [
            'width' => $width,
            'height' => $height,
            'maintain_aspect_ratio' => $maintainAspectRatio
        ];
    }

    /**
     * Génère les styles CSS pour l'image
     *
     * @param array $properties Propriétés de l'élément
     * @return string Styles CSS inline
     */
    private function generateImageStyles(array $properties): string
    {
        $styles = self::DEFAULT_STYLES;
// Propriétés de bordure
        if (isset($properties['borderWidth'])) {
            $styles['border-width'] = $this->sanitizeCssValue($properties['borderWidth'], '0px');
        }
        if (isset($properties['borderStyle'])) {
            $styles['border-style'] = $this->sanitizeCssValue($properties['borderStyle'], 'none');
        }
        if (isset($properties['borderColor'])) {
            $styles['border-color'] = $this->sanitizeCssValue($properties['borderColor'], '#000000');
        }

        // Bordures arrondies
        if (isset($properties['borderRadius'])) {
            $styles['border-radius'] = $this->sanitizeCssValue($properties['borderRadius'], '0px');
        }

        // Ajustement de l'objet
        if (isset($properties['objectFit'])) {
            $validFits = ['fill', 'contain', 'cover', 'none', 'scale-down'];
            $styles['object-fit'] = in_array($properties['objectFit'], $validFits) ? $properties['objectFit'] : 'contain';
        }

        // Opacité
        if (isset($properties['opacity'])) {
            $opacity = (float)$properties['opacity'];
            $styles['opacity'] = max(0, min(1, $opacity));
        }

        // Conversion en chaîne CSS
        return $this->arrayToCss($styles);
    }

    /**
     * Génère le HTML pour l'image
     *
     * @param string $imageUrl URL de l'image
     * @param array $dimensions Dimensions calculées
     * @param string $styles Styles CSS
     * @param array $properties Propriétés originales
     * @return string HTML de l'image
     */
    private function generateImageHtml(string $imageUrl, array $dimensions, string $styles, array $properties): string
    {
        $alt = $properties['alt'] ?? $properties['label'] ?? 'Image';
        $title = $properties['title'] ?? $alt;
// Placeholder par défaut pour le lazy loading
        $placeholder = $properties['placeholder'] ?? 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkxvYWRpbmcuLi48L3RleHQ+PC9zdmc+';
        $html = sprintf('<img data-src="%s" src="%s" alt="%s" title="%s" loading="lazy" style="width: %dpx; height: %dpx; %s" class="lazy-image" />', htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'), htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8'), htmlspecialchars($alt, ENT_QUOTES, 'UTF-8'), htmlspecialchars($title, ENT_QUOTES, 'UTF-8'), $dimensions['width'], $dimensions['height'], $styles);
        return $html;
    }

    /**
     * Génère un placeholder d'erreur
     *
     * @param string $message Message d'erreur
     * @return string HTML du placeholder
     */
    private function getErrorPlaceholder(string $message): string
    {
        return sprintf('<div style="border: 1px solid #ff0000; padding: 10px; background-color: #ffe6e6; color: #ff0000; font-size: 12px;">%s</div>', htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Nettoie une valeur CSS
     *
     * @param mixed $value Valeur à nettoyer
     * @param string $default Valeur par défaut
     * @return string Valeur nettoyée
     */
    private function sanitizeCssValue($value, string $default): string
    {
        if (is_null($value) || $value === '') {
            return $default;
        }

        // Pour les couleurs, valider le format
        if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $value)) {
            return $value;
        }

        // Pour les tailles, accepter px, em, rem, %
        if (preg_match('/^(\d+(?:\.\d+)?)(px|em|rem|%)$/', $value)) {
            return $value;
        }

        // Pour les mots-clés CSS courants
        $allowedKeywords = ['none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset'];
        if (in_array(strtolower($value), $allowedKeywords)) {
            return $value;
        }

        return $default;
    }

    /**
     * Convertit un tableau de styles en chaîne CSS
     *
     * @param array $styles Tableau de styles
     * @return string Chaîne CSS
     */
    private function arrayToCss(array $styles): string
    {
        $css = [];
        foreach ($styles as $property => $value) {
            $css[] = sprintf('%s: %s', $property, $value);
        }
        return implode('; ', $css);
    }

    /**
     * Vérifie si ce renderer supporte le type d'élément
     *
     * @param string $type Type d'élément
     * @return bool True si supporté
     */
    public function supports(string $type): bool
    {
        return in_array($type, self::SUPPORTED_TYPES);
    }

    /**
     * Retourne les types supportés
     *
     * @return array Types d'éléments supportés
     */
    public function getSupportedTypes(): array
    {
        return self::SUPPORTED_TYPES;
    }
}
