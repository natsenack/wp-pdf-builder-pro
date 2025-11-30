<?php
/**
 * Templates par statut tab content
 * Gestion des templates PDF par défaut selon le statut des commandes WooCommerce
 * Supporte les statuts personnalisés ajoutés par des plugins tiers
 * Updated: 2025-11-24 12:00:00
 */

/**
 * Détecte dynamiquement le plugin responsable d'un statut personnalisé WooCommerce
 *
 * @param string $status_key Le slug du statut (sans préfixe wc-)
 * @return string|null Le nom du plugin ou null si non détecté
 */
function detect_custom_status_plugin($status_key) {
    // 1. D'abord, vérifier les options WooCommerce pour les statuts personnalisés
    $custom_statuses = get_option('wc_order_statuses', []);
    if (!empty($custom_statuses) && isset($custom_statuses['wc-' . $status_key])) {
        $status_data = $custom_statuses['wc-' . $status_key];
        if (is_array($status_data) && isset($status_data['label'])) {
            // C'est un statut ajouté via l'interface WooCommerce native ou un plugin
            // Chercher dans les logs d'activation ou les transients si possible
            return detect_plugin_from_status_data($status_data, $status_key);
        }
    }

    // 2. Chercher dans les plugins actifs qui modifient les statuts WooCommerce
    $detected_plugin = detect_plugin_from_active_plugins($status_key);
    if ($detected_plugin) {
        return $detected_plugin;
    }

    // 3. Analyse des patterns connue (fallback)
    return detect_plugin_from_patterns($status_key);
}

/**
 * Détecte le plugin en analysant les données du statut personnalisé
 */
function detect_plugin_from_status_data($status_data, $status_key) {
    global $wpdb;

    // Chercher dans les options des plugins connus
    $plugin_indicators = [
        // WooCommerce Order Status Manager
        'wc_order_status_manager' => [
            'options' => ['wc_order_status_manager', 'wc_osm_'],
            'transient_prefix' => 'wc_osm_'
        ],
        // YITH Custom Order Status
        'yith_custom_order_status' => [
            'options' => ['yith_wccos', 'yith_custom_order_status'],
            'transient_prefix' => 'yith_wccos_'
        ],
        // Custom Order Status for WooCommerce
        'custom_order_status' => [
            'options' => ['custom_order_status', 'alg_wc_custom_order_status'],
            'transient_prefix' => 'alg_wc_cos_'
        ],
    ];

    foreach ($plugin_indicators as $plugin_key => $indicators) {
        // Vérifier les options
        foreach ($indicators['options'] as $option_pattern) {
            $option_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1",
                $option_pattern . '%'
            ));
            if ($option_exists) {
                return get_plugin_display_name($plugin_key);
            }
        }

        // Vérifier les transients
        if (!empty($indicators['transient_prefix'])) {
            $transient_exists = $wpdb->get_var($wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s LIMIT 1",
                '_transient_' . $indicators['transient_prefix'] . '%'
            ));
            if ($transient_exists) {
                return get_plugin_display_name($plugin_key);
            }
        }
    }

    // Si on trouve des données de statut mais pas de plugin spécifique,
    // continuer avec l'analyse des plugins actifs
    return null;
}

/**
 * Détecte le plugin en analysant les plugins actifs
 */
