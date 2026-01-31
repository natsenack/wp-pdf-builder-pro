<?php

/**
 * PDF Builder Pro - Logger Class
 * Classe de logging pour le débogage et les événements
 */

/**
 * Classe singleton pour le logging des événements du plugin
 */
class PDF_Builder_Logger
{
    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Constructeur privé pour le pattern singleton
     */
    private function __construct()
    {
        // Initialisation si nécessaire
    }

    /**
     * Récupère l'instance unique de la classe
     *
     * @return PDF_Builder_Logger
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log un message de débogage
     *
     * @param string $message Le message à logger
     * @return void
     */
    public function debug_log($message)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[PDF Builder DEBUG] ' . $message);
        }
    }

    /**
     * Log un message d'information
     *
     * @param string $message Le message à logger
     * @return void
     */
    public function info_log($message)
    {
        error_log('[PDF Builder INFO] ' . $message);
    }

    /**
     * Log un message d'erreur
     *
     * @param string $message Le message à logger
     * @return void
     */
    public function error_log($message)
    {
        error_log('[PDF Builder ERROR] ' . $message);
    }

    /**
     * Log un message d'avertissement
     *
     * @param string $message Le message à logger
     * @return void
     */
    public function warning_log($message)
    {
        error_log('[PDF Builder WARNING] ' . $message);
    }
}



