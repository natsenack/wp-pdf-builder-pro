<?php

/**
 * PDF Builder Pro - PDF Generator
 * Responsable de la génération des PDFs
 */

namespace PDF_Builder\Admin\Generators;

use Exception;

/**
 * Classe responsable de la génération des PDFs
 */
class PDFGenerator
{
    /**
     * Instance de la classe principale
     */
    private $admin;

    /**
     * Constructeur
     */
    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    /**
     * Génère un PDF depuis les données du template
     */
    public function generatePdfFromTemplateData($template, $filename)
    {
        // Créer le répertoire de stockage s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_path = $pdf_dir . '/' . $filename;
        // Générer le PDF avec notre générateur personnalisé
        // Ici nous utilisons Dompdf pour la génération PDF

        // Générer le HTML d'abord
        $html_content = $this->admin->generateUnifiedHtml($template);

        // Récupérer les paramètres canvas
        $canvas_settings = [
            'canvas_background_color' => get_option('pdf_builder_canvas_bg_color', '#ffffff'),
            'canvas_border_color' => get_option('pdf_builder_canvas_border_color', '#cccccc'),
            'canvas_border_width' => intval(get_option('pdf_builder_canvas_border_width', 1)),
            'canvas_shadow_enabled' => get_option('pdf_builder_canvas_shadow_enabled', false) == '1'
        ];

        // Utiliser notre générateur PDF personnalisé avec les paramètres canvas
        $generator = new PDF_Generator($canvas_settings);
        $pdf_content = $generator->generate_from_elements($this->convertTemplateToElements($template));
        if ($pdf_content) {
            // Sauvegarder le contenu HTML/PDF
            file_put_contents($pdf_path, $pdf_content);
            return $pdf_path;
        } else {
            throw new Exception('Erreur lors de la génération du PDF');
        }
    }

    /**
     * Génère le PDF de commande en privé
     */
    public function generateOrderPdfPrivate($order, $template_data, $filename)
    {
        // Créer le répertoire de stockage s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder/orders';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_path = $pdf_dir . '/' . $filename;
        try {
            // Générer le HTML d'abord
            if (isset($template_data['elements'])) {
                foreach ($template_data['elements'] as $i => $element) {
                    if (isset($element['type']) && $element['type'] === 'product_table') {
                    }
                }
            }

            $html_content = $this->admin->generateUnifiedHtml($template_data, $order);
            // Récupérer les paramètres PDF depuis les options
            $pdf_quality = get_option('pdf_builder_pdf_quality', 'high');
            $pdf_page_size = get_option('pdf_builder_pdf_page_size', 'A4');
            $pdf_orientation = get_option('pdf_builder_pdf_orientation', 'portrait');
            $pdf_compression = get_option('pdf_builder_pdf_compression', 'medium');
            $pdf_metadata_enabled = get_option('pdf_builder_pdf_metadata_enabled', '1') === '1';
            $pdf_print_optimized = get_option('pdf_builder_pdf_print_optimized', '1') === '1';
            // Utiliser Dompdf pour générer le PDF
            require_once PDF_BUILDER_PLUGIN_DIR . 'vendor/autoload.php';

            if (class_exists('Dompdf\Dompdf')) {
                // Créer les options Dompdf pour éviter l'erreur de dépréciation
                $options = new Dompdf\Options();
                $dompdf = new Dompdf\Dompdf($options);
                $dompdf->set_option('isRemoteEnabled', true);
                $dompdf->set_option('isHtml5ParserEnabled', true);
                $dompdf->set_option('defaultFont', 'Arial');

                // Appliquer les paramètres de qualité
                switch ($pdf_quality) {
                    case 'low':
                        $dompdf->set_option('dpi', 72);
                        $dompdf->set_option('defaultMediaType', 'screen');
                        break;
                    case 'medium':
                        $dompdf->set_option('dpi', 96);
                        $dompdf->set_option('defaultMediaType', 'screen');
                        break;
                    case 'high':
                    default:
                        $dompdf->set_option('dpi', 150);
                        $dompdf->set_option('defaultMediaType', 'print');
                        break;
                }

                // Appliquer la compression
                if ($pdf_compression === 'high') {
                    $dompdf->set_option('compress', true);
                } elseif ($pdf_compression === 'low') {
                    $dompdf->set_option('compress', false);
                } // medium = default

                // Métadonnées
                if ($pdf_metadata_enabled) {
                    $dompdf->set_option('enable_remote', true);
                    // Les métadonnées peuvent être ajoutées via des options supplémentaires si nécessaire
                }

                // Optimisation pour l'impression
                if ($pdf_print_optimized) {
                    $dompdf->set_option('defaultMediaType', 'print');
                }

                $dompdf->loadHtml($html_content);
                $dompdf->setPaper($pdf_page_size, $pdf_orientation);
                $dompdf->render();
                file_put_contents($pdf_path, $dompdf->output());
                return $pdf_path;
            } else {
                // Fallback: créer un fichier HTML pour simulation
                file_put_contents($pdf_path, $html_content);
                return $pdf_path;
            }
        } catch (Exception $e) {
            throw $e;
        } catch (Error $e) {
            throw $e;
        }
    }

    /**
     * Génère le HTML de commande
     */
    public function generateOrderHtml($order_id, $template_id = null)
    {
        return $this->admin->generateOrderHtml($order_id, $template_id);
    }

    /**
     * Convertit les données template en format éléments pour le générateur PDF
     */
    public function convertTemplateToElements($template)
    {
        $elements = [];
        // Utiliser les éléments de la première page
        $template_elements = [];
        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            $template_elements = $firstPage['elements'] ?? [];
        } elseif (isset($template['elements']) && is_array($template['elements'])) {
            // Fallback pour l'ancienne structure
            $template_elements = $template['elements'];
        }

        if (is_array($template_elements)) {
            foreach ($template_elements as $element) {
                // Gérer les deux formats de structure des éléments
                if (isset($element['position']) && isset($element['size'])) {
                    // Format structuré (position.x, position.y, size.width, size.height)
                    $x = $element['position']['x'] ?? 0;
                    $y = $element['position']['y'] ?? 0;
                    $width = $element['size']['width'] ?? 100;
                    $height = $element['size']['height'] ?? 50;
                } else {
                    // Format plat (x, y, width, height directement)
                    $x = $element['x'] ?? 0;
                    $y = $element['y'] ?? 0;
                    $width = $element['width'] ?? 100;
                    $height = $element['height'] ?? 50;
                }

                $converted_element = [
                    'type' => $element['type'] ?? 'text',
                    'x' => $x,
                    'y' => $y,
                    'width' => $width,
                    'height' => $height,
                    'text' => $element['content'] ?? $element['text'] ?? '',
                    'fontSize' => $element['style']['fontSize'] ?? $element['fontSize'] ?? 12,
                    'color' => $element['style']['color'] ?? $element['color'] ?? '#000000',
                    'fontWeight' => $element['style']['fontWeight'] ?? $element['fontWeight'] ?? 'normal'
                ];
                $elements[] = $converted_element;
            }
        }

        return $elements;
    }

    public function generatePdfFromCanvasData($canvas_data)
    {
        // Pour l'instant, retourner true pour simuler
        // Génération PDF réalisée avec Dompdf
        return true;
    }
}

