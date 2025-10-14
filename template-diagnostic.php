<?php
/**
 * Script de diagnostic des templates PDF Builder
 */

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

echo "<h1>🔍 Diagnostic des Templates PDF Builder</h1>";

// Connexion à la base de données
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Vérifier si la table existe
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_templates'") === $table_templates;

if (!$table_exists) {
    echo "<p style='color: red;'>❌ La table $table_templates n'existe pas !</p>";
    exit;
}

echo "<p style='color: green;'>✅ Table $table_templates trouvée</p>";

// Compter les templates
$total_templates = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates");
echo "<p>📊 Nombre total de templates : <strong>$total_templates</strong></p>";

// Récupérer tous les templates
$templates = $wpdb->get_results("SELECT id, name, is_default, created_at FROM $table_templates ORDER BY id", ARRAY_A);

if (empty($templates)) {
    echo "<p style='color: orange;'>⚠️ Aucun template trouvé dans la base de données</p>";
} else {
    echo "<h2>📋 Liste des templates :</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nom</th><th>Par défaut</th><th>Créé le</th><th>Actions</th></tr>";

    foreach ($templates as $template) {
        $is_default = $template['is_default'] ? '✅ Oui' : '❌ Non';
        $default_class = $template['is_default'] ? 'style="background-color: #e8f5e8;"' : '';

        echo "<tr $default_class>";
        echo "<td>{$template['id']}</td>";
        echo "<td>{$template['name']}</td>";
        echo "<td>$is_default</td>";
        echo "<td>{$template['created_at']}</td>";
        echo "<td><button onclick='testTemplate({$template['id']})'>Tester</button></td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Template par défaut
$default_template = $wpdb->get_row("SELECT id, name FROM $table_templates WHERE is_default = 1 LIMIT 1", ARRAY_A);
if ($default_template) {
    echo "<p style='color: green; font-weight: bold;'>✅ Template par défaut : {$default_template['name']} (ID: {$default_template['id']})</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Aucun template par défaut défini</p>";
}

echo "<h2>🧪 Test de génération d'aperçu</h2>";
echo "<p>Order ID: <input type='text' id='orderId' value='9275' /></p>";
echo "<button onclick='testPreview()'>Tester l'aperçu</button>";
echo "<div id='result'></div>";

echo "<script>
function testTemplate(templateId) {
    alert('Template ID: ' + templateId);
}

function testPreview() {
    const orderId = document.getElementById('orderId').value;
    const resultDiv = document.getElementById('result');

    resultDiv.innerHTML = '⏳ Test en cours...';

    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'pdf_builder_preview_order_pdf',
            order_id: orderId,
            nonce: '" . wp_create_nonce('pdf_builder_order_actions') . "'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '✅ Succès : <a href=\"' + data.data.url + '\" target=\"_blank\">Voir le PDF</a>';
        } else {
            resultDiv.innerHTML = '❌ Erreur : ' + data.data;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '❌ Erreur réseau : ' + error;
    });
}
</script>";
?>