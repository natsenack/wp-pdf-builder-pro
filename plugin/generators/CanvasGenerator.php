<?php
namespace WP_PDF_Builder_Pro\Generators;

use WP_PDF_Builder_Pro\Interfaces\DataProviderInterface;

/**
 * Classe CanvasGenerator
 * Générateur d'aperçu utilisant Canvas 2D côté client (fallback)
 */
class CanvasGenerator extends BaseGenerator {

    /** @var string HTML généré pour le canvas */
    private $canvas_html;

    /** @var array Métriques de performance */
    private $performance_metrics;

    /**
     * {@inheritDoc}
     */
    protected function initialize(): void {
        $this->canvas_html = '';
        $this->performance_metrics = [
            'start_time' => microtime(true),
            'memory_start' => memory_get_usage(true),
            'render_time' => 0,
            'fallback_used' => true
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function generate(string $output_type = 'pdf') {
        $this->performance_metrics['render_time'] = microtime(true);

        try {
            // Générer le HTML Canvas avec JavaScript
            $this->canvas_html = $this->generateCanvasHTML();

            $this->performance_metrics['render_time'] = microtime(true) - $this->performance_metrics['render_time'];

            return [
                'success' => true,
                'format' => 'html',
                'content' => $this->canvas_html,
                'generator' => 'canvas',
                'performance' => $this->performance_metrics,
                'is_fallback' => true
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'generator' => 'canvas',
                'performance' => $this->performance_metrics,
                'is_fallback' => true
            ];
        }
    }

    /**
     * Génère le HTML avec Canvas pour l'aperçu
     *
     * @return string HTML du canvas
     */
    private function generateCanvasHTML(): string {
        $template_data = $this->template_data;
        $data_provider = $this->data_provider;

        // Dimensions A4 en pixels (approximatif pour aperçu)
        $width = 595;
        $height = 842;

        $html = '<div class="wp-pdf-canvas-preview" style="position: relative; width: ' . $width . 'px; height: ' . $height . 'px; background: white; border: 1px solid #ddd;">';
        $html .= '<canvas id="pdf-canvas-' . uniqid() . '" width="' . $width . '" height="' . $height . '" style="display: block;"></canvas>';

        // JavaScript pour rendre les éléments
        $html .= '<script>';
        $html .= 'function renderCanvasPreview() {';
        $html .= 'const canvas = document.querySelector("canvas");';
        $html .= 'if (!canvas) return;';
        $html .= 'const ctx = canvas.getContext("2d");';

        // Fond blanc
        $html .= 'ctx.fillStyle = "white";';
        $html .= 'ctx.fillRect(0, 0, ' . $width . ', ' . $height . ');';

        // Rendre les éléments du template
        if (isset($template_data['template']['elements'])) {
            foreach ($template_data['template']['elements'] as $element) {
                $html .= $this->generateCanvasElementJS($element, $width, $height);
            }
        }

        // Message par défaut si pas d'éléments
        if (empty($template_data['template']['elements'])) {
            $html .= $this->generateDefaultCanvasMessage($width, $height);
        }

        $html .= '}';
        $html .= 'renderCanvasPreview();';
        $html .= '</script>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Génère le JavaScript pour rendre un élément sur le canvas
     *
     * @param array $element Données de l'élément
     * @param int $canvas_width Largeur du canvas
     * @param int $canvas_height Hauteur du canvas
     * @return string JavaScript pour rendre l'élément
     */
    private function generateCanvasElementJS(array $element, int $canvas_width, int $canvas_height): string {
        $type = $element['type'] ?? 'text';
        $x = intval($element['x'] ?? 0);
        $y = intval($element['y'] ?? 0);
        $width = intval($element['width'] ?? 100);
        $height = intval($element['height'] ?? 50);

        // Conversion des coordonnées relatives en pixels
        $pixel_x = ($x / 100) * $canvas_width;
        $pixel_y = ($y / 100) * $canvas_height;
        $pixel_width = ($width / 100) * $canvas_width;
        $pixel_height = ($height / 100) * $canvas_height;

        $js = '';

        switch ($type) {
            case 'text':
                $js .= $this->generateCanvasTextJS($element, $pixel_x, $pixel_y, $pixel_width, $pixel_height);
                break;
            case 'rectangle':
                $js .= $this->generateCanvasRectangleJS($element, $pixel_x, $pixel_y, $pixel_width, $pixel_height);
                break;
        }

        return $js;
    }

    /**
     * Génère le JavaScript pour rendre un élément texte
     */
    private function generateCanvasTextJS(array $element, float $x, float $y, float $width, float $height): string {
        $text = addslashes($element['content'] ?? '');
        $font_size = intval($element['fontSize'] ?? 12);
        $color = $element['color'] ?? '#000000';

        $js = 'ctx.fillStyle = "' . $color . '";';
        $js .= 'ctx.font = "' . $font_size . 'px Arial";';
        $js .= 'ctx.textAlign = "center";';
        $js .= 'ctx.textBaseline = "middle";';
        $js .= 'ctx.fillText("' . $text . '", ' . ($x + $width / 2) . ', ' . ($y + $height / 2) . ');';

        return $js;
    }

    /**
     * Génère le JavaScript pour rendre un élément rectangle
     */
    private function generateCanvasRectangleJS(array $element, float $x, float $y, float $width, float $height): string {
        $background_color = $element['backgroundColor'] ?? '#ffffff';
        $border_color = $element['borderColor'] ?? '#000000';

        $js = 'ctx.fillStyle = "' . $background_color . '";';
        $js .= 'ctx.fillRect(' . $x . ', ' . $y . ', ' . $width . ', ' . $height . ');';
        $js .= 'ctx.strokeStyle = "' . $border_color . '";';
        $js .= 'ctx.lineWidth = 1;';
        $js .= 'ctx.strokeRect(' . $x . ', ' . $y . ', ' . $width . ', ' . $height . ');';

        return $js;
    }

    /**
     * Génère le message par défaut quand il n'y a pas d'éléments
     */
    private function generateDefaultCanvasMessage(int $width, int $height): string {
        $js = 'ctx.fillStyle = "#666";';
        $js .= 'ctx.font = "24px Arial";';
        $js .= 'ctx.textAlign = "center";';
        $js .= 'ctx.fillText("Aperçu PDF Builder Pro", ' . ($width / 2) . ', ' . ($height / 2 - 20) . ');';

        $js .= 'ctx.font = "16px Arial";';
        $js .= 'ctx.fillText("Ajoutez des éléments pour voir l\'aperçu", ' . ($width / 2) . ', ' . ($height / 2 + 20) . ');';

        return $js;
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedFormats(): array {
        return ['png', 'jpg', 'html'];
    }

    /**
     * {@inheritDoc}
     */
    public function getCapabilities(): array {
        return ['fast', 'client_side', 'fallback'];
    }

    /**
     * {@inheritDoc}
     */
    public function getPerformanceMetrics(): array {
        return $this->performance_metrics;
    }
}