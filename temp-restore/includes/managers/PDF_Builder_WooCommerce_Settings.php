<?php
/**
 * PDF Builder Pro - WooCommerce Settings Page
 * Version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * WooCommerce Settings Page for PDF Builder Pro
 */
class PDF_Builder_WooCommerce_Settings extends WC_Settings_Page {

    /**
     * Constructor
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
     * Add settings tab
     */
    public function add_settings_tab($settings_tabs) {
        $settings_tabs[$this->id] = $this->label;
        return $settings_tabs;
    }

    /**
     * Get sections
     */
    public function get_sections() {
        $sections = [
            '' => __('Général', 'pdf-builder-pro'),
            'status_templates' => __('Templates par statut', 'pdf-builder-pro'),
            'email_templates' => __('Templates d\'email', 'pdf-builder-pro'),
            'advanced' => __('Avancé', 'pdf-builder-pro'),
        ];

        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * Get settings for section
     */
    public function get_settings($current_section = '') {
        global $wpdb;

        // Get active templates
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
     * Save settings
     */
    public function save() {
        global $current_section;

        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::save_fields($settings);

        // Custom processing for complex settings
        if ($current_section === 'status_templates') {
            $this->save_status_templates();
        } elseif ($current_section === 'email_templates') {
            $this->save_email_templates();
        }
    }

    /**
     * Save status templates configuration
     */
    private function save_status_templates() {
        $enabled_statuses = [];
        $status_templates = [];

        $statuses = ['on-hold', 'processing', 'completed', 'cancelled', 'refunded', 'failed'];

        foreach ($statuses as $status) {
            $template_id = isset($_POST['pdf_builder_status_template_' . $status]) ? sanitize_text_field($_POST['pdf_builder_status_template_' . $status]) : '';
            if (!empty($template_id)) {
                $enabled_statuses[] = $status;
                $status_templates[$status] = $template_id;
            }
        }

        update_option('pdf_builder_wc_status_templates', $enabled_statuses);
        update_option('pdf_builder_wc_status_templates_config', $status_templates);
    }

    /**
     * Save email templates configuration
     */
    private function save_email_templates() {
        $enabled_emails = [];
        $email_templates = [];

        $emails = [
            'new_order',
            'cancelled_order', 
            'failed_order',
            'customer_completed_order',
            'customer_invoice',
            'customer_note',
            'customer_refunded_order'
        ];

        foreach ($emails as $email) {
            $template_id = isset($_POST['pdf_builder_email_template_' . $email]) ? sanitize_text_field($_POST['pdf_builder_email_template_' . $email]) : '';
            if (!empty($template_id)) {
                $enabled_emails[] = $email;
                $email_templates[$email] = $template_id;
            }
        }

        update_option('pdf_builder_wc_email_attachments', $enabled_emails);
        update_option('pdf_builder_wc_email_templates_config', $email_templates);
    }

    /**
     * General settings
     */
    private function get_general_settings() {
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
     * Status templates settings
     */
    private function get_status_templates_settings($template_options) {
        $current_config = get_option('pdf_builder_wc_status_templates_config', []);

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
                'value' => isset($current_config['on-hold']) ? $current_config['on-hold'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande en cours', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "En cours".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_processing',
                'default' => '',
                'value' => isset($current_config['processing']) ? $current_config['processing'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande terminée', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "Terminée".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_completed',
                'default' => '',
                'value' => isset($current_config['completed']) ? $current_config['completed'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande annulée', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "Annulée".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_cancelled',
                'default' => '',
                'value' => isset($current_config['cancelled']) ? $current_config['cancelled'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande remboursée', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "Remboursée".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_refunded',
                'default' => '',
                'value' => isset($current_config['refunded']) ? $current_config['refunded'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Commande échouée', 'pdf-builder-pro'),
                'desc' => __('Template à utiliser quand une commande passe en statut "Échouée".', 'pdf-builder-pro'),
                'id' => 'pdf_builder_status_template_failed',
                'default' => '',
                'value' => isset($current_config['failed']) ? $current_config['failed'] : '',
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
     * Email templates settings
     */
    private function get_email_templates_settings($template_options) {
        $current_config = get_option('pdf_builder_wc_email_templates_config', []);

        return [
            [
                'title' => __('Templates pour les emails WooCommerce', 'pdf-builder-pro'),
                'type' => 'title',
                'desc' => __('Choisissez les templates PDF à attacher aux différents emails WooCommerce.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_templates_options'
            ],
            [
                'title' => __('Email de confirmation de commande', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de confirmation de commande.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_new_order',
                'default' => '',
                'value' => isset($current_config['new_order']) ? $current_config['new_order'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Email de commande annulée', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email d\'annulation de commande.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_cancelled_order',
                'default' => '',
                'value' => isset($current_config['cancelled_order']) ? $current_config['cancelled_order'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Email de commande échouée', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de commande échouée.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_failed_order',
                'default' => '',
                'value' => isset($current_config['failed_order']) ? $current_config['failed_order'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Email de commande terminée', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de commande terminée.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_completed_order',
                'default' => '',
                'value' => isset($current_config['customer_completed_order']) ? $current_config['customer_completed_order'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Email de facture client', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de facture client.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_invoice',
                'default' => '',
                'value' => isset($current_config['customer_invoice']) ? $current_config['customer_invoice'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Email de note client', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de note client.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_note',
                'default' => '',
                'value' => isset($current_config['customer_note']) ? $current_config['customer_note'] : '',
                'type' => 'select',
                'options' => $template_options
            ],
            [
                'title' => __('Email de remboursement', 'pdf-builder-pro'),
                'desc' => __('Template à attacher à l\'email de remboursement.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_email_template_customer_refunded_order',
                'default' => '',
                'value' => isset($current_config['customer_refunded_order']) ? $current_config['customer_refunded_order'] : '',
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
     * Advanced settings
     */
    private function get_advanced_settings() {
        return [
            [
                'title' => __('Paramètres avancés', 'pdf-builder-pro'),
                'type' => 'title',
                'desc' => __('Paramètres avancés pour les utilisateurs expérimentés.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_advanced_options'
            ],
            [
                'title' => __('Actions en masse activées', 'pdf-builder-pro'),
                'desc' => __('Permettre les actions en masse pour générer des PDFs.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_bulk_actions_enabled',
                'default' => 'yes',
                'type' => 'checkbox'
            ],
            [
                'title' => __('Permissions de téléchargement', 'pdf-builder-pro'),
                'desc' => __('Qui peut télécharger les PDFs générés.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_download_permissions',
                'default' => 'customer_and_admin',
                'type' => 'select',
                'options' => [
                    'admin_only' => __('Administrateur seulement', 'pdf-builder-pro'),
                    'customer_and_admin' => __('Client et administrateur', 'pdf-builder-pro'),
                    'customer_only' => __('Client seulement', 'pdf-builder-pro')
                ]
            ],
            [
                'title' => __('Stockage des PDFs', 'pdf-builder-pro'),
                'desc' => __('Où stocker les fichiers PDF générés.', 'pdf-builder-pro'),
                'id' => 'pdf_builder_pdf_storage',
                'default' => 'uploads',
                'type' => 'select',
                'options' => [
                    'uploads' => __('Dossier uploads', 'pdf-builder-pro'),
                    'database' => __('Base de données', 'pdf-builder-pro'),
                    'both' => __('Les deux', 'pdf-builder-pro')
                ]
            ],
            [
                'title' => __('Motif du nom de fichier', 'pdf-builder-pro'),
                'desc' => __('Motif pour nommer les fichiers PDF. Variables disponibles: {order_number}, {template_type}, {date}, {customer_name}', 'pdf-builder-pro'),
                'id' => 'pdf_builder_filename_pattern',
                'default' => 'order-{order_number}-{template_type}-{date}',
                'type' => 'text'
            ],
            [
                'type' => 'sectionend',
                'id' => 'pdf_builder_advanced_options'
            ]
        ];
    }
}

// Return instance for WooCommerce settings
return new PDF_Builder_WooCommerce_Settings();

