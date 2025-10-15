<?php
/**
 * Analyse progressive du JSON pour trouver l'erreur exacte
 */

$json = '[{\"id\":\"element_2\",\"type\":\"product_table\",\"x\":20,\"y\":230,\"width\":550,\"height\":260,\"backgroundColor\":\"transparent\",\"borderColor\":\"transparent\",\"borderWidth\":0,\"borderStyle\":\"solid\",\"borderRadius\":0,\"color\":\"#1e293b\",\"fontFamily\":\"Inter, sans-serif\",\"fontSize\":14,\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textAlign\":\"left\",\"textDecoration\":\"none\",\"text\":\"Texte\",\"opacity\":100,\"rotation\":0,\"scale\":100,\"visible\":true,\"src\":\"\",\"alt\":\"\",\"objectFit\":\"cover\",\"brightness\":100,\"contrast\":100,\"saturate\":100,\"shadow\":false,\"shadowColor\":\"#000000\",\"shadowOffsetX\":2,\"shadowOffsetY\":2,\"showHeaders\":true,\"showBorders\":false,\"dataSource\":\"order_items\",\"showSubtotal\":false,\"showShipping\":false,\"showTaxes\":false,\"showDiscount\":false,\"showTotal\":true,\"progressColor\":\"#3b82f6\",\"progressValue\":75,\"lineColor\":\"#64748b\",\"lineWidth\":2,\"documentType\":\"invoice\",\"imageUrl\":\"\",\"spacing\":8,\"layout\":\"vertical\",\"alignment\":\"left\",\"fit\":\"contain\",\"headers\":[\"Produit\",\"Qté\",\"Prix\"],\"tableStyle\":\"modern\"},{\"id\":\"element_3\",\"type\":\"customer_info\",\"x\":20,\"y\":70,\"width\":300,\"height\":130,\"backgroundColor\":\"transparent\",\"borderColor\":\"transparent\",\"borderWidth\":0,\"borderStyle\":\"solid\",\"borderRadius\":0,\"color\":\"#1e293b\",\"fontFamily\":\"Inter, sans-serif\",\"fontSize\":14,\"fontWeight\":\"normal\",\"fontStyle\":\"normal\",\"textAlign\":\"left\",\"textDecoration\":\"none\",\"text\":\"Texte\",\"opacity\":100,\"rotation\":0,\"scale\":100,\"visible\":true,\"src\":\"\",\"alt\":\"\",\"objectFit\":\"cover\",\"brightness\":100,\"contrast\":100,\"saturate\":100,\"shadow\":false,\"shadowColor\":\"#000000\",\"shadowOffsetX\":2,\"shadowOffsetY\":2,\"showHeaders\":true,\"showBorders\":false,\"dataSource\":\"order_items\",\"showSubtotal\":false,\"showShipping\":true,\"showTaxes\":true,\"showDiscount\":false,\"showTotal\":false,\"progressColor\":\"#3b82f6\",\"progressValue\":75,\"lineColor\":\"#64748b\",\"lineWidth\":2,\"documentType\":\"invoice\",\"imageUrl\":\"\",\"spacing\":3,\"layout\":\"vertical\",\"alignment\":\"left\",\"fit\":\"contain\",\"fields\":[\"name\",\"email\",\"phone\",\"address\"],\"showLabels\":true,\"labelStyle\":\"bold\"}]';

echo "=== ANALYSE PROGRESSIVE ===\n\n";

// Tester des longueurs croissantes
$lengths = [100, 200, 500, 1000, 1500, 2000, 2323];

foreach ($lengths as $len) {
    $partial = substr($json, 0, $len);
    $decoded = json_decode($partial, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ Longueur $len: valide\n";
    } else {
        echo "❌ Longueur $len: " . json_last_error_msg() . "\n";

        // Analyser les derniers caractères
        $last_chars = substr($partial, -50);
        echo "  Derniers 50 chars: ...$last_chars\n";

        // Chercher le dernier guillemet ouvrant/fermant valide
        $open_braces = substr_count($partial, '{');
        $close_braces = substr_count($partial, '}');
        $open_brackets = substr_count($partial, '[');
        $close_brackets = substr_count($partial, ']');

        echo "  Structure: { $open_braces ouverts, $close_braces fermés | [ $open_brackets ouverts, $close_brackets fermés\n";

        break; // Stop at first error
    }
}

echo "\n=== ANALYSE PAR OBJETS ===\n\n";

// Extraire et tester chaque objet séparément
$objects = [];
$depth = 0;
$current_obj = '';
$in_string = false;
$escape_next = false;

for ($i = 1; $i < strlen($json) - 1; $i++) { // Skip [ and ]
    $char = $json[$i];

    if ($escape_next) {
        $escape_next = false;
        $current_obj .= $char;
        continue;
    }

    if ($char === '\\') {
        $escape_next = true;
        $current_obj .= $char;
        continue;
    }

    if ($char === '"') {
        $in_string = !$in_string;
    }

    if (!$in_string) {
        if ($char === '{') {
            $depth++;
        } elseif ($char === '}') {
            $depth--;
            if ($depth === 0) {
                $objects[] = $current_obj . '}';
                $current_obj = '';
                continue;
            }
        } elseif ($char === ',' && $depth === 0) {
            continue; // Skip comma between objects
        }
    }

    if ($depth > 0) {
        $current_obj .= $char;
    }
}

echo "Nombre d'objets trouvés: " . count($objects) . "\n\n";

foreach ($objects as $index => $obj) {
    echo "Test objet " . ($index + 1) . ":\n";
    echo "Longueur: " . strlen($obj) . "\n";

    $decoded = json_decode($obj, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ Valide - Type: " . ($decoded['type'] ?? 'unknown') . "\n";
    } else {
        echo "❌ Erreur: " . json_last_error_msg() . "\n";
        echo "Début: " . substr($obj, 0, 100) . "\n";
        echo "Fin: " . substr($obj, -100) . "\n";
    }
    echo "\n";
}

echo "=== FIN ===\n";
?>