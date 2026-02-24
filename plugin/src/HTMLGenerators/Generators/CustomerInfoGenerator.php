<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed
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
        // Remove background-color from wrapper — we handle it manually below
        unset($styles['background-color']);
        $styleAttr = $this->createStyleString(array_merge($styles, ['overflow' => 'hidden', 'position' => $styles['position'] ?? 'absolute']));

        // Display configuration
        $displayConfig = [
            'name'    => $this->getProperty('showName', true) !== false,
            'email'   => $this->getProperty('showEmail', true) !== false,
            'phone'   => $this->getProperty('showPhone', true) !== false,
            'address' => $this->getProperty('showAddress', true) !== false,
            'company' => $this->getProperty('showCompany', false) !== false,
        ];

        // Customer data — real order data or fallback preview
        $customerData = [
            'name'    => $this->orderData['customer_name'] ?? 'Client Example',
            'email'   => $this->orderData['customer_email'] ?? 'client@example.com',
            'phone'   => $this->orderData['customer_phone'] ?? '',
            'address' => $this->orderData['customer_address'] ?? '',
            'company' => $this->orderData['customer_company'] ?? '',
        ];

        // Colors & fonts
        $bgColor         = $this->normalizeColor($this->getProperty('backgroundColor', '#ffffff'));
        $borderColor     = $this->normalizeColor($this->getProperty('borderColor', '#e5e7eb'));
        $borderWidth     = (float) $this->getProperty('borderWidth', 1);
        $textColor       = $this->normalizeColor($this->getProperty('textColor', '#374151'));
        $headerTextColor = $this->normalizeColor($this->getProperty('headerTextColor', $textColor));

        $headerFontSize = (int) $this->getProperty('headerFontSize', 13);
        $bodyFontSize   = (int) $this->getProperty('bodyFontSize', 12);

        $showBackground = $this->getProperty('showBackground', true) !== false;
        $showBorders    = $this->getProperty('showBorders', false) !== false;

        $html = '<div class="pdf-element pdf-customer-info" ' . $styleAttr . '>';

        // Background layer
        if ($showBackground) {
            $html .= '<div style="position:absolute;top:0;left:0;width:100%;height:100%;background-color:' . $bgColor . ';z-index:-1;"></div>';
        }

        // Content wrapper with padding
        $html .= '<div style="padding:10px;height:100%;overflow:hidden;">';

        // Customer name (header)
        if ($displayConfig['name'] && !empty($customerData['name'])) {
            $html .= '<div style="font-size:' . $headerFontSize . 'px;font-weight:bold;color:' . $headerTextColor . ';margin-bottom:5px;">';
            $html .= htmlspecialchars($customerData['name']);
            $html .= '</div>';
        }

        // Company
        if ($displayConfig['company'] && !empty($customerData['company'])) {
            $html .= '<div style="font-size:' . $bodyFontSize . 'px;color:' . $textColor . ';margin-bottom:3px;">';
            $html .= htmlspecialchars($customerData['company']);
            $html .= '</div>';
        }

        // Address
        if ($displayConfig['address'] && !empty($customerData['address'])) {
            $lines = explode("\n", $customerData['address']);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $html .= '<div style="font-size:' . $bodyFontSize . 'px;color:' . $textColor . ';margin-bottom:2px;">';
                    $html .= htmlspecialchars($line);
                    $html .= '</div>';
                }
            }
        }

        // Email
        if ($displayConfig['email'] && !empty($customerData['email'])) {
            $html .= '<div style="font-size:' . $bodyFontSize . 'px;color:' . $textColor . ';margin-bottom:2px;">';
            $html .= htmlspecialchars($customerData['email']);
            $html .= '</div>';
        }

        // Phone
        if ($displayConfig['phone'] && !empty($customerData['phone'])) {
            $html .= '<div style="font-size:' . $bodyFontSize . 'px;color:' . $textColor . ';margin-bottom:2px;">';
            $html .= htmlspecialchars($customerData['phone']);
            $html .= '</div>';
        }

        $html .= '</div>'; // end content wrapper

        // Border overlay (on top, pointer-events:none so it doesn't block clicks)
        if ($showBorders) {
            $html .= '<div style="position:absolute;top:0;left:0;width:100%;height:100%;border:' . $borderWidth . 'px solid ' . $borderColor . ';box-sizing:border-box;pointer-events:none;"></div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Normalize a color value
     */
    private function normalizeColor($color)
    {
        if (empty($color) || $color === 'transparent') {
            return $color ?? 'transparent';
        }
        // Ensure hex colors have #
        if (ctype_xdigit($color) && (strlen($color) === 6 || strlen($color) === 3)) {
            return '#' . $color;
        }
        return $color;
    }
}
