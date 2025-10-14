<?php
/**
 * Script de diagnostic pour l'erreur "Erreur inconnue lors de la génération"
 */

// Vérifier que WordPress est chargé
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
    require_once ABSPATH . 'wp-load.php';
}

echo "<h1>🔍 Diagnostic - Erreur inconnue lors de la génération</h1>";

// Simuler les paramètres d'une requête AJAX
$order_id = 9275; // ID de commande à tester - modifier selon les commandes disponibles
$template_id = 0; // 0 pour template par défaut

echo "<h2>1. Test des dépendances</h2>";

// Vérifier WooCommerce
if (class_exists('WooCommerce')) {
    echo "✅ WooCommerce actif<br>";
} else {
    echo "❌ WooCommerce non actif<br>";
    exit;
}

// Vérifier TCPDF
if (class_exists('TCPDF')) {
    echo "✅ TCPDF déjà chargé<br>";
} else {
    echo "❌ TCPDF non chargé - Tentative de chargement...<br>";
    // Utiliser la même logique que PDF_Builder_Admin::load_tcpdf_library()
    $tcpdf_paths = [
        __DIR__ . '/lib/tcpdf/tcpdf.php',  // Essayer d'abord tcpdf.php directement
        __DIR__ . '/lib/tcpdf/tcpdf_autoload.php',
        __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php',
        plugin_dir_path(__FILE__) . 'lib/tcpdf/tcpdf.php',
        plugin_dir_path(__FILE__) . 'lib/tcpdf/tcpdf_autoload.php',
        plugin_dir_path(__FILE__) . 'vendor/tecnickcom/tcpdf/tcpdf.php'
    ];

    $tcpdf_loaded = false;
    foreach ($tcpdf_paths as $path) {
        if (file_exists($path)) {
            echo "Tentative de chargement depuis: " . basename(dirname($path)) . '/' . basename($path) . "<br>";
            require_once $path;
            if (class_exists('TCPDF')) {
                $tcpdf_loaded = true;
                echo "✅ TCPDF chargé depuis: " . basename(dirname($path)) . '/' . basename($path) . "<br>";

                // Définir les constantes TCPDF si elles ne sont pas définies
                if (!defined('K_PATH_FONTS')) {
                    define('K_PATH_FONTS', __DIR__ . '/lib/tcpdf/fonts/');
                }
                if (!defined('K_PATH_CACHE')) {
                    define('K_PATH_CACHE', __DIR__ . '/cache/');
                }
                if (!defined('K_PATH_URL_CACHE')) {
                    define('K_PATH_URL_CACHE', plugin_dir_url(__FILE__) . 'cache/');
                }
                if (!defined('K_PATH_IMAGES')) {
                    define('K_PATH_IMAGES', __DIR__ . '/lib/tcpdf/images/');
                }
                if (!defined('K_BLANK_IMAGE')) {
                    define('K_BLANK_IMAGE', K_PATH_IMAGES . 'blank.png');
                }
                if (!defined('PDF_PAGE_FORMAT')) {
                    define('PDF_PAGE_FORMAT', 'A4');
                }
                if (!defined('PDF_PAGE_ORIENTATION')) {
                    define('PDF_PAGE_ORIENTATION', 'P');
                }
                if (!defined('PDF_CREATOR')) {
                    define('PDF_CREATOR', 'PDF Builder Pro');
                }
                if (!defined('PDF_AUTHOR')) {
                    define('PDF_AUTHOR', 'PDF Builder Pro');
                }
                if (!defined('PDF_HEADER_TITLE')) {
                    define('PDF_HEADER_TITLE', 'PDF Builder Pro');
                }
                if (!defined('PDF_HEADER_STRING')) {
                    define('PDF_HEADER_STRING', "par PDF Builder Pro\nwww.pdfbuilderpro.com");
                }
                if (!defined('PDF_UNIT')) {
                    define('PDF_UNIT', 'mm');
                }
                if (!defined('PDF_MARGIN_HEADER')) {
                    define('PDF_MARGIN_HEADER', 5);
                }
                if (!defined('PDF_MARGIN_FOOTER')) {
                    define('PDF_MARGIN_FOOTER', 10);
                }
                if (!defined('PDF_MARGIN_TOP')) {
                    define('PDF_MARGIN_TOP', 27);
                }
                if (!defined('PDF_MARGIN_BOTTOM')) {
                    define('PDF_MARGIN_BOTTOM', 25);
                }
                if (!defined('PDF_MARGIN_LEFT')) {
                    define('PDF_MARGIN_LEFT', 15);
                }
                if (!defined('PDF_MARGIN_RIGHT')) {
                    define('PDF_MARGIN_RIGHT', 15);
                }
                if (!defined('PDF_FONT_SIZE_MAIN')) {
                    define('PDF_FONT_SIZE_MAIN', 10);
                }
                if (!defined('PDF_FONT_SIZE_DATA')) {
                    define('PDF_FONT_SIZE_DATA', 8);
                }
                if (!defined('PDF_FONT_MONOSPACED')) {
                    define('PDF_FONT_MONOSPACED', 'courier');
                }
                if (!defined('PDF_IMAGE_SCALE_RATIO')) {
                    define('PDF_IMAGE_SCALE_RATIO', 1.25);
                }

                break;
            }
        }
    }

    if (!$tcpdf_loaded) {
        echo "❌ Impossible de charger TCPDF<br>";
        exit;
    }
}

