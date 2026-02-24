<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL

/**
 * PDF Builder Pro - Gestionnaire de Templates
 * Gère la création, sauvegarde et chargement des templates
 */

namespace PDF_Builder\Admin\Managers;

if ( ! defined( 'ABSPATH' ) ) exit;

use Exception;
use WP_Error;

/**
 * Classe responsable de la gestion des templates
 */
class TemplateManager
{
    /**
     * Instance de la classe principale
     */
    private $admin;

    /**
     * Constructeur
     */
    public function __construct($admin)
    {
        $this->admin = $admin;
        $this->registerHooks();
    }

    /**
     * Méthode de debug simple
     */
    private function debug_log($message)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {

        }
    }

    /**
     * Enregistrer les hooks
     */
    private function registerHooks()
    {
        // Hooks pour les templates
        \add_action('init', [$this, 'registerTemplatePostType']);
        \add_action('add_meta_boxes', [$this, 'addTemplateMetaBoxes']);
        \add_action('save_post', [$this, 'saveTemplateMeta'], 10, 2);
    }

    /**
     * Enregistrer le type de post pour les templates
     */
    public function registerTemplatePostType()
    {
        $labels = [
            'name' => \__('Templates PDF', 'pdf-builder-pro'),
            'singular_name' => \__('Template PDF', 'pdf-builder-pro'),
            'menu_name' => \__('Templates', 'pdf-builder-pro'),
            'add_new' => \__('Ajouter', 'pdf-builder-pro'),
            'add_new_item' => \__('Ajouter un template PDF', 'pdf-builder-pro'),
            'edit_item' => \__('Modifier le template', 'pdf-builder-pro'),
            'new_item' => \__('Nouveau template', 'pdf-builder-pro'),
            'view_item' => \__('Voir le template', 'pdf-builder-pro'),
            'search_items' => \__('Rechercher des templates', 'pdf-builder-pro'),
            'not_found' => \__('Aucun template trouvé', 'pdf-builder-pro'),
            'not_found_in_trash' => \__('Aucun template dans la corbeille', 'pdf-builder-pro'),
        ];

        $args = [
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'pdf-builder-templates',
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title', 'editor', 'custom-fields'],
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
        ];

        register_post_type('pdf_template', $args);
    }

    /**
     * Ajouter les meta boxes pour les templates
     */
    public function addTemplateMetaBoxes()
    {
        \add_meta_box(
            'pdf_template_data',
            \__('Données du Template', 'pdf-builder-pro'),
            [$this, 'renderTemplateDataMetaBox'],
            'pdf_template',
            'normal',
            'high'
        );

        \add_meta_box(
            'pdf_template_settings',
            \__('Paramètres du Template', 'pdf-builder-pro'),
            [$this, 'renderTemplateSettingsMetaBox'],
            'pdf_template',
            'side',
            'default'
        );
    }

    /**
     * Rendre la meta box des données du template
     */
    public function renderTemplateDataMetaBox($post)
    {
        \wp_nonce_field('pdf_template_meta', 'pdf_template_meta_nonce');

        $template_data = \get_post_meta($post->ID, '_pdf_template_data', true);
        $template_data_json = $template_data ? wp_json_encode($template_data, JSON_PRETTY_PRINT) : '';

        echo '<div id="pdf-template-editor" style="width: 100%; height: 600px;">';
        echo '<textarea id="template-data-json" name="template_data_json" style="width: 100%; height: 100%; font-family: monospace;">';
        echo \esc_textarea($template_data_json);
        echo '</textarea>';
        echo '</div>';

        echo '<p class="description">';
        echo esc_html__('Modifiez les données JSON du template ci-dessus. Soyez prudent avec la syntaxe JSON.', 'pdf-builder-pro');
        echo '</p>';
    }

    /**
     * Rendre la meta box des paramètres du template
     */
    public function renderTemplateSettingsMetaBox($post)
    {
        $template_type = \get_post_meta($post->ID, '_pdf_template_type', true);
        $is_default = \get_post_meta($post->ID, '_pdf_template_default', true);
        $categories = \get_post_meta($post->ID, '_pdf_template_categories', true);

        echo '<p>';
        echo '<label for="template_type"><strong>' . esc_html__('Type de template', 'pdf-builder-pro') . ':</strong></label><br>';
        echo '<select id="template_type" name="template_type" style="width: 100%;">';
        echo '<option value="invoice" ' . \selected($template_type, 'invoice', false) . '>' . esc_html__('Facture', 'pdf-builder-pro') . '</option>';
        echo '<option value="quote" ' . \selected($template_type, 'quote', false) . '>' . esc_html__('Devis', 'pdf-builder-pro') . '</option>';
        echo '<option value="receipt" ' . \selected($template_type, 'receipt', false) . '>' . esc_html__('Reçu', 'pdf-builder-pro') . '</option>';
        echo '<option value="custom" ' . \selected($template_type, 'custom', false) . '>' . esc_html__('Personnalisé', 'pdf-builder-pro') . '</option>';
        echo '</select>';
        echo '</p>';

        echo '<p>';
        echo '<label><input type="checkbox" name="template_default" value="1" ' . \checked($is_default, '1', false) . '> ';
        echo esc_html__('Template par défaut', 'pdf-builder-pro') . '</label>';
        echo '</p>';

        echo '<p>';
        echo '<label for="template_categories"><strong>' . esc_html__('Catégories', 'pdf-builder-pro') . ':</strong></label><br>';
        echo '<input type="text" id="template_categories" name="template_categories" value="' . esc_attr($categories) . '" style="width: 100%;" placeholder="' . esc_attr__('Séparez par des virgules', 'pdf-builder-pro') . '">';
        echo '</p>';
    }

    /**
     * Sauvegarder les métadonnées du template
     */
    public function saveTemplateMeta($post_id, $post)
    {
        // Vérifier le type de post
        if ($post->post_type !== 'pdf_template') {
            return;
        }

        // Vérifier les permissions
        if (!\current_user_can('edit_post', $post_id)) {
            return;
        }

        // Vérifier le nonce
        if (!isset($_POST['pdf_template_meta_nonce']) || !\pdf_builder_verify_nonce($_POST['pdf_template_meta_nonce'], 'pdf_template_meta')) {
            return;
        }

        // Sauvegarder les données JSON
        if (isset($_POST['template_data_json'])) {
            $json_data = stripslashes($_POST['template_data_json']);
            $template_data = json_decode($json_data, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                \update_post_meta($post_id, '_pdf_template_data', $template_data);
            } else {
                // Erreur JSON - sauvegarder quand même pour correction
                \update_post_meta($post_id, '_pdf_template_data', $json_data);
                add_settings_error(
                    'pdf_template_data',
                    'json_error',
                    \__('Erreur dans la syntaxe JSON. Le template n\'a pas été sauvegardé correctement.', 'pdf-builder-pro'),
                    'error'
                );
            }
        }

        // Sauvegarder les paramètres
        if (isset($_POST['template_type'])) {
            \update_post_meta($post_id, '_pdf_template_type', \sanitize_text_field($_POST['template_type']));
        }

        if (isset($_POST['template_default'])) {
            \update_post_meta($post_id, '_pdf_template_default', '1');
        } else {
            \delete_post_meta($post_id, '_pdf_template_default');
        }

        if (isset($_POST['template_categories'])) {
            \update_post_meta($post_id, '_pdf_template_categories', \sanitize_text_field($_POST['template_categories']));
        }
    }

    /**
     * Sauvegarder un template (AJAX)
     */
    /**
     * ✅ NOUVELLE FONCTION: Sauvegarde robuste et testée
     */
    public function ajaxSaveTemplateV3()
    {
        error_log('═══════════════════════════════════════════════════════════════════');
        error_log('🔄 [SAVE] DÉBUT - NOUVEAU SYSTÈME DE SAUVEGARDE');
        error_log('═══════════════════════════════════════════════════════════════════');
        
        try {
            // ✅ ÉTAPE 1: Validation nonce et permissions
            error_log('✅ [SAVE] Étape 1: Validation nonce et permissions');
            $validation = \PDF_Builder\Admin\Handlers\NonceManager::validateRequest(
                \PDF_Builder\Admin\Handlers\NonceManager::MIN_CAPABILITY
            );
            
            if (!$validation['success']) {
                error_log('❌ [SAVE] Validation échouée: ' . $validation['message']);
                if ($validation['code'] === 'nonce_invalid') {
                    \PDF_Builder\Admin\Handlers\NonceManager::sendNonceErrorResponse();
                } else {
                    \PDF_Builder\Admin\Handlers\NonceManager::sendPermissionErrorResponse();
                }
                return;
            }
            error_log('✅ [SAVE] Nonce et permissions OK');
            
            // ✅ ÉTAPE 2: Récupérer et décoder les données POST
            error_log('✅ [SAVE] Étape 2: Récupération des données POST');
            
            // Récupérer les paramètres
            $raw_template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';
            $template_name = isset($_POST['template_name']) ? \sanitize_text_field($_POST['template_name']) : '';
            $template_id = isset($_POST['template_id']) ? \intval($_POST['template_id']) : 0;
            
            error_log('  - Raw data size: ' . strlen($raw_template_data) . ' bytes');
            error_log('  - Template name: ' . $template_name);
            error_log('  - Template ID: ' . ($template_id ?: 'NEW'));
            
            // Décoder le JSON
            $template_data = json_decode($raw_template_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $error_msg = 'JSON decode error: ' . json_last_error_msg();
                error_log('❌ [SAVE] ' . $error_msg);
                \wp_send_json_error($error_msg);
                return;
            }
            
            error_log('✅ [SAVE] JSON décodé correctement');
            
            // ✅ ÉTAPE 3: Validation des données
            error_log('✅ [SAVE] Étape 3: Validation des données');
            
            if (empty($template_data)) {
                error_log('❌ [SAVE] template_data vide après décodage');
                \wp_send_json_error('Données template vides');
                return;
            }
            
            if (empty($template_name)) {
                error_log('❌ [SAVE] template_name vide');
                \wp_send_json_error('Nom du template requis');
                return;
            }
            
            // Vérifier la structure des données
            $elements_count = isset($template_data['elements']) && is_array($template_data['elements']) 
                ? count($template_data['elements']) 
                : 0;
            
            error_log('  - Nombre d\'éléments: ' . $elements_count);
            error_log('  - Canvas: ' . ($template_data['canvasWidth'] ?? 'N/A') . 'x' . ($template_data['canvasHeight'] ?? 'N/A'));
            
            if ($elements_count > 0) {
                for ($i = 0; $i < min(2, $elements_count); $i++) {
                    $el = $template_data['elements'][$i];
                    error_log('    [' . $i . '] ' . ($el['type'] ?? 'unknown') . ': x=' . ($el['x'] ?? '?') . ', y=' . ($el['y'] ?? '?'));
                }
            }
            
            error_log('✅ [SAVE] Validation OK');
            
            // ✅ ÉTAPE 4: Déterminer si INSERT ou UPDATE
            error_log('✅ [SAVE] Étape 4: Déterminer INSERT vs UPDATE');
            
            if ($template_id && $template_id > 0) {
                // UPDATE existant
                error_log('  → Mode: UPDATE (ID=' . $template_id . ')');
                
                // Vérifier que le post existe
                $existing_post = \get_post($template_id);
                if (!$existing_post || $existing_post->post_type !== 'pdf_template') {
                    error_log('❌ [SAVE] Le post avec l\'ID ' . $template_id . ' n\'existe pas ou n\'est pas un pdf_template');
                    \wp_send_json_error('Template non trouvé');
                    return;
                }
                
                error_log('✅ [SAVE] Le post existe, on peut updater');
                
                // ✅ ÉTAPE 5: UPDATE le post
                error_log('✅ [SAVE] Étape 5: Mise à jour du post');
                
                $update_result = \wp_update_post([
                    'ID' => $template_id,
                    'post_title' => $template_name,
                    'post_status' => 'publish',
                ], true);
                
                if (\is_wp_error($update_result)) {
                    $error = $update_result->get_error_message();
                    error_log('❌ [SAVE] wp_update_post échoué: ' . $error);
                    \wp_send_json_error('Erreur: ' . $error);
                    return;
                }
                
                error_log('✅ [SAVE] Post mis à jour avec succès');
                
                // ✅ ÉTAPE 6: UPDATE les meta
                error_log('✅ [SAVE] Étape 6: Mise à jour des meta');
                
                $meta_update_data = \update_post_meta($template_id, '_pdf_template_data', $template_data);
                $meta_update_type = \update_post_meta($template_id, '_pdf_template_type', 'custom');
                
                error_log('  - _pdf_template_data updated: ' . ($meta_update_data !== false ? 'YES' : 'NO'));
                error_log('  - _pdf_template_type updated: ' . ($meta_update_type !== false ? 'YES' : 'NO'));
                
            } else {
                // INSERT nouveau
                error_log('  → Mode: INSERT (nouveau template)');
                
                // ✅ ÉTAPE 5: INSERT le post
                error_log('✅ [SAVE] Étape 5: Création du post');
                
                $insert_result = \wp_insert_post([
                    'post_title' => $template_name,
                    'post_type' => 'pdf_template',
                    'post_status' => 'publish',
                    'meta_input' => [
                        '_pdf_template_data' => $template_data,
                        '_pdf_template_type' => 'custom',
                    ]
                ], true);
                
                if (\is_wp_error($insert_result)) {
                    $error = $insert_result->get_error_message();
                    error_log('❌ [SAVE] wp_insert_post échoué: ' . $error);
                    \wp_send_json_error('Erreur: ' . $error);
                    return;
                }
                
                $template_id = $insert_result;
                error_log('✅ [SAVE] Post créé avec succès (ID=' . $template_id . ')');
            }
            
            // ✅ ÉTAPE 7: Vérification finale en base de données
            error_log('✅ [SAVE] Étape 7: Vérification en base de données');
            
            $verified_data = \get_post_meta($template_id, '_pdf_template_data', true);
            
            if (empty($verified_data)) {
                error_log('❌ [SAVE] PROBLÈME: Les données ne sont pas en base de données!');
                error_log('    Template ID: ' . $template_id);
                error_log('    Résultat get_post_meta: ' . (empty($verified_data) ? 'VIDE' : 'OK'));
                \wp_send_json_error('Erreur de vérification: les données n\'ont pas pu être sauvegardées correctement');
                return;
            }
            
            $verified_elements = isset($verified_data['elements']) && is_array($verified_data['elements']) 
                ? count($verified_data['elements']) 
                : 0;
                
            error_log('✅ [SAVE] Vérification OK');
            error_log('  - Template ID: ' . $template_id);
            error_log('  - Éléments en DB: ' . $verified_elements);
            
            if ($verified_elements > 0) {
                $first_el = $verified_data['elements'][0];
                error_log('  - Premier élément: ' . ($first_el['type'] ?? 'unknown') . ' @ x=' . ($first_el['x'] ?? '?') . ', y=' . ($first_el['y'] ?? '?'));
            }
            
            // ✅ SUCCÈS
            error_log('═══════════════════════════════════════════════════════════════════');
            error_log('✅ [SAVE] SUCCÈS TOTAL - Template ID: ' . $template_id);
            error_log('═══════════════════════════════════════════════════════════════════');
            
            \wp_send_json_success([
                'template_id' => $template_id,
                'message' => 'Template sauvegardé avec succès',
                'saved_elements' => $verified_elements
            ]);
            
        } catch (Exception $e) {
            error_log('❌ [SAVE] EXCEPTION: ' . $e->getMessage());
            error_log('  Stack: ' . $e->getTraceAsString());
            \wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Charger un template (AJAX)
     */
    public function ajaxLoadTemplate()
    {
        try {
            // 🔧 CORRECTION: Utiliser NonceManager unifié pour sécurité cohérente
            // Accepter les éditeurs ET les admins pour le chargement
            $validation = \PDF_Builder\Admin\Handlers\NonceManager::validateRequest(
                \PDF_Builder\Admin\Handlers\NonceManager::MIN_CAPABILITY
            );
            
            if (!$validation['success']) {
                if ($validation['code'] === 'nonce_invalid') {
                    \PDF_Builder\Admin\Handlers\NonceManager::sendNonceErrorResponse();
                } else {
                    \PDF_Builder\Admin\Handlers\NonceManager::sendPermissionErrorResponse();
                }
                return;
            }

            $template_id = isset($_POST['template_id']) ? \intval($_POST['template_id']) : null;

            if (!$template_id) {
                \wp_send_json_error('ID de template manquant');
                return;
            }

            $template_data = \get_post_meta($template_id, '_pdf_template_data', true);

            // $this->debug_log('Loading template ID: ' . $template_id . ', data found: ' . ($template_data ? 'yes' : 'no'));

            if (!$template_data) {
                // $this->debug_log('Template data not found for ID: ' . $template_id);
                \wp_send_json_error('Template introuvable');
                return;
            }

            // $this->debug_log('Template loaded successfully, data size: ' . strlen(json_encode($template_data)));
            \wp_send_json_success([
                'template' => $template_data,
                'message' => 'Template chargé avec succès'
            ]);

        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors du chargement: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir tous les templates
     */
    public function getAllTemplates($type = null)
    {
        $args = [
            'post_type' => 'pdf_template',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ];

        if ($type) {
            $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key' => '_pdf_template_type',
                    'value' => $type,
                    'compare' => '='
                ]
            ];
        }

        $templates = \get_posts($args);
        $result = [];

        foreach ($templates as $template) {
            $result[] = [
                'id' => $template->ID,
                'name' => $template->post_title,
                'type' => \get_post_meta($template->ID, '_pdf_template_type', true),
                'is_default' => \get_post_meta($template->ID, '_pdf_template_default', true) === '1',
                'categories' => \get_post_meta($template->ID, '_pdf_template_categories', true),
                'data' => \get_post_meta($template->ID, '_pdf_template_data', true),
            ];
        }

        return $result;
    }

    /**
     * Obtenir le template par défaut
     */
    public function getDefaultTemplate($type = null)
    {
        $args = [
            'post_type' => 'pdf_template',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key' => '_pdf_template_default',
                    'value' => '1',
                    'compare' => '='
                ]
            ]
        ];

        if ($type) {
            $args['meta_query'][] = [
                'key' => '_pdf_template_type',
                'value' => $type,
                'compare' => '='
            ];
        }

        $templates = \get_posts($args);

        if (empty($templates)) {
            return null;
        }

        $template = $templates[0];
        return [
            'id' => $template->ID,
            'name' => $template->post_title,
            'data' => \get_post_meta($template->ID, '_pdf_template_data', true),
        ];
    }

    /**
     * Supprimer un template
     */
    public function deleteTemplate($template_id)
    {
        if (!\current_user_can('delete_post', $template_id)) {
            return new \WP_Error('insufficient_permissions', 'Permissions insuffisantes');
        }

        $result = \wp_delete_post($template_id, true);

        if (!$result) {
            return new \WP_Error('delete_failed', 'Échec de la suppression');
        }

        return true;
    }

    /**
     * Dupliquer un template
     */
    public function duplicateTemplate($template_id)
    {
        $original = \get_post($template_id);

        if (!$original || $original->post_type !== 'pdf_template') {
            return new \WP_Error('template_not_found', 'Template introuvable');
        }

        $template_data = \get_post_meta($template_id, '_pdf_template_data', true);

        $new_post = [
            'post_title' => $original->post_title . ' (Copie)',
            'post_type' => 'pdf_template',
            'post_status' => 'publish',
            'meta_input' => [
                '_pdf_template_data' => $template_data,
                '_pdf_template_type' => \get_post_meta($template_id, '_pdf_template_type', true),
            ]
        ];

        $new_id = \wp_insert_post($new_post);

        if (\is_wp_error($new_id)) {
            return $new_id;
        }

        return $new_id;
    }
}



