<?php
/**
 * Générateur PDF avec TCPDF pour PDF Builder Pro
 */

// Sécurité - seulement en mode WordPress normal
if (!defined('ABSPATH') && !defined('PDF_GENERATOR_TEST_MODE')) {
    exit;
}

// Inclure TCPDF - seulement quand nécessaire
/*
if (function_exists('plugin_dir_path')) {
    require_once plugin_dir_path(__FILE__) . '../lib/tcpdf_autoload.php';
} else {
    // Mode test - utiliser __DIR__
    require_once __DIR__ . '/../lib/tcpdf_autoload.php';
}
*/

class PDF_Generator {

    private $pdf;

    public function __construct() {
        // Vérifier si TCPDF est disponible
        if (class_exists('TCPDF')) {
            // Créer une instance TCPDF
            $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Configuration de base
            $this->pdf->SetCreator('PDF Builder Pro');
            $this->pdf->SetAuthor('PDF Builder Pro');
            $this->pdf->SetTitle('Document PDF Builder Pro');

            // Supprimer les marges par défaut
            $this->pdf->SetMargins(0, 0, 0);
            $this->pdf->SetHeaderMargin(0);
            $this->pdf->SetFooterMargin(0);

            // Mode paysage si nécessaire (A4: 210x297mm)
            $this->pdf->SetAutoPageBreak(false);
        } else {
            // TCPDF non disponible - mode dégradé
            $this->pdf = null;
            error_log('PDF Builder: TCPDF non disponible, génération PDF désactivée');
        }
    }

    /**
     * Générer le PDF à partir des éléments
     */
    public function generate_from_elements($elements) {
        // Charger TCPDF seulement quand nécessaire
        if (!class_exists('TCPDF')) {
            if (function_exists('plugin_dir_path')) {
                require_once plugin_dir_path(__FILE__) . '../lib/tcpdf_autoload.php';
            } else {
                require_once __DIR__ . '/../lib/tcpdf_autoload.php';
            }
        }

        if (!class_exists('TCPDF')) {
            error_log('PDF Builder: Impossible de charger TCPDF');
            return false;
        }

        // Créer l'instance TCPDF seulement maintenant
        // Définir le cache dans un répertoire accessible
        if (!defined('K_PATH_CACHE')) {
            $upload_dir = wp_upload_dir();
            $cache_dir = $upload_dir['basedir'] . '/pdf-builder-cache/';
            if (!file_exists($cache_dir)) {
                wp_mkdir_p($cache_dir);
            }
            define('K_PATH_CACHE', $cache_dir);
        }

        // Définir le chemin des polices
        if (!defined('K_PATH_FONTS')) {
            define('K_PATH_FONTS', plugin_dir_path(__FILE__) . '../lib/tcpdf/fonts/');
        }

        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuration de base
        $this->pdf->SetCreator('PDF Builder Pro');
        $this->pdf->SetAuthor('PDF Builder Pro');
        $this->pdf->SetTitle('Document PDF Builder Pro');

        // Supprimer les marges par défaut
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->SetHeaderMargin(0);
        $this->pdf->SetFooterMargin(0);

        // Mode paysage si nécessaire (A4: 210x297mm)
        $this->pdf->SetAutoPageBreak(false);

        // Dimensions A4 en mm (TCPDF utilise les mm par défaut)
        $page_width = 210;  // A4 width
        $page_height = 297; // A4 height

        // Ajouter une page
        $this->pdf->AddPage();

        // Facteur de conversion pixels -> mm (72 DPI)
        $px_to_mm = 0.264583;

        foreach ($elements as $element) {
            $this->render_element($element, $px_to_mm, $page_width, $page_height);
        }

        // Générer le PDF
        return $this->pdf->Output('document.pdf', 'S'); // 'S' pour retourner le contenu
    }

