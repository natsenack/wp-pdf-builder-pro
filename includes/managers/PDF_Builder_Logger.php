<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Logger
 * Système de logging pour le plugin PDF Builder Pro
 */



class PDF_Builder_Logger {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Niveau de logging
     */
    private $log_level = 1;

    /**
     * Fichier de log
     */
    private $log_file = '';

    /**
     * Constructeur privé
     */
    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->log_file = $upload_dir['basedir'] . '/pdf-builder-logs/plugin.log';

        // Créer le dossier de logs s'il n'existe pas
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        $this->log_level = defined('PDF_BUILDER_LOG_LEVEL') ? PDF_BUILDER_LOG_LEVEL : 1;
    }

    /**
     * Obtenir l'instance unique
     */
    public static function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Logger un message
     */
    public function log($message, $level = 1, $context = array()) {
        if ($this->log_level < $level) {
            return;
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $level_name = $this->get_level_name($level);

        $log_entry = sprintf(
            "[%s] %s: %s",
            $timestamp,
            $level_name,
            $message
        );

        if (!empty($context)) {
            $log_entry .= " | Context: " . json_encode($context);
        }

        $log_entry .= "\n";

        // Écrire dans le fichier de log
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);

        // En développement, logger aussi dans error_log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[PDF Builder Pro] ' . $log_entry);
        }
    }

    /**
     * Logger une erreur
     */
    public function error($message, $context = array()) {
        $this->log($message, 0, $context);
    }

    /**
     * Logger un avertissement
     */
    public function warning($message, $context = array()) {
        $this->log($message, 1, $context);
    }

    /**
     * Logger une information
     */
    public function info($message, $context = array()) {
        $this->log($message, 2, $context);
    }

    /**
     * Logger un message de débogage
     */
    public function debug($message, $context = array()) {
        $this->log($message, 3, $context);
    }

    /**
     * Obtenir le nom du niveau
     */
    private function get_level_name($level) {
        $levels = array(
            0 => 'ERROR',
            1 => 'WARNING',
            2 => 'INFO',
            3 => 'DEBUG'
        );

        return isset($levels[$level]) ? $levels[$level] : 'UNKNOWN';
    }

    /**
     * Nettoyer les anciens logs
     */
    public function cleanup($days = 30) {
        if (file_exists($this->log_file)) {
            $cutoff_time = time() - ($days * 24 * 60 * 60);

            $lines = file($this->log_file);
            $new_lines = array();

            foreach ($lines as $line) {
                // Extraire la date de la ligne
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                    $line_time = strtotime($matches[1]);
                    if ($line_time > $cutoff_time) {
                        $new_lines[] = $line;
                    }
                } else {
                    $new_lines[] = $line;
                }
            }

            file_put_contents($this->log_file, implode('', $new_lines), LOCK_EX);
        }
    }

    /**
     * Obtenir le contenu du fichier de log
     */
    public function get_log_contents($lines = 100) {
        if (!file_exists($this->log_file)) {
            return '';
        }

        $file_lines = file($this->log_file);
        return implode('', array_slice($file_lines, -$lines));
    }
}

// Fonction globale pour le logging
function pdf_builder_log($message, $level = 2, $context = array()) {
    PDF_Builder_Logger::getInstance()->log($message, $level, $context);
}


