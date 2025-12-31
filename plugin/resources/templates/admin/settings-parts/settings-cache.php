<?php
/**
 * PDF Builder Pro - Section Cache des paramètres
 * Interface de configuration centralisée du système de cache
 */

// Récupération des paramètres depuis le tableau unifié
$settings = get_option('pdf_builder_settings', []);

// === PARAMÈTRES GÉNÉRAUX DU CACHE ===
$cache_enabled = $settings['pdf_builder_cache_enabled'] ?? '1';
$cache_debug = $settings['pdf_builder_cache_debug'] ?? '0';
$cache_stats = $settings['pdf_builder_cache_stats'] ?? '1';

// === PARAMÈTRES TRANSIENTS ===
$transient_enabled = $settings['pdf_builder_cache_transient_enabled'] ?? '1';
$transient_prefix = $settings['pdf_builder_cache_transient_prefix'] ?? 'pdf_builder_cache_';

// === PARAMÈTRES ASSETS ===
$asset_cache_enabled = $settings['pdf_builder_asset_cache_enabled'] ?? '1';
$asset_compression = $settings['pdf_builder_asset_compression'] ?? '1';
$asset_minify = $settings['pdf_builder_asset_minify'] ?? '1';

// === PARAMÈTRES AJAX ===
$ajax_cache_enabled = $settings['pdf_builder_ajax_cache_enabled'] ?? '1';
$ajax_cache_ttl = intval($settings['pdf_builder_ajax_cache_ttl'] ?? 300);

// === PARAMÈTRES IMAGES ===
$image_cache_enabled = $settings['pdf_builder_image_cache_enabled'] ?? '1';
$image_max_memory = intval($settings['pdf_builder_image_max_memory'] ?? 256);

// === PARAMÈTRES APERÇUS ===
$preview_cache_enabled = $settings['pdf_builder_preview_cache_enabled'] ?? '1';
$preview_cache_max_items = intval($settings['pdf_builder_preview_cache_max_items'] ?? 50);

// === PARAMÈTRES NETTOYAGE ===
$cache_compression = $settings['pdf_builder_cache_compression'] ?? '1';
$cache_auto_cleanup = $settings['pdf_builder_cache_auto_cleanup'] ?? '1';
$cache_max_size = intval($settings['pdf_builder_cache_max_size'] ?? 100);
$cache_ttl = intval($settings['pdf_builder_cache_ttl'] ?? 3600);
$cleanup_interval = intval($settings['pdf_builder_cache_cleanup_interval'] ?? 86400);

// === STATISTIQUES EN TEMPS RÉEL ===
$cache_stats_data = [
    'hits' => 0,
    'misses' => 0,
    'memory_usage' => 0,
    'cache_size' => 0
];

// Récupérer les vraies statistiques si le Cache Manager est disponible
if (class_exists('PDF_Builder\Managers\PDF_Builder_Cache_Manager')) {
    try {
        $cache_manager = PDF_Builder\Managers\PDF_Builder_Cache_Manager::getInstance();
        $stats = $cache_manager->getStats();
        $cache_stats_data = [
            'hits' => $stats['hits'] ?? 0,
            'misses' => $stats['misses'] ?? 0,
            'memory_usage' => $stats['memory_usage'] ?? 0,
            'cache_size' => $stats['cache_size'] ?? 0
        ];
    } catch (Exception $e) {
        error_log('[PDF Builder Cache Settings] Erreur récupération stats: ' . $e->getMessage());
    }
}

?>

