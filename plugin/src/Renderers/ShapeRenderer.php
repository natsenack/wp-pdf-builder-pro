<?php
/**
 * PDF Builder Pro - ShapeRenderer
 * Phase 3.3.3 - Renderer spécialisé pour les formes géométriques
 *
 * Gère le rendu des éléments de formes :
 * - rectangle : Rectangles avec coins arrondis
 * - circle : Cercles et ellipses
 * - line : Lignes droites
 * - arrow : Flèches directionnelles
 */

namespace PDF_Builder\Renderers;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class ShapeRenderer {

    /**
     * Types d'éléments supportés par ce renderer
     */
    const SUPPORTED_TYPES = ['rectangle', 'circle', 'line', 'arrow'];

    /**
     * Styles CSS par défaut pour les formes
     */
    const DEFAULT_STYLES = [
        'fill' => 'transparent',
        'stroke' => '#000000',
        'stroke-width' => '1px',
        'stroke-dasharray' => 'none',
        'opacity' => '1'
    ];

    /**
     * Dimensions minimales pour les formes
     */
    const MIN_DIMENSIONS = [
        'width' => 10,
        'height' => 10
    ];

    /**
     * Rend un élément de forme géométrique
     *
     * @param array $element Configuration de l'élément
     * @param array $context Contexte de rendu (variables, etc.)
     * @return string HTML/CSS généré
     */
    public function render(array $element, array $context = []): array {
        // Validation de base
        if (!$this->validateElement($element)) {
            return [
                'html' => $this->generateErrorHtml('Élément de forme invalide'),
                'css' => '',
                'error' => 'Élément de forme invalide'
            ];
        }

        // Déterminer le type de forme
        $shapeType = $element['type'] ?? '';

        // Générer le HTML selon le type
        switch ($shapeType) {
            case 'rectangle':
                $html = $this->renderRectangle($element, $context);
                break;
            case 'circle':
                $html = $this->renderCircle($element, $context);
                break;
            case 'line':
                $html = $this->renderLine($element, $context);
                break;
            case 'arrow':
                $html = $this->renderArrow($element, $context);
                break;
            default:
                $html = $this->generateErrorHtml("Type de forme non supporté: {$shapeType}");
                break;
        }

        return [
            'html' => $html,
            'css' => '',
            'error' => null
        ];
    }

    /**
     * Rend un rectangle
     */
    private function renderRectangle(array $element, array $context): string {
        $styles = $this->generateShapeStyles($element);
        $position = $this->getElementPosition($element);
        $dimensions = $this->getElementDimensions($element);

        // Coins arrondis
        $borderRadius = $element['properties']['borderRadius'] ?? 0;

        $html = sprintf(
            '<div class="pdf-shape pdf-rectangle" style="%s %s %s border-radius: %dpx;"></div>',
            $position,
            $dimensions['dimensions'],
            $styles,
            $borderRadius
        );

        return $html;
    }

    /**
     * Rend un cercle
     */
    private function renderCircle(array $element, array $context): string {
        $styles = $this->generateShapeStyles($element);
        $position = $this->getElementPosition($element);
        $dimensions = $this->getElementDimensions($element);

        // Pour un cercle, width = height = diameter
        $size = min($dimensions['width'], $dimensions['height']);
        $circleDimensions = sprintf('width: %dpx; height: %dpx;', $size, $size);

        $html = sprintf(
            '<div class="pdf-shape pdf-circle" style="%s %s %s border-radius: 50%%;"></div>',
            $position,
            $circleDimensions,
            $styles
        );

        return $html;
    }

    /**
     * Rend une ligne droite
     */
    private function renderLine(array $element, array $context): string {
        $styles = $this->generateShapeStyles($element);
        $position = $this->getElementPosition($element);
        $dimensions = $this->getElementDimensions($element);

        // Pour une ligne, on utilise border-top avec hauteur minimale
        $lineStyles = sprintf(
            'border-top: %s solid %s; height: 0px; width: %dpx;',
            $element['properties']['strokeWidth'] ?? '1px',
            $element['properties']['stroke'] ?? '#000000',
            $dimensions['width']
        );

        $html = sprintf(
            '<div class="pdf-shape pdf-line" style="%s %s %s"></div>',
            $position,
            $dimensions['dimensions'],
            $lineStyles
        );

        return $html;
    }

    /**
     * Rend une flèche
     */
    private function renderArrow(array $element, array $context): string {
        $styles = $this->generateShapeStyles($element);
        $position = $this->getElementPosition($element);
        $dimensions = $this->getElementDimensions($element);

        // Direction de la flèche
        $direction = $element['properties']['direction'] ?? 'right';

        // Générer le SVG pour la flèche
        $svg = $this->generateArrowSvg($dimensions['width'], $dimensions['height'], $direction, $element['properties']);

        $html = sprintf(
            '<div class="pdf-shape pdf-arrow" style="%s">%s</div>',
            $position,
            $svg
        );

        return $html;
    }

