<?php

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Canvas Elements Manager
 * Gestion des éléments du canvas (drag & drop, redimensionnement)
 *
 * @version 1.0.0
 */



/**
 * Classe pour gérer les éléments du canvas
 */
class PDF_Builder_Canvas_Elements_Manager
{
    /**
     * Instance singleton
     *
     * @var PDF_Builder_Canvas_Elements_Manager
     */
    private static $instance = null;

    /**
     * Gestionnaire de cache
     *
     * @var PDF_Builder_Cache_Manager
     */
    private $cache_manager = null;

    /**
     * Constructeur privé (singleton)
     */
    private function __construct()
    {
        $this->init_dependencies();
    }

    /**
     * Obtenir l'instance singleton
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les dépendances
     */
    private function init_dependencies()
    {
        if (class_exists('PDF_Builder_Cache_Manager')) {
            $this->cache_manager = PDF_Builder_Cache_Manager::getInstance();
        }
    }

    /**
     * Valider les données d'un élément
     */
    public function validate_element_data($element_data)
    {
        $required_fields = ['id', 'type', 'x', 'y', 'width', 'height'];
        $errors = [];

        foreach ($required_fields as $field) {
            if (!isset($element_data[$field])) {
                $errors[] = "Champ requis manquant: {$field}";
            }
        }

        // Validation des coordonnées
        if (isset($element_data['x']) && !is_numeric($element_data['x'])) {
            $errors[] = "Coordonnée X invalide";
        }
        if (isset($element_data['y']) && !is_numeric($element_data['y'])) {
            $errors[] = "Coordonnée Y invalide";
        }

        // Validation des dimensions
        if (isset($element_data['width']) && (!is_numeric($element_data['width']) || $element_data['width'] < 1)) {
            $errors[] = "Largeur invalide (minimum 1px)";
        }
        if (isset($element_data['height']) && (!is_numeric($element_data['height']) || $element_data['height'] < 1)) {
            $errors[] = "Hauteur invalide (minimum 1px)";
        }

        // Validation des propriétés avancées
        if (isset($element_data['fontSize']) && (!is_numeric($element_data['fontSize']) || $element_data['fontSize'] < 8 || $element_data['fontSize'] > 72)) {
            $errors[] = "Taille de police invalide (8-72px)";
        }

        if (isset($element_data['borderWidth']) && (!is_numeric($element_data['borderWidth']) || $element_data['borderWidth'] < 0 || $element_data['borderWidth'] > 20)) {
            $errors[] = "Largeur de bordure invalide (0-20px)";
        }

        if (isset($element_data['borderStyle']) && !in_array($element_data['borderStyle'], ['solid', 'dashed', 'dotted', 'double'])) {
            $errors[] = "Style de bordure invalide";
        }

        if (isset($element_data['borderRadius']) && (!is_numeric($element_data['borderRadius']) || $element_data['borderRadius'] < 0 || $element_data['borderRadius'] > 100)) {
            $errors[] = "Rayon de bordure invalide (0-100px)";
        }

        if (isset($element_data['opacity']) && (!is_numeric($element_data['opacity']) || $element_data['opacity'] < 0 || $element_data['opacity'] > 100)) {
            $errors[] = "Opacité invalide (0-100%)";
        }

        if (isset($element_data['rotation']) && (!is_numeric($element_data['rotation']) || $element_data['rotation'] < -180 || $element_data['rotation'] > 180)) {
            $errors[] = "Rotation invalide (-180° à 180°)";
        }

        if (isset($element_data['scale']) && (!is_numeric($element_data['scale']) || $element_data['scale'] < 10 || $element_data['scale'] > 200)) {
            $errors[] = "Échelle invalide (10-200%)";
        }

        // Validation des couleurs (format hexadécimal)
        $color_fields = ['color', 'backgroundColor', 'borderColor', 'shadowColor'];
        foreach ($color_fields as $field) {
            if (isset($element_data[$field]) && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $element_data[$field]) && $element_data[$field] !== 'transparent') {
                $errors[] = "Couleur {$field} invalide (format hexadécimal attendu)";
            }
        }

        // Validation des pourcentages
        $percentage_fields = ['brightness', 'contrast', 'saturate'];
        foreach ($percentage_fields as $field) {
            if (isset($element_data[$field]) && (!is_numeric($element_data[$field]) || $element_data[$field] < 0 || $element_data[$field] > 200)) {
                $errors[] = "Pourcentage {$field} invalide (0-200%)";
            }
        }

