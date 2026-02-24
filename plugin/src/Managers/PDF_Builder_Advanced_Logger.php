<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * PDF Builder Pro - Système de logging avancé
 * Système structuré et configurable pour le logging
 */

class PDF_Builder_Advanced_Logger {
    private static $instance = null;
    private $log_level;
    private $log_file;
    private $max_file_size;
    private $retention_days;

    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;

    private $level_names = [
        self::LEVEL_DEBUG => 'DEBUG',
        self::LEVEL_INFO => 'INFO',
        self::LEVEL_WARNING => 'WARNING',
        self::LEVEL_ERROR => 'ERROR',
        self::LEVEL_CRITICAL => 'CRITICAL'
    ];

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_settings();
        $this->init_hooks();
    }

    private function init_settings() {
        // Récupérer les paramètres de debug depuis les options WordPress
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Niveau de log basé sur pdf_builder_log_level (0-4)
        $log_level_setting = isset($settings['pdf_builder_log_level']) ? intval($settings['pdf_builder_log_level']) : 3; // 3 = Info par défaut
        $this->log_level = $this->map_log_level($log_level_setting);

        // Taille max du fichier de log
        $this->max_file_size = isset($settings['pdf_builder_log_file_size']) ? intval($settings['pdf_builder_log_file_size']) * 1024 * 1024 : 10 * 1024 * 1024; // 10 MB par défaut

        // Rétention des logs en jours
        $this->retention_days = isset($settings['pdf_builder_log_retention']) ? intval($settings['pdf_builder_log_retention']) : 30; // 30 jours par défaut

        // Définir le chemin du fichier de log
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/pdf-builder-logs/pdf-builder.log';
    }

    /**
     * Map les niveaux de log du plugin vers les constantes internes
     */
    private function map_log_level($plugin_level) {
        switch ($plugin_level) {
            case 0: return self::LEVEL_CRITICAL; // Aucun log = seulement erreurs critiques
            case 1: return self::LEVEL_ERROR;     // Erreurs uniquement
            case 2: return self::LEVEL_WARNING;   // Erreurs + Avertissements
            case 3: return self::LEVEL_INFO;      // Info complète
            case 4: return self::LEVEL_DEBUG;     // Détails (Développement)
            default: return self::LEVEL_INFO;     // Par défaut
        }
    }

    private function init_hooks() {
        // Nettoyer les anciens logs régulièrement
        add_action('pdf_builder_daily_maintenance', [$this, 'cleanup_old_logs']);

        // Log les erreurs PHP critiques
        add_action('shutdown', [$this, 'log_php_errors']);
    }

    /**
     * Log un message avec un niveau spécifique
     */
    public function log($level, $message, $context = []) {
        if ($level < $this->log_level) {
            return; // Niveau de log trop bas
        }

        $entry = $this->format_log_entry($level, $message, $context);
        $this->write_to_file($entry);

        // Log aussi les erreurs critiques dans error_log de PHP
        if ($level >= self::LEVEL_ERROR) {

        }
    }

    /**
     * Méthodes de convenance pour différents niveaux
     */
    public function debug($message, $context = []) {
        // $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    public function info($message, $context = []) {
        // $this->log(self::LEVEL_INFO, $message, $context);
    }

    public function warning($message, $context = []) {
        // $this->log(self::LEVEL_WARNING, $message, $context);
    }

    public function error($message, $context = []) {
        // $this->log(self::LEVEL_ERROR, $message, $context);
    }

    public function critical($message, $context = []) {
        // $this->log(self::LEVEL_CRITICAL, $message, $context);
    }

    /**
     * Log un message de debug seulement si le debug PHP est activé
     */
    public function debug_log($message) {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $debug_php_errors = isset($settings['pdf_builder_debug_php_errors']) && $settings['pdf_builder_debug_php_errors'];

        if ($debug_php_errors) {

        }
    }

    /**
     * Handler pour les erreurs PHP fatales au shutdown
     */
    public function log_php_errors() {
        // Vérifier si le debug PHP errors est activé
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $debug_php_errors = isset($settings['pdf_builder_debug_php_errors']) && $settings['pdf_builder_debug_php_errors'];

        if (!$debug_php_errors) {
            return; // Ne pas logger les erreurs PHP si le toggle est désactivé
        }

        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->critical('PHP Fatal Error', [
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type']
            ]);
        }
    }

    /**
     * Formatte une entrée de log
     */
    private function format_log_entry($level, $message, $context = []) {
        $timestamp = current_time('Y-m-d H:i:s');
        $level_name = $this->level_names[$level] ?? 'UNKNOWN';

        $entry = sprintf(
            "[%s] %s: %s",
            $timestamp,
            $level_name,
            $message
        );

        if (!empty($context)) {
            $entry .= " | Context: " . wp_json_encode($context);
        }

        // Ajouter des informations de debug si activé
        if (defined('WP_DEBUG') && WP_DEBUG && $level <= self::LEVEL_DEBUG) {
            $entry .= " | File: " . (isset($context['file']) ? $context['file'] : 'unknown');
            $entry .= " | Line: " . (isset($context['line']) ? $context['line'] : 'unknown');
        }

        return $entry . "\n";
    }

    /**
     * Écrit dans le fichier de log
     */
    private function write_to_file($entry) {
        // Créer le dossier s'il n'existe pas
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        // Vérifier la taille du fichier et faire une rotation si nécessaire
        if (file_exists($this->log_file) && filesize($this->log_file) > $this->max_file_size) {
            $this->rotate_log_file();
        }

        // Écrire dans le fichier
        // file_put_contents($this->log_file, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Fait une rotation du fichier de log
     */
    private function rotate_log_file() {
        $backup_file = $this->log_file . '.' . current_time('Y-m-d_H-i-s') . '.bak';

        // Renommer le fichier actuel
        if (file_exists($this->log_file)) {
            rename($this->log_file, $backup_file); // phpcs:ignore WordPress.WP.AlternativeFunctions
        }

        // Nettoyer les anciens fichiers de backup
        $this->cleanup_old_backups();
    }

    /**
     * Nettoie les anciens fichiers de log
     */
    public function cleanup_old_logs() {
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            return;
        }

        $files = glob($log_dir . '/pdf-builder*.log*');
        $now = current_time('timestamp');
        $max_age = $this->retention_days * DAY_IN_SECONDS;

        foreach ($files as $file) {
            if (file_exists($file) && ($now - filemtime($file)) > $max_age) {
                wp_delete_file($file);
            }
        }
    }

    /**
     * Nettoie les anciens fichiers de backup
     */
    private function cleanup_old_backups() {
        $log_dir = dirname($this->log_file);
        $backup_files = glob($log_dir . '/pdf-builder.log.*.bak');

        // Garder seulement les 5 derniers backups
        if (count($backup_files) > 5) {
            usort($backup_files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            $files_to_delete = array_slice($backup_files, 5);
            foreach ($files_to_delete as $file) {
                wp_delete_file($file);
            }
        }
    }

    /**
     * Récupère les dernières entrées du log
     */
    public function get_recent_logs($lines = 100) {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $logs = [];
        $file = new SplFileObject($this->log_file, 'r');
        $file->seek(PHP_INT_MAX);
        $total_lines = $file->key();

        $start_line = max(0, $total_lines - $lines);
        $file->seek($start_line);

        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $logs[] = $line;
            }
            $file->next();
        }

        return array_reverse($logs);
    }

    /**
     * Recherche dans les logs
     */
    public function search_logs($query, $level = null, $limit = 50) {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $results = [];
        $file = new SplFileObject($this->log_file, 'r');

        while (!$file->eof() && count($results) < $limit) {
            $line = trim($file->current());
            if (!empty($line)) {
                $matches_query = stripos($line, $query) !== false;

                if ($level !== null) {
                    $matches_level = strpos($line, $this->level_names[$level]) !== false;
                } else {
                    $matches_level = true;
                }

                if ($matches_query && $matches_level) {
                    $results[] = $line;
                }
            }
            $file->next();
        }

        return $results;
    }

    /**
     * Obtient les statistiques des logs
     */
    public function get_log_stats() {
        $stats = [
            'file_exists' => file_exists($this->log_file),
            'file_size' => 0,
            'file_size_human' => '0 B',
            'last_modified' => 'Jamais',
            'total_lines' => 0,
            'level_counts' => array_fill_keys($this->level_names, 0)
        ];

        if (!file_exists($this->log_file)) {
            return $stats;
        }

        $stats['file_size'] = filesize($this->log_file);
        $stats['file_size_human'] = size_format($stats['file_size']);
        $stats['last_modified'] = wp_date(get_option('date_format') . ' ' . get_option('time_format'), filemtime($this->log_file));

        // Compter les lignes et les niveaux
        $file = new SplFileObject($this->log_file, 'r');
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $stats['total_lines']++;

                foreach ($this->level_names as $level_name) {
                    if (strpos($line, $level_name) !== false) {
                        $stats['level_counts'][$level_name]++;
                        break;
                    }
                }
            }
            $file->next();
        }

        return $stats;
    }

    /**
     * Vide le fichier de log
     */
    public function clear_logs() {
        if (file_exists($this->log_file)) {
            // file_put_contents($this->log_file, '');
        }
    }
}

// Fonctions globales pour faciliter l'utilisation
function pdf_builder_log_debug($message, $context = []) {
    PDF_Builder_Advanced_Logger::get_instance()->debug($message, $context);
}

function pdf_builder_log_info($message, $context = []) {
    PDF_Builder_Advanced_Logger::get_instance()->info($message, $context);
}

function pdf_builder_log_warning($message, $context = []) {
    PDF_Builder_Advanced_Logger::get_instance()->warning($message, $context);
}

function pdf_builder_log_error($message, $context = []) {
    PDF_Builder_Advanced_Logger::get_instance()->error($message, $context);
}

function pdf_builder_log_critical($message, $context = []) {
    PDF_Builder_Advanced_Logger::get_instance()->critical($message, $context);
}

// Initialiser le logger
add_action('plugins_loaded', function() {
    PDF_Builder_Advanced_Logger::get_instance();
});




