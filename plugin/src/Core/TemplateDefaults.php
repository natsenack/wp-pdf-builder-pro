<?php

namespace PDF_Builder;

/**
 * Gestionnaire des templates par défaut pour le système freemium
 * Fournit 3 templates gratuits de base (Modern, Classic, Corporate)
 */

class TemplateDefaults {

    /**
     * Retourne la liste des templates gratuits disponibles
     *
     * @return array
     */
    public static function get_free_templates() {
        return [
            'modern' => [
                'name' => 'Modern Invoice',
                'description' => 'Template moderne et épuré pour factures professionnelles',
                'category' => 'invoice',
                'is_free' => true,
                'elements' => self::get_modern_template_elements()
            ],
            'classic' => [
                'name' => 'Classic Invoice',
                'description' => 'Template traditionnel professionnel et intemporel',
                'category' => 'invoice',
                'is_free' => true,
                'elements' => self::get_classic_template_elements()
            ],
            'corporate' => [
                'name' => 'Corporate Invoice',
                'description' => 'Template entreprise avec branding professionnel',
                'category' => 'invoice',
                'is_free' => true,
                'elements' => self::get_corporate_template_elements()
            ]
        ];
    }

    /**
     * Retourne la liste des templates premium disponibles
     *
     * @return array
     */
    public static function get_premium_templates() {
        return [
            'invoice_pro' => [
                'name' => 'Invoice Pro',
                'description' => 'Template professionnel avancé avec en-têtes personnalisables',
                'category' => 'invoice',
                'is_free' => false,
                'is_premium' => true,
                'elements' => self::get_modern_template_elements() // Utilise les mêmes éléments pour l'instant
            ],
            'quote_advanced' => [
                'name' => 'Quote Advanced',
                'description' => 'Template de devis professionnel avec calculs automatiques',
                'category' => 'quote',
                'is_free' => false,
                'is_premium' => true,
                'elements' => self::get_classic_template_elements() // Utilise les mêmes éléments pour l'instant
            ],
            'contract_legal' => [
                'name' => 'Contract Legal',
                'description' => 'Template de contrat juridique avec clauses standard',
                'category' => 'contract',
                'is_free' => false,
                'is_premium' => true,
                'elements' => self::get_corporate_template_elements() // Utilise les mêmes éléments pour l'instant
            ]
        ];
    }

