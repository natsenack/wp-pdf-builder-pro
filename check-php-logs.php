<?php
/**
 * Vérificateur de logs PHP - PDF Builder Pro
 * Lit les logs PHP pour diagnostiquer les problèmes de chargement des scripts
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Vérificateur de logs PHP - PDF Builder Pro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .log-entry { background: #f8f9fa; border-left: 4px solid #007cba; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .log-pdf { border-left-color: #28a745; }
        .log-error { border-left-color: #dc3545; background: #f8d7da; }
        .log-wp { border-left-color: #ffc107; }
        .timestamp { color: #666; font-size: 12px; }
        .message { margin-top: 5px; }
        .filter-buttons { margin: 20px 0; }
        .filter-buttons button { margin-right: 10px; padding: 8px 16px; border: 1px solid #ccc; background: white; cursor: pointer; border-radius: 4px; }
        .filter-buttons button.active { background: #007cba; color: white; border-color: #007cba; }
        .refresh-btn { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Vérificateur de logs PHP - PDF Builder Pro</h1>

    <div class="filter-buttons">
        <button onclick="filterLogs('all')" class="active" id="btn-all">Tous les logs</button>
        <button onclick="filterLogs('pdf')" id="btn-pdf">PDF Builder uniquement</button>
        <button onclick="filterLogs('error')" id="btn-error">Erreurs uniquement</button>
        <button onclick="filterLogs('wp')" id="btn-wp">WordPress</button>
        <button onclick="refreshLogs()" class="refresh-btn">🔄 Actualiser</button>
    </div>

    <div id="logs-container">
        <?php
        // Chemin vers le fichier debug.log de WordPress
        $debug_log_path = WP_CONTENT_DIR . '/debug.log';

        if (file_exists($debug_log_path)) {
            $logs = file($debug_log_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $pdf_logs = [];

            // Filtrer les logs des 24 dernières heures et chercher les logs PDF Builder
            foreach (array_reverse($logs) as $log) {
                // Chercher les logs PDF Builder
                if (strpos($log, 'PDF Builder') !== false) {
                    $pdf_logs[] = $log;
                }
            }

            // Afficher les derniers logs PDF Builder (maximum 50)
            $displayed_logs = array_slice($pdf_logs, 0, 50);

            if (empty($displayed_logs)) {
                echo '<div class="log-entry log-error">';
                echo '<div class="timestamp">' . date('Y-m-d H:i:s') . '</div>';
                echo '<div class="message">❌ Aucun log PDF Builder trouvé dans debug.log</div>';
                echo '<div class="message">Vérifiez que WP_DEBUG est activé et que le fichier debug.log existe.</div>';
                echo '</div>';
            } else {
                foreach ($displayed_logs as $log) {
                    $css_class = 'log-pdf';
                    if (strpos($log, '❌') !== false || strpos($log, 'Error') !== false) {
                        $css_class = 'log-error';
                    } elseif (strpos($log, 'WordPress') !== false) {
                        $css_class = 'log-wp';
                    }

                    echo '<div class="log-entry ' . $css_class . '">';
                    echo '<div class="timestamp">' . date('Y-m-d H:i:s') . '</div>';
                    echo '<div class="message">' . htmlspecialchars($log) . '</div>';
                    echo '</div>';
                }
            }

            echo '<div class="log-entry">';
            echo '<div class="timestamp">' . date('Y-m-d H:i:s') . '</div>';
            echo '<div class="message">📊 Total des logs PDF Builder analysés : ' . count($pdf_logs) . '</div>';
            echo '<div class="message">📄 Logs affichés : ' . count($displayed_logs) . ' (derniers 50)</div>';
            echo '</div>';

        } else {
            echo '<div class="log-entry log-error">';
            echo '<div class="timestamp">' . date('Y-m-d H:i:s') . '</div>';
            echo '<div class="message">❌ Fichier debug.log introuvable : ' . $debug_log_path . '</div>';
            echo '<div class="message">Assurez-vous que WP_DEBUG est activé dans wp-config.php</div>';
            echo '</div>';
        }
        ?>
    </div>

    <script>
        function filterLogs(type) {
            const logs = document.querySelectorAll('.log-entry');
            const buttons = document.querySelectorAll('.filter-buttons button');

            // Reset active button
            buttons.forEach(btn => btn.classList.remove('active'));

            // Set active button
            document.getElementById('btn-' + type).classList.add('active');

            logs.forEach(log => {
                if (type === 'all') {
                    log.style.display = 'block';
                } else {
                    if (log.classList.contains('log-' + type)) {
                        log.style.display = 'block';
                    } else {
                        log.style.display = 'none';
                    }
                }
            });
        }

        function refreshLogs() {
            location.reload();
        }

        // Auto-refresh every 30 seconds
        setInterval(refreshLogs, 30000);
    </script>
</body>
</html>