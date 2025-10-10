<?php
/**
 * PDF Builder Pro - Page de paramètres WooCommerce
 * Version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe de paramètres WooCommerce pour PDF Builder Pro
 */
class PDF_Builder_WC_Settings extends WC_Settings_Page {

    /**
     * Constructeur
     */
    public function __construct() {
        $this->id = 'pdf_builder';
        $this->label = __('PDF Builder', 'pdf-builder-pro');

        add_filter('woocommerce_settings_tabs_array', [$this, 'add_settings_tab'], 50);
        add_action('woocommerce_settings_' . $this->id, [$this, 'output']);
        add_action('woocommerce_settings_save_' . $this->id, [$this, 'save']);
        add_action('woocommerce_sections_' . $this->id, [$this, 'output_sections']);
    }

    /**
     * Ajouter l'onglet dans les paramètres WooCommerce
     *
     * @param array $settings_tabs
     * @return array
     */
    public function add_settings_tab(array $settings_tabs): array {
        $settings_tabs[$this->id] = $this->label;
        return $settings_tabs;
    }

    /**
     * Obtenir les sections de paramètres
     *
     * @return array
     */
    public function get_sections(): array {
        $sections = [
            '' => __('Général', 'pdf-builder-pro'),
            'status_templates' => __('Templates par statut', 'pdf-builder-pro'),
            'email_templates' => __('Templates d\'email', 'pdf-builder-pro'),
            'advanced' => __('Avancé', 'pdf-builder-pro'),
        ];

        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * Obtenir les paramètres pour une section
     *
     * @param string $current_section
     * @return array
     */
    public function get_settings(string $current_section = ''): array {
        global $wpdb;

        // Récupérer les templates actifs
        $templates = $wpdb->get_results("
            SELECT id, name
            FROM {$wpdb->prefix}pdf_builder_templates
            WHERE status = 'active'
            ORDER BY name ASC
        ");

        $template_options = ['' => __('Aucun', 'pdf-builder-pro')];
        foreach ($templates as $template) {
            $template_options[$template->id] = $template->name;
        }

        switch ($current_section) {
            case 'status_templates':
                return $this->get_status_templates_settings($template_options);

            case 'email_templates':
                return $this->get_email_templates_settings($template_options);

            case 'advanced':
                return $this->get_advanced_settings();

            default:
                return $this->get_general_settings();
        }
    }

    /**
     * Paramètres généraux
     *
     * @return array
     */
    private function get_general_settings(): array {
        return [
            [
                'title' => __('Configuration générale PDF Builder', 'pdf-builder-pro'),
                'type' => 'title',
                'desc' => __('Configurez les paramètres généraux du générateur de PDF.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_general_options'
            ],
            [
                'title' => __('Activer l\'intégration WooCommerce', 'pdf-builder-pro'),
                'desc' => __('Activez la génération automatique de PDFs pour les commandes WooCommerce.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_wc_integration_enabled',
                'default' => 'yes',
                'type' => 'checkbox'
            ],
            [
                'title' => __('Génération automatique', 'pdf-builder-pro'),
                'desc' => __('Générer automatiquement les PDFs lors des changements de statut de commande.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_auto_generation_enabled',
                'default' => 'yes',
                'type' => 'checkbox'
            ],
            [
                'title' => __('Attachement aux emails', 'pdf-builder-pro'),
                'desc' => __('Attacher automatiquement les PDFs aux emails WooCommerce.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_attachment_enabled',
                'default' => 'yes',
                'type' => 'checkbox'
            ],
            [
                'title' => __('Format PDF par défaut', 'pdf-builder-pro'),
                'desc' => __('Format de papier par défaut pour les PDFs générés.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_default_format',
                'default' => 'A4',
                'type' => 'select',
                'options' => [
                    'A3' => 'A3',
                    'A4' => 'A4',
                    'A5' => 'A5',
                    'Letter' => 'Letter',
                    'Legal' => 'Legal'
                ]
            ],
            [
                'title' => __('Orientation par défaut', 'pdf-builder-pro'),
                'desc' => __('Orientation par défaut pour les PDFs générés.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_default_orientation',
                'default' => 'portrait',
                'type' => 'select',
                'options' => [
                    'portrait' => __('Portrait', 'pdf-builder-pro'),
                    'landscape' => __('Paysage', 'pdf-builder-pro')
                ]
            ],
            [
                'type' => 'sectionend',
                'id' => 'pdf_builder_general_options'
            ]
        ];
    }

    /**
     * Paramètres des templates par statut
     *
     * @param array $template_options
     * @return array
     */
    private function get_status_templates_settings(array $template_options): array {
        return [
            [
                'title' => __('Templates par statut de commande', 'pdf-builder-pro'),
                'type' => 'title',
                'desc' => __('Associez des templates PDF aux différents statuts de commande WooCommerce.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_templates_options'
            ],
            [
                'title' => __('Commande en attente', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "En attente".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_on-hold',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande en cours', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "En cours".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_processing',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande terminée', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "Terminée".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_completed',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande annulée', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "Annulée".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_cancelled',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande remboursée', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "Remboursée".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_refunded',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Échec de paiement', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "Échec de paiement".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_failed',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'type' => 'sectionend',
                'id' => 'pdf_builder_status_templates_options'
            ]
        ];
    }

    /**
     * Paramètres des templates d'email
     *
     * @param array $template_options
     * @return array
     */
    private function get_email_templates_settings(array $template_options): array {
        return [
            [
                'title' => __('Templates d\'email WooCommerce', 'pdf-builder-pro'),
                'type' => 'title',
                'desc' => __('Associez des templates PDF aux différents types d\'email WooCommerce.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_templates_options'
            ],
            [
                'title' => __('Nouvelle commande', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de nouvelle commande.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_new_order',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande annulée', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email d\'annulation de commande.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_cancelled_order',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Échec de commande', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email d\'échec de commande.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_failed_order',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande en cours', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de commande en cours.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_processing_order',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande terminée', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de commande terminée.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_completed_order',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Détails de commande', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email des détails de commande.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_order_details',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Facture', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de facture.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_invoice',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Note de commande', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de note de commande.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_note',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Réinitialisation de mot de passe', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de réinitialisation de mot de passe.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_reset_password',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Nouveau compte', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de nouveau compte.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_new_account',
                'default' => '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'type' => 'sectionend',
                'id' => 'pdf_builder_email_templates_options'
            ]
        ];
    }

    /**
     * Paramètres avancés
     *
     * @return array
     */
    private function get_advanced_settings(): array {
        return [
            [
                'title' => __('Paramètres avancés', 'pdf-builder-pro'),
                'type' => 'title',
                'desc' => __('Configuration avancée du générateur de PDF.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_advanced_options'
            ],
            [
                'title' => __('Qualité PDF', 'pdf-builder-pro'),
                'desc' => __('Qualité de compression des images dans les PDFs.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_pdf_quality',
                'default' => 'high',
                'type' => 'select',
                'options' => [
                    'low' => __('Basse', 'pdf-builder-pro'),
                    'medium' => __('Moyenne', 'pdf-builder-pro'),
                    'high' => __('Haute', 'pdf-builder-pro'),
                    'ultra' => __('Ultra', 'pdf-builder-pro')
                ]
            ],
            [
                'title' => __('Taille maximale des fichiers', 'pdf-builder-pro'),
                'desc' => __('Taille maximale des PDFs générés (en Mo).', 'pdf-builder-pro'),
                'id' => 'pdf_builder_max_file_size',
                'default' => '10',
                'type' => 'number',
                'custom_attributes' => [
                    'min' => 1,
                    'max' => 100
                ]
            ],
            [
                'title' => __('Timeout de génération', 'pdf-builder-pro'),
                'desc' => __('Timeout maximum pour la génération d\'un PDF (en secondes).', 'pdf-builder-pro'),
                'id' => 'pdf_builder_generation_timeout',
                'default' => '30',
                'type' => 'number',
                'custom_attributes' => [
                    'min' => 10,
                    'max' => 300
                ]
            ],
            [
                'title' => __('Nettoyage automatique', 'pdf-builder-pro'),
                'desc' => __('Supprimer automatiquement les anciens PDFs après un certain temps.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_auto_cleanup_enabled',
                'default' => 'no',
                'type' => 'checkbox'
            ],
            [
                'title' => __('Durée de rétention', 'pdf-builder-pro'),
                'desc' => __('Nombre de jours avant suppression automatique des PDFs.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_cleanup_days',
                'default' => '30',
                'type' => 'number',
                'custom_attributes' => [
                    'min' => 1,
                    'max' => 365
                ]
            ],
            [
                'type' => 'sectionend',
                'id' => 'pdf_builder_advanced_options'
            ]
        ];
    }

    /**
     * Sauvegarder les paramètres
     */
    public function save(): void {
        global $current_section;

        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::save_fields($settings);

        // Sauvegarder les configurations spéciales
        if ($current_section === 'status_templates') {
            $this->save_status_templates_config();
        } elseif ($current_section === 'email_templates') {
            $this->save_email_templates_config();
        }
    }

    /**
     * Sauvegarder la configuration des templates par statut
     */
    private function save_status_templates_config(): void {
        $status_templates = [];

        $statuses = ['on-hold', 'processing', 'completed', 'cancelled', 'refunded', 'failed'];
        foreach ($statuses as $status) {
            $option_key = 'pdf_builder_status_template_' . $status;
            $template_id = get_option($option_key);
            if (!empty($template_id)) {
                $status_templates[$status] = intval($template_id);
            }
        }

        update_option('pdf_builder_status_templates', $status_templates);
    }

    /**
     * Sauvegarder la configuration des templates d'email
     */
    private function save_email_templates_config(): void {
        $email_templates = [];

        $emails = [
            'new_order',
            'cancelled_order',
            'failed_order',
            'customer_processing_order',
            'customer_completed_order',
            'customer_order_details',
            'customer_invoice',
            'customer_note',
            'customer_reset_password',
            'customer_new_account'
        ];

        foreach ($emails as $email) {
            $option_key = 'pdf_builder_email_template_' . $email;
            $template_id = get_option($option_key);
            if (!empty($template_id)) {
                $email_templates[$email] = intval($template_id);
            }
        }

        update_option('pdf_builder_email_templates', $email_templates);
    }
}

// Initialiser la page de paramètres
new PDF_Builder_WC_Settings();

