<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange

namespace PDF_Builder\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

use WC_Order;

/**
 * Ajoute une metabox de prévisualisation PDF sur la page de commande WooCommerce
 */
class PDF_Builder_Order_Metabox
{
    public static function register(): void
    {
        add_action('add_meta_boxes', [self::class, 'add_metabox']);
        // Support HPOS WooCommerce 7.1+
        add_action('add_meta_boxes_woocommerce_page_wc-orders', [self::class, 'add_metabox']);
        add_action('wp_ajax_pdf_builder_preview_order_pdf', [self::class, 'handle_preview_ajax']);
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [self::class, 'handle_generate_pdf_ajax']);
    }

    /**
     * Ajoute la metabox sur la page de commande
     */
    public static function add_metabox(): void
    {
        if (!class_exists('WC_Order')) {
            return;
        }

        $screens = ['shop_order', 'woocommerce_page_wc-orders'];
        foreach ($screens as $screen) {
            add_meta_box(
                'pdf_builder_order_preview',
                '📄 Générateur PDF',
                [self::class, 'render_metabox'],
                $screen,
                'normal',
                'high'
            );
        }
    }

    /**
     * Affiche la metabox
     */
    public static function render_metabox($post_or_order): void
    {
        // Compatibilité legacy (WP_Post) et HPOS (WC_Order)
        if ($post_or_order instanceof WC_Order) {
            $order = $post_or_order;
            $order_id = $order->get_id();
        } elseif (is_a($post_or_order, 'WP_Post')) {
            $order_id = $post_or_order->ID;
            $order = function_exists('wc_get_order') ? wc_get_order($order_id) : null;
        } else {
            $order_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
            $order = function_exists('wc_get_order') ? wc_get_order($order_id) : null;
        }

        if (!$order_id) {
            echo '<p style="color:#dc3545;">❌ Commande introuvable.</p>';
            return;
        }
        
        // Récupérer tous les templates disponibles
        $templates = self::get_available_templates();
        
        if (empty($templates)) {
            echo '<p style="color: #666; font-style: italic;">❌ Aucun modèle PDF disponible. <a href="' . esc_url(admin_url('admin.php?page=pdf-builder-settings')) . '">Créer un modèle</a></p>';
            return;
        }

        wp_nonce_field('pdf_builder_order_preview', 'pdf_builder_nonce');
        ?>
        <div id="pdf-builder-metabox" style="padding: 20px; background: #f9f9f9; border-radius: 8px;">
            
            <!-- Sélection du modèle -->
            <div style="margin-bottom: 20px;">
                <label for="pdf_builder_template" style="display: block; margin-bottom: 8px; font-weight: bold;">
                    Sélectionner un modèle:
                </label>
                <select id="pdf_builder_template" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">-- Choisir un modèle --</option>
                    <?php foreach ($templates as $id => $name): ?>
                        <option value="<?php echo esc_attr($id); ?>">
                            <?php echo esc_html($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Boutons d'action -->
            <div style="display: flex; gap: 10px;">
                <button 
                    type="button" 
                    id="pdf_builder_preview_btn"
                    class="button button-secondary"
                    data-order-id="<?php echo esc_attr($order_id); ?>"
                    style="flex: 1;"
                >
                    👁️ Aperçu HTML
                </button>
                <button 
                    type="button" 
                    id="pdf_builder_download_btn"
                    class="button button-primary"
                    data-order-id="<?php echo esc_attr($order_id); ?>"
                    style="flex: 1;"
                >
                    📥 Télécharger PDF
                </button>
            </div>

            <!-- Zone de prévisualisation -->
            <div id="pdf-builder-preview" style="margin-top: 20px; display: none;">
                <div style="border: 1px solid #ccc; border-radius: 4px; padding: 20px; background: white;">
                    <iframe id="pdf-preview-frame" style="width: 100%; height: 600px; border: none;"></iframe>
                </div>
            </div>

            <!-- Statut de chargement -->
            <div id="pdf-builder-loading" style="display: none; text-align: center; margin-top: 20px;">
                <p>⏳ Génération en cours...</p>
                <div style="width: 100%; height: 4px; background: #eee; border-radius: 4px; overflow: hidden;">
                    <div style="width: 100%; height: 100%; background: #0073aa; animation: pulse 1.5s infinite;">
                    </div>
                </div>
            </div>

            <!-- Messages d'erreur -->
            <div id="pdf-builder-error" style="display: none; margin-top: 20px; padding: 12px; background: #fee; border: 1px solid #fcc; border-radius: 4px; color: #c00;">
            </div>
        </div>



        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const orderId = document.getElementById('pdf_builder_preview_btn')?.dataset.orderId;
                const nonce = document.querySelector('[name="pdf_builder_nonce"]')?.value;
                const templateSelect = document.getElementById('pdf_builder_template');
                const previewBtn = document.getElementById('pdf_builder_preview_btn');
                const downloadBtn = document.getElementById('pdf_builder_download_btn');
                const previewArea = document.getElementById('pdf-builder-preview');
                const loadingArea = document.getElementById('pdf-builder-loading');
                const errorArea = document.getElementById('pdf-builder-error');

                previewBtn?.addEventListener('click', function() {
                    const templateId = templateSelect.value;
                    if (!templateId) {
                        showError('Veuillez sélectionner un modèle');
                        return;
                    }
                    generatePreview(templateId, orderId, nonce);
                });

                downloadBtn?.addEventListener('click', function() {
                    const templateId = templateSelect.value;
                    if (!templateId) {
                        showError('Veuillez sélectionner un modèle');
                        return;
                    }
                    downloadPDF(templateId, orderId, nonce);
                });

                function generatePreview(templateId, orderId, nonce) {
                    showLoading(true);
                    errorArea.style.display = 'none';

                    fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'pdf_builder_preview_order_pdf',
                            template_id: templateId,
                            order_id: orderId,
                            nonce: nonce
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        showLoading(false);
                        
                        if (data.success) {
                            const iframe = document.getElementById('pdf-preview-frame');
                            iframe.srcdoc = data.data.html;
                            previewArea.style.display = 'block';
                        } else {
                            showError(data.data?.error || 'Erreur lors de la génération');
                        }
                    })
                    .catch(error => {
                        showLoading(false);
                        showError('Erreur réseau: ' + error.message);
                    });
                }

                function downloadPDF(templateId, orderId, nonce) {
                    showLoading(true);
                    errorArea.style.display = 'none';

                    // Simplement charger la page qui génère le PDF
                    const url = new URL('<?php echo esc_url(admin_url('admin-ajax.php')); ?>');
                    url.searchParams.set('action', 'pdf_builder_generate_order_pdf');
                    url.searchParams.set('template_id', templateId);
                    url.searchParams.set('order_id', orderId);
                    url.searchParams.set('nonce', nonce);
                    
                    window.location.href = url.toString();
                    showLoading(false);
                }

                function showLoading(show) {
                    loadingArea.style.display = show ? 'block' : 'none';
                }

                function showError(message) {
                    errorArea.textContent = '❌ ' + message;
                    errorArea.style.display = 'block';
                }
            });
        </script>
        <?php
    }

    /**
     * Handle AJAX pour la prévisualisation
     */
    public static function handle_preview_ajax(): void
    {
        check_ajax_referer('pdf_builder_order_preview', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['error' => 'Permission refusée']);
        }

        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $order_id = intval($_POST['order_id'] ?? 0);

        if (!$template_id || !$order_id) {
            wp_send_json_error(['error' => 'Paramètres manquants']);
        }

        try {
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error(['error' => 'Commande introuvable']);
            }

            // Récupérer le template
            $template = self::get_template($template_id);
            if (!$template) {
                wp_send_json_error(['error' => 'Modèle introuvable']);
            }

            // Générer l'HTML avec les vraies données
            $html = self::generate_template_html($template, $order);

            wp_send_json_success(['html' => $html]);
        } catch (\Exception $e) {
            wp_send_json_error(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle AJAX pour la génération PDF
     */
    public static function handle_generate_pdf_ajax(): void
    {
        check_ajax_referer('pdf_builder_order_preview', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_die('Permission refusée', '', ['response' => 403]);
        }

        $template_id = sanitize_text_field($_GET['template_id'] ?? '');
        $order_id = intval($_GET['order_id'] ?? 0);

        if (!$template_id || !$order_id) {
            wp_die('Paramètres manquants', '', ['response' => 400]);
        }

        try {
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_die('Commande introuvable', '', ['response' => 404]);
            }

            $template = self::get_template($template_id);
            if (!$template) {
                wp_die('Modèle introuvable', '', ['response' => 404]);
            }

            // Générer l'HTML
            $html = self::generate_template_html($template, $order);

            // TODO: Convertir HTML en PDF avec dompdf/wkhtmltopdf
            // Pour l'instant, retourner l'HTML
            header('Content-Type: text/html; charset=utf-8');
            echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML template content
            exit;
        } catch (\Exception $e) {
            wp_die('Erreur: ' . $e->getMessage(), '', ['response' => 500]); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
        }
    }

    /**
     * Récupère tous les templates disponibles
     */
    private static function get_available_templates(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'pdf_builder_templates';
        $rows = $wpdb->get_results("SELECT id, name FROM {$table} ORDER BY name ASC", ARRAY_A);

        $templates = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $templates[$row['id']] = $row['name'];
            }
        }

        return $templates;
    }

    /**
     * Récupère un template spécifique
     */
    private static function get_template(string $template_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'pdf_builder_templates';
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", intval($template_id)),
            ARRAY_A
        );

        if ($row && !empty($row['template_data'])) {
            $row['data'] = json_decode($row['template_data'], true);
        }

        return $row ?: null;
    }

    /**
     * Génère l'HTML du template avec les vraies données de commande
     */
    private static function generate_template_html(array $template, WC_Order $order): string
    {
        // Utiliser l'OrderDataExtractor pour récupérer les données
        $data_extractor = new \PDF_Builder\Generators\OrderDataExtractor($order);
        $all_data = $data_extractor->get_all_data();

        // Pour l'instant, retourner une version simple
        // Plus tard, on utilisera HTMLGenerators pour le rendu complet
        $html = '<html><head><meta charset="UTF-8"><title>Aperçu PDF</title></head><body>';
        $html .= '<h1>' . esc_html($template['name'] ?? 'Template') . '</h1>';
        $html .= '<pre>' . htmlspecialchars(json_encode($all_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
        $html .= '</body></html>';

        return $html;
    }
}
