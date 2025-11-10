<?php

namespace WP_PDF_Builder_Pro\Elements;

/**
 * Interface ImageElementInterface
 * Contrat spécifique pour les éléments image
 */
interface ImageElementInterface extends ElementInterface
{
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
