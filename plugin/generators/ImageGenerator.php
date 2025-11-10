<?php

namespace WP_PDF_Builder_Pro\Generators;

use WP_PDF_Builder_Pro\Interfaces\DataProviderInterface;

/**
 * Générateur d'images simple utilisant GD
 * Pour les previews quand les autres générateurs échouent
 */
class ImageGenerator extends BaseGenerator
{
    /**
     * Constructeur
     */
    public function __construct(array $template_data, DataProviderInterface $data_provider, array $options = [])
    {
        parent::__construct($template_data, $data_provider, $options);
    }

    /**
     * Initialise le générateur
     */
    protected function initialize(): void
    {
        if (!extension_loaded('gd')) {
            throw new \Exception('GD extension is not loaded');
        }
    }

    /**
     * Génère une image simple
     */
    public function generate(string $output_type = 'png')
    {
        try {
            $template_data = $this->template_data['template'] ?? $this->template_data;
// Dimensions du canvas
            $width = $template_data['canvasWidth'] ?? 595;
            $height = $template_data['canvasHeight'] ?? 842;
// Créer l'image
            $image = imagecreatetruecolor($width, $height);
            if (!$image) {
                throw new \Exception('Failed to create image');
            }

            // Fond blanc
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);
// Couleur noire pour le texte
            $black = imagecolorallocate($image, 0, 0, 0);
// Titre du template
            $title = $this->template_data['name'] ?? 'Template Preview';
            imagestring($image, 5, 50, 50, $title, $black);
// Informations de base
            $info_lines = [
                "Width: {$width}px",
                "Height: {$height}px",
                "Elements: " . count($template_data['elements'] ?? []),
                "Generated: " . date('Y-m-d H:i:s')
            ];
            $y = 80;
            foreach ($info_lines as $line) {
                imagestring($image, 5, 50, $y, $line, $black);
                $y += 20;
            }

            // Si output_file est spécifié, sauvegarder dans le fichier
            if (isset($this->options['output_file'])) {
                $output_path = $this->options['output_file'];
// Créer le répertoire si nécessaire
                $dir = dirname($output_path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                // Sauvegarder selon le format
                switch (strtolower($output_type)) {
                    case 'png':
                        $result = imagepng($image, $output_path);
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $result = imagejpeg($image, $output_path, $this->options['quality'] ?? 90);
                        break;
                    default:
                        $result = imagepng($image, $output_path);
                }

                imagedestroy($image);
                if (!$result) {
                    throw new \Exception("Failed to save image to: {$output_path}");
                }

                return [
                    'success' => true,
                    'format' => $output_type,
                    'file' => $output_path,
                    'generator' => 'image',
                    'is_fallback' => true
                ];
            }

            // Sinon, retourner les données de l'image
            ob_start();
            imagepng($image);
            $image_data = ob_get_clean();
            imagedestroy($image);
            return [
                'success' => true,
                'format' => 'png',
                'data' => $image_data,
                'generator' => 'image',
                'is_fallback' => true
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'generator' => 'image',
                'is_fallback' => true
            ];
        }
    }
}
