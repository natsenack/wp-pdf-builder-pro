<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * PDF Builder Pro - Gestionnaire d'erreurs avancé
 * Centralise la gestion des erreurs et exceptions avec logging structuré
 */

class PDF_Builder_Error_Handler {
    private static $instance = null;
    private $error_levels = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED'
    ];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_error_handling();
        $this->init_hooks();
    }

    private function init_error_handling() {
        // Définir le gestionnaire d'erreurs personnalisé
        set_error_handler([$this, 'handle_error']);

        // Définir le gestionnaire d'exceptions non capturées
        set_exception_handler([$this, 'handle_exception']);

        // Définir le gestionnaire d'arrêt fatal
        register_shutdown_function([$this, 'handle_shutdown']);
    }

    private function init_hooks() {
        // Hook pour les erreurs AJAX
        add_action('wp_ajax_pdf_builder_error_report', [$this, 'handle_ajax_error']);

        // Hook pour les erreurs de base de données
        add_action('db_errors', [$this, 'handle_database_error']);

        // Nettoyage périodique des logs d'erreurs
        add_action('pdf_builder_daily_cleanup', [$this, 'cleanup_error_logs']);
    }

    /**
     * Gestionnaire d'erreurs PHP personnalisé
     */
    public function handle_error($errno, $errstr, $errfile, $errline) {
        // Ignorer les erreurs supprimées avec @
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $error_data = [
            'level' => $this->error_levels[$errno] ?? 'UNKNOWN',
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'context' => $this->get_error_context($errfile, $errline),
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            'timestamp' => current_time('timestamp')
        ];

        // Logger l'erreur
        $this->log_error($error_data);

        // Pour les erreurs fatales, arrêter l'exécution
        if ($errno === E_ERROR || $errno === E_PARSE || $errno === E_CORE_ERROR || $errno === E_COMPILE_ERROR) {
            $this->handle_fatal_error($error_data);
        }

        // Ne pas exécuter le gestionnaire d'erreurs interne de PHP
        return true;
    }

    /**
     * Gestionnaire d'exceptions non capturées
     */
    public function handle_exception($exception) {
        $error_data = [
            'level' => 'EXCEPTION',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString(),
            'context' => $this->get_error_context($exception->getFile(), $exception->getLine()),
            'timestamp' => current_time('timestamp')
        ];

        $this->log_error($error_data);
        $this->handle_fatal_error($error_data);
    }

    /**
     * Gestionnaire d'arrêt pour les erreurs fatales
     */
    public function handle_shutdown() {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
            $error_data = [
                'level' => $this->error_levels[$error['type']] ?? 'FATAL',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'context' => $this->get_error_context($error['file'], $error['line']),
                'timestamp' => current_time('timestamp')
            ];

            $this->log_error($error_data);
            $this->handle_fatal_error($error_data);
        }
    }

    /**
     * Gestion des erreurs AJAX
     */
    public function handle_ajax_error() {
        try {
            // Valider la requête
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_ajax')) {
                wp_send_json_error(['message' => 'Nonce invalide']);
                return;
            }

            $error_data = json_decode(stripslashes($_POST['error_data'] ?? '{}'), true);

            if (!$error_data) {
                wp_send_json_error(['message' => 'Données d\'erreur invalides']);
                return;
            }

            $error_data['source'] = 'ajax';
            $error_data['user_id'] = get_current_user_id();
            $error_data['timestamp'] = current_time('timestamp');

            $this->log_error($error_data);

            wp_send_json_success(['message' => 'Erreur enregistrée']);

        } catch (Exception $e) {
            wp_send_json_error(['message' => 'Erreur lors du traitement: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestion des erreurs de base de données
     */
    public function handle_database_error($error) {
        $error_data = [
            'level' => 'DATABASE_ERROR',
            'message' => $error,
            'source' => 'database',
            'timestamp' => current_time('timestamp')
        ];

        $this->log_error($error_data);
    }

    /**
     * Obtient le contexte autour d'une erreur
     */
    private function get_error_context($file, $line, $context_lines = 5) {
        if (!file_exists($file)) {
            return [];
        }

        $lines = file($file);
        $start = max(0, $line - $context_lines - 1);
        $end = min(count($lines), $line + $context_lines);

        $context = [];
        for ($i = $start; $i < $end; $i++) {
            $context[] = [
                'line' => $i + 1,
                'code' => rtrim($lines[$i]),
                'is_error_line' => ($i + 1) === $line
            ];
        }

        return $context;
    }

    /**
     * Log une erreur dans le système de logging
     */
    private function log_error($error_data) {
        if (class_exists('PDF_Builder_Logger')) {
            $logger = PDF_Builder_Logger::get_instance();

            // Mapper les niveaux d'erreur aux méthodes du logger
            $level_map = [
                'ERROR' => 'error',
                'WARNING' => 'warning',
                'NOTICE' => 'info',
                'EXCEPTION' => 'error',
                'FATAL' => 'critical',
                'DATABASE_ERROR' => 'error'
            ];

            $method = $level_map[$error_data['level']] ?? 'error';

            $logger->$method('Error: ' . $error_data['message'], [
                'file' => $error_data['file'] ?? 'unknown',
                'line' => $error_data['line'] ?? 'unknown',
                'context' => $error_data['context'] ?? [],
                'trace' => $error_data['trace'] ?? '',
                'source' => $error_data['source'] ?? 'php'
            ]);
        } else {
            // Fallback vers error_log
            error_log('Error: ' . $error_data['message']);
        }

        // Stocker en base pour les erreurs critiques
        if (in_array($error_data['level'], ['ERROR', 'EXCEPTION', 'FATAL', 'DATABASE_ERROR'])) {
            $this->store_error_in_db($error_data);
        }
    }

    /**
     * Stocke une erreur critique en base de données
     */
    private function store_error_in_db($error_data) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        // Créer la table si elle n'existe pas
        $this->create_errors_table();

        $wpdb->insert(
            $table,
            [
                'level' => $error_data['level'],
                'message' => substr($error_data['message'], 0, 1000),
                'file' => $error_data['file'] ?? '',
                'line' => $error_data['line'] ?? 0,
                'context' => json_encode($error_data['context'] ?? []),
                'trace' => json_encode($error_data['trace'] ?? ''),
                'source' => $error_data['source'] ?? 'php',
                'user_id' => $error_data['user_id'] ?? get_current_user_id(),
                'ip' => $this->get_client_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );
    }

    /**
     * Crée la table des erreurs si elle n'existe pas
     */
    private function create_errors_table() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) { // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                level varchar(20) NOT NULL,
                message text NOT NULL,
                file varchar(500),
                line int(11),
                context longtext,
                trace longtext,
                source varchar(50) DEFAULT 'php',
                user_id bigint(20) unsigned,
                ip varchar(45),
                user_agent text,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY level (level),
                KEY source (source),
                KEY created_at (created_at)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Gestion des erreurs fatales - affichage d'une page d'erreur
     */
    private function handle_fatal_error($error_data) {
        // En mode debug, afficher les détails
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->display_error_page($error_data);
        } else {
            // En production, afficher une page d'erreur générique
            $this->display_generic_error_page();
        }

        exit;
    }

    /**
     * Affiche une page d'erreur détaillée (debug)
     */
    private function display_error_page($error_data) {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/html; charset=UTF-8');
        }

        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Erreur PDF Builder Pro</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
                .error-container { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                .error-title { color: #d9534f; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
                .error-details { margin-top: 20px; }
                .error-section { margin-bottom: 15px; }
                .error-label { font-weight: bold; color: #333; }
                .code-block { background: #f8f8f8; padding: 10px; border-left: 3px solid #d9534f; margin: 10px 0; font-family: monospace; }
                .context-line { display: block; }
                .error-line { background: #ffebee; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1 class="error-title">Erreur dans PDF Builder Pro</h1>
                <div class="error-details">
                    <div class="error-section">
                        <span class="error-label">Niveau:</span> ' . esc_html($error_data['level']) . '
                    </div>
                    <div class="error-section">
                        <span class="error-label">Message:</span> ' . esc_html($error_data['message']) . '
                    </div>
                    <div class="error-section">
                        <span class="error-label">Fichier:</span> ' . esc_html($error_data['file']) . ' (ligne ' . intval($error_data['line']) . ')
                    </div>';

        if (!empty($error_data['context'])) {
            echo '<div class="error-section">
                        <span class="error-label">Contexte:</span>
                        <div class="code-block">';
            foreach ($error_data['context'] as $line) {
                $class = $line['is_error_line'] ? 'error-line' : '';
                echo '<span class="context-line ' . esc_attr($class) . '">' .
                     esc_html(str_pad($line['line'], 4, ' ', STR_PAD_LEFT)) . ': ' .
                     esc_html($line['code']) . '</span>';
            }
            echo '</div></div>';
        }

        if (!empty($error_data['trace'])) {
            echo '<div class="error-section">
                        <span class="error-label">Trace:</span>
                        <pre class="code-block">' . esc_html($error_data['trace']) . '</pre>
                    </div>';
        }

        echo '</div></div></body></html>';
    }

    /**
     * Affiche une page d'erreur générique (production)
     */
    private function display_generic_error_page() {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/html; charset=UTF-8');
        }

        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Erreur</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; margin: 50px; }
                .error-message { color: #d9534f; font-size: 18px; }
            </style>
        </head>
        <body>
            <h1>Une erreur s\'est produite</h1>
            <p class="error-message">Le système PDF Builder Pro a rencontré une erreur. Veuillez réessayer plus tard.</p>
            <p><a href="' . esc_url(home_url()) . '">Retour à l\'accueil</a></p>
        </body>
        </html>';
    }

    /**
     * Obtient l'IP du client
     */
    private function get_client_ip() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Nettoie les anciens logs d'erreurs
     */
    public function cleanup_error_logs() {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        // Supprimer les erreurs de plus de 30 jours
        $wpdb->query($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            "DELETE FROM $table WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        ));

        // Supprimer les erreurs de niveau NOTICE de plus de 7 jours
        $wpdb->query($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            "DELETE FROM $table WHERE level = 'NOTICE' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        ));
    }

    /**
     * Récupère les statistiques d'erreurs
     */
    public function get_error_stats($days = 7) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        $stats = $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT
                level,
                COUNT(*) as count,
                DATE(created_at) as date
            FROM $table
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY level, DATE(created_at)
            ORDER BY date DESC, count DESC
        ", $days), ARRAY_A);

        return $stats;
    }

    /**
     * Récupère les erreurs récentes
     */
    public function get_recent_errors($limit = 50) {
        global $wpdb;

        $table = $wpdb->prefix . 'pdf_builder_errors';

        return $wpdb->get_results($wpdb->prepare(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            SELECT * FROM $table
            ORDER BY created_at DESC
            LIMIT %d
        ", $limit), ARRAY_A);
    }
}

// Fonctions globales pour faciliter l'utilisation
function pdf_builder_handle_error($message, $context = []) {
    $error_data = array_merge([
        'level' => 'ERROR',
        'message' => $message,
        'timestamp' => current_time('timestamp')
    ], $context);

    PDF_Builder_Error_Handler::get_instance()->log_error($error_data);
}

function pdf_builder_handle_exception($exception) {
    PDF_Builder_Error_Handler::get_instance()->handle_exception($exception);
}

// Initialiser le gestionnaire d'erreurs
add_action('plugins_loaded', function() {
    PDF_Builder_Error_Handler::get_instance();
});



