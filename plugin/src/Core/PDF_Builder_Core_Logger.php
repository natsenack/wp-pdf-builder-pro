<?php
/**
 * Système de Logging Avancé pour PDF Builder Pro
 *
 * Logger intelligent avec rotation, niveaux de log, et archivage automatique.
 *
 * @package PDF_Builder
 * @subpackage Core
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale du système de logging avancé
 */
class PDF_Builder_Core_Logger {

    /**
     * Instance unique
     */
    private static $instance = null;

    /**
     * Configuration du logger
     */
    private $config = array();

    /**
     * Niveaux de log
     */
    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;

    /**
     * Noms des niveaux
     */
    private $level_names = array(
        self::LEVEL_DEBUG => 'DEBUG',
        self::LEVEL_INFO => 'INFO',
        self::LEVEL_WARNING => 'WARNING',
        self::LEVEL_ERROR => 'ERROR',
        self::LEVEL_CRITICAL => 'CRITICAL',
    );

    /**
     * Fichier de log actuel
     */
    private $current_log_file = '';

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_config();
        $this->init_log_file();
        $this->register_hooks();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser la configuration
     */
    private function init_config() {
        $this->config = array(
            'enabled' => pdf_builder_get_option('pdf_builder_enable_logging', '1') === '1',
            'level' => self::LEVEL_INFO,
            'max_file_size' => 10 * 1024 * 1024, // 10 MB en bytes
            'retention_days' => 30,
            'auto_rotate' => true,
            'include_backtrace' => pdf_builder_get_option('pdf_builder_log_backtrace', '0') === '1',
            'remote_logging' => pdf_builder_get_option('pdf_builder_remote_logging', '0') === '1',
        );
    }

    /**
     * Initialiser le fichier de log
     */
    private function init_log_file() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/pdf-builder-logs/';

        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        $this->current_log_file = $log_dir . 'pdf-builder-' . gmdate('Y-m-d') . '.log';

