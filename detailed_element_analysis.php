<?php
/**
 * Script détaillé pour analyser la structure des éléments du template
 * Affiche la structure JSON complète des éléments pour diagnostiquer les problèmes
 */

require_once '../../../wp-load.php';

if (!current_user_can('manage_woocommerce')) {
    die('Permissions insuffisantes');
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 9275; // Default to the problematic order
$template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;

echo "<h1>🔬 Analyse détaillée des éléments du template</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .json-container{background:#f5f5f5;border:1px solid #ddd;padding:10px;margin:10px 0;max-height:400px;overflow:auto;font-family:monospace;font-size:12px;} .error{color:red;} .warning{color:orange;} .success{color:green;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:8px;text-align:left;} th{background:#f2f2f2;}</style>";

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
} else {
    // Détection automatique
    $order_status = $order->get_status();
    $status_templates = get_option('pdf_builder_order_status_templates', []);

    $selected_template_id = null;
    $status_key = 'wc-' . $order_status;

    if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
        $selected_template_id = $status_templates[$status_key];
    } else {
        // Chercher par nom
        $all_templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY name ASC", ARRAY_A);
        foreach ($all_templates as $tpl) {
            if (stripos($tpl['name'], 'facture') !== false || stripos($tpl['name'], 'invoice') !== false) {
                $selected_template_id = $tpl['id'];
                break;
            }
        }
    }

    if ($selected_template_id) {
        $template_data = $wpdb->get_var($wpdb->prepare(
            "SELECT template_data FROM $table_templates WHERE id = %d",
            $selected_template_id
        ));
        $template_id = $selected_template_id;
    }
}

if (!$template_data) {
    echo "<p class='error'>❌ Aucune donnée de template trouvée</p>";
    exit;
}

$template = json_decode($template_data, true);
if (!$template) {
    echo "<p class='error'>❌ Erreur de décodage JSON du template</p>";
    exit;
}

echo "<h2>📄 Template ID: $template_id</h2>";

// Afficher la structure complète du template (tronquée)
echo "<h3>🏗️ Structure complète du template (aperçu)</h3>";
echo "<div class='json-container'>";
echo "Clés principales: " . implode(', ', array_keys($template)) . "<br>";
if (isset($template['pages'])) {
    echo "Nombre de pages: " . count($template['pages']) . "<br>";
    if (!empty($template['pages'])) {
        echo "Page 0 - Clés: " . implode(', ', array_keys($template['pages'][0])) . "<br>";
        if (isset($template['pages'][0]['elements'])) {
            echo "Nombre d'éléments: " . count($template['pages'][0]['elements']) . "<br>";
        }
    }
}
echo "</div>";