    /**
     * Génère le SVG pour une flèche
     */
    private function generateArrowSvg(int $width, int $height, string $direction, array $properties): string {
        $stroke = $properties['stroke'] ?? '#000000';
        $strokeWidth = intval($properties['strokeWidth'] ?? 1);
        $fill = $properties['fill'] ?? 'transparent';

        // Calculer les points selon la direction
        switch ($direction) {
            case 'right':
                $points = sprintf('%d,%d %d,%d %d,%d %d,%d %d,%d',
                    0, $height/4,                    // Point gauche haut
                    $width*2/3, $height/4,           // Point milieu haut
                    $width*2/3, 0,                   // Pointe haut
                    $width, $height/2,               // Pointe droite
                    $width*2/3, $height,             // Pointe bas
                    $width*2/3, $height*3/4,         // Point milieu bas
                    0, $height*3/4                   // Point gauche bas
                );
                break;
            case 'left':
                $points = sprintf('%d,%d %d,%d %d,%d %d,%d %d,%d',
                    $width, $height/4,
                    $width/3, $height/4,
                    $width/3, 0,
                    0, $height/2,
                    $width/3, $height,
                    $width/3, $height*3/4,
                    $width, $height*3/4
                );
                break;
            case 'up':
                $points = sprintf('%d,%d %d,%d %d,%d %d,%d %d,%d',
                    $width/4, $height,
                    $width/4, $height/3,
                    0, $height/3,
                    $width/2, 0,
                    $width, $height/3,
                    $width*3/4, $height/3,
                    $width*3/4, $height
                );
                break;
            case 'down':
                $points = sprintf('%d,%d %d,%d %d,%d %d,%d %d,%d',
                    $width/4, 0,
                    $width/4, $height*2/3,
                    0, $height*2/3,
                    $width/2, $height,
                    $width, $height*2/3,
                    $width*3/4, $height*2/3,
                    $width*3/4, 0
                );
                break;
            default:
                $points = sprintf('%d,%d %d,%d %d,%d %d,%d %d,%d',
                    0, $height/4,
                    $width*2/3, $height/4,
                    $width*2/3, 0,
                    $width, $height/2,
                    $width*2/3, $height,
                    $width*2/3, $height*3/4,
                    0, $height*3/4
                );
        }

        $svg = sprintf(
            '<svg width="%d" height="%d" viewBox="0 0 %d %d" style="display: block;">
                <polygon points="%s" fill="%s" stroke="%s" stroke-width="%d"/>
            </svg>',
            $width, $height, $width, $height, $points, $fill, $stroke, $strokeWidth
        );

        return $svg;
    }

    /**
     * Génère les styles CSS pour une forme
     */
    private function generateShapeStyles(array $element): string {
        $properties = $element['properties'] ?? [];
        $styles = [];

        // Fond (fill) - utiliser valeur par défaut si non définie
        $fill = $properties['fill'] ?? self::DEFAULT_STYLES['fill'];
        if ($fill !== 'transparent') {
            $styles[] = "background-color: {$fill}";
        }

        // Bordure (stroke) - utiliser valeur par défaut si non définie
        $stroke = $properties['stroke'] ?? self::DEFAULT_STYLES['stroke'];
        $strokeWidth = $properties['strokeWidth'] ?? self::DEFAULT_STYLES['stroke-width'];
        $styles[] = "border: {$strokeWidth} solid {$stroke}";

        // Opacité - utiliser valeur par défaut si non définie
        $opacity = $properties['opacity'] ?? self::DEFAULT_STYLES['opacity'];
        if ($opacity !== '1') {
            $styles[] = "opacity: {$opacity}";
        }

        // Styles dashés
        if (isset($properties['strokeDasharray']) && $properties['strokeDasharray'] !== 'none') {
            $styles[] = "border-style: dashed";
        }

        return implode(' ', $styles);
    }

    /**
     * Récupère la position CSS de l'élément
     */
    private function getElementPosition(array $element): string {
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;

        return sprintf('position: absolute; left: %dpx; top: %dpx;', $x, $y);
    }

    /**
     * Récupère les dimensions CSS de l'élément
     */
    private function getElementDimensions(array $element): array {
        $width = max($element['width'] ?? 100, self::MIN_DIMENSIONS['width']);
        $height = max($element['height'] ?? 100, self::MIN_DIMENSIONS['height']);

        return [
            'width' => $width,
            'height' => $height,
            'dimensions' => sprintf('width: %dpx; height: %dpx;', $width, $height)
        ];
    }

    /**
     * Valide la structure de l'élément
     */
    private function validateElement(array $element): bool {
        return isset($element['type']) &&
               in_array($element['type'], self::SUPPORTED_TYPES) &&
               isset($element['x']) && isset($element['y']) &&
               isset($element['width']) && isset($element['height']);
    }

    /**
     * Génère le HTML d'erreur
     */
    private function generateErrorHtml(string $message): string {
        return sprintf(
            '<div class="pdf-shape-error" style="color: red; font-size: 12px; padding: 4px; border: 1px solid red; background: #ffe6e6;">%s</div>',
            htmlspecialchars($message)
        );
    }

    /**
     * Vérifie si ce renderer supporte le type d'élément
     */
    public function supports(string $elementType): bool {
        return in_array($elementType, self::SUPPORTED_TYPES);
    }

    /**
     * Retourne les types supportés
     */
    public function getSupportedTypes(): array {
        return self::SUPPORTED_TYPES;
    }
}