<?php
/**
 * Script simple pour reset l'onboarding de PDF Builder Pro
 * À exécuter via une URL WordPress
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    die('Accès direct non autorisé');
}

// Vérifier les permissions
if (!current_user_can('manage_options')) {
    wp_die('Permissions insuffisantes. Vous devez être administrateur.');
}

echo '<h1>Reset de l\'onboarding PDF Builder Pro</h1>';

// Inclure et utiliser la classe d'onboarding
if (class_exists('PDF_Builder_Onboarding_Manager')) {
    $onboarding_manager = PDF_Builder_Onboarding_Manager::get_instance();

    if ($onboarding_manager->reset_onboarding()) {
        echo '<div style="background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;">';
        echo '<h3>✅ Onboarding reset avec succès !</h3>';
        echo '<p><strong>État après reset :</strong></p>';
        echo '<ul>';
        echo '<li>Étape actuelle : 0 (début)</li>';
        echo '<li>Terminé : Non</li>';
        echo '<li>Étapes complétées : Aucune</li>';
        echo '<li>Ignoré : Non</li>';
        echo '<li>Reset le : ' . date('d/m/Y H:i:s') . '</li>';
        echo '</ul>';
        echo '<p><strong>L\'onboarding recommencera à l\'étape 1 lors de votre prochaine visite.</strong></p>';
        echo '</div>';
    } else {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;">';
        echo '<h3>❌ Erreur lors du reset</h3>';
        echo '<p>Impossible de reset l\'onboarding. Vérifiez les permissions.</p>';
        echo '</div>';
    }
} else {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px 0;">';
    echo '<h3>❌ Erreur : Classe introuvable</h3>';
    echo '<p>La classe PDF_Builder_Onboarding_Manager n\'est pas disponible. Vérifiez que le plugin est activé.</p>';
    echo '</div>';
}

echo '<p><a href="' . admin_url() . '" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;">Retour au tableau de bord</a></p>';