// Analyser chaque élément en détail
if (isset($template['pages'][0]['elements'])) {
    $elements = $template['pages'][0]['elements'];

    echo "<h3>🔍 Analyse détaillée des " . count($elements) . " éléments</h3>";

    foreach ($elements as $index => $element) {
        echo "<h4>📦 Élément #" . ($index + 1) . "</h4>";

        echo "<table>";
        echo "<tr><th>Propriété</th><th>Valeur</th><th>Statut</th></tr>";

        // Type
        $type = $element['type'] ?? 'unknown';
        $supported_types = [
            'text', 'invoice_number', 'order_number', 'invoice_date', 'customer_name',
            'customer_address', 'subtotal', 'tax', 'total', 'rectangle', 'image',
            'company_logo', 'product_table', 'company_info', 'document_type',
            'divider', 'watermark', 'progress-bar', 'barcode', 'qrcode',
            'icon', 'line', 'customer_info'
        ];
        $is_supported = in_array($type, $supported_types) || strpos($type, 'woocommerce-') === 0;
        $type_status = $is_supported ? "<span class='success'>✅ Supporté</span>" : "<span class='error'>❌ Non supporté</span>";

        echo "<tr><td><strong>Type</strong></td><td><code>$type</code></td><td>$type_status</td></tr>";

        // Structure de position/taille
        $has_structured_pos = isset($element['position']) && isset($element['size']);
        $has_flat_pos = isset($element['x']) && isset($element['y']) && isset($element['width']) && isset($element['height']);

        if ($has_structured_pos) {
            $x = $element['position']['x'] ?? 'N/A';
            $y = $element['position']['y'] ?? 'N/A';
            $width = $element['size']['width'] ?? 'N/A';
            $height = $element['size']['height'] ?? 'N/A';
            echo "<tr><td>Position (structuré)</td><td>x: $x, y: $y</td><td><span class='success'>✅ OK</span></td></tr>";
            echo "<tr><td>Taille (structuré)</td><td>width: $width, height: $height</td><td><span class='success'>✅ OK</span></td></tr>";
        } elseif ($has_flat_pos) {
            $x = $element['x'] ?? 'N/A';
            $y = $element['y'] ?? 'N/A';
            $width = $element['width'] ?? 'N/A';
            $height = $element['height'] ?? 'N/A';
            echo "<tr><td>Position (plat)</td><td>x: $x, y: $y</td><td><span class='success'>✅ OK</span></td></tr>";
            echo "<tr><td>Taille (plat)</td><td>width: $width, height: $height</td><td><span class='success'>✅ OK</span></td></tr>";
        } else {
            echo "<tr><td>Position/Taille</td><td>MANQUANT</td><td><span class='error'>❌ Problème détecté</span></td></tr>";
        }

        // Contenu
        $content = $element['content'] ?? '';
        $content_length = strlen($content);
        $content_preview = $content_length > 100 ? substr($content, 0, 100) . '...' : $content;
        echo "<tr><td>Contenu</td><td><code>" . esc_html($content_preview) . "</code></td><td>" . ($content_length > 0 ? "<span class='success'>✅ Présent ($content_length chars)</span>" : "<span class='warning'>⚠️ Vide</span>") . "</td></tr>";

        // Style
        if (isset($element['style'])) {
            echo "<tr><td>Style</td><td>" . json_encode($element['style']) . "</td><td><span class='success'>✅ Présent</span></td></tr>";
        } else {
            echo "<tr><td>Style</td><td>N/A</td><td><span class='warning'>⚠️ Manquant</span></td></tr>";
        }

        // Autres propriétés importantes
        $important_props = ['imageUrl', 'icon', 'progress', 'thickness', 'color', 'opacity'];
        foreach ($important_props as $prop) {
            if (isset($element[$prop])) {
                echo "<tr><td>$prop</td><td>" . json_encode($element[$prop]) . "</td><td><span class='success'>✅ Présent</span></td></tr>";
            }
        }

        echo "</table>";

        // Afficher la structure JSON complète de l'élément
        echo "<h5>📋 Structure JSON complète :</h5>";
        echo "<div class='json-container'>";
        echo json_encode($element, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "</div>";

        echo "<hr>";
    }
} else {
    echo "<p class='error'>❌ Aucun élément trouvé dans le template</p>";
}

// Tester la génération HTML
echo "<h2>🧪 Test de génération HTML</h2>";

try {
    if (!class_exists('PDF_Builder_Admin')) {
        require_once plugin_dir_path(__FILE__) . '../../includes/classes/class-pdf-builder-admin.php';
    }

    $admin = PDF_Builder_Admin::getInstance();
    $html_content = $admin->generate_unified_html($template, $order);

    echo "<p class='success'>✅ HTML généré avec succès (" . strlen($html_content) . " caractères)</p>";

    // Compter les éléments
    $element_count = substr_count($html_content, 'class="pdf-element');
    echo "<p><strong>Éléments PDF rendus:</strong> $element_count / " . count($elements ?? []) . "</p>";

    if ($element_count === 0) {
        echo "<p class='error'>❌ AUCUN élément PDF n'a été rendu ! Il y a un problème majeur.</p>";
    } elseif ($element_count < count($elements ?? [])) {
        echo "<p class='warning'>⚠️ Certains éléments ne sont pas rendus (" . (count($elements ?? []) - $element_count) . " manquants)</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur génération HTML: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='" . admin_url('admin.php?page=wc-orders&action=edit&id=' . $order_id) . "'>Retour à la commande</a> | ";
echo "<a href='" . admin_url('admin.php?page=pdf-builder-diagnostic') . "'>Diagnostic</a></p>";
?>