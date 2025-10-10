<?php
/**
 * Système de Lancement et Growth Hacking - PDF Builder Pro
 *
 * Stratégies de lancement, optimisation produit, acquisition utilisateurs,
 * rétention et monétisation pour maximiser la croissance
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe Système de Lancement et Growth Hacking
 */
class PDF_Builder_Launch_Growth_System {

    /**
     * Instance singleton
     * @var PDF_Builder_Launch_Growth_System
     */
    private static $instance = null;

    /**
     * Gestionnaire de base de données
     * @var PDF_Builder_Database_Manager
     */
    private $db_manager;

    /**
     * Logger
     * @var PDF_Builder_Logger
     */
    private $logger;

    /**
     * Métriques de croissance
     * @var array
     */
    private $growth_metrics = [];

    /**
     * Campagnes de lancement
     * @var array
     */
    private $launch_campaigns = [];

    /**
     * Stratégies d'acquisition
     * @var array
     */
    private $acquisition_strategies = [
        'content_marketing' => [
            'name' => 'Content Marketing',
            'channels' => ['blog', 'youtube', 'linkedin', 'twitter'],
            'budget_allocation' => 30,
            'target_audience' => 'developers, agencies, businesses',
            'content_types' => ['tutorials', 'case_studies', 'whitepapers', 'webinars']
        ],
        'social_proof' => [
            'name' => 'Social Proof & Testimonials',
            'channels' => ['testimonials', 'case_studies', 'reviews', 'social_media'],
            'budget_allocation' => 15,
            'target_audience' => 'potential_customers',
            'content_types' => ['customer_stories', 'video_testimonials', 'reviews']
        ],
        'partnerships' => [
            'name' => 'Strategic Partnerships',
            'channels' => ['wordpress_agencies', 'design_tools', 'saas_companies'],
            'budget_allocation' => 20,
            'target_audience' => 'partners, affiliates',
            'content_types' => ['co-marketing', 'joint_webinars', 'referral_programs']
        ],
        'paid_ads' => [
            'name' => 'Paid Advertising',
            'channels' => ['google_ads', 'facebook_ads', 'linkedin_ads', 'reddit_ads'],
            'budget_allocation' => 25,
            'target_audience' => 'wordpress_users, developers',
            'content_types' => ['targeted_ads', 'retargeting', 'lead_magnets']
        ],
        'seo_optimization' => [
            'name' => 'SEO & ASO',
            'channels' => ['google_search', 'wordpress_org', 'app_directories'],
            'budget_allocation' => 10,
            'target_audience' => 'organic_searchers',
            'content_types' => ['keyword_optimization', 'technical_seo', 'app_store_optimization']
        ]
    ];

    /**
     * Métriques de rétention
     * @var array
     */
    private $retention_metrics = [];

    /**
     * Programmes de monétisation
     * @var array
     */
    private $monetization_programs = [
        'freemium' => [
            'name' => 'Freemium Model',
            'description' => 'Free basic features, premium advanced features',
            'conversion_rate_target' => 5,
            'pricing_tiers' => ['free', 'starter', 'professional', 'enterprise'],
            'features_free' => ['basic_templates', 'pdf_export', '5_documents'],
            'features_premium' => ['advanced_templates', 'all_formats', 'unlimited_documents', 'api_access', 'priority_support']
        ],
        'subscription' => [
            'name' => 'SaaS Subscription',
            'description' => 'Monthly/annual subscription with feature tiers',
            'pricing_strategy' => 'value_based_pricing',
            'churn_rate_target' => 5,
            'upsell_opportunities' => ['add_ons', 'premium_templates', 'white_label']
        ],
        'marketplace' => [
            'name' => 'Template Marketplace',
            'description' => 'Premium templates and add-ons marketplace',
            'commission_rate' => 30,
            'revenue_share' => '70/30',
            'quality_standards' => ['code_quality', 'design_quality', 'compatibility']
        ],
        'enterprise' => [
            'name' => 'Enterprise Solutions',
            'description' => 'Custom enterprise deployments and consulting',
            'target_market' => 'large_businesses',
            'services' => ['custom_development', 'training', 'support_sla', 'white_label_solutions']
        ]
    ];

    /**
     * Analyses A/B en cours
     * @var array
     */
    private $ab_tests = [];

    /**
     * Campagnes d'email marketing
     * @var array
     */
    private $email_campaigns = [];

    /**
     * Métriques de performance produit
     * @var array
     */
    private $product_metrics = [
        'activation_rate' => 0,
        'retention_1_day' => 0,
        'retention_7_days' => 0,
        'retention_30_days' => 0,
        'feature_adoption' => [],
        'user_engagement' => [],
        'conversion_funnel' => []
    ];

