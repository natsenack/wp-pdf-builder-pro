<?php
/**
 * PDF Builder Debug Helper
 * Utilitaires de débogage pour le plugin PDF Builder Pro
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

class PDF_Builder_Debug_Helper {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Niveau de débogage
     */
    private $debug_level = 0;

    /**
     * Constructeur privé
     */
    private function __construct() {
        $this->debug_level = defined('WP_DEBUG') && WP_DEBUG ? 1 : 0;
        $this->debug_level = defined('PDF_BUILDER_DEBUG') ? PDF_BUILDER_DEBUG : $this->debug_level;
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
     * Logger un message de débogage
     */
    public function log($message, $level = 1, $context = '') {
        if ($this->debug_level >= $level) {
            $prefix = '[PDF Builder Pro] ';
            if (!empty($context)) {
                $prefix .= "[$context] ";
            }

            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log($prefix . $message);
            }

            // En développement, afficher aussi dans la console
            if ($this->debug_level >= 2 && defined('WP_DEBUG') && WP_DEBUG) {
                // Debug console output removed
            }
        }
    }

    /**
     * Logger une erreur
     */
    public function error($message, $context = '') {
        $this->log('ERROR: ' . $message, 0, $context);
    }

    /**
     * Logger un avertissement
     */
    public function warning($message, $context = '') {
        $this->log('WARNING: ' . $message, 1, $context);
    }

    /**
     * Logger une information
     */
    public function info($message, $context = '') {
        $this->log('INFO: ' . $message, 1, $context);
    }

    /**
     * Vérifier si le débogage est activé
     */
    public function is_debug_enabled() {
        return $this->debug_level > 0;
    }

    /**
     * Obtenir le niveau de débogage
     */
    public function get_debug_level() {
        return $this->debug_level;
    }
}

// Fonction globale pour accéder au debug helper
function pdf_builder_debug($message, $level = 1, $context = '') {
    PDF_Builder_Debug_Helper::get_instance()->log($message, $level, $context);
}

