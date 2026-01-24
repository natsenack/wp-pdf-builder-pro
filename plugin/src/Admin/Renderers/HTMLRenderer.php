<?php

/**
 * PDF Builder Pro - HTML Renderer
 * Responsable de la génération du HTML pour les PDFs
 */

namespace PDF_Builder\Admin\Renderers;

use Exception;

/**
 * Classe responsable du rendu HTML des templates PDF
 */
class HTMLRenderer
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
     * Génère le HTML unifié pour un template
     */
    public function generateUnifiedHtml($template, $order = null)
    {
        // Cette méthode sera déplacée depuis PDF_Builder_Admin.php
        // Pour l'instant, déléguer à la classe principale
        return $this->admin->generateUnifiedHtml($template, $order);
    }

    /**
     * Génère le HTML du tableau de produits
     */
    public function generateOrderProductsTable($order, $table_style = 'default', $element = null)
    {
        // Récupérer les options depuis l'élément
        $show_headers = isset($element['showHeaders']) ? (bool)$element['showHeaders'] : true;
        $show_borders = isset($element['showBorders']) ? (bool)$element['showBorders'] : true;
        $show_subtotal = isset($element['showSubtotal']) ? (bool)$element['showSubtotal'] : false;
        $show_shipping = isset($element['showShipping']) ? (bool)$element['showShipping'] : true;
        $show_taxes = isset($element['showTaxes']) ? (bool)$element['showTaxes'] : true;
        $show_discount = isset($element['showDiscount']) ? (bool)$element['showDiscount'] : true;
        $show_total = isset($element['showTotal']) ? (bool)$element['showTotal'] : true;
        // Récupérer les colonnes à afficher depuis l'élément
        $columns = isset($element['columns']) && is_array($element['columns']) ? $element['columns'] : [
            'image' => false,
            'name' => true,
            'sku' => false,
            'quantity' => true,
            'price' => true,
            'total' => true
        ];
        // Définir les styles de tableau disponibles (même que dans pdf-generator.php)
        $table_styles = [
            'default' => [
                'header_bg' => ['r' => 248, 'g' => 249, 'b' => 250], // #f8f9fa
                'header_border' => ['r' => 226, 'g' => 232, 'b' => 240], // #e2e8f0
                'row_border' => ['r' => 241, 'g' => 245, 'b' => 249], // #f1f5f9
                'alt_row_bg' => ['r' => 250, 'g' => 251, 'b' => 252], // #fafbfc
                'headerTextColor' => '#000000',
                'rowTextColor' => '#000000',
                'border_width' => 1,
                'headerFontWeight' => 'bold',
                'headerFontSize' => '12px',
                'rowFontSize' => '11px'
            ],
            'classic' => [
                'header_bg' => ['r' => 30, 'g' => 41, 'b' => 59], // #1e293b
                'header_border' => ['r' => 51, 'g' => 65, 'b' => 85], // #334155
                'row_border' => ['r' => 51, 'g' => 65, 'b' => 85], // #334155
                'alt_row_bg' => ['r' => 255, 'g' => 255, 'b' => 255], // #ffffff
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#1e293b',
                'border_width' => 1.5,
                'headerFontWeight' => '700',
                'headerFontSize' => '11px',
                'rowFontSize' => '10px'
            ],
            'blue' => [
                'header_bg' => ['r' => 59, 'g' => 130, 'b' => 246], // #3b82f6
                'header_border' => ['r' => 37, 'g' => 99, 'b' => 235], // #2563eb
                'row_border' => ['r' => 226, 'g' => 232, 'b' => 240], // #e2e8f0
                'alt_row_bg' => ['r' => 248, 'g' => 249, 'b' => 250], // #f8fafc
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#1e293b',
                'border_width' => 1,
                'headerFontWeight' => 'bold',
                'headerFontSize' => '11px',
                'rowFontSize' => '10px'
            ],
            'minimal' => [
                'header_bg' => ['r' => 255, 'g' => 255, 'b' => 255], // #ffffff
                'header_border' => ['r' => 55, 'g' => 65, 'b' => 81], // #374151
                'row_border' => ['r' => 209, 'g' => 213, 'b' => 219], // #d1d5db
                'alt_row_bg' => ['r' => 255, 'g' => 255, 'b' => 255], // #ffffff
                'headerTextColor' => '#374151',
                'rowTextColor' => '#374151',
                'border_width' => 1,
                'headerFontWeight' => '600',
                'headerFontSize' => '11px',
                'rowFontSize' => '10px'
            ],
            'light' => [
                'header_bg' => ['r' => 255, 'g' => 255, 'b' => 255], // #ffffff
                'header_border' => ['r' => 243, 'g' => 244, 'b' => 246], // #f3f4f6
                'row_border' => ['r' => 249, 'g' => 250, 'b' => 251], // #f9fafb
                'alt_row_bg' => ['r' => 255, 'g' => 255, 'b' => 255], // #ffffff
                'headerTextColor' => '#1e293b',
                'rowTextColor' => '#1e293b',
                'border_width' => 1,
                'headerFontWeight' => '500',
                'headerFontSize' => '11px',
                'rowFontSize' => '10px'
            ],
            'emerald_forest' => [
                'header_bg' => ['r' => 6, 'g' => 78, 'b' => 59], // #064e3b (moyenne du gradient)
                'header_border' => ['r' => 6, 'g' => 95, 'b' => 70], // #065f46
                'row_border' => ['r' => 209, 'g' => 250, 'b' => 229], // #d1fae5
                'alt_row_bg' => ['r' => 236, 'g' => 253, 'b' => 245], // #ecfdf5
                'headerTextColor' => '#ffffff',
                'rowTextColor' => '#064e3b',
                'border_width' => 1.5,
                'headerFontWeight' => '600',
                'headerFontSize' => '11px',
                'rowFontSize' => '10px'
            ]
        ];
        // Utiliser le style demandé ou default si non trouvé
        $style = isset($table_styles[$table_style]) ? $table_styles[$table_style] : $table_styles['default'];
        // Fonction helper pour convertir RGB en couleur CSS
        $rgb_to_css = function ($rgb) {

            return sprintf('rgb(%d, %d, %d)', $rgb['r'], $rgb['g'], $rgb['b']);
        };
        // Déterminer la largeur des bordures selon showBorders
        $border_width = $show_borders ? $style['border_width'] : 0;
        $row_border_color = $rgb_to_css($style['row_border']);
        // Styles CSS pour le tableau
        $table_style_css = sprintf('width: 100%%; border-collapse: collapse;%s', $show_borders ? ' border: ' . $border_width . 'px solid ' . $row_border_color . ';' : '');
        $header_style_css = sprintf('background-color: %s; color: %s;%s padding: 6px 8px; font-weight: %s; font-size: %s; text-align: left;', $rgb_to_css($style['header_bg']), $style['headerTextColor'], $show_borders ? ' border: ' . $border_width . 'px solid ' . $rgb_to_css($style['header_border']) . ';' : '', $style['headerFontWeight'], $style['headerFontSize']);
        $cell_style_css = sprintf('%s padding: 6px 8px; font-size: %s; color: %s;', $show_borders ? 'border: ' . $border_width . 'px solid ' . $row_border_color . ';' : '', $style['rowFontSize'], $style['rowTextColor']);
        $alt_row_style_css = $cell_style_css . sprintf(' background-color: %s;', $rgb_to_css($style['alt_row_bg']));
        $html = '<table style="' . $table_style_css . '">';
        // Entête du tableau (si activée)
        if ($show_headers) {
            $html .= '<thead><tr>';
            if ($columns['image'] ?? false) {
                $html .= '<th style="' . $header_style_css . '">Image</th>';
            }
            if ($columns['name'] ?? true) {
                $html .= '<th style="' . $header_style_css . '">Produit</th>';
            }
            if ($columns['sku'] ?? false) {
                $html .= '<th style="' . $header_style_css . '">SKU</th>';
            }
            if ($columns['quantity'] ?? true) {
                $html .= '<th style="' . $header_style_css . '">Qté</th>';
            }
            if ($columns['price'] ?? true) {
                $html .= '<th style="' . $header_style_css . '">Prix</th>';
            }
            if ($columns['total'] ?? true) {
                $html .= '<th style="' . $header_style_css . '">Total</th>';
            }
            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';
        $row_count = 0;
        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $row_style = ($row_count % 2 == 1) ? $alt_row_style_css : $cell_style_css;
            $html .= '<tr>';
            if ($columns['image'] ?? false) {
                $image_url = $product ? wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') : '';
                $html .= '<td style="' . $row_style . ' text-align: center;">';
                if ($image_url) {
                    $html .= '<img src="' . esc_url($image_url) . '" style="max-width: 40px; max-height: 40px; object-fit: contain;" />';
                }
                $html .= '</td>';
            }

            if ($columns['name'] ?? true) {
                $html .= '<td style="' . $row_style . '">' . esc_html($item->get_name()) . '</td>';
            }

            if ($columns['sku'] ?? false) {
                $sku = $product ? $product->get_sku() : '';
                $html .= '<td style="' . $row_style . ' text-align: center;">' . esc_html($sku) . '</td>';
            }

            if ($columns['quantity'] ?? true) {
                $html .= '<td style="' . $row_style . ' text-align: center;">' . $item->get_quantity() . '</td>';
            }

            if ($columns['price'] ?? true) {
                $price = function_exists('wc_price') ? call_user_func('wc_price', $item->get_total() / $item->get_quantity()) : '$' . number_format($item->get_total() / $item->get_quantity(), 2);
                $html .= '<td style="' . $row_style . ' text-align: right;">' . $price . '</td>';
            }

            if ($columns['total'] ?? true) {
                $total = function_exists('wc_price') ? call_user_func('wc_price', $item->get_total()) : '$' . number_format($item->get_total(), 2);
                $html .= '<td style="' . $row_style . ' text-align: right;">' . $total . '</td>';
            }

            $html .= '</tr>';
            $row_count++;
        }

        // Ajouter les frais de commande personnalisés
        foreach ($order->get_fees() as $fee) {
            $fee_name = $fee->get_name();
            $fee_total = $fee->get_total();
            $row_style = ($row_count % 2 == 1) ? $alt_row_style_css : $cell_style_css;
            $html .= '<tr>';
            if ($columns['image'] ?? false) {
                $html .= '<td style="' . $row_style . '"></td>';
            }
            if ($columns['name'] ?? true) {
                $html .= '<td style="' . $row_style . ' font-weight: bold;">' . esc_html($fee_name) . '</td>';
            }
            if ($columns['sku'] ?? false) {
                $html .= '<td style="' . $row_style . '"></td>';
            }
            if ($columns['quantity'] ?? true) {
                $html .= '<td style="' . $row_style . ' text-align: center;">-</td>';
            }
            if ($columns['price'] ?? true) {
                $html .= '<td style="' . $row_style . ' text-align: right;">-</td>';
            }
            if ($columns['total'] ?? true) {
                $fee_price = function_exists('wc_price') ? call_user_func('wc_price', $fee_total) : '$' . number_format($fee_total, 2);
                $html .= '<td style="' . $row_style . ' text-align: right; font-weight: bold;">' . $fee_price . '</td>';
            }
            $html .= '</tr>';
            $row_count++;
        }

        // Ajouter les lignes de totaux si demandées
        if ($show_subtotal) {
            $row_style = $cell_style_css;
            $html .= '<tr>';
            $colspan = 0;
            if ($columns['image'] ?? false) {
                $colspan++;
            }
            if ($columns['name'] ?? true) {
                $colspan++;
            }
            if ($columns['sku'] ?? false) {
                $colspan++;
            }
            if ($columns['quantity'] ?? true) {
                $colspan++;
            }
            if ($columns['price'] ?? true) {
                $colspan++;
            }

            if ($colspan > 0) {
                $html .= '<td colspan="' . $colspan . '" style="' . $row_style . ' text-align: right; font-weight: bold;">Sous-total:</td>';
            }
            if ($columns['total'] ?? true) {
                $subtotal = function_exists('wc_price') ? call_user_func('wc_price', $order->get_subtotal()) : '$' . number_format($order->get_subtotal(), 2);
                $html .= '<td style="' . $row_style . ' text-align: right; font-weight: bold;">' . $subtotal . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Remplace les variables de commande
     */
    public function replaceOrderVariables($content, $order)
    {
        // Préparer les données de la commande
        $billing_address = $this->formatAddress($order, 'billing');
        $shipping_address = $this->formatAddress($order, 'shipping');
        // Détecter le type de document
        $order_status = $order->get_status();
        $document_type = $this->admin->data_utils->detectDocumentType($order_status);
        $document_type_label = $this->admin->data_utils->getDocumentTypeLabel($document_type);
        // Variables avec doubles accolades {{variable}}
        $double_brace_replacements = array(
            '{{order_id}}' => $order->get_id(),
            '{{order_number}}' => $order->get_order_number(),
            '{{order_date}}' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y') : date('d/m/Y'),
            '{{order_date_time}}' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y H:i:s') : date('d/m/Y H:i:s'),
            '{{customer_name}}' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            '{{customer_first_name}}' => $order->get_billing_first_name(),
            '{{customer_last_name}}' => $order->get_billing_last_name(),
            '{{customer_email}}' => $order->get_billing_email(),
            '{{customer_phone}}' => $order->get_billing_phone(),
            '{{billing_company}}' => $order->get_billing_company(),
            '{{billing_address_1}}' => $order->get_billing_address_1(),
            '{{billing_address_2}}' => $order->get_billing_address_2(),
            '{{billing_city}}' => $order->get_billing_city(),
            '{{billing_state}}' => $order->get_billing_state(),
            '{{billing_postcode}}' => $order->get_billing_postcode(),
            '{{billing_country}}' => $order->get_billing_country(),
            '{{billing_address}}' => $billing_address ?: 'Adresse de facturation non disponible',
            '{{complete_customer_info}}' => $this->formatCompleteCustomerInfo($order),
            '{{complete_billing_address}}' => $billing_address ?: 'Adresse de facturation non disponible',
            '{{shipping_first_name}}' => $order->get_shipping_first_name(),
            '{{shipping_last_name}}' => $order->get_shipping_last_name(),
            '{{shipping_company}}' => $order->get_shipping_company(),
            '{{shipping_address_1}}' => $order->get_shipping_address_1(),
            '{{shipping_address_2}}' => $order->get_shipping_address_2(),
            '{{shipping_city}}' => $order->get_shipping_city(),
            '{{shipping_state}}' => $order->get_shipping_state(),
            '{{shipping_postcode}}' => $order->get_shipping_postcode(),
            '{{shipping_country}}' => $order->get_shipping_country(),
            '{{shipping_address}}' => $shipping_address ?: 'Adresse de livraison non disponible',
            '{{total}}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_total()) : '$' . number_format($order->get_total(), 2),
            '{{subtotal}}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_subtotal()) : '$' . number_format($order->get_subtotal(), 2),
            '{{tax}}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_total_tax()) : '$' . number_format($order->get_total_tax(), 2),
            '{{shipping_total}}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_shipping_total()) : '$' . number_format($order->get_shipping_total(), 2),
            '{{discount_total}}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_discount_total()) : '$' . number_format($order->get_discount_total(), 2),
            '{{payment_method}}' => $order->get_payment_method_title(),
            '{{order_status}}' => function_exists('wc_get_order_status_name') ? call_user_func('wc_get_order_status_name', $order->get_status()) : $order->get_status(),
            '{{currency}}' => $order->get_currency(),
            '{{document_type}}' => $document_type,
            '{{document_type_label}}' => $document_type_label,
        );
        // Variables avec crochets [variable]
        $bracket_replacements = array(
            '[order_id]' => $order->get_id(),
            '[order_number]' => $order->get_order_number(),
            '[order_date]' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y') : date('d/m/Y'),
            '[order_date_time]' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y H:i:s') : date('d/m/Y H:i:s'),
            '[customer_name]' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            '[billing_first_name]' => $order->get_billing_first_name(),
            '[billing_last_name]' => $order->get_billing_last_name(),
            '[billing_company]' => $order->get_billing_company(),
            '[billing_address_1]' => $order->get_billing_address_1(),
            '[billing_address_2]' => $order->get_billing_address_2(),
            '[billing_city]' => $order->get_billing_city(),
            '[billing_state]' => $order->get_billing_state(),
            '[billing_postcode]' => $order->get_billing_postcode(),
            '[billing_country]' => $order->get_billing_country(),
            '[billing_address]' => $billing_address ?: 'Adresse de facturation non disponible',
            '[complete_customer_info]' => $this->formatCompleteCustomerInfo($order),
            '[complete_billing_address]' => $billing_address ?: 'Adresse de facturation non disponible',
            '[shipping_first_name]' => $order->get_shipping_first_name(),
            '[shipping_last_name]' => $order->get_shipping_last_name(),
            '[shipping_company]' => $order->get_shipping_company(),
            '[shipping_address_1]' => $order->get_shipping_address_1(),
            '[shipping_address_2]' => $order->get_shipping_address_2(),
            '[shipping_city]' => $order->get_shipping_city(),
            '[shipping_state]' => $order->get_shipping_state(),
            '[shipping_postcode]' => $order->get_shipping_postcode(),
            '[shipping_country]' => $order->get_shipping_country(),
            '[shipping_address]' => $shipping_address ?: 'Adresse de livraison non disponible',
            '[customer_email]' => $order->get_billing_email(),
            '[customer_phone]' => $order->get_billing_phone(),
            '[total]' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_total()) : '$' . number_format($order->get_total(), 2),
            '[subtotal]' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_subtotal()) : '$' . number_format($order->get_subtotal(), 2),
            '[tax]' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_total_tax()) : '$' . number_format($order->get_total_tax(), 2),
            '[shipping_total]' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_shipping_total()) : '$' . number_format($order->get_shipping_total(), 2),
            '[discount_total]' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_discount_total()) : '$' . number_format($order->get_discount_total(), 2),
            '[payment_method]' => $order->get_payment_method_title(),
            '[order_status]' => function_exists('wc_get_order_status_name') ? call_user_func('wc_get_order_status_name', $order->get_status()) : $order->get_status(),
            '[currency]' => $order->get_currency(),
            '[document_type]' => $document_type,
            '[document_type_label]' => $document_type_label,
        );
        // Variables avec accolades simples {variable}
        $single_brace_replacements = array(
            '{order_id}' => $order->get_id(),
            '{order_number}' => $order->get_order_number(),
            '{order_date}' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y') : date('d/m/Y'),
            '{order_date_time}' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y H:i:s') : date('d/m/Y H:i:s'),
            '{customer_name}' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            '{billing_first_name}' => $order->get_billing_first_name(),
            '{billing_last_name}' => $order->get_billing_last_name(),
            '{billing_company}' => $order->get_billing_company(),
            '{billing_address_1}' => $order->get_billing_address_1(),
            '{billing_address_2}' => $order->get_billing_address_2(),
            '{billing_city}' => $order->get_billing_city(),
            '{billing_state}' => $order->get_billing_state(),
            '{billing_postcode}' => $order->get_billing_postcode(),
            '{billing_country}' => $order->get_billing_country(),
            '{billing_address}' => $billing_address ?: 'Adresse de facturation non disponible',
            '{complete_customer_info}' => $this->formatCompleteCustomerInfo($order),
            '{complete_billing_address}' => $billing_address ?: 'Adresse de facturation non disponible',
            '{shipping_first_name}' => $order->get_shipping_first_name(),
            '{shipping_last_name}' => $order->get_shipping_last_name(),
            '{shipping_company}' => $order->get_shipping_company(),
            '{shipping_address_1}' => $order->get_shipping_address_1(),
            '{shipping_address_2}' => $order->get_shipping_address_2(),
            '{shipping_city}' => $order->get_shipping_city(),
            '{shipping_state}' => $order->get_shipping_state(),
            '{shipping_postcode}' => $order->get_shipping_postcode(),
            '{shipping_country}' => $order->get_shipping_country(),
            '{shipping_address}' => $shipping_address ?: 'Adresse de livraison non disponible',
            '{customer_email}' => $order->get_billing_email(),
            '{customer_phone}' => $order->get_billing_phone(),
            '{total}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_total()) : '$' . number_format($order->get_total(), 2),
            '{subtotal}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_subtotal()) : '$' . number_format($order->get_subtotal(), 2),
            '{tax}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_total_tax()) : '$' . number_format($order->get_total_tax(), 2),
            '{shipping_total}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_shipping_total()) : '$' . number_format($order->get_shipping_total(), 2),
            '{discount_total}' => function_exists('wc_price') ? call_user_func('wc_price', $order->get_discount_total()) : '$' . number_format($order->get_discount_total(), 2),
            '{payment_method}' => $order->get_payment_method_title(),
            '{order_status}' => function_exists('wc_get_order_status_name') ? call_user_func('wc_get_order_status_name', $order->get_status()) : $order->get_status(),
            '{currency}' => $order->get_currency(),
            '{order_items_table}' => $this->generateOrderProductsTable($order, 'default'),
            '{document_type}' => $document_type,
            '{document_type_label}' => $document_type_label,
        );
        // Appliquer les remplacements dans l'ordre : simples, doubles, crochets
        $content = str_replace(array_keys($single_brace_replacements), array_values($single_brace_replacements), $content);
        $content = str_replace(array_keys($double_brace_replacements), array_values($double_brace_replacements), $content);
        $content = str_replace(array_keys($bracket_replacements), array_values($bracket_replacements), $content);
        return $content;
    }

    /**
     * Formate les informations complètes du client
     */
    public function formatCompleteCustomerInfo($order)
    {
        $info = [];
        // Nom complet
        $full_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        if (!empty($full_name)) {
            $info[] = $full_name;
        }

        // Société
        $company = $order->get_billing_company();
        if (!empty($company)) {
            $info[] = $company;
        }

        // Adresse complète
        $billing_address = $this->formatAddress($order, 'billing');
        if (!empty($billing_address)) {
            $info[] = $billing_address;
        }

        // Email
        $email = $order->get_billing_email();
        if (!empty($email)) {
            $info[] = 'Email: ' . $email;
        }

        // Téléphone
        $phone = $order->get_billing_phone();
        if (!empty($phone)) {
            $info[] = 'Téléphone: ' . $phone;
        }

        return implode("\n", $info);
    }

    public function formatCompleteCompanyInfo()
    {
        // Essayer d'abord de récupérer depuis l'option personnalisée
        $company_info = get_option('pdf_builder_company_info', '');
        // Si les informations sont configurées manuellement, les utiliser
        if (!empty($company_info)) {
            return $company_info;
        }

        // Sinon, récupérer automatiquement depuis WooCommerce/WordPress
        $company_parts = [];
        // Nom de la société (nom du site WordPress)
        $company_name = get_bloginfo('name');
        if (!empty($company_name)) {
            $company_parts[] = $company_name;
        }

        // Adresse depuis WooCommerce
        $address_parts = [];
        $address1 = get_option('woocommerce_store_address');
        $address2 = get_option('woocommerce_store_address_2');
        $city = get_option('woocommerce_store_city');
        $postcode = get_option('woocommerce_store_postcode');
        $country = get_option('woocommerce_store_country');
        if (!empty($address1)) {
            $address_parts[] = $address1;
        }
        if (!empty($address2)) {
            $address_parts[] = $address2;
        }

        $city_line = [];
        if (!empty($postcode)) {
            $city_line[] = $postcode;
        }
        if (!empty($city)) {
            $city_line[] = $city;
        }
        if (!empty($city_line)) {
            $address_parts[] = implode(' ', $city_line);
        }

        if (!empty($country)) {
            // Convertir le code pays en nom complet si possible
            $wc = call_user_func('WC');
            $countries = $wc ? $wc->countries->get_countries() : [];
            $country_name = isset($countries[$country]) ? $countries[$country] : $country;
            $address_parts[] = $country_name;
        }

        if (!empty($address_parts)) {
            $company_parts = array_merge($company_parts, $address_parts);
        }

        // Email depuis WordPress
        $email = get_bloginfo('admin_email');
        if (!empty($email)) {
            $company_parts[] = 'Email: ' . $email;
        }

        // Si on a au moins le nom, retourner les infos récupérées
        if (!empty($company_parts)) {
            return implode("\n", $company_parts);
        }

        // Sinon, données d'exemple par défaut
        return "Votre Société SARL\n123 Rue de l'Entreprise\n75001 Paris\nFrance\nTél: 01 23 45 67 89\nEmail: contact@votresociete.com";
    }

    public function generateOrderHtml($order, $template_data)
    {
        return $this->admin->generateUnifiedHtml($template_data, $order);
    }

    public function generateHtmlFromTemplateData($template)
    {
        return $this->admin->generateUnifiedHtml($template, null);
    }
    public function replaceWoocommerceVariables($template_data, $woocommerce_data)
    {
        $processed_data = $template_data;
        // Fonction récursive pour remplacer les variables dans toutes les profondeurs
        $replace_vars = function ($data) use ($woocommerce_data, &$replace_vars) {

            if (is_array($data)) {
                $result = [];
                foreach ($data as $key => $value) {
                    $result[$key] = $replace_vars($value);
                }
                return $result;
            } elseif (is_string($data)) {
                // Remplacer les variables du type {order_number}, {customer_name}, etc.
                $replaced = $data;
                foreach ($woocommerce_data as $var => $value) {
                    $replaced = str_replace('{' . $var . '}', $value, $replaced);
                }
                return $replaced;
            } else {
                return $data;
            }
        };
        return $replace_vars($processed_data);
    }

    /**
     * Détection automatique du template basé sur le statut de commande
     */
    /**
     * Parse SQL statements from a string
     *
     * @param  string $sql SQL content to parse
     * @return array<int, string> Array of SQL statements
     */

    /**
     * Formate une adresse manuellement pour éviter l'autoloading WooCommerce
     */
    private function formatAddress($order, $type = 'billing')
    {
        $address_parts = array();

        $company = $type === 'billing' ? $order->get_billing_company() : $order->get_shipping_company();
        if (!empty($company)) {
            $address_parts[] = $company;
        }

        $first_name = $type === 'billing' ? $order->get_billing_first_name() : $order->get_shipping_first_name();
        $last_name = $type === 'billing' ? $order->get_billing_last_name() : $order->get_shipping_last_name();
        if (!empty($first_name) || !empty($last_name)) {
            $address_parts[] = trim($first_name . ' ' . $last_name);
        }

        $address_1 = $type === 'billing' ? $order->get_billing_address_1() : $order->get_shipping_address_1();
        if (!empty($address_1)) {
            $address_parts[] = $address_1;
        }

        $address_2 = $type === 'billing' ? $order->get_billing_address_2() : $order->get_shipping_address_2();
        if (!empty($address_2)) {
            $address_parts[] = $address_2;
        }

        $city = $type === 'billing' ? $order->get_billing_city() : $order->get_shipping_city();
        $postcode = $type === 'billing' ? $order->get_billing_postcode() : $order->get_shipping_postcode();
        $city_line = trim($city . ' ' . $postcode);
        if (!empty($city_line)) {
            $address_parts[] = $city_line;
        }

        $country = $type === 'billing' ? $this->getCountryName($order->get_billing_country()) : $this->getCountryName($order->get_shipping_country());
        if (!empty($country)) {
            $address_parts[] = $country;
        }

        return implode("\n", $address_parts);
    }

    /**
     * Get country name from country code
     */
    private function getCountryName($country_code)
    {
        if (!defined('WC_VERSION') || !$country_code) {
            return $country_code;
        }

        // Use get_option instead of WC()->countries to avoid autoloading
        $countries = get_option('woocommerce_countries', []);
        if (empty($countries)) {
            $countries = get_option('woocommerce_allowed_countries', []);
        }
        return isset($countries[$country_code]) ? $countries[$country_code] : $country_code;
    }
}

