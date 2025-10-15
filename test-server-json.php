<?php
/**
 * Test du JSON problématique récupéré du serveur
 */

// Le JSON complet récupéré du serveur
$json_from_server = '[{\"id\":\"element_2\",\"type\":\"product_table\",\"x\":20,\"y\":230,\"width\":550,\"height\":260,\"backgroundColor\":\"transparent\",\"borderColor\":\"transparent\",\"borderWidth\":0,\"borderStyle\":\"solid\",\"borderRadius\":0,\"color\":\"#1e293b\",\"fontFamily\":\"Inter, sans-serif\",\"fontSize\":14,\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textAlign\":\"left\",\"textDecoration\":\"none\",\"text\":\"Texte\",\"opacity\":100,\"rotation\":0,\"scale\":100,\"visible\":true,\"src\":\"\",\"alt\":\"\",\"objectFit\":\"cover\",\"brightness\":100,\"contrast\":100,\"saturate\":100,\"shadow\":false,\"shadowColor\":\"#000000\",\"shadowOffsetX\":2,\"shadowOffsetY\":2,\"showHeaders\":true,\"showBorders\":false,\"dataSource\":\"order_items\",\"showSubtotal\":false,\"showShipping\":false,\"showTaxes\":false,\"showDiscount\":false,\"showTotal\":true,\"progressColor\":\"#3b82f6\",\"progressValue\":75,\"lineColor\":\"#64748b\",\"lineWidth\":2,\"documentType\":\"invoice\",\"imageUrl\":\"\",\"spacing\":8,\"layout\":\"vertical\",\"alignment\":\"left\",\"fit\":\"contain\",\"headers\":[\"Produit\",\"Qté\",\"Prix\"],\"tableStyle\":\"modern\"},{\"id\":\"element_3\",\"type\":\"customer_info\",\"x\":20,\"y\":70,\"width\":300,\"height\":130,\"backgroundColor\":\"transparent\",\"borderColor\":\"transparent\",\"borderWidth\":0,\"borderStyle\":\"solid\",\"borderRadius\":0,\"color\":\"#1e293b\",\"fontFamily\":\"Inter, sans-serif\",\"fontSize\":14,\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textAlign\":\"left\",\"textDecoration\":\"none\",\"text\":\"Texte\",\"opacity\":100,\"rotation\":0,\"scale\":100,\"visible\":true,\"src\":\"\",\"alt\":\"\",\"objectFit\":\"cover\",\"brightness\":100,\"contrast\":100,\"saturate\":100,\"shadow\":false,\"shadowColor\":\"#000000\",\"shadowOffsetX\":2,\"shadowOffsetY\":2,\"showHeaders\":true,\"showBorders\":false,\"dataSource\":\"order_items\",\"showSubtotal\":false,\"showShipping\":true,\"showTaxes\":true,\"showDiscount\":false,\"showTotal\":false,\"progressColor\":\"#3b82f6\",\"progressValue\":75,\"lineColor\":\"#64748b\",\"lineWidth\":2,\"documentType\":\"invoice\",\"imageUrl\":\"\",\"spacing\":3,\"layout\":\"vertical\",\"alignment\":\"left\",\"fit\":\"contain\",\"fields\":[\"name\",\"email\",\"phone\",\"address\"],\"showLabels\":true,\"labelStyle\":\"bold\"}]';

echo "=== TEST DU JSON PROBLÉMATIQUE ===\n\n";

echo "Longueur du JSON: " . strlen($json_from_server) . "\n";
echo "Premiers 200 chars: " . substr($json_from_server, 0, 200) . "\n";
echo "Derniers 200 chars: " . substr($json_from_server, -200) . "\n";
echo "Commence par '[': " . (substr($json_from_server, 0, 1) === '[' ? 'OUI' : 'NON') . "\n";
echo "Termine par ']': " . (substr($json_from_server, -1) === ']' ? 'OUI' : 'NON') . "\n\n";

// Test de décodage
echo "Test de décodage JSON...\n";
$decoded = json_decode($json_from_server, true);

if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON valide !\n";
    echo "Nombre d'éléments: " . count($decoded) . "\n";
    echo "Premier élément type: " . $decoded[0]['type'] . "\n";
    echo "Deuxième élément type: " . $decoded[1]['type'] . "\n";
} else {
    echo "❌ Erreur JSON: " . json_last_error_msg() . " (code: " . json_last_error() . ")\n";

    // Test avec différents flags
    echo "\nTest avec JSON_BIGINT_AS_STRING...\n";
    $decoded2 = json_decode($json_from_server, true, 512, JSON_BIGINT_AS_STRING);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON valide avec JSON_BIGINT_AS_STRING\n";
    } else {
        echo "❌ Toujours erreur: " . json_last_error_msg() . "\n";
    }

    // Vérifier l'encodage
    echo "\nVérification de l'encodage...\n";
    $encoding = mb_detect_encoding($json_from_server);
    echo "Encodage détecté: " . $encoding . "\n";

    if ($encoding !== 'UTF-8') {
        echo "Conversion en UTF-8...\n";
        $json_utf8 = mb_convert_encoding($json_from_server, 'UTF-8', $encoding);
        $decoded3 = json_decode($json_utf8, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ JSON valide après conversion UTF-8\n";
        } else {
            echo "❌ Toujours erreur après conversion\n";
        }
    }

    // Chercher des caractères problématiques
    echo "\nRecherche de caractères problématiques...\n";
    $problematic_chars = [];
    for ($i = 0; $i < strlen($json_from_server); $i++) {
        $char = $json_from_server[$i];
        $ord = ord($char);
        if ($ord < 32 && $ord !== 9 && $ord !== 10 && $ord !== 13) {
            $problematic_chars[] = "Position $i: ASCII $ord (char: " . ($ord >= 32 ? $char : "\\x" . dechex($ord)) . ")";
        }
    }

    if (!empty($problematic_chars)) {
        echo "Caractères problématiques trouvés:\n";
        foreach ($problematic_chars as $char_info) {
            echo "  - $char_info\n";
        }
    } else {
        echo "Aucun caractère de contrôle trouvé\n";
    }
}

echo "\n=== FIN DU TEST ===\n";
?>