    /**
     * Crée les templates par défaut pour un utilisateur
     *
     * @param int $user_id
     */
    public static function create_default_templates_for_user($user_id) {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $free_templates = self::get_free_templates();

        // Pour les utilisateurs gratuits, créer seulement un template par défaut
        // Sélectionner le premier template disponible (modern)
        $default_template_key = 'modern';
        $template_data = $free_templates[$default_template_key];

        // Vérifier si un template par défaut existe déjà pour cet utilisateur
        $existing_default = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_templates WHERE user_id = %d AND is_default = 1",
            $user_id
        ));

        if ($existing_default == 0) {
            // Insérer le template par défaut dans la table personnalisée
            $result = $wpdb->insert(
                $table_templates,
                [
                    'name' => $template_data['name'],
                    'template_data' => wp_json_encode($template_data['elements']),
                    'user_id' => $user_id,
                    'is_default' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ],
                ['%s', '%s', '%d', '%d', '%s', '%s']
            );

            if ($result === false) {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('Erreur lors de la création du template par défaut pour l\'utilisateur ' . $user_id . ': ' . $wpdb->last_error); }
            } else {
                if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('Template par défaut créé avec succès pour l\'utilisateur ' . $user_id . ': ' . $template_data['name']); }
            }
        }
    }

    /**
     * Éléments du template Modern
     */
    private static function get_modern_template_elements() {
        return [
            // En-tête avec logo et informations entreprise
            [
                'type' => 'text',
                'id' => 'company_name',
                'content' => '{{company_name}}',
                'position' => ['x' => 50, 'y' => 50],
                'style' => [
                    'fontSize' => 24,
                    'fontWeight' => 'bold',
                    'color' => '#2c3e50'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'company_address',
                'content' => '{{company_address}}',
                'position' => ['x' => 50, 'y' => 80],
                'style' => [
                    'fontSize' => 12,
                    'color' => '#7f8c8d'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'invoice_title',
                'content' => 'FACTURE',
                'position' => ['x' => 400, 'y' => 50],
                'style' => [
                    'fontSize' => 36,
                    'fontWeight' => 'bold',
                    'color' => '#3498db'
                ]
            ],
            // Numéro de facture et date
            [
                'type' => 'text',
                'id' => 'invoice_number',
                'content' => 'N° {{invoice_number}}',
                'position' => ['x' => 400, 'y' => 100],
                'style' => [
                    'fontSize' => 14,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'invoice_date',
                'content' => 'Date: {{invoice_date}}',
                'position' => ['x' => 400, 'y' => 120],
                'style' => [
                    'fontSize' => 12
                ]
            ],
            // Informations client
            [
                'type' => 'text',
                'id' => 'client_info_title',
                'content' => 'FACTURER À:',
                'position' => ['x' => 50, 'y' => 150],
                'style' => [
                    'fontSize' => 12,
                    'fontWeight' => 'bold',
                    'color' => '#3498db'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'client_name',
                'content' => '{{client_name}}',
                'position' => ['x' => 50, 'y' => 170],
                'style' => [
                    'fontSize' => 14,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'client_address',
                'content' => '{{client_address}}',
                'position' => ['x' => 50, 'y' => 190],
                'style' => [
                    'fontSize' => 12
                ]
            ],
            // Tableau des articles
            [
                'type' => 'table',
                'id' => 'items_table',
                'position' => ['x' => 50, 'y' => 250],
                'style' => [
                    'width' => 500,
                    'border' => '1px solid #bdc3c7'
                ],
                'headers' => ['Description', 'Qté', 'Prix', 'Total'],
                'data' => '{{items}}'
            ],
            // Totaux
            [
                'type' => 'text',
                'id' => 'total_label',
                'content' => 'TOTAL:',
                'position' => ['x' => 400, 'y' => 400],
                'style' => [
                    'fontSize' => 16,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'total_amount',
                'content' => '{{total}} €',
                'position' => ['x' => 480, 'y' => 400],
                'style' => [
                    'fontSize' => 16,
                    'fontWeight' => 'bold',
                    'color' => '#27ae60'
                ]
            ]
        ];
    }

    /**
     * Éléments du template Classic
     */
    private static function get_classic_template_elements() {
        return [
            // En-tête traditionnel
            [
                'type' => 'text',
                'id' => 'company_name',
                'content' => '{{company_name}}',
                'position' => ['x' => 50, 'y' => 50],
                'style' => [
                    'fontSize' => 20,
                    'fontWeight' => 'bold',
                    'color' => '#000000'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'company_address',
                'content' => '{{company_address}}',
                'position' => ['x' => 50, 'y' => 75],
                'style' => [
                    'fontSize' => 11,
                    'color' => '#333333'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'invoice_title',
                'content' => 'FACTURE',
                'position' => ['x' => 400, 'y' => 50],
                'style' => [
                    'fontSize' => 28,
                    'fontWeight' => 'bold',
                    'color' => '#000000'
                ]
            ],
            // Numéro et date
            [
                'type' => 'text',
                'id' => 'invoice_number',
                'content' => 'Facture N° {{invoice_number}}',
                'position' => ['x' => 400, 'y' => 90],
                'style' => [
                    'fontSize' => 12,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'invoice_date',
                'content' => 'Date: {{invoice_date}}',
                'position' => ['x' => 400, 'y' => 110],
                'style' => [
                    'fontSize' => 12
                ]
            ],
            // Client
            [
                'type' => 'text',
                'id' => 'client_label',
                'content' => 'Client:',
                'position' => ['x' => 50, 'y' => 140],
                'style' => [
                    'fontSize' => 12,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'client_name',
                'content' => '{{client_name}}',
                'position' => ['x' => 50, 'y' => 160],
                'style' => [
                    'fontSize' => 14,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'client_address',
                'content' => '{{client_address}}',
                'position' => ['x' => 50, 'y' => 180],
                'style' => [
                    'fontSize' => 12
                ]
            ],
            // Tableau avec bordures classiques
            [
                'type' => 'table',
                'id' => 'items_table',
                'position' => ['x' => 50, 'y' => 220],
                'style' => [
                    'width' => 500,
                    'border' => '2px solid #000000'
                ],
                'headers' => ['Désignation', 'Quantité', 'Prix unitaire', 'Montant'],
                'data' => '{{items}}'
            ],
            // Totaux
            [
                'type' => 'text',
                'id' => 'total_label',
                'content' => 'NET À PAYER:',
                'position' => ['x' => 350, 'y' => 380],
                'style' => [
                    'fontSize' => 14,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'total_amount',
                'content' => '{{total}} €',
                'position' => ['x' => 480, 'y' => 380],
                'style' => [
                    'fontSize' => 14,
                    'fontWeight' => 'bold'
                ]
            ]
        ];
    }

    /**
     * Éléments du template Corporate
     */
    private static function get_corporate_template_elements() {
        return [
            // En-tête corporate avec ligne de séparation
            [
                'type' => 'line',
                'id' => 'header_line',
                'position' => ['x' => 50, 'y' => 40],
                'endPosition' => ['x' => 550, 'y' => 40],
                'style' => [
                    'stroke' => '#2c3e50',
                    'strokeWidth' => 2
                ]
            ],
            [
                'type' => 'text',
                'id' => 'company_name',
                'content' => '{{company_name}}',
                'position' => ['x' => 50, 'y' => 60],
                'style' => [
                    'fontSize' => 22,
                    'fontWeight' => 'bold',
                    'color' => '#2c3e50'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'company_details',
                'content' => '{{company_address}}\nSIRET: {{company_siret}}',
                'position' => ['x' => 50, 'y' => 85],
                'style' => [
                    'fontSize' => 10,
                    'color' => '#7f8c8d'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'invoice_title',
                'content' => 'FACTURE COMMERCIALE',
                'position' => ['x' => 350, 'y' => 60],
                'style' => [
                    'fontSize' => 18,
                    'fontWeight' => 'bold',
                    'color' => '#34495e'
                ]
            ],
            // Numéro et références
            [
                'type' => 'text',
                'id' => 'invoice_ref',
                'content' => 'Référence: {{invoice_number}}',
                'position' => ['x' => 350, 'y' => 90],
                'style' => [
                    'fontSize' => 12,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'invoice_date',
                'content' => 'Émise le: {{invoice_date}}',
                'position' => ['x' => 350, 'y' => 110],
                'style' => [
                    'fontSize' => 12
                ]
            ],
            // Destinataire
            [
                'type' => 'text',
                'id' => 'recipient_label',
                'content' => 'DESTINATAIRE',
                'position' => ['x' => 50, 'y' => 140],
                'style' => [
                    'fontSize' => 11,
                    'fontWeight' => 'bold',
                    'color' => '#2c3e50'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'client_name',
                'content' => '{{client_name}}',
                'position' => ['x' => 50, 'y' => 160],
                'style' => [
                    'fontSize' => 14,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'client_address',
                'content' => '{{client_address}}',
                'position' => ['x' => 50, 'y' => 180],
                'style' => [
                    'fontSize' => 12
                ]
            ],
            // Tableau corporate
            [
                'type' => 'table',
                'id' => 'items_table',
                'position' => ['x' => 50, 'y' => 220],
                'style' => [
                    'width' => 500,
                    'border' => '1px solid #bdc3c7',
                    'backgroundColor' => '#f8f9fa'
                ],
                'headers' => ['Prestation', 'Quantité', 'Prix HT', 'Montant HT'],
                'data' => '{{items}}'
            ],
            // Ligne de séparation
            [
                'type' => 'line',
                'id' => 'footer_line',
                'position' => ['x' => 50, 'y' => 350],
                'endPosition' => ['x' => 550, 'y' => 350],
                'style' => [
                    'stroke' => '#bdc3c7',
                    'strokeWidth' => 1
                ]
            ],
            // Totaux
            [
                'type' => 'text',
                'id' => 'total_ht_label',
                'content' => 'Total HT:',
                'position' => ['x' => 400, 'y' => 370],
                'style' => [
                    'fontSize' => 12,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'total_tva_label',
                'content' => 'TVA (20%):',
                'position' => ['x' => 400, 'y' => 385],
                'style' => [
                    'fontSize' => 12
                ]
            ],
            [
                'type' => 'text',
                'id' => 'total_ttc_label',
                'content' => 'NET À PAYER:',
                'position' => ['x' => 400, 'y' => 405],
                'style' => [
                    'fontSize' => 14,
                    'fontWeight' => 'bold'
                ]
            ],
            [
                'type' => 'text',
                'id' => 'total_amount',
                'content' => '{{total}} € TTC',
                'position' => ['x' => 480, 'y' => 405],
                'style' => [
                    'fontSize' => 14,
                    'fontWeight' => 'bold',
                    'color' => '#27ae60'
                ]
            ]
        ];
    }

    /**
     * Retourne un template par son slug
     *
     * @param string $slug Le slug du template
     * @return array|null Les données du template ou null si non trouvé
     */
    public static function get_template_by_slug($slug) {
        $free_templates = self::get_free_templates();
        $premium_templates = self::get_premium_templates();

        // Chercher dans les templates gratuits
        if (isset($free_templates[$slug])) {
            return $free_templates[$slug];
        }

        // Chercher dans les templates premium
        if (isset($premium_templates[$slug])) {
            return $premium_templates[$slug];
        }

        return null;
    }
}




