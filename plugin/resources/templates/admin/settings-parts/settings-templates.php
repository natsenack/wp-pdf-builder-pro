<?php
/**
 * Templates par statut tab content
 * Gestion des templates PDF par défaut selon le statut des commandes WooCommerce
 * Supporte les statuts personnalisés ajoutés par des plugins tiers
 * Updated: 2025-12-02 - Code réorganisé pour une meilleure lisibilité
 */

// Inclure les fonctions helper nécessaires
require_once __DIR__ . '/settings-helpers.php';

// require_once __DIR__ . '/../settings-helpers.php'; // REMOVED - settings-helpers.php deleted

// =============================================================================
// CLASSE UTILITAIRE POUR LA GESTION DES STATUTS ET PLUGINS
// =============================================================================
class PDF_Template_Status_Manager {

    private static $instance = null;
    private $woocommerce_active = false;
    private $order_statuses = [];
    private $custom_status_plugins = [];
    private $status_plugins = [];
    private $templates = [];
    private $current_mappings = [];

    private function __construct() {
        $this->init_woocommerce_status();
        $this->load_templates();
        $this->load_mappings();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Initialisation des statuts WooCommerce
    private function init_woocommerce_status() {
        $this->woocommerce_active = class_exists('WooCommerce');

        if (!$this->woocommerce_active) {
            return;
        }

        $this->order_statuses = $this->get_order_statuses();
        $this->detect_custom_statuses();
    }

    // Récupération des statuts de commande
    public function get_order_statuses() {
        if (function_exists('wc_get_order_statuses')) {
            return wc_get_order_statuses();
        } elseif (class_exists('WC_Order') && method_exists('WC_Order', 'get_statuses')) {
            return WC_Order::get_statuses();
        } else {
            $statuses = pdf_builder_safe_get_option('wc_order_statuses', []);
            return !empty($statuses) ? $statuses : [
                'wc-pending' => 'En attente de paiement',
                'wc-processing' => 'En cours',
                'wc-on-hold' => 'En attente',
                'wc-completed' => 'Terminée',
                'wc-cancelled' => 'Annulée',
                'wc-refunded' => 'Remboursée',
                'wc-failed' => 'Échec'
            ];
        }
    }

    // Détection des statuts personnalisés
    private function detect_custom_statuses() {
        $default_statuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'draft', 'checkout-draft'];    

        foreach ($this->order_statuses as $status_key => $status_name) {
            $clean_status_key = str_replace('wc-', '', $status_key);

            if (!in_array($clean_status_key, $default_statuses)) {
                $plugin_name = $this->detect_custom_status_plugin($clean_status_key);
                if ($plugin_name) {
                    $this->custom_status_plugins[] = $plugin_name;
                    $this->status_plugins[$status_key] = $plugin_name;
                }
            }
        }

        $this->custom_status_plugins = array_unique($this->custom_status_plugins);
    }

