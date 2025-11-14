<?php
/**
 * TemplateDefaults - Templates par défaut pour utilisateurs gratuits
 * PDF Builder Pro
 */

namespace PDF_Builder;

class TemplateDefaults
{
    /**
     * Templates par défaut disponibles pour les utilisateurs gratuits
     */
    private static $default_templates = [
        'modern' => [
            'name' => 'Moderne',
            'description' => 'Template moderne avec design épuré',
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'FACTURE',
                    'position' => ['x' => 50, 'y' => 50],
                    'size' => ['width' => 200, 'height' => 40],
                    'style' => [
                        'fontSize' => 24,
                        'fontWeight' => 'bold',
                        'color' => '#2c3e50'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'N° de facture: {{invoice_number}}',
                    'position' => ['x' => 400, 'y' => 50],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#7f8c8d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Date: {{invoice_date}}',
                    'position' => ['x' => 400, 'y' => 75],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#7f8c8d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Client: {{customer_name}}',
                    'position' => ['x' => 50, 'y' => 120],
                    'size' => ['width' => 300, 'height' => 20],
                    'style' => [
                        'fontSize' => 14,
                        'fontWeight' => 'bold',
                        'color' => '#2c3e50'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Adresse: {{customer_address}}',
                    'position' => ['x' => 50, 'y' => 145],
                    'size' => ['width' => 300, 'height' => 40],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#34495e'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'ARTICLES',
                    'position' => ['x' => 50, 'y' => 220],
                    'size' => ['width' => 200, 'height' => 25],
                    'style' => [
                        'fontSize' => 16,
                        'fontWeight' => 'bold',
                        'color' => '#2c3e50'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Sous-total: {{subtotal}}',
                    'position' => ['x' => 400, 'y' => 400],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#34495e'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'TVA: {{tax}}',
                    'position' => ['x' => 400, 'y' => 425],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#34495e'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'TOTAL: {{total}}',
                    'position' => ['x' => 400, 'y' => 455],
                    'size' => ['width' => 150, 'height' => 25],
                    'style' => [
                        'fontSize' => 14,
                        'fontWeight' => 'bold',
                        'color' => '#e74c3c'
                    ]
                ]
            ]
        ],
        'classic' => [
            'name' => 'Classique',
            'description' => 'Template traditionnel avec en-tête formel',
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'FACTURE COMMERCIALE',
                    'position' => ['x' => 50, 'y' => 50],
                    'size' => ['width' => 250, 'height' => 30],
                    'style' => [
                        'fontSize' => 20,
                        'fontWeight' => 'bold',
                        'color' => '#000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'N° {{invoice_number}}',
                    'position' => ['x' => 400, 'y' => 50],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Date: {{invoice_date}}',
                    'position' => ['x' => 400, 'y' => 75],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'FACTURER À:',
                    'position' => ['x' => 50, 'y' => 120],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'fontWeight' => 'bold',
                        'color' => '#000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => '{{customer_name}}',
                    'position' => ['x' => 50, 'y' => 145],
                    'size' => ['width' => 200, 'height' => 20],
                    'style' => [
                        'fontSize' => 14,
                        'fontWeight' => 'bold',
                        'color' => '#000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => '{{customer_address}}',
                    'position' => ['x' => 50, 'y' => 170],
                    'size' => ['width' => 250, 'height' => 40],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'DESCRIPTION',
                    'position' => ['x' => 50, 'y' => 250],
                    'size' => ['width' => 200, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'fontWeight' => 'bold',
                        'color' => '#000000',
                        'borderBottom' => '1px solid #000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'QUANTITÉ',
                    'position' => ['x' => 350, 'y' => 250],
                    'size' => ['width' => 80, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'fontWeight' => 'bold',
                        'color' => '#000000',
                        'borderBottom' => '1px solid #000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'PRIX',
                    'position' => ['x' => 450, 'y' => 250],
                    'size' => ['width' => 80, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'fontWeight' => 'bold',
                        'color' => '#000000',
                        'borderBottom' => '1px solid #000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Sous-total: {{subtotal}}',
                    'position' => ['x' => 400, 'y' => 400],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'TVA: {{tax}}',
                    'position' => ['x' => 400, 'y' => 425],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#000000'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'TOTAL TTC: {{total}}',
                    'position' => ['x' => 400, 'y' => 455],
                    'size' => ['width' => 150, 'height' => 25],
                    'style' => [
                        'fontSize' => 14,
                        'fontWeight' => 'bold',
                        'color' => '#000000'
                    ]
                ]
            ]
        ],
        'corporate' => [
            'name' => 'Corporate',
            'description' => 'Template professionnel pour entreprises',
            'elements' => [
                [
                    'type' => 'text',
                    'content' => 'ENTREPRISE SARL',
                    'position' => ['x' => 50, 'y' => 30],
                    'size' => ['width' => 200, 'height' => 25],
                    'style' => [
                        'fontSize' => 18,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'FACTURE N° {{invoice_number}}',
                    'position' => ['x' => 350, 'y' => 30],
                    'size' => ['width' => 200, 'height' => 25],
                    'style' => [
                        'fontSize' => 16,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Date d\'émission: {{invoice_date}}',
                    'position' => ['x' => 350, 'y' => 60],
                    'size' => ['width' => 200, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#4a5568'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'FACTURER À',
                    'position' => ['x' => 50, 'y' => 100],
                    'size' => ['width' => 150, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => '{{customer_name}}',
                    'position' => ['x' => 50, 'y' => 125],
                    'size' => ['width' => 250, 'height' => 20],
                    'style' => [
                        'fontSize' => 14,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => '{{customer_address}}',
                    'position' => ['x' => 50, 'y' => 150],
                    'size' => ['width' => 250, 'height' => 50],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#4a5568'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'DÉTAIL DES PRESTATIONS',
                    'position' => ['x' => 50, 'y' => 230],
                    'size' => ['width' => 250, 'height' => 25],
                    'style' => [
                        'fontSize' => 14,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Description',
                    'position' => ['x' => 50, 'y' => 270],
                    'size' => ['width' => 250, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d',
                        'borderBottom' => '2px solid #e2e8f0'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Montant HT',
                    'position' => ['x' => 450, 'y' => 270],
                    'size' => ['width' => 100, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d',
                        'borderBottom' => '2px solid #e2e8f0'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'Prestation de services informatiques',
                    'position' => ['x' => 50, 'y' => 300],
                    'size' => ['width' => 250, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#4a5568'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => '{{subtotal}}',
                    'position' => ['x' => 450, 'y' => 300],
                    'size' => ['width' => 100, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#4a5568'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'SOUS-TOTAL HT',
                    'position' => ['x' => 350, 'y' => 380],
                    'size' => ['width' => 120, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => '{{subtotal}}',
                    'position' => ['x' => 480, 'y' => 380],
                    'size' => ['width' => 70, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#4a5568'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'TVA (20%)',
                    'position' => ['x' => 350, 'y' => 405],
                    'size' => ['width' => 120, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => '{{tax}}',
                    'position' => ['x' => 480, 'y' => 405],
                    'size' => ['width' => 70, 'height' => 20],
                    'style' => [
                        'fontSize' => 12,
                        'color' => '#4a5568'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => 'TOTAL TTC',
                    'position' => ['x' => 350, 'y' => 435],
                    'size' => ['width' => 120, 'height' => 25],
                    'style' => [
                        'fontSize' => 14,
                        'fontWeight' => 'bold',
                        'color' => '#1a365d'
                    ]
                ],
                [
                    'type' => 'text',
                    'content' => '{{total}}',
                    'position' => ['x' => 480, 'y' => 435],
                    'size' => ['width' => 70, 'height' => 25],
                    'style' => [
                        'fontSize' => 14,
                        'fontWeight' => 'bold',
                        'color' => '#e53e3e'
                    ]
                ]
            ]
        ]
    ];

    /**
     * Obtenir tous les templates par défaut
     */
    public static function get_free_templates() {
        return self::$default_templates;
    }

    /**
     * Créer les templates par défaut pour un utilisateur gratuit
     */
    public static function create_default_templates_for_user($user_id) {
        if (!$user_id) {
            return false;
        }

        // Vérifier si l'utilisateur est premium
        if (\PDF_Builder\Admin\PdfBuilderAdmin::is_premium_user()) {
            return true; // Pas besoin de templates par défaut pour premium
        }

        $templates_created = 0;

        foreach (self::$default_templates as $template_key => $template_data) {
            // Vérifier si le template existe déjà pour cet utilisateur
            $existing_template = get_posts([
                'post_type' => 'pdf_template',
                'author' => $user_id,
                'title' => $template_data['name'],
                'posts_per_page' => 1
            ]);

            if (empty($existing_template)) {
                // Créer le template
                $template_id = wp_insert_post([
                    'post_title' => $template_data['name'],
                    'post_content' => $template_data['description'],
                    'post_type' => 'pdf_template',
                    'post_author' => $user_id,
                    'post_status' => 'publish'
                ]);

                if ($template_id) {
                    // Sauvegarder les éléments du template
                    $template_structure = [
                        'pages' => [
                            [
                                'elements' => $template_data['elements'],
                                'size' => [
                                    'width' => 595, // A4 width in points
                                    'height' => 842 // A4 height in points
                                ],
                                'margins' => [
                                    'top' => 20,
                                    'right' => 20,
                                    'bottom' => 20,
                                    'left' => 20
                                ]
                            ]
                        ]
                    ];

                    update_post_meta($template_id, '_pdf_template_data', wp_json_encode($template_structure));
                    update_post_meta($template_id, '_pdf_template_type', 'default');
                    update_post_meta($template_id, '_pdf_template_key', $template_key);

                    $templates_created++;
                }
            }
        }

        return $templates_created > 0;
    }

    /**
     * Obtenir un template par défaut spécifique
     */
    public static function get_template($template_key) {
        return self::$default_templates[$template_key] ?? null;
    }

    /**
     * Vérifier si un template est un template par défaut
     */
    public static function is_default_template($template_id) {
        $template_type = get_post_meta($template_id, '_pdf_template_type', true);
        return $template_type === 'default';
    }
}