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
     * NOTE: Supprimés pour le modèle freemium strict (1 template personnalisé maximum)
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
        if (\PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->is_premium()) {
            return true; // Pas besoin de templates par défaut pour premium
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Créer un seul template par défaut pour les utilisateurs gratuits
        $default_template_data = [
            'name' => 'Template Facture Moderne',
            'description' => 'Template de facture moderne et professionnel',
            'elements' => [
                // Structure de base d'une facture
                [
                    'type' => 'text',
                    'content' => 'FACTURE',
                    'x' => 50,
                    'y' => 50,
                    'width' => 200,
                    'height' => 30,
                    'fontSize' => 24,
                    'fontWeight' => 'bold',
                    'color' => '#000000'
                ],
                [
                    'type' => 'text',
                    'content' => 'N° de facture: [NUMERO_FACTURE]',
                    'x' => 400,
                    'y' => 50,
                    'width' => 150,
                    'height' => 20,
                    'fontSize' => 12,
                    'color' => '#666666'
                ]
            ]
        ];

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
                    'name' => $default_template_data['name'],
                    'template_data' => wp_json_encode($default_template_data['elements']),
                    'user_id' => $user_id,
                    'is_default' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ],
                ['%s', '%s', '%d', '%d', '%s', '%s']
            );

            if ($result === false) {
                error_log('Erreur lors de la création du template par défaut pour l\'utilisateur ' . $user_id . ': ' . $wpdb->last_error);
                return false;
            } else {
                error_log('Template par défaut créé avec succès pour l\'utilisateur ' . $user_id . ': ' . $default_template_data['name']);
                return true;
            }
        }

        return true; // Template par défaut existe déjà
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
