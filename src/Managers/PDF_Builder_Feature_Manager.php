<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Feature Manager
 * Gestion des fonctionnalités freemium
 */



class PDF_Builder_Feature_Manager {

    /**
     * Définition des fonctionnalités et leurs restrictions
     */
    private static $features = [
        // FREE FEATURES - Toujours disponibles
        'basic_templates' => [
            'free' => true,
            'premium' => true,
            'name' => 'Templates de base',
            'description' => '4 templates prédéfinis (Facture, Devis, Reçu, Autre)'
        ],
        'basic_elements' => [
            'free' => true,
            'premium' => true,
            'name' => 'Éléments standards',
            'description' => 'Texte, image, ligne, rectangle'
        ],
        'woocommerce_integration' => [
            'free' => true,
            'premium' => true,
            'name' => 'Intégration WooCommerce',
            'description' => 'Variables de commande et produit'
        ],

        // LIMITED FREE FEATURES - Avec limites
        'pdf_generation' => [
            'free' => true,
            'premium' => true,
            'limit' => 50, // 50 PDFs par mois
            'name' => 'Génération PDF',
            'description' => 'Création de documents PDF'
        ],

        // PREMIUM FEATURES - Payantes uniquement
        'advanced_templates' => [
            'free' => false,
            'premium' => true,
            'name' => 'Templates avancés',
            'description' => 'Bibliothèque complète de templates personnalisables'
        ],
        'premium_elements' => [
            'free' => false,
            'premium' => true,
            'name' => 'Éléments premium',
            'description' => 'Codes-barres, QR codes, graphiques, signatures'
        ],
        'bulk_generation' => [
            'free' => false,
            'premium' => true,
            'name' => 'Génération en masse',
            'description' => 'Création multiple de PDFs'
        ],
        'api_access' => [
            'free' => false,
            'premium' => true,
            'name' => 'API développeur',
            'description' => 'Accès complet à l\'API REST'
        ],
        'white_label' => [
            'free' => false,
            'premium' => true,
            'name' => 'White-label',
            'description' => 'Rebranding et personnalisation complète'
        ],
        'multi_format_export' => [
            'free' => false,
            'premium' => true,
            'name' => 'Export multi-format',
            'description' => 'PDF, PNG, JPG, SVG'
        ],
        'priority_support' => [
            'free' => false,
            'premium' => true,
            'name' => 'Support prioritaire',
            'description' => 'Support 24/7 avec SLA garanti'
        ],
        'advanced_analytics' => [
            'free' => false,
            'premium' => true,
            'name' => 'Analytics avancés',
            'description' => 'Tableaux de bord détaillés et rapports'
        ]
    ];

    /**
     * Vérifier si une fonctionnalité peut être utilisée
     */
    public static function can_use_feature($feature_name) {
        $license_manager = PDF_Builder_License_Manager::getInstance();
        $is_premium = $license_manager->is_premium();

        if (!isset(self::$features[$feature_name])) {
            return false;
        }

        $feature = self::$features[$feature_name];

        if ($is_premium) {
            return $feature['premium'];
        }

        // Pour les utilisateurs free, vérifier les limites d'usage
        if (isset($feature['limit'])) {
            return self::check_usage_limit($feature_name, $feature['limit']);
        }

        return $feature['free'];
    }

    /**
     * Vérifier les limites d'usage pour les utilisateurs free
     */
    private static function check_usage_limit($feature_name, $limit) {
        $usage_key = 'pdf_builder_usage_' . $feature_name;
        $current_usage = get_option($usage_key, 0);
        $reset_time = get_option($usage_key . '_reset', 0);

        $now = time();
        $month_start = strtotime('first day of this month');

        // Reset counter monthly
        if ($reset_time < $month_start) {
            update_option($usage_key, 0);
            update_option($usage_key . '_reset', $month_start);
            $current_usage = 0;
        }

        return $current_usage < $limit;
    }

    /**
     * Incrémenter le compteur d'usage
     */
    public static function increment_usage($feature_name) {
        if (!isset(self::$features[$feature_name])) {
            return false;
        }

        $license_manager = PDF_Builder_License_Manager::getInstance();
        if ($license_manager->is_premium()) {
            return true; // Pas de limite pour premium
        }

        $usage_key = 'pdf_builder_usage_' . $feature_name;
        $current_usage = get_option($usage_key, 0);
        update_option($usage_key, $current_usage + 1);

        return true;
    }

    /**
     * Obtenir l'usage actuel pour une fonctionnalité
     */
    public static function get_current_usage($feature_name) {
        if (!isset(self::$features[$feature_name])) {
            return 0;
        }

        return get_option('pdf_builder_usage_' . $feature_name, 0);
    }

    /**
     * Obtenir la limite pour une fonctionnalité
     */
    public static function get_feature_limit($feature_name) {
        if (!isset(self::$features[$feature_name]) || !isset(self::$features[$feature_name]['limit'])) {
            return -1; // Pas de limite
        }

        return self::$features[$feature_name]['limit'];
    }

    /**
     * Obtenir toutes les fonctionnalités disponibles
     */
    public static function get_all_features() {
        return self::$features;
    }

    /**
     * Obtenir les fonctionnalités disponibles pour l'utilisateur actuel
     */
    public static function get_available_features() {
        $license_manager = PDF_Builder_License_Manager::getInstance();
        $is_premium = $license_manager->is_premium();
        $available_features = [];

        foreach (self::$features as $key => $feature) {
            $can_use = $is_premium ? $feature['premium'] : $feature['free'];

            if ($can_use) {
                // Vérifier les limites pour free users
                if (!$is_premium && isset($feature['limit'])) {
                    $can_use = self::check_usage_limit($key, $feature['limit']);
                }
            }

            if ($can_use) {
                $available_features[$key] = $feature;
            }
        }

        return $available_features;
    }

    /**
     * Obtenir les fonctionnalités premium (pour les suggestions d'upgrade)
     */
    public static function get_premium_features() {
        $premium_features = [];

        foreach (self::$features as $key => $feature) {
            if (!$feature['free'] && $feature['premium']) {
                $premium_features[$key] = $feature;
            }
        }

        return $premium_features;
    }

    /**
     * Vérifier si une fonctionnalité est premium
     */
    public static function is_premium_feature($feature_name) {
        if (!isset(self::$features[$feature_name])) {
            return false;
        }

        return !$self::$features[$feature_name]['free'] && $self::$features[$feature_name]['premium'];
    }

    /**
     * Obtenir les détails d'une fonctionnalité
     */
    public static function get_feature_details($feature_name) {
        if (!isset(self::$features[$feature_name])) {
            return null;
        }

        $feature = self::$features[$feature_name];
        $license_manager = PDF_Builder_License_Manager::getInstance();
        $is_premium = $license_manager->is_premium();

        return [
            'name' => $feature['name'],
            'description' => $feature['description'],
            'is_premium' => self::is_premium_feature($feature_name),
            'can_use' => self::can_use_feature($feature_name),
            'current_usage' => self::get_current_usage($feature_name),
            'limit' => self::get_feature_limit($feature_name),
            'is_available' => $is_premium ? $feature['premium'] : $feature['free']
        ];
    }
}


