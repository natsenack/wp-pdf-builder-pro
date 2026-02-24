<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.SchemaChange
    /**
     * Templates par statut tab content
     * Gestion des templates PDF par défaut selon le statut des commandes WooCommerce
     * Supporte les statuts personnalisés ajoutés par des plugins tiers
     * Updated: 2025-12-02 - Code réorganisé pour une meilleure lisibilité
     */

if (!defined('ABSPATH')) {
    exit;
}

    // Vérifier si l'utilisateur a une licence premium
    $is_premium = false;
    if (class_exists('PDF_Builder\Managers\PDF_Builder_License_Manager')) {
        $license_manager = PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
        $is_premium = $license_manager->isPremium();
    }

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

        // Initialisation des statuts WooCommerce - différée
        private function init_woocommerce_status() {
            // Différer la vérification pour éviter les problèmes de chargement prématuré
            if (did_action('plugins_loaded')) {
                $this->woocommerce_active = defined('WC_VERSION');
            } else {
                $this->woocommerce_active = false;
            }

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
            } else {
                $statuses = get_option('wc_order_statuses', array());
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
            $templates_wp = $wpdb->get_results(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
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
            $templates_custom = $wpdb->get_results(" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
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
            // Charger depuis l'option dédiée (sauvegardée par save_templates_settings)
            $raw_option = pdf_builder_get_option('pdf_builder_order_status_templates', array());
            
            $this->current_mappings = $raw_option;

            // S'assurer que c'est un tableau
            if (!is_array($this->current_mappings)) {
                $this->current_mappings = [];
            }

            // Nettoyer les mappings obsolètes (statuts supprimés)
            if (!empty($this->current_mappings) && !empty($this->order_statuses)) {
                $valid_statuses = array_keys($this->order_statuses);
                $cleaned_mappings = array_intersect_key($this->current_mappings, array_flip($valid_statuses));

                // Sauvegarder si nettoyage effectué
                if (count($cleaned_mappings) !== count($this->current_mappings)) {
                    pdf_builder_update_option('pdf_builder_order_status_templates', $cleaned_mappings);
                    $this->current_mappings = $cleaned_mappings;
                }
            }
        }

        // Détection du plugin pour un statut personnalisé
        private function detect_custom_status_plugin($status_key) {
            // 1. Vérifier les options WooCommerce
            $custom_statuses = get_option('wc_order_statuses', array());
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
                    $option_exists = $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
                        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1",
                        $option_pattern . '%'
                    ));
                    if ($option_exists) {
                        return $this->get_plugin_display_name($plugin_key);
                    }
                }

                if (!empty($indicators['transient_prefix'])) {
                    $transient_exists = $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
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

            $active_plugins = get_option('active_plugins', array());
            $excluded_plugins = ['woocommerce/woocommerce.php'];
            $active_plugins = array_diff($active_plugins, $excluded_plugins);

            // Analyse des options de base de données
            foreach ($active_plugins as $plugin_file) {
                $plugin_slug = dirname($plugin_file);

                $plugin_options = $wpdb->get_results($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
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

                $transient_exists = $wpdb->get_var($wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
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

    // =============================================================================
    // AFFICHAGE HTML
    // =============================================================================
?>
<section class="pdfb-templates-status-wrapper">
    <!-- En-tête -->
    <header>
        <h3 style="display: flex; justify-content: flex-start; align-items: center;">
            <span>📋 Templates par Statut de Commande</span>
            <?php if (!empty($custom_status_plugins)): ?>
                <span style="font-size: 14px; font-weight: normal; color: #666; margin-left: auto;">
                    🔌 Plugins détectés: <?php echo esc_html(implode(', ', $custom_status_plugins)); ?>
                </span>
            <?php elseif ($woocommerce_active && !empty($order_statuses)): ?>
                <span style="font-size: 14px; font-weight: normal; color: #28a745; margin-left: auto;">
                    ✅ Statuts WooCommerce standards uniquement
                </span>
            <?php endif; ?>
        </h3>

        <?php if (!$is_premium): ?>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 12px; margin: 15px 0; font-size: 14px;">
                <strong>🔒 Version Gratuite :</strong> Vous pouvez uniquement assigner des templates au statut "Terminée".
                Les statuts personnalisés restent disponibles. <a href="#" onclick="if(window.PDFBuilderTabsAPI && PDFBuilderTabsAPI.switchToTab) { PDFBuilderTabsAPI.switchToTab('licence'); return false; } else if(window.switchTab) { switchTab('licence'); return false; } else { window.location.href='<?php echo esc_url(admin_url('admin.php?page=pdf-builder-settings#licence')); ?>'; return false; }" style="color: #856404;">Passer à la version Premium</a> pour débloquer toutes les fonctionnalités.
            </div>
        <?php endif; ?>
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
            <!-- Configuration des templates par statut -->
                <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>

                <!-- Grille des statuts -->
                <div class="pdfb-templates-status-grid">
                    <?php
                    // Trier les statuts pour les utilisateurs free : disponibles en premier
                    $sorted_statuses = $order_statuses;
                    if (!$is_premium) {
                        $available_first = [];
                        $available_later = [];

                        foreach ($order_statuses as $status_key => $status_label) {
                            $is_custom_status = $status_manager->is_custom_status($status_key);
                            $is_default_completed = ($status_key === 'wc-completed');

                            // Priorité : statuts personnalisés, puis "Terminée", puis autres
                            if ($is_custom_status || $is_default_completed) {
                                $available_first[$status_key] = $status_label;
                            } else {
                                $available_later[$status_key] = $status_label;
                            }
                        }

                        $sorted_statuses = array_merge($available_first, $available_later);
                    }
                    ?>

                    <?php foreach ($sorted_statuses as $status_key => $status_label):
                        $is_custom_status = $status_manager->is_custom_status($status_key);
                        $is_premium_required = false;
                        if (!$is_premium) {
                            $is_default_completed = ($status_key === 'wc-completed');
                            $is_premium_required = !$is_custom_status && !$is_default_completed;
                        }
                    ?>
                        <article class="pdfb-template-status-card <?php echo esc_attr($is_custom_status ? 'custom-status-card' : ''); ?> <?php echo esc_attr($is_premium_required ? 'premium-card' : ''); ?>">
                            <header>
                                <h4>
                                    <?php echo esc_html($status_label); ?>
                                    <?php if ($is_custom_status): ?>
                                        <?php
                                        $detected_plugin = isset($status_plugins[$status_key]) ? $status_plugins[$status_key] : 'Plugin inconnu';
                                        $tooltip_text = "Slug personnalisé détecté - ajouté par: {$detected_plugin}";
                                        ?>
                                        <span class="pdfb-custom-status-indicator"
                                              data-tooltip="<?php echo esc_attr($tooltip_text); ?>"
                                              style="font-family: Arial, sans-serif;">🔍</span>
                                    <?php endif; ?>
                                    <?php if ($is_premium_required): ?>
                                        <span class="pdfb-premium-badge">⭐ PREMIUM</span>
                                    <?php endif; ?>
                                </h4>
                            </header>

                            <!-- Sélecteur de template -->
                            <div class="pdfb-template-selector">
                                <label for="template_<?php echo esc_attr($status_key); ?>">
                                    📄 Template par défaut :
                                </label>
                                <select name="pdf_builder_settings[pdf_builder_order_status_templates][<?php echo esc_attr($status_key); ?>]"
                                        id="template_<?php echo esc_attr($status_key); ?>"
                                        class="pdfb-template-select"
                                        <?php
                                        if (!$is_premium) {
                                            $is_custom_status = $status_manager->is_custom_status($status_key);
                                            $is_default_completed = ($status_key === 'wc-completed');

                                            // En mode free, désactiver le select pour les statuts par défaut sauf "Terminée"
                                            if (!$is_custom_status && !$is_default_completed) {
                                                echo 'disabled title="Fonctionnalité réservée aux utilisateurs premium"';
                                            }
                                        }
                                        ?>>
                                    <option value="">-- Aucun template --</option>
                                    <?php foreach ($templates as $template_id => $template_title):
                                        $current_value = isset($current_mappings[$status_key]) ? trim((string)$current_mappings[$status_key]) : '';
                                        $is_selected = $current_value === (string)$template_id;

                                        // Logique de restriction pour les utilisateurs free
                                        $is_disabled = false;
                                        $disabled_reason = '';

                                        if (!$is_premium) {
                                            $is_custom_status = $status_manager->is_custom_status($status_key);
                                            $is_default_completed = ($status_key === 'wc-completed');

                                            // En mode free :
                                            // - Les statuts personnalisés sont autorisés (pas de restriction)
                                            // - Pour les statuts par défaut, seul "Terminée" peut avoir un template
                                            if (!$is_custom_status && !$is_default_completed) {
                                                $is_disabled = true;
                                                $disabled_reason = 'Fonctionnalité réservée aux utilisateurs premium';
                                            }
                                        }

                                        ?>
                                        <option value="<?php echo esc_attr($template_id); ?>"
                                                <?php selected($current_value, (string)$template_id); ?>
                                                <?php if ($is_disabled): ?>disabled<?php endif; ?>
                                                title="<?php echo $is_disabled ? esc_attr($disabled_reason) : ''; ?>">
                                            <?php echo esc_html($template_title); ?>
                                            <?php if ($is_disabled): ?> 🔒<?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Aperçu du template assigné -->
                            <div class="pdfb-template-preview">
                                <?php if (!empty($current_mappings[$status_key]) && isset($templates[$current_mappings[$status_key]])): ?>
                                    <p class="pdfb-current-template">Assigné : <?php echo esc_html($templates[$current_mappings[$status_key]]); ?></p>
                                <?php else: ?>
                                    <p class="pdfb-no-template">Aucun template assigné</p>
                                <?php endif; ?>
                            </div>

                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Actions -->
                <section class="pdfb-templates-status-actions">
                    <button type="button" class="button button-secondary" onclick="PDFBuilderTabsAPI.resetTemplatesStatus()">
                        🔄 Réinitialiser les paramètres
                    </button>
                    <p class="description" style="margin-top: 10px; color: #666;">
                        💡 Utilisez le bouton "Enregistrer" flottant en bas de page pour sauvegarder vos modifications.
                    </p>
                </section>
        <?php endif; ?>
    </main>
</section>

<!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->

<!-- JavaScript pour la gestion des templates -->
<script>
    (function() {
        'use strict';

        // Attendre que le DOM soit chargé
        document.addEventListener('DOMContentLoaded', function() {
            // Écouter l'événement de sauvegarde globale
            document.addEventListener('pdfBuilderSettingsSaved', function(event) {
                
                // Mettre à jour les prévisualisations après sauvegarde globale
                updatePreviewsAfterSave();
            });

            // Initialiser les prévisualisations au chargement
            updatePreviewsAfterSave();

            // Fonction pour mettre à jour les prévisualisations
            function updatePreviewsAfterSave() {
                // Récupérer les mappings actuels depuis les selects
                var selects = document.querySelectorAll('.template-select');
                var mappings = {};
                var templates = {};

                // Collecter les données depuis les selects actuels
                selects.forEach(function(select) {
                    var statusKey = select.name.replace('pdf_builder_settings[pdf_builder_order_status_templates][', '').replace(']', '');
                    var templateId = select.value;
                    var templateTitle = select.options[select.selectedIndex]?.text || 'Aucun template';

                    mappings[statusKey] = templateId;
                    if (templateId) {
                        templates[templateId] = templateTitle.replace('-- ', '').replace(' --', '');
                    }
                });

                // Mettre à jour les prévisualisations
                updatePreviewsWithData(mappings, templates);
            }

            // Fonction pour mettre à jour les prévisualisations avec les données
            function updatePreviewsWithData(mappings, templates) {
                var previews = document.querySelectorAll('.template-preview');
                for (var i = 0; i < previews.length; i++) {
                    var preview = previews[i];
                    var select = preview.closest('article').querySelector('.template-select');
                    if (select) {
                        var statusKey = select.name.replace('pdf_builder_settings[pdf_builder_order_status_templates][', '').replace(']', '');
                        var assignedTemplateId = mappings[statusKey];

                        if (assignedTemplateId && templates[assignedTemplateId]) {
                            preview.innerHTML = '<p class="current-template">' + templates[assignedTemplateId] + '</p>';
                        } else {
                            preview.innerHTML = '<p class="pdfb-no-template">Aucun template</p>';
                        }
                    }
                }
            }

            // Écouter les changements sur les selects pour mettre à jour les prévisualisations en temps réel
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('template-select')) {
                    updatePreviewsAfterSave();
                }
            });
        });
    })();
</script>






