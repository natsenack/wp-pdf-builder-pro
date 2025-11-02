<?php
namespace WP_PDF_Builder_Pro\Elements;

/**
 * Interface ElementInterface
 * Définit le contrat pour tous les éléments de template
 */
interface ElementInterface {

    /**
     * Récupère le type de l'élément
     *
     * @return string Type de l'élément (text, image, rectangle, etc.)
     */
    public function getType(): string;

    /**
     * Récupère l'ID unique de l'élément
     *
     * @return string ID de l'élément
     */
    public function getId(): string;

    /**
     * Valide les données de l'élément
     *
     * @return bool true si valide, false sinon
     */
    public function validate(): bool;

    /**
     * Rend l'élément en HTML
     *
     * @return string HTML de l'élément
     */
    public function render(): string;

    /**
     * Récupère les propriétés de positionnement
     *
     * @return array Propriétés de position (x, y, width, height)
     */
    public function getPosition(): array;

    /**
     * Définit les propriétés de positionnement
     *
     * @param array $position Propriétés de position
     */
    public function setPosition(array $position): void;

    /**
     * Récupère les propriétés de style
     *
     * @return array Propriétés de style (color, fontSize, etc.)
     */
    public function getStyle(): array;

    /**
     * Définit les propriétés de style
     *
     * @param array $style Propriétés de style
     */
    public function setStyle(array $style): void;
}

/**
 * Interface TextElementInterface
 * Contrat spécifique pour les éléments texte
 */
interface TextElementInterface extends ElementInterface {

    /**
     * Récupère le contenu texte
     *
     * @return string Contenu texte
     */
    public function getText(): string;

    /**
     * Définit le contenu texte
     *
     * @param string $text Contenu texte
     */
    public function setText(string $text): void;

    /**
     * Récupère les variables dynamiques dans le texte
     *
     * @return array Liste des variables trouvées
     */
    public function getVariables(): array;
}

/**
 * Interface ImageElementInterface
 * Contrat spécifique pour les éléments image
 */
interface ImageElementInterface extends ElementInterface {

    /**
     * Récupère l'URL de l'image
     *
     * @return string URL de l'image
     */
    public function getSrc(): string;

    /**
     * Définit l'URL de l'image
     *
     * @param string $src URL de l'image
     */
    public function setSrc(string $src): void;

    /**
     * Récupère le texte alternatif
     *
     * @return string Texte alternatif
     */
    public function getAlt(): string;

    /**
     * Définit le texte alternatif
     *
     * @param string $alt Texte alternatif
     */
    public function setAlt(string $alt): void;
}

/**
 * Interface ShapeElementInterface
 * Contrat spécifique pour les éléments de forme (rectangle, cercle, etc.)
 */
interface ShapeElementInterface extends ElementInterface {

    /**
     * Récupère la couleur de remplissage
     *
     * @return string Couleur de remplissage
     */
    public function getFillColor(): string;

    /**
     * Définit la couleur de remplissage
     *
     * @param string $color Couleur de remplissage
     */
    public function setFillColor(string $color): void;

    /**
     * Récupère la couleur de bordure
     *
     * @return string Couleur de bordure
     */
    public function getStrokeColor(): string;

    /**
     * Définit la couleur de bordure
     *
     * @param string $color Couleur de bordure
     */
    public function setStrokeColor(string $color): void;

    /**
     * Récupère l'épaisseur de bordure
     *
     * @return int Épaisseur de bordure
     */
    public function getStrokeWidth(): int;

    /**
     * Définit l'épaisseur de bordure
     *
     * @param int $width Épaisseur de bordure
     */
    public function setStrokeWidth(int $width): void;
}

/**
 * Classe abstraite BaseElement
 * Implémentation de base commune à tous les éléments
 */
abstract class BaseElement implements ElementInterface {

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
    public function __construct(string $id, array $data = []) {
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

    public function getId(): string {
        return $this->id;
    }

    public function getPosition(): array {
        return $this->position;
    }

    public function setPosition(array $position): void {
        $this->position = array_merge($this->position, $position);
    }

    public function getStyle(): array {
        return $this->style;
    }

    public function setStyle(array $style): void {
        $this->style = array_merge($this->style, $style);
    }

    public function validate(): bool {
        return !empty($this->id) && is_array($this->position) && is_array($this->style);
    }

    /**
     * Génère le style CSS inline pour l'élément
     *
     * @return string Style CSS
     */
    protected function generateStyle(): string {
        $css = '';

        // Position
        if (isset($this->position['x'])) $css .= "left: {$this->position['x']}px; ";
        if (isset($this->position['y'])) $css .= "top: {$this->position['y']}px; ";
        if (isset($this->position['width'])) $css .= "width: {$this->position['width']}px; ";
        if (isset($this->position['height'])) $css .= "height: {$this->position['height']}px; ";

        // Style de base
        if (isset($this->style['color'])) $css .= "color: {$this->style['color']}; ";
        if (isset($this->style['fontSize'])) $css .= "font-size: {$this->style['fontSize']}px; ";
        if (isset($this->style['fontWeight'])) $css .= "font-weight: {$this->style['fontWeight']}; ";
        if (isset($this->style['textAlign'])) $css .= "text-align: {$this->style['textAlign']}; ";

        // Visibilité
        if (!$this->visible) $css .= "display: none; ";

        return $css;
    }

    /**
     * Génère les attributs HTML communs
     *
     * @return string Attributs HTML
     */
    protected function generateAttributes(): string {
        $attrs = "id=\"{$this->id}\" class=\"pdf-element {$this->getType()}-element\"";

        if ($this->locked) {
            $attrs .= " data-locked=\"true\"";
        }

        return $attrs;
    }
}