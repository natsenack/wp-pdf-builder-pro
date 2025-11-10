<?php

namespace WP_PDF_Builder_Pro\Elements;

/**
 * Interface ElementInterface
 * Définit le contrat pour tous les éléments de template
 */
interface ElementInterface
{
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