    // Chargement des templates
    private function load_templates() {
        global $wpdb;

        // Templates WordPress
        $templates_wp = $wpdb->get_results("
            SELECT ID, post_title
            FROM {$wpdb->posts}
            WHERE post_type = 'pdf_template'
            AND post_status = 'publish'
            ORDER BY post_title ASC
        ", ARRAY_A);

        $wp_templates = [];
        if ($templates_wp) {
            foreach ($templates_wp as $template) {
                $wp_templates[$template['ID']] = $template['post_title'];
            }
        }

        // Templates personnalisés
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $templates_custom = $wpdb->get_results("
            SELECT id, name
            FROM {$table_templates}
            ORDER BY name ASC
        ", ARRAY_A);

        $custom_templates = [];
        if ($templates_custom) {
            foreach ($templates_custom as $template) {
                $custom_templates['custom_' . $template['id']] = $template['name'];
            }
        }

        $this->templates = array_merge($wp_templates, $custom_templates);
    }

    // Chargement des mappings
    private function load_mappings() {
        $this->current_mappings = pdf_builder_safe_get_option('pdf_builder_order_status_templates', []);

        // S'assurer que c'est un tableau
        if (!is_array($this->current_mappings)) {
            $this->current_mappings = [];
        }

        // Nettoyer les mappings obsolètes
        if (!empty($this->current_mappings) && !empty($this->order_statuses)) {
            $valid_statuses = array_keys($this->order_statuses);
            $this->current_mappings = array_intersect_key($this->current_mappings, array_flip($valid_statuses));

            // Sauvegarder si nécessaire
            if (count($this->current_mappings) !== count(pdf_builder_safe_get_option('pdf_builder_order_status_templates', []))) {
                update_option('pdf_builder_order_status_templates', $this->current_mappings);
            }
        }
    }

    // Détection du plugin pour un statut personnalisé
    private function detect_custom_status_plugin($status_key) {
        // 1. Vérifier les options WooCommerce
        $custom_statuses = pdf_builder_safe_get_option('wc_order_statuses', []);
        if (!empty($custom_statuses) && isset($custom_statuses['wc-' . $status_key])) {
            $status_data = $custom_statuses['wc-' . $status_key];
            if (is_array($status_data) && isset($status_data['label'])) {
                return $this->detect_plugin_from_status_data($status_data, $status_key);
            }
        }

        // 2. Chercher dans les plugins actifs
        $detected_plugin = $this->detect_plugin_from_active_plugins($status_key);
        if ($detected_plugin) {
            return $detected_plugin;
        }

        // 3. Analyse des patterns (fallback)
        return $this->detect_plugin_from_patterns($status_key);
    }

    // Détection depuis les données du statut
    private function detect_plugin_from_status_data($status_data, $status_key) {
        global $wpdb;

        $plugin_indicators = [
            'wc_order_status_manager' => [
                'options' => ['wc_order_status_manager', 'wc_osm_'],
                'transient_prefix' => 'wc_osm_'
            ],
            'yith_custom_order_status' => [
                'options' => ['yith_wccos', 'yith_custom_order_status'],
                'transient_prefix' => 'yith_wccos_'
            ],
            'custom_order_status' => [
                'options' => ['custom_order_status', 'alg_wc_custom_order_status'],
                'transient_prefix' => 'alg_wc_cos_'
            ],
        ];

        foreach ($plugin_indicators as $plugin_key => $indicators) {
            foreach ($indicators['options'] as $option_pattern) {
                $option_exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1",
                    $option_pattern . '%'
                ));
                if ($option_exists) {
                    return $this->get_plugin_display_name($plugin_key);
                }
            }

            if (!empty($indicators['transient_prefix'])) {
                $transient_exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1",
                    '_transient_' . $indicators['transient_prefix'] . '%'
                ));
                if ($transient_exists) {
                    return $this->get_plugin_display_name($plugin_key);
                }
            }
        }

        return null;
    }

    // Détection depuis les plugins actifs
    private function detect_plugin_from_active_plugins($status_key) {
        global $wpdb;

        $active_plugins = pdf_builder_safe_get_option('active_plugins', []);
        $excluded_plugins = ['woocommerce/woocommerce.php'];
        $active_plugins = array_diff($active_plugins, $excluded_plugins);

        // Analyse des options de base de données
        foreach ($active_plugins as $plugin_file) {
            $plugin_slug = dirname($plugin_file);

            $plugin_options = $wpdb->get_results($wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options}
                WHERE option_name LIKE %s
                AND (option_name LIKE %s OR option_name LIKE %s)
                LIMIT 5",
                $plugin_slug . '%', '%status%', '%order%'
            ));

            if (!empty($plugin_options)) {
                $plugin_name = $this->get_plugin_display_name_from_file($plugin_file);
                if ($plugin_name && $plugin_name !== 'Plugin inconnu') {
                    return $plugin_name;
                }
            }
        }

        // Analyse des transients
        foreach ($active_plugins as $plugin_file) {
            $plugin_slug = dirname($plugin_file);

            $transient_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options}
                WHERE option_name LIKE %s
                AND (option_name LIKE %s OR option_name LIKE %s)
                LIMIT 1",
                '_transient_' . $plugin_slug . '%', '%status%', '%order%'
            ));

            if ($transient_exists) {
                $plugin_name = $this->get_plugin_display_name_from_file($plugin_file);
                if ($plugin_name && $plugin_name !== 'Plugin inconnu') {
                    return $plugin_name;
                }
            }
        }

        // Analyse des headers des plugins
        foreach ($active_plugins as $plugin_file) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
            $search_text = strtolower($plugin_data['Name'] . ' ' . $plugin_data['Description']);

            if (strpos($search_text, 'status') !== false && strpos($search_text, 'order') !== false) {
                return $plugin_data['Name'];
            }

            if (strpos($search_text, 'expédition') !== false || strpos($search_text, 'shipping') !== false) {
                return $plugin_data['Name'] . ' (Expédition)';
            }

            if (strpos($search_text, 'marketplace') !== false || strpos($search_text, 'vendor') !== false) {
                return $plugin_data['Name'] . ' (Marketplace)';
            }
        }

        return null;
    }

    // Détection par patterns
    private function detect_plugin_from_patterns($status_key) {
        $status_patterns = [
            // Expédition
            'shipped' => 'Plugin d\'expédition',
            'delivered' => 'Plugin de livraison',
            'ready_to_ship' => 'Plugin d\'expédition',
            'partial_shipment' => 'Plugin d\'expédition partielle',
            'in_transit' => 'Plugin de suivi d\'expédition',
            'out_for_delivery' => 'Plugin de livraison',
            'shipped_partial' => 'Plugin d\'expédition partielle',

            // Préparation
            'packed' => 'Plugin de préparation de commande',
            'packing' => 'Plugin de préparation de commande',
            'ready_for_pickup' => 'Plugin de préparation de commande',
            'prepared' => 'Plugin de préparation de commande',

            // Paiement
            'awaiting_payment' => 'Plugin de paiement personnalisé',
            'payment_pending' => 'Plugin de paiement personnalisé',
            'payment_confirmed' => 'Plugin de paiement personnalisé',
            'payment_failed' => 'Plugin de paiement personnalisé',
            'payment_cancelled' => 'Plugin de paiement personnalisé',

            // Retours
            'return_requested' => 'Plugin de gestion des retours',
            'return_approved' => 'Plugin de gestion des retours',
            'return_received' => 'Plugin de gestion des retours',
            'refund_pending' => 'Plugin de remboursement personnalisé',
            'refund_issued' => 'Plugin de remboursement personnalisé',

            // Marketplace
            'vendor_pending' => 'Plugin marketplace',
            'vendor_approved' => 'Plugin marketplace',
            'vendor_rejected' => 'Plugin marketplace',
            'commission_pending' => 'Plugin marketplace',
            'commission_paid' => 'Plugin marketplace',
        ];

        if (isset($status_patterns[$status_key])) {
            return $status_patterns[$status_key];
        }

        foreach ($status_patterns as $pattern => $plugin_type) {
            if (strpos($status_key, $pattern) !== false || strpos($pattern, $status_key) !== false) {
                return $plugin_type;
            }
        }

        return null;
    }

    // Utilitaires pour les noms de plugins
    private function get_plugin_display_name($plugin_key) {
        $plugin_names = [
            'wc_order_status_manager' => 'WooCommerce Order Status Manager',
            'yith_custom_order_status' => 'YITH WooCommerce Custom Order Status',
            'woobewoo_order_status' => 'WooBeWoo Order Status',
            'custom_order_status' => 'Custom Order Status for WooCommerce',
            'order_status_actions' => 'WooCommerce Order Status & Actions Manager',
            'table_rate_shipping' => 'WooCommerce Table Rate Shipping',
            'shipment_tracking' => 'WooCommerce Shipment Tracking',
            'dokan' => 'Dokan (Marketplace)',
            'wc_vendors' => 'WC Vendors (Marketplace)',
            'product_vendors' => 'WooCommerce Product Vendors',
        ];

        return isset($plugin_names[$plugin_key]) ? $plugin_names[$plugin_key] : ucfirst(str_replace('_', ' ', $plugin_key));
    }

    private function get_plugin_display_name_from_file($plugin_file) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);

        if (!empty($plugin_data['Name'])) {
            return $plugin_data['Name'];
        }

        $plugin_slug = dirname($plugin_file);
        return ucwords(str_replace(['-', '_'], ' ', $plugin_slug));
    }

    // Getters publics
    public function is_woocommerce_active() {
        return $this->woocommerce_active;
    }

    public function get_custom_status_plugins() {
        return $this->custom_status_plugins;
    }

    public function get_status_plugins() {
        return $this->status_plugins;
    }

    public function get_templates() {
        return $this->templates;
    }

    public function get_current_mappings() {
        return $this->current_mappings;
    }

    public function is_custom_status($status_key) {
        $default_statuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'draft', 'checkout-draft'];    
        $clean_status_key = str_replace('wc-', '', $status_key);
        return !in_array($clean_status_key, $default_statuses);
    }
}

