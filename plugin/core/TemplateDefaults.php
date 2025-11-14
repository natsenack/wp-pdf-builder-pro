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
     * NOTE: Supprimés pour le modèle freemium strict (1 template personnalisé uniquement)
     */
    private static $default_templates = [
        // Templates supprimés - uniquement disponibles en Premium
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