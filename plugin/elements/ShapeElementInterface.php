<?php

namespace WP_PDF_Builder_Pro\Elements;

/**
 * Interface ShapeElementInterface
 * Contrat spécifique pour les éléments de forme (rectangle, cercle, etc.)
 */
interface ShapeElementInterface extends ElementInterface
{
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
