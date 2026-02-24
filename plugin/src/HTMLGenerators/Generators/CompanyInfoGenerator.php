<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * Company info element generator
 */

namespace PDF_Builder\HTMLGenerators\Generators;

use PDF_Builder\HTMLGenerators\ElementGeneratorBase;

class CompanyInfoGenerator extends ElementGeneratorBase
{
    public function generateHTML()
    {
        $styles = $this->getElementStyles();
        $styleAttr = $this->createStyleString($styles);

        // Get display configuration
        $displayConfig = [
            'companyName' => $this->getProperty('showCompanyName', true) !== false,
            'address' => $this->getProperty('showAddress', true) !== false,
            'phone' => $this->getProperty('showPhone', true) !== false,
            'email' => $this->getProperty('showEmail', true) !== false,
            'siret' => $this->getProperty('showSiret', true) !== false,
            'vat' => $this->getProperty('showVat', true) !== false,
            'rcs' => $this->getProperty('showRcs', true) !== false,
            'capital' => $this->getProperty('showCapital', true) !== false,
        ];

        // Get company data - first from element, then from global settings
        $companyData = $this->getCompanyData();

        // Theme styling
        $theme = $this->getTheme();
        $bgColor = $this->normalizeColor($this->getProperty('backgroundColor', $theme['backgroundColor']));
        $borderColor = $this->normalizeColor($this->getProperty('borderColor', $theme['borderColor']));
        $textColor = $this->normalizeColor($this->getProperty('textColor', $theme['textColor']));
        $headerTextColor = $this->normalizeColor($this->getProperty('headerTextColor', $theme['headerTextColor']));

        // Font configuration
        $headerFontSize = $this->getProperty('headerFontSize', 14);
        $bodyFontSize = $this->getProperty('bodyFontSize', 12);

        $html = '<div class="pdf-element pdf-company-info" ' . $styleAttr . '>';

        // Background
        if ($this->getProperty('showBackground', true) !== false) {
            $html .= '<div style="position:absolute; top:0; left:0; width:100%; height:100%; background-color:' . $bgColor . '; z-index:-1;"></div>';
        }

        // Content wrapper
        $html .= '<div style="padding:10px; height:100%; overflow:hidden;">';

        // Company name
        if ($displayConfig['companyName'] && $this->hasValue($companyData['name'])) {
            $html .= '<div style="font-size:' . $headerFontSize . 'px; font-weight:bold; color:' . $headerTextColor . '; margin-bottom:5px;">';
            $html .= htmlspecialchars($companyData['name']);
            $html .= '</div>';
        }

        // Address
        if ($displayConfig['address']) {
            $address = '';
            if ($this->hasValue($companyData['address'])) {
                $address .= htmlspecialchars($companyData['address']);
            }
            if ($this->hasValue($companyData['city'])) {
                if (!empty($address)) {
                    $address .= ', ';
                }
                $address .= htmlspecialchars($companyData['city']);
            }

            if (!empty($address)) {
                $html .= '<div style="font-size:' . $bodyFontSize . 'px; color:' . $textColor . '; margin-bottom:3px;">';
                $html .= $address;
                $html .= '</div>';
            }
        }

        // Other fields
        $fields = [
            ['value' => $companyData['siret'], 'show' => $displayConfig['siret'], 'label' => 'SIRET'],
            ['value' => $companyData['tva'], 'show' => $displayConfig['vat'], 'label' => 'TVA'],
            ['value' => $companyData['rcs'], 'show' => $displayConfig['rcs'], 'label' => 'RCS'],
            ['value' => $companyData['capital'], 'show' => $displayConfig['capital'], 'label' => 'Capital'],
            ['value' => $companyData['phone'], 'show' => $displayConfig['phone'], 'label' => 'Tél'],
            ['value' => $companyData['email'], 'show' => $displayConfig['email'], 'label' => 'Email'],
        ];

        foreach ($fields as $field) {
            if ($field['show'] && $this->hasValue($field['value'])) {
                $html .= '<div style="font-size:' . $bodyFontSize . 'px; color:' . $textColor . '; margin-bottom:2px;">';
                $html .= htmlspecialchars($field['value']);
                $html .= '</div>';
            }
        }

        $html .= '</div>';

        // Border
        if ($this->getProperty('showBorders', true) !== false) {
            $borderWidth = $this->getProperty('borderWidth', 1);
            $html .= '<div style="position:absolute; top:0; left:0; width:100%; height:100%; border:' . $borderWidth . 'px solid ' . $borderColor . '; box-sizing:border-box; pointer-events:none;"></div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get company data from element or global settings
     */
    private function getCompanyData()
    {
        return [
            'name' => $this->getProperty('companyName', '') ?: get_option('pdf_builder_company_name', ''),
            'address' => $this->getProperty('companyAddress', '') ?: get_option('pdf_builder_company_address', ''),
            'city' => $this->getProperty('companyCity', '') ?: get_option('pdf_builder_company_city', ''),
            'siret' => $this->getProperty('companySiret', '') ?: get_option('pdf_builder_company_siret', ''),
            'tva' => $this->getProperty('companyTva', '') ?: get_option('pdf_builder_company_tva', ''),
            'rcs' => $this->getProperty('companyRcs', '') ?: get_option('pdf_builder_company_rcs', ''),
            'capital' => $this->getProperty('companyCapital', '') ?: get_option('pdf_builder_company_capital', ''),
            'phone' => $this->getProperty('companyPhone', '') ?: get_option('pdf_builder_company_phone', ''),
            'email' => $this->getProperty('companyEmail', '') ?: get_option('pdf_builder_company_email', ''),
        ];
    }

    /**
     * Get theme configuration
     */
    private function getTheme()
    {
        $themeName = $this->getProperty('theme', 'corporate');
        
        $themes = [
            'corporate' => [
                'backgroundColor' => '#f5f5f5',
                'borderColor' => '#333333',
                'textColor' => '#333333',
                'headerTextColor' => '#000000',
            ],
            'minimal' => [
                'backgroundColor' => '#ffffff',
                'borderColor' => '#cccccc',
                'textColor' => '#666666',
                'headerTextColor' => '#333333',
            ],
            'modern' => [
                'backgroundColor' => '#f9f9f9',
                'borderColor' => '#0066cc',
                'textColor' => '#444444',
                'headerTextColor' => '#0066cc',
            ],
        ];

        return $themes[$themeName] ?? $themes['corporate'];
    }

    /**
     * Check if a value is valid/not empty
     */
    private function hasValue($value)
    {
        return !empty($value) && $value !== 'Non indiqué';
    }
}
