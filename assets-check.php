<?php
/**
 * Vérification des assets déployés
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Fonction d'affichage de la page de vérification des assets
function pdf_builder_assets_check_page() {
    $assets_url = PDF_BUILDER_PRO_ASSETS_URL;
    $assets_path = PDF_BUILDER_PRO_ASSETS_PATH;

    echo '<div class="wrap">';
    echo '<h1>🔍 Vérification des Assets PDF Builder Pro</h1>';

    echo '<h2>📂 Chemins des assets</h2>';
    echo '<ul>';
    echo '<li><strong>URL:</strong> ' . $assets_url . '</li>';
    echo '<li><strong>Chemin serveur:</strong> ' . $assets_path . '</li>';
    echo '</ul>';

    echo '<h2>📄 Fichiers JavaScript</h2>';
    $js_files = [
        'js/dist/pdf-builder-admin.js',
        'js/dist/pdf-builder-nonce-fix.js'
    ];

    echo '<ul>';
    foreach ($js_files as $file) {
        $file_path = $assets_path . $file;
        $file_url = $assets_url . $file;

        $exists = file_exists($file_path);
        $size = $exists ? filesize($file_path) : 0;

        echo '<li>';
        echo '<strong>' . $file . '</strong><br>';
        echo 'Chemin: ' . $file_path . '<br>';
        echo 'URL: <a href="' . $file_url . '" target="_blank">' . $file_url . '</a><br>';
        echo 'Existe: <span style="color:' . ($exists ? 'green' : 'red') . ';">' . ($exists ? '✅ OUI' : '❌ NON') . '</span><br>';
        if ($exists) {
            echo 'Taille: ' . number_format($size) . ' octets<br>';
            echo 'Modifié: ' . date('Y-m-d H:i:s', filemtime($file_path)) . '<br>';
        }
        echo '</li>';
    }
    echo '</ul>';

    echo '<h2>🎨 Fichiers CSS</h2>';
    $css_files = [
        'css/mainApp.css',
        'css/PageBuilderStyle.css',
        'css/metaBoxStyle.css'
    ];

    echo '<ul>';
    foreach ($css_files as $file) {
        $file_path = $assets_path . $file;
        $file_url = $assets_url . $file;

        $exists = file_exists($file_path);
        $size = $exists ? filesize($file_path) : 0;

        echo '<li>';
        echo '<strong>' . $file . '</strong><br>';
        echo 'Chemin: ' . $file_path . '<br>';
        echo 'URL: <a href="' . $file_url . '" target="_blank">' . $file_url . '</a><br>';
        echo 'Existe: <span style="color:' . ($exists ? 'green' : 'red') . ';">' . ($exists ? '✅ OUI' : '❌ NON') . '</span><br>';
        if ($exists) {
            echo 'Taille: ' . number_format($size) . ' octets<br>';
            echo 'Modifié: ' . date('Y-m-d H:i:s', filemtime($file_path)) . '<br>';
        }
        echo '</li>';
    }
    echo '</ul>';

    echo '<h2>🔧 Test de chargement JavaScript</h2>';
    echo '<button onclick="testJsLoading()" class="button">Tester le chargement JS</button>';
    echo '<div id="js-test-result"></div>';

    ?>
    <script>
    function testJsLoading() {
        const resultDiv = document.getElementById('js-test-result');
        resultDiv.innerHTML = '🔄 Test en cours...';

        // Vérifier si les variables globales existent
        const checks = [
            { name: 'pdfBuilderAjax', check: typeof window.pdfBuilderAjax !== 'undefined' },
            { name: 'jQuery', check: typeof window.jQuery !== 'undefined' },
            { name: 'React', check: typeof window.React !== 'undefined' },
            { name: 'ReactDOM', check: typeof window.ReactDOM !== 'undefined' }
        ];

        let results = '<h3>Résultats du test:</h3><ul>';
        checks.forEach(check => {
            results += '<li>' + check.name + ': ' +
                (check.check ? '<span style="color:green;">✅ Disponible</span>' : '<span style="color:red;">❌ Manquant</span>') +
                '</li>';
        });
        results += '</ul>';

        // Vérifier les scripts chargés
        results += '<h3>Scripts chargés:</h3><ul>';
        const scripts = document.querySelectorAll('script[src]');
        scripts.forEach(script => {
            const src = script.src;
            if (src.includes('pdf-builder')) {
                results += '<li>' + src + '</li>';
            }
        });
        results += '</ul>';

        resultDiv.innerHTML = results;
    }
    </script>
    <?php

    echo '</div>';
}

// Ajouter le menu de diagnostic
add_action('admin_menu', 'pdf_builder_add_assets_check_menu');

function pdf_builder_add_assets_check_menu() {
    add_submenu_page(
        'tools.php',
        'Vérification Assets PDF Builder',
        'Vérification Assets',
        'manage_options',
        'pdf-builder-assets-check',
        'pdf_builder_assets_check_page'
    );
}