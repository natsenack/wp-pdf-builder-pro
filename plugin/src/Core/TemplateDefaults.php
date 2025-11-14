<?php

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
     * Crée les templates par défaut pour un utilisateur
     *
     * @param int $user_id
     */
    public static function create_default_templates_for_user($user_id) {
        $free_templates = self::get_free_templates();

        foreach ($free_templates as $key => $template_data) {
            // Vérifier si le template existe déjà pour cet utilisateur
            $existing = get_posts([
                'post_type' => 'pdf_template',
                'author' => $user_id,
                'meta_query' => [
                    [
                        'key' => '_pdf_template_key',
                        'value' => $key
                    ]
                ]
            ]);

            if (empty($existing)) {
                // Créer le post template
                $post_id = wp_insert_post([
                    'post_title' => $template_data['name'],
                    'post_content' => $template_data['description'],
                    'post_type' => 'pdf_template',
                    'post_author' => $user_id,
                    'post_status' => 'publish'
                ]);

                if ($post_id && !is_wp_error($post_id)) {
                    // Sauvegarder les métadonnées
                    update_post_meta($post_id, '_pdf_template_key', $key);
                    update_post_meta($post_id, '_pdf_template_data_json', wp_json_encode($template_data['elements']));
                    update_post_meta($post_id, '_pdf_template_is_free', true);
                    update_post_meta($post_id, '_pdf_template_category', $template_data['category']);

                    // Log de création
                    error_log("PDF Builder: Template par défaut '$key' créé pour utilisateur $user_id (post_id: $post_id)");
                } else {
                    error_log("PDF Builder: Erreur création template '$key' pour utilisateur $user_id: " . print_r($post_id, true));
                }
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
}