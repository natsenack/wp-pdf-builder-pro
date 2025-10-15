<?php
/**
 * Analyse détaillée du premier objet JSON
 */

$json = '[{\"id\":\"element_2\",\"type\":\"product_table\",\"x\":20,\"y\":230,\"width\":550,\"height\":260,\"backgroundColor\":\"transparent\",\"borderColor\":\"transparent\",\"borderWidth\":0,\"borderStyle\":\"solid\",\"borderRadius\":0,\"color\":\"#1e293b\",\"fontFamily\":\"Inter, sans-serif\",\"fontSize\":14,\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textAlign\":\"left\",\"textDecoration\":\"none\",\"text\":\"Texte\",\"opacity\":100,\"rotation\":0,\"scale\":100,\"visible\":true,\"src\":\"\",\"alt\":\"\",\"objectFit\":\"cover\",\"brightness\":100,\"contrast\":100,\"saturate\":100,\"shadow\":false,\"shadowColor\":\"#000000\",\"shadowOffsetX\":2,\"shadowOffsetY\":2,\"showHeaders\":true,\"showBorders\":false,\"dataSource\":\"order_items\",\"showSubtotal\":false,\"showShipping\":false,\"showTaxes\":false,\"showDiscount\":false,\"showTotal\":true,\"progressColor\":\"#3b82f6\",\"progressValue\":75,\"lineColor\":\"#64748b\",\"lineWidth\":2,\"documentType\":\"invoice\",\"imageUrl\":\"\",\"spacing\":8,\"layout\":\"vertical\",\"alignment\":\"left\",\"fit\":\"contain\",\"headers\":[\"Produit\",\"Qté\",\"Prix\"],\"tableStyle\":\"modern\"},{\"id\":\"element_3\",\"type\":\"customer_info\",\"x\":20,\"y\":70,\"width\":300,\"height\":130,\"backgroundColor\":\"transparent\",\"borderColor\":\"transparent\",\"borderWidth\":0,\"borderStyle\":\"solid\",\"borderRadius\":0,\"color\":\"#1e293b\",\"fontFamily\":\"Inter, sans-serif\",\"fontSize\":14,\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textAlign\":\"left\",\"textDecoration\":\"none\",\"text\":\"Texte\",\"opacity\":100,\"rotation\":0,\"scale\":100,\"visible\":true,\"src\":\"\",\"alt\":\"\",\"objectFit\":\"cover\",\"brightness\":100,\"contrast\":100,\"saturate\":100,\"shadow\":false,\"shadowColor\":\"#000000\",\"shadowOffsetX\":2,\"shadowOffsetY\":2,\"showHeaders\":true,\"showBorders\":false,\"dataSource\":\"order_items\",\"showSubtotal\":false,\"showShipping\":true,\"showTaxes\":true,\"showDiscount\":false,\"showTotal\":false,\"progressColor\":\"#3b82f6\",\"progressValue\":75,\"lineColor\":\"#64748b\",\"lineWidth\":2,\"documentType\":\"invoice\",\"imageUrl\":\"\",\"spacing\":3,\"layout\":\"vertical\",\"alignment\":\"left\",\"fit\":\"contain\",\"fields\":[\"name\",\"email\",\"phone\",\"address\"],\"showLabels\":true,\"labelStyle\":\"bold\"}]';

// Extraire le premier objet
$first_obj_end = strpos($json, '},', 1) + 1;
$first_obj = substr($json, 1, $first_obj_end - 1);

echo "=== ANALYSE DU PREMIER OBJET ===\n\n";
echo "Objet complet:\n$first_obj\n\n";

echo "Longueur: " . strlen($first_obj) . "\n\n";

// Tester l'objet
$decoded = json_decode($first_obj, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ Objet valide\n";
    print_r($decoded);
} else {
    echo "❌ Erreur: " . json_last_error_msg() . "\n\n";

    // Analyser caractère par caractère autour de l'endroit problématique
    echo "Recherche de caractères problématiques:\n";
    for ($i = 0; $i < strlen($first_obj); $i++) {
        $char = $first_obj[$i];
        $ord = ord($char);
        if ($ord < 32 && $ord !== 9 && $ord !== 10 && $ord !== 13) {
            echo "Position $i: Caractère de contrôle ASCII $ord\n";
            echo "Contexte: ..." . substr($first_obj, max(0, $i-10), 21) . "...\n\n";
        }
    }

    // Tester des parties de l'objet
    echo "Test de parties de l'objet:\n";

    // Chercher la partie headers qui semble problématique
    $headers_pos = strpos($first_obj, '"headers"');
    if ($headers_pos !== false) {
        $headers_part = substr($first_obj, $headers_pos, 50);
        echo "Partie headers: $headers_part\n";

        // Tester juste le tableau headers
        $headers_array = '["Produit","Qté","Prix"]';
        $test = json_decode($headers_array, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ Tableau headers valide\n";
        } else {
            echo "❌ Tableau headers invalide: " . json_last_error_msg() . "\n";
        }
    }

    // Vérifier s'il y a des sauts de ligne
    if (strpos($first_obj, "\n") !== false) {
        echo "❌ L'objet contient des sauts de ligne!\n";
        $lines = explode("\n", $first_obj);
        echo "Nombre de lignes: " . count($lines) . "\n";
        foreach ($lines as $line_num => $line) {
            echo "Ligne " . ($line_num + 1) . ": $line\n";
        }
    } else {
        echo "✅ Pas de sauts de ligne dans l'objet\n";
    }
}

echo "\n=== FIN ===\n";
?>