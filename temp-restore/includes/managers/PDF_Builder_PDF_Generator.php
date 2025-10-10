<?php
/**
 * PDF Builder Pro - Générateur PDF avancé avec support WooCommerce
 * Version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe PDF_Builder_PDF_Generator - Générateur PDF avec variables dynamiques
 */
class PDF_Builder_PDF_Generator {

    /**
     * Données de substitution pour les variables
     * @var array
     */
    private $variables = [];

    /**
     * Générer un PDF depuis un document
     *
     * @param mixed $document
     * @param mixed $template
     * @param array $options
     * @return string Chemin du fichier PDF généré
     */
    public function generate_from_document($document, $template, $options = []) {
        // Préparer les variables
        $this->variables = isset($options['variables']) ? $options['variables'] : [];

        // Pour l'instant, retourner un PDF de test
        // TODO: Implémenter la vraie génération PDF
        return $this->generate_test_pdf($template, $this->variables);
    }

    /**
     * Générer un PDF depuis un template
     *
     * @param mixed $template
     * @param array $data
     * @return string Contenu PDF (pour l'instant)
     */
    public function generate_from_template($template, $data = []) {
        // Préparer les variables
        $this->variables = $data;

        // Pour l'instant, retourner un contenu HTML simulé
        // TODO: Implémenter la vraie génération PDF avec une bibliothèque comme TCPDF ou DomPDF
        return $this->generate_html_content($template, $this->variables);
    }

    /**
     * Générer un contenu HTML simulé pour les tests
     *
     * @param mixed $template
     * @param array $variables
     * @return string
     */
    private function generate_html_content($template, $variables = []) {
        // Template HTML de base
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Builder Pro - Document</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .company-info { float: right; text-align: right; }
        .invoice-title { font-size: 24px; font-weight: bold; color: #333; }
        .invoice-details { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .total { text-align: right; font-weight: bold; }
        .footer { margin-top: 40px; border-top: 1px solid #333; padding-top: 10px; font-size: 12px; color: #666; }
    </style>
</head>
<body>';

        // En-tête
        $html .= '<div class="header">';
        $html .= '<div class="company-info">';
        $html .= '<h1>' . $this->replace_variables('Votre Entreprise', $variables) . '</h1>';
        $html .= '<p>123 Rue de l\'Exemple<br>75001 Paris<br>France</p>';
        $html .= '</div>';
        $html .= '<div style="clear: both;"></div>';
        $html .= '</div>';

        // Titre de la facture/devis
        $html .= '<h1 class="invoice-title">' . $this->replace_variables('FACTURE', $variables) . '</h1>';

        // Détails de la commande
        $html .= '<div class="invoice-details">';
        $html .= '<p><strong>N° de commande:</strong> ' . $this->replace_variables('[order_number]', $variables) . '</p>';
        $html .= '<p><strong>Date:</strong> ' . $this->replace_variables('[order_date]', $variables) . '</p>';
        $html .= '<p><strong>Client:</strong> ' . $this->replace_variables('[billing_first_name] [billing_last_name]', $variables) . '</p>';
        $html .= '</div>';

        // Tableau des articles
        $html .= '<table>';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Description</th>';
        $html .= '<th>Quantité</th>';
        $html .= '<th>Prix</th>';
        $html .= '<th>Total</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        // Articles de la commande
        if (isset($variables['order_items']) && is_array($variables['order_items'])) {
            foreach ($variables['order_items'] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . esc_html($item['name']) . '</td>';
                $html .= '<td>' . esc_html($item['quantity']) . '</td>';
                $html .= '<td>' . wc_price($item['price'] / $item['quantity']) . '</td>';
                $html .= '<td>' . wc_price($item['total']) . '</td>';
                $html .= '</tr>';
            }
        } else {
            // Données de test
            $html .= '<tr>';
            $html .= '<td>Produit de test</td>';
            $html .= '<td>1</td>';
            $html .= '<td>100,00 €</td>';
            $html .= '<td>100,00 €</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        // Totaux
        $html .= '<div class="total">';
        $html .= '<p><strong>Sous-total:</strong> ' . $this->replace_variables('[order_subtotal] €', $variables) . '</p>';
        $html .= '<p><strong>TVA:</strong> ' . $this->replace_variables('[order_tax] €', $variables) . '</p>';
        $html .= '<p><strong>Total:</strong> ' . $this->replace_variables('[order_total] €', $variables) . '</p>';
        $html .= '</div>';

        // Pied de page
        $html .= '<div class="footer">';
        $html .= '<p>Merci pour votre confiance. Pour toute question, contactez-nous.</p>';
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Générer un PDF de test (simulation)
     *
     * @param mixed $template
     * @param array $variables
     * @return string
     */
    private function generate_test_pdf($template, $variables = []) {
        // Créer un fichier temporaire avec du contenu HTML
        $temp_dir = sys_get_temp_dir();
        $filename = 'pdf_builder_test_' . time() . '.html';
        $filepath = $temp_dir . DIRECTORY_SEPARATOR . $filename;

        $content = $this->generate_html_content($template, $variables);
        file_put_contents($filepath, $content);

        return $filepath;
    }

    /**
     * Remplacer les variables dans un texte
     *
     * @param string $text
     * @param array $variables
     * @return string
     */
    private function replace_variables($text, $variables = []) {
        // Variables WooCommerce courantes
        $replacements = [
            '[order_number]' => $variables['order_number'] ?? 'CMD-001',
            '[order_date]' => isset($variables['order_date']) ? date_i18n(get_option('date_format'), strtotime($variables['order_date'])) : date_i18n(get_option('date_format')),
            '[order_total]' => isset($variables['order_total']) ? wc_price($variables['order_total']) : '0,00 €',
            '[order_subtotal]' => isset($variables['order_subtotal']) ? wc_price($variables['order_subtotal']) : '0,00 €',
            '[order_tax]' => isset($variables['order_tax']) ? wc_price($variables['order_tax']) : '0,00 €',
            '[billing_first_name]' => $variables['billing_first_name'] ?? 'Prénom',
            '[billing_last_name]' => $variables['billing_last_name'] ?? 'Nom',
            '[billing_company]' => $variables['billing_company'] ?? '',
            '[billing_address_1]' => $variables['billing_address_1'] ?? 'Adresse',
            '[billing_city]' => $variables['billing_city'] ?? 'Ville',
            '[billing_postcode]' => $variables['billing_postcode'] ?? 'Code postal',
            '[billing_country]' => $variables['billing_country'] ?? 'Pays',
            '[shipping_first_name]' => $variables['shipping_first_name'] ?? 'Prénom',
            '[shipping_last_name]' => $variables['shipping_last_name'] ?? 'Nom',
            '[shipping_address_1]' => $variables['shipping_address_1'] ?? 'Adresse',
            '[shipping_city]' => $variables['shipping_city'] ?? 'Ville',
            '[shipping_postcode]' => $variables['shipping_postcode'] ?? 'Code postal',
            '[shipping_country]' => $variables['shipping_country'] ?? 'Pays',
            '[customer_email]' => $variables['customer_email'] ?? 'email@exemple.com',
            '[payment_method]' => $variables['payment_method'] ?? 'Carte bancaire',
        ];

        // Variables personnalisées
        if (isset($variables['custom_variables'])) {
            $replacements = array_merge($replacements, $variables['custom_variables']);
        }

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    /**
     * Définir les variables de substitution
     *
     * @param array $variables
     */
    public function set_variables($variables = []) {
        $this->variables = $variables;
    }

    /**
     * Obtenir les variables actuelles
     *
     * @return array
     */
    public function get_variables() {
        return $this->variables;
    }
}

