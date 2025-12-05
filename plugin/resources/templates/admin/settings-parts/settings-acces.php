<?php
/**
 * PDF Builder Pro - Onglet Accès (Gestion des Rôles)
 * Interface complète pour la gestion des rôles utilisateur avec feedback visuel
 */

// Inclure les fonctions helper
require_once __DIR__ . '/../settings-helpers.php';

// Récupérer les données des rôles
global $wp_roles;
$all_roles = $wp_roles->roles;
$allowed_roles = pdf_builder_get_allowed_roles();

// Descriptions des rôles en français
$role_descriptions = [
    'administrator' => 'Accès complet à toutes les fonctionnalités du plugin',
    'editor' => 'Peut publier et gérer les articles et pages',
    'author' => 'Peut publier et gérer ses propres articles',
    'contributor' => 'Peut écrire et soumettre des articles pour révision',
    'subscriber' => 'Peut uniquement lire les articles et gérer son profil',
    'shop_manager' => 'Gestionnaire de boutique WooCommerce',
    'customer' => 'Client WooCommerce avec accès limité',
    'translator' => 'Traducteur avec accès aux fonctions de traduction',
    'pdf_editor' => 'Éditeur PDF avec droits de modification',
    'pdf_manager' => 'Gestionnaire PDF avec droits étendus',
    'pdf_admin' => 'Administrateur PDF avec tous les droits',
];

// Statistiques des rôles
$stats = [
    'total_roles' => count($all_roles),
    'active_roles' => count($allowed_roles),
    'inactive_roles' => count($all_roles) - count($allowed_roles),
    'admin_always_active' => in_array('administrator', $allowed_roles)
];
?>

<!-- En-tête de l'onglet -->
<div class="acces-header" style="margin-bottom: 30px;">
    <h2 style="margin: 0 0 10px 0; color: #23282d; font-size: 24px; font-weight: 600;">
        👥 Gestion des Rôles et Permissions
    </h2>
    <p style="margin: 0; color: #666; font-size: 16px; line-height: 1.5;">
        Définissez quels rôles WordPress peuvent accéder à PDF Builder Pro.
        <strong>Le rôle Administrateur a toujours accès complet.</strong>
    </p>
</div>

