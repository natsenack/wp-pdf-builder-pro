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
    public function __construct($main_instance)
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
        add_action('wp_ajax_pdf_builder_load_template', [$this, 'ajax_load_template']);
        add_action('wp_ajax_pdf_builder_flush_rest_cache', [$this, 'ajax_flush_rest_cache']);
        add_action('wp_ajax_pdf_builder_get_predefined_templates', [$this, 'ajax_get_predefined_templates']);
        add_action('wp_ajax_pdf_builder_install_predefined_template', [$this, 'ajax_install_predefined_template']);
        add_action('wp_ajax_pdf_builder_regenerate_predefined_thumbnails', [$this, 'ajax_regenerate_predefined_thumbnails']);
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

            // Récupération et validation de l'ID
            $template_id = isset($_POST['template_id']) ? \intval($_POST['template_id']) : 0;

            if (!$template_id) {
                \wp_send_json_error('ID template invalide');
            }

            // Récupération depuis wp_posts
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

            $template_data = \json_decode($template_data_raw, true);
            if ($template_data === null && \json_last_error() !== JSON_ERROR_NONE) {
                $json_error = \json_last_error_msg();
                \wp_send_json_error('Données du template corrompues - Erreur JSON: ' . $json_error);
                return;
            }

            // Validation de la structure
            $validation_errors = $this->validate_template_structure($template_data);
            if (!empty($validation_errors)) {
                // Log les erreurs pour debug
                error_log('PDF Builder Template Validation Errors for ID ' . $template_id . ': ' . implode(', ', $validation_errors));
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
            'name' => $post->post_title,
            'element_count' => $element_count,
            'element_types' => $element_types
            )
        );
        } catch (Exception $e) {
            error_log('PDF Builder Template Load Error: ' . $e->getMessage());
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

    /**
     * Obtenir tous les templates prédéfinis
     *
     * @return array Liste des templates prédéfinis avec leurs métadonnées
     */
    public function get_predefined_templates()
    {
        $templates = [];

        // Chemin vers le dossier des templates prédéfinis
        // Depuis src/Managers/, remonter vers la racine du plugin puis aller dans templates/predefined/
        $plugin_root = dirname(dirname(dirname(__FILE__)));
        $predefined_dir = $plugin_root . '/templates/predefined/';

        if (!is_dir($predefined_dir)) {
            return $templates;
        }

        // Scanner le dossier pour les fichiers JSON
        $files = glob($predefined_dir . '*.json');

        foreach ($files as $file) {
            $template_data = $this->load_predefined_template($file);
            if ($template_data) {
                $templates[] = $template_data;
            }
        }

        return $templates;
    }

    /**
     * Charger un template prédéfini depuis un fichier JSON
     *
     * @param string $file_path Chemin complet vers le fichier JSON
     * @return array|null Données du template ou null si erreur
     */
    private function load_predefined_template($file_path)
    {
        if (!file_exists($file_path)) {
            return null;
        }

        $json_content = file_get_contents($file_path);
        if ($json_content === false) {
            return null;
        }

        $template_data = json_decode($json_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        // Normaliser la structure : si les propriétés sont dans un sous-objet 'template', les remonter au niveau racine
        if (isset($template_data['template']) && is_array($template_data['template'])) {
            $nested_template = $template_data['template'];
            unset($template_data['template']); // Supprimer l'objet imbriqué

            // Fusionner les propriétés du template imbriqué vers le niveau racine
            $template_data = array_merge($nested_template, $template_data);
        }

        // Validation de la structure
        $validation_errors = $this->validate_template_structure($template_data);
        if (!empty($validation_errors)) {
            return null;
        }

        // Ajouter des métadonnées
        $filename = basename($file_path, '.json');
        $template_data['_metadata'] = [
            'filename' => $filename,
            'file_path' => $file_path,
            'is_predefined' => true,
            'loaded_at' => current_time('mysql')
        ];

        return $template_data;
    }

    /**
     * Obtenir un template prédéfini par son nom
     *
     * @param string $template_name Nom du template (sans extension .json)
     * @return array|null Données du template ou null si non trouvé
     */
    public function get_predefined_template($template_name)
    {
        // Chemin vers le dossier des templates prédéfinis
        // Depuis src/Managers/, remonter vers la racine du plugin puis aller dans templates/predefined/
        $plugin_root = dirname(dirname(dirname(__FILE__)));
        $predefined_dir = $plugin_root . '/templates/predefined/';
        $file_path = $predefined_dir . $template_name . '.json';

        return $this->load_predefined_template($file_path);
    }

    /**
     * Installer un template prédéfini dans la base de données utilisateur
     *
     * @param string $template_name Nom du template prédéfini
     * @param string $custom_name Nom personnalisé pour le template utilisateur
     * @return array Résultat de l'opération
     */
    public function install_predefined_template($template_name, $custom_name = '')
    {
        $template_data = $this->get_predefined_template($template_name);

        if (!$template_data) {
            return [
                'success' => false,
                'message' => 'Template prédéfini non trouvé: ' . $template_name
            ];
        }

        // Utiliser le nom personnalisé ou le nom du template
        $final_name = !empty($custom_name) ? $custom_name : $template_data['name'];

        // Préparer les données pour la sauvegarde
        $save_data = [
            'template_name' => $final_name,
            'template_data' => wp_json_encode($template_data),
            'nonce' => wp_create_nonce('pdf_builder_templates')
        ];

        // Simuler la requête AJAX de sauvegarde
        $_POST = $save_data;

        // Capturer la réponse
        ob_start();
        $this->ajax_save_template();
        $response = ob_get_clean();

        $decoded_response = json_decode($response, true);

        if ($decoded_response && isset($decoded_response['success']) && $decoded_response['success']) {
            return [
                'success' => true,
                'message' => 'Template installé avec succès',
                'template_id' => $decoded_response['data']['template_id'] ?? 0
            ];
        }

        return [
            'success' => false,
            'message' => 'Erreur lors de l\'installation: ' . ($decoded_response['data'] ?? 'Erreur inconnue')
        ];
    }

    /**
     * Obtenir tous les templates builtin (statiques)
     *
     * @return array Liste des templates builtin avec leurs métadonnées
     */
    public function get_builtin_templates()
    {
        $templates = [];

        // Définir les constantes si elles ne le sont pas
        if (!defined('PDF_BUILDER_PLUGIN_DIR')) {
            define('PDF_BUILDER_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__, 3)));
        }
        if (!defined('PDF_BUILDER_PLUGIN_URL')) {
            define('PDF_BUILDER_PLUGIN_URL', plugin_dir_url(dirname(__FILE__, 3)));
        }

        // Utiliser la constante définie dans pdf-builder-pro.php
        // PDF_BUILDER_PLUGIN_DIR est défini comme: dirname(__FILE__) . '/'
        // Cela garantit la cohérence entre dev et production
        $builtin_dir = PDF_BUILDER_PLUGIN_DIR . 'templates/builtin/';

        error_log('PDF Builder: get_builtin_templates - PDF_BUILDER_PLUGIN_DIR: ' . PDF_BUILDER_PLUGIN_DIR);
        error_log('PDF Builder: get_builtin_templates - PDF_BUILDER_PLUGIN_URL: ' . PDF_BUILDER_PLUGIN_URL);
        error_log('PDF Builder: get_builtin_templates - builtin_dir: ' . $builtin_dir);
        error_log('PDF Builder: get_builtin_templates - is_dir check: ' . (is_dir($builtin_dir) ? 'true' : 'false'));
        error_log('PDF Builder: get_builtin_templates - file_exists check: ' . (file_exists($builtin_dir) ? 'true' : 'false'));

        if (!is_dir($builtin_dir)) {
            error_log('PDF Builder: get_builtin_templates - builtin_dir n\'existe pas');
            return $templates;
        }

        // Scanner le dossier pour les fichiers JSON
        $files = glob($builtin_dir . '*.json');

        error_log('PDF Builder: get_builtin_templates - fichiers trouvés: ' . count($files));
        foreach ($files as $file) {
            error_log('PDF Builder: get_builtin_templates - fichier trouvé: ' . basename($file));
        }

        error_log('PDF Builder: get_builtin_templates - fichiers trouvés: ' . count($files));

        foreach ($files as $index => $file) {
            try {
                $filename = basename($file, '.json');
                error_log('PDF Builder: get_builtin_templates - ===== TRAITEMENT TEMPLATE ' . ($index + 1) . '/' . count($files) . ': ' . $filename . ' =====');
                error_log('PDF Builder: get_builtin_templates - FICHIER À TRAITER: ' . $filename . ' (index ' . $index . ')');
                error_log('PDF Builder: get_builtin_templates - chemin complet: ' . $file);
                error_log('PDF Builder: get_builtin_templates - fichier existe: ' . (file_exists($file) ? 'OUI' : 'NON'));
                error_log('PDF Builder: get_builtin_templates - est lisible: ' . (is_readable($file) ? 'OUI' : 'NON'));
                
                $content = file_get_contents($file);
                if ($content === false) {
                    error_log('PDF Builder: get_builtin_templates - ❌ ERREUR CRITIQUE: impossible de lire le fichier: ' . $file);
                    error_log('PDF Builder: get_builtin_templates - Détails fichier: existe=' . (file_exists($file) ? 'oui' : 'non') . ', lisible=' . (is_readable($file) ? 'oui' : 'non') . ', taille=' . filesize($file));
                    continue;
                }
                
                error_log('PDF Builder: get_builtin_templates - ✅ fichier lu, taille: ' . strlen($content) . ' caractères');
                
                // Log the first 200 chars and last 200 chars to debug
                error_log('PDF Builder: get_builtin_templates - début du contenu: ' . substr($content, 0, 200));
                error_log('PDF Builder: get_builtin_templates - fin du contenu: ' . substr($content, -200));
                
                $template_data = json_decode($content, true);
                error_log('PDF Builder: get_builtin_templates - Tentative de décodage JSON pour ' . $filename);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('PDF Builder: get_builtin_templates - ❌ ERREUR CRITIQUE JSON pour ' . $filename . ': ' . json_last_error_msg());
                    error_log('PDF Builder: get_builtin_templates - Contenu problématique (200 premiers caractères): ' . substr($content, 0, 200));
                    error_log('PDF Builder: get_builtin_templates - Contenu problématique (200 derniers caractères): ' . substr($content, -200));
                    continue;
                }
                error_log('PDF Builder: get_builtin_templates - ✅ JSON décodé avec succès pour ' . $filename);
                
                error_log('PDF Builder: get_builtin_templates - ✅ JSON décodé avec succès');
                error_log('PDF Builder: get_builtin_templates - structure détectée: ' . (isset($template_data['elements']) ? 'nouveau format' : 'ancien format'));
                error_log('PDF Builder: get_builtin_templates - nombre d\'éléments: ' . (isset($template_data['elements']) ? count($template_data['elements']) : 'N/A'));
                
                // Validation de la structure
                $validation_errors = $this->validate_template_structure($template_data);
                if (!empty($validation_errors)) {
                    error_log('PDF Builder: get_builtin_templates - ❌ ÉCHEC VALIDATION pour ' . $filename . ': ' . implode(', ', $validation_errors));
                    error_log('PDF Builder: get_builtin_templates - Détails éléments: ' . count($template_data['elements']) . ' éléments trouvés');
                    foreach ($validation_errors as $error) {
                        error_log('PDF Builder: get_builtin_templates - Erreur validation: ' . $error);
                    }
                    continue;
                }
                
                error_log('PDF Builder: get_builtin_templates - ✅ VALIDATION RÉUSSIE pour ' . $filename);

                // Ajouter des métadonnées pour la modal
                $template_data['id'] = $filename;
                $template_data['preview_url'] = $this->get_template_preview_url($filename);
                
                error_log('PDF Builder: get_builtin_templates - preview_url générée: ' . $template_data['preview_url']);

                // S'assurer que les champs requis pour la modal sont présents
                if (!isset($template_data['name'])) {
                    $template_data['name'] = ucfirst($filename);
                    error_log('PDF Builder: get_builtin_templates - nom généré: ' . $template_data['name']);
                } else {
                    error_log('PDF Builder: get_builtin_templates - nom existant: ' . $template_data['name']);
                }
                
                if (!isset($template_data['description'])) {
                    $template_data['description'] = 'Template ' . ucfirst($filename);
                    error_log('PDF Builder: get_builtin_templates - description générée: ' . $template_data['description']);
                } else {
                    error_log('PDF Builder: get_builtin_templates - description existante: ' . $template_data['description']);
                }
                
                if (!isset($template_data['category'])) {
                    $template_data['category'] = 'general';
                    error_log('PDF Builder: get_builtin_templates - catégorie générée: ' . $template_data['category']);
                } else {
                    error_log('PDF Builder: get_builtin_templates - catégorie existante: ' . $template_data['category']);
                }
                if (!isset($template_data['features'])) {
                    $template_data['features'] = ['Standard'];
                }

                $templates[] = $template_data;
                error_log('PDF Builder: get_builtin_templates - ✅ template ajouté: ' . $template_data['name']);
                error_log('PDF Builder: get_builtin_templates - ===== FIN TRAITEMENT TEMPLATE: ' . $filename . ' =====');
                
            } catch (Exception $e) {
                error_log('PDF Builder: get_builtin_templates - ❌ ERREUR FATALE (Exception) dans le template ' . basename($file) . ': ' . $e->getMessage());
                error_log('PDF Builder: get_builtin_templates - Trace: ' . $e->getTraceAsString());
                continue;
            } catch (Throwable $t) {
                error_log('PDF Builder: get_builtin_templates - ❌ ERREUR CRITIQUE (Throwable) dans le template ' . basename($file) . ': ' . $t->getMessage());
                error_log('PDF Builder: get_builtin_templates - Trace: ' . $t->getTraceAsString());
                continue;
            }
        }

        error_log('PDF Builder: get_builtin_templates - ===== RÉSUMÉ FINAL =====');
        error_log('PDF Builder: get_builtin_templates - total templates retournés: ' . count($templates));
        
        if (count($templates) > 0) {
            error_log('PDF Builder: get_builtin_templates - liste des templates:');
            foreach ($templates as $index => $template) {
                error_log('PDF Builder: get_builtin_templates - ' . ($index + 1) . '. ' . $template['name'] . ' (id: ' . $template['id'] . ', category: ' . $template['category'] . ')');
            }
        } else {
            error_log('PDF Builder: get_builtin_templates - ❌ AUCUN TEMPLATE RETOURNÉ');
        }

        return $templates;
    }

    /**
     * Charger un template builtin depuis un fichier JSON
     *
     * @param string $file_path Chemin complet vers le fichier JSON
     * @return array|null Données du template ou null si erreur
     */
    private function load_builtin_template($file_path)
    {
        try {
            if (!file_exists($file_path)) {
                return null;
            }

            $json_content = file_get_contents($file_path);
            if ($json_content === false) {
                error_log('PDF Builder: Impossible de lire le fichier template builtin: ' . $file_path);
                return null;
            }

            $template_data = json_decode($json_content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('PDF Builder: Erreur JSON dans le template builtin ' . basename($file_path) . ': ' . json_last_error_msg());
                return null;
            }

            // Validation de la structure
            $validation_errors = $this->validate_template_structure($template_data);
            if (!empty($validation_errors)) {
                error_log('PDF Builder: Erreurs de validation dans le template builtin ' . basename($file_path) . ': ' . implode(', ', $validation_errors));
                return null;
            }

        // Ajouter des métadonnées pour la modal
        $filename = basename($file_path, '.json');
        $template_data['id'] = $filename;
        $template_data['preview_url'] = $this->get_template_preview_url($filename);

        // S'assurer que les champs requis pour la modal sont présents
        if (!isset($template_data['name'])) {
            $template_data['name'] = ucfirst($filename);
        }
        if (!isset($template_data['description'])) {
            $template_data['description'] = 'Template ' . ucfirst($filename);
        }
        if (!isset($template_data['category'])) {
            $template_data['category'] = 'general';
        }
        if (!isset($template_data['features'])) {
            $template_data['features'] = ['Standard'];
        }

        return $template_data;
        } catch (Exception $e) {
            error_log('PDF Builder: Exception lors du chargement du template builtin ' . basename($file_path) . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir l'URL de prévisualisation d'un template
     *
     * @param string $template_name Nom du template
     * @return string URL de l'image de prévisualisation
     */
    public function get_template_preview_url($template_name)
    {
        // Utiliser la constante définie pour l'URL du plugin
        $cache_bust = time();
        return PDF_BUILDER_PLUGIN_URL . 'assets/images/templates/' . $template_name . '-preview.svg?v=' . $cache_bust;
    }

    /**
     * Obtenir un template builtin par son nom
     *
     * @param string $template_name Nom du template (sans extension .json)
     * @return array|null Données du template ou null si non trouvé
     */
    public function get_builtin_template($template_name)
    {
        // Chemin vers le dossier des templates builtin
        $plugin_root = dirname(dirname(dirname(__FILE__)));
        $builtin_dir = $plugin_root . '/templates/builtin/';
        $file_path = $builtin_dir . $template_name . '.json';

        return $this->load_builtin_template($file_path);
    }

    /**
     * Installer un template builtin dans la base de données utilisateur
     *
     * @param string $template_name Nom du template builtin
     * @param string $custom_name Nom personnalisé pour le template utilisateur
     * @return array Résultat de l'opération
     */
    public function install_builtin_template($template_name, $custom_name = '')
    {
        $template_data = $this->get_builtin_template($template_name);
        if (!$template_data) {
            return [
                'success' => false,
                'message' => 'Template builtin non trouvé: ' . $template_name
            ];
        }

        if (empty($custom_name)) {
            $custom_name = isset($template_data['metadata']['name'])
                ? $template_data['metadata']['name']
                : ucfirst($template_name);
        }

        // Supprimer les métadonnées avant la sauvegarde
        unset($template_data['_metadata']);

        // Sauvegarder comme template utilisateur
        $result = $this->save_template($template_data, $custom_name);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Template "' . $custom_name . '" installé avec succès',
                'template_id' => $result['template_id']
            ];
        }

        return $result;
    }

    /**
     * Vérifier si un template prédéfini est installé pour l'utilisateur
     *
     * @param string $template_name Nom du template prédéfini
     * @return bool True si installé
     */
    public function is_predefined_template_installed($template_name)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_templates WHERE name LIKE %s",
            $template_name . '%'
        ));

        return $count > 0;
    }

    /**
     * AJAX - Obtenir la liste des templates prédéfinis
     */
    public function ajax_get_predefined_templates()
    {
        error_log("PDF Builder Debug - ajax_get_predefined_templates called");

        try {
            // Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            $nonce_valid = false;
            if (isset($_POST['nonce'])) {
                $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates');
            }

            if (!$nonce_valid) {
                wp_send_json_error('Sécurité: Nonce invalide');
            }

            // Récupérer les templates prédéfinis
            $templates = $this->get_predefined_templates();
            error_log("PDF Builder Debug - Found " . count($templates) . " templates");

            // Formater la réponse
            $formatted_templates = [];
            foreach ($templates as $template) {
                $formatted_templates[] = [
                    'name' => $template['name'],
                    'description' => $template['description'] ?? '',
                    'category' => $template['category'] ?? 'general',
                    'tags' => $template['tags'] ?? [],
                    'isPremium' => $template['isPremium'] ?? false,
                    'previewImage' => $template['previewImage'] ?? '',
                    'elementCount' => count($template['elements'] ?? []),
                    'filename' => $template['_metadata']['filename'] ?? '',
                    'isInstalled' => $this->is_predefined_template_installed($template['name'])
                ];
            }

            error_log("PDF Builder Debug - Sending " . count($formatted_templates) . " formatted templates");
            foreach ($formatted_templates as $i => $template) {
                error_log("PDF Builder Debug - Template $i: " . $template['name'] . " - Preview URL will be generated");
            }

            wp_send_json_success([
                'templates' => $formatted_templates,
                'total' => count($formatted_templates)
            ]);

        } catch (Exception $e) {
            error_log("PDF Builder Debug - Error in ajax_get_predefined_templates: " . $e->getMessage());
            wp_send_json_error('Erreur lors de la récupération des templates: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Installer un template prédéfini
     */
    public function ajax_install_predefined_template()
    {
        try {
            // Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            $nonce_valid = false;
            if (isset($_POST['nonce'])) {
                $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates');
            }

            if (!$nonce_valid) {
                wp_send_json_error('Sécurité: Nonce invalide');
            }

            // Récupération des paramètres
            $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
            $custom_name = isset($_POST['custom_name']) ? sanitize_text_field($_POST['custom_name']) : '';

            if (empty($template_name)) {
                wp_send_json_error('Nom du template requis');
            }

            // Installer le template
            $result = $this->install_predefined_template($template_name, $custom_name);

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'template_id' => $result['template_id']
                ]);
            } else {
                wp_send_json_error($result['message']);

            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de l\'installation: ' . $e->getMessage());
        }
    }

    /**
     * Régénérer les vignettes de prévisualisation pour tous les templates prédéfinis
     */
    public function regenerate_predefined_thumbnails()
    {
        try {
            error_log('[PDF Builder] Starting thumbnail regeneration');

            // Charger PreviewImageAPI pour générer de vraies prévisualisations
            if (!class_exists('WP_PDF_Builder_Pro\Api\PreviewImageAPI')) {
                error_log('[PDF Builder] PreviewImageAPI class not found');
                throw new Exception("PreviewImageAPI class not found - plugin not properly initialized");
            }

            $preview_api = new \WP_PDF_Builder_Pro\Api\PreviewImageAPI();
            error_log('[PDF Builder] PreviewImageAPI loaded successfully');

            // Dossier des templates prédéfinis
            // Depuis src/Managers/, remonter vers la racine du plugin puis aller dans templates/predefined/
            $plugin_root = dirname(dirname(dirname(__FILE__)));
            $templates_dir = $plugin_root . '/templates/predefined/';
            error_log('[PDF Builder] Templates directory: ' . $templates_dir);

            $templates = glob($templates_dir . '*.json');
            error_log('[PDF Builder] Found ' . count($templates) . ' template files');

            if (empty($templates)) {
                throw new Exception("Aucun template prédéfini trouvé dans $templates_dir");
            }

            $results = [];
            $success_count = 0;

            foreach ($templates as $template_file) {
                $filename = basename($template_file, '.json');
                error_log('[PDF Builder] Processing template: ' . $filename);

                // Charger le JSON du template
                $template_json = file_get_contents($template_file);
                if (!$template_json) {
                    error_log('[PDF Builder] Failed to read template file: ' . $template_file);
                    $results[] = ['filename' => $filename, 'success' => false, 'error' => 'Impossible de lire le fichier'];
                    continue;
                }

                $template_data = json_decode($template_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('[PDF Builder] JSON decode error for ' . $filename . ': ' . json_last_error_msg());
                    $results[] = ['filename' => $filename, 'success' => false, 'error' => 'JSON invalide: ' . json_last_error_msg()];
                    continue;
                }

                // Vérifier si c'est un template valide
                // Supporte les deux formats : structure plate ou avec clé 'template'
                $has_flat_structure = isset($template_data['canvasWidth']) && isset($template_data['canvasHeight']) && isset($template_data['elements']);
                $has_nested_structure = isset($template_data['template']) && isset($template_data['template']['canvasWidth']) && isset($template_data['template']['canvasHeight']) && isset($template_data['template']['elements']);

                if (!$has_flat_structure && !$has_nested_structure) {
                    error_log('[PDF Builder] Invalid template structure for ' . $filename);
                    $results[] = ['filename' => $filename, 'success' => false, 'error' => 'Template invalide: champs requis manquants'];
                    continue;
                }

                // Garder une copie de la structure originale pour la sauvegarde
                $original_template_data = $template_data;

                // Normaliser la structure pour les générateurs (qui attendent une clé 'template')
                if ($has_flat_structure && !$has_nested_structure) {
                    $template_data = [
                        'name' => $template_data['name'] ?? $filename,
                        'description' => $template_data['description'] ?? '',
                        'category' => $template_data['category'] ?? 'general',
                        'tags' => $template_data['tags'] ?? [],
                        'version' => $template_data['version'] ?? '1.0.0',
                        'isPremium' => $template_data['isPremium'] ?? false,
                        'previewImage' => $template_data['previewImage'] ?? '',
                        'template' => [
                            'canvasWidth' => $template_data['canvasWidth'],
                            'canvasHeight' => $template_data['canvasHeight'],
                            'orientation' => $template_data['orientation'] ?? 'portrait',
                            'elements' => $template_data['elements'],
                            'variables' => $template_data['variables'] ?? []
                        ]
                    ];
                }

                try {
                    error_log('[PDF Builder] Generating preview for ' . $filename);

                    // Générer une vraie prévisualisation avec PreviewImageAPI
                    $preview_params = [
                        'context' => 'editor',
                        'template_data' => $template_data,
                        'quality' => 75, // Qualité réduite pour vignette
                        'format' => 'png',
                        'order_id' => null
                    ];

                    // Générer la vignette
                    $result = $preview_api->generate_with_cache($preview_params);
                    error_log('[PDF Builder] Preview generation result for ' . $filename . ': ' . json_encode($result));

                    if ($result && isset($result['image_url'])) {
                        // Mettre à jour le champ previewImage dans le JSON original
                        $original_template_data['previewImage'] = $result['image_url'];

                        error_log('[PDF Builder] About to save template file: ' . $template_file);
                        error_log('[PDF Builder] New previewImage URL: ' . $result['image_url']);

                        // Sauvegarder le JSON mis à jour
                        $updated_json = json_encode($original_template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        if (file_put_contents($template_file, $updated_json)) {
                            error_log('[PDF Builder] File saved successfully, size: ' . strlen($updated_json) . ' bytes');
                            $results[] = ['filename' => $filename, 'success' => true, 'image_url' => $result['image_url']];
                            $success_count++;
                            error_log('[PDF Builder] Successfully updated ' . $filename);
                        } else {
                            error_log('[PDF Builder] Failed to save updated JSON for ' . $filename . ' - check file permissions');
                            $results[] = ['filename' => $filename, 'success' => false, 'error' => 'Impossible de sauvegarder le fichier'];
                        }
                    } else {
                        error_log('[PDF Builder] Preview generation failed for ' . $filename . ' - no image_url in result');
                        error_log('[PDF Builder] Result details: ' . json_encode($result));
                        $results[] = ['filename' => $filename, 'success' => false, 'error' => 'Échec de génération de la vignette'];
                    }

                } catch (Exception $e) {
                    error_log('[PDF Builder] Exception processing ' . $filename . ': ' . $e->getMessage());
                    $results[] = ['filename' => $filename, 'success' => false, 'error' => 'Exception: ' . $e->getMessage()];
                }
            }

            return [
                'success' => true,
                'total' => count($templates),
                'success_count' => $success_count,
                'results' => $results
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * AJAX - Régénérer les vignettes des templates prédéfinis
     */
    public function ajax_regenerate_predefined_thumbnails()
    {
        try {
            // Vérification des permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Permissions insuffisantes');
            }

            // Vérification du nonce
            $nonce_valid = false;
            if (isset($_POST['nonce'])) {
                $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates');
            }

            if (!$nonce_valid) {
                wp_send_json_error('Sécurité: Nonce invalide');
            }

            // Régénérer les vignettes
            $result = $this->regenerate_predefined_thumbnails();

            if ($result['success']) {
                wp_send_json_success([
                    'message' => sprintf('Vignettes régénérées: %d/%d réussies', $result['success_count'], $result['total']),
                    'results' => $result['results']
                ]);
            } else {
                wp_send_json_error($result['error']);
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur lors de la régénération: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un template
     *
     * @param int $template_id ID du template
     * @return bool True si suppression réussie
     */
    public function delete_template($template_id)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $result = $wpdb->delete(
            $table_templates,
            ['id' => $template_id],
            ['%d']
        );

        if ($result) {
            do_action('pdf_builder_template_deleted', $template_id);
            return true;
        }

        return false;
    }

    /**
     * Récupérer les données d'un template
     *
     * @param int $template_id ID du template
     * @return array|null Données du template ou null
     */
    public function get_template_data($template_id)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_templates WHERE id = %d",
            $template_id
        ), ARRAY_A);

        if ($result && isset($result['data'])) {
            $result['data'] = json_decode($result['data'], true);
        }

        return $result;
    }
}


