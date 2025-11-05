<?php
/**
 * Validation standalone du template corporate
 */

// Fonction de validation des éléments (extraite du Template Manager)
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

    if (!isset($element['x']) || !is_numeric($element['x'])) {
        $errors[] = "Élément $index: propriété 'x' manquante ou invalide";
    }

    if (!isset($element['y']) || !is_numeric($element['y'])) {
        $errors[] = "Élément $index: propriété 'y' manquante ou invalide";
    }

    if (!isset($element['width']) || !is_numeric($element['width'])) {
        $errors[] = "Élément $index: propriété 'width' manquante ou invalide";
    }

    if (!isset($element['height']) || !is_numeric($element['height'])) {
        $errors[] = "Élément $index: propriété 'height' manquante ou invalide";
    }

    // Vérifications spécifiques selon le type
    $type = $element['type'] ?? '';
    switch ($type) {
        case 'text':
        case 'company_info':
        case 'customer_info':
        case 'order_number':
        case 'dynamic-text':
        case 'document_type':
            // Ces types nécessitent des propriétés
            if (!isset($element['properties'])) {
                $errors[] = "Élément $index ($type): propriété 'properties' manquante";
            }
            break;

        case 'rectangle':
        case 'circle':
        case 'product_table':
        case 'order_info':
        case 'product_table':
            // Ces types peuvent avoir des propriétés optionnelles
            break;

        default:
            $errors[] = "Élément $index: type '$type' non reconnu";
    }

    return $errors;
}

// Fonction de validation de la structure (simplifiée)
function validate_template_structure($template_data) {
    $errors = [];

    // Vérification de base
    if (!is_array($template_data)) {
        $errors[] = 'Les données doivent être un objet JSON (array PHP)';
        return $errors;
    }

    // Propriétés obligatoires
    $required_keys = ['elements', 'canvasWidth', 'canvasHeight', 'version'];
    foreach ($required_keys as $key) {
        if (!isset($template_data[$key])) {
            $errors[] = "Propriété obligatoire manquante: '$key'";
        }
    }

    if (!empty($errors)) {
        return $errors;
    }

    // Types des propriétés
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

    // Validation des éléments
    foreach ($template_data['elements'] as $index => $element) {
        $element_errors = validate_template_element($element, $index);
        $errors = array_merge($errors, $element_errors);

        if (count($errors) >= 10) {
            $errors[] = '... et plus d\'erreurs détectées';
            break;
        }
    }

    return $errors;
}

// Script principal
echo "Validation du template Corporate\n";
echo "================================\n\n";

// Charger le fichier
$corporate_file = __DIR__ . '/templates/builtin/corporate.json';

if (!file_exists($corporate_file)) {
    echo "❌ Fichier corporate.json non trouvé\n";
    exit(1);
}

$content = file_get_contents($corporate_file);
if ($content === false) {
    echo "❌ Impossible de lire le fichier\n";
    exit(1);
}

$data = json_decode($content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Erreur JSON: " . json_last_error_msg() . "\n";
    exit(1);
}

echo "✅ JSON valide\n";
echo "📊 Nombre d'éléments: " . count($data['elements']) . "\n\n";

// Validation de la structure
echo "🔍 Validation de la structure...\n";
$errors = validate_template_structure($data);

if (empty($errors)) {
    echo "✅ Structure valide\n";
} else {
    echo "❌ Erreurs de validation:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
}

echo "\n🏁 Validation terminée\n";