<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange

/**
 * DashboardDataProvider - Fournit les données du tableau de bord
 * Responsabilités : statistiques, comptage, informations version
 */

namespace PDF_Builder\Admin\Providers;

class DashboardDataProvider
{
    /**
     * Obtient les statistiques du tableau de bord
     */
    public function getDashboardStats()
    {
        global $wpdb;

        // Nombre de templates
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $templates_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery

        // Nombre total de documents générés (logs)
        $table_logs = $wpdb->prefix . 'pdf_builder_logs';
        $documents_count = 0;
        $today_count = 0;

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_logs'") == $table_logs) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            // Vérifier si la colonne log_message existe
            $columns = $wpdb->get_results("DESCRIBE $table_logs"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            $has_log_message = false;
            foreach ($columns as $column) {
                if ($column->Field === 'log_message') {
                    $has_log_message = true;
                    break;
                }
            }

            if ($has_log_message) {
                $documents_count = $wpdb->get_var( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
                    "SELECT COUNT(*) FROM $table_logs WHERE log_message LIKE '%PDF généré%' OR log_message LIKE '%Document créé%'"
                );

                // Documents générés aujourd'hui
                $today = gmdate('Y-m-d');
                $today_count = $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
                    "SELECT COUNT(*) FROM $table_logs WHERE DATE(created_at) = %s AND (log_message LIKE '%PDF généré%' OR log_message LIKE '%Document créé%')",
                    $today
                ));
            } else {
                // Si la colonne n'existe pas, compter tous les logs
                $documents_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_logs"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery

                // Documents d'aujourd'hui
                $today = gmdate('Y-m-d');
                $today_count = $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
                    "SELECT COUNT(*) FROM $table_logs WHERE DATE(created_at) = %s",
                    $today
                ));
            }
        }

        return [
            'templates' => (int) $templates_count,
            'documents' => (int) $documents_count,
            'today' => (int) $today_count
        ];
    }

    /**
     * Obtient le nombre de templates
     */
    public function getTemplateCount()
    {
        $templates = pdf_builder_get_option('pdf_builder_templates', []);
        return is_array($templates) ? count($templates) : 0;
    }

    /**
     * Récupère la version du plugin
     */
    public function getPluginVersion()
    {
        static $version = null;

        if ($version === null) {
            if (defined('PDF_BUILDER_PLUGIN_FILE') && file_exists(PDF_BUILDER_PLUGIN_FILE)) {
                $plugin_data = get_file_data(PDF_BUILDER_PLUGIN_FILE, array('Version' => 'Version'));
                $version = $plugin_data['Version'] ?: '1.1.0';
            } else {
                $version = '1.1.0';
            }
        }

        return $version;
    }

    /**
     * Crée un template par défaut
     */
    public function createDefaultTemplate($template_id)
    {
        // Template par défaut avec quelques éléments de base
        $default_template = [
            'id' => $template_id,
            'name' => 'Template par défaut',
            'description' => 'Template créé automatiquement',
            'elements' => [
                [
                    'id' => 'title',
                    'type' => 'text',
                    'content' => 'PDF Builder Pro',
                    'position' => ['x' => 50, 'y' => 50],
                    'size' => ['width' => 200, 'height' => 40],
                    'style' => [
                        'fontSize' => 24,
                        'fontWeight' => 'bold',
                        'color' => '#000000',
                        'textAlign' => 'center'
                    ]
                ],
                [
                    'id' => 'subtitle',
                    'type' => 'text',
                    'content' => 'Éditeur de PDF professionnel',
                    'position' => ['x' => 50, 'y' => 100],
                    'size' => ['width' => 200, 'height' => 30],
                    'style' => [
                        'fontSize' => 16,
                        'color' => '#666666',
                        'textAlign' => 'center'
                    ]
                ]
            ],
            'pages' => [
                [
                    'id' => 1,
                    'name' => 'Page 1',
                    'width' => 595, // A4 width in points
                    'height' => 842, // A4 height in points
                    'orientation' => 'portrait',
                    'margins' => [
                        'top' => 28,
                        'right' => 28,
                        'bottom' => 28,
                        'left' => 28
                    ],
                    'backgroundColor' => '#ffffff'
                ]
            ],
            'created_at' => \current_time('mysql'),
        ];

        return $default_template;
    }
}