function detect_plugin_from_active_plugins($status_key) {
    global $wpdb;

    // Obtenir tous les plugins actifs
    $active_plugins = get_option('active_plugins', []);

    // Plugins à exclure de la détection (plugins de base qui ne comptent pas comme "modificateurs de statuts")
    $excluded_plugins = [
        'woocommerce/woocommerce.php', // WooCommerce lui-même
    ];

    // Filtrer les plugins exclus
    $active_plugins = array_diff($active_plugins, $excluded_plugins);

    // 1. D'abord, analyser les options de la base de données pour les plugins actifs
    foreach ($active_plugins as $plugin_file) {
        $plugin_slug = dirname($plugin_file); // Obtenir le slug du plugin

        // Chercher des options liées aux statuts WooCommerce pour ce plugin
        $plugin_options = $wpdb->get_results($wpdb->prepare(
            "SELECT option_name, option_value FROM {$wpdb->options}
             WHERE option_name LIKE %s
             AND (option_name LIKE %s OR option_name LIKE %s)
             LIMIT 5",
            $plugin_slug . '%', '%status%', '%order%'
        ));

        if (!empty($plugin_options)) {
            // Ce plugin a des options liées aux statuts/commandes
            $plugin_name = get_plugin_display_name_from_file($plugin_file);
            if ($plugin_name && $plugin_name !== 'Plugin inconnu') {
                return $plugin_name;
            }
        }
    }

    // 2. Chercher dans les transients pour les plugins qui modifient les statuts
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
            $plugin_name = get_plugin_display_name_from_file($plugin_file);
            if ($plugin_name && $plugin_name !== 'Plugin inconnu') {
                return $plugin_name;
            }
        }
    }

    // 3. Analyse des headers des plugins pour détecter les fonctionnalités
    foreach ($active_plugins as $plugin_file) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);

        // Chercher des mots-clés dans la description ou le nom
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

/**
 * Obtient le nom d'affichage d'un plugin depuis son fichier
 */
function get_plugin_display_name_from_file($plugin_file) {
    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);

    if (!empty($plugin_data['Name'])) {
        return $plugin_data['Name'];
    }

    // Fallback: utiliser le slug du plugin
    $plugin_slug = dirname($plugin_file);
    return ucwords(str_replace(['-', '_'], ' ', $plugin_slug));
}

/**
 * Détecte le plugin basé sur des patterns de statut connus (fallback)
 */
function detect_plugin_from_patterns($status_key) {
    // Patterns de statut organisés par catégorie de plugin
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

    // Recherche exacte
    if (isset($status_patterns[$status_key])) {
        return $status_patterns[$status_key];
    }

    // Recherche par pattern partiel
    foreach ($status_patterns as $pattern => $plugin_type) {
        if (strpos($status_key, $pattern) !== false || strpos($pattern, $status_key) !== false) {
            return $plugin_type;
        }
    }

    return null;
}

/**
 * Retourne le nom d'affichage d'un plugin
 *
 * @param string $plugin_key La clé du plugin
 * @return string Le nom d'affichage
 */
