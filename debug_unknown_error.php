<?php
/**
 * Script de diagnostic pour l'erreur "Erreur inconnue lors de la génération"
 */

// Fonction pour définir les constantes TCPDF
function define_tcpdf_constants_if_needed() {
    $constants = [
        'K_PATH_FONTS' => plugin_dir_path(__FILE__) . 'lib/tcpdf/fonts/',
        'K_PATH_CACHE' => plugin_dir_path(__FILE__) . 'cache/',
        'K_PATH_URL_CACHE' => plugin_dir_url(__FILE__) . 'cache/',
        'K_PATH_IMAGES' => plugin_dir_path(__FILE__) . 'lib/tcpdf/images/',
        'K_BLANK_IMAGE' => plugin_dir_path(__FILE__) . 'lib/tcpdf/images/blank.png',
        'PDF_PAGE_FORMAT' => 'A4',
        'PDF_PAGE_ORIENTATION' => 'P',
        'PDF_CREATOR' => 'PDF Builder Pro',
        'PDF_AUTHOR' => 'PDF Builder Pro',
        'PDF_HEADER_TITLE' => 'PDF Builder Pro',
        'PDF_HEADER_STRING' => "par PDF Builder Pro\nwww.pdfbuilderpro.com",
        'PDF_UNIT' => 'mm',
        'PDF_MARGIN_HEADER' => 5,
        'PDF_MARGIN_FOOTER' => 10,
        'PDF_MARGIN_TOP' => 27,
        'PDF_MARGIN_BOTTOM' => 25,
        'PDF_MARGIN_LEFT' => 15,
        'PDF_MARGIN_RIGHT' => 15,
        'PDF_FONT_SIZE_MAIN' => 10,
        'PDF_FONT_SIZE_DATA' => 8,
        'PDF_FONT_MONOSPACED' => 'courier',
        'PDF_IMAGE_SCALE_RATIO' => 1.25
    ];

    foreach ($constants as $name => $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}

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

    // Déterminer le répertoire du plugin
    $plugin_dir = plugin_dir_path(__FILE__);
    echo "📁 Répertoire du plugin: " . $plugin_dir . "<br>";

    // Utiliser la même logique que PDF_Builder_Admin::load_tcpdf_library()
    $tcpdf_paths = [
        $plugin_dir . 'lib/tcpdf/tcpdf_autoload.php', // Essayer d'abord l'autoload
        $plugin_dir . 'lib/tcpdf/tcpdf.php',
        $plugin_dir . 'vendor/tecnickcom/tcpdf/tcpdf.php'
    ];

    $tcpdf_loaded = false;
    foreach ($tcpdf_paths as $path) {
        echo "🔍 Vérification du chemin: " . $path . "<br>";

        if (file_exists($path)) {
            echo "✅ Fichier existe: " . basename($path) . "<br>";

            // Vérifier les permissions
            if (is_readable($path)) {
                echo "✅ Fichier lisible: " . basename($path) . "<br>";

                // Pour tcpdf.php, essayer de précharger les dépendances
                if (basename($path) === 'tcpdf.php') {
                    echo "🔧 Tentative de préchargement des dépendances TCPDF...<br>";
                    $deps = [
                        $plugin_dir . 'lib/tcpdf/tcpdf_autoconfig.php',
                        $plugin_dir . 'lib/tcpdf/tcpdf_static.php',
                        $plugin_dir . 'lib/tcpdf/tcpdf_colors.php',
                        $plugin_dir . 'lib/tcpdf/tcpdf_fonts.php'
                    ];

                    foreach ($deps as $dep) {
                        if (file_exists($dep) && is_readable($dep)) {
                            require_once $dep;
                            echo "✅ Dépendance chargée: " . basename($dep) . "<br>";
                        }
                    }
                }

                $result = require_once $path;
                echo "📦 Résultat require_once: " . ($result ? 'true' : 'false') . "<br>";

                if (class_exists('TCPDF')) {
                    $tcpdf_loaded = true;
                    echo "✅ TCPDF chargé depuis: " . basename($path) . "<br>";

                    // Définir les constantes TCPDF
                    define_tcpdf_constants_if_needed();
                    break;
                } else {
                    echo "❌ Classe TCPDF non trouvée après chargement de " . basename($path) . "<br>";
                }
            } else {
                echo "❌ Fichier non lisible: " . basename($path) . "<br>";
            }
        } else {
            echo "❌ Fichier n'existe pas: " . basename($path) . "<br>";
        }
    }

    if (!$tcpdf_loaded) {
        echo "❌ Impossible de charger TCPDF<br>";
        echo "<h3>🔍 Informations de débogage:</h3>";
        echo "<ul>";
        echo "<li>Plugin dir: " . $plugin_dir . "</li>";
        echo "<li>PHP version: " . phpversion() . "</li>";
        echo "<li>Include path: " . get_include_path() . "</li>";
        echo "<li>Current user: " . get_current_user() . "</li>";
        echo "<li>Script owner: " . (function_exists('posix_getuid') ? posix_getuid() : 'N/A') . "</li>";
        echo "<li>open_basedir: " . ini_get('open_basedir') . "</li>";
        echo "<li>safe_mode: " . (ini_get('safe_mode') ? 'On' : 'Off') . "</li>";
        echo "</ul>";

        // Essayer une approche alternative : charger via eval si possible
        echo "<h3>🔧 Tentative de chargement alternatif...</h3>";
        $tcpdf_content = @file_get_contents($plugin_dir . 'lib/tcpdf/tcpdf.php');
        if ($tcpdf_content !== false) {
            echo "✅ Contenu TCPDF lu, tentative d'évaluation...<br>";
            // Note: eval peut être dangereux, mais c'est pour le diagnostic
            @eval('?>' . $tcpdf_content);
            if (class_exists('TCPDF')) {
                echo "✅ TCPDF chargé via eval()<br>";
                $tcpdf_loaded = true;
                define_tcpdf_constants_if_needed();
            } else {
                echo "❌ Échec du chargement via eval()<br>";
            }
        } else {
            echo "❌ Impossible de lire le contenu du fichier TCPDF<br>";
        }

        if (!$tcpdf_loaded) {
            exit;
        }
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

// Instancier les classes nécessaires
$core = PDF_Builder_Core::getInstance();
$admin = PDF_Builder_Admin::getInstance($core);

if ($template_id > 0) {
    // Utiliser la méthode publique load_template_robust
    $template_data = $admin->load_template_robust($template_id);
    echo "✅ Template chargé depuis database: $template_id<br>";
} else {
    // Pour le template par défaut, utiliser une approche alternative
    echo "🔍 Recherche du template par défaut...<br>";

    // Chercher dans la base de données un template marqué comme défaut
    global $wpdb;
    $table_name = $wpdb->prefix . 'pdf_builder_templates';

    // Essayer de trouver un template par défaut
    $default_template = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE is_default = 1 LIMIT 1"
        )
    );

    if ($default_template) {
        $template_data = json_decode($default_template->template_data, true);
        echo "✅ Template par défaut trouvé en base: ID {$default_template->id}<br>";
    } else {
        // Créer un template minimal par défaut
        $template_data = [
            'pages' => [
                [
                    'elements' => [
                        [
                            'type' => 'text',
                            'content' => 'Template de test - PDF Builder Pro',
                            'position' => ['x' => 50, 'y' => 100],
                            'size' => ['width' => 400, 'height' => 50],
                            'style' => [
                                'fontSize' => 16,
                                'fontWeight' => 'bold'
                            ]
                        ]
                    ],
                    'margins' => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20]
                ]
            ]
        ];
        echo "✅ Template minimal créé pour les tests<br>";
    }
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