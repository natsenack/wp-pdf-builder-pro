<?php

/**
 * HOTFIX: Désactiver temporairement l'initialisation des notifications
 * pour éviter l'erreur fatale de classe non trouvée
 */

// Si la classe PDF_Builder_Core existe, remplacer la méthode problématique
if (class_exists('PDF_Builder_Core')) {
// Créer une nouvelle instance pour accéder aux méthodes
    $core = new PDF_Builder_Core();
// Remplacer la méthode initialize_notification_manager
    $core->initialize_notification_manager = function () {

        // Temporairement désactivé pour éviter l'erreur fatale
        return;
    };
}
