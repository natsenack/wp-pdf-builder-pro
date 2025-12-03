<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());
?>
<main class="wrap" id="pdf-builder-settings-wrapper">
    <!-- SCRIPT DE TEST AU DÉBUT ABSOLU -->
    <script>
    console.log('🔥 PDF BUILDER - SCRIPT DE TEST AU DÉBUT DU HTML - SI ÇA APPARAÎT PAS, LA PAGE NE SE CHARGE PAS');
    console.log('🔥 URL actuelle:', window.location.href);
    console.log('🔥 User Agent:', navigator.userAgent);
    console.log('🔥 Timestamp:', new Date().toISOString());
    </script>

    <header class="pdf-builder-header">
        <h1>Parametres PDF Builder Pro</h1>
    </header>

    <!-- LOG APRES HEADER -->
    <script>console.log('📍 LOG APRES HEADER - HTML parsing OK jusqu\'ici');</script>

    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs" role="tablist" aria-label="Onglets des paramètres PDF Builder">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general" role="tab" aria-selected="true" aria-controls="general">Général</a>
        <a href="#licence" class="nav-tab" data-tab="licence" role="tab" aria-selected="false" aria-controls="licence">Licence</a>
        <a href="#systeme" class="nav-tab" data-tab="systeme" role="tab" aria-selected="false" aria-controls="systeme">Système</a>
        <a href="#acces" class="nav-tab" data-tab="acces" role="tab" aria-selected="false" aria-controls="acces">Accès</a>
        <a href="#securite" class="nav-tab" data-tab="securite" role="tab" aria-selected="false" aria-controls="securite">Sécurité</a>
        <a href="#pdf" class="nav-tab" data-tab="pdf" role="tab" aria-selected="false" aria-controls="pdf">PDF</a>
        <a href="#contenu" class="nav-tab" data-tab="contenu" role="tab" aria-selected="false" aria-controls="contenu">Contenu</a>
        <a href="#templates" class="nav-tab" data-tab="templates" role="tab" aria-selected="false" aria-controls="templates">Modèles</a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur" role="tab" aria-selected="false" aria-controls="developpeur">Développeur</a>
    </nav>

    <!-- LOG APRES NAV -->
    <script>console.log('📍 LOG APRES NAV - HTML parsing OK jusqu\'ici');</script>

    <section id="pdf-builder-tab-content" class="tab-content-wrapper" role="tabpanel" aria-live="polite">
        <!-- LOG AVANT GENERAL -->
        <script>console.log('📍 LOG AVANT GENERAL - Inclusion du fichier general');</script>
        <div id="general" class="tab-content active" role="tabpanel" aria-labelledby="tab-general">
            <?php require_once 'settings-general.php'; ?>
        </div>
        <!-- LOG APRES GENERAL -->
        <script>console.log('📍 LOG APRES GENERAL - Fichier general inclus');</script>

        <!-- LOG AVANT LICENCE -->
        <script>console.log('📍 LOG AVANT LICENCE - Inclusion du fichier licence');</script>
        <div id="licence" class="tab-content" role="tabpanel" aria-labelledby="tab-licence">
            <?php require_once 'settings-licence.php'; ?>
        </div>
        <!-- LOG APRES LICENCE -->
        <script>console.log('📍 LOG APRES LICENCE - Fichier licence inclus');</script>

        <!-- LOG AVANT SYSTEME -->
        <script>console.log('📍 LOG AVANT SYSTEME - Inclusion du fichier systeme');</script>
        <div id="systeme" class="tab-content" role="tabpanel" aria-labelledby="tab-systeme">
            <?php require_once 'settings-systeme.php'; ?>
        </div>
        <!-- LOG APRES SYSTEME -->
        <script>console.log('📍 LOG APRES SYSTEME - Fichier systeme inclus');</script>
        <div id="acces" class="tab-content" role="tabpanel" aria-labelledby="tab-acces">
            <?php require_once 'settings-acces.php'; ?>
        </div>
        <div id="securite" class="tab-content" role="tabpanel" aria-labelledby="tab-securite">
            <?php require_once 'settings-securite.php'; ?>
        </div>
        <div id="pdf" class="tab-content" role="tabpanel" aria-labelledby="tab-pdf">
            <?php require_once 'settings-pdf.php'; ?>
        </div>
        <div id="contenu" class="tab-content" role="tabpanel" aria-labelledby="tab-contenu">
            <?php require_once 'settings-contenu.php'; ?>
        </div>
        <div id="templates" class="tab-content" role="tabpanel" aria-labelledby="tab-templates">
            <?php require_once 'settings-templates.php'; ?>
        </div>
        <div id="developpeur" class="tab-content" role="tabpanel" aria-labelledby="tab-developpeur">
            <?php require_once 'settings-developpeur.php'; ?>
        </div>
    </section>

    <!-- SCRIPT DE DIAGNOSTIC AVANT LE SCRIPT PRINCIPAL -->
    <script>
    console.log('🔍 DIAGNOSTIC: Script juste avant le script principal - HTML parsing OK');
    console.log('🔍 DIAGNOSTIC: Timestamp:', new Date().toISOString());
    </script>

    <!-- AFFICHAGE DE L'URL DU SCRIPT POUR DIAGNOSTIC -->
    <div style="background: #f0f0f0; border: 1px solid #ccc; padding: 10px; margin: 10px 0; font-family: monospace; font-size: 12px;">
        <strong>🔍 URL du script settings-tabs.js qui devrait se charger :</strong><br>
        <?php
        $script_path = plugins_url('settings-tabs.js', __FILE__);
        echo htmlspecialchars($script_path);
        ?><br>
        <strong>📁 Chemin local du fichier :</strong><br>
        <?php echo htmlspecialchars(__FILE__); ?><br>
        <strong>🔗 URL de base plugins :</strong><br>
        <?php echo htmlspecialchars(plugins_url('', __FILE__)); ?>
    </div>

    <!-- Fallback minimal pour navigation des onglets: exécuté seulement si le script principal ne s'est pas chargé -->
    <script>
    try {
        // LOGS JS DIRECTS DANS LE HTML POUR DIAGNOSTIC
        console.log('📄 PDF Builder - PAGE HTML CHARGÉE - settings-main.php');
        console.log('📄 PDF Builder - Vérification éléments DOM au chargement HTML:', {
            wrapper: !!document.getElementById('pdf-builder-settings-wrapper'),
            tabs: !!document.getElementById('pdf-builder-tabs'),
            content: !!document.getElementById('pdf-builder-tab-content'),
            navTabs: document.querySelectorAll('#pdf-builder-tabs .nav-tab').length,
            tabContents: document.querySelectorAll('#pdf-builder-tab-content .tab-content').length
        });

        // LOG DES ÉLÉMENTS TROUVÉS
        const foundTabs = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const foundContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
        console.log('📄 PDF Builder - DÉTAIL ÉLÉMENTS TROUVÉS:');
        foundTabs.forEach((tab, i) => {
            console.log(`  Tab ${i+1}: ${tab.textContent.trim()} (data-tab: ${tab.getAttribute('data-tab')})`);
        });
        foundContents.forEach((content, i) => {
            console.log(`  Content ${i+1}: #${content.id} (${content.classList.contains('active') ? 'actif' : 'inactif'})`);
        });

        document.addEventListener('DOMContentLoaded', function() {
            console.log('📄 PDF Builder - DOM CONTENT LOADED - HTML ready');

            // Vérifier que les scripts externes sont chargés
            setTimeout(function() {
                console.log('📄 PDF Builder - TIMEOUT CHECK - Scripts externes chargés?', {
                    pdfBuilderConfig: typeof PDF_BUILDER_CONFIG !== 'undefined',
                    debug: !!(typeof PDF_BUILDER_CONFIG !== 'undefined' && PDF_BUILDER_CONFIG.debug),
                    tabsInitialized: !!window.PDF_BUILDER_TABS_INITIALIZED
                });

                // TESTER LA NAVIGATION MANUELLEMENT
                console.log('📄 PDF Builder - TEST NAVIGATION MANUELLE');
                const testTab = document.querySelector('[data-tab="systeme"]');
                if (testTab) {
                    console.log('📄 PDF Builder - Clic simulé sur onglet Systeme');
                    testTab.click();
                } else {
                    console.log('📄 PDF Builder - ERREUR: Onglet Systeme non trouvé pour test');
                }
            }, 200);
        });

        (function() {
            // Si le script en file est chargé, ne rien faire
            if (typeof window.PDF_BUILDER_CONFIG !== 'undefined') {
                console.log('📄 PDF Builder - SCRIPT EXTERNE DÉTECTÉ, fallback ignoré');
                return;
            }

            console.log('📄 PDF Builder - SCRIPT EXTERNE NON DÉTECTÉ, activation fallback');

            document.addEventListener('DOMContentLoaded', function() {
                console.warn('📄 PDF Builder: Script principal non détecté — activation du fallback minimal');

                const tabsContainer = document.getElementById('pdf-builder-tabs');
                const contentContainer = document.getElementById('pdf-builder-tab-content');
                if (!tabsContainer || !contentContainer) {
                    console.error('📄 PDF Builder - ERREUR: Containers non trouvés', {tabsContainer, contentContainer});
                    return;
                }

                console.log('📄 PDF Builder - Fallback: Containers trouvés, ajout des event listeners');

                const tabButtons = tabsContainer.querySelectorAll('.nav-tab');
                const tabContents = contentContainer.querySelectorAll('.tab-content');

                console.log(`📄 PDF Builder - Fallback: ${tabButtons.length} boutons et ${tabContents.length} contenus trouvés`);

                tabButtons.forEach(function(btn, index) {
                    console.log(`📄 PDF Builder - Fallback: Ajout listener au bouton ${index + 1}: ${btn.getAttribute('data-tab')}`);
                    btn.addEventListener('click', function(e) {
                        console.log('📄 PDF Builder - Fallback: CLIC DÉTECTÉ sur bouton', btn.getAttribute('data-tab'));

                        e.preventDefault();
                        e.stopPropagation();

                        const tabId = btn.getAttribute('data-tab');
                        if (!tabId) {
                            console.error('📄 PDF Builder - Fallback: ERREUR - Pas de data-tab');
                            return;
                        }

                        console.log('📄 PDF Builder - Fallback: Changement vers onglet:', tabId);

                        tabButtons.forEach(function(b) { b.classList.remove('nav-tab-active'); });
                        tabContents.forEach(function(c) { c.classList.remove('active'); });

                        btn.classList.add('nav-tab-active');
                        const target = document.getElementById(tabId) || document.getElementById('tab-' + tabId);
                        if (target) {
                            target.classList.add('active');
                            console.log('📄 PDF Builder - Fallback: Onglet activé avec succès:', tabId);
                        } else {
                            console.error('📄 PDF Builder - Fallback: ERREUR - Contenu non trouvé:', tabId);
                        }

                        try {
                            localStorage.setItem('pdf_builder_active_tab', tabId);
                            console.log('📄 PDF Builder - Fallback: Sauvegardé en localStorage:', tabId);
                        } catch (err) {
                            console.warn('📄 PDF Builder - Fallback: Erreur localStorage:', err.message);
                        }
                    }, false);
                });

                console.log('📄 PDF Builder - Fallback: Initialisation terminée');
            });
        })();
    } catch (error) {
        console.error('💥 ERREUR FATALE dans le script HTML principal:', error);
        console.error('💥 Stack trace:', error.stack);
    }
    </script>
