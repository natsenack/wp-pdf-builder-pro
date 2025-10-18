<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - Diagnostic Manager
 * Gestion des diagnostics et tests
 */

class PDF_Builder_Diagnostic_Manager {

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance) {
        $this->main = $main_instance;
        $this->init_hooks();
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // AJAX handlers pour les diagnostics
        add_action('wp_ajax_pdf_builder_check_database', [$this, 'ajax_check_database']);
    }

    /**
     * Page de diagnostic
     */
    public function diagnostic_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        if (isset($_POST['diagnose_template']) && wp_verify_nonce($_POST['diagnostic_nonce'], 'pdf_builder_diagnostic')) {
            $this->run_diagnostic();
        }

        include plugin_dir_path(dirname(__FILE__)) . '../../templates/admin/diagnostic-page.php';
    }

    /**
     * Page de test TCPDF
     */
    public function test_tcpdf_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        $result = '';
        if (isset($_POST['run_tcpdf_test'])) {
            $result = $this->run_simple_tcpdf_test();
        }

        // TODO: Créer le fichier test-tcpdf-page.php dans templates/admin/
        // include plugin_dir_path(dirname(__FILE__)) . '../../templates/admin/test-tcpdf-page.php';
    }

    /**
     * Page développeur
     */
    public function developer_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.'));
        }

        include plugin_dir_path(dirname(__FILE__)) . '../../templates/admin/developer-page.php';
    }

    /**
     * Exécuter le test TCPDF
     */
    private function run_simple_tcpdf_test() {
        ob_start();

        echo "<div class='test-section info'>";
        echo "<h3>🚀 Test simple TCPDF...</h3>";
        echo "<pre>";

        try {
            echo "📚 Chargement de TCPDF...\n";

            // Test de chargement TCPDF
            require_once plugin_dir_path(dirname(__FILE__)) . '../../lib/tcpdf/tcpdf_autoload.php';

            echo "✅ TCPDF chargé\n";

            echo "🔨 Création d'une instance TCPDF...\n";

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            echo "✅ Instance TCPDF créée\n";

            $version = TCPDF_STATIC::getTCPDFVersion();
            echo "📊 Version TCPDF : {$version}\n";

            echo "📝 Ajout d'une page...\n";

            $pdf->AddPage();

            echo "✅ Page ajoutée\n";

            echo "✍️ Ajout de texte...\n";

            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Test TCPDF réussi - ' . date('d/m/Y H:i:s'), 0, 1, 'C');

            echo "✅ Texte ajouté\n";

            echo "💾 Génération du PDF...\n";

            $pdf_content = $pdf->Output('', 'S');

            $size = strlen($pdf_content);
            echo "✅ PDF généré avec succès !\n";
            echo "📊 Taille : " . number_format($size) . " octets\n";

            echo "</pre>";
            echo "</div>";

            echo "<div class='test-section success'>";
            echo "<h3>🎉 Test réussi !</h3>";
            echo "<p>TCPDF fonctionne correctement.</p>";
            echo "</div>";

        } catch (Exception $e) {
            echo "❌ Erreur : " . $e->getMessage() . "\n";
            echo "📍 Fichier : " . $e->getFile() . " ligne " . $e->getLine() . "\n";

            echo "</pre>";
            echo "</div>";

            echo "<div class='test-section error'>";
            echo "<h3>💥 Erreur détectée</h3>";
            echo "<p>Le test TCPDF a échoué. Vérifiez les détails ci-dessus.</p>";
            echo "</div>";
        }

        return ob_get_clean();
    }

    /**
     * Exécuter le diagnostic
     */
    private function run_diagnostic() {
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        if (!$template_id) {
            echo '<div class="notice notice-error"><p>ID du template manquant.</p></div>';
            return;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            echo '<div class="notice notice-error"><p>Template non trouvé.</p></div>';
            return;
        }

        echo '<div class="notice notice-info"><h3>Diagnostic du template #' . $template_id . '</h3></div>';

        $template_data_raw = $template['template_data'];
        $data_length = strlen($template_data_raw);

        echo '<p><strong>Longueur des données:</strong> ' . $data_length . ' caractères</p>';

        // Test JSON
        $decoded = json_decode($template_data_raw, true);
        if ($decoded === null) {
            echo '<p style="color: red;"><strong>Erreur JSON:</strong> ' . json_last_error_msg() . '</p>';
        } else {
            echo '<p style="color: green;"><strong>JSON valide</strong></p>';

            if (isset($decoded['elements']) && is_array($decoded['elements'])) {
                echo '<p><strong>Nombre d\'éléments:</strong> ' . count($decoded['elements']) . '</p>';
            }

            if (isset($decoded['pages']) && is_array($decoded['pages'])) {
                echo '<p><strong>Nombre de pages:</strong> ' . count($decoded['pages']) . '</p>';
            }
        }

        // Test des backslashes
        $has_backslashes = strpos($template_data_raw, '\\') !== false;
        echo '<p><strong>Contient des backslashes:</strong> ' . ($has_backslashes ? 'Oui' : 'Non') . '</p>';

        if ($has_backslashes) {
            $unescaped = stripslashes($template_data_raw);
            $unescaped_decoded = json_decode($unescaped, true);
            echo '<p><strong>Après stripslashes - JSON valide:</strong> ' . ($unescaped_decoded !== null ? 'Oui' : 'Non') . '</p>';
        }
    }

    /**
     * AJAX - Vérifier la base de données
     */
    public function ajax_check_database() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_maintenance')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Vérifier si la table existe
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_templates'") === $table_templates;

        if (!$table_exists) {
            wp_send_json_error('Table des templates introuvable');
        }

        // Compter les templates
        $template_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates");

        // Vérifier l'intégrité des données
        $templates = $wpdb->get_results("SELECT id, template_data FROM $table_templates", ARRAY_A);
        $valid_count = 0;
        $invalid_count = 0;

        foreach ($templates as $template) {
            $decoded = json_decode($template['template_data'], true);
            if ($decoded !== null) {
                $valid_count++;
            } else {
                $invalid_count++;
            }
        }

        wp_send_json_success(array(
            'table_exists' => true,
            'template_count' => intval($template_count),
            'valid_templates' => $valid_count,
            'invalid_templates' => $invalid_count,
            'message' => 'Base de données vérifiée avec succès'
        ));
    }
}