<?php
/**
 * Page d'administration pour le diagnostic HTML5
 */

add_action('admin_menu', 'pdf_builder_add_diagnostic_menu');

function pdf_builder_add_diagnostic_menu() {
    add_submenu_page(
        'tools.php',
        'PDF Builder - Diagnostic HTML5',
        'Diagnostic HTML5',
        'manage_options',
        'pdf-builder-diagnostic',
        'pdf_builder_diagnostic_page'
    );
}

function pdf_builder_diagnostic_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    ?>
    <div class="wrap">
        <h1>PDF Builder Pro - Diagnostic HTML5</h1>
        <p>Cette page permet de diagnostiquer qui charge la classe <code>Masterminds\HTML5</code> et cause le conflit.</p>

        <div id="diagnostic-results">
            <button id="run-diagnostic" class="button button-primary">Lancer le diagnostic</button>
            <div id="results" style="margin-top: 20px; padding: 10px; background: #f5f5f5; border: 1px solid #ddd; display: none;">
                <h3>Résultats du diagnostic:</h3>
                <pre id="results-content" style="white-space: pre-wrap;"></pre>
            </div>
        </div>

        <script>
        document.getElementById('run-diagnostic').addEventListener('click', function() {
            const button = this;
            const results = document.getElementById('results');
            const content = document.getElementById('results-content');

            button.disabled = true;
            button.textContent = 'Diagnostic en cours...';
            results.style.display = 'block';
            content.textContent = 'Chargement...';

            fetch('/wp-admin/admin-ajax.php?action=pdf_builder_diagnostic_html5', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.textContent = data.data.output;
                } else {
                    content.textContent = 'Erreur: ' + JSON.stringify(data.data);
                }
            })
            .catch(error => {
                content.textContent = 'Erreur réseau: ' + error.message;
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = 'Lancer le diagnostic';
            });
        });
        </script>
    </div>
    <?php
}