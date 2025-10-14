<?php
/**
 * PDF Preview Test System - Isolated File
 * Système d'aperçu PDF isolé pour tester la génération avec TCPDF
 */

// Inclure les dépendances WordPress et WooCommerce
require_once '../../../wp-load.php';
require_once '../../../wp-admin/includes/plugin.php';

// Inclure TCPDF
if (file_exists(__DIR__ . '/../lib/tcpdf/tcpdf.php')) {
    require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
} elseif (file_exists(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php')) {
    require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
} else {
    die('TCPDF not found');
}

/**
 * Classe de test pour l'aperçu PDF
 */
class PDF_Preview_Test {

    private $wpdb;
    private $order;
    private $template_data;
    private $elements;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Point d'entrée principal pour tester l'aperçu
     */
    public function test_preview($order_id) {
        try {
            // Charger la commande
            $this->load_order($order_id);

            // Détecter le type de document et charger le template
            $this->load_template();

            // Charger les éléments du canvas
            $this->load_canvas_elements();

            // Générer l'aperçu PDF
            $this->generate_preview_pdf();

        } catch (Exception $e) {
            $this->output_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Générer l'aperçu PDF pour AJAX (retourne l'URL)
     */
    public function generate_preview_for_ajax($order_id) {
        try {
            // Charger la commande
            $this->load_order($order_id);

            // Détecter le type de document et charger le template
            $this->load_template();

            // Charger les éléments du canvas
            $this->load_canvas_elements();

            // Générer l'aperçu PDF et retourner l'URL
            return $this->generate_preview_pdf_ajax();

        } catch (Exception $e) {
            throw new Exception('Erreur génération aperçu: ' . $e->getMessage());
        }
    }

    /**
     * Charger la commande WooCommerce
     */
    private function load_order($order_id) {
        $this->order = wc_get_order($order_id);
        if (!$this->order) {
            throw new Exception('Commande non trouvée: ' . $order_id);
        }
        echo "✅ Commande chargée: #" . $this->order->get_order_number() . "\n";
    }

    /**
     * Détecter le type de document et charger le template approprié
     */
    private function load_template() {
        $order_status = $this->order->get_status();

        // Mapping des statuts vers les types de document
        $status_mapping = [
            'pending' => 'devis',
            'processing' => 'commande',
            'on-hold' => 'commande',
            'completed' => 'facture',
            'cancelled' => 'annulation',
            'refunded' => 'remboursement',
            'failed' => 'erreur'
        ];

        $document_type = $status_mapping[$order_status] ?? 'commande';

        // Vérifier d'abord s'il y a un mapping spécifique pour ce statut
        $table_templates = $this->wpdb->prefix . 'pdf_builder_templates';
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;

        $template_id = null;

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            $template_id = $status_templates[$status_key];
            echo "✅ Template spécifique trouvé pour le statut '{$order_status}': ID {$template_id}\n";
        } else {
            // Chercher un template par défaut pour ce type de document
            $template = $this->wpdb->get_row($this->wpdb->prepare(
                "SELECT id, name FROM {$table_templates} WHERE is_default = 1 AND name LIKE %s LIMIT 1",
                '%' . $document_type . '%'
            ), ARRAY_A);

            if ($template) {
                $template_id = $template['id'];
                echo "✅ Template par défaut trouvé pour le type '{$document_type}': {$template['name']} (ID {$template_id})\n";
            } else {
                // Prendre n'importe quel template par défaut
                $template = $this->wpdb->get_row("SELECT id, name FROM {$table_templates} WHERE is_default = 1 LIMIT 1", ARRAY_A);
                if ($template) {
                    $template_id = $template['id'];
                    echo "✅ Template par défaut générique trouvé: {$template['name']} (ID {$template_id})\n";
                }
            }
        }

        if (!$template_id) {
            throw new Exception('Aucun template trouvé pour le statut: ' . $order_status);
        }

        // Charger les données du template
        $this->template_data = $this->wpdb->get_row($this->wpdb->prepare(
            "SELECT * FROM {$table_templates} WHERE id = %d",
            $template_id
        ), ARRAY_A);

        if (!$this->template_data) {
            throw new Exception('Template introuvable: ' . $template_id);
        }

        echo "✅ Template chargé: {$this->template_data['name']}\n";
    }

    /**
     * Charger les éléments du canvas depuis la BDD
     */
    private function load_canvas_elements() {
        if (empty($this->template_data['canvas_data'])) {
            throw new Exception('Aucune donnée canvas dans le template');
        }

        $canvas_data = json_decode($this->template_data['canvas_data'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erreur de décodage JSON du canvas: ' . json_last_error_msg());
        }

        // Extraire les éléments de la première page
        $this->elements = [];
        if (isset($canvas_data['pages']) && is_array($canvas_data['pages']) && !empty($canvas_data['pages'])) {
            $first_page = $canvas_data['pages'][0];
            $this->elements = $first_page['elements'] ?? [];
        } elseif (isset($canvas_data['elements']) && is_array($canvas_data['elements'])) {
            $this->elements = $canvas_data['elements'];
        }

        if (empty($this->elements)) {
            throw new Exception('Aucun élément trouvé dans le canvas');
        }

        echo "✅ " . count($this->elements) . " éléments chargés depuis le canvas\n";
    }

    /**
     * Générer l'aperçu PDF avec TCPDF
     */
    private function generate_preview_pdf() {
        try {
            // Créer une nouvelle instance TCPDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Configuration de base
            $pdf->SetCreator('PDF Builder Pro - Test');
            $pdf->SetAuthor('PDF Builder Pro');
            $pdf->SetTitle('Aperçu PDF - Commande #' . $this->order->get_order_number());
            $pdf->SetSubject('Aperçu de la commande WooCommerce');

            // Supprimer les en-têtes et pieds de page par défaut
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Marges (conversion pixels vers mm approximative)
            $margins = [20, 20, 20, 20]; // top, right, bottom, left en mm
            $pdf->SetMargins($margins[3], $margins[0], $margins[1]);
            $pdf->SetAutoPageBreak(true, $margins[2]);

            // Ajouter une page
            $pdf->AddPage();

            // Traiter chaque élément du canvas
            foreach ($this->elements as $element) {
                $this->render_canvas_element($pdf, $element);
            }

            // Générer le nom du fichier
            $filename = 'preview-order-' . $this->order->get_id() . '-' . time() . '.pdf';
            $filepath = wp_upload_dir()['basedir'] . '/pdf-builder/' . $filename;

            // Créer le répertoire si nécessaire
            wp_mkdir_p(dirname($filepath));

            // Sauvegarder le PDF
            $pdf->Output($filepath, 'F');

            echo "✅ PDF généré avec succès: {$filepath}\n";
            echo "📄 URL d'accès: " . wp_upload_dir()['baseurl'] . '/pdf-builder/' . $filename . "\n";

            // Afficher un résumé des informations
            $this->display_order_summary();

        } catch (Exception $e) {
            throw new Exception('Erreur génération PDF: ' . $e->getMessage());
        }
    }

    /**
     * Générer l'aperçu PDF pour AJAX (version qui retourne l'URL)
     */
    private function generate_preview_pdf_ajax() {
        try {
            // Créer une nouvelle instance TCPDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Configuration de base
            $pdf->SetCreator('PDF Builder Pro - Test');
            $pdf->SetAuthor('PDF Builder Pro');
            $pdf->SetTitle('Aperçu PDF - Commande #' . $this->order->get_order_number());
            $pdf->SetSubject('Aperçu de la commande WooCommerce');

            // Supprimer les en-têtes et pieds de page par défaut
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Marges (conversion pixels vers mm approximative)
            $margins = [20, 20, 20, 20]; // top, right, bottom, left en mm
            $pdf->SetMargins($margins[3], $margins[0], $margins[1]);
            $pdf->SetAutoPageBreak(true, $margins[2]);

            // Ajouter une page
            $pdf->AddPage();

            // Traiter chaque élément du canvas
            foreach ($this->elements as $element) {
                $this->render_canvas_element($pdf, $element);
            }

            // Générer le nom du fichier
            $filename = 'preview-order-' . $this->order->get_id() . '-' . time() . '.pdf';
            $filepath = wp_upload_dir()['basedir'] . '/pdf-builder/' . $filename;

            // Créer le répertoire si nécessaire
            wp_mkdir_p(dirname($filepath));

            // Sauvegarder le PDF
            $pdf->Output($filepath, 'F');

            // Retourner l'URL d'accès
            return wp_upload_dir()['baseurl'] . '/pdf-builder/' . $filename;

        } catch (Exception $e) {
            throw new Exception('Erreur génération PDF: ' . $e->getMessage());
        }
    }

    /**
     * Rendre un élément du canvas dans le PDF
     */
    private function render_canvas_element($pdf, $element) {
        $type = $element['type'] ?? 'text';
        $content = $element['content'] ?? '';
        $x = ($element['x'] ?? 0) * 0.264583; // Conversion px vers mm
        $y = ($element['y'] ?? 0) * 0.264583;
        $width = ($element['width'] ?? 100) * 0.264583;
        $height = ($element['height'] ?? 50) * 0.264583;

        // Remplacer les variables dans le contenu
        $content = $this->replace_order_variables($content);

        // Styles
        $font_size = 12;
        $font_family = 'helvetica';
        $text_color = [0, 0, 0]; // Noir par défaut

        if (isset($element['style'])) {
            if (isset($element['style']['fontSize'])) {
                $font_size = $element['style']['fontSize'] * 0.352778; // Conversion pt vers mm approximative
            }
            if (isset($element['style']['color'])) {
                $text_color = $this->hex_to_rgb($element['style']['color']);
            }
        }

        // Positionner le curseur
        $pdf->SetXY($x, $y);

        // Appliquer les styles
        $pdf->SetFont($font_family, '', $font_size);
        $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);

        switch ($type) {
            case 'text':
                $pdf->MultiCell($width, $height, $content, 0, 'L', false);
                break;

            case 'invoice_number':
                $invoice_number = $this->order->get_id() . '-' . time();
                $pdf->MultiCell($width, $height, $invoice_number, 0, 'L', false);
                break;

            case 'order_number':
                $pdf->MultiCell($width, $height, $this->order->get_order_number(), 0, 'L', false);
                break;

            case 'customer_name':
                $customer_name = $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name();
                $pdf->MultiCell($width, $height, $customer_name, 0, 'L', false);
                break;

            case 'customer_address':
                $address = $this->order->get_formatted_billing_address();
                $pdf->MultiCell($width, $height, $address, 0, 'L', false);
                break;

            case 'company_info':
                $company_info = $this->get_company_info();
                $pdf->MultiCell($width, $height, $company_info, 0, 'L', false);
                break;

            case 'total':
                $total = $this->order->get_total();
                $pdf->MultiCell($width, $height, wc_price($total), 0, 'R', false);
                break;

            case 'product_table':
                $this->render_product_table($pdf, $x, $y, $width, $height);
                break;

            default:
                // Élément non supporté - afficher le type
                $pdf->MultiCell($width, $height, '[' . $type . ']', 0, 'L', false);
                break;
        }
    }

    /**
     * Rendre le tableau de produits
     */
    private function render_product_table($pdf, $x, $y, $width, $height) {
        $pdf->SetXY($x, $y);

        // En-têtes du tableau
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(80, 8, 'Produit', 1, 0, 'L');
        $pdf->Cell(20, 8, 'Qté', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Prix', 1, 0, 'R');
        $pdf->Cell(30, 8, 'Total', 1, 1, 'R');

        // Contenu du tableau
        $pdf->SetFont('helvetica', '', 9);

        foreach ($this->order->get_items() as $item) {
            $product = $item->get_product();
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $price = $item->get_total() / $quantity;
            $total = $item->get_total();

            $pdf->Cell(80, 6, $product_name, 1, 0, 'L');
            $pdf->Cell(20, 6, $quantity, 1, 0, 'C');
            $pdf->Cell(30, 6, wc_price($price), 1, 0, 'R');
            $pdf->Cell(30, 6, wc_price($total), 1, 1, 'R');
        }

        // Total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 8, 'Total: ' . wc_price($this->order->get_total()), 1, 1, 'R');
    }

    /**
     * Remplacer les variables de commande dans le contenu
     */
    private function replace_order_variables($content) {
        if (!$this->order) {
            return $content;
        }

        $replacements = [
            '{{order_number}}' => $this->order->get_order_number(),
            '{{customer_name}}' => $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name(),
            '{{customer_email}}' => $this->order->get_billing_email(),
            '{{order_date}}' => $this->order->get_date_created()->format('d/m/Y'),
            '{{total}}' => wc_price($this->order->get_total()),
            '{{subtotal}}' => wc_price($this->order->get_subtotal()),
            '{{tax}}' => wc_price($this->order->get_total_tax()),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Récupérer les informations de l'entreprise
     */
    private function get_company_info() {
        $company_parts = [];

        // Nom de l'entreprise
        $company_name = get_bloginfo('name');
        if (!empty($company_name)) {
            $company_parts[] = $company_name;
        }

        // Adresse
        $address_parts = [];
        $address1 = get_option('woocommerce_store_address');
        $address2 = get_option('woocommerce_store_address_2');
        $city = get_option('woocommerce_store_city');
        $postcode = get_option('woocommerce_store_postcode');
        $country = get_option('woocommerce_store_country');

        if (!empty($address1)) $address_parts[] = $address1;
        if (!empty($address2)) $address_parts[] = $address2;
        if (!empty($postcode) && !empty($city)) {
            $address_parts[] = $postcode . ' ' . $city;
        } elseif (!empty($city)) {
            $address_parts[] = $city;
        }

        if (!empty($address_parts)) {
            $company_parts[] = implode("\n", $address_parts);
        }

        // Email et téléphone
        $email = get_option('woocommerce_email_from_address');
        $phone = get_option('woocommerce_store_phone');

        if (!empty($email)) $company_parts[] = 'Email: ' . $email;
        if (!empty($phone)) $company_parts[] = 'Tél: ' . $phone;

        return implode("\n", $company_parts);
    }

    /**
     * Convertir couleur hex vers RGB
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) {
            $hex = str_repeat(substr($hex, 0, 1), 2) .
                   str_repeat(substr($hex, 1, 1), 2) .
                   str_repeat(substr($hex, 2, 1), 2);
        }
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Afficher un résumé de la commande
     */
    private function display_order_summary() {
        echo "\n📋 RÉSUMÉ DE LA COMMANDE:\n";
        echo "------------------------\n";
        echo "Numéro: #" . $this->order->get_order_number() . "\n";
        echo "Statut: " . wc_get_order_status_name($this->order->get_status()) . "\n";
        echo "Client: " . $this->order->get_billing_first_name() . ' ' . $this->order->get_billing_last_name() . "\n";
        echo "Email: " . $this->order->get_billing_email() . "\n";
        echo "Total: " . wc_price($this->order->get_total()) . "\n";
        echo "Date: " . $this->order->get_date_created()->format('d/m/Y H:i') . "\n";

        echo "\n🛒 PRODUITS:\n";
        echo "-----------\n";
        foreach ($this->order->get_items() as $item) {
            echo "- " . $item->get_name() . " (x" . $item->get_quantity() . ") : " . wc_price($item->get_total()) . "\n";
        }

        echo "\n🏢 INFORMATIONS ENTREPRISE:\n";
        echo "--------------------------\n";
        echo $this->get_company_info() . "\n";

        echo "\n🎨 TEMPLATE UTILISÉ:\n";
        echo "-------------------\n";
        echo "Nom: " . $this->template_data['name'] . "\n";
        echo "Éléments: " . count($this->elements) . "\n";
    }

    /**
     * Afficher une erreur
     */
    private function output_error($message) {
        echo "❌ ERREUR: {$message}\n";
    }
}

// Fonction principale de test
function run_pdf_preview_test() {
    echo "🚀 TEST DU SYSTÈME D'APERÇU PDF\n";
    echo "===============================\n\n";

    // Vérifier si un ID de commande est fourni
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;

    if (!$order_id) {
        echo "❌ Paramètre 'order_id' manquant dans l'URL\n";
        echo "💡 Utilisation: ?order_id=123\n";
        return;
    }

    // Créer et exécuter le test
    $test = new PDF_Preview_Test();
    $test->test_preview($order_id);

    echo "\n✅ Test terminé!\n";
}

// Exécuter le test si le fichier est appelé directement
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    run_pdf_preview_test();
}
?>