<?php

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Template Manager
 * Gestion centralisée des templates
 */

class PDF_Builder_Template_Manager
{
    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance = null)
    {
        $this->main = $main_instance;
        // Les hooks AJAX sont enregistrés par PDF_Builder_Admin, pas ici
        // pour éviter que la traduction soit appelée trop tôt
    }

    /**
     * Initialiser les hooks
     * NOTE: Cette méthode n'est plus appelée depuis le constructeur
     * Les hooks AJAX sont enregistrés directement par PDF_Builder_Admin
     */
    private function init_hooks()
    {
        // AJAX handlers pour les templates
        add_action('wp_ajax_pdf_builder_save_template', [$this, 'ajax_save_template']);
        add_action('wp_ajax_pdf_builder_pro_save_template', [$this, 'ajax_save_template']); // Alias pour compatibilité
        add_action('wp_ajax_pdf_builder_auto_save_template', [$this, 'ajax_auto_save_template']); // Auto-save handler
        // NOTE: pdf_builder_load_template est enregistré dans PDF_Builder_Admin.php
        // add_action('wp_ajax_pdf_builder_load_template', [$this, 'ajax_load_template']);
        // NOTE: pdf_builder_flush_rest_cache est enregistré dans PDF_Builder_Admin.php
        // add_action('wp_ajax_pdf_builder_flush_rest_cache', [$this, 'ajax_flush_rest_cache']);
    }

    /**
     * Page de gestion des templates
     */
    public function templates_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Récupérer tous les templates
        $templates = $wpdb->get_results("SELECT * FROM $table_templates ORDER BY updated_at DESC", ARRAY_A);

        include plugin_dir_path(dirname(__FILE__)) . '../../templates/admin/templates-page.php';
    }

    /**
     * AJAX - Sauvegarder un template
     */
    public function ajax_save_template()
    {
        try {
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            $nonce_valid = false;
            if (isset($_POST['nonce'])) {
                $nonce_valid = \wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce') ||
                              \wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions') ||
                              \wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates');
            }

            if (!$nonce_valid) {
                \wp_send_json_error('Sécurité: Nonce invalide');
            }

            // Récupération et nettoyage des données
            $template_data = isset($_POST['template_data']) ? \trim(\wp_unslash($_POST['template_data'])) : '';
            $template_name = isset($_POST['template_name']) ? \sanitize_text_field($_POST['template_name']) : '';
            $template_id = isset($_POST['template_id']) ? \intval($_POST['template_id']) : 0;

            // Si template_data n'est pas fourni, construire à partir d'elements et canvas séparés
            if (empty($template_data)) {
                $elements = isset($_POST['elements']) ? \wp_unslash($_POST['elements']) : '[]';
                $canvas = isset($_POST['canvas']) ? \wp_unslash($_POST['canvas']) : '{}';

                // Validation du JSON pour elements
                $elements_data = \json_decode($elements, true);
                if (\json_last_error() !== JSON_ERROR_NONE) {
                    \wp_send_json_error('Données elements JSON invalides: ' . \json_last_error_msg());
                    return;
                }

                // Validation du JSON pour canvas
                $canvas_data = \json_decode($canvas, true);
                if (\json_last_error() !== JSON_ERROR_NONE) {
                    \wp_send_json_error('Données canvas JSON invalides: ' . \json_last_error_msg());
                    return;
                }

                // Construction de la structure complète du template
                $template_structure = [
                    'elements' => $elements_data,
                    'canvasWidth' => isset($canvas_data['width']) ? $canvas_data['width'] : 794,
                    'canvasHeight' => isset($canvas_data['height']) ? $canvas_data['height'] : 1123,
                    'version' => '1.0'
                ];

                $template_data = \wp_json_encode($template_structure);
                if ($template_data === false) {
                    \wp_send_json_error('Erreur lors de l\'encodage des données template');
                    return;
                }
            }

            // Validation du JSON
            $decoded_test = \json_decode($template_data, true);
            if (\json_last_error() !== JSON_ERROR_NONE) {
                $json_error = \json_last_error_msg();
                \wp_send_json_error('Données JSON invalides: ' . $json_error);
                return;
            }

            // Validation de la structure du template
            $validation_errors = $this->validate_template_structure($decoded_test);
            if (!empty($validation_errors)) {
                \wp_send_json_error('Structure invalide: ' . \implode(', ', $validation_errors));
                return;
            }

            // Validation des données obligatoires
            if (empty($template_data) || empty($template_name)) {
                \wp_send_json_error('Données template ou nom manquant');
                return;
            }

            // Sauvegarde en utilisant les posts WordPress
            try {
                if ($template_id > 0) {
                    // Vérifier que le post existe et est du bon type
                    $existing_post = \get_post($template_id);
                    if (!$existing_post || $existing_post->post_type !== 'pdf_template') {
                        throw new \Exception('Template non trouvé ou type invalide');
                    }

                    // Mise à jour d'un template existant
                    $post_data = array(
                        'ID' => $template_id,
                        'post_title' => $template_name,
                        'post_modified' => \current_time('mysql')
                    );

                    $result = \wp_update_post($post_data, true);
                    if (\is_wp_error($result)) {
                        throw new \Exception('Erreur de mise à jour du post: ' . $result->get_error_message());
                    }
                } else {
                    // Création d'un nouveau template
                    $post_data = array(
                        'post_title' => $template_name,
                        'post_type' => 'pdf_template',
                        'post_status' => 'publish',
                        'post_date' => \current_time('mysql'),
                        'post_modified' => \current_time('mysql')
                    );

                    $template_id = \wp_insert_post($post_data, true);
                    if (\is_wp_error($template_id)) {
                        throw new \Exception('Erreur de création du post: ' . $template_id->get_error_message());
                    }
                }

                // Sauvegarder les données du template dans les métadonnées
                \update_post_meta($template_id, '_pdf_template_data', $template_data);

            } catch (\Exception $e) {
                \wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
                return;
            }

            // Vérification post-sauvegarde
            $saved_post = \get_post($template_id);
            $saved_template_data = \get_post_meta($template_id, '_pdf_template_data', true);

            if (!$saved_post || empty($saved_template_data)) {
                \wp_send_json_error('Erreur: Template introuvable après sauvegarde');
                return;
            }

            $saved_data = \json_decode($saved_template_data, true);
            $element_count = isset($saved_data['elements']) ? \count($saved_data['elements']) : 0;

            // Réponse de succès
            \wp_send_json_success(
                array(
                'message' => 'Template sauvegardé avec succès',
                'template_id' => $template_id,
                'element_count' => $element_count
                )
            );
        } catch (\Exception $e) {
            \wp_send_json_error('Erreur critique lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Charger un template
     */
    public function ajax_load_template()
    {
        try {
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Récupération de l'ID (doit être numérique pour les templates personnalisés)
            $template_id = isset($_POST['template_id']) ? \intval($_POST['template_id']) : 0;

            if (empty($template_id)) {
                \wp_send_json_error('ID template invalide');
            }

            // Chercher d'abord dans la table personnalisée (custom table)
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $template_row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                ARRAY_A
            );

            $template_data = null;
            $template_name = '';

            if ($template_row) {
                // Trouver dans la table custom
                $template_data_raw = $template_row['template_data'];
                $template_name = $template_row['name'];
                
                $template_data = \json_decode($template_data_raw, true);
                if ($template_data === null && \json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    \wp_send_json_error('Données du template corrompues - Erreur JSON: ' . $json_error);
                    return;
                }
            } else {
                // Fallback: chercher dans wp_posts
                $post = get_post($template_id);

                if (!$post || $post->post_type !== 'pdf_template') {
                    \wp_send_json_error('Template non trouvé');
                    return;
                }

                // Récupération des métadonnées
                $template_data_raw = get_post_meta($post->ID, '_pdf_template_data', true);

                if (empty($template_data_raw)) {
                    \wp_send_json_error('Données du template manquantes');
                    return;
                }

                $template_name = $post->post_title;
                $template_data = \json_decode($template_data_raw, true);
                if ($template_data === null && \json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = \json_last_error_msg();
                    \wp_send_json_error('Données du template corrompues - Erreur JSON: ' . $json_error);
                    return;
                }
            }

            // Validation de la structure
            $validation_errors = $this->validate_template_structure($template_data);
            if (!empty($validation_errors)) {
                // Ajouter les propriétés manquantes par défaut pour la compatibilité
                if (!isset($template_data['version'])) {
                    $template_data['version'] = '1.0.0';
                }
                if (!isset($template_data['canvasWidth'])) {
                    $template_data['canvasWidth'] = 794; // A4 width
                }
                if (!isset($template_data['canvasHeight'])) {
                    $template_data['canvasHeight'] = 1123; // A4 height
                }
                if (!isset($template_data['elements'])) {
                    $template_data['elements'] = [];
                }
            }

            // Analyse du contenu
            $element_count = isset($template_data['elements']) ? \count($template_data['elements']) : 0;

        // Analyser les types d'éléments
        $element_types = [];
        foreach ($template_data['elements'] as $element) {
            $type = $element['type'] ?? 'unknown';
            $element_types[$type] = ($element_types[$type] ?? 0) + 1;
        }

        // Réponse de succès
        \wp_send_json_success(
            array(
            'template' => $template_data,
            'name' => $template_name,
            'element_count' => $element_count,
            'element_types' => $element_types
            )
        );
        } catch (Exception $e) {
            \wp_send_json_error('Erreur lors du chargement du template: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Auto-save un template (simplifié)
     */
    public function ajax_auto_save_template()
    {
        try {
            // Vérification des permissions
            if (!\current_user_can('manage_options')) {
                \wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce (accepter les nonces des contextes autorisés)
            $nonce_valid = false;
            if (isset($_POST['nonce'])) {
                // Vérifier contre les contextes possibles
                $nonce_valid = \wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce') ||
                              \wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions') ||
                              \wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates');
            }

            if (!$nonce_valid) {
                \wp_send_json_error('Sécurité: Nonce invalide');
            }

            // Récupération des données
            $template_id = isset($_POST['template_id']) ? \intval($_POST['template_id']) : 0;
            $elements = isset($_POST['elements']) ? \wp_unslash($_POST['elements']) : '[]';

            if (!$template_id) {
                \wp_send_json_error('ID template manquant');
                return;
            }

            // Validation du JSON
            $elements_decoded = \json_decode($elements, true);
            if (\json_last_error() !== JSON_ERROR_NONE) {
                \wp_send_json_error('JSON invalide: ' . \json_last_error_msg());
                return;
            }

            // Préparation des données pour sauvegarde (on ne sauvegarde que les éléments)
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            // Récupérer le template existant
            $template = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
                ARRAY_A
            );

            if (!$template) {
                \wp_send_json_error('Template non trouvé');
                return;
            }

            // Décoder les données existantes
            $template_data = \json_decode($template['template_data'], true);
            if ($template_data === null) {
                $template_data = [];
            }

            // Mettre à jour les éléments
            $template_data['elements'] = $elements_decoded;

            // Sauvegarder en base
            $result = $wpdb->update(
                $table_templates,
                array(
                    'template_data' => \wp_json_encode($template_data),
                    'updated_at' => \current_time('mysql')
                ),
                array('id' => $template_id),
                array('%s', '%s'),
                array('%d')
            );

            if ($result === false) {
                \wp_send_json_error('Erreur de sauvegarde en base de données');
                return;
            }

            // Réponse de succès
            \wp_send_json_success(array(
                'message' => 'Auto-save réussi',
                'template_id' => $template_id,
                'saved_at' => \current_time('mysql'),
                'element_count' => \count($elements_decoded)
            ));
        } catch (\Exception $e) {
            \wp_send_json_error('Erreur lors de l\'auto-save: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Vider le cache REST
     */
    public function ajax_flush_rest_cache()
    {
        if (!\current_user_can('manage_options')) {
            \wp_send_json_error('Permissions insuffisantes');
        }

        // Vider le cache des transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

        \wp_send_json_success('Cache REST vidé avec succès');
    }

    /**
     * Charger un template de manière robuste
     */
    public function load_template_robust($template_id)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            return false;
        }

        $template_data_raw = $template['template_data'];

        // Vérifier si les données contiennent des backslashes (échappement PHP)
        if (strpos($template_data_raw, '\\') !== false) {
            $template_data_raw = stripslashes($template_data_raw);
        }

        $template_data = json_decode($template_data_raw, true);
        if ($template_data === null && json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $template_data;
    }

    /**
     * Valider la structure complète d'un template
     * Retourne un tableau d'erreurs (vide si valide)
     *
     * @param  array $template_data Données du template décodées
     * @return array Tableau d'erreurs de validation
     */
    private function validate_template_structure($template_data)
    {
        $errors = [];

        // ===== Vérification 1 : Type et structure de base =====
        if (!is_array($template_data)) {
            $errors[] = 'Les données doivent être un objet JSON (array PHP)';
            return $errors;
        }

        // ===== Vérification 2 : Propriétés obligatoires =====
        $required_keys = ['elements', 'canvasWidth', 'canvasHeight', 'version'];
        foreach ($required_keys as $key) {
            if (!isset($template_data[$key])) {
                $errors[] = "Propriété obligatoire manquante: '$key'";
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        // ===== Vérification 3 : Types des propriétés principales =====
        if (!is_array($template_data['elements'])) {
            $errors[] = "'elements' doit être un tableau d'objets";
        }

        if (!is_numeric($template_data['canvasWidth'])) {
            $errors[] = "'canvasWidth' doit être un nombre (reçu: " . gettype($template_data['canvasWidth']) . ')';
        }

        if (!is_numeric($template_data['canvasHeight'])) {
            $errors[] = "'canvasHeight' doit être un nombre (reçu: " . gettype($template_data['canvasHeight']) . ')';
        }

        if (!is_string($template_data['version'])) {
            $errors[] = "'version' doit être une chaîne de caractères";
        }

        if (!empty($errors)) {
            return $errors;
        }

        // ===== Vérification 4 : Valeurs numériques raisonnables =====
        $width = (float) $template_data['canvasWidth'];
        $height = (float) $template_data['canvasHeight'];

        if ($width < 50 || $width > 2000) {
            $errors[] = "canvasWidth doit être entre 50 et 2000 (reçu: $width)";
        }

        if ($height < 50 || $height > 2000) {
            $errors[] = "canvasHeight doit être entre 50 et 2000 (reçu: $height)";
        }

        // ===== Vérification 5 : Nombre d'éléments raisonnable =====
        $element_count = count($template_data['elements']);
        if ($element_count > 1000) {
            $errors[] = "Nombre d'éléments trop élevé: $element_count (max: 1000)";
        }

        // ===== Vérification 6 : Validation de chaque élément =====
        foreach ($template_data['elements'] as $index => $element) {
            $element_errors = $this->validate_template_element($element, $index);
            $errors = array_merge($errors, $element_errors);

            // Limiter à 10 erreurs pour éviter un flood de messages
            if (count($errors) >= 10) {
                $errors[] = '... et plus d\'erreurs détectées';
                break;
            }
        }

        return $errors;
    }

    /**
     * Valider un élément individuel du template
     *
     * @param  array $element Élément à valider
     * @param  int   $index   Index de l'élément dans
     *                        le
 tableau
     * @return array Tableau d'erreurs pour cet élément
     */
    private function validate_template_element($element, $index)
    {
        $errors = [];

        // Vérification que c'est un objet
        if (!is_array($element)) {
            $errors[] = "Élément $index: doit être un objet JSON (reçu: " . gettype($element) . ')';
            return $errors;
        }

        // Propriétés obligatoires pour chaque élément
        if (!isset($element['id'])) {
            $errors[] = "Élément $index: propriété 'id' manquante";
        }

        if (!isset($element['type'])) {
            $errors[] = "Élément $index: propriété 'type' manquante";
        }

        // Si les propriétés obligatoires manquent, arrêter ici
        if (count($errors) > 0) {
            return $errors;
        }

        $element_id = $element['id'];
        $element_type = $element['type'];

        // Vérifier le format de l'ID
        if (!is_string($element_id) || empty($element_id)) {
            $errors[] = "Élément $index: id doit être une chaîne non-vide (reçu: '$element_id')";
        }

        // Vérifier le type d'élément valide
        $valid_types = ['text', 'image', 'rectangle', 'line', 'product_table',
                       'customer_info', 'company_logo', 'company_info', 'order_number',
                       'document_type', 'textarea', 'html', 'divider', 'progress-bar',
                       'dynamic-text', 'mentions'];

        if (!in_array($element_type, $valid_types)) {
            $errors[] = "Élément $index ($element_id): type invalide '$element_type' (types valides: " .
                       implode(', ', $valid_types) . ')';
        }

        // Vérifier les propriétés numériques
        $numeric_props = ['x', 'y', 'width', 'height', 'fontSize', 'opacity', 'zIndex',
                         'borderWidth', 'borderRadius', 'padding', 'margin', 'rotation'];

        foreach ($numeric_props as $prop) {
            if (isset($element[$prop])) {
                if (!is_numeric($element[$prop])) {
                    $errors[] = "Élément $index ($element_id): '$prop' doit être numérique (reçu: " .
                               gettype($element[$prop]) . ')';
                }
            }
        }

        // Vérifier que x, y, width, height sont présents et raisonnables
        $required_position_props = ['x', 'y', 'width', 'height'];
        foreach ($required_position_props as $prop) {
            if (!isset($element[$prop])) {
                $errors[] = "Élément $index ($element_id): propriété '$prop' obligatoire manquante";
            } else {
                $value = (float) $element[$prop];
                if ($value < 0 || $value > 3000) {
                    $errors[] = "Élément $index ($element_id): '$prop' doit être entre 0 et 3000 (reçu: $value)";
                }
            }
        }

        // Vérifier les propriétés de couleur (format hex)
        $color_props = ['color', 'backgroundColor', 'borderColor', 'shadowColor'];
        foreach ($color_props as $prop) {
            if (isset($element[$prop]) && !empty($element[$prop])) {
                $color = $element[$prop];
                if ($color !== 'transparent' && !preg_match('/^#[0-9A-Fa-f]{3,6}$/', $color)) {
                    $errors[] = "Élément $index ($element_id): '$prop' format couleur invalide '$color'";
                }
            }
        }

        // Vérifier les propriétés de texte
        $text_props = ['fontFamily', 'fontWeight', 'textAlign', 'textDecoration', 'fontStyle'];
        $valid_values = [
            'fontWeight' => ['normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900'],
            'textAlign' => ['left', 'center', 'right', 'justify'],
            'textDecoration' => ['none', 'underline', 'overline', 'line-through'],
            'fontStyle' => ['normal', 'italic', 'oblique']
        ];

        foreach ($text_props as $prop) {
            if (isset($element[$prop]) && isset($valid_values[$prop])) {
                if (!in_array($element[$prop], $valid_values[$prop])) {
                    $errors[] = "Élément $index ($element_id): '$prop' valeur invalide '" .
                               $element[$prop] . "' (valeurs: " . implode(', ', $valid_values[$prop]) . ')';
                }
            }
        }

        return $errors;
    }






}
