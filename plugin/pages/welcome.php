<?php
/**
 * PDF Builder Pro V2 - Page d'accueil du plugin
 * Redirection automatique vers l'éditeur
 */

if (!current_user_can('manage_options')) {
    wp_die(__('Accès refusé', 'pdf-builder-pro'));
}

// Redirection automatique vers l'éditeur
wp_redirect(admin_url('admin.php?page=pdf-builder-react-editor'));
exit;
    margin-top: 0;
    color: #0073aa;
}

.info-column ul {
    margin: 10px 0;
    padding-left: 20px;
}

.info-column li {
    margin-bottom: 8px;
    color: #666;
}

.info-column p {
    margin: 8px 0;
    color: #666;
}

@media (max-width: 768px) {
    .welcome-info {
        grid-template-columns: 1fr;
    }
    
    .welcome-header h1 {
        font-size: 24px;
    }
}
</style>

