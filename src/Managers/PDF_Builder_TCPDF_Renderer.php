<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
/**
 * PDF Builder Pro - TCPDF Renderer
 * Génération PDF structurée avec TCPDF pour les données WooCommerce
 */

class PDF_Builder_TCPDF_Renderer
{

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Instance TCPDF
     */
    private $pdf;

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
        $this->initialize_tcpdf();
    }

    /**
     * Initialiser TCPDF
     */
    private function initialize_tcpdf()
    {
        if (!class_exists('TCPDF')) {
            include_once WP_PLUGIN_DIR . '/wp-pdf-builder-pro/lib/tcpdf/tcpdf.php';
        }

        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuration de base
        $this->pdf->SetCreator('PDF Builder Pro');
        $this->pdf->SetAuthor('WordPress Plugin');
        $this->pdf->SetTitle('PDF Document');
        $this->pdf->SetSubject('Generated PDF');

        // Configuration des marges
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetHeaderMargin(5);
        $this->pdf->SetFooterMargin(10);

        // Configuration des polices
        $this->pdf->SetFont('helvetica', '', 10);

        // Pas d'en-tête/footer par défaut
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
    }

    /**
     * Générer PDF structuré avec données WooCommerce
     *
     * @param  array  $canvas_data     Données
     *                                 du canvas
     * @param  array  $structured_data Données
     *                                 structurées
     * @param  string $output_path     Chemin de sortie
     * @return bool Succès
     */
    public function generate_structured_pdf($canvas_data, $structured_data = [], $output_path = null)
    {
        try {
            $this->initialize_tcpdf();

            // Ajouter une page
            $this->pdf->AddPage();

            // Générer le contenu depuis les données du canvas
            $this->render_canvas_content($canvas_data);

            // Ajouter les données structurées si disponibles
            if (!empty($structured_data)) {
                $this->render_structured_data($structured_data);
            }

            // Générer le PDF
            if ($output_path) {
                $this->pdf->Output($output_path, 'F');
                return file_exists($output_path);
            } else {
                $this->pdf->Output('generated_pdf.pdf', 'I');
                return true;
            }

        } catch (Exception $e) {
            error_log('Erreur génération TCPDF: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer PDF overlay pour fusion
     */
    public function generate_overlay_pdf($structured_data, $filename)
    {
        try {
            $this->initialize_tcpdf();

            // Configuration pour overlay (fond transparent)
            $this->pdf->SetAlpha(0.9); // Semi-transparent

            $this->pdf->AddPage();

            // Rendre seulement les données structurées importantes
            $this->render_overlay_content($structured_data);

            $upload_dir = wp_upload_dir();
            $output_path = $upload_dir['basedir'] . '/pdf-builder-dual/' . $filename;
            $this->pdf->Output($output_path, 'F');

            return file_exists($output_path);

        } catch (Exception $e) {
            error_log('Erreur génération overlay TCPDF: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Rendre le contenu du canvas avec TCPDF
     */
    private function render_canvas_content($canvas_data)
    {
        if (empty($canvas_data['elements'])) {
            return;
        }

        foreach ($canvas_data['elements'] as $element) {
            $this->render_canvas_element($element);
        }
    }

    /**
     * Rendre un élément du canvas
     */
    private function render_canvas_element($element)
    {
        $type = $element['type'] ?? 'text';
        $x = $element['x'] ?? 0;
        $y = $element['y'] ?? 0;
        $width = $element['width'] ?? 100;
        $height = $element['height'] ?? 20;

        // Convertir les coordonnées canvas en mm (approximation)
        $x_mm = $x * 0.264583; // px to mm
        $y_mm = $y * 0.264583;
        $width_mm = $width * 0.264583;
        $height_mm = $height * 0.264583;

        switch ($type) {
        case 'text':
            $this->render_text_element($element, $x_mm, $y_mm);
            break;
        case 'rectangle':
            $this->render_rectangle_element($element, $x_mm, $y_mm, $width_mm, $height_mm);
            break;
        case 'image':
            $this->render_image_element($element, $x_mm, $y_mm, $width_mm, $height_mm);
            break;
        case 'line':
            $this->render_line_element($element);
            break;
        }
    }

    /**
     * Rendre un élément texte
     */
    private function render_text_element($element, $x, $y)
    {
        $text = $element['text'] ?? '';
        $font_size = $element['fontSize'] ?? 12;
        $font_family = $element['fontFamily'] ?? 'helvetica';
        $color = $element['color'] ?? '#000000';

        // Convertir la taille de police (approximation)
        $font_size_mm = $font_size * 0.352778; // pt to mm

        $this->pdf->SetFont($font_family, '', $font_size_mm);
        $this->set_color($color);

        $this->pdf->SetXY($x, $y);
        $this->pdf->Write(0, $text);
    }

    /**
     * Rendre un élément rectangle
     */
    private function render_rectangle_element($element, $x, $y, $width, $height)
    {
        $fill_color = $element['fillColor'] ?? '#ffffff';
        $stroke_color = $element['strokeColor'] ?? '#000000';
        $stroke_width = $element['strokeWidth'] ?? 1;

        // Couleur de remplissage
        if ($fill_color !== 'transparent') {
            $this->set_color($fill_color, 'fill');
            $this->pdf->Rect($x, $y, $width, $height, 'F');
        }

        // Bordure
        if ($stroke_width > 0) {
            $this->set_color($stroke_color, 'draw');
            $this->pdf->SetLineWidth($stroke_width * 0.264583); // px to mm
            $this->pdf->Rect($x, $y, $width, $height, 'D');
        }
    }

    /**
     * Rendre un élément image
     */
    private function render_image_element($element, $x, $y, $width, $height)
    {
        $src = $element['src'] ?? '';
        if (empty($src)) {
            return;
        }

        // Télécharger l'image si c'est une URL
        if (filter_var($src, FILTER_VALIDATE_URL)) {
            $temp_image = $this->download_image($src);
            if ($temp_image) {
                $src = $temp_image;
            }
        }

        if (file_exists($src)) {
            $this->pdf->Image($src, $x, $y, $width, $height);
        }
    }

    /**
     * Rendre un élément ligne
     */
    private function render_line_element($element)
    {
        $x1 = ($element['x1'] ?? 0) * 0.264583;
        $y1 = ($element['y1'] ?? 0) * 0.264583;
        $x2 = ($element['x2'] ?? 100) * 0.264583;
        $y2 = ($element['y2'] ?? 100) * 0.264583;
        $color = $element['color'] ?? '#000000';
        $width = ($element['strokeWidth'] ?? 1) * 0.264583;

        $this->set_color($color, 'draw');
        $this->pdf->SetLineWidth($width);
        $this->pdf->Line($x1, $y1, $x2, $y2);
    }

    /**
     * Rendre les données structurées (WooCommerce)
     */
    private function render_structured_data($data)
    {
        $this->pdf->AddPage();

        // Titre
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->Cell(0, 10, 'Données WooCommerce', 0, 1, 'C');
        $this->pdf->Ln(5);

        // Rendre les données
        $this->pdf->SetFont('helvetica', '', 10);

        if (isset($data['order'])) {
            $this->render_order_data($data['order']);
        }

        if (isset($data['products'])) {
            $this->render_products_data($data['products']);
        }

        if (isset($data['customer'])) {
            $this->render_customer_data($data['customer']);
        }
    }

    /**
     * Rendre les données de commande
     */
    private function render_order_data($order)
    {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'Commande', 0, 1);
        $this->pdf->SetFont('helvetica', '', 10);

        $fields = [
            'ID' => $order['id'] ?? '',
            'Numéro' => $order['number'] ?? '',
            'Date' => $order['date_created'] ?? '',
            'Statut' => $order['status'] ?? '',
            'Total' => $order['total'] ?? ''
        ];

        foreach ($fields as $label => $value) {
            $this->pdf->Cell(30, 6, $label . ':', 0, 0);
            $this->pdf->Cell(0, 6, $value, 0, 1);
        }

        $this->pdf->Ln(3);
    }

    /**
     * Rendre les données produits
     */
    private function render_products_data($products)
    {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'Produits', 0, 1);
        $this->pdf->SetFont('helvetica', '', 10);

        foreach ($products as $product) {
            $this->pdf->Cell(0, 6, $product['name'] ?? 'Produit', 0, 1);
            $this->pdf->Cell(30, 6, 'Quantité:', 0, 0);
            $this->pdf->Cell(0, 6, $product['quantity'] ?? '1', 0, 1);
            $this->pdf->Cell(30, 6, 'Prix:', 0, 0);
            $this->pdf->Cell(0, 6, $product['price'] ?? '0', 0, 1);
            $this->pdf->Ln(2);
        }
    }

    /**
     * Rendre les données client
     */
    private function render_customer_data($customer)
    {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'Client', 0, 1);
        $this->pdf->SetFont('helvetica', '', 10);

        $fields = [
            'Nom' => $customer['name'] ?? '',
            'Email' => $customer['email'] ?? '',
            'Téléphone' => $customer['phone'] ?? '',
            'Adresse' => $customer['address'] ?? ''
        ];

        foreach ($fields as $label => $value) {
            $this->pdf->Cell(30, 6, $label . ':', 0, 0);
            $this->pdf->Cell(0, 6, $value, 0, 1);
        }
    }

    /**
     * Rendre le contenu overlay
     */
    private function render_overlay_content($data)
    {
        // Overlay minimal avec seulement les données essentielles
        $this->pdf->SetFont('helvetica', 'B', 8);
        $this->pdf->SetTextColor(100, 100, 100); // Gris discret

        if (isset($data['order']['number'])) {
            $this->pdf->SetXY(15, 15);
            $this->pdf->Write(0, 'Commande: ' . $data['order']['number']);
        }

        if (isset($data['order']['total'])) {
            $this->pdf->SetXY(15, 25);
            $this->pdf->Write(0, 'Total: ' . $data['order']['total']);
        }
    }

    /**
     * Définir la couleur
     */
    private function set_color($color, $type = 'text')
    {
        if (preg_match('/^#([a-fA-F0-9]{6})$/', $color, $matches)) {
            $r = hexdec(substr($matches[1], 0, 2));
            $g = hexdec(substr($matches[1], 2, 2));
            $b = hexdec(substr($matches[1], 4, 2));

            switch ($type) {
            case 'fill':
                $this->pdf->SetFillColor($r, $g, $b);
                break;
            case 'draw':
                $this->pdf->SetDrawColor($r, $g, $b);
                break;
            default:
                $this->pdf->SetTextColor($r, $g, $b);
            }
        }
    }

    /**
     * Télécharger une image depuis une URL
     */
    private function download_image($url)
    {
        try {
            $response = wp_remote_get($url);
            if (is_wp_error($response)) {
                return false;
            }

            $image_data = wp_remote_retrieve_body($response);
            if (empty($image_data)) {
                return false;
            }

            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
            }

            $filename = 'temp_' . time() . '_' . basename($url);
            $filepath = $temp_dir . '/' . $filename;

            if (file_put_contents($filepath, $image_data)) {
                return $filepath;
            }

        } catch (Exception $e) {
            error_log('Erreur téléchargement image: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Obtenir l'instance TCPDF
     */
    public function get_tcpdf_instance()
    {
        return $this->pdf;
    }
}
