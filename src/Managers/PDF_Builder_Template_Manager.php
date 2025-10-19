<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Template Manager
 * Gestion centralisée des templates
 */

class PDF_Builder_Template_Manager {

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance) {
        $this->main = $main_instance;
        $this->init_hooks();
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // AJAX handlers pour les templates
        add_action('wp_ajax_pdf_builder_save_template', [$this, 'ajax_save_template']);
        add_action('wp_ajax_pdf_builder_pro_save_template', [$this, 'ajax_save_template']); // Alias pour compatibilité
        add_action('wp_ajax_pdf_builder_load_template', [$this, 'ajax_load_template']);
        add_action('wp_ajax_pdf_builder_flush_rest_cache', [$this, 'ajax_flush_rest_cache']);
    }

    /**
     * Page de gestion des templates
     */
    public function templates_page() {
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
     * Avec validation stricte et logging complet
     */
    public function ajax_save_template() {
        $log_prefix = '[PDF Builder] Template Save';

        try {
            // ========== LOGGING INITIAL ==========
            error_log($log_prefix . ' - 🚀 DÉBUT DE LA SAUVEGARDE');
            error_log($log_prefix . ' - PHP Version: ' . PHP_VERSION);
            error_log($log_prefix . ' - Memory limit: ' . ini_get('memory_limit'));
            error_log($log_prefix . ' - POST data keys: ' . implode(', ', array_keys($_POST)));

            // ========== ÉTAPE 1 : Vérification des permissions ==========
            if (!current_user_can('manage_options')) {
                error_log($log_prefix . ' - ❌ ERREUR: Permissions insuffisantes pour user ID ' . get_current_user_id());
                wp_send_json_error('Permissions insuffisantes');
            }

            error_log($log_prefix . ' - ✅ Permissions vérifiées pour user ID ' . get_current_user_id());        // ========== ÉTAPE 2 : Vérification du nonce ==========
        $received_nonce = isset($_POST['nonce']) ? $_POST['nonce'] : 'none';
        
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions') ||
                          wp_verify_nonce($_POST['nonce'], 'pdf_builder_templates');
        }

        if (!$nonce_valid) {
            error_log($log_prefix . ' - ❌ ERREUR: Nonce invalide reçu');
            wp_send_json_error('Sécurité: Nonce invalide');
        }
        
        error_log($log_prefix . ' - ✅ Nonce valide');

        // ========== ÉTAPE 3 : Récupération et nettoyage des données ==========
        $template_data = isset($_POST['template_data']) ? trim(wp_unslash($_POST['template_data'])) : '';
        $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        error_log($log_prefix . " - Données reçues: nom='$template_name', id=$template_id, taille JSON=" . strlen($template_data) . ' bytes');

        // Décoder les entités HTML
        $template_data = html_entity_decode($template_data, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // ========== ÉTAPE 4 : Validation du JSON ==========
        $decoded_test = json_decode($template_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $json_error = json_last_error_msg();
            error_log($log_prefix . " - ❌ ERREUR JSON: $json_error");
            wp_send_json_error('Données JSON invalides: ' . $json_error);
        }
        
        error_log($log_prefix . ' - ✅ JSON valide');

        // ========== ÉTAPE 5 : Validation de la structure du template ==========
        $validation_errors = $this->validate_template_structure($decoded_test, $log_prefix);
        if (!empty($validation_errors)) {
            foreach ($validation_errors as $error) {
                error_log($log_prefix . ' - ❌ ' . $error);
            }
            wp_send_json_error('Structure invalide: ' . implode(', ', $validation_errors));
        }
        
        error_log($log_prefix . ' - ✅ Structure valide');

        // ========== ÉTAPE 6 : Validation des données obligatoires ==========
        if (empty($template_data) || empty($template_name)) {
            error_log($log_prefix . ' - ❌ ERREUR: Données manquantes (nom ou data)');
            wp_send_json_error('Données template ou nom manquant');
        }

        // ========== ÉTAPE 7 : Sauvegarde en base de données ==========
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $data = array(
            'name' => $template_name,
            'template_data' => $template_data,
            'updated_at' => current_time('mysql')
        );

        try {
            if ($template_id > 0) {
                // Mise à jour d'un template existant
                error_log($log_prefix . " - Mise à jour du template ID=$template_id");
                $result = $wpdb->update($table_templates, $data, array('id' => $template_id));
                
                if ($result === false) {
                    $db_error = $wpdb->last_error;
                    error_log($log_prefix . " - ❌ ERREUR DB UPDATE: $db_error");
                    throw new Exception('Erreur de mise à jour en base de données: ' . $db_error);
                }
                
                error_log($log_prefix . " - ✅ Template ID=$template_id mis à jour (rows affected: $result)");
            } else {
                // Création d'un nouveau template
                $data['created_at'] = current_time('mysql');
                error_log($log_prefix . ' - Création d\'un nouveau template');
                
                $result = $wpdb->insert($table_templates, $data);
                if ($result === false) {
                    $db_error = $wpdb->last_error;
                    error_log($log_prefix . " - ❌ ERREUR DB INSERT: $db_error");
                    throw new Exception('Erreur d\'insertion en base de données: ' . $db_error);
                }
                
                $template_id = $wpdb->insert_id;
                error_log($log_prefix . " - ✅ Nouveau template créé avec ID=$template_id");
            }
        } catch (Exception $e) {
            error_log($log_prefix . ' - ❌ Exception: ' . $e->getMessage());
            wp_send_json_error('Erreur lors de la sauvegarde: ' . $e->getMessage());
            return;
        }

        // ========== ÉTAPE 8 : Vérification post-sauvegarde ==========
        $saved_template = $wpdb->get_row(
            $wpdb->prepare("SELECT id, name, template_data FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$saved_template) {
            error_log($log_prefix . " - ❌ ERREUR: Template ID=$template_id introuvable après sauvegarde");
            wp_send_json_error('Erreur: Template introuvable après sauvegarde');
            return;
        }

        $saved_data = json_decode($saved_template['template_data'], true);
        $element_count = isset($saved_data['elements']) ? count($saved_data['elements']) : 0;

        error_log($log_prefix . " - ✅ Vérification post-sauvegarde: ID=$template_id, nom='{$saved_template['name']}', éléments=$element_count");

        // ========== ÉTAPE 9 : Réponse de succès ==========
        error_log($log_prefix . " - ✅ SUCCÈS: Template ID=$template_id sauvegardé avec $element_count éléments");
        
        wp_send_json_success(array(
            'message' => 'Template sauvegardé avec succès',
            'template_id' => $template_id,
            'element_count' => $element_count
        ));
        } catch (Exception $e) {
            error_log($log_prefix . ' - 💥 EXCEPTION GLOBALE: ' . $e->getMessage());
            error_log($log_prefix . ' - Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur critique lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Charger un template
     * Avec logging complet et gestion d'erreurs améliorée
     */
    public function ajax_load_template() {
        $log_prefix = '[PDF Builder] Template Load';
        
        // ===== ÉTAPE 1 : Vérification des permissions =====
        if (!current_user_can('manage_options')) {
            error_log($log_prefix . ' - ❌ Permissions insuffisantes pour user ID ' . get_current_user_id());
            wp_send_json_error('Permissions insuffisantes');
        }
        
        error_log($log_prefix . ' - ✅ Permissions vérifiées');

        // ===== ÉTAPE 2 : Récupération et validation de l'ID =====
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        if (!$template_id) {
            error_log($log_prefix . ' - ❌ ID template invalide: ' . (isset($_POST['template_id']) ? $_POST['template_id'] : 'none'));
            wp_send_json_error('ID template invalide');
        }

        error_log($log_prefix . " - Chargement du template ID=$template_id");

        // ===== ÉTAPE 3 : Récupération depuis la base de données =====
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            error_log($log_prefix . " - ❌ Template ID=$template_id introuvable en base de données");
            wp_send_json_error('Template non trouvé');
            return;
        }

        error_log($log_prefix . " - ✅ Template trouvé: nom='{$template['name']}', taille JSON=" . strlen($template['template_data']) . ' bytes');

        // ===== ÉTAPE 4 : Décodage du JSON =====
        $template_data_raw = $template['template_data'];

        $template_data = json_decode($template_data_raw, true);
        if ($template_data === null && json_last_error() !== JSON_ERROR_NONE) {
            $json_error = json_last_error_msg();
            error_log($log_prefix . " - ❌ Erreur JSON au décodage: $json_error");
            error_log($log_prefix . " - ❌ Premières 500 caractères: " . substr($template_data_raw, 0, 500));
            wp_send_json_error('Données du template corrompues - Erreur JSON: ' . $json_error);
            return;
        }

        error_log($log_prefix . ' - ✅ JSON décodé avec succès');

        // ===== ÉTAPE 5 : Validation de la structure =====
        $validation_errors = $this->validate_template_structure($template_data, $log_prefix);
        if (!empty($validation_errors)) {
            error_log($log_prefix . ' - ⚠️ Erreurs de validation détectées:');
            foreach ($validation_errors as $error) {
                error_log($log_prefix . '   - ' . $error);
            }
            // On log mais on envoie quand même les données (backward compatibility)
            error_log($log_prefix . ' - ⚠️ Envoi des données malgré les erreurs (mode compatibilité)');
        } else {
            error_log($log_prefix . ' - ✅ Structure validée');
        }

        // ===== ÉTAPE 6 : Analyse du contenu =====
        $element_count = isset($template_data['elements']) ? count($template_data['elements']) : 0;
        error_log($log_prefix . " - Analyse: $element_count éléments, version " . 
                 ($template_data['version'] ?? 'unknown') . 
                 ', dimensions ' . ($template_data['canvasWidth'] ?? 'N/A') . 'x' . 
                 ($template_data['canvasHeight'] ?? 'N/A'));

        // Analyser les types d'éléments
        $element_types = [];
        foreach ($template_data['elements'] as $element) {
            $type = $element['type'] ?? 'unknown';
            $element_types[$type] = ($element_types[$type] ?? 0) + 1;
        }
        
        error_log($log_prefix . ' - Types d\'éléments: ' . json_encode($element_types));

        // ===== ÉTAPE 7 : Réponse de succès =====
        error_log($log_prefix . " - ✅ SUCCÈS: Template ID=$template_id chargé avec $element_count éléments");
        
        wp_send_json_success(array(
            'template' => $template_data,
            'name' => $template['name'],
            'element_count' => $element_count,
            'element_types' => $element_types
        ));
    }

    /**
     * AJAX - Vider le cache REST
     */
    public function ajax_flush_rest_cache() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vider le cache des transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pdf_builder_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_pdf_builder_%'");

        wp_send_json_success('Cache REST vidé avec succès');
    }

    /**
     * Charger un template de manière robuste
     */
    public function load_template_robust($template_id) {
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
     * @param array $template_data Données du template décodées
     * @param string $log_prefix Préfixe pour le logging
     * @return array Tableau d'erreurs de validation
     */
    private function validate_template_structure($template_data, $log_prefix = '[PDF Builder]') {
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

        // Log du résumé de la validation
        if (empty($errors)) {
            error_log($log_prefix . " - ✅ Validation structure OK: $element_count éléments, dimensions {$width}x{$height}");
        } else {
            error_log($log_prefix . ' - ⚠️ Validation structure révèle ' . count($errors) . ' erreur(s)');
        }

        return $errors;
    }

    /**
     * Valider un élément individuel du template
     * 
     * @param array $element Élément à valider
     * @param int $index Index de l'élément dans le tableau
     * @return array Tableau d'erreurs pour cet élément
     */
    private function validate_template_element($element, $index) {
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
