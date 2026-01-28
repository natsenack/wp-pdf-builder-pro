<?php

/**
 * PDF Builder Pro - WooCommerceRenderer
 * Phase 3.3.6 - Renderer spécialisé pour les éléments WooCommerce
 *
 * Gère le rendu des éléments WooCommerce :
 * - woocommerce_order_date : Date de commande formatée
 * - woocommerce_invoice_number : Numéro de facture formaté
 */

namespace PDF_Builder\Renderers;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

// Système de cache supprimé - génération directe des styles

class WooCommerceRenderer
{
    /**
     * Types d'éléments supportés par ce renderer
     */
    const SUPPORTED_TYPES = ['woocommerce_order_date', 'woocommerce_invoice_number'];

    /**
     * Styles CSS par défaut pour les éléments WooCommerce
     */
    const DEFAULT_STYLES = [
        'font-family' => 'Arial, sans-serif',
        'font-size' => '12px',
        'color' => '#000000',
        'text-align' => 'left',
        'line-height' => '1.4'
    ];

    /**
     * Rend un élément WooCommerce
     *
     * @param array $elementData Données de l'élément
     * @return string HTML rendu
     */
    public function render(array $elementData): string
    {
        $type = $elementData['type'] ?? '';

        switch ($type) {
            case 'woocommerce_order_date':
                return $this->renderOrderDate($elementData);
            case 'woocommerce_invoice_number':
                return $this->renderInvoiceNumber($elementData);
            default:
                return "<div class=\"pdf-element woocommerce-element\">Type non supporté: {$type}</div>";
        }
    }

    /**
     * Rend un élément woocommerce_order_date
     *
     * @param array $elementData Données de l'élément
     * @return string HTML rendu
     */
    private function renderOrderDate(array $elementData): string
    {
        // Construction du style CSS
        $style = $this->buildElementStyle($elementData);

        // Accès standardisé aux propriétés (directes uniquement)
        $date = $elementData['date'] ?? $elementData['orderDate'] ?? $elementData['order_date'] ?? date('d/m/Y');
        $dateFormat = $elementData['dateFormat'] ?? $elementData['format'] ?? 'd/m/Y';
        $showTime = $elementData['showTime'] ?? $elementData['time'] ?? $elementData['show_time'] ?? false;

        // Formatage de la date
        $formattedDate = date($dateFormat, strtotime($date));
        if ($showTime) {
            $formattedDate .= ' ' . date('H:i:s', strtotime($date));
        }

        return "<div class=\"pdf-element woocommerce-order-date\" style=\"{$style}\">{$formattedDate}</div>";
    }

    /**
     * Rend un élément woocommerce_invoice_number
     *
     * @param array $elementData Données de l'élément
     * @return string HTML rendu
     */
    private function renderInvoiceNumber(array $elementData): string
    {
        // Construction du style CSS
        $style = $this->buildElementStyle($elementData);

        // Accès standardisé aux propriétés (directes uniquement)
        $prefix = $elementData['prefix'] ?? '';
        $suffix = $elementData['suffix'] ?? '';
        $orderNumber = $elementData['orderNumber'] ?? $elementData['order_number'] ?? '12345';

        // Formatage du numéro de facture
        $invoiceNumber = $prefix . $orderNumber . $suffix;

        return "<div class=\"pdf-element woocommerce-invoice-number\" style=\"{$style}\">{$invoiceNumber}</div>";
    }

    /**
     * Construit le style CSS pour un élément
     *
     * @param array $elementData Données de l'élément
     * @return string Style CSS
     */
    private function buildElementStyle(array $elementData): string
    {
        $styles = self::DEFAULT_STYLES;

        // Application des propriétés de style directes
        if (isset($elementData['fontFamily'])) {
            $styles['font-family'] = $elementData['fontFamily'];
        }
        if (isset($elementData['fontSize'])) {
            $styles['font-size'] = $elementData['fontSize'] . 'px';
        }
        if (isset($elementData['color'])) {
            $styles['color'] = $elementData['color'];
        }
        if (isset($elementData['fontWeight'])) {
            $styles['font-weight'] = $elementData['fontWeight'];
        }
        if (isset($elementData['fontStyle'])) {
            $styles['font-style'] = $elementData['fontStyle'];
        }
        if (isset($elementData['textAlign'])) {
            $styles['text-align'] = $elementData['textAlign'];
        }

        // Construction de la chaîne de style
        $styleParts = [];
        foreach ($styles as $property => $value) {
            $styleParts[] = "{$property}:{$value}";
        }

        return implode(';', $styleParts);
    }
}