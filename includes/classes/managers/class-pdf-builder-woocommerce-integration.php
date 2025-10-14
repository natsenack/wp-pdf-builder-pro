<?php
/**
 * PDF Builder Pro - WooCommerce Integration Manager
 * Gestion de l'intégration WooCommerce
 */

class PDF_Builder_WooCommerce_Integration {

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
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf']);
        add_action('wp_ajax_pdf_builder_pro_preview_order_pdf', [$this, 'ajax_preview_order_pdf']);
    }

    /**
     * Ajoute la meta box PDF Builder dans les commandes WooCommerce
     */
    public function add_woocommerce_order_meta_box() {
        // Vérifier que nous sommes sur la bonne page
        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Support both legacy (shop_order) and HPOS (woocommerce_page_wc-orders) screens
        $valid_screens = ['shop_order', 'woocommerce_page_wc-orders'];
        if (!in_array($screen->id, $valid_screens)) {
            return;
        }

        add_meta_box(
            'pdf-builder-order-actions',
            __('PDF Builder Pro', 'pdf-builder-pro'),
            [$this, 'render_woocommerce_order_meta_box'],
            $screen->id,
            'side',
            'high'
        );
    }

    /**
     * Rend la meta box dans les commandes WooCommerce
     */
    public function render_woocommerce_order_meta_box($post_or_order) {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Handle both legacy (WP_Post) and HPOS (WC_Order) cases
        if (is_a($post_or_order, 'WC_Order')) {
            $order = $post_or_order;
            $order_id = $order->get_id();
        } elseif (is_a($post_or_order, 'WP_Post')) {
            $order_id = $post_or_order->ID;
            $order = wc_get_order($order_id);
        } else {
            // Try to get order ID from URL for HPOS
            $order_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
            $order = wc_get_order($order_id);
        }

        if (!$order) {
            echo '<p>' . __('Commande invalide', 'pdf-builder-pro') . '</p>';
            return;
        }

        // Récupérer les templates disponibles
        $templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY name ASC", ARRAY_A);

        // Récupérer le template par défaut pour le statut de la commande
        $order_status = $order->get_status();
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;
        $default_template_id = isset($status_templates[$status_key]) ? $status_templates[$status_key] : 0;

        ?>
        <div id="pdf-builder-order-meta-box">
            <p><?php _e('Générer un PDF pour cette commande', 'pdf-builder-pro'); ?></p>

            <div style="margin-bottom: 10px;">
                <label for="pdf_template_select"><?php _e('Template:', 'pdf-builder-pro'); ?></label>
                <select id="pdf_template_select" style="width: 100%;">
                    <option value="0"><?php _e('Template par défaut', 'pdf-builder-pro'); ?></option>
                    <?php foreach ($templates as $template): ?>
                        <option value="<?php echo esc_attr($template['id']); ?>"
                                <?php selected($default_template_id, $template['id']); ?>>
                            <?php echo esc_html($template['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button" id="generate-order-pdf" class="button button-primary" style="width: 100%;">
                <?php _e('Générer PDF', 'pdf-builder-pro'); ?>
            </button>

            <div id="pdf-result" style="margin-top: 10px;"></div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#generate-order-pdf').on('click', function() {
                var templateId = $('#pdf_template_select').val();
                var orderId = <?php echo intval($order_id); ?>;

                $(this).prop('disabled', true).text('<?php _e('Génération...', 'pdf-builder-pro'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_generate_order_pdf',
                        order_id: orderId,
                        template_id: templateId,
                        nonce: '<?php echo wp_create_nonce('pdf_builder_order_actions'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#pdf-result').html('<p style="color: green;">' + response.data.message + '</p>' +
                                '<p><a href="' + response.data.url + '" target="_blank" class="button">Télécharger PDF</a></p>');
                        } else {
                            $('#pdf-result').html('<p style="color: red;">' + response.data + '</p>');
                        }
                    },
                    error: function() {
                        $('#pdf-result').html('<p style="color: red;">Erreur lors de la génération</p>');
                    },
                    complete: function() {
                        $('#generate-order-pdf').prop('disabled', false).text('<?php _e('Générer PDF', 'pdf-builder-pro'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX - Générer PDF pour une commande WooCommerce
     */
    public function ajax_generate_order_pdf() {
        // Désactiver l'affichage des erreurs PHP pour éviter les réponses HTML
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        // Vérifier les permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        if (!$order_id) {
            wp_send_json_error('ID commande manquant');
        }

        // Vérifier que WooCommerce est actif
        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce n\'est pas installé ou activé');
        }

        // Vérifier que les fonctions WooCommerce nécessaires existent
        if (!function_exists('wc_get_order')) {
            wp_send_json_error('Fonction wc_get_order non disponible - WooCommerce mal installé');
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error('Commande non trouvée');
        }

        // Vérifier que l'objet order a les méthodes nécessaires
        if (!method_exists($order, 'get_id') || !method_exists($order, 'get_total')) {
            wp_send_json_error('Objet commande WooCommerce invalide');
        }

        try {
            // Charger le template
            if ($template_id > 0) {
                $template_data = $this->load_template_robust($template_id);
            } else {
                // Vérifier s'il y a un template spécifique pour le statut de la commande
                $order_status = $order->get_status();
                $status_templates = get_option('pdf_builder_order_status_templates', []);
                $status_key = 'wc-' . $order_status;

                if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                    $mapped_template_id = $status_templates[$status_key];
                    $template_data = $this->load_template_robust($mapped_template_id);
                } else {
                    $template_data = $this->get_default_invoice_template();
                }
            }

            // Générer le PDF avec les données de la commande
            $pdf_filename = 'order-' . $order_id . '-' . time() . '.pdf';
            $pdf_path = $this->generate_order_pdf($order, $template_data, $pdf_filename);

            if ($pdf_path && file_exists($pdf_path)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

                wp_send_json_success(array(
                    'message' => 'PDF généré avec succès',
                    'url' => $pdf_url,
                    'filename' => $pdf_filename
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF - fichier non créé');
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Prévisualiser PDF pour une commande
     */
    public function ajax_preview_order_pdf() {
        // Même logique que ajax_generate_order_pdf mais pour la prévisualisation
        // (Code simplifié pour la démo)
        wp_send_json_error('Fonction de prévisualisation à implémenter');
    }

    /**
     * Générer PDF pour une commande
     */
    private function generate_order_pdf($order, $template_data, $filename) {
        // Générer le HTML de la commande
        $html = $this->generate_order_html($order, $template_data);

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
        $pdf->SetTitle('Facture Commande #' . $order->get_order_number());
        $pdf->SetSubject('Facture générée par PDF Builder Pro');

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
     * Générer HTML pour une commande
     */
    private function generate_order_html($order, $template_data) {
        $html = $this->generate_unified_html($template_data, $order);
        return $html;
    }

    /**
     * Générer HTML unifié (méthode commune avec PDF Generator)
     */
    private function generate_unified_html($template, $order = null) {
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

        // Informations société (si configurées)
        $company_info = $this->format_complete_company_info();
        $content = str_replace('{{company_info}}', $company_info, $content);

        // Informations client complètes
        $customer_info = $this->format_complete_customer_info($order);
        $content = str_replace('{{customer_info}}', $customer_info, $content);

        // Tableau des produits
        $products_table = $this->generate_order_products_table($order);
        $content = str_replace('{{products_table}}', $products_table, $content);

        return $content;
    }

    /**
     * Formatter les informations complètes de la société
     */
    private function format_complete_company_info() {
        $company_name = get_option('pdf_builder_company_name', '');
        $company_address = get_option('pdf_builder_company_address', '');
        $company_phone = get_option('pdf_builder_company_phone', '');
        $company_email = get_option('pdf_builder_company_email', '');

        $info = '';
        if ($company_name) $info .= $company_name . "\n";
        if ($company_address) $info .= $company_address . "\n";
        if ($company_phone) $info .= "Tel: " . $company_phone . "\n";
        if ($company_email) $info .= "Email: " . $company_email . "\n";

        return nl2br($info);
    }

    /**
     * Formatter les informations complètes du client
     */
    private function format_complete_customer_info($order) {
        $info = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . "\n";
        $info .= $order->get_billing_address_1() . "\n";
        if ($order->get_billing_address_2()) {
            $info .= $order->get_billing_address_2() . "\n";
        }
        $info .= $order->get_billing_postcode() . ' ' . $order->get_billing_city() . "\n";
        $info .= $order->get_billing_country() . "\n";
        $info .= "Email: " . $order->get_billing_email() . "\n";
        if ($order->get_billing_phone()) {
            $info .= "Tel: " . $order->get_billing_phone() . "\n";
        }

        return nl2br($info);
    }

    /**
     * Générer le tableau des produits de la commande
     */
    private function generate_order_products_table($order) {
        $html = '<table style="width: 100%; border-collapse: collapse; margin: 10px 0;">';
        $html .= '<thead><tr style="background-color: #f5f5f5;">';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Produit</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Qté</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Prix</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $price = $item->get_total() / $quantity;
            $total = $item->get_total();

            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($product_name) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $quantity . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . wc_price($price) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . wc_price($total) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Obtenir le template de facture par défaut
     */
    private function get_default_invoice_template() {
        return array(
            'pages' => array(
                array(
                    'elements' => array(
                        array(
                            'type' => 'text',
                            'content' => 'FACTURE',
                            'x' => 50,
                            'y' => 50,
                            'width' => 200,
                            'height' => 40,
                            'style' => array(
                                'fontSize' => 24,
                                'fontWeight' => 'bold'
                            )
                        ),
                        array(
                            'type' => 'text',
                            'content' => 'Commande #{{order_number}}',
                            'x' => 50,
                            'y' => 100,
                            'width' => 200,
                            'height' => 30
                        ),
                        array(
                            'type' => 'text',
                            'content' => 'Date: {{order_date}}',
                            'x' => 400,
                            'y' => 100,
                            'width' => 150,
                            'height' => 30
                        )
                    )
                )
            )
        );
    }

    /**
     * Charger un template de manière robuste
     */
    private function load_template_robust($template_id) {
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
}