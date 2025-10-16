<?php
/**
 * Diagnostic JSON pour PDF Builder Pro
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Fonction de diagnostic JSON
function pdf_builder_json_diagnostic() {
    if (!current_user_can('manage_options')) {
        wp_die('Permissions insuffisantes');
    }

    echo '<h1>🔍 Diagnostic JSON PDF Builder Pro</h1>';

    // Récupérer les données des fichiers de debug
    $debug_files = [
        'debug_received_json_server.txt',
        'debug_raw_post_elements_server.txt'
    ];

    foreach ($debug_files as $file) {
        $file_path = plugin_dir_path(__FILE__) . $file;
        if (file_exists($file_path)) {
            echo "<h2>Fichier: {$file}</h2>";
            $content = file_get_contents($file_path);
            echo '<pre style="background:#f5f5f5; padding:10px; border:1px solid #ccc; max-height:400px; overflow:auto;">';
            echo htmlspecialchars(substr($content, 0, 2000));
            echo '</pre>';

            // Tester le JSON
            $json_start = strpos($content, '[');
            if ($json_start !== false) {
                $json_content = substr($content, $json_start);
                $json_test = json_decode($json_content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo '<p style="color:green;">✅ JSON valide - ' . count($json_test) . ' éléments</p>';
                } else {
                    echo '<p style="color:red;">❌ Erreur JSON: ' . json_last_error_msg() . '</p>';
                    echo '<p>Position approximative de l\'erreur: ' . json_last_error() . '</p>';
                }
            }
        } else {
            echo "<p>Fichier {$file} non trouvé</p>";
        }
    }

    // Test de simulation d'envoi
    echo '<h2>🧪 Test de simulation d\'envoi</h2>';
    echo '<button onclick="testJsonSend()">Tester l\'envoi JSON</button>';
    echo '<div id="test-result"></div>';

    ?>
    <script>
    function testJsonSend() {
        const testData = {
            elements: [
                {
                    id: "element_1",
                    type: "text",
                    x: 20,
                    y: 20,
                    width: 200,
                    height: 50,
                    text: "Test élément",
                    backgroundColor: "transparent"
                }
            ],
            canvasWidth: 600,
            canvasHeight: 800
        };

        const jsonString = JSON.stringify(testData);
        console.log('JSON à envoyer:', jsonString);

        const formData = new FormData();
        formData.append('action', 'pdf_builder_pro_save_template');
        formData.append('template_data', jsonString);
        formData.append('template_name', 'Test Diagnostic');
        formData.append('template_id', '0');
        formData.append('nonce', '<?php echo wp_create_nonce("pdf_builder_nonce"); ?>');

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('test-result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            console.log('Réponse du serveur:', data);
        })
        .catch(error => {
            document.getElementById('test-result').innerHTML = '<p style="color:red;">Erreur: ' + error.message + '</p>';
            console.error('Erreur:', error);
        });
    }
    </script>
    <?php
}

// Ajouter le menu de diagnostic
add_action('admin_menu', 'pdf_builder_add_diagnostic_menu');

function pdf_builder_add_diagnostic_menu() {
    add_submenu_page(
        'tools.php',
        'Diagnostic JSON PDF Builder',
        'Diagnostic JSON PDF',
        'manage_options',
        'pdf-builder-json-diagnostic',
        'pdf_builder_json_diagnostic'
    );
}