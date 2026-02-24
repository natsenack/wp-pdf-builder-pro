<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
/**
 * Outil de diagnostic pour le système de monitoring des cartes canvas
 * Permet de vérifier l'état du système et diagnostiquer les problèmes
 */

// Sécurité
if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

// Vérifier les permissions
if (!current_user_can('manage_options')) {
    wp_die('Permissions insuffisantes');
}

$settings = pdf_builder_get_option('pdf_builder_settings', array());

// Fonction pour analyser l'état du système
function analyze_canvas_monitoring_system($settings) {
    $results = array(
        'status' => 'unknown',
        'issues' => array(),
        'recommendations' => array(),
        'details' => array()
    );

    // 1. Vérifier les paramètres de base
    $required_settings = array(
        'pdf_builder_canvas_width',
        'pdf_builder_canvas_height',
        'pdf_builder_canvas_dpi',
        'pdf_builder_canvas_format',
        'pdf_builder_canvas_bg_color',
        'pdf_builder_canvas_border_color'
    );

    $missing_settings = array();
    foreach ($required_settings as $setting) {
        if (!isset($settings[$setting]) || empty($settings[$setting])) {
            $missing_settings[] = $setting;
        }
    }

    if (!empty($missing_settings)) {
        $results['issues'][] = 'Paramètres manquants: ' . implode(', ', $missing_settings);
        $results['recommendations'][] = 'Réinitialiser les paramètres canvas par défaut';
    }

    // 2. Vérifier la cohérence des dimensions
    $width = intval($settings['pdf_builder_canvas_width'] ?? 0);
    $height = intval($settings['pdf_builder_canvas_height'] ?? 0);
    $dpi = intval($settings['pdf_builder_canvas_dpi'] ?? 0);

    if ($width <= 0 || $height <= 0) {
        $results['issues'][] = 'Dimensions canvas invalides (width ou height <= 0)';
    }

    if ($dpi <= 0) {
        $results['issues'][] = 'DPI canvas invalide (<= 0)';
    }

    // 3. Vérifier les couleurs
    $color_fields = array(
        'pdf_builder_canvas_bg_color',
        'pdf_builder_canvas_border_color',
        'pdf_builder_canvas_container_bg_color'
    );

    foreach ($color_fields as $field) {
        $color = $settings[$field] ?? '';
        if (!empty($color) && !preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            $results['issues'][] = "Couleur invalide pour $field: $color";
        }
    }

    // 4. Vérifier les valeurs numériques
    $numeric_fields = array(
        'pdf_builder_canvas_border_width' => array('min' => 0, 'max' => 10),
        'pdf_builder_canvas_grid_size' => array('min' => 5, 'max' => 100),
        'pdf_builder_canvas_zoom_min' => array('min' => 1, 'max' => 100),
        'pdf_builder_canvas_zoom_max' => array('min' => 100, 'max' => 1000),
        'pdf_builder_canvas_zoom_default' => array('min' => 10, 'max' => 500),
        'pdf_builder_canvas_export_quality' => array('min' => 1, 'max' => 100)
    );

    foreach ($numeric_fields as $field => $range) {
        $value = intval($settings[$field] ?? 0);
        if ($value < $range['min'] || $value > $range['max']) {
            $results['issues'][] = "Valeur hors limites pour $field: $value (doit être entre {$range['min']} et {$range['max']})";
        }
    }

    // 5. Vérifier les formats de papier
    $valid_formats = array('A4', 'A3', 'A5', 'Letter', 'Legal', 'Tabloid');
    $format = $settings['pdf_builder_canvas_format'] ?? 'A4';
    if (!in_array($format, $valid_formats)) {
        $results['issues'][] = "Format de papier invalide: $format";
        $results['recommendations'][] = 'Utiliser un format valide: ' . implode(', ', $valid_formats);
    }

    // Déterminer le statut global
    if (empty($results['issues'])) {
        $results['status'] = 'healthy';
        $results['recommendations'][] = 'Système fonctionnel - aucune action requise';
    } elseif (count($results['issues']) <= 2) {
        $results['status'] = 'warning';
        $results['recommendations'][] = 'Vérifier et corriger les paramètres problématiques';
    } else {
        $results['status'] = 'error';
        $results['recommendations'][] = 'Réinitialisation complète des paramètres recommandée';
    }

    // Détails supplémentaires
    $results['details'] = array(
        'total_settings' => count($settings),
        'canvas_settings_count' => count(array_filter(array_keys($settings), function($key) {
            return strpos($key, 'pdf_builder_canvas_') === 0;
        })),
        'last_analysis' => current_time('mysql')
    );

    return $results;
}

