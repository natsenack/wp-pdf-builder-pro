<?php
/**
 * Test simple de logique pour vérifier la synchronisation sans WordPress
 */

echo "<h1>🧪 Test de logique de synchronisation (sans WordPress)</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Simuler une classe PDF_Builder_Admin simplifiée pour tester la logique
class TestPDFBuilder {

    public function generate_unified_html($template, $order = null) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Test</title></head><body>';

        if (isset($template['elements']) && is_array($template['elements'])) {
            foreach ($template['elements'] as $element) {
                $content = $element['content'] ?? '';

                // Simuler le remplacement de variables si order existe
                if ($order) {
                    $content = str_replace('{{customer_name}}', 'John Doe (test)', $content);
                    $content = str_replace('{{order_id}}', '123', $content);
                }

                switch ($element['type']) {
                    case 'text':
                        $html .= '<div>' . htmlspecialchars($content) . '</div>';
                        break;
                    case 'customer_name':
                        if ($order) {
                            $html .= '<div>John Doe (test)</div>';
                        } else {
                            $html .= '<div>' . htmlspecialchars($content) . '</div>';
                        }
                        break;
                    default:
                        $html .= '<div>' . htmlspecialchars($content ?: $element['type']) . '</div>';
                        break;
                }
            }
        }

        $html .= '</body></html>';
        return $html;
    }

    public function generate_html_from_template_data($template) {
        return $this->generate_unified_html($template, null);
    }

    public function generate_order_html($order, $template_data) {
        return $this->generate_unified_html($template_data, $order);
    }
}

// Test des données
$test_template = [
    'elements' => [
        [
            'type' => 'text',
            'content' => 'Texte statique'
        ],
        [
            'type' => 'customer_name',
            'content' => '{{customer_name}}'
        ]
    ]
];

$test_order = ['id' => 123]; // Objet order simulé

$builder = new TestPDFBuilder();

echo "<h2>1. Test sans commande (aperçu éditeur)</h2>";
$html_no_order = $builder->generate_unified_html($test_template, null);
echo "<p class='info'>HTML généré: " . htmlspecialchars(substr($html_no_order, 0, 200)) . "...</p>";

if (strpos($html_no_order, 'Texte statique') !== false) {
    echo "<p class='success'>✅ Contenu statique présent</p>";
} else {
    echo "<p class='error'>❌ Contenu statique manquant</p>";
}

if (strpos($html_no_order, '{{customer_name}}') !== false) {
    echo "<p class='success'>✅ Variables non remplacées (aperçu éditeur)</p>";
} else {
    echo "<p class='error'>❌ Variables remplacées incorrectement</p>";
}

echo "<h2>2. Test avec commande (aperçu commande)</h2>";
$html_with_order = $builder->generate_unified_html($test_template, $test_order);
echo "<p class='info'>HTML généré: " . htmlspecialchars(substr($html_with_order, 0, 200)) . "...</p>";

if (strpos($html_with_order, 'Texte statique') !== false) {
    echo "<p class='success'>✅ Contenu statique présent</p>";
} else {
    echo "<p class='error'>❌ Contenu statique manquant</p>";
}

if (strpos($html_with_order, 'John Doe (test)') !== false) {
    echo "<p class='success'>✅ Variables remplacées (aperçu commande)</p>";
} else {
    echo "<p class='error'>❌ Variables non remplacées</p>";
}

echo "<h2>3. Test de cohérence des méthodes</h2>";

// Test generate_html_from_template_data
$html_editor = $builder->generate_html_from_template_data($test_template);
if ($html_editor === $html_no_order) {
    echo "<p class='success'>✅ generate_html_from_template_data cohérent</p>";
} else {
    echo "<p class='error'>❌ generate_html_from_template_data incohérent</p>";
}

// Test generate_order_html
$html_order = $builder->generate_order_html($test_order, $test_template);
if ($html_order === $html_with_order) {
    echo "<p class='success'>✅ generate_order_html cohérent</p>";
} else {
    echo "<p class='error'>❌ generate_order_html incohérent</p>";
}

echo "<h2>4. Résumé</h2>";
echo "<p><strong>Si tous les tests sont verts, la logique de synchronisation fonctionne !</strong></p>";
echo "<p>La méthode unifiée gère correctement les deux cas : aperçu éditeur et aperçu commandes.</p>";

?>