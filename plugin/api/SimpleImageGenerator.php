<?php
namespace WP_PDF_Builder_Pro\Api;

/**
 * Générateur d'image simple basé sur HTML avec support GD
 * Version minimale sans dépendances externes
 */
class SimpleImageGenerator {
    
    private $width = 500;
    private $height = 700;
    private $background_color = '#ffffff';
    private $dpi = 96;
    
    public function __construct($width = 500, $height = 700) {
        $this->width = $width;
        $this->height = $height;
    }
    
    /**
     * Générer une image PNG à partir des données de template
     */
    public function generateFromTemplate($template_data, $quality = 150) {
        error_log('[SimpleImageGenerator] Génération image à partir du template');
        
        try {
            // Parser le template_data
            $template = $this->parseTemplate($template_data);
            
            // Créer image GD
            $image = imagecreatetruecolor($this->width, $this->height);
            
            // Remplir avec la couleur de fond
            $bg_color = $this->hexToRGB($this->background_color);
            $bg = imagecolorallocate($image, $bg_color['r'], $bg_color['g'], $bg_color['b']);
            imagefill($image, 0, 0, $bg);
            
            // Charger une police de base
            $font_file = $this->getFontPath();
            
            // Dessiner les éléments
            if (!empty($template['elements']) && is_array($template['elements'])) {
                $this->drawElements($image, $template['elements'], $font_file);
            }
            
            // Sauvegarder temporairement
            $temp_file = tempnam(sys_get_temp_dir(), 'pdf_preview_') . '.png';
            imagepng($image, $temp_file, 9);
            imagedestroy($image);
            
            error_log('[SimpleImageGenerator] Image générée: ' . $temp_file);
            return $temp_file;
            
        } catch (\Exception $e) {
            error_log('[SimpleImageGenerator] Erreur: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Parser les données du template
     */
    private function parseTemplate($template_data) {
        if (is_string($template_data)) {
            $template_data = json_decode(stripslashes($template_data), true);
        }
        
        // Supporter deux formats
        if (isset($template_data['template']) && isset($template_data['template']['elements'])) {
            return $template_data['template'];
        } elseif (isset($template_data['elements'])) {
            return $template_data;
        }
        
        return ['elements' => []];
    }
    
    /**
     * Dessiner les éléments sur l'image
     */
    private function drawElements(&$image, $elements, $font_file) {
        foreach ($elements as $element) {
            if (!is_array($element)) continue;
            
            $type = $element['type'] ?? 'text';
            
            switch ($type) {
                case 'text':
                    $this->drawTextElement($image, $element, $font_file);
                    break;
                case 'rectangle':
                    $this->drawRectangleElement($image, $element);
                    break;
                case 'image':
                    // Images - pour plus tard
                    break;
                default:
                    $this->drawTextElement($image, $element, $font_file);
            }
        }
    }
    
    /**
     * Dessiner un élément texte
     */
    private function drawTextElement(&$image, $element, $font_file) {
        try {
            $content = $element['content'] ?? 'Texte';
            $x = intval($element['x'] ?? 10);
            $y = intval($element['y'] ?? 10);
            $font_size = intval($element['fontSize'] ?? 12);
            $color = $element['color'] ?? '#000000';
            
            // Convertir couleur hex en RGB
            $rgb = $this->hexToRGB($color);
            $text_color = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);
            
            // Dessiner texte (utiliser imagettftext si FreeType disponible)
            if (function_exists('imagettftext') && file_exists($font_file)) {
                // Avec FreeType pour meilleure qualité
                imagettftext($image, $font_size, 0, $x, $y + $font_size, $text_color, $font_file, $content);
            } else {
                // Fallback avec imagestring
                imagestring($image, 1, $x, $y, $content, $text_color);
            }
            
        } catch (\Exception $e) {
            error_log('[SimpleImageGenerator] Erreur texte: ' . $e->getMessage());
        }
    }
    
    /**
     * Dessiner un élément rectangle
     */
    private function drawRectangleElement(&$image, $element) {
        try {
            $x = intval($element['x'] ?? 0);
            $y = intval($element['y'] ?? 0);
            $width = intval($element['width'] ?? 100);
            $height = intval($element['height'] ?? 100);
            $color = $element['backgroundColor'] ?? '#f0f0f0';
            
            $rgb = $this->hexToRGB($color);
            $fill_color = imagecolorallocate($image, $rgb['r'], $rgb['g'], $rgb['b']);
            
            imagefilledrectangle($image, $x, $y, $x + $width, $y + $height, $fill_color);
            
        } catch (\Exception $e) {
            error_log('[SimpleImageGenerator] Erreur rectangle: ' . $e->getMessage());
        }
    }
    
    /**
     * Convertir couleur hex en RGB
     */
    private function hexToRGB($hex) {
        // Enlever #
        $hex = ltrim($hex, '#');
        
        // Par défaut noir
        if (strlen($hex) !== 6 && strlen($hex) !== 3) {
            return ['r' => 0, 'g' => 0, 'b' => 0];
        }
        
        // Convertir
        if (strlen($hex) === 3) {
            $r = hexdec(str_repeat($hex[0], 2));
            $g = hexdec(str_repeat($hex[1], 2));
            $b = hexdec(str_repeat($hex[2], 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        
        return ['r' => $r, 'g' => $g, 'b' => $b];
    }
    
    /**
     * Obtenir le chemin d'une police de caractères
     */
    private function getFontPath() {
        // Chercher DejaVuSans.ttf
        $common_paths = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/System/Library/Fonts/Arial.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
        ];
        
        foreach ($common_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Fallback - pas de police TTF disponible
        return false;
    }
}
?>