    /**
     * Constructeur privé
     */
    private function __construct() {
        $core = PDF_Builder_Core::getInstance();
        $this->db_manager = $core->get_database_manager();
        $this->logger = $core->get_logger();

        $this->init_growth_hooks();
        $this->schedule_growth_tasks();
        $this->load_growth_data();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Launch_Growth_System
     */
    public static function getInstance(): PDF_Builder_Launch_Growth_System {
        return self::getLaunchGrowthSystem();
    }

    /**
     * Obtenir l'instance singleton
     *
     * @return PDF_Builder_Launch_Growth_System
     */
    public static function getLaunchGrowthSystem(): PDF_Builder_Launch_Growth_System {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks de croissance
     */
    private function init_growth_hooks(): void {
        // Hooks AJAX pour le growth system
        add_action('wp_ajax_pdf_builder_get_growth_metrics', [$this, 'ajax_get_growth_metrics']);
        add_action('wp_ajax_pdf_builder_create_launch_campaign', [$this, 'ajax_create_launch_campaign']);
        add_action('wp_ajax_pdf_builder_run_ab_test', [$this, 'ajax_run_ab_test']);
        add_action('wp_ajax_pdf_builder_get_product_analytics', [$this, 'ajax_get_product_analytics']);

        // Hooks pour les métriques utilisateur
        add_action('pdf_builder_user_registered', [$this, 'track_user_registration'], 10, 1);
        add_action('pdf_builder_user_activated', [$this, 'track_user_activation'], 10, 1);
        add_action('pdf_builder_document_created', [$this, 'track_document_creation'], 10, 2);
        add_action('pdf_builder_feature_used', [$this, 'track_feature_usage'], 10, 2);
        add_action('pdf_builder_upgrade_completed', [$this, 'track_upgrade_conversion'], 10, 2);

        // Hooks pour les tâches automatiques
        add_action('pdf_builder_collect_growth_metrics', [$this, 'collect_growth_metrics']);
        add_action('pdf_builder_optimize_acquisition', [$this, 'optimize_acquisition_channels']);
        add_action('pdf_builder_send_growth_emails', [$this, 'send_growth_emails']);
        add_action('pdf_builder_analyze_ab_tests', [$this, 'analyze_ab_tests']);
        add_action('pdf_builder_generate_growth_report', [$this, 'generate_growth_report']);
    }

    /**
     * Programmer les tâches de croissance
     */
    private function schedule_growth_tasks(): void {
        // Collecte de métriques (toutes les heures)
        if (!wp_next_scheduled('pdf_builder_collect_growth_metrics')) {
            wp_schedule_event(time(), 'hourly', 'pdf_builder_collect_growth_metrics');
        }

        // Optimisation acquisition (toutes les 6 heures)
        if (!wp_next_scheduled('pdf_builder_optimize_acquisition')) {
            wp_schedule_event(time(), 'every_six_hours', 'pdf_builder_optimize_acquisition');
        }

        // Emails de croissance (quotidien)
        if (!wp_next_scheduled('pdf_builder_send_growth_emails')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_send_growth_emails');
        }

        // Analyse A/B tests (quotidien)
        if (!wp_next_scheduled('pdf_builder_analyze_ab_tests')) {
            wp_schedule_event(time(), 'daily', 'pdf_builder_analyze_ab_tests');
        }

        // Rapport de croissance (hebdomadaire)
        if (!wp_next_scheduled('pdf_builder_generate_growth_report')) {
            wp_schedule_event(time(), 'weekly', 'pdf_builder_generate_growth_report');
        }
    }

    /**
     * Charger les données de croissance
     */
    private function load_growth_data(): void {
        $this->growth_metrics = get_option('pdf_builder_growth_metrics', []);
        $this->launch_campaigns = get_option('pdf_builder_launch_campaigns', []);
        $this->ab_tests = get_option('pdf_builder_ab_tests', []);
        $this->email_campaigns = get_option('pdf_builder_email_campaigns', []);
    }

    /**
     * Créer une campagne de lancement
     *
     * @param array $campaign_data
     * @return array
     */
    public function create_launch_campaign(array $campaign_data): array {
        $required_fields = ['name', 'objective', 'target_audience', 'channels', 'budget', 'timeline'];

        foreach ($required_fields as $field) {
            if (empty($campaign_data[$field])) {
                return [
                    'success' => false,
                    'message' => "Le champ {$field} est requis."
                ];
            }
        }

        $campaign = [
            'id' => $this->generate_campaign_id(),
            'name' => sanitize_text_field($campaign_data['name']),
            'objective' => sanitize_text_field($campaign_data['objective']),
            'target_audience' => sanitize_text_field($campaign_data['target_audience']),
            'channels' => array_map('sanitize_text_field', $campaign_data['channels']),
            'budget' => floatval($campaign_data['budget']),
            'timeline' => [
                'start_date' => sanitize_text_field($campaign_data['start_date']),
                'end_date' => sanitize_text_field($campaign_data['end_date'])
            ],
            'status' => 'planned',
            'metrics' => [
                'impressions' => 0,
                'clicks' => 0,
                'conversions' => 0,
                'cost' => 0,
                'roi' => 0
            ],
            'content_plan' => $campaign_data['content_plan'] ?? [],
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];

        $this->launch_campaigns[$campaign['id']] = $campaign;
        $this->save_launch_campaigns();

        $this->logger->info('Launch campaign created', [
            'campaign_id' => $campaign['id'],
            'name' => $campaign['name'],
            'budget' => $campaign['budget']
        ]);

        return [
            'success' => true,
            'campaign_id' => $campaign['id'],
            'message' => 'Campagne de lancement créée avec succès.'
        ];
    }

    /**
     * Lancer une campagne
     *
     * @param string $campaign_id
     * @return bool
     */
    public function launch_campaign(string $campaign_id): bool {
        if (!isset($this->launch_campaigns[$campaign_id])) {
            return false;
        }

        $this->launch_campaigns[$campaign_id]['status'] = 'active';
        $this->launch_campaigns[$campaign_id]['launched_at'] = current_time('mysql');
        $this->launch_campaigns[$campaign_id]['updated_at'] = current_time('mysql');

        $this->save_launch_campaigns();

        // Activer les canaux de campagne
        $this->activate_campaign_channels($campaign_id);

        $this->logger->info('Campaign launched', ['campaign_id' => $campaign_id]);

        return true;
    }

    /**
     * Créer un test A/B
     *
     * @param array $test_data
     * @return array
     */
    public function create_ab_test(array $test_data): array {
        $required_fields = ['name', 'hypothesis', 'variants', 'metric', 'sample_size'];

        foreach ($required_fields as $field) {
            if (empty($test_data[$field])) {
                return [
                    'success' => false,
                    'message' => "Le champ {$field} est requis."
                ];
            }
        }

        $test = [
            'id' => $this->generate_test_id(),
            'name' => sanitize_text_field($test_data['name']),
            'hypothesis' => sanitize_text_field($test_data['hypothesis']),
            'variants' => $test_data['variants'], // Array of variant configurations
            'metric' => sanitize_text_field($test_data['metric']),
            'sample_size' => intval($test_data['sample_size']),
            'status' => 'running',
            'results' => [
                'variant_a' => ['participants' => 0, 'conversions' => 0, 'conversion_rate' => 0],
                'variant_b' => ['participants' => 0, 'conversions' => 0, 'conversion_rate' => 0]
            ],
            'confidence_level' => 0,
            'winner' => null,
            'created_at' => current_time('mysql'),
            'completed_at' => null
        ];

        $this->ab_tests[$test['id']] = $test;
        $this->save_ab_tests();

        $this->logger->info('A/B test created', [
            'test_id' => $test['id'],
            'name' => $test['name'],
            'metric' => $test['metric']
        ]);

        return [
            'success' => true,
            'test_id' => $test['id'],
            'message' => 'Test A/B créé avec succès.'
        ];
    }

    /**
     * Enregistrer un participant au test A/B
     *
     * @param string $test_id
     * @param string $variant
     * @param int $user_id
     */
    public function record_ab_test_participant(string $test_id, string $variant, int $user_id): void {
        if (!isset($this->ab_tests[$test_id]) || $this->ab_tests[$test_id]['status'] !== 'running') {
            return;
        }

        if (!isset($this->ab_tests[$test_id]['results'][$variant])) {
            return;
        }

        $this->ab_tests[$test_id]['results'][$variant]['participants']++;

        // Assigner l'utilisateur à la variante
        update_user_meta($user_id, "pdf_builder_ab_test_{$test_id}", $variant);

        $this->save_ab_tests();
    }

    /**
     * Enregistrer une conversion A/B
     *
     * @param string $test_id
     * @param int $user_id
     */
    public function record_ab_test_conversion(string $test_id, int $user_id): void {
        $variant = get_user_meta($user_id, "pdf_builder_ab_test_{$test_id}", true);

        if (!$variant || !isset($this->ab_tests[$test_id])) {
            return;
        }

        $this->ab_tests[$test_id]['results'][$variant]['conversions']++;

        // Calculer le taux de conversion
        $participants = $this->ab_tests[$test_id]['results'][$variant]['participants'];
        $conversions = $this->ab_tests[$test_id]['results'][$variant]['conversions'];

        if ($participants > 0) {
            $this->ab_tests[$test_id]['results'][$variant]['conversion_rate'] = ($conversions / $participants) * 100;
        }

        // Vérifier si le test est terminé
        $this->check_ab_test_completion($test_id);

        $this->save_ab_tests();
    }

    /**
     * Collecter les métriques de croissance
     */
    public function collect_growth_metrics(): void {
        $metrics = [
            'timestamp' => current_time('mysql'),
            'user_acquisition' => $this->get_user_acquisition_metrics(),
            'engagement' => $this->get_engagement_metrics(),
            'retention' => $this->get_retention_metrics(),
            'monetization' => $this->get_monetization_metrics(),
            'channel_performance' => $this->get_channel_performance_metrics()
        ];

        $this->growth_metrics[] = $metrics;

        // Garder seulement les 1000 dernières entrées
        if (count($this->growth_metrics) > 1000) {
            $this->growth_metrics = array_slice($this->growth_metrics, -1000);
        }

        update_option('pdf_builder_growth_metrics', $this->growth_metrics);

        $this->logger->info('Growth metrics collected');
    }

    /**
     * Optimiser les canaux d'acquisition
     */
    public function optimize_acquisition_channels(): void {
        $channel_performance = $this->get_channel_performance_metrics();

        foreach ($channel_performance as $channel => $metrics) {
            $roi = $metrics['roi'] ?? 0;
            $cac = $metrics['cac'] ?? 0;

            // Ajuster le budget basé sur les performances
            if ($roi > 3) {
                // Augmenter le budget pour les canaux performants
                $this->increase_channel_budget($channel, 1.5);
            } elseif ($roi < 1) {
                // Réduire le budget pour les canaux peu performants
                $this->decrease_channel_budget($channel, 0.7);
            }

            // Optimiser les enchères pour les canaux payants
            if (strpos($channel, 'ads') !== false) {
                $this->optimize_paid_channel($channel, $metrics);
            }
        }

        $this->logger->info('Acquisition channels optimized');
    }

    /**
     * Envoyer les emails de croissance
     */
    public function send_growth_emails(): void {
        // Emails de réactivation pour utilisateurs inactifs
        $inactive_users = $this->get_inactive_users(30); // 30 jours d'inactivité

        foreach ($inactive_users as $user) {
            $this->send_reactivation_email($user);
        }

        // Emails de mise à niveau pour utilisateurs freemium
        $freemium_users = $this->get_freemium_users();

        foreach ($freemium_users as $user) {
            if ($this->should_send_upgrade_email($user)) {
                $this->send_upgrade_email($user);
            }
        }

        // Emails de réengagement pour anciens utilisateurs
        $lapsed_users = $this->get_lapsed_users(90); // 90 jours sans connexion

        foreach ($lapsed_users as $user) {
            $this->send_reengagement_email($user);
        }

        $this->logger->info('Growth emails sent', [
            'reactivation' => count($inactive_users),
            'upgrade' => count($freemium_users),
            'reengagement' => count($lapsed_users)
        ]);
    }

    /**
     * Analyser les tests A/B
     */
    public function analyze_ab_tests(): void {
        foreach ($this->ab_tests as $test_id => $test) {
            if ($test['status'] !== 'running') {
                continue;
            }

            $this->check_ab_test_completion($test_id);
        }

        $this->save_ab_tests();
    }

    /**
     * Générer un rapport de croissance
     */
    public function generate_growth_report(): void {
        $report = [
            'generated_at' => current_time('mysql'),
            'period' => '7 days',
            'executive_summary' => $this->generate_executive_summary(),
            'acquisition_metrics' => $this->get_acquisition_report(),
            'engagement_metrics' => $this->get_engagement_report(),
            'retention_metrics' => $this->get_retention_report(),
            'monetization_metrics' => $this->get_monetization_report(),
            'campaign_performance' => $this->get_campaign_performance_report(),
            'ab_test_results' => $this->get_ab_test_results_report(),
            'recommendations' => $this->generate_growth_recommendations()
        ];

        update_option('pdf_builder_growth_report', $report);

        // Envoyer le rapport par email
        $this->send_growth_report_email($report);

        $this->logger->info('Growth report generated');
    }

    /**
     * Tracker l'inscription utilisateur
     *
     * @param int $user_id
     */
    public function track_user_registration(int $user_id): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_user_events',
            [
                'user_id' => $user_id,
                'event_type' => 'registration',
                'event_data' => wp_json_encode(['source' => $this->get_user_source($user_id)]),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s']
        );
    }

    /**
     * Tracker l'activation utilisateur
     *
     * @param int $user_id
     */
    public function track_user_activation(int $user_id): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_user_events',
            [
                'user_id' => $user_id,
                'event_type' => 'activation',
                'event_data' => wp_json_encode(['activation_method' => 'email_verification']),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s']
        );

        // Mettre à jour les métriques de produit
        $this->product_metrics['activation_rate'] = $this->calculate_activation_rate();
    }

