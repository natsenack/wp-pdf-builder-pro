<?php
/**
 * Script de test de la logique de sélection de template
 */

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

echo "<h1>🧪 Test de sélection de template PDF Builder</h1>";

// Simuler une commande WooCommerce
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 9275;
$order = wc_get_order($order_id);

if (!$order) {
    echo "<p style='color: red;'>❌ Commande #$order_id non trouvée</p>";
    exit;
}

$order_status = $order->get_status();
echo "<p>📋 Commande #$order_id - Statut: <strong>$order_status</strong></p>";

// Connexion à la base de données
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Vérifier s'il y a un mapping spécifique pour ce statut de commande
$status_templates = get_option('pdf_builder_order_status_templates', []);
$status_key = 'wc-' . $order_status;
$mapped_template = null;

echo "<h2>🔍 Étape 1: Mapping spécifique</h2>";
echo "<p>Clé recherchée: <code>$status_key</code></p>";
echo "<p>Mappings disponibles: <pre>" . print_r($status_templates, true) . "</pre></p>";

if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
    $mapped_template = $wpdb->get_row($wpdb->prepare(
        "SELECT id, name FROM $table_templates WHERE id = %d",
        $status_templates[$status_key]
    ), ARRAY_A);
    echo "<p style='color: green;'>✅ Template mappé trouvé: {$mapped_template['name']} (ID: {$mapped_template['id']})</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Aucun mapping spécifique trouvé</p>";
}

// Si pas de mapping spécifique, utiliser la logique de détection automatique
$template_id = null;
if ($mapped_template) {
    $template_id = $mapped_template['id'];
    echo "<p style='color: green;'>🎯 Template sélectionné: {$mapped_template['name']} (ID: $template_id)</p>";
} else {
    echo "<h2>🔍 Étape 2: Détection automatique</h2>";

    // Logique de détection automatique basée sur le statut
    $keywords = [];
    switch ($order_status) {
        case 'pending':
            $keywords = ['devis', 'quote', 'estimation'];
            break;
        case 'processing':
        case 'on-hold':
            $keywords = ['facture', 'invoice', 'commande'];
            break;
        case 'completed':
            $keywords = ['facture', 'invoice', 'reçu', 'receipt'];
            break;
        case 'cancelled':
        case 'refunded':
            $keywords = ['avoir', 'credit', 'refund'];
            break;
        case 'failed':
            $keywords = ['erreur', 'failed', 'échoué'];
            break;
        default:
            $keywords = ['facture', 'invoice'];
            break;
    }

    echo "<p>Mots-clés pour le statut '$order_status': <code>" . implode(', ', $keywords) . "</code></p>";

    if (!empty($keywords)) {
        // Chercher un template par défaut dont le nom contient un mot-clé
        $placeholders = str_repeat('%s,', count($keywords) - 1) . '%s';
        $sql = $wpdb->prepare(
            "SELECT id, name FROM $table_templates WHERE is_default = 1 AND (" .
            implode(' OR ', array_fill(0, count($keywords), 'LOWER(name) LIKE LOWER(%s)')) .
            ") LIMIT 1",
            array_map(function($keyword) { return '%' . $keyword . '%'; }, $keywords)
        );

        echo "<p>Requête SQL: <code>$sql</code></p>";

        $keyword_template = $wpdb->get_row($sql, ARRAY_A);

        if ($keyword_template) {
            $template_id = $keyword_template['id'];
            echo "<p style='color: green;'>✅ Template trouvé par mots-clés: {$keyword_template['name']} (ID: $template_id)</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Aucun template trouvé par mots-clés</p>";
        }
    }

    // Si aucun template spécifique trouvé, prendre n'importe quel template par défaut
    if (!$template_id) {
        $default_template = $wpdb->get_row("SELECT id, name FROM $table_templates WHERE is_default = 1 LIMIT 1", ARRAY_A);
        if ($default_template) {
            $template_id = $default_template['id'];
            echo "<p style='color: blue;'>🔄 Template par défaut utilisé: {$default_template['name']} (ID: $template_id)</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Aucun template par défaut trouvé</p>";
        }
    }

    // Si toujours pas de template, prendre le premier template disponible
    if (!$template_id) {
        $any_template = $wpdb->get_row("SELECT id, name FROM $table_templates ORDER BY id LIMIT 1", ARRAY_A);
        if ($any_template) {
            $template_id = $any_template['id'];
            echo "<p style='color: orange;'>🔄 Premier template disponible: {$any_template['name']} (ID: $template_id)</p>";
        } else {
            echo "<p style='color: red;'>❌ Aucun template trouvé dans la base de données</p>";
        }
    }
}

echo "<h2>📊 Résultat final</h2>";
if ($template_id) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ Template sélectionné: ID $template_id</p>";

    // Afficher les détails du template
    $template_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);
    echo "<h3>Détails du template:</h3>";
    echo "<ul>";
    echo "<li><strong>Nom:</strong> {$template_details['name']}</li>";
    echo "<li><strong>Par défaut:</strong> " . ($template_details['is_default'] ? 'Oui' : 'Non') . "</li>";
    echo "<li><strong>Créé:</strong> {$template_details['created_at']}</li>";
    echo "<li><strong>Modifié:</strong> {$template_details['updated_at']}</li>";
    echo "</ul>";

    // Tester la décodage des données JSON
    $template_data = json_decode($template_details['template_data'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $elements_count = isset($template_data['elements']) ? count($template_data['elements']) : 0;
        echo "<p style='color: green;'>✅ Données JSON valides - $elements_count éléments trouvés</p>";
    } else {
        echo "<p style='color: red;'>❌ Erreur JSON: " . json_last_error_msg() . "</p>";
    }

} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ Aucun template sélectionné</p>";
}

echo "<hr>";
echo "<p><a href='?order_id=9275'>Tester commande 9275</a> | <a href='?order_id=9276'>Tester commande 9276</a></p>";
?>