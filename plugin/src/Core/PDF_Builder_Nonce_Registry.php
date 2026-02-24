<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * PDF Builder Pro - Registre Centralisé des Nonces
 * Source unique de vérité pour tous les nonces du plugin
 * Version: 1.0.0 - Unification complète
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * Registre centralisé et unique pour tous les nonces
 */
class PDF_Builder_Nonce_Registry {
    
    private static $instance = null;
    
    /**
     * action => options de nonce
     */
    private static $nonce_registry = [
        // ========== ACTIONS PRINCIPALES ==========
        'pdf_builder_ajax' => [
            'description' => 'Action AJAX générale par défaut',
            'ttl' => 43200, // 12 heures
            'capability' => 'edit_posts',
            'aliases' => [], // Anciennes actions remappées
        ],
        
        // ========== ACTIONS ADMINISTRATION ==========
        'pdf_builder_settings' => [
            'description' => 'Sauvegarde des paramètres',
            'ttl' => 43200,
            'capability' => 'manage_options',
            'aliases' => [],
        ],
        
        // ========== ACTIONS TEMPLATES ==========
        'pdf_builder_templates' => [
            'description' => 'Gestion des templates',
            'ttl' => 43200,
            'capability' => 'edit_posts',
            'aliases' => ['pdf_builder_predefined_templates'],
        ],
        
        // ========== ACTIONS COMMANDES WOOCOMMERCE ==========
        'pdf_builder_order_actions' => [
            'description' => 'Actions sur commandes WooCommerce',
            'ttl' => 43200,
            'capability' => 'manage_orders',
            'aliases' => [],
        ],
        
        // ========== ACTIONS GDPR ==========
        'pdf_builder_gdpr' => [
            'description' => 'Protections RGPD et données utilisateur',
            'ttl' => 43200,
            'capability' => 'manage_options',
            'aliases' => [],
        ],
        
        // ========== ACTIONS NOTIFICATIONS ==========
        'pdf_builder_notifications' => [
            'description' => 'Gestion des notifications',
            'ttl' => 43200,
            'capability' => 'read',
            'aliases' => [],
        ],
        
        // ========== ACTIONS ONBOARDING ==========
        'pdf_builder_onboarding' => [
            'description' => 'Étapes d\'onboarding',
            'ttl' => 86400, // 24 heures (plus long)
            'capability' => 'manage_options',
            'aliases' => [],
        ],
        
        // ========== ACTIONS CANVAS ==========
        'pdf_builder_canvas_settings' => [
            'description' => 'Paramètres du canvas',
            'ttl' => 43200,
            'capability' => 'edit_posts',
            'aliases' => [],
        ],
        
        // ========== ACTIONS LICENCE ==========
        'pdf_builder_license' => [
            'description' => 'Gestion des licences',
            'ttl' => 43200,
            'capability' => 'manage_options',
            'aliases' => ['pdf_builder_deactivate'],
        ],
        
        // ========== ACTIONS MAINTENANCE ==========
        'pdf_builder_maintenance' => [
            'description' => 'Tâches de maintenance',
            'ttl' => 43200,
            'capability' => 'manage_options',
            'aliases' => [],
        ],
        
        // ========== ACTIONS CRON ==========
        'pdf_builder_cron' => [
            'description' => 'Planification des tâches CRON',
            'ttl' => 43200,
            'capability' => 'manage_options',
            'aliases' => ['pdf_builder_cron_test'],
        ],
    ];
    
    /**
     * Singleton
     */
    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtenir la configuration d'une action
     */
    public static function get_action_config(string $action): ?array {
        return self::instance()->registry()[$action] ?? null;
    }
    
    /**
     * Obtenir toutes les actions enregistrées
     */
    public static function get_all_actions(): array {
        return array_keys(self::instance()->registry());
    }
    
    /**
     * Vérifier si une action est enregistrée
     */
    public static function is_registered(string $action): bool {
        return isset(self::instance()->registry()[$action]);
    }
    
    /**
     * Obtenir la capacité requise pour une action
     */
    public static function get_capability(string $action): string {
        $config = self::get_action_config($action);
        return $config['capability'] ?? 'manage_options';
    }
    
    /**
     * Trouver l'action canonique pour un alias
     */
    public static function resolve_action(string $action_or_alias): string {
        // Si c'est directement enregistré, retourner
        if (self::is_registered($action_or_alias)) {
            return $action_or_alias;
        }
        
        // Chercher dans les aliases
        foreach (self::instance()->registry() as $canonical_action => $config) {
            if (in_array($action_or_alias, $config['aliases'] ?? [])) {
                return $canonical_action;
            }
        }
        
        // Par défaut, retourner l'action
        return $action_or_alias ?? 'pdf_builder_ajax';
    }
    
    /**
     * Obtenir le registre complet
     */
    private function registry(): array {
        return self::$nonce_registry;
    }
    
    /**
     * Ajouter une action personnalisée
     */
    public static function register_custom_action(string $action, array $config = []): void {
        $defaults = [
            'description' => 'Action personnalisée',
            'ttl' => 43200,
            'capability' => 'edit_posts',
            'aliases' => [],
        ];
        
        self::$nonce_registry[$action] = array_merge($defaults, $config);
    }
    
    /**
     * Logging pour audit
     */
    public static function log_nonce_event(string $event, string $action, array $data = []): void {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'action' => $action,
            'user_id' => get_current_user_id(),
            'data' => $data,
        ];
        
        error_log('[PDF_BUILDER_NONCE] ' . json_encode($log_entry));
    }
}

/**
 * Objet global pour accès simplifié (wrapper)
 */
if (!function_exists('pdf_builder_nonce_registry')) {
    function pdf_builder_nonce_registry(): PDF_Builder_Nonce_Registry {
        return PDF_Builder_Nonce_Registry::instance();
    }
}
