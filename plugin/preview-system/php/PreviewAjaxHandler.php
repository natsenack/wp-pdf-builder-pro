<?php

namespace PDF_Builder\PreviewSystem;

if (!defined('ABSPATH')) {
    exit('Acces interdit');
}

class PreviewAjaxHandler {
    
    public static function init() {
        add_action('wp_ajax_pdf_builder_generate_preview', [self::class, 'generatePreviewAjax']);
        add_action('wp_ajax_nopriv_pdf_builder_generate_preview', [self::class, 'generatePreviewAjax']);
        add_action('wp_ajax_pdf_builder_generate_html_preview', [self::class, 'generateHtmlPreviewAjax']);
        add_action('wp_ajax_nopriv_pdf_builder_generate_html_preview', [self::class, 'generateHtmlPreviewAjax']);
    }

    public static function generatePreviewAjax() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes', 403);
        }
        
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pdf_builder_ajax')) {
            wp_send_json_error('Nonce invalide', 403);
        }
        
        // Vérifier si on utilise le nouveau format (pageOptions) ou l'ancien (template_data)
        if (isset($_POST['data'])) {
            // Nouveau format (inspiré de woo-pdf)
            $options = stripslashes($_POST['data']);
            
            if (empty($options)) {
                wp_send_json_error('Données manquantes', 400);
            }
            
            $options = json_decode($options);
            if (!$options) {
                wp_send_json_error('Données JSON invalides', 400);
            }
            
            $pageOptions = $options->pageOptions ?? null;
            $previewType = $options->previewType ?? 'general';
            $orderNumberToPreview = $options->orderNumberToPreview ?? '';
            
            if (!$pageOptions) {
                wp_send_json_error('Options de page manquantes', 400);
            }
            
            $result = self::generatePreviewNew($pageOptions, $previewType, $orderNumberToPreview);
            
        } else {
            // Ancien format (rétrocompatibilité)
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
            $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'pdf';
            
            // D'abord, essayer de charger les données depuis POST (template_data envoyé par le frontend)
            $template_data = [];
            if (isset($_POST['template_data'])) {
                $json_data = sanitize_text_field($_POST['template_data']);
                $decoded = json_decode($json_data, true);
                if (is_array($decoded)) {
                    $template_data = $decoded;
                }
            }
            
            // Si pas de template_data dans POST, essayer depuis la DB
            if (empty($template_data) && $template_id && $template_id > 0) {
                $template_data = self::loadTemplateFromDatabase($template_id);
            }
            
            if (!is_array($template_data) || empty($template_data)) {
                wp_send_json_error('Template non trouvé', 400);
            }
            
            $result = self::generatePreviewLegacy($template_data, $format);
        }
        
        if (isset($result['error'])) {
            wp_send_json_error($result['error'], 400);
        }
        
        wp_send_json_success($result);
    }

    public static function generateHtmlPreviewAjax() {
        // Vérification de base - permission de lecture
        if (!current_user_can('read')) {
            wp_send_json_error('Permissions insuffisantes', 403);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pdf_builder_ajax')) {
            wp_send_json_error('Nonce invalide', 403);
        }
        
        // Vérifier si on utilise le nouveau format (pageOptions)
        if (isset($_POST['data'])) {
            $options = stripslashes($_POST['data']);
            
            if (empty($options)) {
                wp_send_json_error('Données manquantes', 400);
            }
            
            $options = json_decode($options);
            if (!$options) {
                wp_send_json_error('Données JSON invalides', 400);
            }
            
            $pageOptions = $options->pageOptions ?? null;
            
            if (!$pageOptions) {
                wp_send_json_error('Options de page manquantes', 400);
            }
            
            $result = self::generateHtmlPreview($pageOptions);
            
        } else {
            wp_send_json_error('Format de données non supporté pour l\'aperçu HTML', 400);
        }
        
        if (isset($result['error'])) {
            wp_send_json_error($result['error'], 400);
        }
        
        wp_send_json_success($result);
    }

    private static function loadTemplateFromDatabase(int $template_id): array {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'pdf_builder_templates';
        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT id, name, template_data FROM {$table_name} WHERE id = %d", $template_id),
            ARRAY_A
        );
        
        if (!$template) {
            return [];
        }
        
        $template_data = json_decode($template['template_data'], true);
        if (!is_array($template_data)) {
            $template_data = [];
        }
        
        $template_data['template_id'] = $template['id'];
        $template_data['template_name'] = $template['name'];
        
        return $template_data;
    }

    private static function generatePreviewNew($pageOptions, string $previewType = 'general', string $orderNumberToPreview = ''): array {
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';
        
        // Log to file for debugging
        $logFile = dirname(__FILE__) . '/../../../logs/pdf_preview_debug.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logMessage = "[PDF PREVIEW] ===== STARTING generatePreviewNew =====\n";
        $logMessage .= "[PDF PREVIEW] pageOptions type: " . gettype($pageOptions) . "\n";
        $logMessage .= "[PDF PREVIEW] pageOptions is object: " . (is_object($pageOptions) ? 'YES' : 'NO') . "\n";
        $logMessage .= "[PDF PREVIEW] pageOptions is array: " . (is_array($pageOptions) ? 'YES' : 'NO') . "\n";
        
        if (is_object($pageOptions)) {
            $logMessage .= "[PDF PREVIEW] pageOptions keys (object): " . implode(', ', array_keys((array)$pageOptions)) . "\n";
            $logMessage .= "[PDF PREVIEW] pageOptions->template exists: " . (isset($pageOptions->template) ? 'YES' : 'NO') . "\n";
            if (isset($pageOptions->template)) {
                $logMessage .= "[PDF PREVIEW] pageOptions->template type: " . gettype($pageOptions->template) . "\n";
                if (is_object($pageOptions->template)) {
                    $logMessage .= "[PDF PREVIEW] pageOptions->template keys: " . implode(', ', array_keys((array)$pageOptions->template)) . "\n";
                    $logMessage .= "[PDF PREVIEW] pageOptions->template has elements: " . (isset($pageOptions->template->elements) ? 'YES' : 'NO') . "\n";
                    if (isset($pageOptions->template->elements)) {
                        $logMessage .= "[PDF PREVIEW] elements count: " . (is_array($pageOptions->template->elements) ? count($pageOptions->template->elements) : 'NOT ARRAY') . "\n";
                        if (is_array($pageOptions->template->elements) && count($pageOptions->template->elements) > 0) {
                            $logMessage .= "[PDF PREVIEW] first element: " . print_r($pageOptions->template->elements[0], true) . "\n";
                        }
                    }
                }
            }
        } elseif (is_array($pageOptions)) {
            $logMessage .= "[PDF PREVIEW] pageOptions keys (array): " . implode(', ', array_keys($pageOptions)) . "\n";
        }
        
        $logMessage .= "[PDF PREVIEW] previewType: " . $previewType . "\n";
        $logMessage .= "[PDF PREVIEW] orderNumberToPreview: " . $orderNumberToPreview . "\n";
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        error_log('[PDF PREVIEW] ===== STARTING generatePreviewNew =====');
        error_log('[PDF PREVIEW] pageOptions type: ' . gettype($pageOptions));
        error_log('[PDF PREVIEW] pageOptions is object: ' . (is_object($pageOptions) ? 'YES' : 'NO'));
        error_log('[PDF PREVIEW] pageOptions is array: ' . (is_array($pageOptions) ? 'YES' : 'NO'));
        
        if (!class_exists('Dompdf\Dompdf')) {
            error_log('[PDF PREVIEW] Dompdf not available');
            return ['error' => 'Dompdf non disponible', 'fallback' => true];
        }
        
        try {
            error_log('[PDF PREVIEW] Creating DataProvider');
            // Créer un DataProvider basique pour les options de page
            $dataProvider = new class($pageOptions) implements \PDF_Builder\Interfaces\DataProviderInterface {
                private $pageOptions;
                
                public function __construct($pageOptions) {
                    $this->pageOptions = $pageOptions;
                }
                
                public function getVariableValue(string $variable): string {
                    // Pour l'instant, retourner des valeurs fictives
                    return 'Test Value';
                }
                
                public function hasVariable(string $variable): bool {
                    return true;
                }
                
                public function getAllVariables(): array {
                    return ['test'];
                }
                
                public function isSampleData(): bool {
                    return true;
                }
                
                public function getContext(): string {
                    return 'preview';
                }
                
                public function validateAndSanitizeData(array $data): array {
                    return $data;
                }
            };
            
            error_log('[PDF PREVIEW] Converting pageOptions to array');
            // Convertir les options de page en array si nécessaire
            $pageOptionsArray = is_array($pageOptions) ? $pageOptions : (array) $pageOptions;
            
            error_log('[PDF PREVIEW] Extracting template data');
            // Extraire les données du template depuis pageOptions.template
            $templateData = $pageOptionsArray['template'] ?? $pageOptionsArray;
            
            // S'assurer que templateData est un array
            $templateData = is_array($templateData) ? $templateData : (array) $templateData;
            error_log('[PDF PREVIEW] Template data keys: ' . implode(', ', array_keys($templateData)));
            
            error_log('[PDF PREVIEW] Creating PDFGenerator');
            // Créer le générateur PDF avec les données du template
            // Passer directement templateData au lieu de ['template' => templateData]
            $generator = new \PDF_Builder\Generators\PDFGenerator($templateData, $dataProvider, true, []);
            
            error_log('[PDF PREVIEW] Calling generatePreview');
            // Pour l'instant, on ne gère que les aperçus généraux
            // TODO: Implémenter la gestion des aperçus de commande spécifique
            
            // Générer et streamer directement le PDF
            $generator->generatePreview();
            
            // Cette ligne ne devrait pas être atteinte car generatePreview() fait exit()
            error_log('[PDF PREVIEW] generatePreview completed without exit');
            return ['success' => true];
            
        } catch (\Exception $e) {
            error_log('[PDF PREVIEW] Exception caught: ' . $e->getMessage());
            error_log('[PDF PREVIEW] Exception trace: ' . $e->getTraceAsString());
            return ['error' => 'Erreur lors de la génération de l\'aperçu: ' . $e->getMessage()];
        }
    }
    
    private static function generateHtmlPreview($pageOptions): array {
        require_once dirname(__FILE__) . '/../../vendor/autoload.php';
        
        try {
            error_log('[HTML PREVIEW] ===== STARTING generateHtmlPreview =====');
            
            // Créer un DataProvider basique pour les options de page
            $dataProvider = new class($pageOptions) implements \PDF_Builder\Interfaces\DataProviderInterface {
                private $pageOptions;
                
                public function __construct($pageOptions) {
                    $this->pageOptions = $pageOptions;
                }
                
                public function getVariableValue(string $variable): string {
                    return 'Test Value';
                }
                
                public function hasVariable(string $variable): bool {
                    return true;
                }
                
                public function getAllVariables(): array {
                    return ['test'];
                }
                
                public function isSampleData(): bool {
                    return true;
                }
                
                public function getContext(): string {
                    return 'preview';
                }
                
                public function validateAndSanitizeData(array $data): array {
                    return $data;
                }
            };
            
            // Convertir les options de page en array si nécessaire
            $pageOptionsArray = is_array($pageOptions) ? $pageOptions : (array) $pageOptions;
            
            // Extraire les données du template depuis pageOptions.template
            $templateData = $pageOptionsArray['template'] ?? $pageOptionsArray;
            
            // S'assurer que templateData est un array
            $templateData = is_array($templateData) ? $templateData : (array) $templateData;
            
            error_log('[HTML PREVIEW] Creating PDFGenerator for HTML preview');
            // Créer le générateur PDF avec les données du template
            $generator = new \PDF_Builder\Generators\PDFGenerator($templateData, $dataProvider, true, []);
            
            error_log('[HTML PREVIEW] Calling generateHtmlPreview');
            // Générer l'aperçu HTML
            $html = $generator->generateHtmlPreview();
            
            error_log('[HTML PREVIEW] HTML preview generation completed, length: ' . strlen($html));
            return ['html' => $html, 'success' => true];
            
        } catch (\Exception $e) {
            error_log('[HTML PREVIEW] Exception caught: ' . $e->getMessage());
            error_log('[HTML PREVIEW] Exception trace: ' . $e->getTraceAsString());
            return ['error' => 'Erreur lors de la génération de l\'aperçu HTML: ' . $e->getMessage()];
        }
    }

    private static function buildHtmlFromTemplate(array $template_data): string {
        $elements = $template_data['elements'] ?? [];
        
        // Extraire les éléments par type
        $logo_element = null;
        $doc_type_element = null;
        $company_info_element = null;
        $customer_info_element = null;
        $order_number_element = null;
        $order_date_element = null;
        $product_table_element = null;
        $dynamic_text_element = null;
        $mentions_element = null;
        
        foreach ($elements as $element) {
            switch ($element['type'] ?? '') {
                case 'company_logo':
                    $logo_element = $element;
                    break;
                case 'document_type':
                    $doc_type_element = $element;
                    break;
                case 'company_info':
                    $company_info_element = $element;
                    break;
                case 'customer_info':
                    $customer_info_element = $element;
                    break;
                case 'order_number':
                    $order_number_element = $element;
                    break;
                case 'woocommerce_order_date':
                    $order_date_element = $element;
                    break;
                case 'product_table':
                    $product_table_element = $element;
                    break;
                case 'dynamic-text':
                    $dynamic_text_element = $element;
                    break;
                case 'mentions':
                    $mentions_element = $element;
                    break;
            }
        }
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 8mm;
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8mm;
            gap: 8mm;
            align-items: flex-start;
        }
        .header-col {
            flex: 1;
        }
        .logo-container {
            text-align: center;
            max-width: 80mm;
            height: 30mm;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2mm;
        }
        .logo-container img {
            max-width: 100%;
            max-height: 28mm;
            width: auto;
            height: auto;
        }
        .document-type-title {
            font-size: 28px;
            font-weight: bold;
            color: #111827;
            text-align: right;
        }
        .separator-line {
            border-top: 1px solid #999999;
            margin: 6mm 0 8mm 0;
        }
        .two-col {
            display: flex;
            gap: 15mm;
            margin-bottom: 10mm;
        }
        .two-col > div {
            flex: 1;
        }
        .info-box {
            background-color: #e5e7eb;
            padding: 5mm;
            font-size: 11px;
            line-height: 1.5;
        }
        .info-box-title {
            font-weight: bold;
            color: #111827;
            margin-bottom: 3mm;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 2mm;
        }
        .info-item {
            color: #374151;
            margin-bottom: 2mm;
        }
        .order-info {
            text-align: right;
        }
        .order-number-label {
            font-weight: bold;
        }
        .order-date {
            font-size: 11px;
            color: #374151;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin: 10mm 0;
        }
        .product-table th {
            background-color: #f9fafb;
            color: #111827;
            padding: 5mm;
            text-align: left;
            border: 0.5px solid #e5e7eb;
            font-weight: bold;
        }
        .product-table td {
            padding: 4mm 5mm;
            border: 0.5px solid #e5e7eb;
            color: #374151;
        }
        .product-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .signature-section {
            margin-top: 20mm;
            font-size: 11px;
        }
        .signature-text {
            white-space: pre-wrap;
            color: #374151;
        }
        .mentions-line {
            margin-top: 10mm;
            padding-top: 5mm;
            border-top: 0.5px solid #d1d5db;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>';
        
        // En-tête avec logo et titre
        $html .= '<div class="header-row">';
        
        // Colonne gauche: infos entreprise
        $html .= '<div class="header-col">';
        if ($company_info_element) {
            $html .= '<div class="info-box">
                <div class="info-box-title">Infos Entreprise</div>
                <div class="info-item">[Company Name]</div>
                <div class="info-item">[Company Address]</div>
                <div class="info-item">Email: [Company Email]</div>
                <div class="info-item">Tél: [Company Phone]</div>
                <div class="info-item">SIRET: [SIRET]</div>
                <div class="info-item">TVA: [VAT]</div>
            </div>';
        }
        $html .= '</div>';
        
        // Colonne milieu: logo
        $html .= '<div class="header-col" style="text-align: center;">';
        if ($logo_element && !empty($logo_element['src'])) {
            error_log('[PREVIEW] Tentative affichage logo: ' . $logo_element['src']);
            $img_src = self::convertImageToBase64($logo_element['src']);
            if ($img_src) {
                error_log('[PREVIEW] Logo image URL: ' . $img_src);
                $html .= '<div class="logo-container">
                    <img src="' . esc_url($img_src) . '" alt="Logo">
                </div>';
            } else {
                error_log('[PREVIEW] Conversion logo échouée, affichage placeholder');
                $html .= '<div style="color: #999; font-size: 10px;">Logo (non chargé)</div>';
            }
        } else {
            $html .= '<div style="color: #999; font-size: 10px;">Logo</div>';
        }
        $html .= '</div>';
        
        // Colonne droite: titre document + commande
        $html .= '<div class="header-col">';
        if ($doc_type_element) {
            $html .= '<div class="document-type-title">' . htmlspecialchars($doc_type_element['title'] ?? 'DOCUMENT') . '</div>';
        }
        if ($order_number_element) {
            $html .= '<div class="order-info">
                <div class="order-number-label">Commande: [Order #]</div>';
            if ($order_date_element) {
                $html .= '<div class="order-date">Date: ' . date('d/m/Y') . '</div>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Ligne séparatrice
        $html .= '<div class="separator-line"></div>';
        
        // Infos client
        $html .= '<div class="two-col">';
        if ($customer_info_element) {
            $html .= '<div class="info-box">
                <div class="info-box-title">Informations Client</div>
                <div class="info-item">Nom: [Customer Name]</div>
                <div class="info-item">Adresse: [Customer Address]</div>
                <div class="info-item">Email: [Customer Email]</div>
                <div class="info-item">Téléphone: [Customer Phone]</div>
            </div>';
        }
        $html .= '</div>';
        
        // Table des produits
        if ($product_table_element) {
            $html .= '<table class="product-table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th width="15%">Qty</th>
                        <th width="20%">Prix Unit.</th>
                        <th width="20%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Produit exemple</td>
                        <td>1</td>
                        <td>100.00 €</td>
                        <td>100.00 €</td>
                    </tr>
                </tbody>
            </table>';
        }
        
        // Signature/Dynamic Text
        if ($dynamic_text_element) {
            $text_content = $dynamic_text_element['text'] ??
                           $dynamic_text_element['content'] ??
                           $dynamic_text_element['value'] ??
                           'Texte dynamique non défini';

            $html .= '<div class="signature-section">
                <div class="signature-text">' . htmlspecialchars($text_content) . '</div>
            </div>';
        }
        
        // Mentions
        if ($mentions_element) {
            $html .= '<div class="mentions-line">
                Email • Téléphone • SIRET • TVA
            </div>';
        }
        
        $html .= '
</body>
</html>';
        
        return $html;
    }

    private static function savePdfTemporarily(string $pdf_content): string {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];
        $base_url = $upload_dir['baseurl'];
        $temp_dir = $base_dir . '/pdf-builder-temp';
        
        if (!is_dir($temp_dir)) {
            @mkdir($temp_dir, 0755, true);
        }
        
        $filename = 'preview-' . uniqid() . '.pdf';
        $filepath = $temp_dir . '/' . $filename;
        file_put_contents($filepath, $pdf_content);
        
        return $base_url . '/pdf-builder-temp/' . $filename;
    }
    
    private static function convertImageToBase64(string $image_url): string {
        try {
            error_log('[PREVIEW] Tentative téléchargement image pour: ' . $image_url);
            
            // Si c'est déjà un chemin local, retourner tel quel
            if (strpos($image_url, 'http') !== 0) {
                error_log('[PREVIEW] Image est déjà un chemin local');
                return $image_url;
            }
            
            // Télécharger l'image
            $response = wp_remote_get($image_url, [
                'timeout' => 10,
                'sslverify' => false
            ]);
            
            if (is_wp_error($response)) {
                error_log('[PREVIEW] Erreur wp_remote_get: ' . $response->get_error_message());
                return '';
            }
            
            $image_data = wp_remote_retrieve_body($response);
            if (empty($image_data)) {
                error_log('[PREVIEW] Image data vide');
                return '';
            }
            
            error_log('[PREVIEW] Image téléchargée, taille: ' . strlen($image_data) . ' bytes');
            
            // Déterminer l'extension depuis l'URL
            $path_info = pathinfo($image_url);
            $ext = strtolower($path_info['extension'] ?? 'png');
            error_log('[PREVIEW] Extension détectée: ' . $ext);
            
            // Créer le répertoire temp s'il n'existe pas
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';
            if (!is_dir($temp_dir)) {
                @mkdir($temp_dir, 0755, true);
            }
            
            // Sauver l'image temporairement avec un nom unique
            $temp_filename = 'img-' . uniqid() . '.' . $ext;
            $temp_path = $temp_dir . '/' . $temp_filename;
            
            if (file_put_contents($temp_path, $image_data) === false) {
                error_log('[PREVIEW] Impossible d\'écrire l\'image: ' . $temp_path);
                return '';
            }
            
            // Retourner l'URL absolue de l'image temporaire
            $temp_url = $upload_dir['baseurl'] . '/pdf-builder-temp/' . $temp_filename;
            error_log('[PREVIEW] Image sauvegardée: ' . $temp_url);
            
            return $temp_url;
            
        } catch (Exception $e) {
            error_log('[PREVIEW] Exception: ' . $e->getMessage());
            return '';
        }
    }
}