    /**
     * Rendre un élément dans le PDF
     */
    private function render_element($element, $px_to_mm, $page_width, $page_height) {
        if (!$this->pdf) {
            return;
        }

        $x = $element['x'] * $px_to_mm;
        $y = $element['y'] * $px_to_mm;
        $width = $element['width'] * $px_to_mm;
        $height = $element['height'] * $px_to_mm;

        // Positionner le curseur
        $this->pdf->SetXY($x, $y);

        switch ($element['type']) {
            case 'text':
                $this->render_text_element($element, $width, $height);
                break;

            case 'company_logo':
                $this->render_image_element($element, $width, $height);
                break;

            case 'customer_info':
                $this->render_customer_info($element, $width, $height);
                break;

            case 'company_info':
                $this->render_company_info($element, $width, $height);
                break;

            case 'product_table':
                $this->render_product_table($element, $width, $height, $x, $y);
                break;

            case 'document_type':
                $this->render_document_type($element, $width, $height);
                break;

            case 'divider':
                $this->render_divider($element, $width, $height, $x, $y);
                break;

            // WooCommerce elements
            case 'woocommerce-invoice-number':
            case 'woocommerce-invoice-date':
            case 'woocommerce-order-number':
            case 'woocommerce-order-date':
            case 'woocommerce-customer-name':
            case 'woocommerce-customer-email':
            case 'woocommerce-billing-address':
            case 'woocommerce-shipping-address':
            case 'woocommerce-payment-method':
            case 'woocommerce-order-status':
                $this->render_woocommerce_element($element, $width, $height);
                break;

            default:
                // Élément non supporté - afficher le type
                $this->render_text($element['type'], $element, $width, $height);
                break;
        }
    }

    private function render_text_element($element, $width, $height) {
        $text = $element['text'] ?? 'Texte';
        $this->render_text($text, $element, $width, $height);
    }

    private function render_image_element($element, $width, $height) {
        if (!empty($element['src'])) {
            // Télécharger l'image depuis l'URL
            $image_data = $this->download_image($element['src']);
            if ($image_data) {
                // Créer un fichier temporaire
                $temp_file = tempnam(sys_get_temp_dir(), 'pdf_img');
                file_put_contents($temp_file, $image_data);

                // Ajouter l'image au PDF
                $this->pdf->Image($temp_file, $this->pdf->GetX(), $this->pdf->GetY(), $width, $height);

                // Supprimer le fichier temporaire
                unlink($temp_file);
            }
        }
    }

    private function render_customer_info($element, $width, $height) {
        $text = "Informations Client\nNom: Jean Dupont\nEmail: jean@example.com\nTéléphone: +33 1 23 45 67 89";
        $this->render_multiline_text($text, $element, $width, $height);
    }

    private function render_company_info($element, $width, $height) {
        $text = "Ma Société SARL\n123 Rue de l'Entreprise\n75001 Paris, France\nTél: +33 1 23 45 67 89\ncontact@masociete.com";
        $this->render_multiline_text($text, $element, $width, $height);
    }

    private function render_product_table($element, $width, $height, $x, $y) {
        // Créer un tableau simple
        $this->pdf->SetXY($x, $y);

        // En-tête
        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->Cell(40, 5, 'Produit', 1, 0, 'L');
        $this->pdf->Cell(15, 5, 'Qté', 1, 0, 'C');
        $this->pdf->Cell(20, 5, 'Prix', 1, 0, 'R');
        $this->pdf->Cell(20, 5, 'Total', 1, 1, 'R');

        // Lignes de données
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(40, 5, 'Produit A', 1, 0, 'L');
        $this->pdf->Cell(15, 5, '2', 1, 0, 'C');
        $this->pdf->Cell(20, 5, '19.99€', 1, 0, 'R');
        $this->pdf->Cell(20, 5, '39.98€', 1, 1, 'R');

        $this->pdf->Cell(40, 5, 'Produit B', 1, 0, 'L');
        $this->pdf->Cell(15, 5, '1', 1, 0, 'C');
        $this->pdf->Cell(20, 5, '29.99€', 1, 0, 'R');
        $this->pdf->Cell(20, 5, '29.99€', 1, 1, 'R');
    }

