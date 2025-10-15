<?php
/**
 * Debug script pour analyser les données JSON reçues
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

function debug_pdf_builder_json_validation() {
    // Simuler la réception des données comme dans la vraie fonction
    if (!isset($_POST['elements'])) {
        echo "<h2>❌ Aucune donnée 'elements' reçue</h2>";
        echo "<pre>POST data: " . print_r($_POST, true) . "</pre>";
        return;
    }

    $json_data = $_POST['elements'];
    echo "<h2>🔍 Analyse des données JSON reçues</h2>";

    echo "<h3>1. Données brutes reçues:</h3>";
    echo "<div style='background: #f5f5f5; padding: 10px; margin: 10px 0; border-left: 4px solid #007cba;'>";
    echo "<strong>Longueur:</strong> " . strlen($json_data) . " caractères<br>";
    echo "<strong>Premiers 500 caractères:</strong><br>";
    echo "<pre>" . htmlspecialchars(substr($json_data, 0, 500)) . "</pre>";
    echo "</div>";

    echo "<h3>2. Vérification URL-encoding:</h3>";
    if (strpos($json_data, '%') !== false) {
        echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
        echo "⚠️ Données semblent URL-encodées (contiennent '%')<br>";
        echo "<strong>Avant décodage:</strong> " . htmlspecialchars(substr($json_data, 0, 100)) . "<br>";
        $decoded = urldecode($json_data);
        echo "<strong>Après décodage:</strong> " . htmlspecialchars(substr($decoded, 0, 100)) . "<br>";
        echo "<strong>Longueur après décodage:</strong> " . strlen($decoded) . "<br>";
        $json_data = $decoded;
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>";
        echo "✅ Données ne semblent pas URL-encodées";
        echo "</div>";
    }

    echo "<h3>3. Test de décodage JSON:</h3>";

    // Test avec json_decode
    $elements = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
        echo "❌ Erreur JSON: " . json_last_error_msg() . "<br>";
        echo "<strong>Code d'erreur:</strong> " . json_last_error() . "<br>";
        echo "<strong>Position approximative de l'erreur:</strong><br>";

        // Trouver la position de l'erreur
        $error_pos = strpos($json_data, '}', 0);
        if ($error_pos !== false) {
            $context_start = max(0, $error_pos - 50);
            $context_end = min(strlen($json_data), $error_pos + 50);
            echo "<pre>" . htmlspecialchars(substr($json_data, $context_start, $context_end - $context_start)) . "</pre>";
            echo "<strong>Position:</strong> " . $error_pos . " (caractère '}' trouvé)<br>";
        }

        echo "<br><strong>Données JSON problématiques (tronquées):</strong><br>";
        echo "<pre>" . htmlspecialchars(substr($json_data, 0, 1000)) . "...</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>";
        echo "✅ JSON décodé avec succès<br>";
        echo "<strong>Nombre d'éléments:</strong> " . count($elements) . "<br>";
        echo "<strong>Premier élément:</strong><br>";
        echo "<pre>" . print_r($elements[0], true) . "</pre>";
        echo "</div>";
    }

    echo "<h3>4. Analyse détaillée des caractères problématiques:</h3>";
    $problematic_chars = [];
    for ($i = 0; $i < strlen($json_data); $i++) {
        $char = $json_data[$i];
        $ord = ord($char);
        if ($ord < 32 && $ord != 9 && $ord != 10 && $ord != 13) {
            $problematic_chars[] = "Position $i: Caractère ASCII $ord (0x" . dechex($ord) . ")";
        }
    }

    if (empty($problematic_chars)) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;'>";
        echo "✅ Aucun caractère de contrôle problématique trouvé";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
        echo "❌ Caractères problématiques trouvés:<br>";
        foreach ($problematic_chars as $char_info) {
            echo "- $char_info<br>";
        }
        echo "</div>";
    }
}

// Appeler la fonction de debug si on reçoit des données POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_pdf_builder_json_validation();
} else {
    echo "<h1>🧪 Debug JSON Validation - PDF Builder Pro</h1>";
    echo "<p>Envoyez des données POST avec le champ 'elements' pour analyser le JSON.</p>";
}
?>