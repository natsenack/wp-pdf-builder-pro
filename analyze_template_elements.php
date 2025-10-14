<?php
/**
 * Script de debug pour analyser les éléments du template PDF
 * Permet de voir quels éléments sont présents et lesquels sont traités
 */

require_once '../../../wp-load.php';

if (!current_user_can('manage_woocommerce')) {
    die('Permissions insuffisantes');
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;

echo "<h1>🔍 Analyse des éléments du template PDF</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;} .success{color:green;} .warning{color:orange;} .error{color:red;}</style>";

if (!$order_id) {
    echo "<p class='error'>❌ Order ID manquant. Utilisez ?order_id=123</p>";
    echo "<p><a href='" . admin_url('edit.php?post_type=shop_order') . "'>Voir les commandes</a></p>";
    exit;
}

$order = wc_get_order($order_id);
if (!$order) {
    echo "<p class='error'>❌ Commande #$order_id introuvable</p>";
    exit;
}

echo "<h2>📋 Commande #$order_id</h2>";
echo "<p><strong>Statut:</strong> " . $order->get_status() . "</p>";
echo "<p><strong>Client:</strong> " . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . "</p>";

// Charger le template
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

if ($template_id > 0) {
    $template_data = $wpdb->get_var($wpdb->prepare(
        "SELECT template_data FROM $table_templates WHERE id = %d",
        $template_id
    ));
    if ($template_data) {
        $template = json_decode($template_data, true);
        echo "<h2>📄 Template chargé (ID: $template_id)</h2>";
    }
} else {
    // Détection automatique
    $order_status = $order->get_status();
    $status_templates = get_option('pdf_builder_order_status_templates', []);

    $selected_template_id = null;
    $status_key = 'wc-' . $order_status;

    if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
        $selected_template_id = $status_templates[$status_key];
        echo "<h2>🎯 Template détecté automatiquement (statut: $order_status)</h2>";
    } else {
        // Chercher par nom
        $all_templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY name ASC", ARRAY_A);
        foreach ($all_templates as $tpl) {
            if (stripos($tpl['name'], 'facture') !== false || stripos($tpl['name'], 'invoice') !== false) {
                $selected_template_id = $tpl['id'];
                echo "<h2>🔍 Template trouvé par nom (contient 'facture')</h2>";
                break;
            }
        }
    }

    if ($selected_template_id) {
        $template_data = $wpdb->get_var($wpdb->prepare(
            "SELECT template_data FROM $table_templates WHERE id = %d",
            $selected_template_id
        ));
        if ($template_data) {
            $template = json_decode($template_data, true);
            $template_id = $selected_template_id;
        }
    }
}

if (!isset($template) || !$template) {
    echo "<h2 class='error'>❌ ERREUR: Aucun template trouvé !</h2>";
    echo "<h3>Templates disponibles:</h3>";
    $all_templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY name ASC", ARRAY_A);
    echo "<ul>";
    foreach ($all_templates as $tpl) {
        echo "<li><strong>{$tpl['id']}:</strong> " . esc_html($tpl['name']) . "</li>";
    }
    echo "</ul>";
    exit;
}

echo "<p><strong>Template ID:</strong> $template_id</p>";

// Analyser la structure du template
echo "<h2>🏗️ Structure du template</h2>";
echo "<pre style='background:#f5f5f5;padding:10px;max-height:200px;overflow:auto;'>";
echo "Clés du template: " . implode(', ', array_keys($template)) . "\n";
echo "Nombre de pages: " . (isset($template['pages']) ? count($template['pages']) : 0) . "\n";
echo "</pre>";

// Analyser les éléments de chaque page
if (isset($template['pages']) && is_array($template['pages'])) {
    foreach ($template['pages'] as $page_index => $page) {
        echo "<h3>📄 Page " . ($page_index + 1) . "</h3>";

        if (!isset($page['elements']) || !is_array($page['elements'])) {
            echo "<p class='warning'>⚠️ Aucun élément dans cette page</p>";
            continue;
        }

        $elements = $page['elements'];
        echo "<p><strong>" . count($elements) . " éléments trouvés</strong></p>";

        echo "<table>";
        echo "<tr><th>Type</th><th>Contenu</th><th>Position</th><th>Taille</th><th>Statut</th></tr>";

        // Types d'éléments supportés par generate_unified_html
        $supported_types = [
            'text', 'invoice_number', 'order_number', 'invoice_date', 'customer_name',
            'customer_address', 'subtotal', 'tax', 'total', 'rectangle', 'image',
            'company_logo', 'product_table', 'company_info', 'document_type',
            'divider', 'watermark', 'progress-bar', 'barcode', 'qrcode',
            'icon', 'line', 'customer_info'
        ];

        foreach ($elements as $index => $element) {
            $type = $element['type'] ?? 'unknown';
            $content = isset($element['content']) ? substr($element['content'], 0, 50) : '';
            if (strlen($element['content'] ?? '') > 50) $content .= '...';

            // Position et taille
            if (isset($element['position'])) {
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

            $position = "($x, $y)";
            $size = "{$width}x{$height}";

            // Vérifier si le type est supporté
            $is_supported = in_array($type, $supported_types) || strpos($type, 'woocommerce-') === 0;
            $status = $is_supported ? "<span class='success'>✅ Supporté</span>" : "<span class='error'>❌ Non supporté</span>";

            echo "<tr>";
            echo "<td><code>$type</code></td>";
            echo "<td>" . esc_html($content) . "</td>";
            echo "<td>$position</td>";
            echo "<td>$size</td>";
            echo "<td>$status</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} else {
    echo "<p class='error'>❌ Structure de template invalide - pas de pages !</p>";
}

// Tester la génération HTML
echo "<h2>🧪 Test de génération HTML</h2>";

try {
    // Inclure la classe admin pour accéder à generate_unified_html
    if (!class_exists('PDF_Builder_Admin')) {
        require_once plugin_dir_path(__FILE__) . '../../includes/classes/class-pdf-builder-admin.php';
    }

    $admin = PDF_Builder_Admin::getInstance();
    $html_content = $admin->generate_unified_html($template, $order);

    echo "<p class='success'>✅ HTML généré avec succès (" . strlen($html_content) . " caractères)</p>";

    // Analyser le HTML généré
    echo "<h3>📊 Analyse du HTML généré</h3>";

    // Compter les éléments PDF
    $pdf_elements = substr_count($html_content, 'pdf-element');
    echo "<p><strong>Éléments PDF trouvés:</strong> $pdf_elements</p>";

    // Vérifier les types d'éléments présents
    $element_types_found = [];
    if (preg_match_all('/pdf-element ([a-zA-Z0-9_-]+)/', $html_content, $matches)) {
        $element_types_found = array_unique($matches[1]);
    }

    echo "<p><strong>Types d'éléments présents dans le HTML:</strong></p>";
    echo "<ul>";
    foreach ($element_types_found as $type) {
        echo "<li><code>$type</code></li>";
    }
    echo "</ul>";

    // Aperçu du HTML (tronqué)
    echo "<h3>👀 Aperçu HTML (tronqué)</h3>";
    echo "<div style='border:1px solid #ccc;padding:10px;max-height:300px;overflow:auto;background:#f9f9f9;font-family:monospace;font-size:12px;'>";
    echo esc_html(substr($html_content, 0, 1000));
    if (strlen($html_content) > 1000) {
        echo "\n\n[... " . (strlen($html_content) - 1000) . " caractères tronqués ...]";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur lors de la génération HTML: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='" . admin_url('admin.php?page=pdf-builder-diagnostic') . "'>Retour au diagnostic</a></p>";
?>