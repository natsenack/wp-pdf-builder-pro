<?php

/**
 * PDF Builder Pro - Preview Generator
 * Génère des aperçus PDF pour l'éditeur
 */

namespace PDF_Builder\AJAX\Managers;

class PDF_Builder_Preview_Generator
{
    private $template_data;
    private $preview_type;
    private $order_id;
    private $cache_key;

    public function __construct($template_data, $preview_type = 'editor', $order_id = null)
    {
        $this->template_data = $template_data;
        $this->preview_type = $preview_type;
        $this->order_id = $order_id;
        $this->cache_key = $this->generate_cache_key();
    }

    /**
     * Génère l'aperçu PDF
     */
    public function generate_preview()
    {
        // Pour l'instant, retournons une URL temporaire
        // TODO: Implémenter la vraie génération d'aperçu
        $upload_dir = \wp_upload_dir();
        $preview_dir = $upload_dir['basedir'] . '/pdf-builder-previews';

        // Créer le dossier s'il n'existe pas
        if (!file_exists($preview_dir)) {
            \wp_mkdir_p($preview_dir);
        }

        $filename = 'preview-' . $this->cache_key . '.png';
        $filepath = $preview_dir . '/' . $filename;

        // Générer une image temporaire basique pour l'instant
        $this->generate_placeholder_image($filepath);

        return $upload_dir['baseurl'] . '/pdf-builder-previews/' . $filename;
    }

    /**
     * Génère une image placeholder pour l'aperçu
     */
    private function generate_placeholder_image($filepath)
    {
        // Créer une image simple avec GD
        $width = 794; // A4 width at 96 DPI
        $height = 1123; // A4 height at 96 DPI

        $image = imagecreatetruecolor($width, $height);

        // Couleurs
        $white = imagecolorallocate($image, 255, 255, 255);
        $gray = imagecolorallocate($image, 200, 200, 200);
        $black = imagecolorallocate($image, 0, 0, 0);

        // Fond blanc
        imagefill($image, 0, 0, $white);

        // Rectangle gris pour simuler le contenu
        imagefilledrectangle($image, 50, 50, $width - 50, $height - 50, $gray);

        // Texte
        $font_size = 5; // GD font size
        imagestring($image, $font_size, 60, 70, 'APERÇU PDF BUILDER PRO', $black);
        imagestring($image, $font_size, 60, 90, 'Template: ' . ($this->template_data['name'] ?? 'Unknown'), $black);
        imagestring($image, $font_size, 60, 110, 'Type: ' . $this->preview_type, $black);
        imagestring($image, $font_size, 60, 130, 'Generated: ' . date('Y-m-d H:i:s'), $black);

        // Sauvegarder l'image
        imagepng($image, $filepath);
        imagedestroy($image);
    }

    /**
     * Génère une clé de cache unique
     */
    private function generate_cache_key()
    {
        $data = $this->template_data;
        $data['preview_type'] = $this->preview_type;
        $data['order_id'] = $this->order_id;
        $data['timestamp'] = time();

        return md5(serialize($data));
    }

    /**
     * Retourne la clé de cache
     */
    public function get_cache_key()
    {
        return $this->cache_key;
    }
}
