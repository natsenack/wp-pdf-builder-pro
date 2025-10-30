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
echo '<p>Nonce généré: ' . $nonce . '</p>';

echo '<h2>Récupération des données...</h2>';

// Faire l'appel AJAX
$url = admin_url('admin-ajax.php?action=pdf_builder_get_template&template_id=1&nonce=' . $nonce);

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json',
        'timeout' => 30,
    ]
]);

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo '<p style="color: red;">Erreur : Impossible de récupérer les données du template.</p>';
    echo '<p>Vérifiez que le plugin est activé et que l\'action AJAX fonctionne.</p>';
    exit;
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo '<p style="color: red;">Erreur : Réponse JSON invalide.</p>';
    echo '<pre>' . htmlspecialchars($response) . '</pre>';
    exit;
}

if (!$data['success']) {
    echo '<p style="color: red;">Erreur API : ' . htmlspecialchars($data['data']) . '</p>';
    exit;
}

echo '<h2 style="color: green;">✅ Template chargé avec succès</h2>';

echo '<h3>Informations générales :</h3>';
echo '<ul>';
echo '<li><strong>ID :</strong> ' . htmlspecialchars($data['data']['id']) . '</li>';
echo '<li><strong>Nom :</strong> ' . htmlspecialchars($data['data']['name']) . '</li>';
echo '<li><strong>Créé le :</strong> ' . htmlspecialchars($data['data']['created_at']) . '</li>';
echo '<li><strong>Modifié le :</strong> ' . htmlspecialchars($data['data']['updated_at']) . '</li>';
echo '<li><strong>Nombre d\'éléments :</strong> ' . count($data['data']['elements']) . '</li>';
echo '</ul>';

echo '<h3>Configuration Canvas :</h3>';
echo '<pre>' . json_encode($data['data']['canvas'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';

echo '<h3>Éléments (aperçu) :</h3>';
foreach ($data['data']['elements'] as $index => $element) {
    echo '<h4>Élément ' . ($index + 1) . ' : ' . htmlspecialchars($element['type']) . '</h4>';
    echo '<ul>';
    echo '<li><strong>ID :</strong> ' . htmlspecialchars($element['id']) . '</li>';
    echo '<li><strong>Position :</strong> x=' . $element['x'] . ', y=' . $element['y'] . '</li>';
    echo '<li><strong>Taille :</strong> ' . $element['width'] . ' x ' . $element['height'] . '</li>';
    if (isset($element['text'])) {
        echo '<li><strong>Texte :</strong> ' . htmlspecialchars(substr($element['text'], 0, 100)) . (strlen($element['text']) > 100 ? '...' : '') . '</li>';
    }
    echo '</ul>';
}

echo '<h3>JSON complet du template :</h3>';
echo '<pre>' . json_encode($data['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';

echo '<hr>';
echo '<p><a href="' . admin_url('admin.php?page=pdf-builder-react-editor&template_id=1') . '">Retour à l\'éditeur</a></p>';
?>