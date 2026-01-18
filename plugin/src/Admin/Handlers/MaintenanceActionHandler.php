<?php

/**
 * MaintenanceActionHandler - Gestion des actions de maintenance
 * Responsabilités : nettoyage, réparation, réinitialisation
 */

namespace PDF_Builder\Admin\Handlers;

class MaintenanceActionHandler
{
    /**
     * Obtient la taille d'un répertoire
     */
    public function getDirectorySize($directory)
    {
        $size = 0;
        if (!is_dir($directory)) {
            return $size;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Nettoie les fichiers temporaires
     */
    public function performClearTempFiles()
    {
        $temp_dir = sys_get_temp_dir() . '/pdf-builder/';
        $cleared_files = 0;
        $total_size = 0;
        
        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < time() - 86400) {
                    // Fichiers de plus de 24h
                    $size = filesize($file);
                    if (unlink($file)) {
                        $cleared_files++;
                        $total_size += $size;
                    }
                }
            }
        }

        return array(
            'success' => true,
            'message' => sprintf(
                __('Fichiers temporaires nettoyés. %d fichiers supprimés, %s libérés.', 'pdf-builder-pro'),
                $cleared_files,
                size_format($total_size)
            )
        );
    }

    /**
     * Répare les templates corrompus
     */
    public function performRepairTemplates()
    {
        $templates = pdf_builder_get_option('pdf_builder_templates', []);
        $repaired_count = 0;
        
        if (is_array($templates)) {
            foreach ($templates as $key => $template) {
                // Vérifier et réparer la structure des templates
                if (!isset($template['name']) || !isset($template['data'])) {
                    unset($templates[$key]);
                    $repaired_count++;
                }
            }
        }

        pdf_builder_update_option('pdf_builder_templates', $templates);
        
        return array(
            'success' => true,
            'message' => sprintf(
                __('Templates réparés. %d templates corrompus supprimés.', 'pdf-builder-pro'),
                $repaired_count
            )
        );
    }

    /**
     * Réinitialise tous les paramètres aux valeurs par défaut
     */
    public function performResetSettings()
    {
        // Liste des options à réinitialiser
        $options_to_reset = [
            'pdf_builder_settings',
            'pdf_builder_allowed_roles',
            'pdf_builder_templates',
            'pdf_builder_admin_email',
            'pdf_builder_default_canvas_width',
            'pdf_builder_default_canvas_height',
            'pdf_builder_show_grid',
            'pdf_builder_snap_to_grid',
            'pdf_builder_snap_to_elements'
        ];
        
        $reset_count = 0;
        foreach ($options_to_reset as $option) {
            if (delete_option($option)) {
                $reset_count++;
            }
        }

        // Vider le cache
        wp_cache_flush();
        
        return array(
            'success' => true,
            'message' => sprintf(
                __('Paramètres réinitialisés avec succès. %d options supprimées.', 'pdf-builder-pro'),
                $reset_count
            )
        );
    }
}