// =============================================================================
// INITIALISATION ET RÉCUPÉRATION DES DONNÉES
// =============================================================================

$status_manager = PDF_Template_Status_Manager::get_instance();
$woocommerce_active = $status_manager->is_woocommerce_active();
$order_statuses = $status_manager->get_order_statuses();
$custom_status_plugins = $status_manager->get_custom_status_plugins();
$status_plugins = $status_manager->get_status_plugins();
$templates = $status_manager->get_templates();
$current_mappings = $status_manager->get_current_mappings();

// Debug temporaire
error_log("DEBUG Template Load: current_mappings = " . json_encode($current_mappings));
error_log("DEBUG Template Load: templates = " . json_encode($templates));

// =============================================================================
// AFFICHAGE HTML
// =============================================================================
?>
<section class="templates-status-wrapper">
    <!-- En-tête -->
    <header>
        <h2 style="display: flex; justify-content: space-between; align-items: center;">
            <span>[TEMPLATES] Templates par Statut de Commande</span>
            <?php if (!empty($custom_status_plugins)): ?>
                <span style="font-size: 14px; font-weight: normal; color: #666;">
                    [PLUGINS] Plugins détectés: <?php echo esc_html(implode(', ', $custom_status_plugins)); ?>
                </span>
            <?php elseif ($woocommerce_active && !empty($order_statuses)): ?>
                <span style="font-size: 14px; font-weight: normal; color: #28a745;">
                    ✅ Statuts WooCommerce standards uniquement
                </span>
            <?php endif; ?>
        </h2>
    </header>

    <!-- Contenu principal -->
    <main>
        <?php if (!$woocommerce_active): ?>
            <!-- Message d'avertissement WooCommerce -->
            <div class="notice notice-warning">
                <p><strong>[WARNING] WooCommerce n'est pas actif</strong></p>
                <p>Cette fonctionnalité nécessite WooCommerce pour fonctionner. Veuillez installer et activer WooCommerce.</p>
            </div>
        <?php else: ?>
            <!-- Formulaire de configuration -->
            <form method="post" action="" id="templates-status-form" data-custom-save="true">
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>

                <!-- Grille des statuts -->
                <div class="templates-status-grid">
                    <?php foreach ($order_statuses as $status_key => $status_label):
                        $is_custom_status = $status_manager->is_custom_status($status_key);
                    ?>
                        <article class="template-status-card <?php echo $is_custom_status ? 'custom-status-card' : ''; ?>">
                            <header>
                                <h4>
                                    <?php echo esc_html($status_label); ?>
                                    <?php if ($is_custom_status): ?>
                                        <?php
                                        $detected_plugin = isset($status_plugins[$status_key]) ? $status_plugins[$status_key] : 'Plugin inconnu'