</main>

<?php
// Préparer la configuration pour le JavaScript
$js_config = array(
    'nonce' => wp_create_nonce('pdf_builder_settings_ajax'),
    'ajax_url' => admin_url('admin-ajax.php'),
    // activer/désactiver le debug JS depuis les options (pdf_builder_debug_javascript)
    'debug' => true, // FORCÉ À TRUE TEMPORAIREMENT POUR DIAGNOSTIC
    'debug_verbose' => !empty($settings['pdf_builder_debug_javascript_verbose']) ? true : false,
);

// Enqueue jQuery d'abord, puis notre script
wp_enqueue_script('jquery');
$script_path = plugins_url('settings-tabs.js', __FILE__); // Même dossier que settings-main.php
error_log('PDF Builder: Enqueue script path: ' . $script_path);
error_log('PDF Builder: __FILE__ value: ' . __FILE__);
error_log('PDF Builder: plugin_dir_path: ' . plugin_dir_path(__FILE__));
error_log('PDF Builder: plugins_url base: ' . plugins_url('', __FILE__));
wp_enqueue_script('pdf-builder-settings-tabs', $script_path, array('jquery'), time(), true);
// Localiser la config APRES enqueue mais AVANT le script se charge
wp_localize_script('pdf-builder-settings-tabs', 'PDF_BUILDER_CONFIG', $js_config);
?>


