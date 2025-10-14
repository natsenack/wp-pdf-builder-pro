<?php
/**
 * Diagnostic script pour tester la génération PDF des commandes WooCommerce
 */

echo "<h1>🔍 Diagnostic PDF Builder Pro - Génération commande</h1>";

// Vérifier que WordPress est chargé
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
    require_once ABSPATH . 'wp-load.php';
}

echo "<h2>1. Vérification des dépendances</h2>";

// Vérifier WooCommerce
if (class_exists('WooCommerce')) {
    echo "✅ WooCommerce actif<br>";
} else {
    echo "❌ WooCommerce non actif<br>";
}

// Vérifier TCPDF
if (class_exists('TCPDF')) {
    echo "✅ TCPDF disponible<br>";
} else {
    echo "❌ TCPDF non disponible<br>";
}

// Vérifier les fonctions WooCommerce
if (function_exists('wc_get_order')) {
    echo "✅ wc_get_order disponible<br>";
} else {
    echo "❌ wc_get_order non disponible<br>";
}

echo "<h2>2. Test de récupération commande</h2>";

// Tester avec la commande #9275
$order_id = 9275;
$order = wc_get_order($order_id);

if ($order) {
    echo "✅ Commande #{$order_id} trouvée<br>";
    echo "Numéro de commande: " . $order->get_order_number() . "<br>";
    echo "Statut: " . $order->get_status() . "<br>";
    echo "Total: " . $order->get_total() . " " . $order->get_currency() . "<br>";
} else {
    echo "❌ Commande #{$order_id} non trouvée<br>";
}

echo "<h2>3. Test de récupération template</h2>";

// Tester la récupération d'un template
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

$templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY id DESC LIMIT 5", ARRAY_A);

if (!empty($templates)) {
    echo "✅ Templates trouvés:<br>";
    foreach ($templates as $template) {
        echo "- ID: {$template['id']}, Nom: {$template['name']}<br>";
    }

    // Tester le chargement du premier template
    // Tester avec un template valide au lieu du corrompu
$valid_template_id = null;
$valid_templates = array_filter($templates, function($template) {
    return strpos($template['name'], '[CORROMPU]') === false;
});

if (!empty($valid_templates)) {
    $valid_template_id = reset($valid_templates)['id'];
    echo "✅ Utilisation du template valide #{$valid_template_id}<br>";
} else {
    echo "❌ Aucun template valide trouvé, utilisation du template par défaut intégré<br>";
    $valid_template_id = 0; // Utilisera le template par défaut
}

$template_id = $valid_template_id;

// Pour le test, forçons l'utilisation du template par défaut si le template chargé est corrompu
$template_data = json_decode($template['template_data'], true);
if (!$template_data) {
    echo "🔄 Template corrompu détecté, basculement vers template par défaut<br>";
    $template_id = 0;
}
    $template = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_templates WHERE id = %d",
        $template_id
    ), ARRAY_A);

if ($template_id > 0) {
    $template = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_templates WHERE id = %d",
        $template_id
    ), ARRAY_A);

    if ($template) {
        echo "✅ Template #{$template_id} chargé<br>";
        $template_data = json_decode($template['template_data'], true);
        if ($template_data) {
            echo "✅ Données template valides (JSON)<br>";
        } else {
            echo "❌ Données template invalides (JSON)<br>";
        }
    } else {
        echo "❌ Échec chargement template #{$template_id}<br>";
    }
} else {
    echo "🔄 Utilisation du template par défaut intégré<br>";
    $template_data = null; // Sera géré par get_default_invoice_template()
}
} else {
    echo "❌ Aucun template trouvé<br>";
}

echo "<h2>4. Test génération PDF (simulation)</h2>";

// Simuler l'appel à generate_order_pdf
if (class_exists('PDF_Builder_Core')) {
    $core = PDF_Builder_Core::getInstance();
    if (method_exists($core, 'generate_order_pdf')) {
        echo "✅ Méthode generate_order_pdf disponible dans PDF_Builder_Core<br>";

        // Tester avec la commande et le template
        if ($order && isset($template_id)) {
            echo "🧪 Test génération PDF...<br>";

            $result = $core->generate_order_pdf($order_id, $template_id, true);

            if (is_wp_error($result)) {
                echo "❌ Erreur génération PDF: " . $result->get_error_message() . "<br>";
            } elseif (is_string($result) && !empty($result)) {
                echo "✅ PDF généré avec succès<br>";
                echo "URL: <a href='{$result}' target='_blank'>{$result}</a><br>";

                // Vérifier si le fichier existe
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $result);

                if (file_exists($file_path)) {
                    echo "✅ Fichier PDF existe sur le serveur<br>";
                    echo "Taille: " . filesize($file_path) . " bytes<br>";
                } else {
                    echo "❌ Fichier PDF n'existe pas sur le serveur<br>";
                    echo "Chemin attendu: {$file_path}<br>";
                }
            } else {
                echo "❌ Résultat invalide de generate_order_pdf<br>";
                var_dump($result);
            }
        } else {
            echo "⚠️ Commande ou template manquant pour le test<br>";
        }
    } else {
        echo "❌ Méthode generate_order_pdf non disponible dans PDF_Builder_Core<br>";
    }
} else {
    echo "❌ Classe PDF_Builder_Core non disponible<br>";
}

echo "<h2>5. Test des permissions d'écriture</h2>";

// Tester les permissions d'écriture
$upload_dir = wp_upload_dir();
$pdf_dir = $upload_dir['basedir'] . '/pdf-builder/orders';

if (!file_exists($pdf_dir)) {
    if (wp_mkdir_p($pdf_dir)) {
        echo "✅ Répertoire PDF créé: {$pdf_dir}<br>";
    } else {
        echo "❌ Impossible de créer le répertoire PDF<br>";
    }
} else {
    echo "✅ Répertoire PDF existe: {$pdf_dir}<br>";
}

if (is_writable($pdf_dir)) {
    echo "✅ Répertoire PDF accessible en écriture<br>";
} else {
    echo "❌ Répertoire PDF non accessible en écriture<br>";
}

echo "<h2>6. Informations système</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "WordPress Version: " . get_bloginfo('version') . "<br>";
echo "Upload dir: " . $upload_dir['basedir'] . "<br>";
echo "Upload URL: " . $upload_dir['baseurl'] . "<br>";

echo "<hr>";
echo "<p><strong>Fin du diagnostic</strong></p>";
?></content>
<parameter name="filePath">g:\wp-pdf-builder-pro\debug_pdf_order_generation.php