;                                                                                                                                                                                       $tooltip_text = "Slug personnalisé détecté - ajouté par: {$detected_plugin}";
                                        ?>
                                        <span class="custom-status-indicator"
                                              data-tooltip="<?php echo esc_attr($tooltip_text); ?>"
                                              style="font-family: Arial, sans-serif;">[SEARCH]</span>
                                    <?php endif; ?>
                                </h4>
                            </header>

                            <!-- Sélecteur de template -->
                            <div class="template-selector">
                                <label for="template_<?php echo esc_attr($status_key); ?>">
                                    Template par défaut :
                                </label>
                                <select name="pdf_builder_order_status_templates[<?php echo esc_attr($status_key); ?>]"
                                        id="template_<?php echo esc_attr($status_key); ?>"
                                        class="template-select">
                                    <option value="">-- Aucun template --</option>
                                    <?php foreach ($templates as $template_id => $template_title):
                                        $current_value = isset($current_mappings[$status_key]) ? trim((string)$current_mappings[$status_key]) : '';
                                        $is_selected = $current_value === (string)$template_id;
                                        // Debug temporaire
                                        if ($status_key === 'wc-completed') {
                                            error_log("DEBUG Select: status=$status_key, current_value='$current_value', template_id='$template_id', is_selected=" . ($is_selected ? 'YES' : 'NO'));
                                        }
                                        ?>
                                        <option value="<?php echo esc_attr($template_id); ?>"
                                                <?php selected($current_value, (string)$template_id); ?>>        
                                            <?php echo esc_html($template_title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Aperçu du template assigné -->
                            <div class="template-preview" data-original-value="<?php echo esc_attr($current_mappings[$status_key] ?? ''); ?>">
                                <?php if (!empty($current_mappings[$status_key]) && isset($templates[$current_mappings[$status_key]])): ?>      
                                    <p class="current-template">
                                        <strong>Assigné :</strong> <?php echo esc_html($templates[$current_mappings[$status_key]]); ?>
                                        <span class="assigned-badge assigned-badge-saved">✅</span>
                                    </p>
                                <?php else: ?>
                                    <p class="no-template">Aucun template assigné</p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Actions -->
                <section class="templates-status-actions">
                    <button type="button" class="button button-primary" id="save-templates-btn">
                        [SAVE] Sauvegarder les mappings
                    </button>
                    <button type="button" class="button button-secondary" onclick="PDFBuilderTabsAPI.resetTemplatesStatus()">
                        [RESET] Réinitialiser
                    </button>
                </section>
            </form>
        <?php endif; ?>
    </main>
</section>

<!-- Styles CSS -->


<style>
.templates-status-actions {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.templates-status-actions .button {
    margin-right: 10px;
    margin-bottom: 10px;
}

.templates-status-actions .button-success {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
    color: white !important;
}

.templates-status-actions .button-error {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
    color: white !important;
}

/* Styles pour l'affichage en temps réel */
.template-preview .current-template {
    margin: 8px 0;
    padding: 8px 12px;
    background: #e8f5e8;
    border: 1px solid #c3e6c3;
    border-radius: 4px;
    color: #2d5a2d;
}

.template-preview .no-template {
    margin: 8px 0;
    padding: 8px 12px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    color: #6c757d;
    font-style: italic;
}

.assigned-badge {
    float: right;
    font-weight: bold;
    font-size: 14px;
}

.assigned-badge-unsaved {
    color: #ffc107;
}

.assigned-badge-saved {
    color: #28a745;
}
</style>


<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->

<!-- JavaScript pour la sauvegarde des templates -->
<script>
(function() {
    'use strict';

    // Attendre que le DOM soit chargé
    document.addEventListener('DOMContentLoaded', function() {
        const saveBtn = document.getElementById('save-templates-btn');
        if (!saveBtn) return;

        // === AFFICHAGE EN TEMPS RÉEL DES ASSIGNATIONS ===
        initRealTimePreview();

        function initRealTimePreview() {
            // Écouter les changements sur tous les selects de template
            const templateSelects = document.querySelectorAll('.template-select');

            templateSelects.forEach(function(select) {
                select.addEventListener('change', function() {
                    updateTemplatePreview(this);
                });

                // Stocker la valeur originale pour la comparaison (utiliser la valeur du HTML)
                const previewDiv = select.closest('.template-status-card').querySelector('.template-preview');
                if (previewDiv) {
                    // Ne pas écraser la valeur originale définie dans le HTML
                    // previewDiv.dataset.originalValue = select.value;
                    // Initialiser l'affichage avec la valeur actuelle
                    updateTemplatePreview(select);
                }
            });
        }

        function updateTemplatePreview(select) {
            const statusKey = select.id.replace('template_', '');
            const selectedValue = select.value;
            const selectedText = select.options[select.selectedIndex].text;

            // Trouver la section preview correspondante
            const card = select.closest('.template-status-card');
            const previewDiv = card.querySelector('.template-preview');

            if (!previewDiv) return;

            // Vérifier si la valeur a changé par rapport à la valeur sauvegardée
            const originalValue = previewDiv.dataset.originalValue || '';
            const isChanged = selectedValue !== originalValue;

            if (selectedValue && selectedValue !== '') {
                // Template assigné
                const badgeClass = isChanged ? 'assigned-badge-unsaved' : 'assigned-badge-saved';
                const badgeIcon = isChanged ? '🔄' : '✅';

                previewDiv.innerHTML = `
                    <p class="current-template">
                        <strong>Assigné :</strong> ${selectedText}
                        <span class="assigned-badge ${badgeClass}">${badgeIcon}</span>
                    </p>
                `;
            } else {
                // Aucun template
                const badgeClass = isChanged ? 'assigned-badge-unsaved' : '';
                const badgeIcon = isChanged ? '🔄' : '';

                previewDiv.innerHTML = `
                    <p class="no-template">
                        Aucun template assigné
                        ${badgeIcon ? `<span class="assigned-badge ${badgeClass}">${badgeIcon}</span>` : ''}
                    </p>
                `;
            }
        }

        function updateOriginalValues() {
            // Mettre à jour les valeurs originales pour tous les selects après sauvegarde
            const templateSelects = document.querySelectorAll('.template-select');
            templateSelects.forEach(function(select) {
                const previewDiv = select.closest('.template-status-card').querySelector('.template-preview');
                if (previewDiv) {
                    previewDiv.dataset.originalValue = select.value;
                    // Remettre à jour l'aperçu pour changer le badge
                    updateTemplatePreview(select);
                }
            });
        }

        // Initialiser les aperçus temps réel
        initRealTimePreview();
    });
})();
</script>
