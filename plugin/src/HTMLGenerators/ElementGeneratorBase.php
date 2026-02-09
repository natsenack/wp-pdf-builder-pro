<?php
/**
 * Base class for all element generators
 * Inspired by woo-pdf-invoice-builder architecture
 */

namespace PDF_Builder\HTMLGenerators;

abstract class ElementGeneratorBase
{
    protected $element;
    protected $orderData;
    protected $companyData;

    public function __construct($element, $orderData = [], $companyData = [])
    {
        $this->element = $element;
        $this->orderData = $orderData;
        $this->companyData = $companyData;
    }

    /**
     * Generate HTML for this element
     * Must be implemented by subclasses
     */
    abstract public function generateHTML();

    /**
     * Utility: Create style attribute from array
     */
    protected function createStyleString($styleArray = [])
    {
        if (empty($styleArray)) {
            return '';
        }

        $styles = 'style="';
        foreach ($styleArray as $name => $value) {
            $styles .= htmlspecialchars($name) . ':' . $value . ';';
        }
        $styles .= '"';

        return $styles;
    }

    /**
     * Utility: Get element property value
     */
    protected function getProperty($propertyName, $default = '')
    {
        return $this->element[$propertyName] ?? $default;
    }

    /**
     * Utility: Get element styling
     */
    protected function getElementStyles()
    {
        $styles = [];

        // Position and size
        if (isset($this->element['x'])) {
            $styles['position'] = 'absolute';
            $styles['left'] = $this->element['x'] . 'px';
        }
        if (isset($this->element['y'])) {
            $styles['top'] = $this->element['y'] . 'px';
        }
        if (isset($this->element['width'])) {
            $styles['width'] = $this->element['width'] . 'px';
        }
        if (isset($this->element['height'])) {
            $styles['height'] = $this->element['height'] . 'px';
        }

        // Colors
        if (isset($this->element['backgroundColor'])) {
            $styles['background-color'] = $this->getProperty('backgroundColor');
        }
        if (isset($this->element['textColor'])) {
            $styles['color'] = $this->getProperty('textColor');
        }

        // Font
        if (isset($this->element['fontFamily'])) {
            $styles['font-family'] = $this->getProperty('fontFamily');
        }
        if (isset($this->element['fontSize'])) {
            $styles['font-size'] = $this->getProperty('fontSize') . 'px';
        }
        if (isset($this->element['fontWeight'])) {
            $styles['font-weight'] = $this->getProperty('fontWeight');
        }

        // Borders
        if (isset($this->element['border']) && is_array($this->element['border'])) {
            $border = $this->element['border'];
            if (!empty($border['width']) && !empty($border['color'])) {
                $style = $border['style'] ?? 'solid';
                $styles['border'] = $border['width'] . 'px ' . $style . ' ' . $border['color'];
            }
        }

        // Padding
        if (isset($this->element['padding']) && is_array($this->element['padding'])) {
            $padding = $this->element['padding'];
            $styles['padding'] = ($padding['top'] ?? 0) . 'px ' . 
                                 ($padding['right'] ?? 0) . 'px ' . 
                                 ($padding['bottom'] ?? 0) . 'px ' . 
                                 ($padding['left'] ?? 0) . 'px';
        }

        return $styles;
    }

    /**
     * Utility: Normalize color values
     */
    protected function normalizeColor($color)
    {
        if (empty($color)) {
            return '#000000';
        }

        // If it's already a valid hex color
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return $color;
        }

        // If it's rgb or rgba format, return as is
        if (stripos($color, 'rgb') === 0) {
            return $color;
        }

        return '#000000';
    }
}
