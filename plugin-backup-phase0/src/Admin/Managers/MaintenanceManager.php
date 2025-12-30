<?php

/**
 * PDF Builder Pro - Maintenance Manager
 * Responsable des tâches de maintenance du plugin
 */

namespace PDF_Builder\Admin\Managers;

/**
 * Classe responsable des opérations de maintenance du plugin
 */
class MaintenanceManager
{
    /**
     * Supprime les fichiers temporaires
     *
     * @return array Résultat de l'opération
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
                    $file_size = filesize($file);
                    if (unlink($file)) {
                        $cleared_files++;
                        $total_size += $file_size;
                    }
                }
            }
        }

        return [
            'success' => true,
            'message' => sprintf(
                __('Fichiers temporaires nettoyés. %d fichiers supprimés, %s libérés.', 'pdf-builder-pro'),
                $cleared_files,
                size_format($total_size)
            )
        ];
    }

    /**
     * Répare les templates corrompus
     *
     * @return array Résultat de l'opération
     */
    public function performRepairTemplates()
    {
        $templates = get_option('pdf_builder_templates', []);
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

        update_option('pdf_builder_templates', $templates);

        return [
            'success' => true,
            'message' => sprintf(
                __('Templates réparés. %d templates corrompus supprimés.', 'pdf-builder-pro'),
                $repaired_count
            )
        ];
    }

    /**
     * Réinitialise tous les paramètres aux valeurs par défaut
     *
     * @return array Résultat de l'opération
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

        return [
            'success' => true,
            'message' => sprintf(
                __('Paramètres réinitialisés avec succès. %d options supprimées.', 'pdf-builder-pro'),
                $reset_count
            )
        ];
    }
}