<?php

    // Inclure le diagnostic si nécessaire
    if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
        require_once __DIR__ . '/tab-diagnostic.php';
    }

    // GESTIONNAIRES AJAX - Fonctionnalité de sauvegarde centralisée gérée dans settings-ajax.php

    add_action('wp_ajax_pdf_builder_save_tab_settings', function() {
        try {
            // Vérifier le nonce
            if (!wp_verify_nonce(sanitize_text_field($_POST['nonce'] ?? ''), 'pdf_builder_settings_ajax')) {
                wp_send_json_error(['message' => 'Échec de vérification de sécurité']);
                return;
            }

            $tab = sanitize_text_field($_POST['tab'] ?? 'unknown');

            // Collecter les données spécifiques à l'onglet
            $updated_fields = [];
            foreach ($_POST as $key => $value) {
                if (in_array($key, ['action', 'nonce', 'tab'])) continue;
                $updated_fields[$key] = sanitize_text_field($value);
            }

            // Traiter selon l'onglet
            switch ($tab) {
                case 'general':
                    update_option('pdf_builder_company_phone_manual', $updated_fields['company_phone_manual'] ?? '');
                    update_option('pdf_builder_company_siret', $updated_fields['company_siret'] ?? '');
                    update_option('pdf_builder_company_vat', $updated_fields['company_vat'] ?? '');
                    update_option('pdf_builder_company_rcs', $updated_fields['company_rcs'] ?? '');
                    update_option('pdf_builder_company_capital', $updated_fields['company_capital'] ?? '');
                    // New CSS and HTML settings
                    update_option('pdf_builder_custom_css', $updated_fields['pdf_builder_custom_css'] ?? '');
                    update_option('pdf_builder_css_enabled', $updated_fields['pdf_builder_css_enabled'] ?? '0');
                    update_option('pdf_builder_invoice_template', $updated_fields['pdf_builder_invoice_template'] ?? '');
                    update_option('pdf_builder_quote_template', $updated_fields['pdf_builder_quote_template'] ?? '');
                    update_option('pdf_builder_html_enabled', $updated_fields['pdf_builder_html_enabled'] ?? '0');
                    break;
                case 'acces':
                    // Gérer les rôles d'accès (tableau de rôles)
                    if (isset($_POST['pdf_builder_allowed_roles']) && is_array($_POST['pdf_builder_allowed_roles'])) {
                        $allowed_roles = array_map('sanitize_text_field', $_POST['pdf_builder_allowed_roles']);
                        // Toujours inclure administrator
                        if (!in_array('administrator', $allowed_roles)) {
                            $allowed_roles[] = 'administrator';
                        }
                        update_option('pdf_builder_allowed_roles', $allowed_roles);
                    }
                    break;
                default:
                    // Gestionnaire de paramètres général pour les autres onglets
                    foreach ($updated_fields as $key => $value) {
                        if (strpos($key, 'pdf_builder_') === 0 || strpos($key, 'systeme_') === 0) {
                            update_option('pdf_builder_' . str_replace(['pdf_builder_', 'systeme_'], '', $key), $value);
                        }
                    }
                    break;
            }

            wp_send_json_success([
                'message' => ucfirst($tab) . ' paramètres sauvegardés avec succès',
                'tab' => $tab
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    });

?><!-- LOG JUSTE AVANT LE SCRIPT -->
<script>console.log('📍 LOG JUSTE AVANT LE SCRIPT - PHP execution OK jusqu\'ici');</script>

<!-- CHARGEMENT DU SCRIPT DE NAVIGATION DES ONGLES -->
<script>
<?php
echo "// DEBUG: PHP execution reached script loading section\n";
$script_path = plugin_dir_path(__FILE__) . 'settings-tabs.js';
echo "// DEBUG: Chemin du script: " . $script_path . "\n";
echo "// DEBUG: Fichier existe: " . (file_exists($script_path) ? 'OUI' : 'NON') . "\n";
echo "// DEBUG: plugin_dir_path(__FILE__): " . plugin_dir_path(__FILE__) . "\n";
echo "// DEBUG: __FILE__: " . __FILE__ . "\n";

if (file_exists($script_path)) {
    echo "// DEBUG: Chargement du script...\n";
    $content = file_get_contents($script_path);
    echo "// DEBUG: Taille du fichier: " . strlen($content) . " caractères\n";
    echo $content;
    echo "\n// DEBUG: Script chargé avec succès\n";
} else {
    echo "console.error('PDF Builder: Script settings-tabs.js non trouvé à: " . $script_path . "');\n";
    echo "console.error('PDF Builder: plugin_dir_path: " . plugin_dir_path(__FILE__) . "');\n";
    echo "console.error('PDF Builder: __FILE__: " . __FILE__ . "');\n";
}
?>
</script>