        // Rotation automatique si nécessaire
        if ($this->config['auto_rotate']) {
            $this->rotate_if_needed();
        }
    }

    /**
     * Enregistrer les hooks
     */
    private function register_hooks() {
        add_action('pdf_builder_daily_maintenance', array($this, 'cleanup_old_logs'));
        add_action('wp_ajax_pdf_builder_view_logs', array($this, 'ajax_view_logs'));

        // Hook pour les erreurs PHP
        if ($this->config['enabled']) {
            set_error_handler(array($this, 'handle_php_error'));
            set_exception_handler(array($this, 'handle_php_exception'));
        }
    }

    /**
     * Logger un message
     */
    public function log($message, $level = self::LEVEL_INFO, $context = array()) {
        if (!$this->config['enabled'] || $level < $this->config['level']) {
            return;
        }

        $entry = $this->format_log_entry($message, $level, $context);
        $this->write_to_file($entry);

        // Logging distant si activé
        if ($this->config['remote_logging']) {
            $this->send_to_remote($entry);
        }
    }

    /**
     * Méthodes de logging par niveau
     */
    public function debug($message, $context = array()) {
        // $this->log($message, self::LEVEL_DEBUG, $context);
    }

    public function info($message, $context = array()) {
        // $this->log($message, self::LEVEL_INFO, $context);
    }

    public function warning($message, $context = array()) {
        // $this->log($message, self::LEVEL_WARNING, $context);
    }

    public function error($message, $context = array()) {
        // $this->log($message, self::LEVEL_ERROR, $context);
    }

    public function critical($message, $context = array()) {
        // $this->log($message, self::LEVEL_CRITICAL, $context);
    }

    /**
     * Logger une erreur
     */
    public function log_error($error, $context = array()) {
        $context['error_type'] = 'error';
        $this->error($error, $context);
    }

    /**
     * Logger un avertissement
     */
    public function log_warning($warning, $context = array()) {
        $context['warning_type'] = 'warning';
        $this->warning($warning, $context);
    }

    /**
     * Logger une activité utilisateur
     */
    public function log_user_activity($action, $user_id = null, $context = array()) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        $context['user_id'] = $user_id;
        $context['user_ip'] = $this->get_user_ip();
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $this->info("User activity: {$action}", $context);
    }

    /**
     * Logger une activité système
     */
    public function log_system_activity($component, $action, $context = array()) {
        $context['component'] = $component;
        $context['system_info'] = $this->get_system_info();

        $this->info("System activity: {$component} - {$action}", $context);
    }

    /**
     * Obtenir les logs récents
     */
    public function get_recent_logs($limit = 100, $level = null) {
        if (!file_exists($this->current_log_file)) {
            return array();
        }

        $logs = array();
        $handle = fopen($this->current_log_file, 'r'); // phpcs:ignore WordPress.WP.AlternativeFunctions

        if ($handle) {
            $lines = array();
            while (($line = fgets($handle)) !== false) {
                $lines[] = $line;
            }
            fclose($handle); // phpcs:ignore WordPress.WP.AlternativeFunctions

            // Inverser pour avoir les plus récents en premier
            $lines = array_reverse($lines);

            $count = 0;
            foreach ($lines as $line) {
                if ($count >= $limit) {
                    break;
                }

                $parsed = $this->parse_log_line($line);
                if ($parsed) {
                    if ($level === null || $parsed['level'] >= $level) {
                        $logs[] = $parsed;
                        $count++;
                    }
                }
            }
        }

        return $logs;
    }

    /**
     * Obtenir tous les fichiers de log
     */
    public function get_log_files() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/pdf-builder-logs/';

        if (!file_exists($log_dir)) {
            return array();
        }

        $files = glob($log_dir . '*.log');
        $log_files = array();

        foreach ($files as $file) {
            $log_files[] = array(
                'filename' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'modified' => filemtime($file),
                'readable' => is_readable($file),
            );
        }

        // Trier par date de modification (plus récent en premier)
        usort($log_files, function($a, $b) {
            return $b['modified'] - $a['modified'];
        });

        return $log_files;
    }

    /**
     * Nettoyer les anciens logs
     */
    public function cleanup_old_logs() {
        $files = $this->get_log_files();
        $now = time();
        $retention_seconds = $this->config['retention_days'] * 24 * 60 * 60;
        $deleted = 0;

        foreach ($files as $file) {
            if (($now - $file['modified']) > $retention_seconds) {
                if (wp_delete_file($file['path'])) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old log files");
        }

        return $deleted;
    }

    /**
     * Handler pour les erreurs PHP
     */
    public function handle_php_error($errno, $errstr, $errfile, $errline) {
        $levels = array(
            E_ERROR => self::LEVEL_CRITICAL,
            E_WARNING => self::LEVEL_WARNING,
            E_NOTICE => self::LEVEL_INFO,
            E_USER_ERROR => self::LEVEL_ERROR,
            E_USER_WARNING => self::LEVEL_WARNING,
            E_USER_NOTICE => self::LEVEL_INFO,
        );

        $level = isset($levels[$errno]) ? $levels[$errno] : self::LEVEL_ERROR;

        $context = array(
            'error_number' => $errno,
            'file' => $errfile,
            'line' => $errline,
            'type' => 'php_error',
        );

        // $this->log($errstr, $level, $context);
    }

    /**
     * Handler pour les exceptions PHP
     */
    public function handle_php_exception($exception) {
        $context = array(
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'type' => 'php_exception',
        );

        $this->critical($exception->getMessage(), $context);
    }

    /**
     * Formatter une entrée de log
     */
    private function format_log_entry($message, $level, $context) {
        $timestamp = current_time('Y-m-d H:i:s');
        $level_name = $this->level_names[$level] ?? 'UNKNOWN';

        $entry = "[{$timestamp}] [{$level_name}] {$message}";

        if (!empty($context)) {
            $entry .= " | " . json_encode($context);
        }

        if ($this->config['include_backtrace'] && $level >= self::LEVEL_WARNING) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
            $entry .= " | Backtrace: " . json_encode($backtrace);
        }

        return $entry . "\n";
    }

    /**
     * Écrire dans le fichier de log
     */
    private function write_to_file($entry) {
        // Rotation si nécessaire avant d'écrire
        $this->rotate_if_needed();

        // $result = file_put_contents($this->current_log_file, $entry, FILE_APPEND | LOCK_EX);

        // if ($result === false) {

        // }
    }

    /**
     * Rotation du fichier de log si nécessaire
     */
    private function rotate_if_needed() {
        if (!file_exists($this->current_log_file)) {
            return;
        }

        if (filesize($this->current_log_file) >= $this->config['max_file_size']) {
            $this->rotate_log_file();
        }
    }

    /**
     * Effectuer la rotation du fichier de log
     */
    private function rotate_log_file() {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/pdf-builder-logs/';

        $timestamp = gmdate('Y-m-d_H-i-s');
        $archive_file = $log_dir . 'pdf-builder-' . $timestamp . '.log';

        if (rename($this->current_log_file, $archive_file)) { // phpcs:ignore WordPress.WP.AlternativeFunctions
            $this->info("Log file rotated to: {$archive_file}");
        }
    }

    /**
     * Parser une ligne de log
     */
    private function parse_log_line($line) {
        $pattern = '/^\[([^\]]+)\]\s*\[([^\]]+)\]\s*(.+?)(?:\s*\|\s*(.+))?$/';
        if (preg_match($pattern, trim($line), $matches)) {
            $context = array();
            if (isset($matches[4])) {
                $context = json_decode($matches[4], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $context = array('raw_context' => $matches[4]);
                }
            }

            return array(
                'timestamp' => $matches[1],
                'level' => array_search($matches[2], $this->level_names),
                'level_name' => $matches[2],
                'message' => $matches[3],
                'context' => $context,
            );
        }

        return false;
    }

    /**
     * Envoyer vers un système de logging distant
     */
    private function send_to_remote($entry) {
        // Implémentation pour logging distant (par exemple vers un service externe)
        // Pour l'instant, juste logger localement
        $this->debug('Remote logging not implemented yet', array('entry' => $entry));
    }

    /**
     * Obtenir l'IP de l'utilisateur
     */
    private function get_user_ip() {
        $ip_headers = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

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

        return 'unknown';
    }

    /**
     * Obtenir les informations système
     */
    private function get_system_info() {
        return array(
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'memory_usage' => memory_get_peak_usage(true),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        );
    }

    /**
     * Handler AJAX pour voir les logs
     */
    public function ajax_view_logs() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $limit = intval($_POST['limit'] ?? 50);
        $level = isset($_POST['level']) ? intval($_POST['level']) : null;

        $logs = $this->get_recent_logs($limit, $level);
        $files = $this->get_log_files();

        wp_send_json_success(array(
            'logs' => $logs,
            'files' => $files,
            'current_file' => basename($this->current_log_file),
        ));
    }
}




