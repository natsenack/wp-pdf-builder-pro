<?php

/**
 * PDF Builder Pro - Utils
 * Méthodes utilitaires système
 */

namespace PDF_Builder\Admin\Utils;

/**
 * Classe responsable des utilitaires système
 */
class Utils
{
    /**
     * Instance de la classe principale
     */
    private $admin;

    /**
     * Constructeur
     */
    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    /**
     * Vide le cache des permissions
     */
    public function clearPermissionsCache()
    {
        global $wpdb;
        // Supprimer tous les transients liés aux permissions PDF Builder
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like('_transient_pdf_builder_user_access_') . '%'));
        // Supprimer aussi les timeouts
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like('_transient_timeout_pdf_builder_user_access_') . '%'));
        return array(
            'success' => true,
            'message' => __('Cache des permissions vidé avec succès.', 'pdf-builder-pro')
        );
    }

    /**
     * Effectue le nettoyage du cache
     */
    public function performClearCache()
    {
        global $wpdb;

        // Supprimer tous les transients liés au PDF Builder
        $result1 = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like('_transient_pdf_builder_') . '%'));
        $result2 = $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like('_transient_timeout_pdf_builder_') . '%'));

        // Nettoyer le cache des objets WordPress
        wp_cache_flush();

        // Supprimer les fichiers temporaires si ils existent
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';
        if (is_dir($temp_dir)) {
            $this->removeDirectory($temp_dir);
        }

        return array(
            'success' => true,
            'message' => sprintf(__('Cache nettoyé. %d transients supprimés.', 'pdf-builder-pro'), ($result1 + $result2))
        );
    }

    /**
     * Supprime récursivement un répertoire
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }

    /**
     * Calcule la taille d'un répertoire
     */
    public function getDirectorySize($directory)
    {
        $size = 0;
        if (is_dir($directory)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
                $size += $file->getSize();
            }
        }
        return $size;
    }
}

