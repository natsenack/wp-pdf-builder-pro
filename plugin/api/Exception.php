<?php

namespace WP_PDF_Builder_Pro\Api;

/**
 * Exception personnalisée pour l'API PreviewImageAPI
 */
class Exception extends \Exception
{
    /**
     * Constructeur
     *
     * @param string $message Message d'erreur
     * @param int $code Code d'erreur
     * @param \Throwable $previous Exception précédente
     */
    public function __construct($message = "", $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
