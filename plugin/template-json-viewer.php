<?php
/**
 * Script pour afficher le JSON du template ID 1
 * À placer dans le répertoire du plugin WordPress
 */

// Charger WordPress si nécessaire
if (!defined('ABSPATH')) {
    echo '<h2>Débogage du chargement WordPress:</h2>';

    // Essayer plusieurs chemins possibles
    $possible_paths = [
        dirname(__FILE__, 3) . '/wp-load.php', // wp-content/wp-load.php
        dirname(__FILE__, 4) . '/wp-load.php', // racine/wp-load.php
        dirname(__FILE__, 5) . '/wp-load.php', // au cas où
        $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php', // depuis document root
        realpath(__DIR__ . '/../../../wp-load.php'), // relatif
        realpath(__DIR__ . '/../../../../wp-load.php'), // relatif +1
    ];

    echo '<ul>';
    foreach ($possible_paths as $index => $path) {
        $exists = file_exists($path) ? 'EXISTS' : 'NOT FOUND';
        echo '<li>Path ' . ($index + 1) . ': ' . htmlspecialchars($path) . ' - <strong>' . $exists . '</strong></li>';
    }
    echo '</ul>';

    echo '<p>Current dir: ' . __DIR__ . '</p>';
    echo '<p>Document root: ' . $_SERVER['DOCUMENT_ROOT'] . '</p>';

    // Essayer de charger depuis le document root
    $wp_load_path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

    if (file_exists($wp_load_path)) {
        echo '<p style="color: green;">✅ Trouvé wp-load.php à: ' . htmlspecialchars($wp_load_path) . '</p>';
        require_once($wp_load_path);
        echo '<p style="color: green;">✅ WordPress chargé avec succès!</p>';
    } else {
        echo '<p style="color: red;">❌ Aucun wp-load.php trouvé aux emplacements testés.</p>';
        echo '<p>Essayez de définir manuellement le chemin correct dans le script.</p>';
        exit;
    }
}

// Vérifier les permissions (admin seulement)
if (!current_user_can('manage_options')) {
    wp_die('Accès refusé - Vous devez être administrateur.');
}

echo '<h1>Template ID 1 - Données JSON</h1>';
echo '<style>pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }</style>';

// Debug: Afficher les informations de base
echo '<h2>Debug Information:</h2>';
echo '<ul>';
echo '<li>Current User ID: ' . get_current_user_id() . '</li>';
echo '<li>Is Admin: ' . (current_user_can('manage_options') ? 'Yes' : 'No') . '</li>';
echo '<li>ABSPATH: ' . ABSPATH . '</li>';
echo '<li>Plugin File: ' . __FILE__ . '</li>';
echo '</ul>';

// Récupérer le nonce
$nonce = wp_create_nonce('pdf_builder_nonce');

echo '<h2>Récupération des données...</h2>';

// Debug du nonce
echo '<h3>Debug du nonce:</h3>';
echo '<ul>';
echo '<li>Nonce généré: <code>' . $nonce . '</code></li>';
echo '<li>Action du nonce: <code>pdf_builder_nonce</code></li>';
echo '<li>Vérification locale: ' . (wp_verify_nonce($nonce, 'pdf_builder_nonce') ? '✅ VALIDE' : '❌ INVALIDE') . '</li>';
echo '</ul>';

// URL de l'API
$url = admin_url('admin-ajax.php?action=pdf_builder_get_template&template_id=1&nonce=' . $nonce);
echo '<p>URL appelée: <code>' . htmlspecialchars($url) . '</code></p>';

echo '<h3>Réponse brute de l\'API:</h3>';

// Au lieu de faire un appel HTTP externe, appelons directement la fonction WordPress
// Cela évite les problèmes de session/cookies
echo '<h3>🔧 Méthode alternative : Appel direct de la fonction WordPress</h3>';

// Simuler les paramètres GET comme si c'était une requête AJAX
$_GET['nonce'] = $nonce;
$_GET['template_id'] = '1';

// Démarrer la bufferisation de sortie pour capturer la réponse JSON
ob_start();

// Appeler directement la fonction AJAX
try {
    pdf_builder_ajax_get_template();
} catch (Exception $e) {
    echo '<p style="color: red;">Erreur lors de l\'appel de la fonction: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Récupérer la sortie bufferisée
$response = ob_get_clean();

echo '<h4>Réponse de la fonction WordPress:</h4>';
echo '<pre>' . htmlspecialchars($response) . '</pre>';

// Essayer de parser la réponse JSON
$data = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo '<h4>Données parsées:</h4>';
    if ($data['success']) {
        echo '<p style="color: green;">✅ Template chargé avec succès !</p>';

        // Afficher les informations générales
        echo '<h3>Informations générales :</h3>';
        echo '<ul>';
        echo '<li><strong>ID :</strong> ' . htmlspecialchars($data['data']['id']) . '</li>';
        echo '<li><strong>Nom :</strong> ' . htmlspecialchars($data['data']['name']) . '</li>';
        echo '<li><strong>Créé le :</strong> ' . htmlspecialchars($data['data']['created_at']) . '</li>';
        echo '<li><strong>Modifié le :</strong> ' . htmlspecialchars($data['data']['updated_at']) . '</li>';
        echo '<li><strong>Nombre d\'éléments :</strong> ' . count($data['data']['elements']) . '</li>';
        echo '</ul>';

        // Afficher le JSON complet
        echo '<h3>JSON complet du template :</h3>';
        echo '<pre>' . json_encode($data['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';

    } else {
        echo '<p style="color: red;">❌ Erreur: ' . htmlspecialchars($data['data']) . '</p>';
    }
} else {
    echo '<p style="color: orange;">⚠️ Réponse non-JSON reçue</p>';
}
?>