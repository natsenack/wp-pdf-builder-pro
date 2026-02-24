<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags

namespace PDF_Builder\Api;

/**
 * Exception personnalisée pour les APIs
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