    private function render_document_type($element, $width, $height) {
        $this->render_text('FACTURE', $element, $width, $height);
    }

    private function render_divider($element, $width, $height, $x, $y) {
        $this->pdf->Line($x, $y + $height/2, $x + $width, $y + $height/2);
    }

    private function render_woocommerce_element($element, $width, $height) {
        // Labels simplifiés pour WooCommerce
        $labels = [
            'woocommerce-invoice-number' => 'N° Facture',
            'woocommerce-invoice-date' => 'Date Facture',
            'woocommerce-order-number' => 'N° Commande',
            'woocommerce-order-date' => 'Date Commande',
            'woocommerce-customer-name' => 'Nom Client',
            'woocommerce-customer-email' => 'Email Client',
            'woocommerce-billing-address' => 'Adresse Facturation',
            'woocommerce-shipping-address' => 'Adresse Livraison',
            'woocommerce-payment-method' => 'Paiement',
            'woocommerce-order-status' => 'Statut'
        ];

        $text = $labels[$element['type']] ?? 'Élément WC';
        $this->render_text($text, $element, $width, $height);
    }

    private function render_text($text, $element, $width, $height) {
        $font_size = ($element['fontSize'] ?? 14) * 0.75; // Ajuster la taille pour TCPDF
        $this->pdf->SetFont('helvetica', $this->get_font_style($element), $font_size);

        $color = $this->hex_to_rgb($element['color'] ?? '#000000');
        $this->pdf->SetTextColor($color[0], $color[1], $color[2]);

        // Calculer la position verticale pour centrer
        $current_y = $this->pdf->GetY();
        $this->pdf->MultiCell($width, $height, $text, 0, $this->get_text_align($element), false);
    }

    private function render_multiline_text($text, $element, $width, $height) {
        $font_size = ($element['fontSize'] ?? 12) * 0.75;
        $this->pdf->SetFont('helvetica', '', $font_size);

        $color = $this->hex_to_rgb($element['color'] ?? '#000000');
        $this->pdf->SetTextColor($color[0], $color[1], $color[2]);

        $this->pdf->MultiCell($width, $height/4, $text, 0, 'L', false);
    }

    private function get_font_style($element) {
        $style = '';
        if ($element['fontWeight'] === 'bold') $style .= 'B';
        if ($element['fontStyle'] === 'italic') $style .= 'I';
        return $style ?: '';
    }

    private function get_text_align($element) {
        switch ($element['textAlign']) {
            case 'center': return 'C';
            case 'right': return 'R';
            default: return 'L';
        }
    }

    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    private function download_image($url) {
        // Utiliser WordPress HTTP API pour télécharger l'image
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return false;
        }
        return wp_remote_retrieve_body($response);
    }
}

// Fonction principale pour traiter la requête AJAX
function pdf_builder_generate_pdf() {
    try {
        // Vérifier la sécurité
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_nonce')) {
            wp_die('Sécurité non valide');
        }

        // Récupérer les éléments
        $elements = json_decode(stripslashes($_POST['elements'] ?? '[]'), true);

        if (empty($elements)) {
            wp_die('Aucun élément à traiter');
        }

        // Générer le PDF
        $generator = new PDF_Generator();
        $pdf_content = $generator->generate_from_elements($elements);

        // Retourner le PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="document.pdf"');
        header('Content-Length: ' . strlen($pdf_content));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdf_content;
        exit;

    } catch (Exception $e) {
        error_log('Erreur génération PDF: ' . $e->getMessage());
        if (function_exists('wp_die')) {
            wp_die('Erreur lors de la génération du PDF: ' . $e->getMessage());
        } else {
            die('Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }
}

// Enregistrer l'action AJAX seulement si on est dans WordPress
if (function_exists('add_action')) {
    add_action('wp_ajax_pdf_builder_generate_pdf', 'pdf_builder_generate_pdf');
}