function get_plugin_display_name($plugin_key) {
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

// Vérifier si WooCommerce est actif
$woocommerce_active = class_exists('WooCommerce');

// Récupérer les statuts de commande WooCommerce (incluant les statuts personnalisés)
$order_statuses = [];
$custom_status_plugins = [];
if ($woocommerce_active) {
    // Méthode compatible avec toutes les versions de WooCommerce
    if (function_exists('wc_get_order_statuses')) {
        // WooCommerce 3.0+
        $order_statuses = wc_get_order_statuses();
    } elseif (class_exists('WC_Order') && method_exists('WC_Order', 'get_statuses')) {
        // Version alternative
        $order_statuses = WC_Order::get_statuses();
    } else {
        // Fallback: récupérer manuellement depuis les options
        $order_statuses = get_option('wc_order_statuses', []);
        if (empty($order_statuses)) {
            // Statuts par défaut si rien n'est trouvé
            $order_statuses = [
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

    // Détecter les statuts personnalisés et leurs plugins associés
    $default_statuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'draft', 'checkout-draft'];
    $status_plugins = []; // Association statut => plugin

    foreach ($order_statuses as $status_key => $status_name) {
        // Enlever le préfixe 'wc-' si présent
        $clean_status_key = str_replace('wc-', '', $status_key);

        if (!in_array($clean_status_key, $default_statuses)) {
            // C'est un statut personnalisé, essayer de détecter le plugin responsable
            $plugin_name = detect_custom_status_plugin($clean_status_key);

            if ($plugin_name) {
                $custom_status_plugins[] = $plugin_name;
                $status_plugins[$status_key] = $plugin_name; // Stocker l'association
            }
        }
    }

    // Éliminer les doublons
    $custom_status_plugins = array_unique($custom_status_plugins);
}

// Récupérer les templates disponibles depuis les posts WordPress
$templates_wp = [];
global $wpdb;
$templates_wp_query = $wpdb->get_results("
    SELECT ID, post_title
    FROM {$wpdb->posts}
    WHERE post_type = 'pdf_template'
    AND post_status = 'publish'
    ORDER BY post_title ASC
", ARRAY_A);

if ($templates_wp_query) {
    foreach ($templates_wp_query as $template) {
        $templates_wp[$template['ID']] = $template['post_title'];
    }
}

// Récupérer les templates disponibles depuis la table custom pdf_builder_templates
$templates_custom = [];
$table_templates = $wpdb->prefix . 'pdf_builder_templates';
$templates_custom_query = $wpdb->get_results("
    SELECT id, name
    FROM {$table_templates}
    ORDER BY name ASC
", ARRAY_A);

if ($templates_custom_query) {
    foreach ($templates_custom_query as $template) {
        $templates_custom['custom_' . $template['id']] = $template['name'];
    }
}

// Fusionner les deux sources de templates
$templates = array_merge($templates_wp, $templates_custom);

// Récupérer et nettoyer les mappings actuels
$current_mappings = get_option('pdf_builder_order_status_templates', []);
// Nettoyer les mappings pour les statuts qui n'existent plus (plugins tiers désactivés)
if (!empty($current_mappings) && !empty($order_statuses)) {
    $valid_statuses = array_keys($order_statuses);
    $current_mappings = array_intersect_key($current_mappings, array_flip($valid_statuses));
    // Sauvegarder les mappings nettoyés si nécessaire
    if (count($current_mappings) !== count(get_option('pdf_builder_order_status_templates', []))) {
        update_option('pdf_builder_order_status_templates', $current_mappings);
    }
}
?>
<div class="templates-status-wrapper">
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

            <?php if (!$woocommerce_active): ?>
            <div class="notice notice-warning">
                <p><strong>[WARNING] WooCommerce n'est pas actif</strong></p>
                <p>Cette fonctionnalité nécessite WooCommerce pour fonctionner. Veuillez installer et activer WooCommerce.</p>
            </div>
            <?php else: ?>


            <form method="post" action="" id="templates-status-form">
                <div class="templates-status-grid">
                    <?php
                    // Définir les statuts WooCommerce par défaut
                    $default_statuses = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'draft', 'checkout-draft'];
                    ?>
                    <?php foreach ($order_statuses as $status_key => $status_label): ?>
                    <?php
                    // Vérifier si c'est un statut personnalisé
                    $clean_status_key = str_replace('wc-', '', $status_key);
                    $is_custom_status = !in_array($clean_status_key, $default_statuses);
                    ?>
                    <div class="template-status-card <?php echo $is_custom_status ? 'custom-status-card' : ''; ?>">
                        <h4>
                            <?php echo esc_html($status_label); ?>
                            <?php if ($is_custom_status): ?>
                            <?php
                            $detected_plugin = isset($status_plugins[$status_key]) ? $status_plugins[$status_key] : 'Plugin inconnu';
                            $tooltip_text = "Slug personnalisé détecté - ajouté par: {$detected_plugin}";
                            ?>
                            <span class="custom-status-indicator" data-tooltip="<?php echo esc_attr($tooltip_text); ?>" style="font-family: Arial, sans-serif;">[SEARCH]</span>
                            <?php endif; ?>
                        </h4>
                        <div class="template-selector">
                            <label for="template_<?php echo esc_attr($status_key); ?>">
                                Template par défaut :
                            </label>
                            <select name="order_status_templates[<?php echo esc_attr($status_key); ?>]"
                                    id="template_<?php echo esc_attr($status_key); ?>"
                                    class="template-select">
                                <option value="">-- Aucun template --</option>
                                <?php foreach ($templates as $template_id => $template_title): ?>
                                <option value="<?php echo esc_attr($template_id); ?>"
                                        <?php selected($current_mappings[$status_key] ?? '', $template_id); ?>>
                                    <?php echo esc_html($template_title); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="template-preview">
                            <?php if (!empty($current_mappings[$status_key]) && isset($templates[$current_mappings[$status_key]])): ?>
                            <p class="current-template">
                                <strong>Assigné :</strong> <?php echo esc_html($templates[$current_mappings[$status_key]]); ?>
                                <span class="assigned-badge">✅</span>
                            </p>
                            <?php else: ?>
                            <p class="no-template">Aucun template assigné</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="templates-status-actions">
                    <button type="button" class="button button-secondary" onclick="resetTemplatesStatus()">
                        [RESET] Réinitialiser
                    </button>
                </div>
            </form>

            <?php endif; ?>

            <style>
                .templates-status-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 20px;
                    margin: 20px 0;
                }

                .template-status-card {
                    background: white;
                    border: 1px solid #ddd;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    transition: box-shadow 0.3s ease;
                }

                .template-status-card:hover {
                    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
                }

                .template-status-card h4 {
                    margin: 0 0 15px 0;
                    color: #23282d;
                    font-size: 16px;
                    font-weight: 600;
                    border-bottom: 2px solid #007cba;
                    padding-bottom: 8px;
                }

                .template-selector {
                    margin-bottom: 15px;
                }

                .template-selector label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: 5px;
                    color: #23282d;
                }

                .template-select {
                    width: 100%;
                    max-width: 100%;
                    padding: 8px 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                    background: white;
                }

                .template-preview {
                    padding: 10px;
                    background: #f8f9fa;
                    border-radius: 4px;
                    min-height: 40px;
                }

                .current-template {
                    color: #155724;
                    font-size: 13px;
                    margin: 0;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }

                .assigned-badge {
                    background: #28a745;
                    color: white;
                    padding: 2px 6px;
                    border-radius: 12px;
                    font-size: 11px;
                    font-weight: bold;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 18px;
                    height: 18px;
                }

                .no-template {
                    color: #6c757d;
                    font-style: italic;
                    font-size: 13px;
                    margin: 0;
                }

                .templates-status-actions {
                    margin-top: 30px;
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 8px;
                    display: flex;
                    gap: 10px;
                    justify-content: center;
                }

                .templates-status-notice {
                    margin-bottom: 20px;
                }

                /* Styles pour les statuts personnalisés */
                .custom-status-card {
                    border-color: #007cba;
                    background: linear-gradient(135deg, #f0f8ff 0%, #ffffff 100%);
                }

                .custom-status-card h4 {
                    border-bottom-color: #007cba;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }

                .custom-status-indicator {
                    font-size: 14px;
                    background: #007cba;
                    color: white;
                    padding: 2px 6px;
                    border-radius: 10px;
                    font-weight: bold;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 20px;
                    height: 20px;
                    cursor: help;
                    transition: transform 0.2s ease;
                }

                .custom-status-indicator:hover {
                    transform: scale(1.05);
                }

                /* Tooltip personnalisé */
                .custom-status-indicator {
                    position: relative;
                }

                .custom-status-indicator:hover::after {
                    content: attr(data-tooltip);
                    position: absolute;
                    bottom: 125%;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #333;
                    color: white;
                    padding: 8px 12px;
                    border-radius: 4px;
                    font-size: 12px;
                    white-space: nowrap;
                    z-index: 1000;
                    opacity: 1;
                    visibility: visible;
                    pointer-events: none;
                }

                .custom-status-indicator:hover::before {
                    content: '';
                    position: absolute;
                    bottom: 110%;
                    left: 50%;
                    transform: translateX(-50%);
                    border: 5px solid transparent;
                    border-top-color: #333;
                    z-index: 1000;
                    pointer-events: none;
                }
            </style>

</div>

            <script>
            function resetTemplatesStatus() {
                if (confirm('Êtes-vous sûr de vouloir réinitialiser toutes les assignations de templates ?')) {
                    document.querySelectorAll('.template-select').forEach(select => {
                        select.value = '';
                    });
                }
            }

            // Auto-save functionality (optional enhancement)
            document.addEventListener('DOMContentLoaded', function() {
                const selects = document.querySelectorAll('.template-select');
                selects.forEach(select => {
                    select.addEventListener('change', function() {
                        // Optional: Add visual feedback
                        this.style.borderColor = '#007cba';
                        setTimeout(() => {
                            this.style.borderColor = '#ddd';
                        }, 1000);
                    });
                });
            });
            </script>