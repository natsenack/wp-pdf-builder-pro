<?php

/**
 * PDF Builder Pro - Admin Page Renderer
 * Responsable du rendu HTML de la page d'administration (Tableau de bord)
 */

namespace PDF_Builder\Admin\Renderers;

class AdminPageRenderer
{
    private $admin;

    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    public function renderAdminPage()
    {
        // Récupérer les données nécessaires depuis l'admin
        $stats = $this->admin->getDashboardStats();
        $plugin_version = $this->admin->getPluginVersion();
        
        // Vérifier le statut premium
        $is_premium = false;
        if (class_exists('\PDF_Builder\Managers\PDF_Builder_License_Manager')) {
            $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
            $is_premium = $license_manager->isPremium();
        }

        // Charger le template dashboard-page.php qui utilise les classes avec préfixe pdfb-
        $template_path = PDF_BUILDER_PLUGIN_DIR . 'templates/admin/dashboard-page.php';
        
        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        }

        // Fallback si le template n'existe pas
        return '<div class="wrap"><h1>PDF Builder Pro</h1><p>Erreur: Template dashboard introuvable.</p></div>';
    }
}
