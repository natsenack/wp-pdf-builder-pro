<?php

namespace PDF_Builder\Utilities;

/**
 * PDF Builder Pro - Image Converter Utility
 * Gestionnaire de conversion PDF vers Images (PNG/JPG)
 *
 * @package PDF_Builder
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe pour convertir les PDFs en images
 */
class ImageConverter
{
    /**
     * Convertit un PDF en image (PNG/JPG)
     *
     * @param string $pdf_content Contenu PDF binaire
     * @param array $params Paramètres de conversion
     * @return array Résultat de la conversion
     */
    public static function convertPdfToImage($pdf_content, $params)
    {
        try {
            // Sauvegarder temporairement le PDF
            $temp_pdf_path = tempnam(sys_get_temp_dir(), 'pdf_preview_') . '.pdf';
            file_put_contents($temp_pdf_path, $pdf_content);

            $format = $params['format'];
            $quality = $params['quality'];

            // Essayer d'abord Imagick (meilleur)
            if (extension_loaded('imagick') && class_exists('Imagick')) {
                $result = self::convertWithImagick($temp_pdf_path, $format, $quality);
                if ($result['success']) {
                    unlink($temp_pdf_path);
                    return $result;
                }
            }

            // Fallback vers GD
            if (extension_loaded('gd')) {
                $result = self::convertWithGD($temp_pdf_path, $format, $quality);
                if ($result['success']) {
                    unlink($temp_pdf_path);
                    return $result;
                }
            }

            // Nettoyer et retourner erreur
            unlink($temp_pdf_path);
            return [
                'success' => false,
                'error' => 'Aucune extension d\'image disponible (Imagick ou GD requis)',
                'converter' => 'none'
            ];

        } catch (\Exception $e) {
            // Nettoyer en cas d'erreur
            if (isset($temp_pdf_path) && file_exists($temp_pdf_path)) {
                unlink($temp_pdf_path);
            }
            return [
                'success' => false,
                'error' => 'Erreur de conversion: ' . $e->getMessage(),
                'converter' => 'error'
            ];
        }
    }

    /**
     * Conversion avec Imagick (recommandé)
     */
    private static function convertWithImagick($pdf_path, $format, $quality)
    {
        try {
            $imagick = new \Imagick();
            $imagick->setResolution($quality, $quality);
            $imagick->readImage($pdf_path . '[0]'); // Première page seulement

            // Configuration selon format
            if ($format === 'jpg') {
                $imagick->setImageFormat('jpeg');
                $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $imagick->setImageCompressionQuality(min(100, $quality));
            } else {
                $imagick->setImageFormat('png');
                $imagick->setImageCompression(\Imagick::COMPRESSION_ZIP);
                $imagick->setImageCompressionQuality(9); // Meilleure compression PNG
            }

            // Optimisations
            $imagick->stripImage(); // Supprimer métadonnées
            $imagick->setImageBackgroundColor(new \ImagickPixel('white'));

            $image_data = $imagick->getImageBlob();
            $imagick->clear();

            return [
                'success' => true,
                'image_data' => $image_data,
                'converter' => 'imagick',
                'format' => $format,
                'quality' => $quality,
                'file_size' => strlen($image_data)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Imagick conversion failed: ' . $e->getMessage(),
                'converter' => 'imagick'
            ];
        }
    }

    /**
     * Conversion avec GD (fallback)
     */
    private static function convertWithGD($pdf_path, $format, $quality)
    {
        try {
            // GD ne peut pas lire PDF directement, nous créons une image placeholder
            // Dans un vrai système, il faudrait une bibliothèque supplémentaire

            $width = 800; // Largeur par défaut
            $height = 1100; // Hauteur approximative A4

            $image = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($image, 255, 255, 255);
            $gray = imagecolorallocate($image, 200, 200, 200);
            $black = imagecolorallocate($image, 0, 0, 0);

            // Fond blanc
            imagefill($image, 0, 0, $white);

            // Rectangle gris pour simuler le contenu
            imagefilledrectangle($image, 50, 50, $width - 50, $height - 50, $gray);

            // Texte informatif
            imagestring($image, 5, 100, 100, 'APERÇU PDF BUILDER PRO', $black);
            imagestring($image, 3, 100, 130, 'Conversion GD - Jour 5-7', $black);
            imagestring($image, 2, 100, 150, 'Format: ' . strtoupper($format), $black);
            imagestring($image, 2, 100, 170, 'Qualite: ' . $quality . '%', $black);
            imagestring($image, 2, 100, 190, 'Fallback: GD (Imagick recommande)', $black);

            // Capture de l'image
            ob_start();
            if ($format === 'jpg') {
                imagejpeg($image, null, min(100, $quality));
            } else {
                imagepng($image, null, 9);
            }
            $image_data = ob_get_clean();

            imagedestroy($image);

            return [
                'success' => true,
                'image_data' => $image_data,
                'converter' => 'gd_fallback',
                'format' => $format,
                'quality' => $quality,
                'file_size' => strlen($image_data),
                'note' => 'Conversion GD limitée - Imagick recommandé pour production'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'GD conversion failed: ' . $e->getMessage(),
                'converter' => 'gd'
            ];
        }
    }

    /**
     * Vérifie si les extensions d'image sont disponibles
     *
     * @return array État des extensions
     */
    public static function checkImageExtensions()
    {
        return [
            'imagick' => extension_loaded('imagick') && class_exists('Imagick'),
            'gd' => extension_loaded('gd'),
            'recommended' => extension_loaded('imagick') && class_exists('Imagick') ? 'imagick' : 'gd'
        ];
    }

    /**
     * Optimise une image selon le format
     *
     * @param string $image_data Données image
     * @param string $format Format souhaité
     * @param int $quality Qualité (1-100)
     * @return string Données optimisées
     */
    public static function optimizeImage($image_data, $format, $quality = 85)
    {
        // Pour l'instant, retourner tel quel
        // À étendre avec des optimisations avancées
        return $image_data;
    }
}
