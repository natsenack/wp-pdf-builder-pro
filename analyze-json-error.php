<?php
/**
 * Analyse détaillée du JSON problématique
 */

// Le JSON complet récupéré du serveur
$json_from_server = '[{\"id\":\"element_2\",\"type\":\"product_table\",\"x\":20,\"y\":230,\"width\":550,\"height\":260,\"backgroundColor\":\"transparent\",\"borderColor\":\"transparent\",\"borderWidth\":0,\"borderStyle\":\"solid\",\"borderRadius\":0,\"color\":\"#1e293b\",\"fontFamily\":\"Inter, sans-serif\",\"fontSize\":14,\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textAlign\":\"left\",\"textDecoration\":\"none\",\"text\":\"Texte\",\"opacity\":100,\"rotation\":0,\"scale\":100,\"visible\":true,\"src\":\"\",\"alt\":\"\",\"objectFit\":\"cover\",\"brightness\":100,\"contrast\":100,\"saturate\":100,\"shadow\":false,\"shadowColor\":\"#000000\",\"shadowOffsetX\":2,\"shadowOffsetY\":2,\"showHeaders\":true,\"showBorders\":false,\"dataSource\":\"order_items\",\"showSubtotal\":false,\"showShipping\":false,\"showTaxes\":false,\"showDiscount\":false,\"showTotal\":true,\"progressColor\":\"#3b82f6\",\"progressValue\":75,\"lineColor\":\"#64748b\",\"lineWidth\":2,\"documentType\":\"invoice\",\"imageUrl\":\"\",\"spacing\":8,\"layout\":\"vertical\",\"alignment\":\"left\",\"fit\":\"contain\",\"headers\":[\"Produit\",\"Qté\",\"Prix\"],\"tableStyle\":\"modern\"},{\"id\":\"element_3\",\"type\":\"customer_info\",\"x\":20,\"y\":70,\"width\":300,\"height\":130,\"backgroundColor\":\"transparent\",\"borderColor\":\"transparent\",\"borderWidth\":0,\"borderStyle\":\"solid\",\"borderRadius\":0,\"color\":\"#1e293b\",\"fontFamily\":\"Inter, sans-serif\",\"fontSize\":14,\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textAlign\":\"left\",\"textDecoration\":\"none\",\"text\":\"Texte\",\"opacity\":100,\"rotation\":0,\"scale\":100,\"visible\":true,\"src\":\"\",\"alt\":\"\",\"objectFit\":\"cover\",\"brightness\":100,\"contrast\":100,\"saturate\":100,\"shadow\":false,\"shadowColor\":\"#000000\",\"shadowOffsetX\":2,\"shadowOffsetY\":2,\"showHeaders\":true,\"showBorders\":false,\"dataSource\":\"order_items\",\"showSubtotal\":false,\"showShipping\":true,\"showTaxes\":true,\"showDiscount\":false,\"showTotal\":false,\"progressColor\":\"#3b82f6\",\"progressValue\":75,\"lineColor\":\"#64748b\",\"lineWidth\":2,\"documentType\":\"invoice\",\"imageUrl\":\"\",\"spacing\":3,\"layout\":\"vertical\",\"alignment\":\"left\",\"fit\":\"contain\",\"fields\":[\"name\",\"email\",\"phone\",\"address\"],\"showLabels\":true,\"labelStyle\":\"bold\"}]';

echo "=== ANALYSE DÉTAILLÉE DU JSON ===\n\n";

echo "Longueur totale: " . strlen($json_from_server) . "\n\n";

// Test de décodage
$decoded = json_decode($json_from_server, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON valide\n";
    exit;
}

echo "❌ Erreur de syntaxe détectée\n";
echo "Erreur: " . json_last_error_msg() . "\n\n";

// Analyser caractère par caractère autour de l'endroit où l'erreur pourrait être
echo "Analyse des caractères autour de la position 1000-1100:\n";
$start = 1000;
$end = min(1100, strlen($json_from_server));
for ($i = $start; $i < $end; $i++) {
    $char = $json_from_server[$i];
    $ord = ord($char);
    if ($ord < 32 || $ord > 126) {
        echo "Position $i: Caractère spécial - ASCII $ord\n";
        echo "Contexte: ..." . substr($json_from_server, $i-10, 21) . "...\n";
    }
}

// Chercher les guillemets non échappés
echo "\nRecherche de guillemets problématiques:\n";
$in_string = false;
$escape_next = false;

for ($i = 0; $i < strlen($json_from_server); $i++) {
    $char = $json_from_server[$i];

    if ($escape_next) {
        $escape_next = false;
        continue;
    }

    if ($char === '\\') {
        $escape_next = true;
        continue;
    }

    if ($char === '"') {
        $in_string = !$in_string;
    }

    if (!$in_string && $char === '"') {
        echo "Guillemet hors chaîne à position $i\n";
        echo "Contexte: ..." . substr($json_from_server, $i-20, 41) . "...\n";
    }
}

// Tester des portions du JSON
echo "\nTest de portions du JSON:\n";

$parts = [
    "headers part" => '{\"headers\":[\"Produit\",\"Qté\",\"Prix\"],\"tableStyle\":\"modern\"}',
    "fields part" => '{\"fields\":[\"name\",\"email\",\"phone\",\"address\"],\"showLabels\":true,\"labelStyle\":\"bold\"}',
    "spacing part" => '\"spacing\":8',
    "Qté test" => '["Produit","Qté","Prix"]'
];

foreach ($parts as $name => $part) {
    $test = json_decode($part, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ $name: valide\n";
    } else {
        echo "❌ $name: " . json_last_error_msg() . "\n";
    }
}

echo "\n=== FIN DE L'ANALYSE ===\n";
?>