<?php
/**
 * PDF Builder Pro - Système de surveillance de la santé - DISABLED
 * Système de surveillance supprimé pour simplification
 */

class PDF_Builder_Health_Monitor_DISABLED {

    /**
     * Obtenir l'instance - DÉSACTIVÉ
     */
    public static function get_instance()
    {
        // Surveillance désactivée - système de cache supprimé
        return false;
    }

    /**
     * Obtenir l'état de santé actuel - DÉSACTIVÉ
     */
    public static function get_latest_health_status()
    {
        // Surveillance désactivée - système de cache supprimé
        return ['status' => 'disabled'];
    }

    /**
     * Effectuer des vérifications de santé - DÉSACTIVÉ
     */
    public static function perform_health_checks()
    {
        // Vérifications désactivées - système de cache supprimé
        return ['status' => 'disabled'];
    }
}
