<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * Exemple d'utilisation des propriétés uniformes des éléments
 * PDF Builder Pro - Backend Properties Example
 */



// Inclure le gestionnaire d'éléments
require_once plugin_dir_path(__FILE__) . 'managers/PDF_Builder_Canvas_Elements_Manager.php';

/**
 * Exemple de création d'un élément avec toutes les propriétés disponibles
 */
function pdf_builder_create_example_element() {
    $elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();

    // Créer un élément texte avec toutes les propriétés
    $text_element = [
        'id' => 'example_text_1',
        'type' => 'text',
        'x' => 100,
        'y' => 50,
        'width' => 200,
        'height' => 40,

        // Propriétés d'apparence
        'backgroundColor' => '#f0f9ff',
        'borderColor' => '#0ea5e9',
        'borderWidth' => 2,
        'borderStyle' => 'solid',
        'borderRadius' => 8,
        'opacity' => 90,

        // Propriétés de typographie
        'color' => '#1e40af',
        'fontFamily' => 'Arial, sans-serif',
        'fontSize' => 16,
        'fontWeight' => 'bold',
        'fontStyle' => 'normal',
        'textAlign' => 'center',
        'textDecoration' => 'none',

        // Contenu
        'content' => 'Exemple de texte avec toutes les propriétés',

        // Effets
        'shadow' => true,
        'shadowColor' => '#000000',
        'shadowOffsetX' => 2,
        'shadowOffsetY' => 2,
        'brightness' => 105,
        'contrast' => 110,
        'saturate' => 100,

        // Transformation
        'rotation' => 0,
        'scale' => 100,

        // Autres propriétés
        'visible' => true,
        'spacing' => 8,
        'layout' => 'vertical',
        'alignment' => 'center',
        'fit' => 'contain'
    ];

    // Créer un élément image avec toutes les propriétés
    $image_element = [
        'id' => 'example_image_1',
        'type' => 'image',
        'x' => 50,
        'y' => 150,
        'width' => 150,
        'height' => 100,

        // Propriétés d'apparence
        'backgroundColor' => 'transparent',
        'borderColor' => '#e5e7eb',
        'borderWidth' => 1,
        'borderStyle' => 'solid',
        'borderRadius' => 4,
        'opacity' => 100,

        // Propriétés d'image
        'src' => 'https://example.com/image.jpg',
        'alt' => 'Image d\'exemple',
        'objectFit' => 'cover',
        'imageUrl' => 'https://example.com/image.jpg',

        // Effets
        'brightness' => 100,
        'contrast' => 100,
        'saturate' => 100,

        // Transformation
        'rotation' => 0,
        'scale' => 100,

        'visible' => true
    ];

    // Créer un élément tableau avec toutes les propriétés
    $table_element = [
        'id' => 'example_table_1',
        'type' => 'product_table',
        'x' => 50,
        'y' => 300,
        'width' => 400,
        'height' => 200,

        // Propriétés d'apparence
        'backgroundColor' => '#ffffff',
        'borderColor' => '#e5e7eb',
        'borderWidth' => 1,
        'borderStyle' => 'solid',
        'borderRadius' => 0,
        'opacity' => 100,

        // Propriétés de typographie
        'color' => '#374151',
        'fontFamily' => 'Arial, sans-serif',
        'fontSize' => 12,
        'fontWeight' => 'normal',
        'textAlign' => 'left',

        // Propriétés spécifiques aux tableaux
        'showHeaders' => true,
        'showBorders' => true,
        'headers' => ['Produit', 'Qté', 'Prix', 'Total'],
        'dataSource' => 'order_items',
        'columns' => [
            'image' => true,
            'name' => true,
            'sku' => false,
            'quantity' => true,
            'price' => true,
            'total' => true
        ],
        'showSubtotal' => true,
        'showShipping' => true,
        'showTaxes' => true,
        'showDiscount' => false,
        'showTotal' => false,

        'visible' => true
    ];

    // Valider les éléments
    $errors_text = $elements_manager->validate_element_data($text_element);
    $errors_image = $elements_manager->validate_element_data($image_element);
    $errors_table = $elements_manager->validate_element_data($table_element);

    if (!empty($errors_text) || !empty($errors_image) || !empty($errors_table)) {
        return new WP_Error('validation_failed', 'Erreurs de validation: ' .
            implode(', ', array_merge($errors_text, $errors_image, $errors_table)));
    }

    // Sanitiser les éléments
    $sanitized_text = $elements_manager->sanitize_element_properties($text_element);
    $sanitized_image = $elements_manager->sanitize_element_properties($image_element);
    $sanitized_table = $elements_manager->sanitize_element_properties($table_element);

    return [
        'text_element' => $sanitized_text,
        'image_element' => $sanitized_image,
        'table_element' => $sanitized_table
    ];
}

/**
 * Exemple de récupération des propriétés par défaut pour un type d'élément
 */
function pdf_builder_get_default_properties_example() {
    $elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();

    // Obtenir les propriétés par défaut pour différents types d'éléments
    $text_defaults = $elements_manager->get_default_element_properties('text');
    $image_defaults = $elements_manager->get_default_element_properties('image');
    $table_defaults = $elements_manager->get_default_element_properties('product_table');

    return [
        'text_defaults' => $text_defaults,
        'image_defaults' => $image_defaults,
        'table_defaults' => $table_defaults
    ];
}

/**
 * Exemple de sauvegarde et chargement d'éléments
 */
function pdf_builder_save_load_example() {
    $elements_manager = PDF_Builder_Canvas_Elements_Manager::getInstance();

    // Créer des éléments d'exemple
    $example_elements = pdf_builder_create_example_element();
    if (is_wp_error($example_elements)) {
        return $example_elements;
    }

    $template_id = 'example_template_123';
    $elements = array_values($example_elements);

    // Sauvegarder les éléments
    $save_result = $elements_manager->save_canvas_elements($template_id, $elements);
    if (is_wp_error($save_result)) {
        return $save_result;
    }

    // Charger les éléments
    $loaded_elements = $elements_manager->load_canvas_elements($template_id);

    return [
        'saved_count' => count($elements),
        'loaded_count' => count($loaded_elements),
        'elements_match' => json_encode($elements) === json_encode($loaded_elements)
    ];
}
