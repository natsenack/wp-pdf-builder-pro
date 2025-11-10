<?php

namespace WP_PDF_Builder_Pro\Managers;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Status Manager
 * Gestion dynamique des statuts de commande WooCommerce et assignation de templates
 */

class PdfBuilderStatusManager
{
    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Cache des statuts détectés
     */
    private $detected_statuses = null;

    /**
     * Cache des mappings
     */
    private $status_mappings = null;

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
        $this->initHooks();
    }

    /**
     * Initialiser les hooks
     */
    private function initHooks()
    {
        // Détection automatique des statuts lors de l'activation de plugins
        add_action('activated_plugin', [$this, 'check_for_new_statuses']);
        add_action('deactivated_plugin', [$this, 'check_for_removed_statuses']);

        // Hook pour étendre les paramètres
        add_filter('pdf_builder_order_status_settings', [$this, 'extend_status_settings']);

        // Vérification périodique des statuts (une fois par jour)
        if (!wp_next_scheduled('pdf_builder_daily_status_check')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_daily_status_check');
        }
        add_action('pdf_builder_daily_status_check', [$this, 'daily_status_check']);

        // Hook pour la génération PDF avec fallback
        add_filter('pdf_builder_get_template_for_status', [$this, 'get_template_with_fallback'], 10, 2);
    }

    /**
     * Détecter tous les statuts WooCommerce disponibles
     */
    public function detectWoocommerceStatuses()
    {
        if ($this->detected_statuses !== null) {
            return $this->detected_statuses;
        }

        $statuses = [];

        // Statuts par défaut WooCommerce
        $default_statuses = [
            'wc-pending' => __('En attente', 'pdf-builder-pro'),
            'wc-processing' => __('En cours', 'pdf-builder-pro'),
            'wc-on-hold' => __('En attente', 'pdf-builder-pro'),
            'wc-completed' => __('Terminée', 'pdf-builder-pro'),
            'wc-cancelled' => __('Annulée', 'pdf-builder-pro'),
            'wc-refunded' => __('Remboursée', 'pdf-builder-pro'),
            'wc-failed' => __('Échec', 'pdf-builder-pro')
        ];

        // Fusionner avec les statuts par défaut
        $statuses = array_merge($statuses, $default_statuses);

        // Détecter les statuts personnalisés via WooCommerce
        if (function_exists('wc_get_order_statuses')) {
            $wc_statuses = wc_get_order_statuses();
            foreach ($wc_statuses as $status_key => $status_name) {
                // WooCommerce retourne les clés avec 'wc-' préfixe
                $statuses[$status_key] = $status_name;
            }
        }

        // Détecter les statuts via plugins populaires
        $statuses = $this->detectPluginStatuses($statuses);

        $this->detected_statuses = $statuses;
        return $statuses;
    }

    /**
     * Détecter les statuts ajoutés par des plugins populaires
     */
    private function detectPluginStatuses($existing_statuses)
    {
        // Liste des plugins qui ajoutent des statuts personnalisés
        $plugin_statuses = [
            // Additional Custom Order Status for WooCommerce
            'wc-devis' => __('Devis', 'pdf-builder-pro'),
            'wc-quote' => __('Devis', 'pdf-builder-pro'),
            'wc-estimate' => __('Estimation', 'pdf-builder-pro'),
            'wc-partial-payment' => __('Paiement partiel', 'pdf-builder-pro'),
            'wc-quotation' => __('Devis', 'pdf-builder-pro'),

            // WooCommerce Quote
            'wc-quote-sent' => __('Devis envoyé', 'pdf-builder-pro'),
            'wc-quote-accepted' => __('Devis accepté', 'pdf-builder-pro'),
            'wc-quote-rejected' => __('Devis rejeté', 'pdf-builder-pro'),

            // WooCommerce Order Status Manager
            'wc-custom-status' => __('Statut personnalisé', 'pdf-builder-pro'),
            'wc-awaiting-shipment' => __('En attente d\'expédition', 'pdf-builder-pro'),
            'wc-shipped' => __('Expédié', 'pdf-builder-pro'),
            'wc-delivered' => __('Livré', 'pdf-builder-pro'),

            // Autres plugins courants
            'wc-backordered' => __('En rupture de stock', 'pdf-builder-pro'),
            'wc-pre-ordered' => __('Pré-commandé', 'pdf-builder-pro'),
            'wc-disputed' => __('Contesté', 'pdf-builder-pro'),
        ];

        // Vérifier si ces statuts existent réellement
        foreach ($plugin_statuses as $status_key => $status_name) {
            if (!isset($existing_statuses[$status_key]) && $this->statusExists($status_key)) {
                $existing_statuses[$status_key] = $status_name;
            }
        }

        return $existing_statuses;
    }

    /**
     * Vérifier si un statut existe réellement
     */
    private function statusExists($status_key)
    {
        // En mode test ou si $wpdb n'est pas disponible, retourner false
        if (!isset($GLOBALS['wpdb']) || !is_object($GLOBALS['wpdb'])) {
            return false;
        }

        global $wpdb;

        // Vérifier dans les commandes existantes
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'shop_order'
             AND pm.meta_key = '_status'
             AND pm.meta_value = %s
             LIMIT 1",
                str_replace('wc-', '', $status_key)
            )
        );

        return $count > 0;
    }

    /**
     * Étendre les paramètres avec les nouveaux statuts détectés
     */
    public function extendStatusSettings($settings)
    {
        $all_statuses = $this->detectWoocommerceStatuses();
        $current_mappings = $this->getStatusMappings();

        // Ajouter les nouveaux statuts automatiquement
        foreach ($all_statuses as $status_key => $status_name) {
            if (!isset($current_mappings[$status_key])) {
                // Nouveau statut détecté - assigner template par défaut si disponible
                $default_template = $this->getDefaultTemplateId();
                if ($default_template) {
                    $current_mappings[$status_key] = $default_template;
                    $this->logStatusDetection($status_key, $status_name, 'auto_assigned');
                } else {
                    $this->logStatusDetection($status_key, $status_name, 'detected_no_default');
                }
            }
        }

        // Sauvegarder les nouveaux mappings
        update_option('pdf_builder_order_status_templates', $current_mappings);

        return $settings;
    }

    /**
     * Obtenir l'ID du template par défaut
     */
    private function getDefaultTemplateId()
    {
        // En mode test ou si $wpdb n'est pas disponible, retourner null
        if (!isset($GLOBALS['wpdb']) || !is_object($GLOBALS['wpdb'])) {
            return null;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Chercher un template marqué comme par défaut ou le premier disponible
        $default_template = $wpdb->get_var(
            "SELECT id FROM $table_templates
             WHERE is_default = 1
             ORDER BY id ASC LIMIT 1"
        );

        if (!$default_template) {
            // Si pas de template par défaut, prendre le premier template disponible
            $default_template = $wpdb->get_var(
                "SELECT id FROM $table_templates ORDER BY id ASC LIMIT 1"
            );
        }

        return $default_template;
    }

    /**
     * Obtenir les mappings statut -> template
     */
    public function getStatusMappings()
    {
        if ($this->status_mappings === null) {
            $this->status_mappings = get_option('pdf_builder_order_status_templates', []);
        }
        return $this->status_mappings;
    }

    /**
     * Obtenir le template pour un statut avec fallback
     */
    public function getTemplateWithFallback($template_id, $status_key)
    {
        $mappings = $this->getStatusMappings();

        if (isset($mappings[$status_key]) && $mappings[$status_key] > 0) {
            return $mappings[$status_key];
        }

        // Fallback vers template par défaut
        $default_template = $this->getDefaultTemplateId();
        if ($default_template) {
            $this->logUnknownStatusUsage($status_key);
            return $default_template;
        }

        return $template_id; // Retourner l'original si rien trouvé
    }

    /**
     * Logger la détection d'un nouveau statut
     */
    private function logStatusDetection($status_key, $status_name, $action)
    {
        // Utiliser la classe avec le bon espace de noms
        $logger = \PDF_Builder\Managers\PDF_Builder_Logger::getInstance();
        $message = sprintf(
            'Nouveau statut détecté: %s (%s) - Action: %s',
            $status_key,
            $status_name,
            $action
        );
        $logger->log($message, 'info', 'status_manager');
    }
    /**
     * Logger l'utilisation d'un statut inconnu
     */
    private function logUnknownStatusUsage($status_key)
    {
        $logger = \PDF_Builder\Managers\PDF_Builder_Logger::getInstance();
        $message = sprintf(
            'Statut inconnu utilisé: %s - Template par défaut appliqué',
            $status_key
        );
        $logger->log($message, 'warning', 'status_manager');
    }

    /**
     * Vérification quotidienne des statuts
     */
    public function dailyStatusCheck()
    {
        $this->detectWoocommerceStatuses();
        // Forcer la régénération du cache
        $this->detected_statuses = null;
        $this->status_mappings = null;
    }

    /**
     * Vérifier les nouveaux statuts lors de l'activation d'un plugin
     */
    public function checkForNewStatuses($plugin)
    {
        // Plugins qui ajoutent des statuts personnalisés
        $status_plugins = [
            'additional-custom-order-status-for-woocommerce/additional-custom-order-status.php',
            'woocommerce-order-status-manager/woocommerce-order-status-manager.php',
            'woo-quote/woo-quote.php',
            // Ajouter d'autres plugins si nécessaire
        ];

        if (in_array($plugin, $status_plugins)) {
            // Attendre un peu que le plugin s'initialise
            wp_schedule_single_event(time() + 30, 'pdf_builder_check_new_statuses_delayed');
        }
    }

    /**
     * Vérifier les statuts supprimés lors de la désactivation d'un plugin
     */
    public function checkForRemovedStatuses($plugin)
    {
        // Nettoyer le cache
        $this->detected_statuses = null;
        $this->status_mappings = null;
    }

    /**
     * Obtenir les informations détaillées sur les statuts pour l'admin
     */
    public function getStatusInfoForAdmin()
    {
        $all_statuses = $this->detectWoocommerceStatuses();
        $mappings = $this->getStatusMappings();
        $default_template = $this->getDefaultTemplateId();

        $info = [
            'total_statuses' => count($all_statuses),
            'mapped_statuses' => count($mappings),
            'unmapped_statuses' => count($all_statuses) - count($mappings),
            'default_template_id' => $default_template,
            'newly_detected' => []
        ];

        // Identifier les statuts récemment détectés
        foreach ($all_statuses as $status_key => $status_name) {
            if (!isset($mappings[$status_key])) {
                $info['newly_detected'][] = [
                    'key' => $status_key,
                    'name' => $status_name,
                    'auto_assigned' => $default_template ? true : false
                ];
            }
        }

        return $info;
    }
}
