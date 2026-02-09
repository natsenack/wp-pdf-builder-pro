<?php

/**
 * PDF Builder Pro - Data Utils
 * Utilitaires de traitement des données
 */

namespace PDF_Builder\Admin\Utils;

use Exception;

/**
 * Classe utilitaire pour le traitement des données
 */
class DataUtils
{
    /**
     * Instance de la classe principale
     */
    private $admin;

    /**
     * Constructeur
     */
    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    /**
     * Nettoie une valeur de paramètre
     */
    public function sanitizeSettingValue($value, $type = 'string')
    {
        return $this->admin->sanitizeSettingValue($value, $type);
    }

    /**
     * Nettoie les données JSON
     */
    public function cleanJsonData($data)
    {
        return $this->admin->cleanJsonData($data);
    }

    /**
     * Nettoie agressivement les données JSON
     */
    public function aggressiveJsonClean($json_string)
    {
        return $this->admin->aggressiveJsonClean($json_string);
    }

    /**
     * Parse les instructions SQL
     */
    public function parseSqlStatements($sql)
    {
        return $this->admin->parseSqlStatements($sql);
    }

    /**
     * Obtient la taille du répertoire
     */
    public function getDirectorySize($directory)
    {
        return $this->admin->getDirectorySize($directory);
    }
}



