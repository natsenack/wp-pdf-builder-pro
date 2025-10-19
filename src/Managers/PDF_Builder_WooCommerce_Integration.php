<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
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
        // Enregistrer les hooks AJAX via l'action init pour s'assurer qu'ils sont disponibles tôt
        add_action('init', [$this, 'register_ajax_hooks']);
    }

    /**
     * Enregistrer les hooks AJAX
     */
    public function register_ajax_hooks() {
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_pdf', [$this, 'ajax_generate_order_pdf'], 1);
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf'], 1); // Alias pour compatibilité
        add_action('wp_ajax_pdf_builder_generate_order_pdf_preview', [$this, 'ajax_generate_order_pdf_preview'], 1);
        add_action('wp_ajax_pdf_builder_save_order_canvas', [$this, 'ajax_save_order_canvas'], 1);
    }
    private function detect_document_type($order_status) {
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
    private function get_document_type_label($document_type) {
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
     * Rend la meta box dans les commandes WooCommerce - VERSION SIMPLE & ROBUSTE
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

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            // Il y a un mapping spécifique pour ce statut
            $selected_template = $wpdb->get_row($wpdb->prepare(
                "SELECT id, name FROM $table_templates WHERE id = %d",
                $status_templates[$status_key]
            ), ARRAY_A);
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
                    <button type="button" id="pdf-preview-btn" class="button button-primary" style="padding: 8px 16px;">
                        👁️ Aperçu PDF
                    </button>

                    <?php if ($selected_template): ?>
                    <button type="button" id="pdf-generate-btn" class="button button-secondary" style="padding: 8px 16px;">
                        📄 Générer PDF
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Modal d'aperçu simplifié -->
        <div id="pdf-preview-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
            <div style="background: white; border-radius: 8px; width: 90%; max-width: 1200px; height: 90%; display: flex; flex-direction: column;">
                <div style="padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0;">Aperçu PDF - Commande #<?php echo esc_html($order->get_order_number()); ?></h3>
                    <button type="button" id="pdf-preview-close" style="background: none; border: none; font-size: 24px; cursor: pointer; padding: 0;">&times;</button>
                </div>
                <div style="flex: 1; padding: 20px; overflow: auto;">
                    <div id="pdf-preview-content" style="width: 100%; height: 100%; border: 1px solid #ddd; background: #f8f9fa;">
                        <div style="display: flex; justify-content: center; align-items: center; height: 100%; color: #6c757d;">
                            <div style="text-align: center;">
                                <div style="font-size: 48px; margin-bottom: 20px;">📄</div>
                                <div>Cliquez sur "Aperçu PDF" pour générer l'aperçu</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var modal = $('#pdf-preview-modal');
            var previewContent = $('#pdf-preview-content');
            var isLoading = false;

            // Ouvrir le modal d'aperçu
            $('#pdf-preview-btn').on('click', function() {
                if (isLoading) return;

                modal.show();
                loadPreview();
            });

            // Fermer le modal
            $('#pdf-preview-close').on('click', function() {
                modal.hide();
            });

            // Fermer en cliquant en dehors
            modal.on('click', function(e) {
                if (e.target === this) {
                    modal.hide();
                }
            });

            // Générer le PDF
            $('#pdf-generate-btn').on('click', function() {
                if (isLoading) return;

                isLoading = true;
                var $btn = $(this);
                var originalText = $btn.text();

                $btn.text('⏳ Génération...').prop('disabled', true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_generate_order_pdf',
                        nonce: $('#pdf_builder_order_nonce').val(),
                        order_id: <?php echo intval($order_id); ?>,
                        template_id: <?php echo intval($selected_template ? $selected_template['id'] : 0); ?>
                    },
                    success: function(response) {
                        if (response.success && response.data && response.data.url) {
                            window.open(response.data.url, '_blank');
                        } else {
                            alert('Erreur lors de la génération du PDF: ' + (response.data || 'Erreur inconnue'));
                        }
                    },
                    error: function() {
                        alert('Erreur de communication lors de la génération du PDF');
                    },
                    complete: function() {
                        $btn.text(originalText).prop('disabled', false);
                        isLoading = false;
                    }
                });
            });

            function loadPreview() {
                if (isLoading) return;

                isLoading = true;
                previewContent.html('<div style="display: flex; justify-content: center; align-items: center; height: 100%;"><div style="text-align: center;"><div style="font-size: 24px; margin-bottom: 10px;">⏳</div>Chargement de l\'aperçu...</div></div>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_generate_order_pdf_preview',
                        nonce: $('#pdf_builder_order_nonce').val(),
                        order_id: <?php echo intval($order_id); ?>,
                        template_id: <?php echo intval($selected_template ? $selected_template['id'] : 0); ?>,
                        preview_type: 'html'
                    },
                    success: function(response) {
                        if (response.success && response.data && response.data.html) {
                            previewContent.html(response.data.html);
                        } else {
                            previewContent.html('<div style="display: flex; justify-content: center; align-items: center; height: 100%; color: #dc3545;"><div style="text-align: center;"><div style="font-size: 48px; margin-bottom: 20px;">❌</div>Erreur lors du chargement de l\'aperçu<br><small>' + (response.data || 'Erreur inconnue') + '</small></div></div>');
                        }
                    },
                    error: function() {
                        previewContent.html('<div style="display: flex; justify-content: center; align-items: center; height: 100%; color: #dc3545;"><div style="text-align: center;"><div style="font-size: 48px; margin-bottom: 20px;">❌</div>Erreur de communication</div></div>');
                    },
                    complete: function() {
                        isLoading = false;
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * AJAX handler pour générer le PDF d'une commande
     */
    public function ajax_generate_order_pdf() {
        // Log immédiat pour vérifier si la fonction est appelée
        error_log('PDF BUILDER DEBUG: ajax_generate_order_pdf function STARTED');
        error_log('PDF BUILDER DEBUG: POST data: ' . print_r($_POST, true));
        error_log('PDF BUILDER DEBUG: REQUEST data: ' . print_r($_REQUEST, true));

        // Vérifier les permissions
        if (!current_user_can('manage_woocommerce')) {
            error_log('PDF BUILDER DEBUG: Permission check failed');
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            error_log('PDF BUILDER DEBUG: Nonce verification failed - received: ' . ($_POST['nonce'] ?? 'none'));
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        error_log("PDF BUILDER DEBUG: Parameters - order_id: $order_id, template_id: $template_id");

        if (!$order_id) {
            error_log('PDF BUILDER DEBUG: Order ID missing');
            wp_send_json_error('ID commande manquant');
        }

        try {
            // Charger la commande WooCommerce
            $order = wc_get_order($order_id);
            if (!$order) {
                error_log('PDF BUILDER DEBUG: Order not found');
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
                error_log('PDF BUILDER DEBUG: Template not found');
                wp_send_json_error('Template non trouvé');
            }

            // Extraire les éléments du template
            $elements = [];
            if (isset($template_data['pages']) && is_array($template_data['pages']) && !empty($template_data['pages'])) {
                $elements = $template_data['pages'][0]['elements'] ?? [];
            } elseif (isset($template_data['elements'])) {
                $elements = $template_data['elements'];
            }

            error_log('PDF BUILDER DEBUG: Elements count: ' . count($elements));

            // Générer le PDF SANS TCPDF - utiliser la nouvelle approche HTML
            error_log('PDF BUILDER DEBUG: About to generate PDF without TCPDF using new HTML approach');

            // Générer le HTML depuis les éléments du template
            $generator = new PDF_Builder_Pro_Generator();
            error_log('PDF BUILDER DEBUG: PDF_Builder_Pro_Generator instantiated');

            $html_content = $generator->generate($elements, ['is_preview' => false, 'order' => $order]);
            error_log('PDF BUILDER DEBUG: HTML generated, length: ' . strlen($html_content));

            // Créer un fichier HTML temporaire pour le téléchargement
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';
            error_log('PDF BUILDER DEBUG: Upload dir: ' . $upload_dir['basedir']);
            error_log('PDF BUILDER DEBUG: Temp dir: ' . $temp_dir);

            // Créer le dossier temporaire s'il n'existe pas
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
                error_log('PDF BUILDER DEBUG: Created temp directory');
            }

            // Générer un nom de fichier unique
            $filename = 'pdf-preview-' . $order_id . '-' . time() . '.html';
            $file_path = $temp_dir . '/' . $filename;
            error_log('PDF BUILDER DEBUG: File path: ' . $file_path);

            // Sauvegarder le HTML dans le fichier
            if (file_put_contents($file_path, $html_content) === false) {
                error_log('PDF BUILDER DEBUG: Failed to write HTML file');
                wp_send_json_error('Erreur lors de la création du fichier HTML');
            }

            error_log('PDF BUILDER DEBUG: HTML file written successfully');

            // Créer l'URL de téléchargement
            $download_url = $upload_dir['baseurl'] . '/pdf-builder-temp/' . $filename;
            error_log('PDF BUILDER DEBUG: Download URL: ' . $download_url);

            wp_send_json_success([
                'message' => 'HTML généré avec succès - TCPDF supprimé complètement',
                'url' => $download_url,
                'html_url' => $download_url,
                'pdf_url' => null
            ]);

        } catch (Exception $e) {
            error_log('PDF BUILDER DEBUG: Exception: ' . $e->getMessage());
            wp_send_json_error('Erreur Exception: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('PDF BUILDER DEBUG: Error: ' . $e->getMessage());
            wp_send_json_error('Erreur PHP: ' . $e->getMessage());
        } catch (Throwable $e) {
            error_log('PDF BUILDER DEBUG: Throwable: ' . $e->getMessage());
            wp_send_json_error('Erreur Throwable: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler pour générer l'aperçu PDF d'une commande
     */
    public function ajax_generate_order_pdf_preview() {
        // S'assurer que les headers JSON sont envoyés en premier
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        }

        // Gestionnaire d'erreur temporaire pour capturer les warnings PHP
        $original_error_handler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
            // Convertir les warnings et erreurs en exceptions
            if ($errno & (E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE)) {
                throw new Exception('PHP Warning: ' . $errstr . ' in ' . $errfile . ':' . $errline);
            }
            return false;
        });

        try {
            // Vérifier les permissions
            if (!current_user_can('manage_woocommerce') && !current_user_can('read')) {
                wp_send_json_error('Permissions insuffisantes');
            }


            // Vérification de sécurité - accepter plusieurs nonces pour flexibilité
            $valid_nonces = ['pdf_builder_order_actions', 'pdf_builder_template_actions'];
            $nonce_valid = false;

            foreach ($valid_nonces as $nonce_action) {
                if (wp_verify_nonce($_POST['nonce'] ?? '', $nonce_action)) {
                    $nonce_valid = true;
                    break;
                }
            }

            if (!$nonce_valid) {
                wp_send_json_error('Sécurité: Nonce invalide');
            }


            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
            $elements = isset($_POST['elements']) ? $_POST['elements'] : null;
            $preview_type = isset($_POST['preview_type']) ? $_POST['preview_type'] : 'html'; // 'pdf' ou 'html' - FORCER HTML POUR ÉVITER TCPDF

            // LOG DES ÉLÉMENTS REÇUS - DEBUG
            error_log('PDF PREVIEW DEBUG - Order ID: ' . $order_id);
            error_log('PDF PREVIEW DEBUG - Template ID: ' . $template_id);
            error_log('PDF PREVIEW DEBUG - Preview Type: ' . $preview_type);
            error_log('PDF PREVIEW DEBUG - Elements received: ' . (is_null($elements) ? 'NULL' : substr($elements, 0, 500) . '...'));
            error_log('PDF PREVIEW DEBUG - Elements is empty: ' . (empty($elements) ? 'YES' : 'NO'));



            // S'assurer que la classe PDF_Builder_Pro_Generator est chargée
            if (!class_exists('PDF_Builder_Pro_Generator')) {
                $generator_path = plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'includes/pdf-generator.php';
                if (file_exists($generator_path)) {
                    require_once $generator_path;
                } else {
                    wp_send_json_error('Fichier générateur PDF non trouvé');
                }
            }

            try {
                $generator = new PDF_Builder_Pro_Generator();
                error_log('PDF PREVIEW DEBUG - Generator instantiated successfully');
            } catch (Exception $e) {
                error_log('PDF PREVIEW DEBUG - Generator instantiation failed: ' . $e->getMessage());
                wp_send_json_error('Erreur d\'instanciation du générateur: ' . $e->getMessage());
            }

            // Déterminer le type d'aperçu
            error_log('PDF PREVIEW DEBUG - About to check elements condition');
            if (!empty($elements)) {
                error_log('PDF PREVIEW DEBUG - Entering elements branch (Canvas editor)');
                // Première branche pour les éléments du Canvas
                // Priorité aux éléments passés directement (depuis l'éditeur Canvas)

                // Récupérer l'order pour les variables dynamiques si order_id est fourni
                $order = null;
                if ($order_id && $order_id > 0) {
                    if (!class_exists('WooCommerce')) {
                        wp_send_json_error('WooCommerce n\'est pas installé ou activé');
                    }
                    $order = wc_get_order($order_id);
                    if (!$order) {
                        wp_send_json_error('Commande non trouvée');
                    }
                }

                // Nettoyer les slashes échappés par PHP (correction force)
                $clean_elements = stripslashes($elements);

                // Décoder les éléments
                $decoded_elements = json_decode($clean_elements, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_send_json_error('Données du template invalides');
                }

                // LOG DES ÉLÉMENTS DÉCODÉS
                error_log('PDF PREVIEW DEBUG - Decoded elements count: ' . count($decoded_elements));
                error_log('PDF PREVIEW DEBUG - Decoded elements: ' . json_encode($decoded_elements, JSON_PRETTY_PRINT));
                
                // Log des éléments individuels
                foreach ($decoded_elements as $index => $element) {
                    error_log('PDF PREVIEW DEBUG - Element ' . $index . ': type=' . ($element['type'] ?? 'unknown') . ', content=' . substr($element['content'] ?? '', 0, 100));
                }


                if ($preview_type === 'html') {
                    // Générer l'aperçu HTML avec les éléments du Canvas - SANS TCPDF
                    $options = ['is_preview' => true];
                    if ($order) {
                        $options['order'] = $order;
                        error_log('PDF PREVIEW DEBUG - Passing order to generator (Canvas elements), order ID: ' . $order->get_id());
                    } else {
                        error_log('PDF PREVIEW DEBUG - No order passed to generator (Canvas elements)');
                    }
                    $result = $generator->generate($decoded_elements, $options);
                } else {
                    // PLUS DE GÉNÉRATION PDF AVEC TCPDF - Forcer HTML
                    $options = ['is_preview' => true];
                    if ($order) {
                        $options['order'] = $order;
                        error_log('PDF PREVIEW DEBUG - Passing order to generator (Canvas elements, PDF mode), order ID: ' . $order->get_id());
                    } else {
                        error_log('PDF PREVIEW DEBUG - No order passed to generator (Canvas elements, PDF mode)');
                    }
                    $result = $generator->generate($decoded_elements, $options);
                }

                // LOG DU RÉSULTAT GÉNÉRÉ
                error_log('PDF PREVIEW DEBUG - Generation result: ' . substr($result, 0, 500) . '...');
                error_log('PDF PREVIEW DEBUG - Result length: ' . strlen($result));

            } elseif ($order_id && $order_id > 0) {
                // Aperçu de template depuis l'éditeur (éléments JSON)

                // Récupérer l'order pour les variables dynamiques
                if (!class_exists('WooCommerce')) {
                    wp_send_json_error('WooCommerce n\'est pas installé ou activé');
                }
                $order = wc_get_order($order_id);
                if (!$order) {
                    wp_send_json_error('Commande non trouvée');
                }

                // Nettoyer les slashes échappés par PHP (correction force)
                $clean_elements = stripslashes($elements);

                // Décoder les éléments
                $decoded_elements = json_decode($clean_elements, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_send_json_error('Données du template invalides');
                }


                if ($preview_type === 'html') {
                    // Générer l'aperçu HTML avec les éléments du template - SANS TCPDF
                    error_log('PDF PREVIEW DEBUG - Generating HTML preview for template elements with order ID: ' . ($order ? $order->get_id() : 'NULL'));
                    $result = $generator->generate($decoded_elements, ['is_preview' => true, 'order' => $order]);
                } else {
                    // PLUS DE GÉNÉRATION PDF AVEC TCPDF - Forcer HTML
                    error_log('PDF PREVIEW DEBUG - Generating PDF preview for template elements with order ID: ' . ($order ? $order->get_id() : 'NULL'));
                    $result = $generator->generate($decoded_elements, ['is_preview' => true, 'order' => $order]);
                }

            } elseif ($order_id && $order_id > 0) {
                error_log('PDF PREVIEW DEBUG - Entering order_id branch (WooCommerce metabox), order_id: ' . $order_id);
                // Aperçu de commande WooCommerce - récupérer depuis la base de données

                // Vérifier que WooCommerce est actif
                if (!class_exists('WooCommerce')) {
                    wp_send_json_error('WooCommerce n\'est pas installé ou activé');
                }

                $order = wc_get_order($order_id);
                if (!$order) {
                    wp_send_json_error('Commande non trouvée');
                }

                // Déterminer le template à utiliser
                if (!$template_id || $template_id <= 0) {
                    $template_id = $this->get_template_for_order($order);
                }

                // Pour l'aperçu, récupérer les éléments du template depuis la base de données
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';
                $template = $wpdb->get_row($wpdb->prepare("SELECT id, name, template_data FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);

                error_log('PDF PREVIEW DEBUG - Template query result: ' . ($template ? 'FOUND' : 'NOT FOUND'));
                if ($template) {
                    error_log('PDF PREVIEW DEBUG - Template name: ' . $template['name']);
                    error_log('PDF PREVIEW DEBUG - Template data length: ' . strlen($template['template_data']));
                    error_log('PDF PREVIEW DEBUG - Template data preview: ' . substr($template['template_data'], 0, 200));
                }

                if (!$template) {
                    wp_send_json_error('Template non trouvé');
                }

                $template_data = json_decode($template['template_data'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('PDF PREVIEW DEBUG - Template data decode error: ' . json_last_error_msg());
                    error_log('PDF PREVIEW DEBUG - Raw template_data: ' . substr($template['template_data'], 0, 500));
                    // Essayer de récupérer avec stripslashes au cas où
                    $clean_template_data = stripslashes($template['template_data']);
                    $template_data = json_decode($clean_template_data, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        wp_send_json_error('Données du template invalides: ' . json_last_error_msg());
                    }
                }

                $elements_for_pdf = isset($template_data['elements']) ? $template_data['elements'] : [];

                if (empty($elements_for_pdf)) {
                    error_log('PDF PREVIEW DEBUG - Template has no elements, using fallback');
                    // Template vide, utiliser un élément de fallback
                    $elements_for_pdf = [
                        [
                            'type' => 'text',
                            'content' => 'Template vide - Veuillez ajouter des éléments dans l\'éditeur',
                            'position' => ['x' => 20, 'y' => 50],
                            'size' => ['width' => 150, 'height' => 20],
                            'style' => ['fontSize' => 12, 'fontWeight' => 'normal']
                        ]
                    ];
                }

                error_log('PDF PREVIEW DEBUG - Elements for PDF generation: ' . json_encode($elements_for_pdf));
                error_log('PDF PREVIEW DEBUG - Order object for generation: ' . ($order ? 'EXISTS (ID: ' . $order->get_id() . ')' : 'NULL'));

                // Générer l'aperçu PDF
                $result = $generator->generate($elements_for_pdf, ['is_preview' => true, 'order' => $order]);

                error_log('PDF PREVIEW DEBUG - Generator result type: ' . gettype($result));
                error_log('PDF PREVIEW DEBUG - Generator result length: ' . (is_string($result) ? strlen($result) : 'N/A'));

            } else {
                error_log('PDF PREVIEW DEBUG - Entering invalid context branch - no valid conditions met');
                wp_send_json_error('Contexte d\'aperçu invalide');
            }

            if (!$result) {
                wp_send_json_error('Erreur lors de la génération de l\'aperçu');
            }

            // Gérer les différents types d'aperçu
            if ($preview_type === 'html') {
                // Pour l'aperçu HTML, retourner directement le HTML généré
                error_log('PDF PREVIEW DEBUG - Sending HTML response, length: ' . strlen($result));
                wp_send_json_success(['html' => $result]);
            } else {
                // Pour l'aperçu PDF, vérifier le fichier et retourner l'URL

                // Vérifier si le fichier existe réellement
                $file_path = str_replace(home_url('/'), ABSPATH, $result);
                if (file_exists($file_path)) {
                }

                wp_send_json_success(['url' => $result]);
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        } catch (Throwable $t) {
            wp_send_json_error('Erreur fatale: ' . $t->getMessage());
        } finally {
            // Restaurer le gestionnaire d'erreur original
            if (isset($original_error_handler)) {
                set_error_handler($original_error_handler);
            }
        }
    }

    /**
     * Helper pour obtenir le nonce
     */
    private function get_nonce() {
        return wp_create_nonce('pdf_builder_order_actions');
    }

    /**
     * Helper pour obtenir l'URL AJAX
     */
    private function get_ajax_url() {
        return admin_url('admin-ajax.php');
    }

    /**
     * Récupère l'ID du template approprié pour une commande donnée
     */
    private function get_template_for_order($order) {
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
     * Rend le HTML du canvas d'aperçu avec les vraies données de la commande
     */
    private function render_preview_canvas($elements, $order, $canvas_width, $canvas_height) {
        error_log('PDF Builder: render_preview_canvas called with ' . count($elements) . ' elements');
        $html = '';

        if (!is_array($elements) || empty($elements)) {
            error_log('PDF Builder: No elements to render');
            return $html;
        }

        error_log('PDF Builder: Processing elements loop');
        foreach ($elements as $index => $element) {
            // Vérifier les propriétés essentielles
            $x = floatval($element['position']['x'] ?? $element['x'] ?? 0);
            $y = floatval($element['position']['y'] ?? $element['y'] ?? 0);
            $width = floatval($element['position']['width'] ?? $element['size']['width'] ?? $element['width'] ?? 100);
            $height = floatval($element['position']['height'] ?? $element['size']['height'] ?? $element['height'] ?? 50);
            $type = $element['type'] ?? 'text';
            $visible = isset($element['visible']) ? (bool)$element['visible'] : true;

            if (!$visible) {
                continue;
            }

            // Style de base - positionnement
            $style = "position: absolute; left: {$x}px; top: {$y}px; width: {$width}px; height: {$height}px; ";

            // Ajouter TOUS les styles CSS via build_element_style
            $style = $this->build_element_style($element, $style);

            // Contenu de l'élément
            $content = $this->render_element_content($element, $order);

            // Classes CSS
            $classes = 'canvas-element';
            if (isset($element['className'])) {
                $classes .= ' ' . esc_attr($element['className']);
            }

            // Générer l'élément HTML
            $html .= "<div class=\"{$classes}\" style=\"{$style}\">{$content}</div>";
        }

        return $html;
    }

    /**
     * Construit le style CSS d'un élément
     */
    private function build_element_style($element, $base_style) {
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
    private function render_element_content($element, $order) {
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
                return wp_kses($content, [
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
                ]);

            default:
                return esc_html($content);
        }
    }

    /**
     * Remplace les variables de commande dans le contenu
     */
    private function replace_order_variables($content, $order) {
        error_log('REPLACE VARIABLES - Input content: ' . substr($content, 0, 200) . '...');
        error_log('REPLACE VARIABLES - Order object exists: ' . ($order ? 'YES' : 'NO'));
        
        if (!$order) {
            error_log('REPLACE VARIABLES - No order object, returning original content');
            return $content;
        }

        error_log('REPLACE VARIABLES - Order ID: ' . $order->get_id());
        error_log('REPLACE VARIABLES - Order status: ' . $order->get_status());
        error_log('REPLACE VARIABLES - Order total: ' . $order->get_total());
        error_log('REPLACE VARIABLES - Order date: ' . ($order->get_date_created() ? $order->get_date_created()->format('Y-m-d H:i:s') : 'null'));

        // Basic order information
        $replacements = [
            '{{order_number}}' => $order->get_order_number(),
            '{{order_date}}' => $order->get_date_created() ? $order->get_date_created()->format('d/m/Y') : '',
            '{{order_total}}' => $order->get_formatted_order_total(),
            '{{order_status}}' => wc_get_order_status_name($order->get_status()),
            '{{customer_name}}' => $order->get_formatted_billing_full_name(),
            '{{customer_email}}' => $order->get_billing_email() ?: '',
            '{{customer_phone}}' => $order->get_billing_phone() ?: '',
            '{{billing_address}}' => $order->get_formatted_billing_address() ?: '',
            '{{shipping_address}}' => $order->get_formatted_shipping_address() ?: '',
            '{{payment_method}}' => $order->get_payment_method_title(),
            '{{shipping_method}}' => $order->get_shipping_method(),
        ];

        // Financial information
        $subtotal = $order->get_subtotal();
        $total_tax = $order->get_total_tax();
        $shipping_total = $order->get_shipping_total();
        $discount_total = $order->get_discount_total();

        $replacements = array_merge($replacements, [
            '{{subtotal}}' => wc_price($subtotal),
            '{{tax_amount}}' => wc_price($total_tax),
            '{{shipping_amount}}' => wc_price($shipping_total),
            '{{discount_amount}}' => wc_price($discount_total),
            '{{total_excl_tax}}' => wc_price($order->get_total() - $total_tax),
        ]);

        // Handle product-specific variables (for single product display)
        $items = $order->get_items();
        if (!empty($items)) {
            $first_item = reset($items);

            $replacements = array_merge($replacements, [
                '{{product_name}}' => $first_item->get_name(),
                '{{product_qty}}' => $first_item->get_quantity(),
                '{{product_price}}' => wc_price($first_item->get_total() / $first_item->get_quantity()),
                '{{product_total}}' => wc_price($first_item->get_total()),
                '{{product_sku}}' => $first_item->get_product() ? $first_item->get_product()->get_sku() : '',
            ]);

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

        error_log('REPLACE VARIABLES - Total replacements available: ' . count($replacements));
        error_log('REPLACE VARIABLES - Replacement keys: ' . json_encode(array_keys($replacements)));
        error_log('REPLACE VARIABLES - Sample values: ' . json_encode(array_slice($replacements, 0, 10, true)));

        $result = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        error_log('REPLACE VARIABLES - Final result: ' . substr($result, 0, 200) . '...');
        error_log('REPLACE VARIABLES - Content was modified: ' . ($content !== $result ? 'YES' : 'NO'));
        
        return $result;
    }
}
