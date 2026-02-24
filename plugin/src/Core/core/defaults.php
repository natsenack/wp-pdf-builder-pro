<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * PDF Builder Defaults - Valeurs par défaut centralisées
 *
 * Centralise toutes les valeurs par défaut répétitives du plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class PDF_Builder_Defaults {

    // ==========================================
    // COULEURS PAR DÉFAUT
    // ==========================================

    const COLORS = [
        'white' => '#ffffff',
        'light_gray' => '#f8f9fa',
        'medium_gray' => '#cccccc',
        'dark_gray' => '#666666',
        'text_dark' => '#333333',
        'text_medium' => '#666666',
        'black' => '#000000',
        'success' => '#28a745',
        'warning' => '#ffa500',
        'error' => '#dc3545',
        'info' => '#17a2b8'
    ];

    // ==========================================
    // DIMENSIONS PAR DÉFAUT
    // ==========================================

    const DIMENSIONS = [
        'canvas_width' => 794,
        'canvas_height' => 1123,
        'border_width' => 1,
        'margin_top' => 28,
        'margin_right' => 28,
        'margin_bottom' => 10,
        'margin_left' => 10,
        'grid_size' => 20,
        'font_size_large' => 24,
        'font_size_medium' => 16,
        'font_size_small' => 14,
        'font_size_extra_small' => 12,
        'zoom_min' => 10,
        'zoom_max' => 500,
        'zoom_default' => 100,
        'zoom_step' => 25
    ];

    // ==========================================
    // VALEURS TECHNIQUES PAR DÉFAUT
    // ==========================================

    const TECHNICAL = [
        'dpi' => 96,
        'fps_target' => 30,
        'memory_limit_js' => 128,
        'memory_limit_php' => 256,
        'export_quality' => 90,
        'image_quality' => 85,
        'max_image_size' => 2048,
        'auto_save_versions' => 10,
        'max_fps' => 60,
        'response_timeout' => 30
    ];

    // ==========================================
    // FORMAT DE PAPIER
    // ==========================================

    const PAPER_FORMATS = [
        'A4' => ['width' => 210, 'height' => 297],
        'A3' => ['width' => 297, 'height' => 420],
        'A5' => ['width' => 148, 'height' => 210],
        'Letter' => ['width' => 215.9, 'height' => 279.4],
        'Legal' => ['width' => 215.9, 'height' => 355.6],
        'Tabloid' => ['width' => 279.4, 'height' => 431.8]
    ];

    // ==========================================
    // ORIENTATIONS
    // ==========================================

    const ORIENTATIONS = [
        'portrait' => 'portrait',
        'landscape' => 'landscape'
    ];

    // ==========================================
    // UNITÉS
    // ==========================================

    const UNITS = [
        'px' => 'px',
        'mm' => 'mm',
        'cm' => 'cm',
        'in' => 'in'
    ];

    // ==========================================
    // POLICES PAR DÉFAUT
    // ==========================================

    const FONTS = [
        'arial' => 'Arial',
        'helvetica' => 'Helvetica',
        'times' => 'Times New Roman',
        'courier' => 'Courier New',
        'verdana' => 'Verdana'
    ];

    // ==========================================
    // MESSAGES RÉPÉTITIFS
    // ==========================================

    const MESSAGES = [
        'success_save' => 'sauvegardés avec succès.',
        'error_save' => 'Erreur lors de la sauvegarde',
        'error_security' => 'Erreur de sécurité - nonce invalide.',
        'error_unknown' => 'Erreur inconnue',
        'enabled' => 'Activé',
        'disabled' => 'Désactivé',
        'loading' => 'Chargement...',
        'ready' => 'Prêt',
        'saving' => 'Sauvegarde en cours...',
        'saved' => 'Sauvegardé',
        'error' => 'Erreur',
        'success' => 'Succès',
        'warning' => 'Attention',
        'info' => 'Information'
    ];

    // ==========================================
    // STATUTS
    // ==========================================

    const STATUSES = [
        'active' => 'active',
        'inactive' => 'inactive',
        'enabled' => 'enabled',
        'disabled' => 'disabled',
        'pending' => 'pending',
        'completed' => 'completed',
        'error' => 'error'
    ];

    // ==========================================
    // MÉTHODES D'ACCÈS STATIQUE
    // ==========================================

    /**
     * Obtenir une couleur par clé
     */
    public static function get_color($key) {
        return self::COLORS[$key] ?? self::COLORS['white'];
    }

    /**
     * Obtenir une dimension par clé
     */
    public static function get_dimension($key) {
        return self::DIMENSIONS[$key] ?? 0;
    }

    /**
     * Obtenir une valeur technique par clé
     */
    public static function get_technical($key) {
        return self::TECHNICAL[$key] ?? null;
    }

    /**
     * Obtenir un format de papier
     */
    public static function get_paper_format($format) {
        return self::PAPER_FORMATS[$format] ?? self::PAPER_FORMATS['A4'];
    }

    /**
     * Obtenir une police par clé
     */
    public static function get_font($key) {
        return self::FONTS[$key] ?? self::FONTS['arial'];
    }

    /**
     * Obtenir un message par clé
     */
    public static function get_message($key) {
        return self::MESSAGES[$key] ?? '';
    }

    /**
     * Obtenir un statut par clé
     */
    public static function get_status($key) {
        return self::STATUSES[$key] ?? self::STATUSES['inactive'];
    }
}



