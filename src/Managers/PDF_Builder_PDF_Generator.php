<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - PDF Generator Manager
 * Gestion centralisée de la génération PDF
 */

class PDF_Builder_PDF_Generator {

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
        // AJAX handlers pour la génération PDF
        add_action('wp_ajax_pdf_builder_generate_pdf_from_canvas', [$this, 'ajax_generate_pdf_from_canvas']);
        add_action('wp_ajax_pdf_builder_preview_pdf', [$this, 'ajax_preview_pdf']);
        add_action('wp_ajax_pdf_builder_download_pdf', [$this, 'ajax_download_pdf']);
        add_action('wp_ajax_pdf_builder_unified_preview', [$this, 'ajax_unified_preview']);
    }

    /**
     * AJAX - Générer PDF depuis les données canvas
     */
    public function ajax_generate_pdf_from_canvas() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $canvas_data = isset($_POST['canvas_data']) ? $_POST['canvas_data'] : '';

        if (empty($canvas_data)) {
            wp_send_json_error('Données canvas manquantes');
        }

        try {
            // Décoder les données canvas
            $canvas_elements = json_decode($canvas_data, true);
            
            if (!$canvas_elements || !is_array($canvas_elements)) {
                wp_send_json_error('Format JSON invalide pour les données canvas');
            }

            $pdf_path = $this->generate_pdf_from_template_data($canvas_elements, 'canvas-' . time() . '.pdf');

            if ($pdf_path && file_exists($pdf_path)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

                wp_send_json_success(array(
                    'url' => $pdf_url,
                    'path' => $pdf_path
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Prévisualiser PDF
     */
    public function ajax_preview_pdf() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';

        if (empty($template_data)) {
            wp_send_json_error('Données template manquantes');
        }

        try {
            $pdf_path = $this->generate_pdf_from_template_data(json_decode($template_data, true), 'preview-' . time() . '.pdf');

            if ($pdf_path && file_exists($pdf_path)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

                wp_send_json_success(array(
                    'url' => $pdf_url,
                    'path' => $pdf_path
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Télécharger PDF
     */
    public function ajax_download_pdf() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';
        $filename = isset($_POST['filename']) ? sanitize_file_name($_POST['filename']) : 'document.pdf';

        if (empty($template_data)) {
            wp_send_json_error('Données template manquantes');
        }

        try {
            $pdf_path = $this->generate_pdf_from_template_data(json_decode($template_data, true), $filename);

            if ($pdf_path && file_exists($pdf_path)) {
                wp_send_json_success(array(
                    'path' => $pdf_path,
                    'filename' => $filename
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Aperçu unifié (pour le canvas editor)
     */
    public function ajax_unified_preview() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Lire les données depuis POST (FormData)
        if (!isset($_POST['nonce']) || !isset($_POST['elements'])) {
            wp_send_json_error('Données de requête invalides');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $elements = $_POST['elements'];

        // Debug: Log what we received
        error_log('[PDF Builder Preview] Raw elements received: ' . substr($elements, 0, 200) . '...');
        error_log('[PDF Builder Preview] Elements length: ' . strlen($elements));

        // Décoder depuis base64
        $jsonString = base64_decode($elements);
        if ($jsonString === false) {
            wp_send_json_error('Erreur décodage base64');
        }

        error_log('[PDF Builder Preview] Decoded JSON length: ' . strlen($jsonString));
        error_log('[PDF Builder Preview] Decoded JSON start: ' . substr($jsonString, 0, 200) . '...');

        try {
            // Décoder les éléments JSON
            $canvas_elements = json_decode($jsonString, true);
            
            if (!$canvas_elements || !is_array($canvas_elements)) {
                error_log('[PDF Builder Preview] json_last_error: ' . json_last_error());
                error_log('[PDF Builder Preview] json_last_error_msg: ' . json_last_error_msg());
                error_log('[PDF Builder Preview] is_array check: ' . (is_array($canvas_elements) ? 'true' : 'false'));
                error_log('[PDF Builder Preview] count: ' . (is_array($canvas_elements) ? count($canvas_elements) : 'N/A'));
                wp_send_json_error('Format JSON invalide pour les éléments');
            }

            // Créer un template à partir des éléments canvas
            $template_data = array(
                'elements' => $canvas_elements,
                'canvasWidth' => 595, // A4 width in points
                'canvasHeight' => 842, // A4 height in points
                'version' => '1.0'
            );
            
            // Générer le PDF en utilisant les données template
            $pdf_path = $this->generate_pdf_from_template_data($template_data, 'preview-' . time() . '.pdf');

            if ($pdf_path && file_exists($pdf_path)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

                wp_send_json_success(array(
                    'url' => $pdf_url,
                    'path' => $pdf_path,
                    'elements_count' => count($canvas_elements)
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF d\'aperçu');
            }
        } catch (Exception $e) {
            wp_send_json_error('Erreur aperçu: ' . $e->getMessage());
        }
    }

    /**
     * Générer PDF depuis les données template
     */
    private function generate_pdf_from_template_data($template, $filename) {
        if (!$template || !is_array($template)) {
            throw new Exception('Données template invalides');
        }

        // Générer le HTML
        $html = $this->generate_html_from_template_data($template);

        // Créer le répertoire uploads s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_path = $pdf_dir . '/' . $filename;

        // Générer le PDF avec TCPDF
        require_once plugin_dir_path(dirname(__FILE__)) . '../../lib/tcpdf/tcpdf_autoload.php';

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuration PDF
        $pdf->SetCreator('PDF Builder Pro');
        $pdf->SetAuthor('PDF Builder Pro');
        $pdf->SetTitle('Document PDF');
        $pdf->SetSubject('Document généré par PDF Builder Pro');
        $pdf->SetKeywords('PDF, Builder, Pro');

        // Supprimer les headers par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Ajouter une page
        $pdf->AddPage();

        // Écrire le HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Sauvegarder le PDF
        $pdf->Output($pdf_path, 'F');

        return $pdf_path;
    }

    /**
     * Générer HTML depuis les données template
     */
    private function generate_html_from_template_data($template) {
        // Utiliser la même fonction que l'aperçu commande pour la cohérence
        if (class_exists('PDF_Builder\Admin\PDF_Builder_Admin')) {
            $admin = \PDF_Builder\Admin\PDF_Builder_Admin::getInstance();
            if (method_exists($admin, 'generate_unified_html')) {
                return $admin->generate_unified_html($template);
            }
        }

        // Fallback vers l'ancienne implémentation si nécessaire
        return $this->generate_unified_html_legacy($template);
    }

    /**
     * Générer HTML unifié (version legacy - conservée pour compatibilité)
     */
    private function generate_unified_html_legacy($template, $order = null) {
        $html = '<div style="font-family: Arial, sans-serif; padding: 20px;">';

        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            $elements = $firstPage['elements'] ?? [];
        } elseif (isset($template['elements']) && is_array($template['elements'])) {
            $elements = $template['elements'];
        } else {
            $elements = [];
        }

        if (is_array($elements)) {
            foreach ($elements as $element) {
                // Gérer les deux formats de structure des éléments
                if (isset($element['position']) && isset($element['size'])) {
                    $x = $element['position']['x'] ?? 0;
                    $y = $element['position']['y'] ?? 0;
                    $width = $element['size']['width'] ?? 100;
                    $height = $element['size']['height'] ?? 50;
                } else {
                    $x = $element['x'] ?? 0;
                    $y = $element['y'] ?? 0;
                    $width = $element['width'] ?? 100;
                    $height = $element['height'] ?? 50;
                }

                $style = sprintf(
                    'position: absolute; left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
                    $x, $y, $width, $height
                );

                if (isset($element['style'])) {
                    if (isset($element['style']['color'])) {
                        $style .= ' color: ' . $element['style']['color'] . ';';
                    }
                    if (isset($element['style']['fontSize'])) {
                        $style .= ' font-size: ' . $element['style']['fontSize'] . 'px;';
                    }
                    if (isset($element['style']['fontWeight'])) {
                        $style .= ' font-weight: ' . $element['style']['fontWeight'] . ';';
                    }
                    if (isset($element['style']['fillColor'])) {
                        $style .= ' background-color: ' . $element['style']['fillColor'] . ';';
                    }
                }

                $content = $element['content'] ?? '';

                // Remplacer les variables si on a une commande WooCommerce
                if ($order) {
                    $content = $this->replace_order_variables($content, $order);
                }

                switch ($element['type']) {
                    case 'text':
                        $final_content = $order ? $this->replace_order_variables($content, $order) : $content;
                        $html .= sprintf('<div style="%s">%s</div>', $style, esc_html($final_content));
                        break;

                    case 'invoice_number':
                        if ($order) {
                            $invoice_number = $order->get_id() . '-' . time();
                            $html .= sprintf('<div style="%s">%s</div>', $style, esc_html($invoice_number));
                        }
                        break;

                    default:
                        $html .= sprintf('<div style="%s">%s</div>', $style, esc_html($content));
                        break;
                }
            }
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Convertir les éléments en format template
     */
    private function convert_elements_to_template($elements) {
        return array(
            'pages' => array(
                array(
                    'elements' => $elements
                )
            )
        );
    }

    /**
     * Remplacer les variables de commande
     */
    private function replace_order_variables($content, $order) {
        // Variables simples
        $content = str_replace('{{order_id}}', $order->get_id(), $content);
        $content = str_replace('{{order_number}}', $order->get_order_number(), $content);
        $content = str_replace('{{order_date}}', $order->get_date_created()->date('d/m/Y'), $content);
        $content = str_replace('{{order_total}}', $order->get_formatted_order_total(), $content);

        // Informations client
        $content = str_replace('{{customer_name}}', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), $content);
        $content = str_replace('{{customer_email}}', $order->get_billing_email(), $content);

        return $content;
    }
}