<?php
/**
 * PDF Builder Logger
 * Système de logging avancé pour le plugin PDF Builder Pro
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

class PDF_Builder_Logger {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Niveau de log actuel
     */
    private $log_level = 2;

    /**
     * Fichier de log actuel
     */
    private $log_file = null;

    /**
     * Taille maximale du fichier de log (en octets)
     */
    private $max_log_size = 10485760; // 10 Mo

    /**
     * Nombre maximum de fichiers de log
     */
    private $max_log_files = 5;

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->init_logger();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser le logger
     */
    private function init_logger() {
        $this->log_level = pdf_builder_get_log_level();
        $this->max_log_size = PDF_BUILDER_MAX_LOG_SIZE;
        $this->max_log_files = PDF_BUILDER_MAX_LOG_FILES;

        // Créer le répertoire des logs s'il n'existe pas
        if (!file_exists(PDF_BUILDER_PRO_LOGS_DIR)) {
            wp_mkdir_p(PDF_BUILDER_PRO_LOGS_DIR);
        }

        // Définir le fichier de log actuel
        $this->log_file = PDF_BUILDER_PRO_LOGS_DIR . 'pdf-builder-' . date('Y-m-d') . '.log';

        // Nettoyer les anciens fichiers de log
        $this->cleanup_old_logs();

        pdf_builder_debug('Logger initialized with level: ' . $this->log_level, 3, 'logger');
    }

    /**
     * Logger un message
     */
    public function log($message, $level = 1, $context = 'general', $data = null) {
        if (!$this->should_log($level)) {
            return false;
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $level_name = $this->get_level_name($level);
        $context = sanitize_text_field($context);

        // Formater le message
        $log_entry = sprintf(
            "[%s] %s %-8s %s: %s",
            $timestamp,
            $context,
            $level_name,
            get_current_user_id() ? 'User:' . get_current_user_id() : 'System',
            $message
        );

        // Ajouter les données si présentes
        if ($data !== null) {
            if (is_array($data) || is_object($data)) {
                $log_entry .= "\nData: " . json_encode($data, JSON_PRETTY_PRINT);
            } else {
                $log_entry .= "\nData: " . $data;
            }
        }

        $log_entry .= "\n" . str_repeat('-', 80) . "\n";

        // Écrire dans le fichier de log
        return $this->write_to_file($log_entry);
    }

    /**
     * Logger une erreur
     */
    public function error($message, $context = 'general', $data = null) {
        return $this->log($message, 1, $context, $data);
    }

    /**
     * Logger un avertissement
     */
    public function warning($message, $context = 'general', $data = null) {
        return $this->log($message, 2, $context, $data);
    }

    /**
     * Logger une information
     */
    public function info($message, $context = 'general', $data = null) {
        return $this->log($message, 3, $context, $data);
    }

    /**
     * Logger un message de debug
     */
    public function debug($message, $context = 'general', $data = null) {
        return $this->log($message, 4, $context, $data);
    }

    /**
     * Logger une exception
     */
    public function log_exception($exception, $context = 'general') {
        $data = array(
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        );

        return $this->error('Exception caught: ' . $exception->getMessage(), $context, $data);
    }

    /**
     * Vérifier si le message doit être loggé
     */
    private function should_log($level) {
        return pdf_builder_logging_enabled() && $level <= $this->log_level;
    }

    /**
     * Obtenir le nom du niveau de log
     */
    private function get_level_name($level) {
        $levels = array(
            1 => 'ERROR',
            2 => 'WARNING',
            3 => 'INFO',
            4 => 'DEBUG'
        );

        return isset($levels[$level]) ? $levels[$level] : 'UNKNOWN';
    }

    /**
     * Écrire dans le fichier de log
     */
    private function write_to_file($content) {
        // Vérifier la taille du fichier avant d'écrire
        if (file_exists($this->log_file) && filesize($this->log_file) >= $this->max_log_size) {
            $this->rotate_log_file();
        }

        // Écrire dans le fichier
        $result = file_put_contents($this->log_file, $content, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            // Fallback: essayer d'écrire dans le log d'erreurs PHP
            error_log('PDF Builder Logger: Failed to write to log file: ' . $this->log_file);
            return false;
        }

        return true;
    }

    /**
     * Faire tourner le fichier de log
     */
    private function rotate_log_file() {
        for ($i = $this->max_log_files - 1; $i >= 1; $i--) {
            $old_file = PDF_BUILDER_PRO_LOGS_DIR . 'pdf-builder-' . date('Y-m-d') . '.' . $i . '.log';
            $new_file = PDF_BUILDER_PRO_LOGS_DIR . 'pdf-builder-' . date('Y-m-d') . '.' . ($i + 1) . '.log';

            if (file_exists($old_file)) {
                rename($old_file, $new_file);
            }
        }

        // Renommer le fichier actuel
        $current_file = PDF_BUILDER_PRO_LOGS_DIR . 'pdf-builder-' . date('Y-m-d') . '.log';
        $backup_file = PDF_BUILDER_PRO_LOGS_DIR . 'pdf-builder-' . date('Y-m-d') . '.1.log';

        if (file_exists($current_file)) {
            rename($current_file, $backup_file);
        }
    }

    /**
     * Nettoyer les anciens fichiers de log
     */
    private function cleanup_old_logs() {
        if (!is_dir(PDF_BUILDER_PRO_LOGS_DIR)) {
            return;
        }

        $files = glob(PDF_BUILDER_PRO_LOGS_DIR . 'pdf-builder-*.log');
        $now = time();
        $max_age = 30 * 24 * 60 * 60; // 30 jours

        foreach ($files as $file) {
            if (filemtime($file) < ($now - $max_age)) {
                unlink($file);
            }
        }
    }

    /**
     * Obtenir les statistiques de logging
     */
    public function get_stats() {
        $stats = array(
            'log_level' => $this->log_level,
            'log_file' => $this->log_file,
            'max_log_size' => $this->max_log_size,
            'max_log_files' => $this->max_log_files,
            'current_size' => file_exists($this->log_file) ? filesize($this->log_file) : 0,
            'file_exists' => file_exists($this->log_file),
            'writable' => is_writable(PDF_BUILDER_PRO_LOGS_DIR)
        );

        return $stats;
    }

    /**
     * Vider les logs
     */
    public function clear_logs() {
        if (!is_dir(PDF_BUILDER_PRO_LOGS_DIR)) {
            return false;
        }

        $files = glob(PDF_BUILDER_PRO_LOGS_DIR . 'pdf-builder-*.log');
        $cleared = 0;

        foreach ($files as $file) {
            if (unlink($file)) {
                $cleared++;
            }
        }

        $this->info('Logs cleared', 'logger', array('files_cleared' => $cleared));
        return $cleared;
    }

    /**
     * Lire les dernières lignes du log
     */
    public function get_recent_logs($lines = 50) {
        if (!file_exists($this->log_file)) {
            return array();
        }

        $file = new SplFileObject($this->log_file, 'r');
        $file->seek(PHP_INT_MAX);
        $total_lines = $file->key();

        $start_line = max(0, $total_lines - $lines);
        $logs = array();

        $file->seek($start_line);
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $logs[] = $line;
            }
            $file->next();
        }

        return $logs;
    }

    /**
     * Obtenir la taille totale des logs
     */
    public function get_total_log_size() {
        if (!is_dir(PDF_BUILDER_PRO_LOGS_DIR)) {
            return 0;
        }

        $files = glob(PDF_BUILDER_PRO_LOGS_DIR . 'pdf-builder-*.log');
        $total_size = 0;

        foreach ($files as $file) {
            $total_size += filesize($file);
        }

        return $total_size;
    }

    /**
     * Changer le niveau de log
     */
    public function set_log_level($level) {
        $level = max(1, min(4, intval($level)));
        $this->log_level = $level;
        pdf_builder_set_option('log_level', $level);

        $this->info('Log level changed', 'logger', array('new_level' => $level));
        return $level;
    }

    /**
     * Obtenir le niveau de log actuel
     */
    public function get_log_level() {
        return $this->log_level;
    }
}

