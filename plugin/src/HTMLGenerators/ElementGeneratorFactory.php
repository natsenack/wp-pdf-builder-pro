<?php
/**
 * Factory for creating appropriate element generators
 * Inspired by woo-pdf-invoice-builder FieldFactory
 */

namespace PDF_Builder\HTMLGenerators;

class ElementGeneratorFactory
{
    /**
     * Create the appropriate generator for an element type
     */
    public static function createGenerator($element, $orderData = [], $companyData = [])
    {
        $type = $element['type'] ?? 'text';

        switch ($type) {
            case 'text':
                return new Generators\TextElementGenerator($element, $orderData, $companyData);
            
            case 'company_info':
                return new Generators\CompanyInfoGenerator($element, $orderData, $companyData);
            
            case 'rectangle':
                return new Generators\RectangleElementGenerator($element, $orderData, $companyData);
            
            case 'image':
                return new Generators\ImageElementGenerator($element, $orderData, $companyData);
            
            case 'product_table':
                return new Generators\ProductTableGenerator($element, $orderData, $companyData);
            
            case 'customer_info':
                return new Generators\CustomerInfoGenerator($element, $orderData, $companyData);
            
            case 'line':
                return new Generators\LineElementGenerator($element, $orderData, $companyData);
            
            default:
                // Return a generic text generator for unknown types
                return new Generators\TextElementGenerator($element, $orderData, $companyData);
        }
    }

    /**
     * Generate HTML for multiple elements
     */
    public static function generateMultiple($elements, $orderData = [], $companyData = [])
    {
        $html = '';
        
        if (!is_array($elements)) {
            return $html;
        }

        foreach ($elements as $element) {
            try {
                $generator = self::createGenerator($element, $orderData, $companyData);
                $html .= $generator->generateHTML();
            } catch (\Exception $e) {
                error_log('ElementGeneratorFactory error: ' . $e->getMessage());
                continue;
            }
        }

        return $html;
    }
}
