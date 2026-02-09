<?php
/**
 * Customer info element generator
 */

namespace PDF_Builder\HTMLGenerators\Generators;

use PDF_Builder\HTMLGenerators\ElementGeneratorBase;

class CustomerInfoGenerator extends ElementGeneratorBase
{
    public function generateHTML()
    {
        $styles = $this->getElementStyles();
        $styleAttr = $this->createStyleString($styles);

        // Get display configuration
        $displayConfig = [
            'name' => $this->getProperty('showName', true) !== false,
            'email' => $this->getProperty('showEmail', true) !== false,
            'phone' => $this->getProperty('showPhone', true) !== false,
            'address' => $this->getProperty('showAddress', true) !== false,
        ];

        // Get customer data
        $customerData = [
            'name' => $this->orderData['customer_name'] ?? 'Client',
            'email' => $this->orderData['customer_email'] ?? '',
            'phone' => $this->orderData['customer_phone'] ?? '',
            'address' => $this->orderData['customer_address'] ?? '',
        ];

        $fontSize = $this->getProperty('fontSize', 12);

        $html = '<div class="pdf-element pdf-customer-info" ' . $styleAttr . '>';

        if ($displayConfig['name'] && !empty($customerData['name'])) {
            $html .= '<div style="font-weight:bold; font-size:' . ($fontSize + 2) . 'px; margin-bottom:5px;">';
            $html .= htmlspecialchars($customerData['name']);
            $html .= '</div>';
        }

        if ($displayConfig['address'] && !empty($customerData['address'])) {
            $html .= '<div style="font-size:' . $fontSize . 'px; margin-bottom:3px;">';
            $html .= htmlspecialchars($customerData['address']);
            $html .= '</div>';
        }

        if ($displayConfig['email'] && !empty($customerData['email'])) {
            $html .= '<div style="font-size:' . $fontSize . 'px; margin-bottom:3px;">';
            $html .= htmlspecialchars($customerData['email']);
            $html .= '</div>';
        }

        if ($displayConfig['phone'] && !empty($customerData['phone'])) {
            $html .= '<div style="font-size:' . $fontSize . 'px; margin-bottom:3px;">';
            $html .= htmlspecialchars($customerData['phone']);
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
