<?php

namespace WP_PDF_Builder_Pro\Elements;

/**
 * Classe abstraite BaseElement
 * Implémentation de base commune à tous les éléments
 */
abstract class BaseElement implements ElementInterface
{
    /** @var string ID unique de l'élément */
    protected $id;
    /** @var array Propriétés de position */
    protected $position = [];
    /** @var array Propriétés de style */
    protected $style = [];
    /** @var bool Visibilité de l'élément */
    protected $visible = true;
    /** @var bool Élément verrouillé */
    protected $locked = false;

    /**
     * Constructeur de base
     *
     * @param string $id ID de l'élément
     * @param array $data Données d'initialisation
     */
    public function __construct(string $id, array $data = [])
    {
        $this->id = $id;
        if (isset($data['position'])) {
            $this->setPosition($data['position']);
        }

        if (isset($data['style'])) {
            $this->setStyle($data['style']);
        }

        if (isset($data['visible'])) {
            $this->visible = (bool) $data['visible'];
        }

        if (isset($data['locked'])) {
            $this->locked = (bool) $data['locked'];
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPosition(): array
    {
        return $this->position;
    }

    public function setPosition(array $position): void
    {
        $this->position = array_merge($this->position, $position);
    }

    public function getStyle(): array
    {
        return $this->style;
    }

    public function setStyle(array $style): void
    {
        $this->style = array_merge($this->style, $style);
    }

    public function validate(): bool
    {
        return !empty($this->id) && is_array($this->position) && is_array($this->style);
    }

    /**
     * Génère le style CSS inline pour l'élément
     *
     * @return string Style CSS
     */
    protected function generateStyle(): string
    {
        $css = '';
        // Position
        if (isset($this->position['x'])) {
            $css .= "left: {$this->position['x']}px; ";
        }
        if (isset($this->position['y'])) {
            $css .= "top: {$this->position['y']}px; ";
        }
        if (isset($this->position['width'])) {
            $css .= "width: {$this->position['width']}px; ";
        }
        if (isset($this->position['height'])) {
            $css .= "height: {$this->position['height']}px; ";
        }

        // Style de base
        if (isset($this->style['color'])) {
            $css .= "color: {$this->style['color']}; ";
        }
        if (isset($this->style['fontSize'])) {
            $css .= "font-size: {$this->style['fontSize']}px; ";
        }
        if (isset($this->style['fontWeight'])) {
            $css .= "font-weight: {$this->style['fontWeight']}; ";
        }
        if (isset($this->style['textAlign'])) {
            $css .= "text-align: {$this->style['textAlign']}; ";
        }

        // Visibilité
        if (!$this->visible) {
            $css .= "display: none; ";
        }

        return $css;
    }

    /**
     * Génère les attributs HTML communs
     *
     * @return string Attributs HTML
     */
    protected function generateAttributes(): string
    {
        $attrs = "id=\"{$this->id}\" class=\"pdf-element {$this->getType()}-element\"";
        if ($this->locked) {
            $attrs .= " data-locked=\"true\"";
        }

        return $attrs;
    }
}