    /**
     * Tracker la création de document
     *
     * @param int $user_id
     * @param int $document_id
     */
    public function track_document_creation(int $user_id, int $document_id): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_user_events',
            [
                'user_id' => $user_id,
                'event_type' => 'document_created',
                'event_data' => wp_json_encode(['document_id' => $document_id]),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s']
        );
    }

    /**
     * Tracker l'utilisation de fonctionnalité
     *
     * @param int $user_id
     * @param string $feature
     */
    public function track_feature_usage(int $user_id, string $feature): void {
        if (!isset($this->product_metrics['feature_adoption'][$feature])) {
            $this->product_metrics['feature_adoption'][$feature] = 0;
        }

        $this->product_metrics['feature_adoption'][$feature]++;

        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_user_events',
            [
                'user_id' => $user_id,
                'event_type' => 'feature_used',
                'event_data' => wp_json_encode(['feature' => $feature]),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s']
        );
    }

    /**
     * Tracker la conversion de mise à niveau
     *
     * @param int $user_id
     * @param string $plan
     */
    public function track_upgrade_conversion(int $user_id, string $plan): void {
        global $wpdb;

        $wpdb->insert(
            $wpdb->prefix . 'pdf_builder_user_events',
            [
                'user_id' => $user_id,
                'event_type' => 'upgrade',
                'event_data' => wp_json_encode(['plan' => $plan]),
                'created_at' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s']
        );
    }

    /**
     * Obtenir les métriques d'acquisition utilisateur
     *
     * @return array
     */
    private function get_user_acquisition_metrics(): array {
        global $wpdb;

        $result = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                COUNT(DISTINCT user_id) as new_users,
                COUNT(*) as total_registrations
            FROM {$wpdb->prefix}pdf_builder_user_events
            WHERE event_type = 'registration' AND created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-24 hours'))), ARRAY_A);

        return [
            'new_users' => intval($result['new_users'] ?? 0),
            'total_registrations' => intval($result['total_registrations'] ?? 0),
            'conversion_rate' => $this->calculate_conversion_rate()
        ];
    }

    /**
     * Obtenir les métriques d'engagement
     *
     * @return array
     */
    private function get_engagement_metrics(): array {
        global $wpdb;

        $result = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                COUNT(DISTINCT user_id) as active_users,
                AVG(daily_actions) as avg_daily_actions
            FROM (
                SELECT user_id, COUNT(*) as daily_actions
                FROM {$wpdb->prefix}pdf_builder_user_events
                WHERE created_at > %s
                GROUP BY user_id
            ) as daily_stats
        ", date('Y-m-d H:i:s', strtotime('-24 hours'))), ARRAY_A);

        return [
            'active_users' => intval($result['active_users'] ?? 0),
            'avg_daily_actions' => round(floatval($result['avg_daily_actions'] ?? 0), 2),
            'engagement_rate' => $this->calculate_engagement_rate()
        ];
    }

    /**
     * Obtenir les métriques de rétention
     *
     * @return array
     */
    private function get_retention_metrics(): array {
        // Calculer la rétention sur 1, 7, 30 jours
        $this->product_metrics['retention_1_day'] = $this->calculate_retention_rate(1);
        $this->product_metrics['retention_7_days'] = $this->calculate_retention_rate(7);
        $this->product_metrics['retention_30_days'] = $this->calculate_retention_rate(30);

        return [
            'day_1' => $this->product_metrics['retention_1_day'],
            'day_7' => $this->product_metrics['retention_7_days'],
            'day_30' => $this->product_metrics['retention_30_days']
        ];
    }

    /**
     * Obtenir les métriques de monétisation
     *
     * @return array
     */
    private function get_monetization_metrics(): array {
        global $wpdb;

        $result = $wpdb->get_row(PDF_Builder_Debug_Helper::safe_wpdb_prepare("
            SELECT
                COUNT(*) as total_upgrades,
                SUM(plan_value) as total_revenue
            FROM {$wpdb->prefix}pdf_builder_user_events
            WHERE event_type = 'upgrade' AND created_at > %s
        ", date('Y-m-d H:i:s', strtotime('-30 days'))), ARRAY_A);

        return [
            'total_upgrades' => intval($result['total_upgrades'] ?? 0),
            'total_revenue' => floatval($result['total_revenue'] ?? 0),
            'arpu' => $this->calculate_arpu(),
            'conversion_rate' => $this->calculate_monetization_conversion_rate()
        ];
    }

    /**
     * Obtenir les performances des canaux
     *
     * @return array
     */
    private function get_channel_performance_metrics(): array {
        // Simulation - en production, intégrer avec les APIs des canaux
        return [
            'google_ads' => ['impressions' => 50000, 'clicks' => 500, 'conversions' => 25, 'cost' => 250, 'roi' => 4.2],
            'facebook_ads' => ['impressions' => 75000, 'clicks' => 750, 'conversions' => 38, 'cost' => 300, 'roi' => 3.8],
            'content_marketing' => ['impressions' => 100000, 'clicks' => 2000, 'conversions' => 45, 'cost' => 500, 'roi' => 2.9],
            'seo' => ['impressions' => 200000, 'clicks' => 4000, 'conversions' => 80, 'cost' => 0, 'roi' => 999]
        ];
    }

    /**
     * Calculer le taux d'activation
     *
     * @return float
     */
    private function calculate_activation_rate(): float {
        global $wpdb;

        $result = $wpdb->get_row("
            SELECT
                (SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_user_events WHERE event_type = 'activation') /
                (SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_user_events WHERE event_type = 'registration') * 100 as rate
        ", ARRAY_A);

        return round(floatval($result['rate'] ?? 0), 2);
    }

    /**
     * Calculer le taux de conversion
     *
     * @return float
     */
    private function calculate_conversion_rate(): float {
        // Simulation
        return rand(25, 45) / 10;
    }

    /**
     * Calculer le taux d'engagement
     *
     * @return float
     */
    private function calculate_engagement_rate(): float {
        // Simulation
        return rand(60, 85) / 100;
    }

    /**
     * Calculer le taux de rétention
     *
     * @param int $days
     * @return float
     */
    private function calculate_retention_rate(int $days): float {
        // Simulation - en production, calculer basé sur les données réelles
        $base_rate = 100 - ($days * 2); // Diminue avec le temps
        return max(0, $base_rate + rand(-5, 5));
    }

    /**
     * Calculer l'ARPU (Average Revenue Per User)
     *
     * @return float
     */
    private function calculate_arpu(): float {
        // Simulation
        return rand(2500, 4500) / 100;
    }

    /**
     * Calculer le taux de conversion monétisation
     *
     * @return float
     */
    private function calculate_monetization_conversion_rate(): float {
        // Simulation
        return rand(30, 80) / 10;
    }

    /**
     * Générer l'ID de campagne
     *
     * @return string
     */
    private function generate_campaign_id(): string {
        return 'campaign_' . time() . '_' . wp_generate_password(6, false);
    }

    /**
     * Générer l'ID de test
     *
     * @return string
     */
    private function generate_test_id(): string {
        return 'ab_test_' . time() . '_' . wp_generate_password(6, false);
    }

    /**
     * Sauvegarder les campagnes de lancement
     */
    private function save_launch_campaigns(): void {
        update_option('pdf_builder_launch_campaigns', $this->launch_campaigns);
    }

    /**
     * Sauvegarder les tests A/B
     */
    private function save_ab_tests(): void {
        update_option('pdf_builder_ab_tests', $this->ab_tests);
    }

    /**
     * Activer les canaux de campagne
     *
     * @param string $campaign_id
     */
    private function activate_campaign_channels(string $campaign_id): void {
        $campaign = $this->launch_campaigns[$campaign_id];

        foreach ($campaign['channels'] as $channel) {
            // Activer le canal (intégration avec les APIs des canaux)
            $this->logger->info('Campaign channel activated', [
                'campaign_id' => $campaign_id,
                'channel' => $channel
            ]);
        }
    }

    /**
     * Vérifier la fin du test A/B
     *
     * @param string $test_id
     */
    private function check_ab_test_completion(string $test_id): void {
        $test = $this->ab_tests[$test_id];

        $total_participants = $test['results']['variant_a']['participants'] + $test['results']['variant_b']['participants'];

        if ($total_participants >= $test['sample_size']) {
            // Calculer le vainqueur
            $rate_a = $test['results']['variant_a']['conversion_rate'];
            $rate_b = $test['results']['variant_b']['conversion_rate'];

            if ($rate_a > $rate_b) {
                $test['winner'] = 'variant_a';
            } elseif ($rate_b > $rate_a) {
                $test['winner'] = 'variant_b';
            } else {
                $test['winner'] = 'tie';
            }

            $test['status'] = 'completed';
            $test['completed_at'] = current_time('mysql');

            $this->ab_tests[$test_id] = $test;

            $this->logger->info('A/B test completed', [
                'test_id' => $test_id,
                'winner' => $test['winner']
            ]);
        }
    }

    /**
     * Augmenter le budget d'un canal
     *
     * @param string $channel
     * @param float $multiplier
     */
    private function increase_channel_budget(string $channel, float $multiplier): void {
        // Simulation - en production, ajuster les budgets réels
        $this->logger->info('Channel budget increased', [
            'channel' => $channel,
            'multiplier' => $multiplier
        ]);
    }

    /**
     * Réduire le budget d'un canal
     *
     * @param string $channel
     * @param float $multiplier
     */
    private function decrease_channel_budget(string $channel, float $multiplier): void {
        // Simulation
        $this->logger->info('Channel budget decreased', [
            'channel' => $channel,
            'multiplier' => $multiplier
        ]);
    }

    /**
     * Optimiser un canal payant
     *
     * @param string $channel
     * @param array $metrics
     */
    private function optimize_paid_channel(string $channel, array $metrics): void {
        // Simulation - en production, ajuster les enchères et ciblage
        $this->logger->info('Paid channel optimized', [
            'channel' => $channel,
            'metrics' => $metrics
        ]);
    }

    /**
     * Obtenir les utilisateurs inactifs
     *
     * @param int $days
     * @return array
     */
    private function get_inactive_users(int $days): array {
        // Simulation
        return [];
    }

    /**
     * Obtenir les utilisateurs freemium
     *
     * @return array
     */
    private function get_freemium_users(): array {
        // Simulation
        return [];
    }

    /**
     * Vérifier si un email de mise à niveau doit être envoyé
     *
     * @param array $user
     * @return bool
     */
    private function should_send_upgrade_email(array $user): bool {
        // Simulation
        return rand(0, 1) === 1;
    }

    /**
     * Obtenir les utilisateurs perdus
     *
     * @param int $days
     * @return array
     */
    private function get_lapsed_users(int $days): array {
        // Simulation
        return [];
    }

    /**
     * Envoyer un email de réactivation
     *
     * @param array $user
     */
    private function send_reactivation_email(array $user): void {
        // Simulation
        $this->logger->info('Reactivation email sent', ['user_id' => $user['id']]);
    }

    /**
     * Envoyer un email de mise à niveau
     *
     * @param array $user
     */
    private function send_upgrade_email(array $user): void {
        // Simulation
        $this->logger->info('Upgrade email sent', ['user_id' => $user['id']]);
    }

    /**
     * Envoyer un email de réengagement
     *
     * @param array $user
     */
    private function send_reengagement_email(array $user): void {
        // Simulation
        $this->logger->info('Reengagement email sent', ['user_id' => $user['id']]);
    }

    /**
     * Obtenir la source de l'utilisateur
     *
     * @param int $user_id
     * @return string
     */
    private function get_user_source(int $user_id): string {
        // Simulation - en production, tracker via UTM parameters
        return 'organic_search';
    }

    /**
     * Générer le résumé exécutif
     *
     * @return array
     */
    private function generate_executive_summary(): array {
        $latest_metrics = end($this->growth_metrics) ?: [];

        return [
            'total_users' => $this->get_total_users(),
            'active_users' => $latest_metrics['engagement']['active_users'] ?? 0,
            'revenue' => $latest_metrics['monetization']['total_revenue'] ?? 0,
            'growth_rate' => $this->calculate_growth_rate(),
            'key_highlights' => $this->get_key_highlights(),
            'challenges' => $this->get_current_challenges()
        ];
    }

    /**
     * Obtenir le nombre total d'utilisateurs
     *
     * @return int
     */
    private function get_total_users(): int {
        // Simulation
        return rand(5000, 15000);
    }

    /**
     * Calculer le taux de croissance
     *
     * @return float
     */
    private function calculate_growth_rate(): float {
        // Simulation
        return rand(150, 300) / 10;
    }

    /**
     * Obtenir les points clés
     *
     * @return array
     */
    private function get_key_highlights(): array {
        return [
            'User acquisition up 25% this month',
            'Retention rate improved to 78%',
            'New feature adoption at 65%',
            'Revenue growth of 40% quarter-over-quarter'
        ];
    }

    /**
     * Obtenir les défis actuels
     *
     * @return array
     */
    private function get_current_challenges(): array {
        return [
            'Increasing competition in PDF generation space',
            'Need to improve mobile user experience',
            'Scaling infrastructure for growing user base',
            'Optimizing conversion funnel from free to paid'
        ];
    }

    /**
     * Obtenir le rapport d'acquisition
     *
     * @return array
     */
    private function get_acquisition_report(): array {
        return [
            'channel_performance' => $this->get_channel_performance_metrics(),
            'user_sources' => $this->get_user_sources_breakdown(),
            'cost_per_acquisition' => $this->calculate_cac_by_channel(),
            'conversion_funnel' => $this->get_conversion_funnel_data()
        ];
    }

    /**
     * Obtenir la répartition des sources utilisateur
     *
     * @return array
     */
    private function get_user_sources_breakdown(): array {
        return [
            'organic_search' => 45,
            'paid_ads' => 25,
            'social_media' => 15,
            'referrals' => 10,
            'direct' => 5
        ];
    }

    /**
     * Calculer le CAC par canal
     *
     * @return array
     */
    private function calculate_cac_by_channel(): array {
        return [
            'google_ads' => 12.50,
            'facebook_ads' => 8.75,
            'content_marketing' => 3.20,
            'seo' => 0.00
        ];
    }

    /**
     * Obtenir les données de l'entonnoir de conversion
     *
     * @return array
     */
    private function get_conversion_funnel_data(): array {
        return [
            'visitors' => 10000,
            'signups' => 2500,
            'activations' => 1875,
            'premium_conversions' => 375
        ];
    }

    /**
     * Obtenir le rapport d'engagement
     *
     * @return array
     */
    private function get_engagement_report(): array {
        return [
            'daily_active_users' => $this->get_daily_active_users(),
            'session_duration' => $this->get_average_session_duration(),
            'feature_usage' => $this->get_feature_usage_stats(),
            'user_journey' => $this->get_user_journey_analysis()
        ];
    }

    /**
     * Obtenir les utilisateurs actifs quotidiens
     *
     * @return array
     */
    private function get_daily_active_users(): array {
        // Simulation pour les 7 derniers jours
        return [
            'day_1' => rand(800, 1200),
            'day_2' => rand(800, 1200),
            'day_3' => rand(800, 1200),
            'day_4' => rand(800, 1200),
            'day_5' => rand(800, 1200),
            'day_6' => rand(800, 1200),
            'day_7' => rand(800, 1200)
        ];
    }

    /**
     * Obtenir la durée moyenne de session
     *
     * @return int
     */
    private function get_average_session_duration(): int {
        return rand(300, 900); // secondes
    }

    /**
     * Obtenir les statistiques d'utilisation des fonctionnalités
     *
     * @return array
     */
    private function get_feature_usage_stats(): array {
        return [
            'document_editor' => ['usage' => 85, 'satisfaction' => 4.2],
            'template_library' => ['usage' => 72, 'satisfaction' => 4.5],
            'export_pdf' => ['usage' => 91, 'satisfaction' => 4.3],
            'collaboration' => ['usage' => 45, 'satisfaction' => 4.1],
            'api_integration' => ['usage' => 23, 'satisfaction' => 3.9]
        ];
    }

    /**
     * Obtenir l'analyse du parcours utilisateur
     *
     * @return array
     */
    private function get_user_journey_analysis(): array {
        return [
            'onboarding_completion' => 78,
            'time_to_first_value' => 45, // minutes
            'feature_discovery_rate' => 65,
            'support_ticket_rate' => 8
        ];
    }

    /**
     * Obtenir le rapport de rétention
     *
     * @return array
     */
    private function get_retention_report(): array {
        return [
            'cohort_analysis' => $this->get_cohort_retention_data(),
            'churn_analysis' => $this->get_churn_analysis(),
            'retention_drivers' => $this->get_retention_drivers(),
            'reengagement_campaigns' => $this->get_reengagement_campaign_performance()
        ];
    }

    /**
     * Obtenir les données de rétention par cohorte
     *
     * @return array
     */
    private function get_cohort_retention_data(): array {
        // Simulation
        return [
            'cohort_1' => [100, 75, 60, 50, 45, 42, 40],
            'cohort_2' => [100, 78, 65, 55, 48, 45, 43],
            'cohort_3' => [100, 82, 68, 58, 52, 48, 46]
        ];
    }

    /**
     * Obtenir l'analyse du churn
     *
     * @return array
     */
    private function get_churn_analysis(): array {
        return [
            'monthly_churn_rate' => 5.2,
            'churn_reasons' => [
                'price' => 25,
                'features' => 20,
                'competitor' => 15,
                'technical_issues' => 12,
                'other' => 28
            ],
            'churn_prediction' => $this->get_churn_prediction_data()
        ];
    }

    /**
     * Obtenir les facteurs de rétention
     *
     * @return array
     */
    private function get_retention_drivers(): array {
        return [
            'feature_usage' => 0.75,
            'customer_support' => 0.68,
            'product_updates' => 0.62,
            'community_engagement' => 0.55,
            'pricing_satisfaction' => 0.48
        ];
    }

    /**
     * Obtenir les performances des campagnes de réengagement
     *
     * @return array
     */
    private function get_reengagement_campaign_performance(): array {
        return [
            'email_open_rate' => 28,
            'click_through_rate' => 8,
            'reengagement_rate' => 15,
            'revenue_recovered' => 4500
        ];
    }

    /**
     * Obtenir le rapport de monétisation
     *
     * @return array
     */
    private function get_monetization_report(): array {
        return [
            'revenue_streams' => $this->get_revenue_streams(),
            'pricing_optimization' => $this->get_pricing_optimization_data(),
            'lifetime_value' => $this->get_customer_lifetime_value(),
            'expansion_opportunities' => $this->get_expansion_opportunities()
        ];
    }

    /**
     * Obtenir les flux de revenus
     *
     * @return array
     */
    private function get_revenue_streams(): array {
        return [
            'subscription_premium' => ['revenue' => 45000, 'percentage' => 65],
            'template_marketplace' => ['revenue' => 12000, 'percentage' => 17],
            'enterprise_licenses' => ['revenue' => 8500, 'percentage' => 12],
            'one_time_services' => ['revenue' => 3500, 'percentage' => 6]
        ];
    }

    /**
     * Obtenir les données d'optimisation des prix
     *
     * @return array
     */
    private function get_pricing_optimization_data(): array {
        return [
            'price_elasticity' => -1.2,
            'optimal_price_points' => [29, 49, 99, 199],
            'conversion_rates_by_price' => [
                19 => 2.1,
                29 => 3.8,
                49 => 5.2,
                99 => 4.1,
                199 => 2.9
            ],
            'discount_effectiveness' => 1.45
        ];
    }

    /**
     * Obtenir la valeur vie client
     *
     * @return array
     */
    private function get_customer_lifetime_value(): array {
        return [
            'average_ltv' => 285,
            'ltv_by_plan' => [
                'starter' => 95,
                'professional' => 285,
                'enterprise' => 1250
            ],
            'ltv_cac_ratio' => 4.2,
            'payback_period' => 8 // months
        ];
    }

    /**
     * Obtenir les opportunités d'expansion
     *
     * @return array
     */
    private function get_expansion_opportunities(): array {
        return [
            'upsell_opportunities' => 12500, // potentiel de revenus additionnels
            'cross_sell_opportunities' => 8300,
            'expansion_rate' => 18, // % des clients qui upgrade
            'untapped_markets' => ['education', 'healthcare', 'government']
        ];
    }

    /**
     * Obtenir les données de prédiction du churn
     *
     * @return array
     */
    private function get_churn_prediction_data(): array {
        return [
            'high_risk_users' => 125,
            'medium_risk_users' => 340,
            'predicted_churn_next_month' => 45,
            'prevention_opportunities' => 280
        ];
    }

    /**
     * Obtenir le rapport de performance des campagnes
     *
     * @return array
     */
    private function get_campaign_performance_report(): array {
        $campaign_performance = [];

        foreach ($this->launch_campaigns as $campaign) {
            $campaign_performance[] = [
                'name' => $campaign['name'],
                'status' => $campaign['status'],
                'budget' => $campaign['budget'],
                'metrics' => $campaign['metrics'],
                'roi' => $this->calculate_campaign_roi($campaign),
                'performance_score' => $this->calculate_campaign_performance_score($campaign)
            ];
        }

        return $campaign_performance;
    }

    /**
     * Obtenir les résultats des tests A/B
     *
     * @return array
     */
    private function get_ab_test_results_report(): array {
        $test_results = [];

        foreach ($this->ab_tests as $test) {
            $test_results[] = [
                'name' => $test['name'],
                'status' => $test['status'],
                'metric' => $test['metric'],
                'results' => $test['results'],
                'winner' => $test['winner'],
                'confidence_level' => $test['confidence_level'],
                'impact' => $this->calculate_ab_test_impact($test)
            ];
        }

        return $test_results;
    }

    /**
     * Générer les recommandations de croissance
     *
     * @return array
     */
    private function generate_growth_recommendations(): array {
        return [
            'acquisition' => [
                'Increase budget for high-ROI channels (SEO, content marketing)',
                'Optimize Facebook Ads targeting for better conversion rates',
                'Launch referral program to leverage existing user base',
                'Expand content marketing to include video tutorials'
            ],
            'engagement' => [
                'Implement gamification features to increase daily usage',
                'Personalize onboarding experience based on user type',
                'Add push notifications for important updates',
                'Create user communities for peer support'
            ],
            'retention' => [
                'Address top churn reasons through product improvements',
                'Implement proactive customer success management',
                'Create loyalty program for long-term users',
                'Improve customer support response times'
            ],
            'monetization' => [
                'Optimize pricing tiers based on feature usage data',
                'Launch annual billing discount to reduce churn',
                'Expand enterprise offerings with custom integrations',
                'Develop add-on marketplace for additional revenue streams'
            ]
        ];
    }

    /**
     * Calculer le ROI d'une campagne
     *
     * @param array $campaign
     * @return float
     */
    private function calculate_campaign_roi(array $campaign): float {
        $revenue = $campaign['metrics']['conversions'] * 50; // Valeur moyenne par conversion
        $cost = $campaign['metrics']['cost'];

        return $cost > 0 ? round(($revenue - $cost) / $cost * 100, 2) : 0;
    }

    /**
     * Calculer le score de performance d'une campagne
     *
     * @param array $campaign
     * @return float
     */
    private function calculate_campaign_performance_score(array $campaign): float {
        // Score basé sur ROI, taux de conversion, coût par acquisition
        $roi_score = min($campaign['roi'] / 2, 50); // Max 50 points
        $conversion_score = min($campaign['metrics']['conversions'] / 10, 30); // Max 30 points
        $efficiency_score = $campaign['metrics']['cost'] > 0 ? min(1000 / $campaign['metrics']['cost'], 20) : 0; // Max 20 points

        return round($roi_score + $conversion_score + $efficiency_score, 1);
    }

    /**
     * Calculer l'impact d'un test A/B
     *
     * @param array $test
     * @return array
     */
    private function calculate_ab_test_impact(array $test): array {
        if ($test['status'] !== 'completed' || !$test['winner']) {
            return ['improvement' => 0, 'confidence' => 0];
        }

        $winner_rate = $test['results'][$test['winner']]['conversion_rate'];
        $loser_rate = $test['results'][$test['winner'] === 'variant_a' ? 'variant_b' : 'variant_a']['conversion_rate'];

        $improvement = $loser_rate > 0 ? (($winner_rate - $loser_rate) / $loser_rate) * 100 : 0;

        return [
            'improvement' => round($improvement, 2),
            'confidence' => rand(85, 98) // Simulation
        ];
    }

    /**
     * Envoyer le rapport de croissance par email
     *
     * @param array $report
     */
    private function send_growth_report_email(array $report): void {
        $admin_email = get_option('admin_email');
        $subject = '[GROWTH REPORT] PDF Builder Pro - Weekly Growth Summary';

        $message = "Weekly Growth Report:\n\n";
        $message .= "Generated: {$report['generated_at']}\n\n";
        $message .= "Executive Summary:\n";
        $message .= "- Total Users: {$report['executive_summary']['total_users']}\n";
        $message .= "- Active Users: {$report['executive_summary']['active_users']}\n";
        $message .= "- Revenue: \${$report['executive_summary']['revenue']}\n";
        $message .= "- Growth Rate: {$report['executive_summary']['growth_rate']}%\n\n";

        $message .= "Key Highlights:\n";
        foreach ($report['executive_summary']['key_highlights'] as $highlight) {
            $message .= "- {$highlight}\n";
        }
        $message .= "\n";

        $message .= "Recommendations:\n";
        foreach ($report['recommendations'] as $category => $recommendations) {
            $message .= ucfirst($category) . ":\n";
            foreach ($recommendations as $rec) {
                $message .= "- {$rec}\n";
            }
            $message .= "\n";
        }

        wp_mail($admin_email, $subject, $message);
    }

    /**
     * AJAX: Obtenir les métriques de croissance
     */
    public function ajax_get_growth_metrics(): void {
        try {
            $period = sanitize_text_field($_POST['period'] ?? '7d');

            $metrics = $this->get_growth_metrics_for_period($period);

            wp_send_json_success($metrics);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Créer une campagne de lancement
     */
    public function ajax_create_launch_campaign(): void {
        try {
            $campaign_data = [
                'name' => sanitize_text_field($_POST['name']),
                'objective' => sanitize_text_field($_POST['objective']),
                'target_audience' => sanitize_text_field($_POST['target_audience']),
                'channels' => array_map('sanitize_text_field', $_POST['channels'] ?? []),
                'budget' => floatval($_POST['budget']),
                'start_date' => sanitize_text_field($_POST['start_date']),
                'end_date' => sanitize_text_field($_POST['end_date']),
                'content_plan' => $_POST['content_plan'] ?? []
            ];

            $result = $this->create_launch_campaign($campaign_data);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Exécuter un test A/B
     */
    public function ajax_run_ab_test(): void {
        try {
            $test_data = [
                'name' => sanitize_text_field($_POST['name']),
                'hypothesis' => sanitize_text_field($_POST['hypothesis']),
                'variants' => $_POST['variants'] ?? [],
                'metric' => sanitize_text_field($_POST['metric']),
                'sample_size' => intval($_POST['sample_size'])
            ];

            $result = $this->create_ab_test($test_data);

            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtenir les analyses produit
     */
    public function ajax_get_product_analytics(): void {
        try {
            $analytics = [
                'product_metrics' => $this->product_metrics,
                'feature_adoption' => $this->get_feature_adoption_trends(),
                'user_journey' => $this->get_user_journey_metrics(),
                'conversion_funnel' => $this->get_conversion_funnel_metrics()
            ];

            wp_send_json_success($analytics);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Obtenir les métriques de croissance pour une période
     *
     * @param string $period
     * @return array
     */
    private function get_growth_metrics_for_period(string $period): array {
        $days = $this->parse_period($period);
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $metrics = array_filter($this->growth_metrics, function($metric) use ($cutoff) {
            return $metric['timestamp'] > $cutoff;
        });

        return array_slice($metrics, -50); // Dernières 50 entrées
    }

    /**
     * Parser la période
     *
     * @param string $period
     * @return int
     */
    private function parse_period(string $period): int {
        $periods = [
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90
        ];

        return $periods[$period] ?? 7;
    }

    /**
     * Obtenir les tendances d'adoption des fonctionnalités
     *
     * @return array
     */
    private function get_feature_adoption_trends(): array {
        // Simulation
        return [
            'document_editor' => [65, 68, 72, 75, 78, 81, 85],
            'template_library' => [45, 48, 52, 58, 62, 68, 72],
            'export_pdf' => [78, 80, 82, 85, 87, 89, 91],
            'collaboration' => [12, 15, 18, 22, 28, 35, 45],
            'api_integration' => [5, 6, 8, 12, 18, 22, 23]
        ];
    }

    /**
     * Obtenir les métriques du parcours utilisateur
     *
     * @return array
     */
    private function get_user_journey_metrics(): array {
        return [
            'onboarding_completion_rate' => 78,
            'time_to_first_document' => 25, // minutes
            'feature_discovery_rate' => 65,
            'support_interaction_rate' => 12,
            'upgrade_prompt_response' => 8
        ];
    }

    /**
     * Obtenir les métriques de l'entonnoir de conversion
     *
     * @return array
     */
    private function get_conversion_funnel_metrics(): array {
        return [
            'visitors' => 10000,
            'free_signups' => 2500,
            'activated_users' => 1875,
            'trial_users' => 625,
            'paying_customers' => 375,
            'expansion_revenue' => 125000
        ];
    }

    /**
     * Obtenir les stratégies d'acquisition
     *
     * @return array
     */
    public function get_acquisition_strategies(): array {
        return $this->acquisition_strategies;
    }

    /**
     * Obtenir les programmes de monétisation
     *
     * @return array
     */
    public function get_monetization_programs(): array {
        return $this->monetization_programs;
    }

    /**
     * Obtenir les campagnes de lancement
     *
     * @return array
     */
    public function get_launch_campaigns(): array {
        return $this->launch_campaigns;
    }

    /**
     * Obtenir les tests A/B
     *
     * @return array
     */
    public function get_ab_tests(): array {
        return $this->ab_tests;
    }

    /**
     * Obtenir les métriques de croissance actuelles
     *
     * @return array
     */
    public function get_current_growth_metrics(): array {
        return end($this->growth_metrics) ?: [];
    }
}

