<?php

/**
 * PDF Builder Pro - Template Processor
 * Responsable du traitement et de la gestion des templates
 */

namespace PDF_Builder\Admin\Processors;

use Exception;

/**
 * Classe responsable du traitement des templates PDF
 */
class TemplateProcessor
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
    }

    /**
     * Transforme les éléments pour React
     */
    public function transformElementsForReact($elements)
    {
        return $this->admin->transformElementsForReact($elements);
    }

    /**
     * Charge un template de manière robuste
     */
    public function loadTemplateRobust($template_id)
    {
        try {
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';

            // Vérifier que la table existe
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
                return $this->getDefaultInvoiceTemplate();
            }

            $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
            if (!$template) {
                return $this->getDefaultInvoiceTemplate();
            }

            // Essayer de décoder le JSON
            $template_data = json_decode($template['template_data'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // S'assurer qu'il y a toujours un nom de template valide
                if (!isset($template_data['name']) || empty($template_data['name']) || preg_match('/^Template \d+$/', $template_data['name'])) {
                    $template_data['name'] = !empty($template['name']) ? $template['name'] : 'Template ' . $template_id;
                }
                // Ajouter les métadonnées du template
                $template_data['_db_name'] = $template['name'] ?? '';
                $template_data['_db_id'] = $template['id'];
                return $template_data;
            }

            // Essayer le nettoyage normal
            $clean_json = $this->admin->data_utils->cleanJsonData($template['template_data']);
            if ($clean_json !== $template['template_data']) {
                $template_data = json_decode($clean_json, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Ajouter le nom du template depuis la base de données
                    if (isset($template['name']) && (!isset($template_data['name']) || empty($template_data['name']) || preg_match('/^Template \d+$/', $template_data['name']))) {
                        $template_data['name'] = $template['name'];
                    }
                    // Ajouter les métadonnées du template
                    $template_data['_db_name'] = $template['name'] ?? '';
                    $template_data['_db_id'] = $template['id'];
                    return $template_data;
                }
            }

            // Essayer le nettoyage agressif
            $aggressive_clean = $this->admin->data_utils->aggressiveJsonClean($template['template_data']);
            if ($aggressive_clean !== $template['template_data']) {
                $template_data = json_decode($aggressive_clean, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Ajouter le nom du template depuis la base de données
                    if (isset($template['name']) && (!isset($template_data['name']) || empty($template_data['name']) || preg_match('/^Template \d+$/', $template_data['name']))) {
                        $template_data['name'] = $template['name'];
                    }
                    // Ajouter les métadonnées du template
                    $template_data['_db_name'] = $template['name'] ?? '';
                    $template_data['_db_id'] = $template['id'];
                    return $template_data;
                }
            }

            // Marquer comme corrompu et utiliser un template par défaut
            $this->markTemplateCorrupted($template_id);
            return $this->getDefaultInvoiceTemplate();

        } catch (Exception $e) {
            // error_log('[PDF Builder] loadTemplateRobust Exception: ' . $e->getMessage());
            // error_log('[PDF Builder] loadTemplateRobust Trace: ' . $e->getTraceAsString());
            return $this->getDefaultInvoiceTemplate();
        }
    }

    /**
     * Crée un template par défaut
     */
    public function getDefaultInvoiceTemplate()
    {
        return array(
            'canvas' => array(
                'width' => 595,
                'height' => 842,
                'zoom' => 1,
                'pan' => array('x' => 0, 'y' => 0)
            ),
            'pages' => array(
                array(
                    'margins' => array('top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20),
                    'elements' => array(
                        array(
                            'id' => 'company_name',
                            'type' => 'text',
                            'position' => array('x' => 50, 'y' => 50),
                            'size' => array('width' => 200, 'height' => 30),
                            'style' => array('fontSize' => 18, 'fontWeight' => 'bold', 'color' => '#000000'),
                            'content' => 'Ma Société'
                        ),
                        array(
                            'id' => 'invoice_title',
                            'type' => 'text',
                            'position' => array('x' => 400, 'y' => 50),
                            'size' => array('width' => 150, 'height' => 30),
                            'style' => array('fontSize' => 20, 'fontWeight' => 'bold', 'color' => '#000000'),
                            'content' => 'FACTURE'
                        ),
                        array(
                            'id' => 'invoice_number',
                            'type' => 'invoice_number',
                            'position' => array('x' => 400, 'y' => 90),
                            'size' => array('width' => 150, 'height' => 25),
                            'style' => array('fontSize' => 14, 'color' => '#000000'),
                            'content' => 'N° de facture'
                        ),
                        array(
                            'id' => 'invoice_date',
                            'type' => 'invoice_date',
                            'position' => array('x' => 400, 'y' => 120),
                            'size' => array('width' => 150, 'height' => 25),
                            'style' => array('fontSize' => 14, 'color' => '#000000'),
                            'content' => 'Date'
                        ),
                        array(
                            'id' => 'customer_info',
                            'type' => 'customer_info',
                            'position' => array('x' => 50, 'y' => 150),
                            'size' => array('width' => 250, 'height' => 80),
                            'style' => array('fontSize' => 12, 'color' => '#000000'),
                            'content' => 'Informations client'
                        ),
                        array(
                            'id' => 'products_table',
                            'type' => 'product_table',
                            'position' => array('x' => 50, 'y' => 250),
                            'size' => array('width' => 500, 'height' => 200),
                            'style' => array('fontSize' => 12, 'color' => '#000000'),
                            'content' => 'Tableau produits'
                        ),
                        array(
                            'id' => 'total',
                            'type' => 'total',
                            'position' => array('x' => 400, 'y' => 500),
                            'size' => array('width' => 150, 'height' => 30),
                            'style' => array('fontSize' => 16, 'fontWeight' => 'bold', 'color' => '#000000'),
                            'content' => 'Total'
                        )
                    )
                )
            )
        );
    }

    /**
     * Marque un template comme corrompu
     */
    public function markTemplateCorrupted($template_id)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        // Ajouter un flag de corruption (on peut utiliser un champ meta ou modifier le nom)
        $current_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $table_templates WHERE id = %d", $template_id));
        if ($current_name && strpos($current_name, '[CORROMPU]') !== 0) {
            $wpdb->update($table_templates, ['name' => '[CORROMPU] ' . $current_name], ['id' => $template_id]);
        }
    }

    /**
     * Répare les templates corrompus
     */
    public function performRepairTemplates()
    {
        $templates = get_option('pdf_builder_templates', []);
        $repaired_count = 0;
        if (is_array($templates)) {
            foreach ($templates as $key => $template) {
                // Vérifier et réparer la structure des templates
                if (!isset($template['name']) || !isset($template['data'])) {
                    unset($templates[$key]);
                    $repaired_count++;
                }
            }
        }

        update_option('pdf_builder_templates', $templates);
        return array(
            'success' => true,
            'message' => sprintf(__('Templates réparés. %d templates corrompus supprimés.', 'pdf-builder-pro'), $repaired_count)
        );
    }

    /**
     * Charge un template par ID
     */
    public function loadTemplateById($template_id)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $template_data = $wpdb->get_var($wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", $template_id));
        if (!$template_data) {
            return null;
        }

        $template = json_decode($template_data, true);
        // MIGRATION: Corriger les valeurs par défaut obsolètes dans les templates
        // Les templates créés avant cette correction peuvent avoir des valeurs par défaut incorrectes
        if (is_array($template) && isset($template['elements']) && is_array($template['elements'])) {
            foreach ($template['elements'] as &$element) {
                if ($element['type'] === 'product_table') {
                    // Valeurs par défaut correctes du Canvas Elements Manager
                    // showSubtotal, showDiscount, showTotal doivent être false par défaut
                    // showShipping et showTaxes doivent être true par défaut

                    // Générer les headers automatiquement s'ils ne correspondent pas aux colonnes
                    $columns = $element['columns'] ?? [];
                    $headers = $element['headers'] ?? [];
                    $visible_count = count(array_filter($columns));
                    if (is_array($headers) && count($headers) !== $visible_count) {
                            // Les headers ne correspondent pas au nombre de colonnes visibles
                        // Les régénérer automatiquement
                        $default_headers_map = [
                            'image' => 'Image',
                            'name' => 'Produit',
                            'sku' => 'Produit',
                            'quantity' => 'Qté',
                            'price' => 'Prix',
                            'total' => 'Total'
                        ];
                            $element['headers'] = [];
                        foreach ($columns as $col_name => $col_visible) {
                            if ($col_visible) {
                                        $element['headers'][] = $default_headers_map[$col_name] ?? ucfirst($col_name);
                            }
                        }
                    }
                }
            }
        }

        return $template;
    }
}
