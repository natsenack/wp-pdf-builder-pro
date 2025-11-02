<?php
if (!defined("ABSPATH")) exit;

add_action("admin_menu", function() {
    add_menu_page(
        "Diagnostic PDF Builder",
        "🔍 Diagnostic PDF Builder",
        "manage_options",
        "diagnostic-pdf-builder",
        function() {
            echo "<h1>🔍 Diagnostic Complet PDF Builder Pro</h1>";
            echo "<div class=\"diagnostic-results\">";

            echo "<h2>📊 État du Plugin</h2>";
            echo "<table class=\"widefat\"><tbody>";
            echo "<tr><td><strong>Plugin activé:</strong></td><td>" .
                 (is_plugin_active("wp-pdf-builder-pro/pdf-builder-pro.php") ?
                 "<span style=\"color:green\">✅ OUI</span>" :
                 "<span style=\"color:red\">❌ NON</span>") . "</td></tr>";
            echo "<tr><td><strong>Fichier principal existe:</strong></td><td>" .
                 (file_exists(WP_PLUGIN_DIR . "/wp-pdf-builder-pro/pdf-builder-pro.php") ?
                 "<span style=\"color:green\">✅ OUI</span>" :
                 "<span style=\"color:red\">❌ NON</span>") . "</td></tr>";
            echo "<tr><td><strong>Utilisateur admin:</strong></td><td>" .
                 (current_user_can("manage_options") ?
                 "<span style=\"color:green\">✅ OUI</span>" :
                 "<span style=\"color:red\">❌ NON</span>") . "</td></tr>";
            echo "<tr><td><strong>WordPress version:</strong></td><td>" . get_bloginfo("version") . "</td></tr>";
            echo "<tr><td><strong>PHP version:</strong></td><td>" . PHP_VERSION . "</td></tr>";
            echo "</tbody></table>";

            echo "<h2>📁 Fichiers du Plugin</h2>";
            echo "<table class=\"widefat\"><tbody>";
            $files = ["pdf-builder-pro.php", "bootstrap.php", "admin-validator.php", "server-validator.php"];
            foreach ($files as $file) {
                $path = WP_PLUGIN_DIR . "/wp-pdf-builder-pro/" . $file;
                echo "<tr><td><strong>$file:</strong></td><td>" .
                     (file_exists($path) ?
                     "<span style=\"color:green\">✅ Existe (" . filesize($path) . " bytes)</span>" :
                     "<span style=\"color:red\">❌ Manquant</span>") . "</td></tr>";
            }
            echo "</tbody></table>";

            echo "<h2>🎛️ Menus Enregistrés</h2>";
            echo "<pre style=\"background:#f5f5f5;padding:10px;border:1px solid #ddd;\">";
            global $menu, $submenu;
            echo "Menus principaux:\n";
            foreach ($menu as $item) {
                if (strpos($item[2], "pdf") !== false || strpos($item[0], "PDF") !== false) {
                    echo "- " . $item[0] . " (" . $item[2] . ")\n";
                }
            }
            echo "\nSous-menus Outils:\n";
            if (isset($submenu["tools.php"])) {
                foreach ($submenu["tools.php"] as $item) {
                    if (strpos($item[2], "pdf") !== false || strpos($item[0], "PDF") !== false) {
                        echo "- " . $item[0] . " (" . $item[2] . ")\n";
                    }
                }
            }
            echo "</pre>";

            echo "<h2>🔧 Actions Disponibles</h2>";
            echo "<p>";
            echo "<a href=\"" . admin_url("tools.php?page=pdf-builder-validator") . "\" class=\"button button-primary\">Aller au Validateur</a> ";
            echo "<a href=\"" . admin_url("plugins.php") . "\" class=\"button\">Gérer les Plugins</a> ";
            echo "<a href=\"" . admin_url("tools.php?page=debug-pdf-builder") . "\" class=\"button\">Debug Menu</a>";
            echo "</p>";

            echo "</div>";
            echo "<style>
                .diagnostic-results table { margin: 10px 0; }
                .diagnostic-results td { padding: 5px; }
                .diagnostic-results h2 { margin-top: 30px; color: #23282d; }
            </style>";
        }
    );
});