echo "<h2>2. Test de récupération commande</h2>";

$order = wc_get_order($order_id);
if ($order) {
    echo "✅ Commande trouvée: #" . $order->get_order_number() . "<br>";
    echo "Statut: " . $order->get_status() . "<br>";
    echo "Total: " . $order->get_total() . " " . $order->get_currency() . "<br>";
} else {
    echo "❌ Commande non trouvée<br>";
    exit;
}

echo "<h2>3. Test de chargement template</h2>";

// Instancier la classe admin
$core = PDF_Builder_Core::getInstance();
$admin = PDF_Builder_Admin::getInstance($core);

if ($template_id > 0) {
    $template_data = $admin->load_template_robust($template_id);
    echo "✅ Template chargé depuis database: $template_id<br>";
} else {
    $template_data = $admin->get_default_invoice_template();
    echo "✅ Template par défaut chargé<br>";
}

if (!$template_data) {
    echo "❌ Échec chargement template<br>";
    exit;
}

echo "<h2>4. Test de génération HTML</h2>";

try {
    $html_content = $admin->generate_unified_html($template_data, $order);
    if (!empty($html_content)) {
        echo "✅ HTML généré - Longueur: " . strlen($html_content) . " caractères<br>";
        echo "<details><summary>Afficher les 500 premiers caractères du HTML</summary><pre>" . esc_html(substr($html_content, 0, 500)) . "...</pre></details>";
    } else {
        echo "❌ HTML vide généré<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Exception lors de la génération HTML: " . $e->getMessage() . "<br>";
    exit;
} catch (Error $e) {
    echo "❌ Erreur fatale lors de la génération HTML: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>5. Test de génération PDF</h2>";

try {
    $result = $admin->generate_order_pdf($order_id, $template_id, true);

    if (is_wp_error($result)) {
        echo "❌ Erreur WP_Error: " . $result->get_error_message() . "<br>";
        exit;
    }

    if (empty($result)) {
        echo "❌ Résultat vide retourné<br>";
        exit;
    }

    if (!filter_var($result, FILTER_VALIDATE_URL)) {
        echo "❌ URL invalide retournée: " . $result . "<br>";
        exit;
    }

    echo "✅ PDF généré avec succès<br>";
    echo "URL: <a href='$result' target='_blank'>$result</a><br>";

    // Vérifier que le fichier existe
    $upload_dir = wp_upload_dir();
    $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $result);
    if (file_exists($file_path)) {
        echo "✅ Fichier PDF existe sur le serveur<br>";
        echo "Taille: " . filesize($file_path) . " bytes<br>";
    } else {
        echo "❌ Fichier PDF n'existe pas sur le serveur<br>";
    }

} catch (Exception $e) {
    echo "❌ Exception lors de la génération PDF: " . $e->getMessage() . "<br>";
    exit;
} catch (Error $e) {
    echo "❌ Erreur fatale lors de la génération PDF: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>✅ Test terminé avec succès</h2>";
echo "La génération PDF fonctionne correctement. L'erreur 'Erreur inconnue lors de la génération' doit venir d'ailleurs.";