        return $errors;
    }

    /**
     * Valider et nettoyer les données JSON des éléments
     * Cette fonction remplace les anciens appels json_decode pour une validation robuste
     */
    public function validate_and_clean_json_elements($json_string, $source = 'unknown')
    {
        // Log pour le débogage

        // Vérifier si la chaîne JSON est vide ou nulle
        if (empty($json_string) || $json_string === 'null') {
            return [];
        }

        // Nettoyer la chaîne JSON (supprimer les slashes si nécessaire)
        $clean_json = stripslashes($json_string);

        // Essayer de décoder le JSON
        $elements = json_decode($clean_json, true);

        // Vérifier les erreurs de décodage JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_msg = json_last_error_msg();

            // Essayer de réparer les problèmes courants
            $repaired_json = $this->repair_json_string($clean_json);
            if ($repaired_json !== false) {
                $elements = json_decode($repaired_json, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                } else {
                    return new WP_Error('json_decode_error', "Erreur JSON irrécupérable: {$error_msg}");
                }
            } else {
                return new WP_Error('json_decode_error', "Erreur JSON: {$error_msg}");
            }
        }

        // Vérifier que nous avons un tableau
        if (!is_array($elements)) {
            return new WP_Error('invalid_data_structure', 'Les données JSON doivent être un tableau d\'éléments');
        }

        // Valider et nettoyer chaque élément
        $cleaned_elements = [];
        $errors = [];

        foreach ($elements as $index => $element) {
            if (!is_array($element)) {
                $errors[] = "Élément {$index}: données non valides (doit être un objet)";
                continue;
            }

            // Valider les champs requis
            if (!isset($element['id']) || !isset($element['type'])) {
                $errors[] = "Élément {$index}: champs 'id' et 'type' requis manquants";
                continue;
            }

            // Nettoyer les propriétés de l'élément selon son type
            $cleaned_element = $this->sanitize_element_properties($element);

            // Valider l'élément nettoyé
            $element_errors = $this->validate_element_data($cleaned_element);
            if (!empty($element_errors)) {
                $errors = array_merge(
                    $errors,
                    array_map(
                        function ($err) use ($index) {
                            return "Élément {$index}: {$err}";
                        },
                        $element_errors
                    )
                );
                // Continuer avec l'élément quand même (on nettoie ce qu'on peut)
            }

            $cleaned_elements[] = $cleaned_element;
        }

        // Log des erreurs trouvées
        if (!empty($errors)) {
        }


        return $cleaned_elements;
    }

    /**
     * Tenter de réparer une chaîne JSON défaillante
     */
    private function repair_json_string($json_string)
    {
        // Essayer de corriger les problèmes courants

        // 1. Caractères de contrôle ou invisibles
        $json_string = preg_replace('/[\x00-\x1F\x7F]/', '', $json_string);

        // 2. Guillemets non échappés dans les valeurs (risqué, mais peut aider)
        // Cette réparation est basique et peut introduire des erreurs

        // 3. Tester si c'est maintenant valide
        json_decode($json_string, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json_string;
        }

        // Si rien ne marche, retourner false
        return false;
    }

    /**
     * Obtenir les propriétés par défaut pour un élément
     */
    public function get_default_element_properties($element_type = 'text')
    {
        // Propriétés communes à TOUS les éléments (sans propriétés spécifiques)
        $common_defaults = [
            // Propriétés de base
            'x' => 50,
            'y' => 50,
            'width' => 100,
            'height' => 50,
            'opacity' => 100,
            'rotation' => 0,
            'scale' => 100,
            'visible' => true,

            // Apparence
            'backgroundColor' => 'transparent',
            'borderColor' => 'transparent',
            'borderWidth' => 0,
            'borderStyle' => 'solid',
            'borderRadius' => 0,

            // Typographie
            'color' => '#1e293b',
            'fontFamily' => 'Inter, sans-serif',
            'fontSize' => 14,
            'fontWeight' => 'normal',
            'fontStyle' => 'normal',
            'textAlign' => 'left',
            'textDecoration' => 'none',
            'lineHeight' => 1.2,

            // Contenu
            'content' => 'Texte',

            // Images
            'src' => '',
            'alt' => '',
            'objectFit' => 'cover',
            'imageUrl' => '',

            // Effets
            'shadow' => false,
            'shadowColor' => '#000000',
            'shadowOffsetX' => 2,
            'shadowOffsetY' => 2,
            'brightness' => 100,
            'contrast' => 100,
            'saturate' => 100,

            // Propriétés d'espacement et mise en page
            'spacing' => 8,
            'layout' => 'vertical',
            'alignment' => 'left',
            'fit' => 'contain',

            // Propriétés pour les champs et options
            'fields' => [],
            'showLabel' => false,
            'labelText' => ''
        ];

        // Propriétés spécifiques selon le type d'élément
        $type_specific_defaults = [
            'text' => [
                'width' => 150,
                'height' => 30
            ],
            'image' => [
                'width' => 150,
                'height' => 100
            ],
            'rectangle' => [
                'backgroundColor' => '#f1f5f9',
                'borderWidth' => 1,
                'width' => 150,
                'height' => 80
            ],
            'product_table' => [
                'width' => 300,
                'height' => 150,
                // Propriétés spécifiques aux tableaux
                'showHeaders' => true,
                'showBorders' => true,
                'showLabels' => true,
                'tableStyle' => 'default',
                'headers' => ['Produit', 'Qté', 'Prix'],
                'dataSource' => 'order_items',
                'columns' => [
                    'image' => true,
                    'name' => true,
                    'sku' => false,
                    'quantity' => true,
                    'price' => true,
                    'total' => true
                ],
                'showSubtotal' => false,
                'showShipping' => true,
                'showTaxes' => true,
                'showDiscount' => false,
                'showTotal' => false
            ],
            'customer_info' => [
                'width' => 200,
                'height' => 100
            ],
            'company_logo' => [
                'width' => 100,
                'height' => 60
            ],
            'order_number' => [
                'width' => 150,
                'height' => 30
            ],
            'company_info' => [
                'width' => 200,
                'height' => 80
            ],
            'document_type' => [
                'width' => 120,
                'height' => 40,
                'documentType' => 'invoice'
            ],
            'watermark' => [
                'width' => 300,
                'height' => 200,
                'opacity' => 10,
                'content' => 'CONFIDENTIEL'
            ],
            'progress-bar' => [
                'width' => 200,
                'height' => 20,
                'progressColor' => '#3b82f6',
                'progressValue' => 75
            ],
            'barcode' => [
                'width' => 150,
                'height' => 60,
                'lineColor' => '#64748b',
                'lineWidth' => 2
            ],
            'qrcode' => [
                'width' => 80,
                'height' => 80,
                'lineColor' => '#64748b',
                'lineWidth' => 2
            ],
            'icon' => [
                'width' => 50,
                'height' => 50
            ],
            'line' => [
                'width' => 200,
                'height' => 2
            ]
        ];

        // Fusionner les propriétés communes avec les propriétés spécifiques du type
        $defaults = $common_defaults;
        if (isset($type_specific_defaults[$element_type])) {
            $defaults = array_merge($defaults, $type_specific_defaults[$element_type]);
        }

        return $defaults;
    }

    /**
     * Nettoyer et sanitiser les propriétés d'un élément
     */
    public function sanitize_element_properties($element_data, $element_type = 'text')
    {
        // Obtenir les valeurs par défaut complètes selon le type
        $defaults = $this->get_default_element_properties($element_type);

        // Fusionner les données fournies avec les valeurs par défaut
        $element_data = array_merge($defaults, $element_data);

        $sanitized = [];

        // Sanitisation des champs de base (communs à tous les éléments)
        $sanitized['id'] = sanitize_text_field($element_data['id'] ?? '');
        $sanitized['type'] = sanitize_text_field($element_data['type'] ?? 'text');

        // Sanitisation des coordonnées et dimensions
        $sanitized['x'] = floatval($element_data['x']);
        $sanitized['y'] = floatval($element_data['y']);
        $sanitized['width'] = max(1, floatval($element_data['width']));
        $sanitized['height'] = max(1, floatval($element_data['height']));

        // Propriétés communes à tous les éléments
        $common_numeric = [
            'opacity' => [0, 100, 100],
            'rotation' => [-180, 180, 0],
            'scale' => [10, 200, 100],
            'borderWidth' => [0, 20, 0],
            'borderRadius' => [0, 100, 0],
            'brightness' => [0, 200, 100],
            'contrast' => [0, 200, 100],
            'saturate' => [0, 200, 100],
            'shadowOffsetX' => [-50, 50, 2],
            'shadowOffsetY' => [-50, 50, 2]
        ];

        $common_colors = ['color', 'backgroundColor', 'borderColor', 'shadowColor'];
        $common_text = ['fontFamily', 'fontWeight', 'fontStyle', 'textAlign', 'textDecoration'];
        $common_boolean = ['visible', 'shadow'];

        // Appliquer les propriétés communes
        foreach ($common_numeric as $field => $constraints) {
            list($min, $max, $default) = $constraints;
            $value = $element_data[$field] ?? $default;
            $sanitized[$field] = max($min, min($max, floatval($value)));
        }

        foreach ($common_colors as $field) {
            $color = $element_data[$field] ?? '';
            if ($this->is_valid_color($color)) {
                $sanitized[$field] = $color;
            } else {
                $sanitized[$field] = $defaults[$field] ?? 'transparent';
            }
        }

        foreach ($common_text as $field) {
            if (isset($element_data[$field])) {
                $sanitized[$field] = sanitize_text_field($element_data[$field]);
            }
        }

        foreach ($common_boolean as $field) {
            $sanitized[$field] = (bool) ($element_data[$field] ?? false);
        }

        // Propriétés spécifiques selon le type d'élément
        switch ($element_type) {
            case 'text':
            case 'dynamic_text':
            case 'conditional_text':
                $sanitized['content'] = sanitize_text_field($element_data['content'] ?? '');
                $sanitized['fontSize'] = max(8, min(72, floatval($element_data['fontSize'] ?? 14)));
                $sanitized['lineHeight'] = max(0.5, min(3, floatval($element_data['lineHeight'] ?? 1.2)));
                break;

            case 'image':
            case 'logo':
                $sanitized['src'] = sanitize_text_field($element_data['src'] ?? '');
                $sanitized['alt'] = sanitize_text_field($element_data['alt'] ?? '');
                $sanitized['objectFit'] = in_array($element_data['objectFit'] ?? '', ['cover', 'contain', 'fill', 'none', 'scale-down'])
                ? $element_data['objectFit'] : 'cover';
                break;

            case 'product_table':
                // Propriétés spécifiques aux tableaux
                $sanitized['fontSize'] = max(8, min(72, floatval($element_data['fontSize'] ?? 12)));
                $sanitized['lineHeight'] = max(0.5, min(3, floatval($element_data['lineHeight'] ?? 1.2)));
                $sanitized['spacing'] = max(0, min(50, floatval($element_data['spacing'] ?? 8)));

                // Propriétés booléennes spécifiques aux tableaux
                $table_booleans = ['showHeaders', 'showBorders', 'showSubtotal', 'showShipping', 'showTaxes', 'showDiscount', 'showTotal'];
                foreach ($table_booleans as $field) {
                    $sanitized[$field] = (bool) ($element_data[$field] ?? false);
                }

                // Colonnes du tableau
                if (isset($element_data['columns']) && is_array($element_data['columns'])) {
                    $sanitized['columns'] = array_map('boolval', $element_data['columns']);
                }

                // Headers du tableau
                if (isset($element_data['headers']) && is_array($element_data['headers'])) {
                    $sanitized['headers'] = array_map(
                        function ($header) {
                            return sanitize_text_field($header);
                        },
                        $element_data['headers']
                    );
                }

                // Style du tableau
                $allowed_table_styles = ['default', 'classic', 'striped', 'bordered', 'minimal', 'modern', 'blue_ocean', 'emerald_forest', 'sunset_orange', 'royal_purple', 'rose_pink', 'teal_aqua', 'crimson_red', 'amber_gold', 'indigo_night', 'slate_gray', 'coral_sunset', 'mint_green', 'violet_dream', 'sky_blue', 'forest_green', 'ruby_red'];
                $sanitized['tableStyle'] = in_array($element_data['tableStyle'] ?? '', $allowed_table_styles)
                ? $element_data['tableStyle'] : 'default';

                // Source de données
                $sanitized['dataSource'] = sanitize_text_field($element_data['dataSource'] ?? 'order_items');

                break;

            case 'customer_info':
            case 'company_info':
                $sanitized['fontSize'] = max(8, min(72, floatval($element_data['fontSize'] ?? 12)));
                $sanitized['lineHeight'] = max(0.5, min(3, floatval($element_data['lineHeight'] ?? 1.2)));
                $sanitized['spacing'] = max(0, min(50, floatval($element_data['spacing'] ?? 8)));

                $sanitized['layout'] = in_array($element_data['layout'] ?? '', ['vertical', 'horizontal'])
                ? $element_data['layout'] : 'vertical';
                $sanitized['alignment'] = in_array($element_data['alignment'] ?? '', ['left', 'center', 'right'])
                ? $element_data['alignment'] : 'left';
                $sanitized['showLabels'] = (bool) ($element_data['showLabels'] ?? true);
                $sanitized['labelStyle'] = in_array($element_data['labelStyle'] ?? '', ['normal', 'bold', 'uppercase'])
                ? $element_data['labelStyle'] : 'normal';

                if (isset($element_data['fields']) && is_array($element_data['fields'])) {
                    $sanitized['fields'] = array_map(
                        function ($field) {
                            return sanitize_text_field($field);
                        },
                        $element_data['fields']
                    );
                }
                break;

            case 'order_number':
            case 'document_type':
                $sanitized['fontSize'] = max(8, min(72, floatval($element_data['fontSize'] ?? 18)));
                $sanitized['textAlign'] = in_array($element_data['textAlign'] ?? '', ['left', 'center', 'right', 'justify'])
                ? $element_data['textAlign'] : 'center';

                if ($element_type === 'order_number') {
                    $sanitized['format'] = sanitize_text_field($element_data['format'] ?? 'Commande #{order_number} - {order_date}');
                    $sanitized['showLabel'] = (bool) ($element_data['showLabel'] ?? true);
                    $sanitized['labelText'] = sanitize_text_field($element_data['labelText'] ?? 'N° de commande:');
                } elseif ($element_type === 'document_type') {
                    $sanitized['documentType'] = in_array($element_data['documentType'] ?? '', ['invoice', 'quote', 'receipt', 'order', 'credit_note'])
                    ? $element_data['documentType'] : 'invoice';
                }
                break;

            case 'company_logo':
                $sanitized['imageUrl'] = sanitize_text_field($element_data['imageUrl'] ?? '');
                $sanitized['fit'] = in_array($element_data['fit'] ?? '', ['cover', 'contain', 'fill', 'none', 'scale-down'])
                ? $element_data['fit'] : 'contain';
                break;

            case 'progress-bar':
                $sanitized['progressValue'] = max(0, min(100, floatval($element_data['progressValue'] ?? 75)));
                $sanitized['progressColor'] = $this->is_valid_color($element_data['progressColor'] ?? '')
                ? $element_data['progressColor'] : '#3b82f6';
                break;

            case 'barcode':
            case 'qrcode':
                $sanitized['lineWidth'] = max(1, min(10, floatval($element_data['lineWidth'] ?? 2)));
                $sanitized['lineColor'] = $this->is_valid_color($element_data['lineColor'] ?? '')
                ? $element_data['lineColor'] : '#64748b';
                break;

            case 'rectangle':
            case 'line':
            case 'shape-rectangle':
            case 'shape-circle':
            case 'shape-line':
            case 'shape-arrow':
            case 'shape-triangle':
            case 'shape-star':
            case 'divider':
                // Ces éléments utilisent principalement les propriétés communes
                break;

            case 'watermark':
                $sanitized['content'] = sanitize_text_field($element_data['content'] ?? 'CONFIDENTIEL');
                $sanitized['opacity'] = max(0, min(100, floatval($element_data['opacity'] ?? 10)));
                break;

            default:
                // Pour les types inconnus, on garde seulement les propriétés communes
                break;
        }

        // Nettoyer les propriétés qui ne devraient pas exister pour ce type
        $this->remove_invalid_properties($sanitized, $element_type);

        return $sanitized;
    }

    /**
     * Supprime les propriétés invalides selon le type d'élément
     */
    private function remove_invalid_properties(&$element_data, $element_type)
    {
        // Propriétés qui ne devraient exister que pour les tableaux
        $table_only_properties = [
            'showHeaders', 'showBorders', 'showSubtotal', 'showShipping', 'showTaxes',
            'showDiscount', 'showTotal', 'columns', 'headers', 'tableStyle', 'dataSource'
        ];

        // Propriétés qui ne devraient exister que pour les éléments d'information client/entreprise
        $info_only_properties = [
            'fields', 'showLabels', 'labelStyle', 'layout', 'alignment'
        ];

        // Propriétés qui ne devraient exister que pour les éléments texte
        $text_only_properties = [
            'content', 'lineHeight'
        ];

        // Propriétés qui ne devraient exister que pour les images
        $image_only_properties = [
            'src', 'alt', 'objectFit'
        ];

        // Propriétés qui ne devraient exister que pour les logos d'entreprise
        $logo_only_properties = [
            'imageUrl', 'fit'
        ];

        // Propriétés qui ne devraient exister que pour les barres de progression
        $progress_only_properties = [
            'progressValue', 'progressColor'
        ];

        // Propriétés qui ne devraient exister que pour les codes-barres/QR
        $barcode_only_properties = [
            'lineWidth', 'lineColor'
        ];

        // Propriétés qui ne devraient exister que pour les numéros de commande
        $order_only_properties = [
            'format', 'showLabel', 'labelText'
        ];

        // Propriétés qui ne devraient exister que pour les types de document
        $document_only_properties = [
            'documentType'
        ];

        // Propriétés qui ne devraient exister que pour les filigranes
        $watermark_only_properties = [
            'content'
        ];

        // Supprimer les propriétés selon le type d'élément
        switch ($element_type) {
            case 'text':
            case 'dynamic_text':
            case 'conditional_text':
            case 'counter':
            case 'date_dynamic':
            case 'currency':
            case 'formula':
                // Supprimer les propriétés qui ne sont pas pour le texte
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'image':
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $text_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'product_table':
                $properties_to_remove = array_merge(
                    $info_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'customer_info':
            case 'company_info':
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $text_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'company_logo':
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $text_only_properties,
                    $image_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'order_number':
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $text_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'document_type':
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $text_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'progress-bar':
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $text_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'barcode':
            case 'qrcode':
            case 'qrcode-dynamic':
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $text_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $order_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'rectangle':
            case 'line':
            case 'shape-rectangle':
            case 'shape-circle':
            case 'shape-line':
            case 'shape-arrow':
            case 'shape-triangle':
            case 'shape-star':
            case 'divider':
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $text_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;

            case 'watermark':
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $document_only_properties
                );
                break;

            default:
                // Pour les types inconnus, garder seulement les propriétés communes
                $properties_to_remove = array_merge(
                    $table_only_properties,
                    $info_only_properties,
                    $text_only_properties,
                    $image_only_properties,
                    $logo_only_properties,
                    $progress_only_properties,
                    $barcode_only_properties,
                    $order_only_properties,
                    $document_only_properties,
                    $watermark_only_properties
                );
                break;
        }

        // Supprimer les propriétés invalides
        foreach ($properties_to_remove as $property) {
            unset($element_data[$property]);
        }
    }

    /**
     * Valider un format de couleur (support étendu)
     */
    private function is_valid_color($color)
    {
        if (!$color || $color === 'transparent') {
            return true;
        }

        // Couleurs hexadécimales
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return true;
        }

        // Couleurs RGB/RGBA
        if (preg_match('/^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/', $color, $matches)) {
            foreach ($matches as $i => $value) {
                if ($i > 0 && ($value < 0 || $value > 255)) {
                    return false;
                }
            }
            return true;
        }

        if (preg_match('/^rgba\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3}),\s*([0-1](\.\d+)?)\)$/', $color, $matches)) {
            if ($matches[4] < 0 || $matches[4] > 1) {
                return false;
            }
            foreach ([$matches[1], $matches[2], $matches[3]] as $value) {
                if ($value < 0 || $value > 255) {
                    return false;
                }
            }
            return true;
        }

        // Couleurs HSL/HSLA
        if (preg_match('/^hsl\((\d{1,3}),\s*(\d{1,3})%,\s*(\d{1,3})%\)$/', $color, $matches)) {
            if ($matches[1] < 0 || $matches[1] > 360) {
                return false;
            }
            if ($matches[2] < 0 || $matches[2] > 100) {
                return false;
            }
            if ($matches[3] < 0 || $matches[3] > 100) {
                return false;
            }
            return true;
        }

        if (preg_match('/^hsla\((\d{1,3}),\s*(\d{1,3})%,\s*(\d{1,3})%,\s*([0-1](\.\d+)?)\)$/', $color, $matches)) {
            if ($matches[1] < 0 || $matches[1] > 360) {
                return false;
            }
            if ($matches[2] < 0 || $matches[2] > 100) {
                return false;
            }
            if ($matches[3] < 0 || $matches[3] > 100) {
                return false;
            }
            if ($matches[4] < 0 || $matches[4] > 1) {
                return false;
            }
            return true;
        }

        // Noms de couleurs CSS courants
        $css_color_names = [
            'black', 'silver', 'gray', 'white', 'maroon', 'red', 'purple', 'fuchsia', 'green',
            'lime', 'olive', 'yellow', 'navy', 'blue', 'teal', 'aqua', 'orange', 'aliceblue',
            'antiquewhite', 'aquamarine', 'azure', 'beige', 'bisque', 'blanchedalmond',
            'blueviolet', 'brown', 'burlywood', 'cadetblue', 'chartreuse', 'chocolate',
            'coral', 'cornflowerblue', 'cornsilk', 'crimson', 'cyan', 'darkblue', 'darkcyan',
            'darkgoldenrod', 'darkgray', 'darkgreen', 'darkgrey', 'darkkhaki', 'darkmagenta',
            'darkolivegreen', 'darkorange', 'darkorchid', 'darkred', 'darksalmon', 'darkseagreen',
            'darkslateblue', 'darkslategray', 'darkslategrey', 'darkturquoise', 'darkviolet',
            'deeppink', 'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick',
            'floralwhite', 'forestgreen', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod',
            'greenyellow', 'grey', 'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory',
            'khaki', 'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue',
            'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgray', 'lightgreen',
            'lightgrey', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue',
            'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow', 'limegreen',
            'linen', 'magenta', 'mediumaquamarine', 'mediumblue', 'mediumorchid', 'mediumpurple',
            'mediumseagreen', 'mediumslateblue', 'mediumspringgreen', 'mediumturquoise',
            'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose', 'moccasin', 'navajowhite',
            'oldlace', 'olivedrab', 'orangered', 'orchid', 'palegoldenrod', 'palegreen',
            'paleturquoise', 'palevioletred', 'papayawhip', 'peachpuff', 'peru', 'pink',
            'plum', 'powderblue', 'rosybrown', 'royalblue', 'saddlebrown', 'salmon',
            'sandybrown', 'seagreen', 'seashell', 'sienna', 'skyblue', 'slateblue',
            'slategray', 'slategrey', 'snow', 'springgreen', 'steelblue', 'tan', 'thistle',
            'tomato', 'turquoise', 'violet', 'wheat', 'whitesmoke', 'yellowgreen'
        ];

        if (in_array(strtolower($color), $css_color_names)) {
            return true;
        }

        return false;
    }

    /**
     * Sauvegarder les éléments d'un template (implémentation temporaire avec WP options)
     */
    private function save_template_elements_to_db($template_id, $elements)
    {
        $option_key = 'pdf_builder_template_' . $template_id . '_elements';
        $json_data = wp_json_encode($elements);

        if ($json_data === false) {
            return new WP_Error('json_encode_error', 'Erreur lors de l\'encodage JSON des éléments');
        }

        $result = update_option($option_key, $json_data, false);

        if ($result === false) {
            return new WP_Error('save_error', 'Erreur lors de la sauvegarde des éléments');
        }

        return true;
    }

    /**
     * Charger les éléments d'un template depuis la base de données
     */
    private function get_template_elements_from_db($template_id)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Récupérer les données depuis la table pdf_builder_templates
        $template_data = $wpdb->get_var($wpdb->prepare(
            "SELECT template_data FROM $table_templates WHERE id = %d",
            $template_id
        ));

        if (!$template_data) {
            return [];
        }

        // Décoder le JSON avec gestion des erreurs
        $decoded_data = json_decode($template_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('PDF BUILDER - Erreur décodage JSON template ' . $template_id . ': ' . json_last_error_msg());
            return [];
        }

        // Extraire les éléments du tableau (le format sauvegardé est un tableau d'éléments)
        if (is_array($decoded_data)) {
            return $decoded_data;
        }

        // Si c'est un objet avec une clé 'elements', l'utiliser
        if (is_array($decoded_data) && isset($decoded_data['elements'])) {
            return $decoded_data['elements'];
        }

        return [];
    }

    /**
     * Calculer les nouvelles coordonnées après déplacement
     */
    public function calculate_drag_position($element, $delta_x, $delta_y, $canvas_bounds = null)
    {
        $new_x = $element['position']['x'] + $delta_x;
        $new_y = $element['position']['y'] + $delta_y;

        // Appliquer les contraintes du canvas si fournies
        if ($canvas_bounds) {
            $new_x = max(0, min($new_x, $canvas_bounds['width'] - $element['size']['width']));
            $new_y = max(0, min($new_y, $canvas_bounds['height'] - $element['size']['height']));
        }

        return [
            'x' => round($new_x, 2),
            'y' => round($new_y, 2)
        ];
    }

    /**
     * Calculer les nouvelles dimensions après redimensionnement
     */
    public function calculate_resize_dimensions($element, $handle, $delta_x, $delta_y, $constraints = null)
    {
        $current_width = $element['size']['width'];
        $current_height = $element['size']['height'];
        $current_x = $element['position']['x'];
        $current_y = $element['position']['y'];

        $new_width = $current_width;
        $new_height = $current_height;
        $new_x = $current_x;
        $new_y = $current_y;

        // Appliquer les contraintes par défaut
        $min_width = $constraints['min_width'] ?? 10;
        $min_height = $constraints['min_height'] ?? 10;
        $max_width = $constraints['max_width'] ?? 2000;
        $max_height = $constraints['max_height'] ?? 2000;

        switch ($handle) {
            case 'nw': // Nord-Ouest
                $new_width = max($min_width, $current_width - $delta_x);
                $new_height = max($min_height, $current_height - $delta_y);
                $new_x = $current_x + ($current_width - $new_width);
                $new_y = $current_y + ($current_height - $new_height);
                break;

            case 'ne': // Nord-Est
                $new_width = max($min_width, $current_width + $delta_x);
                $new_height = max($min_height, $current_height - $delta_y);
                $new_y = $current_y + ($current_height - $new_height);
                break;

            case 'sw': // Sud-Ouest
                $new_width = max($min_width, $current_width - $delta_x);
                $new_height = max($min_height, $current_height + $delta_y);
                $new_x = $current_x + ($current_width - $new_width);
                break;

            case 'se': // Sud-Est
                $new_width = max($min_width, $current_width + $delta_x);
                $new_height = max($min_height, $current_height + $delta_y);
                break;

            case 'n': // Nord
                $new_height = max($min_height, $current_height - $delta_y);
                $new_y = $current_y + ($current_height - $new_height);
                break;

            case 's': // Sud
                $new_height = max($min_height, $current_height + $delta_y);
                break;

            case 'w': // Ouest
                $new_width = max($min_width, $current_width - $delta_x);
                $new_x = $current_x + ($current_width - $new_width);
                break;

            case 'e': // Est
                $new_width = max($min_width, $current_width + $delta_x);
                break;
        }

        // Appliquer les contraintes maximales
        $new_width = min($new_width, $max_width);
        $new_height = min($new_height, $max_height);

        return [
            'position' => [
                'x' => round($new_x, 2),
                'y' => round($new_y, 2)
            ],
            'size' => [
                'width' => round($new_width, 2),
                'height' => round($new_height, 2)
            ]
        ];
    }

    /**
     * Détecter la poignée de redimensionnement à une position donnée
     */
    public function get_resize_handle($element, $mouse_x, $mouse_y, $threshold = 10)
    {
        $element_x = $element['position']['x'];
        $element_y = $element['position']['y'];
        $element_width = $element['size']['width'];
        $element_height = $element['size']['height'];

        $near_left = $mouse_x >= $element_x - $threshold && $mouse_x <= $element_x + $threshold;
        $near_right = $mouse_x >= $element_x + $element_width - $threshold && $mouse_x <= $element_x + $element_width + $threshold;
        $near_top = $mouse_y >= $element_y - $threshold && $mouse_y <= $element_y + $threshold;
        $near_bottom = $mouse_y >= $element_y + $element_height - $threshold && $mouse_y <= $element_y + $element_height + $threshold;

        if ($near_top && $near_left) {
            return 'nw';
        }
        if ($near_top && $near_right) {
            return 'ne';
        }
        if ($near_bottom && $near_left) {
            return 'sw';
        }
        if ($near_bottom && $near_right) {
            return 'se';
        }
        if ($near_top) {
            return 'n';
        }
        if ($near_bottom) {
            return 's';
        }
        if ($near_left) {
            return 'w';
        }
        if ($near_right) {
            return 'e';
        }

        return null;
    }

    /**
     * Obtenir le curseur approprié pour une poignée de redimensionnement
     */
    public function get_resize_cursor($handle)
    {
        $cursors = [
            'nw' => 'nw-resize',
            'ne' => 'ne-resize',
            'sw' => 'sw-resize',
            'se' => 'se-resize',
            'n' => 'n-resize',
            's' => 's-resize',
            'w' => 'w-resize',
            'e' => 'e-resize'
        ];

        return $cursors[$handle] ?? 'default';
    }

    /**
     * Vérifier les collisions entre éléments
     */
    public function check_collisions($element, $other_elements, $tolerance = 0)
    {
        $element_bounds = [
            'x' => $element['position']['x'] - $tolerance,
            'y' => $element['position']['y'] - $tolerance,
            'width' => $element['size']['width'] + (2 * $tolerance),
            'height' => $element['size']['height'] + (2 * $tolerance)
        ];

        $collisions = [];

        foreach ($other_elements as $other_element) {
            if ($other_element['id'] === $element['id']) {
                continue;
            }

            $other_bounds = [
                'x' => $other_element['position']['x'],
                'y' => $other_element['position']['y'],
                'width' => $other_element['size']['width'],
                'height' => $other_element['size']['height']
            ];

            if ($this->rectangles_overlap($element_bounds, $other_bounds)) {
                $collisions[] = $other_element['id'];
            }
        }

        return $collisions;
    }

    /**
     * Vérifier si deux rectangles se chevauchent
     */
    private function rectangles_overlap($rect1, $rect2)
    {
        return !(
            $rect1['x'] + $rect1['width'] < $rect2['x'] ||
            $rect2['x'] + $rect2['width'] < $rect1['x'] ||
            $rect1['y'] + $rect1['height'] < $rect2['y'] ||
            $rect2['y'] + $rect2['height'] < $rect1['y']
        );
    }

    /**
     * Aligner un élément sur la grille
     */
    public function snap_to_grid($position, $grid_size = 10)
    {
        return [
            'x' => round($position['x'] / $grid_size) * $grid_size,
            'y' => round($position['y'] / $grid_size) * $grid_size
        ];
    }

    /**
     * Sauvegarder les éléments du canvas
     */
    public function save_canvas_elements($template_id, $elements)
    {
        // Valider et sanitiser tous les éléments
        $sanitized_elements = [];
        foreach ($elements as $element) {
            $errors = $this->validate_element_data($element);
            if (!empty($errors)) {
                return new WP_Error('validation_error', 'Invalid element data: ' . implode(', ', $errors));
            }

            // Sanitiser les propriétés de l'élément
            $sanitized_elements[] = $this->sanitize_element_properties($element, $element['type']);
        }

        // Charger le template existant
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            return new WP_Error('template_not_found', 'Template not found');
        }

        // Décoder les données JSON existantes avec validation robuste
        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Invalid JSON in existing template data: ' . json_last_error_msg());
        }

        // Valider et nettoyer les données existantes si elles contiennent des éléments
        if (isset($template_data['elements']) && is_array($template_data['elements'])) {
            $validated_existing = $this->validate_and_clean_json_elements(
                wp_json_encode($template_data['elements']),
                "template_save_existing_{$template_id}"
            );
            if (!is_wp_error($validated_existing)) {
                $template_data['elements'] = $validated_existing;
            }
            // Si la validation échoue, garder les données originales
        }

        // Mettre à jour les éléments dans les données du template
        $template_data['elements'] = $sanitized_elements;

        // Réencoder en JSON
        $updated_template_data = wp_json_encode($template_data);
        if ($updated_template_data === false) {
            return new WP_Error('json_encode_error', 'Failed to encode template data');
        }

        // Sauvegarder en base de données
        $result = $wpdb->update(
            $table_templates,
            array('template_data' => $updated_template_data),
            array('id' => $template_id),
            array('%s'),
            array('%d')
        );

        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update template in database');
        }

        return true;
    }

    /**
     * Charger les éléments du canvas
     */
    public function load_canvas_elements($template_id)
    {

        // Charger le template depuis la base de données
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );


        if (!$template) {
            return [];
        }

        // Décoder les données JSON du template avec validation robuste
        $template_data = json_decode($template['template_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }


        // Extraire et valider les éléments du canvas
        $elements = [];
        if (isset($template_data['elements']) && is_array($template_data['elements'])) {
            // Utiliser la validation robuste sur les éléments chargés
            $validated_elements = $this->validate_and_clean_json_elements(
                wp_json_encode($template_data['elements']),
                "template_load_{$template_id}"
            );

            if (is_wp_error($validated_elements)) {
                // Retourner les éléments bruts si la validation échoue (pour éviter de perdre des données)
                $elements = $template_data['elements'];
            } else {
                $elements = $validated_elements;
            }
        } else {
        }

        return $elements;
    }
}