// Analyser le système
$analysis = analyze_canvas_monitoring_system($settings);

// Interface HTML
?>
<div class="wrap">
    <h1>🔍 Diagnostic du système de monitoring Canvas</h1>

    <div class="notice <?php echo $analysis['status'] === 'healthy' ? 'notice-success' : ($analysis['status'] === 'warning' ? 'notice-warning' : 'notice-error'); ?> inline">
        <p><strong>Statut du système:</strong>
            <?php if ($analysis['status'] === 'healthy'): ?>
                <span style="color: #28a745;">✅ Système sain</span>
            <?php elseif ($analysis['status'] === 'warning'): ?>
                <span style="color: #ffc107;">⚠️ Avertissements détectés</span>
            <?php else: ?>
                <span style="color: #dc3545;">❌ Problèmes critiques</span>
            <?php endif; ?>
        </p>
    </div>

    <div class="card">
        <h2>📊 Résumé de l'analyse</h2>
        <table class="widefat">
            <tbody>
                <tr>
                    <td><strong>Paramètres totaux</strong></td>
                    <td><?php echo esc_html($analysis['details']['total_settings']); ?></td>
                </tr>
                <tr>
                    <td><strong>Paramètres Canvas</strong></td>
                    <td><?php echo esc_html($analysis['details']['canvas_settings_count']); ?></td>
                </tr>
                <tr>
                    <td><strong>Problèmes détectés</strong></td>
                    <td><?php echo count($analysis['issues']); ?></td>
                </tr>
                <tr>
                    <td><strong>Dernière analyse</strong></td>
                    <td><?php echo esc_html($analysis['details']['last_analysis']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if (!empty($analysis['issues'])): ?>
    <div class="card">
        <h2>⚠️ Problèmes détectés</h2>
        <ul class="ul-disc">
            <?php foreach ($analysis['issues'] as $issue): ?>
            <li><?php echo esc_html($issue); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>💡 Recommandations</h2>
        <ul class="ul-disc">
            <?php foreach ($analysis['recommendations'] as $rec): ?>
            <li><?php echo esc_html($rec); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card">
        <h2>🔧 Actions disponibles</h2>
        <p>
            <button type="button" class="button button-primary" onclick="forceCanvasResync()">
                🔄 Forcer la resynchronisation
            </button>
            <button type="button" class="button button-secondary" onclick="resetCanvasDefaults()">
                🔙 Réinitialiser les paramètres par défaut
            </button>
            <button type="button" class="button button-secondary" onclick="exportCanvasSettings()">
                📤 Exporter les paramètres
            </button>
        </p>
    </div>

    <div class="card">
        <h2>📋 Paramètres actuels</h2>
        <details>
            <summary>Afficher tous les paramètres canvas</summary>
            <pre style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 400px; overflow-y: auto;">
<?php
$canvas_settings = array_filter($settings, function($key) {
    return strpos($key, 'pdf_builder_canvas_') === 0;
}, ARRAY_FILTER_USE_KEY);

ksort($canvas_settings);
echo json_encode($canvas_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
            </pre>
        </details>
    </div>
</div>

<script>
function forceCanvasResync() {
    if (confirm('Forcer la resynchronisation de toutes les cartes canvas ?')) {
        // Simuler un clic sur le bouton de sauvegarde pour déclencher la resynchronisation
        const saveBtn = document.querySelector('.pdf-builder-save-btn');
        if (saveBtn) {
            saveBtn.click();
            alert('Resynchronisation lancée. Actualisez la page dans quelques secondes.');
        } else {
            alert('Bouton de sauvegarde non trouvé. Actualisez la page manuellement.');
        }
    }
}

function resetCanvasDefaults() {
    if (confirm('Réinitialiser tous les paramètres canvas aux valeurs par défaut ? Cette action est irréversible.')) {
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'reset_canvas_defaults',
                nonce: '<?php echo esc_attr(wp_create_nonce('reset_canvas_defaults')); ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Paramètres réinitialisés avec succès. Actualisez la page.');
                location.reload();
            } else {
                alert('Erreur lors de la réinitialisation: ' + (data.data || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            alert('Erreur réseau: ' + error.message);
        });
    }
}

function exportCanvasSettings() {
    const settings = <?php echo json_encode($canvas_settings, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    const dataStr = JSON.stringify(settings, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);

    const exportFileDefaultName = 'canvas-settings-' + new Date().toISOString().split('T')[0] + '.json';

    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
}
</script></content>
<parameter name="filePath">i:\pdf-builder-pro\plugin\resources\templates\admin\canvas-monitor-diagnostic.php

