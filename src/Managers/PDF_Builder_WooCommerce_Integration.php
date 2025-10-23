<?php

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - WooCommerce Integration Manager
 * Gestion de l'intégration WooCommerce
 */

class PDF_Builder_WooCommerce_Integration
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
        $this->init_hooks();
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks()
    {
        // Enregistrer les hooks AJAX via l'action init pour s'assurer qu'ils sont disponibles tôt
        add_action('init', [$this, 'register_ajax_hooks']);
    }

    /**
     * Enregistrer les hooks AJAX
     */
    public function register_ajax_hooks()
    {
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_pdf', [$this, 'ajax_generate_order_pdf'], 1);
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf'], 1); // Alias pour compatibilité
        add_action('wp_ajax_pdf_builder_save_order_canvas', [$this, 'ajax_save_order_canvas'], 1);
        add_action('wp_ajax_pdf_builder_load_order_canvas', [$this, 'ajax_load_order_canvas'], 1);
        add_action('wp_ajax_pdf_builder_get_canvas_elements', [$this, 'ajax_get_canvas_elements'], 1);
        add_action('wp_ajax_pdf_builder_get_order_data', [$this, 'ajax_get_order_data'], 1);
        add_action('wp_ajax_pdf_builder_validate_order_access', [$this, 'ajax_validate_order_access'], 1);
    }
    private function detect_document_type($order_status)
    {
        $status_mapping = [
            'pending' => 'devis',
            'processing' => 'commande',
            'on-hold' => 'commande',
            'completed' => 'facture',
            'cancelled' => 'annulation',
            'refunded' => 'remboursement',
            'failed' => 'erreur'
        ];

        return isset($status_mapping[$order_status]) ? $status_mapping[$order_status] : 'commande';
    }

    /**
     * Retourne le label du type de document
     */
    private function get_document_type_label($document_type)
    {
        $labels = [
            'devis' => 'Devis',
            'commande' => 'Bon de commande',
            'facture' => 'Facture',
            'annulation' => 'Annulation',
            'remboursement' => 'Remboursement',
            'erreur' => 'Document d\'erreur'
        ];

        return isset($labels[$document_type]) ? $labels[$document_type] : 'Document';
    }

    /**
     * Ajoute la meta box PDF Builder dans les commandes WooCommerce
     */
    public function add_woocommerce_order_meta_box()
    {
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
     * Rend la meta box dans les commandes WooCommerce - VERSION SIMPLE & ROBUSTE
     */
    public function render_woocommerce_order_meta_box($post_or_order)
    {
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
            echo '<div style="padding: 20px; text-align: center; color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">❌</div>
                    <strong>Commande invalide</strong><br>
                    <small>ID commande: ' . esc_html($order_id) . '</small>
                  </div>';
            return;
        }

        // Détecter automatiquement le type de document basé sur le statut de la commande
        $order_status = $order->get_status();
        $document_type = $this->detect_document_type($order_status);
        $document_type_label = $this->get_document_type_label($document_type);

        // Récupérer tous les templates disponibles
        $all_templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY name ASC", ARRAY_A);

        // Vérifier d'abord s'il y a un mapping spécifique pour ce statut de commande
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;
        $selected_template = null;

        // Utiliser le filtre du StatusManager pour appliquer le fallback
        $mapped_template_id = apply_filters('pdf_builder_get_template_for_status', null, $status_key);

        if ($mapped_template_id) {
            // Il y a un mapping spécifique pour ce statut (ou fallback appliqué)
            $selected_template = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id, name FROM $table_templates WHERE id = %d",
                    $mapped_template_id
                ),
                ARRAY_A
            );
        } elseif (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            // Fallback vers l'ancienne logique si le filtre ne retourne rien
            $selected_template = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT id, name FROM $table_templates WHERE id = %d",
                    $status_templates[$status_key]
                ),
                ARRAY_A
            );
        }

        // Si pas de mapping spécifique, utiliser la logique de détection automatique
        if (!$selected_template && !empty($all_templates)) {
            // Chercher un template dont le nom contient le type de document détecté
            foreach ($all_templates as $template) {
                if (stripos($template['name'], $document_type_label) !== false) {
                    $selected_template = $template;
                    break;
                }
            }

            // Fallback: prendre le premier template disponible
            if (!$selected_template) {
                $selected_template = $all_templates[0];
            }
        }

        // Vérifier que le template sélectionné existe vraiment
        if ($selected_template) {
            $existing_template = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM $table_templates WHERE id = %d",
                    $selected_template['id']
                )
            );

            if (!$existing_template) {
                // Le template sélectionné n'existe pas, utiliser le premier disponible
                $selected_template = !empty($all_templates) ? $all_templates[0] : null;
            }
        }

        wp_nonce_field('pdf_builder_order_actions', 'pdf_builder_order_nonce');

        // Récupérer le label du statut WooCommerce
        $order_statuses = wc_get_order_statuses();
        $status_label = isset($order_statuses['wc-' . $order_status]) ? $order_statuses['wc-' . $order_status] : ucfirst($order_status);

        // Déterminer si le template a été trouvé via mapping ou fallback
        $template_source = 'par défaut';
        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            $template_source = 'configuré pour ce statut';
        }
        ?>
        <div class="pdf-meta-box">
            <div class="pdf-template-section">
                <div class="pdf-template-title">
                    📄 Template: <?php echo esc_html($selected_template ? $selected_template['name'] : 'Aucun template disponible'); ?>
                </div>
                <div style="color: #6c757d; font-size: 14px; margin-bottom: 15px;">
                    Statut: <strong><?php echo esc_html($status_label); ?></strong> |
                    Type détecté: <strong><?php echo esc_html($document_type_label); ?></strong> |
                    Source: <em><?php echo esc_html($template_source); ?></em>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <?php if ($selected_template) : ?>
                    <button type="button" id="pdf-generate-btn" class="button button-secondary" style="padding: 8px 16px;">
                        📄 Générer PDF
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var orderId = <?php echo intval($order_id); ?>;
            var templateId = <?php echo intval($selected_template ? $selected_template['id'] : 0); ?>;
            var nonce = '<?php echo wp_create_nonce('pdf_builder_order_actions'); ?>';

            // Bouton génération PDF
            $('#pdf-generate-btn').on('click', function() {
                generatePDF(orderId, templateId, nonce);
            });
        });

        function generatePDF(orderId, templateId, nonce) {
            // Générer le PDF
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_generate_order_pdf',
                    order_id: orderId,
                    template_id: templateId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Télécharger le fichier
                        var link = document.createElement('a');
                        link.href = response.data.download_url;
                        link.download = response.data.filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert('Erreur: ' + response.data);
                    }
                },
                error: function() {
                    alert('Erreur lors de la génération du PDF');
                }
            });
        }
        </script>
        <?php
    }

    /**
     * AJAX handler pour générer le PDF d'une commande
     */
    public function ajax_generate_order_pdf()
    {
        // === SÉCURITÉ PHASE 5.8 - Vérifications de sécurité ===

        // 1. Vérification des permissions utilisateur
        if (!PDF_Builder_Security_Validator::check_permissions('manage_woocommerce')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes', 'code' => 'insufficient_permissions']);
            return;
        }

        // 2. Vérification du rate limiting
        if (!PDF_Builder_Rate_Limiter::check_rate_limit('pdf_generation')) {
            $remaining_time = PDF_Builder_Rate_Limiter::get_reset_time('pdf_generation');
            wp_send_json_error(
                [
                'message' => 'Trop de requêtes. Veuillez réessayer dans ' . ceil($remaining_time / 60) . ' minutes.',
                'code' => 'rate_limit_exceeded',
                'reset_in' => $remaining_time
                ]
            );
            return;
        }

        // 3. Validation du nonce
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!PDF_Builder_Security_Validator::validate_nonce($nonce, 'pdf_builder_order_actions')) {
            wp_send_json_error(['message' => 'Sécurité: Nonce invalide', 'code' => 'invalid_nonce']);
            return;
        }

        // 4. Validation et sanitisation des paramètres d'entrée
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        $custom_content = isset($_POST['content']) ? $_POST['content'] : '';
        $image_path = isset($_POST['image_path']) ? $_POST['image_path'] : '';

        // Validation de l'order_id
        if (!$order_id || $order_id <= 0) {
            wp_send_json_error(['message' => 'ID commande invalide', 'code' => 'invalid_order_id']);
            return;
        }

        // Sanitisation du contenu HTML personnalisé
        if (!empty($custom_content)) {
            $custom_content = PDF_Builder_Security_Validator::sanitize_html_content($custom_content);
            if (empty($custom_content)) {
                wp_send_json_error(['message' => 'Contenu HTML invalide', 'code' => 'invalid_html_content']);
                return;
            }
        }

        // Validation du chemin d'image si fourni
        if (!empty($image_path)) {
            if (!PDF_Builder_Path_Validator::validate_file_path($image_path)) {
                wp_send_json_error(['message' => 'Chemin de fichier invalide', 'code' => 'invalid_file_path']);
                return;
            }
        }

        try {
            // Charger la commande WooCommerce
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error('Commande introuvable');
                return;
            }

            // Charger le template
            if ($template_id > 0) {
                $templates = get_option('pdf_builder_templates', []);
                $template_data = isset($templates[$template_id]) ? $templates[$template_id] : null;
            } else {
                // Détecter automatiquement le template basé sur le statut de la commande
                $order_status = $order->get_status();
                $status_templates = get_option('pdf_builder_order_status_templates', []);
                $status_key = 'wc-' . $order_status;

                if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                    $mapped_template_id = $status_templates[$status_key];
                    $templates = get_option('pdf_builder_templates', []);
                    $template_data = isset($templates[$mapped_template_id]) ? $templates[$mapped_template_id] : null;
                } else {
                    // Template par défaut - prendre le premier template disponible
                    $templates = get_option('pdf_builder_templates', []);
                    $template_data = !empty($templates) ? reset($templates) : null;
                }
            }

            if (!$template_data) {
                wp_send_json_error('Template non trouvé');
            }

            // Extraire les éléments du template
            $elements = [];
            if (isset($template_data['pages']) && is_array($template_data['pages']) && !empty($template_data['pages'])) {
                $elements = $template_data['pages'][0]['elements'] ?? [];
            } elseif (isset($template_data['elements'])) {
                $elements = $template_data['elements'];
            }

            // Générer le PDF SANS TCPDF - utiliser la nouvelle approche HTML

            // Générer le HTML depuis les éléments du template
            $generator = new PDF_Builder_Pro_Generator();

            $html_content = $generator->generate($elements, ['order' => $order]);

            // Créer un fichier HTML temporaire pour le téléchargement
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';

            // Créer le dossier temporaire s'il n'existe pas
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
            }

            // Générer un nom de fichier unique
            $filename = 'pdf-document-' . $order_id . '-' . time() . '.html';
            $file_path = $temp_dir . '/' . $filename;

            // Sauvegarder le HTML dans le fichier
            if (file_put_contents($file_path, $html_content) === false) {
                wp_send_json_error('Erreur lors de la création du fichier HTML');
            }

            // Créer l'URL de téléchargement
            $download_url = $upload_dir['baseurl'] . '/pdf-builder-temp/' . $filename;

            wp_send_json_success(
                [
                'message' => 'HTML généré avec succès - TCPDF supprimé complètement',
                'url' => $download_url,
                'html_url' => $download_url,
                'pdf_url' => null
                ]
            );
        } catch (Exception $e) {
            wp_send_json_error('Erreur Exception: ' . $e->getMessage());
        } catch (Error $e) {
            wp_send_json_error('Erreur PHP: ' . $e->getMessage());
        } catch (Throwable $e) {
            wp_send_json_error('Erreur Throwable: ' . $e->getMessage());
        }
    }

    /**
     * Helper pour obtenir le nonce
     */
    private function get_nonce()
    {
        return wp_create_nonce('pdf_builder_order_actions');
    }

    /**
     * Helper pour obtenir l'URL AJAX
     */
    private function get_ajax_url()
    {
        return admin_url('admin-ajax.php');
    }

    /**
     * Récupère l'ID du template approprié pour une commande donnée
     */
    private function get_template_for_order($order)
    {
        if (!$order) {
            return null;
        }

        // Vérifier si un template spécifique est demandé via POST
        if (isset($_POST['template_id']) && !empty($_POST['template_id'])) {
            return intval($_POST['template_id']);
        }

        // Détecter automatiquement le template basé sur le statut de la commande
        $order_status = $order->get_status();
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            return $status_templates[$status_key];
        }

        // Template par défaut - prendre le premier template disponible
        $templates = get_option('pdf_builder_templates', []);
        if (!empty($templates)) {
            $first_template = reset($templates);
            return $first_template['id'] ?? null;
        }

        return null;
    }

    /**
     * Construit le style CSS d'un élément
     */
    private function build_element_style($element, $base_style)
    {
        $style = $base_style;

        // Get styles from element
        $element_styles = $element['style'] ?? $element['styles'] ?? [];

        // Font properties
        if (isset($element_styles['fontSize'])) {
            $style .= "font-size: {$element_styles['fontSize']}; ";
        }
        if (isset($element_styles['fontWeight'])) {
            $style .= "font-weight: {$element_styles['fontWeight']}; ";
        }
        if (isset($element_styles['fontStyle'])) {
            $style .= "font-style: {$element_styles['fontStyle']}; ";
        }
        if (isset($element_styles['fontFamily'])) {
            $style .= "font-family: {$element_styles['fontFamily']}; ";
        }
        if (isset($element_styles['lineHeight'])) {
            $style .= "line-height: {$element_styles['lineHeight']}; ";
        }

        // Text properties
        if (isset($element_styles['color'])) {
            $style .= "color: {$element_styles['color']}; ";
        }
        if (isset($element_styles['textAlign'])) {
            $style .= "text-align: {$element_styles['textAlign']}; ";
        }
        if (isset($element_styles['textDecoration'])) {
            $style .= "text-decoration: {$element_styles['textDecoration']}; ";
        }
        if (isset($element_styles['textTransform'])) {
            $style .= "text-transform: {$element_styles['textTransform']}; ";
        }
        if (isset($element_styles['letterSpacing'])) {
            $style .= "letter-spacing: {$element_styles['letterSpacing']}; ";
        }
        if (isset($element_styles['wordSpacing'])) {
            $style .= "word-spacing: {$element_styles['wordSpacing']}; ";
        }

        // Background properties
        if (isset($element_styles['backgroundColor'])) {
            $style .= "background-color: {$element_styles['backgroundColor']}; ";
        }
        if (isset($element_styles['backgroundImage'])) {
            $style .= "background-image: url('{$element_styles['backgroundImage']}'); ";
        }
        if (isset($element_styles['backgroundSize'])) {
            $style .= "background-size: {$element_styles['backgroundSize']}; ";
        }
        if (isset($element_styles['backgroundPosition'])) {
            $style .= "background-position: {$element_styles['backgroundPosition']}; ";
        }
        if (isset($element_styles['backgroundRepeat'])) {
            $style .= "background-repeat: {$element_styles['backgroundRepeat']}; ";
        }

        // Border properties - comprehensive
        if (isset($element_styles['border'])) {
            $style .= "border: {$element_styles['border']}; ";
        }
        if (isset($element_styles['borderWidth'])) {
            $style .= "border-width: {$element_styles['borderWidth']}; ";
        }
        if (isset($element_styles['borderStyle'])) {
            $style .= "border-style: {$element_styles['borderStyle']}; ";
        }
        if (isset($element_styles['borderColor'])) {
            $style .= "border-color: {$element_styles['borderColor']}; ";
        }
        if (isset($element_styles['borderRadius'])) {
            $style .= "border-radius: {$element_styles['borderRadius']}; ";
        }

        // Individual border sides
        $border_sides = ['Top', 'Right', 'Bottom', 'Left'];
        foreach ($border_sides as $side) {
            $side_lower = strtolower($side);
            if (isset($element_styles['border' . $side])) {
                $style .= "border-{$side_lower}: {$element_styles['border' . $side]}; ";
            }
            if (isset($element_styles['border' . $side . 'Width'])) {
                $style .= "border-{$side_lower}-width: {$element_styles['border' . $side . 'Width']}; ";
            }
            if (isset($element_styles['border' . $side . 'Style'])) {
                $style .= "border-{$side_lower}-style: {$element_styles['border' . $side . 'Style']}; ";
            }
            if (isset($element_styles['border' . $side . 'Color'])) {
                $style .= "border-{$side_lower}-color: {$element_styles['border' . $side . 'Color']}; ";
            }
        }

        // Padding and margins - comprehensive
        $spacing_props = ['padding', 'margin'];
        foreach ($spacing_props as $prop) {
            if (isset($element_styles[$prop])) {
                $style .= "{$prop}: {$element_styles[$prop]}; ";
            }
            $directions = ['Top', 'Right', 'Bottom', 'Left'];
            foreach ($directions as $dir) {
                $dir_lower = strtolower($dir);
                if (isset($element_styles[$prop . $dir])) {
                    $style .= "{$prop}-{$dir_lower}: {$element_styles[$prop . $dir]}; ";
                }
            }
        }

        // Dimensions
        if (isset($element_styles['width'])) {
            $style .= "width: {$element_styles['width']}; ";
        }
        if (isset($element_styles['height'])) {
            $style .= "height: {$element_styles['height']}; ";
        }
        if (isset($element_styles['minWidth'])) {
            $style .= "min-width: {$element_styles['minWidth']}; ";
        }
        if (isset($element_styles['minHeight'])) {
            $style .= "min-height: {$element_styles['minHeight']}; ";
        }
        if (isset($element_styles['maxWidth'])) {
            $style .= "max-width: {$element_styles['maxWidth']}; ";
        }
        if (isset($element_styles['maxHeight'])) {
            $style .= "max-height: {$element_styles['maxHeight']}; ";
        }

        // Positioning
        if (isset($element_styles['position'])) {
            $style .= "position: {$element_styles['position']}; ";
        }
        if (isset($element_styles['top'])) {
            $style .= "top: {$element_styles['top']}; ";
        }
        if (isset($element_styles['left'])) {
            $style .= "left: {$element_styles['left']}; ";
        }
        if (isset($element_styles['right'])) {
            $style .= "right: {$element_styles['right']}; ";
        }
        if (isset($element_styles['bottom'])) {
            $style .= "bottom: {$element_styles['bottom']}; ";
        }

        // Display and visibility
        if (isset($element_styles['display'])) {
            $style .= "display: {$element_styles['display']}; ";
        }
        if (isset($element_styles['visibility'])) {
            $style .= "visibility: {$element_styles['visibility']}; ";
        }
        if (isset($element_styles['opacity'])) {
            $style .= "opacity: {$element_styles['opacity']}; ";
        }

        // Flexbox properties
        if (isset($element_styles['display']) && $element_styles['display'] === 'flex') {
            if (isset($element_styles['flexDirection'])) {
                $style .= "flex-direction: {$element_styles['flexDirection']}; ";
            }
            if (isset($element_styles['justifyContent'])) {
                $style .= "justify-content: {$element_styles['justifyContent']}; ";
            }
            if (isset($element_styles['alignItems'])) {
                $style .= "align-items: {$element_styles['alignItems']}; ";
            }
            if (isset($element_styles['flexWrap'])) {
                $style .= "flex-wrap: {$element_styles['flexWrap']}; ";
            }
        }

        // Box shadow
        if (isset($element_styles['boxShadow'])) {
            $style .= "box-shadow: {$element_styles['boxShadow']}; ";
        }

        // Z-index and overflow
        if (isset($element_styles['zIndex'])) {
            $style .= "z-index: {$element_styles['zIndex']}; ";
        }
        if (isset($element_styles['overflow'])) {
            $style .= "overflow: {$element_styles['overflow']}; ";
        }

        // Special handling for rectangle elements
        if (($element['type'] ?? '') === 'rectangle') {
            if (isset($element_styles['backgroundColor'])) {
                $style .= "background-color: {$element_styles['backgroundColor']}; ";
            }
            if (isset($element_styles['border'])) {
                $style .= "border: {$element_styles['border']}; ";
            }
        }

        // Special handling for line elements
        if (($element['type'] ?? '') === 'line') {
            if (isset($element_styles['borderTop'])) {
                $style .= "border-top: {$element_styles['borderTop']}; ";
            } else {
                $style .= "border-top: 1px solid #000000; ";
            }
            $style .= "height: 1px; ";
        }

        return trim($style);
    }

    /**
     * Rend le contenu d'un élément avec les données de la commande
     */
    private function render_element_content($element, $order)
    {
        $type = $element['type'] ?? 'text';
        $content = $element['content'] ?? '';

        // Remplacer les variables dynamiques avec les vraies données de la commande
        if ($order) {
            $content = $this->replace_order_variables($content, $order);
        }

        switch ($type) {
            case 'text':
                return esc_html($content);

            case 'textarea':
                return nl2br(esc_html($content));

            case 'image':
                $src = $element['src'] ?? $element['content'] ?? '';
                $alt = $element['alt'] ?? 'Image';
                $width = $element['width'] ?? $element['size']['width'] ?? 'auto';
                $height = $element['height'] ?? $element['size']['height'] ?? 'auto';

                // Handle different width/height formats
                if (is_numeric($width)) {
                    $width .= 'px';
                }
                if (is_numeric($height)) {
                    $height .= 'px';
                }

                if (!empty($src)) {
                    return "<img src=\"{$src}\" alt=\"{$alt}\" style=\"width: {$width}; height: {$height}; object-fit: cover; max-width: 100%; max-height: 100%;\">";
                }
                return '';

            case 'line':
                return ''; // Handled by CSS styling only

            case 'rectangle':
                return ''; // Just a colored/styled div, no content needed

            case 'html':
                // Allow basic HTML but sanitize it
                return wp_kses(
                    $content,
                    [
                    'br' => [],
                    'strong' => [],
                    'b' => [],
                    'em' => [],
                    'i' => [],
                    'u' => [],
                    'span' => ['style' => []],
                    'div' => ['style' => []],
                    'p' => ['style' => []],
                    'table' => ['style' => []],
                    'tr' => ['style' => []],
                    'td' => ['style' => []],
                    'th' => ['style' => []],
                    'thead' => ['style' => []],
                    'tbody' => ['style' => []],
                    ]
                );

            default:
                return esc_html($content);
        }
    }

    /**
     * Remplace les variables de commande dans le contenu
     */
    private function replace_order_variables($content, $order)
    {
        if (!$order) {
            return $content;
        }

        // Basic order information
        $replacements = [
            '{{order_number}}' => $order->get_order_number(),
            '{{order_date}}' => $order->get_date_created() ? $order->get_date_created()->format('d/m/Y') : '',
            '{{order_date_time}}' => $order->get_date_created() ? $order->get_date_created()->format('d/m/Y H:i:s') : '',
            '{{order_date_modified}}' => $order->get_date_modified() ? $order->get_date_modified()->format('d/m/Y') : '',
            '{{order_total}}' => $order->get_formatted_order_total(),
            '{{order_status}}' => wc_get_order_status_name($order->get_status()),
            '{{customer_name}}' => $order->get_formatted_billing_full_name(),
            '{{customer_email}}' => $order->get_billing_email() ?: '',
            '{{customer_phone}}' => $order->get_billing_phone() ?: '',
            '{{customer_note}}' => $order->get_customer_note() ?: '',
            '{{billing_address}}' => $order->get_formatted_billing_address() ?: '',
            '{{shipping_address}}' => $order->get_formatted_shipping_address() ?: '',
            '{{payment_method}}' => $order->get_payment_method_title(),
            '{{payment_method_code}}' => $order->get_payment_method(),
            '{{transaction_id}}' => $order->get_transaction_id() ?: '',
            '{{shipping_method}}' => $order->get_shipping_method(),
        ];

        // Financial information
        $subtotal = $order->get_subtotal();
        $total_tax = $order->get_total_tax();
        $shipping_total = $order->get_shipping_total();
        $discount_total = $order->get_discount_total();

        $replacements = array_merge(
            $replacements,
            [
            '{{subtotal}}' => wc_price($subtotal),
            '{{tax_amount}}' => wc_price($total_tax),
            '{{shipping_amount}}' => wc_price($shipping_total),
            '{{discount_amount}}' => wc_price($discount_total),
            '{{total_excl_tax}}' => wc_price($order->get_total() - $total_tax),
            ]
        );

        // Handle product-specific variables (for single product display)
        $items = $order->get_items();
        if (!empty($items)) {
            $first_item = reset($items);

            $replacements = array_merge(
                $replacements,
                [
                '{{product_name}}' => $first_item->get_name(),
                '{{product_qty}}' => $first_item->get_quantity(),
                '{{product_price}}' => wc_price($first_item->get_total() / $first_item->get_quantity()),
                '{{product_total}}' => wc_price($first_item->get_total()),
                '{{product_sku}}' => $first_item->get_product() ? $first_item->get_product()->get_sku() : '',
                ]
            );

            // For multiple products, create a summary
            if (count($items) > 1) {
                $product_summary = '';
                foreach ($items as $item) {
                    $product_summary .= $item->get_name() . ' (x' . $item->get_quantity() . ') - ' . wc_price($item->get_total()) . "\n";
                }
                $replacements['{{products_list}}'] = $product_summary;
            } else {
                $replacements['{{products_list}}'] = $first_item->get_name() . ' (x' . $first_item->get_quantity() . ') - ' . wc_price($first_item->get_total());
            }
        }

        // Handle billing address components
        $billing_address = [
            '{{billing_first_name}}' => $order->get_billing_first_name(),
            '{{billing_last_name}}' => $order->get_billing_last_name(),
            '{{billing_company}}' => $order->get_billing_company(),
            '{{billing_address_1}}' => $order->get_billing_address_1(),
            '{{billing_address_2}}' => $order->get_billing_address_2(),
            '{{billing_city}}' => $order->get_billing_city(),
            '{{billing_state}}' => $order->get_billing_state(),
            '{{billing_postcode}}' => $order->get_billing_postcode(),
            '{{billing_country}}' => $order->get_billing_country(),
        ];

        // Handle shipping address components
        $shipping_address = [
            '{{shipping_first_name}}' => $order->get_shipping_first_name(),
            '{{shipping_last_name}}' => $order->get_shipping_last_name(),
            '{{shipping_company}}' => $order->get_shipping_company(),
            '{{shipping_address_1}}' => $order->get_shipping_address_1(),
            '{{shipping_address_2}}' => $order->get_shipping_address_2(),
            '{{shipping_city}}' => $order->get_shipping_city(),
            '{{shipping_state}}' => $order->get_shipping_state(),
            '{{shipping_postcode}}' => $order->get_shipping_postcode(),
            '{{shipping_country}}' => $order->get_shipping_country(),
        ];

        $replacements = array_merge($replacements, $billing_address, $shipping_address);

        $result = str_replace(array_keys($replacements), array_values($replacements), $content);

        return $result;
    }

    /**
     * AJAX handler pour sauvegarder le canvas d'une commande
     */
    public function ajax_save_order_canvas()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!current_user_can('manage_woocommerce') && !current_user_can('edit_shop_orders')) {
                wp_send_json_error('Permissions insuffisantes pour sauvegarder le canvas');
                return;
            }

            // Vérifier le nonce de sécurité
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser les données d'entrée
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
            $canvas_data = isset($_POST['canvas_data']) ? wp_unslash($_POST['canvas_data']) : '';

            if (!$order_id || empty($canvas_data)) {
                wp_send_json_error('Données manquantes: order_id et canvas_data requis');
                return;
            }

            // Valider que la commande existe
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error('Commande introuvable');
                return;
            }

            // Valider le format JSON des données canvas
            $canvas_elements = json_decode($canvas_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Format JSON invalide pour les données canvas');
                return;
            }

            // Sanitiser les données canvas (validation basique)
            $sanitized_elements = $this->sanitize_canvas_elements($canvas_elements);

            // Sauvegarder les données dans les meta de la commande
            $meta_key = '_pdf_builder_canvas_data';
            $save_result = update_post_meta($order_id, $meta_key, $sanitized_elements);

            if ($save_result === false) {
                wp_send_json_error('Erreur lors de la sauvegarde des données canvas');
                return;
            }

            wp_send_json_success(
                [
                'message' => 'Canvas sauvegardé avec succès',
                'order_id' => $order_id,
                'elements_count' => count($sanitized_elements)
                ]
            );
        } catch (Exception $e) {
            wp_send_json_error('Erreur interne lors de la sauvegarde');
        }
    }

    /**
     * Sanitise les éléments du canvas
     */
    private function sanitize_canvas_elements($elements)
    {
        if (!is_array($elements)) {
            return [];
        }

        $sanitized = [];
        foreach ($elements as $element) {
            if (!is_array($element)) {
                continue;
            }

            $sanitized_element = [];

            // Sanitiser les champs de base
            $sanitized_element['id'] = isset($element['id']) ? sanitize_text_field($element['id']) : '';
            $sanitized_element['type'] = isset($element['type']) ? sanitize_text_field($element['type']) : '';
            $sanitized_element['x'] = isset($element['x']) ? floatval($element['x']) : 0;
            $sanitized_element['y'] = isset($element['y']) ? floatval($element['y']) : 0;
            $sanitized_element['width'] = isset($element['width']) ? floatval($element['width']) : 0;
            $sanitized_element['height'] = isset($element['height']) ? floatval($element['height']) : 0;

            // Sanitiser le contenu selon le type
            if (isset($element['content'])) {
                $sanitized_element['content'] = $this->sanitize_element_content($element['content'], $element['type'] ?? '');
            }

            // Sanitiser les styles
            if (isset($element['style']) && is_array($element['style'])) {
                $sanitized_element['style'] = $this->sanitize_element_styles($element['style']);
            }

            $sanitized[] = $sanitized_element;
        }

        return $sanitized;
    }

    /**
     * Sanitise le contenu d'un élément selon son type
     */
    private function sanitize_element_content($content, $type)
    {
        switch ($type) {
            case 'text':
            case 'dynamic-text':
                return wp_kses(
                    $content,
                    [
                    'br' => [],
                    'strong' => [],
                    'em' => [],
                    'u' => []
                    ]
                );
            case 'image':
                return esc_url_raw($content);
            default:
                return sanitize_text_field($content);
        }
    }

    /**
     * Sanitise les styles d'un élément
     */
    private function sanitize_element_styles($styles)
    {
        $allowed_styles = [
            'fontSize', 'fontFamily', 'color', 'backgroundColor',
            'textAlign', 'fontWeight', 'fontStyle', 'textDecoration',
            'borderWidth', 'borderColor', 'borderStyle'
        ];

        $sanitized = [];
        foreach ($styles as $key => $value) {
            if (in_array($key, $allowed_styles)) {
                if (strpos($key, 'color') !== false) {
                    $sanitized[$key] = sanitize_hex_color($value) ?: '#000000';
                } elseif (strpos($key, 'fontSize') !== false || strpos($key, 'borderWidth') !== false) {
                    $sanitized[$key] = floatval($value);
                } else {
                    $sanitized[$key] = sanitize_text_field($value);
                }
            }
        }

        return $sanitized;
    }

    /**
     * AJAX handler pour charger le canvas d'une commande
     */
    public function ajax_load_order_canvas()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!current_user_can('manage_woocommerce') && !current_user_can('edit_shop_orders')) {
                wp_send_json_error('Permissions insuffisantes pour charger le canvas');
                return;
            }

            // Vérifier le nonce de sécurité
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser les données d'entrée
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

            if (!$order_id) {
                wp_send_json_error('ID commande manquant');
                return;
            }

            // Valider que la commande existe
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error('Commande introuvable');
                return;
            }

            // Récupérer les données canvas depuis les meta
            $meta_key = '_pdf_builder_canvas_data';
            $canvas_data = get_post_meta($order_id, $meta_key, true);

            // Si aucune donnée canvas n'existe, retourner un tableau vide
            if (empty($canvas_data)) {
                wp_send_json_success(
                    [
                    'canvas_data' => [],
                    'message' => 'Aucune donnée canvas trouvée pour cette commande'
                    ]
                );
                return;
            }

            // Valider que les données sont un tableau
            if (!is_array($canvas_data)) {
                wp_send_json_error('Format de données canvas invalide');
                return;
            }

            wp_send_json_success(
                [
                'canvas_data' => $canvas_data,
                'order_id' => $order_id,
                'elements_count' => count($canvas_data)
                ]
            );
        } catch (Exception $e) {
            wp_send_json_error('Erreur interne lors du chargement');
        }
    }

    /**
     * AJAX handler pour récupérer les éléments canvas d'un template
     */
    public function ajax_get_canvas_elements()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!current_user_can('manage_woocommerce') && !current_user_can('edit_shop_orders')) {
                wp_send_json_error('Permissions insuffisantes pour accéder aux éléments canvas');
                return;
            }

            // Vérifier le nonce de sécurité
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser le template_id
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

            if (!$template_id || $template_id <= 0) {
                wp_send_json_error('ID template invalide ou manquant');
                return;
            }

            // Vérifier que le template existe (soit comme post WordPress, soit dans la table personnalisée)
            $template_exists = false;
            $template_data = null;

            // D'abord vérifier si c'est un post WordPress
            if (get_post($template_id)) {
                $template_exists = true;
            } else {

                // Essayer de récupérer depuis la table pdf_builder_templates
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';
                $template = $wpdb->get_row(
                    $wpdb->prepare("SELECT id, name, template_data FROM $table_templates WHERE id = %d", $template_id),
                    ARRAY_A
                );

                if ($template && !empty($template['template_data'])) {
                    $template_exists = true;
                    $template_data = $template['template_data'];
                } else {
                }
            }

            if (!$template_exists) {
                wp_send_json_error('Template introuvable');
                return;
            }

            // Récupérer les éléments depuis le cache ou la base de données
            $cache_key = 'pdf_builder_canvas_elements_' . $template_id;
            $canvas_elements = get_transient($cache_key);

            if ($canvas_elements === false) {

                // Si on a déjà les données du template depuis la table personnalisée, les utiliser
                if ($template_data !== null) {
                    $decoded_data = json_decode($template_data, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_data)) {

                        // Extraire les éléments depuis la structure du template
                        if (isset($decoded_data['elements']) && is_array($decoded_data['elements'])) {
                            $canvas_elements = $decoded_data['elements'];
                        } elseif (isset($decoded_data['pages']) && is_array($decoded_data['pages']) && !empty($decoded_data['pages'])) {
                            // Fallback pour l'ancienne structure
                            $first_page = $decoded_data['pages'][0];
                            if (isset($first_page['elements']) && is_array($first_page['elements'])) {
                                $canvas_elements = $first_page['elements'];
                            } else {
                                $canvas_elements = [];
                            }
                        } else {
                            $canvas_elements = [];
                        }
                    } else {
                        $canvas_elements = [];
                    }
                } else {
                    // Template WordPress classique - récupérer depuis les métadonnées
                    $canvas_elements = get_post_meta($template_id, 'pdf_builder_elements', true);
                }

                // Validation et nettoyage des données
                $canvas_elements = $this->validate_and_clean_canvas_elements($canvas_elements);

                // Mettre en cache pour 5 minutes
                set_transient($cache_key, $canvas_elements, 5 * MINUTE_IN_SECONDS);
            }

            wp_send_json_success(
                [
                'elements' => $canvas_elements,
                'template_id' => $template_id,
                'cached' => ($canvas_elements !== false),
                'element_count' => is_array($canvas_elements) ? count($canvas_elements) : 0
                ]
            );
        } catch (Exception $e) {
            wp_send_json_error('Erreur interne lors de la récupération des éléments');
        }
    }

    /**
     * Valide et nettoie les éléments canvas récupérés
     */
    private function validate_and_clean_canvas_elements($elements)
    {
        if (!is_array($elements)) {
            // Essayer de décoder si c'est du JSON
            if (is_string($elements)) {
                $decoded = json_decode($elements, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $elements = $decoded;
                } else {
                    return []; // Retourner tableau vide si JSON invalide
                }
            } else {
                return []; // Retourner tableau vide si format invalide
            }
        }

        // Nettoyer et valider chaque élément
        $cleaned_elements = [];
        foreach ($elements as $element) {
            if (is_array($element) && isset($element['type'])) {
                $cleaned_element = $this->clean_canvas_element($element);
                if ($cleaned_element) {
                    $cleaned_elements[] = $cleaned_element;
                }
            }
        }

        return $cleaned_elements;
    }

    /**
     * Nettoie un élément canvas individuel
     */
    private function clean_canvas_element($element)
    {
        $cleaned = [];

        // Champs obligatoires
        $required_fields = ['id', 'type', 'x', 'y', 'width', 'height'];
        foreach ($required_fields as $field) {
            if (!isset($element[$field])) {
                return false; // Élément invalide
            }
            $cleaned[$field] = $this->sanitize_element_field($field, $element[$field]);
        }

        // Champs optionnels
        $optional_fields = ['content', 'style', 'rotation', 'opacity', 'zIndex'];
        foreach ($optional_fields as $field) {
            if (isset($element[$field])) {
                $cleaned[$field] = $this->sanitize_element_field($field, $element[$field]);
            }
        }

        return $cleaned;
    }

    /**
     * Sanitise un champ d'élément selon son type
     */
    private function sanitize_element_field($field, $value)
    {
        switch ($field) {
            case 'id':
            case 'type':
                return sanitize_text_field($value);
            case 'x':
            case 'y':
            case 'width':
            case 'height':
            case 'rotation':
            case 'opacity':
            case 'zIndex':
                return floatval($value);
            case 'content':
                return $this->sanitize_element_content($value, 'text'); // Type par défaut
            case 'style':
                return is_array($value) ? $this->sanitize_element_styles($value) : [];
            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Récupère et valide complètement une commande WooCommerce
     */
    private function get_and_validate_order($order_id)
    {
        // Vérifier que WooCommerce est actif
        if (!function_exists('wc_get_order')) {
            return new WP_Error('woocommerce_not_active', 'WooCommerce n\'est pas actif');
        }

        // Récupérer la commande
        $order = wc_get_order($order_id);
        if (!$order) {
            return new WP_Error('order_not_found', 'Commande introuvable');
        }

        // Vérifier que c'est bien une commande WooCommerce
        if (!is_a($order, 'WC_Order')) {
            return new WP_Error('invalid_order_type', 'Type d\'objet invalide');
        }

        // Vérifier les permissions d'accès à cette commande spécifique
        if (!current_user_can('manage_woocommerce')) {
            // Pour les utilisateurs non-admin, vérifier qu'ils ont accès à cette commande
            $user_id = get_current_user_id();
            $order_user_id = $order->get_customer_id();

            // Si l'utilisateur n'est pas le propriétaire et n'a pas les droits d'édition
            if ($user_id !== $order_user_id && !current_user_can('edit_shop_orders')) {
                return new WP_Error('access_denied', 'Accès non autorisé à cette commande');
            }
        }

        // Vérifier le statut de la commande (éviter les commandes en cours de traitement)
        $valid_statuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed'];
        $current_status = $order->get_status();

        if (!in_array($current_status, $valid_statuses)) {
            return new WP_Error('invalid_order_status', 'Statut de commande non valide pour le traitement');
        }

        return $order;
    }

    /**
     * Récupère les données complètes des articles de commande
     */
    private function get_order_items_complete_data($order)
    {
        $items_data = [];

        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $item_data = [
                'id' => $item_id,
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $item->get_total() / max(1, $item->get_quantity()), // Prix unitaire
                'regular_price' => $product ? $product->get_regular_price() : null,
                'sale_price' => $product ? $product->get_sale_price() : null,
                'total' => $item->get_total(),
                'total_tax' => $item->get_total_tax(),
                'subtotal' => $item->get_subtotal(),
                'subtotal_tax' => method_exists($item, 'get_subtotal_tax') ? $item->get_subtotal_tax() : 0,
                'tax_class' => $item->get_tax_class(),
                'sku' => $product ? $product->get_sku() : '',
                'product_id' => $item->get_product_id(),
                'variation_id' => $item->get_variation_id(),
                'type' => $product ? $product->get_type() : 'simple',
            ];

            // Gestion des variations
            if ($item->get_variation_id()) {
                $variation = wc_get_product($item->get_variation_id());
                if ($variation) {
                    $item_data['variation_attributes'] = [];
                    $variation_attributes = $variation->get_variation_attributes();

                    foreach ($variation_attributes as $attribute_name => $attribute_value) {
                        $attribute_name_clean = str_replace('attribute_', '', $attribute_name);
                        $attribute_name_clean = str_replace('pa_', '', $attribute_name_clean);

                        // Récupérer le nom d'attribut lisible
                        $attribute_taxonomy = 'pa_' . $attribute_name_clean;
                        $attribute_terms = get_terms(
                            [
                            'taxonomy' => $attribute_taxonomy,
                            'slug' => $attribute_value,
                            'hide_empty' => false
                            ]
                        );

                        $attribute_label = $attribute_terms && !is_wp_error($attribute_terms) && !empty($attribute_terms)
                            ? $attribute_terms[0]->name
                            : ucfirst($attribute_name_clean);

                        $item_data['variation_attributes'][$attribute_name_clean] = [
                            'label' => $attribute_label,
                            'value' => $attribute_value,
                            'taxonomy' => $attribute_taxonomy
                        ];
                    }
                }
            }

            // Métadonnées de l'article
            $item_data['meta_data'] = [];
            foreach ($item->get_meta_data() as $meta) {
                $item_data['meta_data'][$meta->key] = $meta->value;
            }

            $items_data[] = $item_data;
        }

        return $items_data;
    }

    /**
     * AJAX: Récupère les données complètes d'une commande pour le mode Metabox
     */
    public function ajax_get_order_data()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!current_user_can('manage_woocommerce') && !current_user_can('edit_shop_orders')) {
                wp_send_json_error('Permissions insuffisantes pour accéder aux données de commande');
                return;
            }

            // Vérifier le nonce de sécurité
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser les données d'entrée
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

            if (!$order_id) {
                wp_send_json_error('ID commande manquant');
                return;
            }

            // Récupération et validation complète de la commande
            $order = $this->get_and_validate_order($order_id);
            if (is_wp_error($order)) {
                wp_send_json_error($order->get_error_message());
                return;
            }

            // Récupérer les données de commande formatées
            $order_data = [
                'order' => [
                    'id' => $order->get_id(),
                    'number' => $order->get_order_number(),
                    'status' => $order->get_status(),
                    'total' => $order->get_total(),
                ],
                'items' => $this->get_order_items_complete_data($order),
            ];

            wp_send_json_success(
                [
                'order' => $order_data,
                'order_id' => $order_id
                ]
            );
        } catch (Exception $e) {
            wp_send_json_error('Erreur interne lors de la récupération des données de commande');
        }
    }

    /**
     * AJAX: Valide l'accès à une commande
     */
    public function ajax_validate_order_access()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!current_user_can('manage_woocommerce') && !current_user_can('edit_shop_orders')) {
                wp_send_json_error('Permissions insuffisantes pour accéder à cette commande');
                return;
            }

            // Vérifier le nonce de sécurité
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser les données d'entrée
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

            if (!$order_id) {
                wp_send_json_error('ID commande manquant');
                return;
            }

            // Récupération et validation de la commande
            $order = $this->get_and_validate_order($order_id);
            if (is_wp_error($order)) {
                wp_send_json_error($order->get_error_message());
                return;
            }

            wp_send_json_success(
                [
                'order_id' => $order_id,
                'accessible' => true
                ]
            );
        } catch (Exception $e) {
            wp_send_json_error('Erreur interne lors de la validation d\'accès');
        }
    }
}
