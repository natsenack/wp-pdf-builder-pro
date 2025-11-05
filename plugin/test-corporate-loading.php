<?php
// Simuler exactement la validation du vrai code
function plugin_dir_path($file) {
    return dirname($file) . '/';
}

define('PDF_BUILDER_PLUGIN_DIR', dirname(__FILE__) . '/');

// Fonction de validation d'élément (simplifiée)
function validate_template_element($element, $index) {
    $errors = [];

    // Vérification que c'est un objet
    if (!is_array($element)) {
        $errors[] = "Élément $index: doit être un objet JSON (reçu: " . gettype($element) . ')';
        return $errors;
    }

    // Propriétés obligatoires pour chaque élément
    if (!isset($element['id'])) {
        $errors[] = "Élément $index: propriété 'id' manquante";
    }

    if (!isset($element['type'])) {
        $errors[] = "Élément $index: propriété 'type' manquante";
    }

    // Si les propriétés obligatoires manquent, arrêter ici
    if (count($errors) > 0) {
        return $errors;
    }

    $element_id = $element['id'];
    $element_type = $element['type'];

    // Vérifier le format de l'ID
    if (!is_string($element_id) || empty($element_id)) {
        $errors[] = "Élément $index: id doit être une chaîne non-vide (reçu: '$element_id')";
    }

    // Vérifier le type d'élément valide
    $valid_types = ['text', 'image', 'rectangle', 'line', 'product_table',
                   'customer_info', 'company_logo', 'company_info', 'order_number',
                   'document_type', 'textarea', 'html', 'divider', 'progress-bar',
                   'dynamic-text', 'mentions'];

    if (!in_array($element_type, $valid_types)) {
        $errors[] = "Élément $index ($element_id): type invalide '$element_type' (types valides: " .
                   implode(', ', $valid_types) . ')';
    }

    // Vérifier les propriétés numériques
    $numeric_props = ['x', 'y', 'width', 'height', 'fontSize', 'opacity', 'zIndex',
                     'borderWidth', 'borderRadius', 'padding', 'margin', 'rotation'];

    foreach ($numeric_props as $prop) {
        if (isset($element[$prop])) {
            if (!is_numeric($element[$prop])) {
                $errors[] = "Élément $index ($element_id): '$prop' doit être numérique (reçu: " .
                           gettype($element[$prop]) . ')';
            }
        }
    }

    // Vérifier que x, y, width, height sont présents et raisonnables
    $required_position_props = ['x', 'y', 'width', 'height'];
    foreach ($required_position_props as $prop) {
        if (!isset($element[$prop])) {
            $errors[] = "Élément $index ($element_id): propriété '$prop' obligatoire manquante";
        } else {
            $value = (float) $element[$prop];
            if ($value < 0 || $value > 3000) {
                $errors[] = "Élément $index ($element_id): '$prop' doit être entre 0 et 3000 (reçu: $value)";
            }
        }
    }

    return $errors;
}

// Fonction de validation de structure (simplifiée)
function validate_template_structure($template_data) {
    $errors = [];

    // Vérification 1 : Type et structure de base
    if (!is_array($template_data)) {
        $errors[] = 'Les données doivent être un objet JSON (array PHP)';
        return $errors;
    }

    // Vérification 2 : Propriétés obligatoires
    $required_keys = ['elements', 'canvasWidth', 'canvasHeight', 'version'];
    foreach ($required_keys as $key) {
        if (!isset($template_data[$key])) {
            $errors[] = "Propriété obligatoire manquante: '$key'";
        }
    }

    if (!empty($errors)) {
        return $errors;
    }

    // Vérification 3 : Types des propriétés principales
    if (!is_array($template_data['elements'])) {
        $errors[] = "'elements' doit être un tableau d'objets";
    }

    if (!is_numeric($template_data['canvasWidth'])) {
        $errors[] = "'canvasWidth' doit être un nombre";
    }

    if (!is_numeric($template_data['canvasHeight'])) {
        $errors[] = "'canvasHeight' doit être un nombre";
    }

    if (!is_string($template_data['version'])) {
        $errors[] = "'version' doit être une chaîne de caractères";
    }

    if (!empty($errors)) {
        return $errors;
    }

    // Vérification 4 : Valeurs numériques raisonnables
    $width = (float) $template_data['canvasWidth'];
    $height = (float) $template_data['canvasHeight'];

    if ($width < 50 || $width > 2000) {
        $errors[] = "canvasWidth doit être entre 50 et 2000 (reçu: $width)";
    }

    if ($height < 50 || $height > 2000) {
        $errors[] = "canvasHeight doit être entre 50 et 2000 (reçu: $height)";
    }

    // Vérification 5 : Nombre d'éléments raisonnable
    $element_count = count($template_data['elements']);
    if ($element_count > 1000) {
        $errors[] = "Nombre d'éléments trop élevé: $element_count (max: 1000)";
    }

    // Vérification 6 : Validation de chaque élément
    foreach ($template_data['elements'] as $index => $element) {
        $element_errors = validate_template_element($element, $index);
        $errors = array_merge($errors, $element_errors);

        // Limiter à 10 erreurs
        if (count($errors) >= 10) {
            $errors[] = '... et plus d\'erreurs détectées';
            break;
        }
    }

    return $errors;
}

echo "=== Test de validation complète du template corporate ===\n";

$corporate_file = PDF_BUILDER_PLUGIN_DIR . 'templates/builtin/corporate.json';
$json = file_get_contents($corporate_file);
$data = json_decode($json, true);

echo "Fichier chargé: " . (file_exists($corporate_file) ? 'OUI' : 'NON') . "\n";
echo "JSON décodé: " . ($data !== null ? 'OUI' : 'NON') . "\n";

if ($data) {
    $validation_errors = validate_template_structure($data);

    if (empty($validation_errors)) {
        echo "✅ Template corporate valide!\n";
        echo "Name: " . ($data['name'] ?? 'N/A') . "\n";
        echo "Elements: " . count($data['elements']) . "\n";
        echo "Canvas: " . $data['canvasWidth'] . "x" . $data['canvasHeight'] . "\n";
    } else {
        echo "❌ Erreurs de validation:\n";
        foreach ($validation_errors as $error) {
            echo "  - $error\n";
        }
    }
}
?>