<div class="cache-settings-section">
    <div class="cache-header">
        <h3><?php _e('🗄️ Système de Cache Centralisé', 'pdf-builder-pro'); ?></h3>
        <p class="description">
            <?php _e('Configuration unifiée de tous les systèmes de cache du plugin PDF Builder Pro.', 'pdf-builder-pro'); ?>
        </p>
    </div>

    <!-- STATISTIQUES EN TEMPS RÉEL -->
    <div class="cache-stats-card">
        <h4><?php _e('📊 Statistiques Cache', 'pdf-builder-pro'); ?></h4>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label"><?php _e('Hits', 'pdf-builder-pro'); ?>:</span>
                <span class="stat-value" id="cache-hits"><?php echo number_format($cache_stats_data['hits']); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php _e('Misses', 'pdf-builder-pro'); ?>:</span>
                <span class="stat-value" id="cache-misses"><?php echo number_format($cache_stats_data['misses']); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php _e('Taux de succès', 'pdf-builder-pro'); ?>:</span>
                <span class="stat-value" id="cache-hit-rate">
                    <?php
                    $total = $cache_stats_data['hits'] + $cache_stats_data['misses'];
                    $rate = $total > 0 ? round(($cache_stats_data['hits'] / $total) * 100, 1) : 0;
                    echo $rate . '%';
                    ?>
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php _e('Utilisation mémoire', 'pdf-builder-pro'); ?>:</span>
                <span class="stat-value" id="cache-memory">
                    <?php echo size_format($cache_stats_data['memory_usage'], 2); ?>
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label"><?php _e('Éléments en cache', 'pdf-builder-pro'); ?>:</span>
                <span class="stat-value" id="cache-items"><?php echo number_format($cache_stats_data['cache_size']); ?></span>
            </div>
        </div>
        <div class="cache-actions">
            <button type="button" id="refresh-cache-stats" class="button">
                <?php _e('🔄 Actualiser', 'pdf-builder-pro'); ?>
            </button>
            <button type="button" id="clear-cache-all" class="button button-secondary">
                <?php _e('🗑️ Vider tout le cache', 'pdf-builder-pro'); ?>
            </button>
        </div>
    </div>

    <!-- CONFIGURATION GÉNÉRALE -->
    <div class="cache-config-section">
        <h4><?php _e('⚙️ Configuration Générale', 'pdf-builder-pro'); ?></h4>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Cache activé', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_cache_enabled">
                        <input type="checkbox"
                               id="pdf_builder_cache_enabled"
                               name="pdf_builder_cache_enabled"
                               value="1"
                               <?php checked($cache_enabled, '1'); ?> />
                        <?php _e('Activer le système de cache centralisé', 'pdf-builder-pro'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Désactiver pour utiliser uniquement les fonctions natives de WordPress.', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Durée de vie par défaut', 'pdf-builder-pro'); ?></th>
                <td>
                    <input type="number"
                           id="pdf_builder_cache_ttl"
                           name="pdf_builder_cache_ttl"
                           value="<?php echo esc_attr($cache_ttl); ?>"
                           min="60"
                           max="2592000"
                           step="60" />
                    <span><?php _e('secondes', 'pdf-builder-pro'); ?></span>
                    <p class="description">
                        <?php _e('Durée de vie par défaut des éléments en cache (60 sec - 30 jours).', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Taille maximale du cache', 'pdf-builder-pro'); ?></th>
                <td>
                    <input type="number"
                           id="pdf_builder_cache_max_size"
                           name="pdf_builder_cache_max_size"
                           value="<?php echo esc_attr($cache_max_size); ?>"
                           min="10"
                           max="1024"
                           step="10" />
                    <span><?php _e('MB', 'pdf-builder-pro'); ?></span>
                    <p class="description">
                        <?php _e('Taille maximale du cache fichier (10MB - 1GB).', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Compression activée', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_cache_compression">
                        <input type="checkbox"
                               id="pdf_builder_cache_compression"
                               name="pdf_builder_cache_compression"
                               value="1"
                               <?php checked($cache_compression, '1'); ?> />
                        <?php _e('Compresser automatiquement les données en cache', 'pdf-builder-pro'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Nettoyage automatique', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_cache_auto_cleanup">
                        <input type="checkbox"
                               id="pdf_builder_cache_auto_cleanup"
                               name="pdf_builder_cache_auto_cleanup"
                               value="1"
                               <?php checked($cache_auto_cleanup, '1'); ?> />
                        <?php _e('Nettoyer automatiquement les éléments expirés', 'pdf-builder-pro'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Exécute un nettoyage quotidien des éléments de cache expirés.', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Intervalle de nettoyage', 'pdf-builder-pro'); ?></th>
                <td>
                    <select id="pdf_builder_cache_cleanup_interval" name="pdf_builder_cache_cleanup_interval">
                        <option value="3600" <?php selected($cleanup_interval, 3600); ?>>
                            <?php _e('Toutes les heures', 'pdf-builder-pro'); ?>
                        </option>
                        <option value="86400" <?php selected($cleanup_interval, 86400); ?>>
                            <?php _e('Tous les jours', 'pdf-builder-pro'); ?>
                        </option>
                        <option value="604800" <?php selected($cleanup_interval, 604800); ?>>
                            <?php _e('Toutes les semaines', 'pdf-builder-pro'); ?>
                        </option>
                    </select>
                    <p class="description">
                        <?php _e('Fréquence du nettoyage automatique du cache.', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- CACHE TRANSIENTS -->
    <div class="cache-type-section">
        <h4><?php _e('🗃️ Cache Transients (Base de données)', 'pdf-builder-pro'); ?></h4>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Cache transients activé', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_cache_transient_enabled">
                        <input type="checkbox"
                               id="pdf_builder_cache_transient_enabled"
                               name="pdf_builder_cache_transient_enabled"
                               value="1"
                               <?php checked($transient_enabled, '1'); ?> />
                        <?php _e('Utiliser les transients WordPress pour le cache', 'pdf-builder-pro'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Préfixe des transients', 'pdf-builder-pro'); ?></th>
                <td>
                    <input type="text"
                           id="pdf_builder_cache_transient_prefix"
                           name="pdf_builder_cache_transient_prefix"
                           value="<?php echo esc_attr($transient_prefix); ?>"
                           pattern="[a-zA-Z0-9_]+"
                           maxlength="50" />
                    <p class="description">
                        <?php _e('Préfixe pour identifier les transients du plugin.', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- CACHE ASSETS -->
    <div class="cache-type-section">
        <h4><?php _e('📦 Cache d\'Assets (JS/CSS/Images)', 'pdf-builder-pro'); ?></h4>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Cache d\'assets activé', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_asset_cache_enabled">
                        <input type="checkbox"
                               id="pdf_builder_asset_cache_enabled"
                               name="pdf_builder_asset_cache_enabled"
                               value="1"
                               <?php checked($asset_cache_enabled, '1'); ?> />
                        <?php _e('Mettre en cache les fichiers JS, CSS et images optimisés', 'pdf-builder-pro'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Compression des assets', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_asset_compression">
                        <input type="checkbox"
                               id="pdf_builder_asset_compression"
                               name="pdf_builder_asset_compression"
                               value="1"
                               <?php checked($asset_compression, '1'); ?> />
                        <?php _e('Compresser automatiquement les assets (GZIP)', 'pdf-builder-pro'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Minification des assets', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_asset_minify">
                        <input type="checkbox"
                               id="pdf_builder_asset_minify"
                               name="pdf_builder_asset_minify"
                               value="1"
                               <?php checked($asset_minify, '1'); ?> />
                        <?php _e('Minifier automatiquement le code JS/CSS', 'pdf-builder-pro'); ?>
                    </label>
                </td>
            </tr>
        </table>
    </div>

    <!-- CACHE AJAX -->
    <div class="cache-type-section">
        <h4><?php _e('🌐 Cache AJAX', 'pdf-builder-pro'); ?></h4>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Cache AJAX activé', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_ajax_cache_enabled">
                        <input type="checkbox"
                               id="pdf_builder_ajax_cache_enabled"
                               name="pdf_builder_ajax_cache_enabled"
                               value="1"
                               <?php checked($ajax_cache_enabled, '1'); ?> />
                        <?php _e('Mettre en cache les réponses AJAX pour éviter les appels répétés', 'pdf-builder-pro'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('TTL cache AJAX', 'pdf-builder-pro'); ?></th>
                <td>
                    <input type="number"
                           id="pdf_builder_ajax_cache_ttl"
                           name="pdf_builder_ajax_cache_ttl"
                           value="<?php echo esc_attr($ajax_cache_ttl); ?>"
                           min="60"
                           max="3600"
                           step="60" />
                    <span><?php _e('secondes', 'pdf-builder-pro'); ?></span>
                    <p class="description">
                        <?php _e('Durée de vie du cache AJAX (1min - 1h).', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- CACHE IMAGES -->
    <div class="cache-type-section">
        <h4><?php _e('🖼️ Cache d\'Images (Canvas)', 'pdf-builder-pro'); ?></h4>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Cache d\'images activé', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_image_cache_enabled">
                        <input type="checkbox"
                               id="pdf_builder_image_cache_enabled"
                               name="pdf_builder_image_cache_enabled"
                               value="1"
                               <?php checked($image_cache_enabled, '1'); ?> />
                        <?php _e('Mettre en cache les images chargées dans le canvas', 'pdf-builder-pro'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Mémoire maximale images', 'pdf-builder-pro'); ?></th>
                <td>
                    <input type="number"
                           id="pdf_builder_image_max_memory"
                           name="pdf_builder_image_max_memory"
                           value="<?php echo esc_attr($image_max_memory); ?>"
                           min="32"
                           max="1024"
                           step="32" />
                    <span><?php _e('MB', 'pdf-builder-pro'); ?></span>
                    <p class="description">
                        <?php _e('Mémoire maximale pour le cache d\'images côté client (32MB - 1GB).', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- CACHE APERÇUS -->
    <div class="cache-type-section">
        <h4><?php _e('👁️ Cache des Aperçus', 'pdf-builder-pro'); ?></h4>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Cache d\'aperçus activé', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_preview_cache_enabled">
                        <input type="checkbox"
                               id="pdf_builder_preview_cache_enabled"
                               name="pdf_builder_preview_cache_enabled"
                               value="1"
                               <?php checked($preview_cache_enabled, '1'); ?> />
                        <?php _e('Mettre en cache les miniatures et aperçus générés', 'pdf-builder-pro'); ?>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Nombre maximum d\'aperçus', 'pdf-builder-pro'); ?></th>
                <td>
                    <input type="number"
                           id="pdf_builder_preview_cache_max_items"
                           name="pdf_builder_preview_cache_max_items"
                           value="<?php echo esc_attr($preview_cache_max_items); ?>"
                           min="10"
                           max="200"
                           step="10" />
                    <p class="description">
                        <?php _e('Nombre maximum d\'aperçus gardés en cache (10-200).', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- DÉBOGAGE ET STATISTIQUES -->
    <div class="cache-debug-section">
        <h4><?php _e('🔍 Débogage et Statistiques', 'pdf-builder-pro'); ?></h4>

        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Mode debug activé', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_cache_debug">
                        <input type="checkbox"
                               id="pdf_builder_cache_debug"
                               name="pdf_builder_cache_debug"
                               value="1"
                               <?php checked($cache_debug, '1'); ?> />
                        <?php _e('Activer les logs détaillés du système de cache', 'pdf-builder-pro'); ?>
                    </label>
                    <p class="description">
                        <?php _e('Utile pour diagnostiquer les problèmes de cache.', 'pdf-builder-pro'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Statistiques activées', 'pdf-builder-pro'); ?></th>
                <td>
                    <label for="pdf_builder_cache_stats">
                        <input type="checkbox"
                               id="pdf_builder_cache_stats"
                               name="pdf_builder_cache_stats"
                               value="1"
                               <?php checked($cache_stats, '1'); ?> />
                        <?php _e('Collecter des statistiques d\'utilisation du cache', 'pdf-builder-pro'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <div class="cache-management-actions">
            <h5><?php _e('Actions de gestion', 'pdf-builder-pro'); ?></h5>
            <div class="action-buttons">
                <button type="button" id="clear-cache-transient" class="button">
                    <?php _e('🗑️ Vider transients', 'pdf-builder-pro'); ?>
                </button>
                <button type="button" id="clear-cache-assets" class="button">
                    <?php _e('🗑️ Vider assets', 'pdf-builder-pro'); ?>
                </button>
                <button type="button" id="clear-cache-ajax" class="button">
                    <?php _e('🗑️ Vider AJAX', 'pdf-builder-pro'); ?>
                </button>
                <button type="button" id="clear-cache-images" class="button">
                    <?php _e('🗑️ Vider images', 'pdf-builder-pro'); ?>
                </button>
                <button type="button" id="clear-cache-previews" class="button">
                    <?php _e('🗑️ Vider aperçus', 'pdf-builder-pro'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.cache-settings-section {
    margin: 20px 0;
}

.cache-header h3 {
    margin: 0 0 10px 0;
    color: #23282d;
    font-size: 1.3em;
}

.cache-stats-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.cache-stats-card h4 {
    margin: 0 0 15px 0;
    color: #23282d;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.stat-label {
    font-weight: 600;
    color: #666;
}

.stat-value {
    font-weight: bold;
    color: #007cba;
    font-size: 1.1em;
}

.cache-actions {
    display: flex;
    gap: 10px;
}

.cache-config-section,
.cache-type-section,
.cache-debug-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.cache-config-section h4,
.cache-type-section h4,
.cache-debug-section h4 {
    margin: 0 0 15px 0;
    color: #23282d;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.cache-management-actions h5 {
    margin: 20px 0 10px 0;
    color: #23282d;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.action-buttons .button {
    margin: 0;
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    'use strict';

    // Actualiser les statistiques du cache
    function refreshCacheStats() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_cache_stats',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                if (response.success && response.data && response.data.stats) {
                    const stats = response.data.stats;
                    $('#cache-hits').text(stats.hits.toLocaleString());
                    $('#cache-misses').text(stats.misses.toLocaleString());

                    const total = stats.hits + stats.misses;
                    const hitRate = total > 0 ? ((stats.hits / total) * 100).toFixed(1) : 0;
                    $('#cache-hit-rate').text(hitRate + '%');

                    $('#cache-memory').text(formatBytes(stats.memory_usage));
                    $('#cache-items').text(stats.cache_size.toLocaleString());
                }
            },
            error: function() {
                console.warn('[PDF Builder Cache] Erreur récupération statistiques');
            }
        });
    }

    // Vider un type spécifique de cache
    function clearCache(cacheType) {
        const button = $(`#clear-cache-${cacheType}`);
        const originalText = button.text();

        button.prop('disabled', true).text('<?php _e('Vider...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_clear_cache',
                cache_type: cacheType,
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Afficher un message de succès
                    showNotice('<?php _e('Cache vidé avec succès', 'pdf-builder-pro'); ?>', 'success');

                    // Actualiser les statistiques
                    setTimeout(refreshCacheStats, 1000);
                } else {
                    showNotice(response.data?.message || '<?php _e('Erreur lors du vidage', 'pdf-builder-pro'); ?>', 'error');
                }
            },
            error: function() {
                showNotice('<?php _e('Erreur AJAX', 'pdf-builder-pro'); ?>', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    }

    // Formater les bytes
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Afficher une notice
    function showNotice(message, type) {
        // Utiliser le système de notices de WordPress si disponible
        if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
            wp.data.dispatch('core/notices').createNotice(
                type === 'success' ? 'info' : 'error',
                message,
                { isDismissible: true, type: type }
            );
        } else {
            // Fallback avec alert
            alert(message);
        }
    }

    // Événements
    $('#refresh-cache-stats').on('click', refreshCacheStats);

    $('#clear-cache-all').on('click', function() {
        if (confirm('<?php _e('Vider TOUT le cache ? Cette action est irréversible.', 'pdf-builder-pro'); ?>')) {
            clearCache(null);
        }
    });

    $('#clear-cache-transient').on('click', () => clearCache('transient'));
    $('#clear-cache-assets').on('click', () => clearCache('asset'));
    $('#clear-cache-ajax').on('click', () => clearCache('ajax'));
    $('#clear-cache-images').on('click', () => clearCache('image'));
    $('#clear-cache-previews').on('click', () => clearCache('preview'));

    // Actualisation automatique toutes les 30 secondes si l'onglet est visible
    let statsInterval;
    function startStatsUpdates() {
        if (statsInterval) clearInterval(statsInterval);
        statsInterval = setInterval(refreshCacheStats, 30000);
    }

    function stopStatsUpdates() {
        if (statsInterval) {
            clearInterval(statsInterval);
            statsInterval = null;
        }
    }

    // Démarrer les mises à jour quand l'onglet cache devient visible
    $(document).on('pdf-builder-tab-shown', function(e, tabId) {
        if (tabId === 'cache') {
            startStatsUpdates();
        } else {
            stopStatsUpdates();
        }
    });

    // Arrêter les mises à jour quand la page se ferme
    $(window).on('beforeunload', stopStatsUpdates);
});
</script>
