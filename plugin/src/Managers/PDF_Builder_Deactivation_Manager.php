<?php
/**
 * PDF Builder Pro - Deactivation Manager
 * Gère le modal de feedback et les options de suppression lors de la désactivation
 */

namespace PDF_Builder\Managers;

class PDF_Builder_Deactivation_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialiser les hooks pour la désactivation
     */
    public function init() {
        add_action('admin_footer-plugins.php', array($this, 'load_deactivation_modal'));
        add_action('wp_ajax_pdf_builder_deactivation_feedback', array($this, 'handle_deactivation_feedback'));
    }
    
    /**
     * Charger le modal et le script de désactivation
     */
    public function load_deactivation_modal() {
        $screen = get_current_screen();
        if ($screen && $screen->id !== 'plugins') {
            return;
        }
        
        // Vérifier les permissions
        if (!current_user_can('activate_plugins')) {
            return;
        }
        
        $this->render_deactivation_modal();
        $this->render_deactivation_script();
    }
    
    /**
     * Rendre le HTML du modal
     */
    private function render_deactivation_modal() {
        ?>
        <div id="pdf-builder-deactivation-modal" style="display:none;">
            <div class="pdf-builder-modal-overlay"></div>
            <div class="pdf-builder-modal-content">
                <h2>Nous regrettons de vous voir partir 👋</h2>
                
                <div class="pdf-builder-feedback-section">
                    <label>Nous aimerions connaître votre avis (optionnel):</label>
                    <select id="pdf-builder-feedback-reason" style="width: 100%; padding: 8px; margin: 10px 0;">
                        <option value="">-- Sélectionnez une raison --</option>
                        <option value="not_needed">Je n'en ai plus besoin</option>
                        <option value="feature_missing">Il manque des fonctionnalités</option>
                        <option value="bug">J'ai rencontré des bugs</option>
                        <option value="performance">Problèmes de performance</option>
                        <option value="replacement">J'utilise un autre plugin</option>
                        <option value="other">Autre raison</option>
                    </select>
                    
                    <textarea id="pdf-builder-feedback-comment" 
                              placeholder="Laissez un commentaire (optionnel)..." 
                              style="width: 100%; height: 80px; padding: 8px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                </div>
                
                <div class="pdf-builder-database-section" style="background: #f5f5f5; padding: 12px; border-radius: 4px; margin: 15px 0;">
                    <label style="display: flex; align-items: center; margin: 10px 0;">
                        <input type="checkbox" id="pdf-builder-delete-tables" value="1">
                        <span style="margin-left: 8px;"><strong>Supprimer les données du plugin de la base de données</strong></span>
                    </label>
                    <p style="margin: 8px 0; color: #666; font-size: 13px;">
                        ⚠️ Cette action supprimera toutes les tables et données liées à PDF Builder Pro (paramètres, templates, etc.)
                    </p>
                </div>
                
                <div class="pdf-builder-modal-actions" style="text-align: right; margin-top: 20px;">
                    <button id="pdf-builder-btn-cancel" class="button" style="margin-right: 10px;">Annuler</button>
                    <button id="pdf-builder-btn-deactivate" class="button button-primary">Désactiver le plugin</button>
                </div>
            </div>
        </div>
        
        <style>
            .pdf-builder-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999999;
            }
            
            .pdf-builder-modal-content {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 5px 40px rgba(0, 0, 0, 0.16);
                z-index: 1000000;
                max-width: 500px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .pdf-builder-modal-content h2 {
                margin-top: 0;
                margin-bottom: 20px;
            }
            
            .pdf-builder-feedback-section label {
                display: block;
                margin-bottom: 10px;
                font-weight: 500;
            }
        </style>
        <?php
    }
    
    /**
     * Rendre le script pour le modal
     */
    private function render_deactivation_script() {
        $nonce = wp_create_nonce('pdf_builder_deactivation');
        ?>
        <script>
        (function() {
            let deactivationPluginSlug = 'wp-pdf-builder-pro/pdf-builder-pro';
            let deactivationIntercepted = false;
            
            // Intercepter le lien de désactivation
            document.addEventListener('DOMContentLoaded', function() {
                const deactivateLink = document.querySelector('[data-plugin="' + deactivationPluginSlug + '"] .row-actions .deactivate a');
                
                if (deactivateLink) {
                    const originalHref = deactivateLink.href;
                    deactivateLink.href = '#';
                    
                    deactivateLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        showDeactivationModal();
                        
                        // Stocker l'URL originale pour plus tard
                        window.pdfBuilderDeactivationUrl = originalHref;
                    });
                }
            });
            
            function showDeactivationModal() {
                const modal = document.getElementById('pdf-builder-deactivation-modal');
                if (modal) {
                    modal.style.display = 'block';
                    
                    // Bouton Annuler
                    document.getElementById('pdf-builder-btn-cancel').addEventListener('click', function() {
                        modal.style.display = 'none';
                    });
                    
                    // Fermer avec la croix (overlay)
                    document.querySelector('.pdf-builder-modal-overlay').addEventListener('click', function() {
                        modal.style.display = 'none';
                    });
                    
                    // Bouton Désactiver
                    document.getElementById('pdf-builder-btn-deactivate').addEventListener('click', function() {
                        submitDeactivationFeedback();
                    });
                }
            }
            
            function submitDeactivationFeedback() {
                const reason = document.getElementById('pdf-builder-feedback-reason').value;
                const comment = document.getElementById('pdf-builder-feedback-comment').value;
                const deleteTables = document.getElementById('pdf-builder-delete-tables').checked;
                
                // Envoyer le feedback via AJAX
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'pdf_builder_deactivation_feedback',
                        'nonce': '<?php echo $nonce; ?>',
                        'reason': reason,
                        'comment': comment,
                        'delete_tables': deleteTables ? '1' : '0'
                    })
                }).then(response => {
                    // Rediriger vers la désactivation
                    if (window.pdfBuilderDeactivationUrl) {
                        window.location.href = window.pdfBuilderDeactivationUrl;
                    }
                }).catch(error => {
                    console.error('Erreur:', error);
                    // Toujours désactiver même en cas d'erreur
                    if (window.pdfBuilderDeactivationUrl) {
                        window.location.href = window.pdfBuilderDeactivationUrl;
                    }
                });
            }
        })();
        </script>
        <?php
    }
    
    /**
     * Traiter le feedback de désactivation
     */
    public function handle_deactivation_feedback() {
        check_ajax_referer('pdf_builder_deactivation', 'nonce');
        
        $reason = sanitize_text_field($_POST['reason'] ?? '');
        $comment = sanitize_textarea_field($_POST['comment'] ?? '');
        $delete_tables = sanitize_text_field($_POST['delete_tables'] ?? '0');
        
        // Enregistrer le feedback
        error_log('[PDF Builder] Deactivation Feedback: Reason=' . $reason . ', Delete_Tables=' . $delete_tables);
        
        // Supprimer les tables si demandé
        if ($delete_tables === '1') {
            $this->delete_database_tables();
        }
        
        // Envoyer le feedback (optionnel - vous pourriez l'envoyer à un serveur)
        // $this->send_feedback_to_server($reason, $comment);
        
        wp_send_json_success(['message' => 'Feedback reçu']);
    }
    
    /**
     * Supprimer les tables de la base de données
     */
    private function delete_database_tables() {
        global $wpdb;
        
        $table_settings = $wpdb->prefix . 'pdf_builder_settings';
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        
        $wpdb->query("DROP TABLE IF EXISTS $table_settings");
        $wpdb->query("DROP TABLE IF EXISTS $table_templates");
        
        error_log('[PDF Builder] Database tables supprimées lors de la désactivation');
    }
}

// Initialiser le manager lors du chargement du plugin
add_action('admin_init', function() {
    if (is_admin()) {
        PDF_Builder_Deactivation_Manager::get_instance()->init();
    }
});
