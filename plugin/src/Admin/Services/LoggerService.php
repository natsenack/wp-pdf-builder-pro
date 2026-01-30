<?php

/**
 * PDF Builder Pro - Logger Service
 * Responsable de la journalisation des événements
 */

namespace PDF_Builder\Admin\Services;

/**
 * Classe responsable du logging des événements du plugin
 */
class LoggerService
{
    /**
     * Log un changement de rôles/permissions
     *
     * @param array $old_roles Anciens rôles
     * @param array $new_roles Nouveaux rôles
     * @return void
     */
    public function logRolePermissionsChange($old_roles, $new_roles)
    {
        $log_entry = [
            'timestamp' => \current_time('mysql'),
            'event' => 'role_permissions_changed',
            'old_roles' => $old_roles,
            'new_roles' => $new_roles,
            'changed_by' => \get_current_user_id(),
            'user_login' => \wp_get_current_user()->user_login ?? 'system'
        ];

        // Ajouter au log
        $logs = pdf_builder_get_option('pdf_builder_role_change_logs', array());

        // Limiter à 100 entrées maximum
        if (count($logs) >= 100) {
            array_shift($logs);
        }

        $logs[] = $log_entry;
        pdf_builder_update_option('pdf_builder_role_change_logs', $logs);

        // Log WordPress si debug activé
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // error_log(
            //     '[PDF Builder] Role permissions changed: ' .
            //     'Old: ' . wp_json_encode($old_roles) . ', ' .
            //     'New: ' . wp_json_encode($new_roles) . ', ' .
            //     'By: ' . $log_entry['user_login']
            // );
        }

        /**
         * Hook pour permettre aux extensions de réagir au changement de rôles
         *
         * @param array $old_roles Anciens rôles
         * @param array $new_roles Nouveaux rôles
         */
        \do_action('pdf_builder_roles_changed', $old_roles, $new_roles);
    }

    /**
     * Récupère l'historique des changements de rôles
     *
     * @param int $limit Nombre d'entrées à retourner
     * @return array Historique des changements
     */
    public function getRoleChangeHistory($limit = 50)
    {
        $logs = pdf_builder_get_option('pdf_builder_role_change_logs', array());

        if ($limit > 0) {
            $logs = array_slice($logs, -$limit);
        }

        return array_reverse($logs);
    }

    /**
     * Efface l'historique des changements de rôles
     *
     * @return bool Succès de l'opération
     */
    public function clearRoleChangeHistory()
    {
        return \delete_option('pdf_builder_role_change_logs');
    }
}



