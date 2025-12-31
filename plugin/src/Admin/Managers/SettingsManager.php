<?php

/**
 * PDF Builder Pro - Gestionnaire de Paramètres
 * Gère la sauvegarde et récupération des paramètres
 */

namespace PDF_Builder\Admin\Managers;

use Exception;

/**
 * Stub class pour compatibilité - système de cache supprimé
 */
class SettingsManager {

    public function __construct($admin) {
        // Manager désactivé - système de cache supprimé
    }

    public function registerHooks() {
        // Hooks désactivés - système de cache supprimé
    }

    public function saveSettings($settings) {
        // Sauvegarde désactivée - système de cache supprimé
        return false;
    }

    public function getSettings() {
        // Récupération désactivée - système de cache supprimé
        return [];
        }
    }

    /**
 * Classe responsable de la gestion des paramètres - DISABLED
 */
class SettingsManager_DISABLED
{
    /**
     * Instance de la classe principale - DÉSACTIVÉ
     */
    private $admin;

    /**
     * Constructeur - DÉSACTIVÉ
     */
    public function __construct($admin)
    {
        // Manager désactivé - système de cache supprimé
        $this->admin = $admin;
    }
}
