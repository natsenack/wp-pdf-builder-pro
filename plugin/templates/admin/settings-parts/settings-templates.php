<?php
/**
 * Templates par statut tab content
 * Gestion des templates PDF par défaut selon le statut des commandes WooCommerce
 * Supporte les statuts personnalisés ajoutés par des plugins tiers
 * Updated: 2025-11-24 12:00:00
 */

// Vérifier si WooCommerce est actif
$woocommerce_active = class_exists('WooCommerce');

// Récupérer les statuts de commande WooCommerce (incluant les statuts personnalisés)
$order_statuses = [];
if ($woocommerce_active && function_exists('wc_get_order_statuses')) {
    $order_statuses = wc_get_order_statuses();
    // Note: wc_get_order_statuses() inclut automatiquement les statuts personnalisés
    // ajoutés par des plugins tiers via les hooks WooCommerce
}

// Récupérer les templates disponibles
$templates = [];
global $wpdb;
$templates_query = $wpdb->get_results("
    SELECT ID, post_title
    FROM {$wpdb->posts}
    WHERE post_type = 'pdf_template'
    AND post_status = 'publish'
    ORDER BY post_title ASC
", ARRAY_A);

if ($templates_query) {
    foreach ($templates_query as $template) {
        $templates[$template['ID']] = $template['post_title'];
    }
}

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
            <h2>📋 Templates par Statut de Commande</h2>

            <?php if (!$woocommerce_active): ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ WooCommerce n'est pas actif</strong></p>
                <p>Cette fonctionnalité nécessite WooCommerce pour fonctionner. Veuillez installer et activer WooCommerce.</p>
                <details style="margin-top: 10px;">
                    <summary style="cursor: pointer; font-weight: bold;">Informations de diagnostic</summary>
                    <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px;">
                        <p><strong>Classe WooCommerce :</strong> <?php echo class_exists('WooCommerce') ? 'Disponible' : 'Non disponible'; ?></p>
                        <p><strong>Fonction wc_get_order_statuses :</strong> <?php echo function_exists('wc_get_order_statuses') ? 'Disponible' : 'Non disponible'; ?></p>
                        <p><strong>Plugins actifs :</strong> <?php
                        if (function_exists('get_option')) {
                            $active_plugins = get_option('active_plugins', []);
                            $woocommerce_plugins = array_filter($active_plugins, function($plugin) {
                                return strpos($plugin, 'woocommerce') !== false;
                            });
                            echo empty($woocommerce_plugins) ? 'Aucun plugin WooCommerce détecté' : implode(', ', $woocommerce_plugins);
                        } else {
                            echo 'Impossible de récupérer la liste des plugins';
                        }
                        ?></p>
                    </div>
                </details>
            </div>
            <?php else: ?>

            <?php if (empty($templates)): ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ Aucun template PDF disponible</strong></p>
                <p>Vous devez créer au moins un template PDF avant de pouvoir configurer les assignations par statut.</p>
                <p><a href="<?php echo admin_url('edit.php?post_type=pdf_template'); ?>" class="button button-primary">Créer un template PDF</a></p>
            </div>
            <?php else: ?>

            <div class="templates-status-notice">
                <div class="notice notice-info">
                    <p><strong>ℹ️ Configuration des templates par statut</strong></p>
                    <p>Assignez un template PDF par défaut pour chaque statut de commande WooCommerce. Lorsque le statut d'une commande change, le template correspondant sera automatiquement utilisé pour générer le PDF.</p>
                    <p><strong><?php echo count($order_statuses); ?> statuts détectés :</strong> <?php echo implode(', ', array_values($order_statuses)); ?></p>
                    <p><strong><?php echo count($templates); ?> templates disponibles :</strong> <?php echo implode(', ', array_values($templates)); ?></p>
                </div>
            </div>

            <form method="post" action="" id="templates-status-form">
                <?php wp_nonce_field('pdf_builder_templates_status', 'pdf_builder_templates_status_nonce'); ?>
                <input type="hidden" name="current_tab" value="templates">

                <div class="templates-status-grid">
                    <?php foreach ($order_statuses as $status_key => $status_label): ?>
                    <div class="template-status-card">
                        <h4><?php echo esc_html($status_label); ?></h4>
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
                                <strong>Actuellement :</strong> <?php echo esc_html($templates[$current_mappings[$status_key]]); ?>
                            </p>
                            <?php else: ?>
                            <p class="no-template">Aucun template assigné</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="templates-status-actions">
                    <button type="submit" name="save_templates_status" class="button button-primary">
                        💾 Sauvegarder les assignations
                    </button>
                    <button type="button" class="button button-secondary" onclick="resetTemplatesStatus()">
                        🔄 Réinitialiser
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
            </style>

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
            <?php endif; ?>
            <?php endif; ?>