// Fonctions utilitaires globales pour le logging

/**
 * Logger un message d'erreur
 */
function pdf_builder_log_error($message, $context = 'general', $data = null) {
    $logger = PDF_Builder_Logger::get_instance();
    return $logger->error($message, $context, $data);
}

/**
 * Logger un avertissement
 */
function pdf_builder_log_warning($message, $context = 'general', $data = null) {
    $logger = PDF_Builder_Logger::get_instance();
    return $logger->warning($message, $context, $data);
}

/**
 * Logger une information
 */
function pdf_builder_log_info($message, $context = 'general', $data = null) {
    $logger = PDF_Builder_Logger::get_instance();
    return $logger->info($message, $context, $data);
}

/**
 * Logger un message de debug
 */
function pdf_builder_log_debug($message, $context = 'general', $data = null) {
    $logger = PDF_Builder_Logger::get_instance();
    return $logger->debug($message, $context, $data);
}

/**
 * Logger une exception
 */
function pdf_builder_log_exception($exception, $context = 'general') {
    $logger = PDF_Builder_Logger::get_instance();
    return $logger->log_exception($exception, $context);
}

/**
 * Logger un message général
 */
function pdf_builder_log($message, $level = 2, $context = 'general', $data = null) {
    $logger = PDF_Builder_Logger::get_instance();
    return $logger->log($message, $level, $context, $data);
}

