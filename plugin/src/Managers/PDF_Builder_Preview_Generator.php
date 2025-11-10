<?php

/**
 * PDF Builder Pro - Générateur d'Aperçu
 * Phase 1: Génération d'aperçu côté serveur avec Dompdf
 */

namespace WP_PDF_Builder_Pro\Managers;

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PdfBuilderPreviewGenerator
{
    private $template_data;
    private $preview_type;
    private $order_id;
    private $cache_key;
    private $dompdf;

    /**
     * Constructeur
     */
    public function __construct($template_data, $preview_type = 'sample', $order_id = 0)
    {
        $this->template_data = $template_data;
        $this->preview_type = $preview_type;
        $this->order_id = $order_id;
// Génère une clé de cache unique
        $this->cache_key = $this->generateCacheKey();
// Initialise Dompdf
        $this->initDompdf();
    }

    /**
     * Génère l'aperçu et retourne l'URL
     */
    public function generatePreview()
    {
        try {
// Vérifie le cache d'abord
            $cached_url = $this->getCachedPreview();
            if ($cached_url) {
                return $cached_url;
            }

            // Récupère les données d'aperçu
            $preview_data = $this->getPreviewData();
// Génère le PDF
            $this->renderPdf($preview_data);
// Sauvegarde et retourne l'URL
            return $this->saveAndGetUrl();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Retourne la clé de cache
     */
    public function getCacheKey()
    {
        return $this->cache_key;
    }

    /**
     * Génère une clé de cache unique
     */
    private function generateCacheKey()
    {
        $data_hash = md5(serialize($this->template_data) . $this->preview_type . $this->order_id);
        return 'pdf_preview_' . $data_hash;
    }

    /**
     * Initialise Dompdf
     */
    private function initDompdf()
    {
        require_once WP_PLUGIN_DIR . '/wp-pdf-builder-pro/plugin/vendor/autoload.php';
        $this->dompdf = new Dompdf\Dompdf();
        $this->dompdf->set_option('isRemoteEnabled', true);
        $this->dompdf->set_option('isHtml5ParserEnabled', true);
        $this->dompdf->set_option('defaultFont', 'Arial');
        $this->dompdf->setPaper('A4', 'portrait');
    }

    /**
     * Vérifie si un aperçu est en cache
     */
    private function getCachedPreview()
    {
        $cache_dir = $this->getCacheDirectory();
        $cache_file = $cache_dir . $this->cache_key . '.png';
        if (file_exists($cache_file)) {
        // Vérifie si le cache n'est pas trop vieux (5 minutes)
            $file_age = time() - filemtime($cache_file);
            if ($file_age < 300) {
                $upload_dir = wp_upload_dir();
                $relative_path = str_replace($upload_dir['basedir'], '', $cache_file);
                return $upload_dir['baseurl'] . $relative_path;
            } else {
    // Supprime le cache expiré
                unlink($cache_file);
            }
        }

        return false;
    }

    /**
     * Récupère les données d'aperçu selon le type
     */
    private function getPreviewData()
    {
        switch ($this->preview_type) {
            case 'order':
                return $this->getOrderData();
            case 'sample':
            default:
                return $this->getSampleData();
        }
    }

    /**
     * Récupère les données d'une commande spécifique
     */
    private function getOrderData()
    {
        if (!$this->order_id || !function_exists('wc_get_order')) {
            return $this->getSampleData();
        }

        $order = call_user_func('wc_get_order', $this->order_id);
// @phpstan-ignore-line
        if (!$order) {
            return $this->getSampleData();
        }

        return array(
            'customer' => array(
                'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone(),
                'address' => $order->get_billing_address_1(),
                'city' => $order->get_billing_city(),
                'postcode' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country()
            ),
            'order' => array(
                'number' => $order->get_order_number(),
                'date' => $order->get_date_created()->format('d/m/Y'),
                'total' => $order->get_total(),
                'items' => $this->getOrderItems($order)
            ),
            'company' => $this->getCompanyInfo()
        );
    }

    /**
     * Récupère les données d'exemple
     */
    private function getSampleData()
    {
        return array(
            'customer' => array(
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@email.com',
                'phone' => '01 23 45 67 89',
                'address' => '123 Rue de la Paix',
                'city' => 'Paris',
                'postcode' => '75001',
                'country' => 'France'
            ),
            'order' => array(
                'number' => 'CMD-001',
                'date' => date('d/m/Y'),
                'total' => 125.50,
                'items' => array(
                    array(
                        'name' => 'Produit Exemple 1',
                        'quantity' => 2,
                        'price' => 25.00,
                        'total' => 50.00
                    ),
                    array(
                        'name' => 'Produit Exemple 2',
                        'quantity' => 1,
                        'price' => 75.50,
                        'total' => 75.50
                    )
                )
            ),
            'company' => $this->getCompanyInfo()
        );
    }

    /**
     * Récupère les articles de la commande
     */
    private function getOrderItems($order)
    {
        $items = array();
        foreach ($order->get_items() as $item) {
            $items[] = array(
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $item->get_total() / $item->get_quantity(),
                'total' => $item->get_total()
            );
        }

        return $items;
    }

    /**
     * Récupère les informations de l'entreprise
     */
    private function getCompanyInfo()
    {
        return array(
            'name' => get_option('woocommerce_store_name', get_bloginfo('name')),
            'address' => get_option('woocommerce_store_address', ''),
            'city' => get_option('woocommerce_store_city', ''),
            'postcode' => get_option('woocommerce_store_postcode', ''),
            'country' => get_option('woocommerce_default_country', ''),
            'email' => get_option('woocommerce_email_from_address', get_option('admin_email')),
            'phone' => get_option('woocommerce_store_phone', '')
        );
    }

    /**
     * Rend le PDF avec les données
     */
    private function renderPdf($data)
    {
        // Génère le HTML au lieu d'utiliser les méthodes TCPDF
        $html = $this->generatePreviewHtml($data);
// Charge le HTML dans Dompdf
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();
    }

    /**
     * Génère le HTML de l'aperçu
     */
    private function generatePreviewHtml($data)
    {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Aperçu PDF Builder Pro</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    color: #333;
                }
                .header {
                    text-align: center;
                    font-size: 18px;
                    font-weight: bold;
                    margin-bottom: 20px;
                    color: #2563eb;
                }
                .section {
                    margin-bottom: 20px;
                }
                .section-title {
                    font-size: 14px;
                    font-weight: bold;
                    margin-bottom: 10px;
                    color: #374151;
                }
                .info-row {
                    display: flex;
                    margin-bottom: 5px;
                }
                .info-label {
                    width: 120px;
                    font-weight: bold;
                }
                .info-value {
                    flex: 1;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f0f0f0;
                    font-weight: bold;
                }
                .text-right {
                    text-align: right;
                }
                .text-center {
                    text-align: center;
                }
                .element-text {
                    margin: 10px 0;
                    line-height: 1.4;
                }
                .element-placeholder {
                    border: 1px solid #ccc;
                    padding: 10px;
                    margin: 5px 0;
                    background-color: #f9f9f9;
                }
            </style>
        </head>
        <body>
            <div class="header">APERÇU PDF BUILDER PRO</div>

            <div class="section">
                <div class="section-title">Informations Client</div>
                <div class="info-row">
                    <div class="info-label">Nom:</div>
                    <div class="info-value"><?php echo esc_html($data['customer']['name']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?php echo esc_html($data['customer']['email']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Téléphone:</div>
                    <div class="info-value"><?php echo esc_html($data['customer']['phone']); ?></div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Informations Commande</div>
                <div class="info-row">
                    <div class="info-label">Numéro:</div>
                    <div class="info-value"><?php echo esc_html($data['order']['number']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Date:</div>
                    <div class="info-value"><?php echo esc_html($data['order']['date']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total:</div>
                    <div class="info-value"><?php echo number_format($data['order']['total'], 2, ',', ' ') . ' €'; ?></div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Articles Commandés</div>
                <table>
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th class="text-center">Qté</th>
                            <th class="text-right">Prix</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['order']['items'] as $item) :
                            ?>
                        <tr>
                            <td><?php echo esc_html($item['name']); ?></td>
                            <td class="text-center"><?php echo esc_html($item['quantity']); ?></td>
                            <td class="text-right"><?php echo number_format($item['price'], 2, ',', ' ') . ' €'; ?></td>
                            <td class="text-right"><?php echo number_format($item['total'], 2, ',', ' ') . ' €'; ?></td>
                        </tr>
                            <?php
                        endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($this->template_data['elements'])) :
                ?>
            <div class="section">
                <div class="section-title">Éléments du Template</div>
                <?php foreach ($this->template_data['elements'] as $element) :
                    ?>
                    <?php echo $this->renderTemplateElementHtml($element, $data); ?>
                    <?php
                endforeach; ?>
            </div>
                <?php
            endif; ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Rend un élément du template en HTML
     */
    private function renderTemplateElementHtml($element, $data)
    {
        $output = '';
        switch ($element['type']) {
            case 'text':
                                         $content = $this->processTextContent($element['content'] ?? '', $data);
                $output = '<div class="element-text">' . esc_html($content) . '</div>';

                break;
            case 'image':
                                     $src = $element['src'] ?? '';
                if ($src) {
                    $output = '<div class="element-placeholder"><img src="' . esc_url($src) . '" alt="Image" style="max-width: 100%; height: auto;"></div>';
                } else {
                    $output = '<div class="element-placeholder">[IMAGE: non défini]</div>';
                }

                break;
            default:
                                     $output = '<div class="element-placeholder">[Élément: ' . esc_html($element['type']) . ']</div>';

                break;
        }

        return $output;
    }

    /**
     * Traite le contenu texte avec les variables
     */
    private function processTextContent($content, $data)
    {
        // Remplacement basique des variables
        $replacements = array(
            '{{customer_name}}' => $data['customer']['name'],
            '{{customer_email}}' => $data['customer']['email'],
            '{{order_number}}' => $data['order']['number'],
            '{{order_date}}' => $data['order']['date'],
            '{{order_total}}' => number_format($data['order']['total'], 2, ',', ' ') . ' €',
            '{{company_name}}' => $data['company']['name'],
            '{{company_email}}' => $data['company']['email']
        );
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Sauvegarde le PDF et retourne l'URL
     */
    private function saveAndGetUrl()
    {
        $cache_dir = $this->getCacheDirectory();
// Crée le répertoire de cache s'il n'existe pas
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }

        // Génère le PDF avec Dompdf
        $pdf_content = $this->dompdf->output();
// Sauvegarde en PDF
        $cache_file = $cache_dir . $this->cache_key . '.pdf';
        file_put_contents($cache_file, $pdf_content);
// Retourne l'URL du PDF
        $upload_dir = wp_upload_dir();
        $relative_path = str_replace($upload_dir['basedir'], '', $cache_file);
        return $upload_dir['baseurl'] . $relative_path;
    }

    /**
     * Retourne le répertoire de cache
     */
    private function getCacheDirectory()
    {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/pdf-builder-previews/';
    }
}