<!-- Zone de statut et statistiques -->
<div class="acces-status-panel" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border: 1px solid #dee2e6; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
    <div class="status-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
        <h3 style="margin: 0; color: #495057; font-size: 18px; font-weight: 600;">
            📊 État des Permissions
        </h3>
        <div class="status-indicators" style="display: flex; gap: 15px;">
            <span class="status-badge active" style="background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                ✅ <?php echo $stats['active_roles']; ?> Actif(s)
            </span>
            <span class="status-badge inactive" style="background: #dc3545; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                ❌ <?php echo $stats['inactive_roles']; ?> Inactif(s)
            </span>
        </div>
    </div>

    <div class="status-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="current-roles">
            <h4 style="margin: 0 0 10px 0; color: #495057; font-size: 14px; font-weight: 600;">
                🔓 Rôles avec Accès Actif
            </h4>
            <div id="active-roles-list" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px; min-height: 40px;">
                <?php if (!empty($allowed_roles)): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($allowed_roles as $role_key): ?>
                            <?php
                            $role_name = isset($all_roles[$role_key]) ? translate_user_role($all_roles[$role_key]['name']) : ucfirst($role_key);
                            $is_admin = $role_key === 'administrator';
                            ?>
                            <span class="role-tag <?php echo $is_admin ? 'admin-tag' : 'user-tag'; ?>" style="background: <?php echo $is_admin ? '#007cba' : '#28a745'; ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                <?php echo $is_admin ? '🔒' : '👤'; ?> <?php echo esc_html($role_name); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <span style="color: #6c757d; font-style: italic;">Aucun rôle actif</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="save-status">
            <h4 style="margin: 0 0 10px 0; color: #495057; font-size: 14px; font-weight: 600;">
                💾 Dernière Sauvegarde
            </h4>
            <div id="save-status-display" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 12px;">
                <span id="last-save-time" style="color: #6c757d; font-size: 13px;">
                    <?php
                    $last_save = get_option('pdf_builder_last_roles_save', false);
                    if ($last_save) {
                        echo 'Dernière sauvegarde: ' . date_i18n('d/m/Y à H:i:s', strtotime($last_save));
                    } else {
                        echo 'Aucune sauvegarde récente';
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire principal -->
<form method="post" action="" id="acces-settings-form" style="background: white; border: 1px solid #e9ecef; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <?php wp_nonce_field('pdf_builder_save_settings', 'pdf_builder_acces_nonce'); ?>

    <!-- Section des rôles utilisateur -->
    <div class="roles-section">
        <h3 style="margin: 0 0 20px 0; color: #23282d; font-size: 18px; font-weight: 600; border-bottom: 2px solid #007cba; padding-bottom: 10px;">
            👤 Rôles Utilisateur
        </h3>

        <div class="roles-grid" style="display: grid; gap: 15px;">
            <?php foreach ($all_roles as $role_key => $role):
                $role_name = translate_user_role($role['name']);
                $is_selected = in_array($role_key, $allowed_roles);
                $description = $role_descriptions[$role_key] ?? 'Rôle personnalisé - ' . $role_key;
                $is_admin = $role_key === 'administrator';
                $is_core_role = in_array($role_key, ['administrator', 'editor', 'author', 'contributor', 'subscriber']);
                $is_woo_role = in_array($role_key, ['shop_manager', 'customer']);
                $is_pdf_role = strpos($role_key, 'pdf_') === 0;
            ?>
                <div class="role-card <?php echo $is_selected ? 'role-active' : 'role-inactive'; ?> <?php echo $is_admin ? 'role-admin' : ''; ?>"
                     style="display: flex; align-items: center; justify-content: space-between; padding: 18px; border: 2px solid <?php echo $is_selected ? '#28a745' : '#dee2e6'; ?>; border-radius: 10px; background: <?php echo $is_admin ? 'linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%)' : 'white'; ?>; transition: all 0.3s ease; position: relative; overflow: hidden;">

                    <!-- Indicateur de statut -->
                    <div class="role-status-indicator" style="position: absolute; top: 10px; right: 10px; width: 12px; height: 12px; border-radius: 50%; background: <?php echo $is_selected ? '#28a745' : '#dc3545'; ?>; border: 2px solid white; box-shadow: 0 0 4px rgba(0,0,0,0.2);"></div>

                    <!-- Informations du rôle -->
                    <div class="role-info" style="flex: 1; margin-right: 20px;">
                        <div class="role-header" style="display: flex; align-items: center; margin-bottom: 8px;">
                            <label for="role_<?php echo esc_attr($role_key); ?>" style="cursor: pointer; margin: 0; flex: 1;">
                                <strong style="color: #23282d; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                                    <?php if ($is_admin): ?>
                                        🔒
                                    <?php elseif ($is_core_role): ?>
                                        👤
                                    <?php elseif ($is_woo_role): ?>
                                        🛒
                                    <?php elseif ($is_pdf_role): ?>
                                        📄
                                    <?php else: ?>
                                        ⚙️
                                    <?php endif; ?>
                                    <?php echo esc_html($role_name); ?>
                                </strong>
                            </label>

                            <?php if ($is_admin): ?>
                                <span class="admin-badge" style="background: #007cba; color: white; padding: 3px 10px; border-radius: 15px; font-size: 11px; font-weight: 600; margin-left: 10px;">
                                    TOUJOURS ACTIF
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="role-description" style="margin: 0; color: #6c757d; font-size: 14px; line-height: 1.4;">
                            <?php echo esc_html($description); ?>
                        </p>

                        <div class="role-meta" style="margin-top: 8px; font-size: 12px; color: #9ca3af;">
                            <span class="role-key">Clé: <code style="background: #f8f9fa; padding: 2px 4px; border-radius: 3px;"><?php echo esc_html($role_key); ?></code></span>
                            <?php if ($is_core_role): ?>
                                <span class="role-type" style="margin-left: 10px; color: #007cba;">• Rôle WordPress natif</span>
                            <?php elseif ($is_woo_role): ?>
                                <span class="role-type" style="margin-left: 10px; color: #28a745;">• Rôle WooCommerce</span>
                            <?php elseif ($is_pdf_role): ?>
                                <span class="role-type" style="margin-left: 10px; color: #dc3545;">• Rôle PDF Builder</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Contrôle checkbox -->
                    <div class="role-control" style="display: flex; align-items: center; gap: 15px;">
                        <div class="role-status-text" style="font-size: 13px; font-weight: 600; min-width: 80px; text-align: center;">
                            <span class="status-text <?php echo $is_selected ? 'active' : 'inactive'; ?>" style="color: <?php echo $is_selected ? '#28a745' : '#dc3545'; ?>;">
                                <?php echo $is_selected ? '✅ ACTIF' : '❌ INACTIF'; ?>
                            </span>
                        </div>

                        <div class="checkbox-wrapper">
                            <input type="checkbox"
                                   id="role_<?php echo esc_attr($role_key); ?>"
                                   name="pdf_builder_allowed_roles[]"
                                   value="<?php echo esc_attr($role_key); ?>"
                                   <?php echo $is_selected ? 'checked' : ''; ?>
                                   <?php echo $is_admin ? 'disabled' : ''; ?>
                                   class="role-checkbox"
                                   style="width: 20px; height: 20px; margin: 0; cursor: pointer;" />
                            <label for="role_<?php echo esc_attr($role_key); ?>" class="checkbox-label" style="margin-left: 8px; cursor: pointer; user-select: none;">
                                <?php echo $is_admin ? 'Verrouillé' : 'Activer'; ?>
                            </label>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</form>

<!-- Zone de feedback et actions -->
<div class="acces-actions-panel" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 12px; padding: 20px;">
    <div class="actions-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
        <h3 style="margin: 0; color: #495057; font-size: 16px; font-weight: 600;">
            🎯 Actions Rapides
        </h3>
        <div class="action-buttons" style="display: flex; gap: 10px;">
            <button type="button" id="select-all-roles" class="button button-secondary" style="padding: 6px 12px; font-size: 12px;">
                📋 Tout Sélectionner
            </button>
            <button type="button" id="select-none-roles" class="button button-secondary" style="padding: 6px 12px; font-size: 12px;">
                🚫 Tout Désélectionner
            </button>
            <button type="button" id="reset-default-roles" class="button button-secondary" style="padding: 6px 12px; font-size: 12px;">
                🔄 Rôles par Défaut
            </button>
        </div>
    </div>

    <!-- Zone de messages de feedback -->
    <div id="acces-feedback-zone" style="display: none; padding: 15px; border-radius: 8px; margin-top: 15px; font-weight: 500;">
        <!-- Les messages de feedback seront insérés ici par JavaScript -->
    </div>
</div>

<!-- Instructions d'utilisation -->
<div class="acces-instructions" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border: 1px solid #2196f3; border-radius: 12px; padding: 20px; margin-top: 20px;">
    <h4 style="margin: 0 0 15px 0; color: #1976d2; font-size: 16px; font-weight: 600;">
        📖 Comment utiliser cette interface
    </h4>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div>
            <h5 style="margin: 0 0 10px 0; color: #1976d2; font-size: 14px;">✅ Activation des rôles</h5>
            <ul style="margin: 0; padding-left: 20px; color: #424242; font-size: 13px; line-height: 1.6;">
                <li>Cochez la case à côté du rôle souhaité</li>
                <li>Le statut passe automatiquement à "ACTIF" (vert)</li>
                <li>La bordure de la carte devient verte</li>
                <li>L'indicateur en haut à droite devient vert</li>
            </ul>
        </div>
        <div>
            <h5 style="margin: 0 0 10px 0; color: #1976d2; font-size: 14px;">💾 Sauvegarde des paramètres</h5>
            <ul style="margin: 0; padding-left: 20px; color: #424242; font-size: 13px; line-height: 1.6;">
                <li>Utilisez le bouton "💾 Enregistrer" flottant</li>
                <li>Un message de confirmation apparaît</li>
                <li>Les paramètres sont sauvegardés en base de données</li>
                <li>L'horodatage de sauvegarde est mis à jour</li>
            </ul>
        </div>
    </div>
</div>

<!-- JavaScript pour la gestion interactive -->
<script>
jQuery(document).ready(function($) {
    'use strict';

    // Configuration
    const CONFIG = {
        defaultRoles: ['administrator', 'editor', 'author', 'contributor', 'subscriber', 'customer'],
        feedbackDuration: 4000,
        animationDuration: 300
    };

    // État de l'interface
    let interfaceState = {
        isSaving: false,
        lastSaveTime: null,
        activeRoles: []
    };

    // Utilitaires
    const Utils = {
        showFeedback: function(type, message, duration = CONFIG.feedbackDuration) {
            const $feedbackZone = $('#acces-feedback-zone');
            const colors = {
                success: '#d4edda',
                error: '#f8d7da',
                warning: '#fff3cd',
                info: '#d1ecf1'
            };
            const borders = {
                success: '#c3e6cb',
                error: '#f5c6cb',
                warning: '#ffeaa7',
                info: '#bee5eb'
            };
            const textColors = {
                success: '#155724',
                error: '#721c24',
                warning: '#856404',
                info: '#0c5460'
            };

            $feedbackZone
                .removeClass('success error warning info')
                .addClass(type)
                .css({
                    'display': 'block',
                    'background-color': colors[type],
                    'border-color': borders[type],
                    'color': textColors[type],
                    'border': '1px solid ' + borders[type]
                })
                .html(message)
                .fadeIn(CONFIG.animationDuration);

            if (duration > 0) {
                setTimeout(function() {
                    $feedbackZone.fadeOut(CONFIG.animationDuration);
                }, duration);
            }
        },

        updateRoleCard: function($checkbox, isActive) {
            const $card = $checkbox.closest('.role-card');
            const $indicator = $card.find('.role-status-indicator');
            const $statusText = $card.find('.status-text');

            $card.removeClass('role-active role-inactive').addClass(isActive ? 'role-active' : 'role-inactive');
            $card.css('border-color', isActive ? '#28a745' : '#dee2e6');
            $indicator.css('background-color', isActive ? '#28a745' : '#dc3545');
            $statusText.removeClass('active inactive').addClass(isActive ? 'active' : 'inactive');
            $statusText.css('color', isActive ? '#28a745' : '#dc3545');
            $statusText.text(isActive ? '✅ ACTIF' : '❌ INACTIF');
        },

        updateGlobalStatus: function() {
            const activeRoles = [];
            $('input[name="pdf_builder_allowed_roles[]"]:checked:not(:disabled)').each(function() {
                activeRoles.push($(this).val());
            });

            interfaceState.activeRoles = activeRoles;

            // Mettre à jour le compteur
            $('.status-badge.active').text(`✅ ${activeRoles.length} Actif(s)`);
            $('.status-badge.inactive').text(`❌ ${<?php echo $stats['total_roles']; ?> - activeRoles.length} Inactif(s)`);

            // Mettre à jour la liste des rôles actifs
            const $activeList = $('#active-roles-list');
            if (activeRoles.length > 0) {
                const roleTags = activeRoles.map(function(roleKey) {
                    const $roleCard = $(`#role_${roleKey}`).closest('.role-card');
                    const roleName = $roleCard.find('strong').text().replace(/^[🔒👤🛒📄⚙️]\s*/, '');
                    const isAdmin = roleKey === 'administrator';
                    const icon = isAdmin ? '🔒' : '👤';
                    const bgColor = isAdmin ? '#007cba' : '#28a745';

                    return `<span class="role-tag" style="background: ${bgColor}; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; margin: 2px;">${icon} ${roleName}</span>`;
                }).join(' ');

                $activeList.html(`<div style="display: flex; flex-wrap: wrap; gap: 8px;">${roleTags}</div>`);
            } else {
                $activeList.html('<span style="color: #6c757d; font-style: italic;">Aucun rôle actif</span>');
            }
        },

        saveRoles: function(callback) {
            if (interfaceState.isSaving) {
                Utils.showFeedback('warning', '⚠️ Une sauvegarde est déjà en cours...', 2000);
                return;
            }

            interfaceState.isSaving = true;
            const selectedRoles = [];

            $('input[name="pdf_builder_allowed_roles[]"]:checked:not(:disabled)').each(function() {
                selectedRoles.push($(this).val());
            });

            Utils.showFeedback('info', '💾 Sauvegarde des rôles en cours...', 0);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_save_settings',
                    tab: 'acces',
                    pdf_builder_allowed_roles: selectedRoles,
                    pdf_builder_acces_nonce: $('#pdf_builder_acces_nonce').val()
                },
                success: function(response) {
                    interfaceState.isSaving = false;

                    if (response.success) {
                        interfaceState.lastSaveTime = new Date();
                        $('#last-save-time').text('Dernière sauvegarde: ' + interfaceState.lastSaveTime.toLocaleString('fr-FR'));

                        Utils.showFeedback('success',
                            `✅ Rôles sauvegardés avec succès ! ${selectedRoles.length} rôle(s) actif(s).`
                        );

                        if (typeof callback === 'function') {
                            callback(true, response);
                        }
                    } else {
                        Utils.showFeedback('error',
                            '❌ Erreur lors de la sauvegarde: ' + (response.data || 'Erreur inconnue')
                        );

                        if (typeof callback === 'function') {
                            callback(false, response);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    interfaceState.isSaving = false;
                    Utils.showFeedback('error',
                        `❌ Erreur de connexion: ${status} - ${error}`
                    );

                    if (typeof callback === 'function') {
                        callback(false, {error: error});
                    }
                }
            });
        }
    };

    // Gestionnaires d'événements
    const EventHandlers = {
        init: function() {
            this.bindCheckboxChanges();
            this.bindActionButtons();
            this.initializeInterface();
        },

        bindCheckboxChanges: function() {
            $(document).on('change', 'input[name="pdf_builder_allowed_roles[]"]', function() {
                const $checkbox = $(this);
                const isChecked = $checkbox.is(':checked') && !$checkbox.is(':disabled');

                Utils.updateRoleCard($checkbox, isChecked);
                Utils.updateGlobalStatus();
            });
        },

        bindActionButtons: function() {
            // Tout sélectionner
            $('#select-all-roles').on('click', function() {
                $('input[name="pdf_builder_allowed_roles[]"]:not(:disabled)').prop('checked', true).trigger('change');
                Utils.showFeedback('info', '📋 Tous les rôles ont été sélectionnés', 2000);
            });

            // Tout désélectionner
            $('#select-none-roles').on('click', function() {
                $('input[name="pdf_builder_allowed_roles[]"]:not(:disabled)').prop('checked', false).trigger('change');
                Utils.showFeedback('warning', '🚫 Tous les rôles ont été désélectionnés', 2000);
            });

            // Rôles par défaut
            $('#reset-default-roles').on('click', function() {
                // Désélectionner tous les rôles
                $('input[name="pdf_builder_allowed_roles[]"]:not(:disabled)').prop('checked', false);

                // Sélectionner les rôles par défaut
                CONFIG.defaultRoles.forEach(function(roleKey) {
                    $(`input[name="pdf_builder_allowed_roles[]"][value="${roleKey}"]:not(:disabled)`).prop('checked', true);
                });

                // Mettre à jour l'interface
                $('input[name="pdf_builder_allowed_roles[]"]').trigger('change');

                Utils.showFeedback('info', `🔄 Rôles remis aux valeurs par défaut (${CONFIG.defaultRoles.length} rôles actifs)`, 3000);
            });
        },

        initializeInterface: function() {
            // Activer automatiquement les rôles souhaités
            const rolesToActivate = ['author', 'contributor', 'subscriber', 'customer'];
            rolesToActivate.forEach(function(roleKey) {
                const $checkbox = $(`input[name="pdf_builder_allowed_roles[]"][value="${roleKey}"]:not(:disabled)`);
                if ($checkbox.length && !$checkbox.prop('checked')) {
                    $checkbox.prop('checked', true);
                    console.log(`✅ Activation automatique du rôle: ${roleKey}`);
                }
            });

            // Initialiser l'état visuel
            $('input[name="pdf_builder_allowed_roles[]"]').each(function() {
                const isChecked = $(this).is(':checked') && !$(this).is(':disabled');
                Utils.updateRoleCard($(this), isChecked);
            });

            Utils.updateGlobalStatus();

            console.log('🔄 Interface des rôles initialisée avec succès');
        }
    };

    // Gestionnaire de sauvegarde globale (bouton flottant)
    $(document).on('pdf_builder_save_tab', function(event, tabName, callback) {
        if (tabName === 'acces') {
            Utils.saveRoles(callback);
        }
    });

    // Intercepter la soumission du formulaire (pour le bouton flottant)
    $('#acces-settings-form').on('submit', function(e) {
        e.preventDefault();
        Utils.saveRoles();
        return false;
    });

    // Initialisation
    EventHandlers.init();

    // Exposition des utilitaires pour le débogage
    if (typeof window.pdfBuilderDebug !== 'undefined') {
        window.pdfBuilderDebug.rolesInterface = {
            Utils: Utils,
            EventHandlers: EventHandlers,
            interfaceState: interfaceState
        };
    }
});
</script>

<style>
/* Styles supplémentaires pour l'interface améliorée */
.role-card {
    transition: all 0.3s ease !important;
}

.role-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.role-card.role-active {
    background: linear-gradient(135deg, #f8fff8 0%, #f0fff0 100%) !important;
}

.role-card.role-admin {
    border-color: #007cba !important;
}

.role-status-indicator {
    transition: background-color 0.3s ease;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
}

.checkbox-label {
    font-weight: 500;
    color: #495057;
}

.status-badge {
    font-size: 11px !important;
    font-weight: 600 !important;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.role-tag {
    transition: opacity 0.3s ease;
}

.role-tag:hover {
    opacity: 0.8;
}

/* Animation pour les feedbacks */
#acces-feedback-zone {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .status-indicators {
        flex-direction: column;
        gap: 8px !important;
    }

    .actions-header {
        flex-direction: column;
        align-items: stretch !important;
        gap: 15px;
    }

    .action-buttons {
        justify-content: center;
    }

    .status-details {
        grid-template-columns: 1fr !important;
        gap: 15px !important;
    }
}
</style>




