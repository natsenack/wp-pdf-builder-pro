<?php // Acces tab content - Updated: 2025-11-18 20:20:00

    require_once __DIR__ . '/../settings-helpers.php';

    global $wp_roles;
    $all_roles = $wp_roles->roles;

    // Récupérer les rôles autorisés (simple et robuste)
    $allowed_roles = pdf_builder_get_allowed_roles();

    // DEBUG TEMPORAIRE - Afficher les valeurs pour vérification
    echo "<div style='background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px 0; border-radius: 4px; font-family: monospace;'>";
    echo "<strong>✅ RÔLES AUTORISÉS (v" . time() . "):</strong> " . implode(', ', $allowed_roles);
    echo "</div>";

    $role_descriptions = [
        'administrator' => 'Accès complet à toutes les fonctionnalités',
        'editor' => 'Peut publier et gérer les articles',
        'author' => 'Peut publier ses propres articles',
        'contributor' => 'Peut soumettre des articles pour révision',
        'subscriber' => 'Peut uniquement lire les articles',
        'shop_manager' => 'Gestionnaire de boutique WooCommerce',
        'customer' => 'Client WooCommerce',
    ];
    ?>
            <h2>👥 Gestion des Rôles et Permissions</h2>

            <!-- Message de confirmation que l'onglet est chargé -->
            <aside class="access-success-notice">
                ✅ Section Rôles chargée - Utilise le bouton "Enregistrer" flottant pour sauvegarder
            </aside>

            <p>Sélectionnez les rôles WordPress qui auront accès à PDF Builder Pro.</p>

            <!-- Disposition en colonnes -->
            <div class="access-main-layout">

                    <!-- Colonne principale : toggles des rôles -->
                    <div>

                        <!-- Access Settings Section (No Form - AJAX Centralized) -->
                        <form method="post" id="acces-form">
                        <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_acces_nonce'); ?>
                        <input type="hidden" name="submit_acces" value="1">
                        <section id="access-settings-container" aria-label="Paramètres d'accès">

                            <!-- Boutons de contrôle rapide -->
                            <nav class="access-role-nav">
                                <button type="button" id="select-all-roles" class="button button-secondary access-select-btn">
                                    Sélectionner Tout
                                </button>
                                <button type="button" id="select-common-roles" class="button button-secondary access-select-btn">
                                    Rôles Courants
                                </button>
                                <button type="button" id="select-none-roles" class="button button-secondary access-select-btn">
                                    Désélectionner Tout
                                </button>
                                <span class="description access-selected-count">
                                    Sélectionnés: <strong id="selected-count"><?php echo count($allowed_roles); ?></strong> rôle(s)
                                </span>
                            </nav>

                            <!-- Boutons toggle pour les rôles -->
                            <div class="roles-toggle-list">
                                <?php foreach ($all_roles as $role_key => $role) :
                                    $role_name = translate_user_role($role['name']);
                                    $is_selected = in_array($role_key, $allowed_roles);
                                    $description = $role_descriptions[$role_key] ?? 'Rôle personnalisé';
                                    $is_admin = $role_key === 'administrator';

                                    // DEBUG: Afficher les valeurs pour chaque rôle
                                    echo "<!-- DEBUG {$role_key}: is_selected = " . ($is_selected ? 'true' : 'false') . ", in_array = " . (in_array($role_key, $allowed_roles) ? 'true' : 'false') . " -->";
                                    ?>
                                    <article class="role-toggle-item <?php echo $is_admin ? 'admin-role' : ''; ?>">
                                        <header class="role-info">
                                            <h5 class="role-name">
                                                <?php echo esc_html($role_name); ?>
                                                <?php if ($is_admin) :
                                                    ?>
                                                    <span class="admin-badge">🔒 Toujours actif</span>
                                                    <?php
                                                endif; ?>
                                            </h5>
                                            <p class="role-description"><?php echo esc_html($description); ?></p>
                                            <small class="role-key"><?php echo esc_html($role_key); ?></small>
                                        </header>
                                        <div class="toggle-switch">
                                            <input type="checkbox"
                                                id="role_<?php echo esc_attr($role_key); ?>"
                                                name="pdf_builder_allowed_roles[]"
                                                value="<?php echo esc_attr($role_key); ?>"
                                                <?php pdf_builder_safe_checked($is_selected); ?>
                                                <?php echo $is_admin ? 'disabled' : ''; ?> />
                                            <label for="role_<?php echo esc_attr($role_key); ?>" class="toggle-slider"></label>
                                        </div>
                                    </article>
                                    <?php
                                endforeach; ?>
                            </div>



                            <!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->
                        </section>
                        </form>
                    </div> <!-- Fin colonne principale -->

                <!-- Colonne informations -->
                <div>

                    <!-- Permissions incluses -->
                    <aside class="access-permissions-aside">
                        <h4>🔐 Permissions Incluses</h4>
                        <p>Les rôles sélectionnés auront accès à :</p>
                        <ul>
                            <li>✅ Création, édition et suppression de templates PDF</li>
                            <li>✅ Génération et téléchargement de PDF</li>
                            <li>✅ Accès aux paramètres et configuration</li>
                            <li>✅ Prévisualisation avant génération</li>
                            <li>✅ Gestion des commandes WooCommerce (si applicable)</li>
                        </ul>
                    </aside>

                    <!-- Avertissement important -->
                    <aside class="access-warning-aside">
                        <h4>⚠️ Informations Importantes</h4>
                        <ul>
                            <li>Les rôles non sélectionnés n'auront aucun accès à PDF Builder Pro</li>
                            <li>Le rôle "Administrator" a toujours accès complet, indépendamment</li>
                            <li>Minimum requis : au moins un rôle sélectionné</li>
                        </ul>
                    </aside>

                </div> <!-- Fin colonne informations -->

            </div> <!-- Fin disposition en colonnes -->

<script>
jQuery(document).ready(function($) {
    // Gestion des boutons de contrôle rapide
    $('#select-all-roles').on('click', function() {
        $('input[name="pdf_builder_allowed_roles[]"]:not(:disabled)').prop('checked', true);
        updateSelectedCount();
    });

    $('#select-common-roles').on('click', function() {
        $('input[name="pdf_builder_allowed_roles[]"]:not(:disabled)').prop('checked', false);
        // Sélectionner les rôles courants
        $('input[name="pdf_builder_allowed_roles[]"][value="administrator"]').prop('checked', true);
        $('input[name="pdf_builder_allowed_roles[]"][value="editor"]').prop('checked', true);
        $('input[name="pdf_builder_allowed_roles[]"][value="shop_manager"]').prop('checked', true);
        updateSelectedCount();
    });

    $('#select-none-roles').on('click', function() {
        $('input[name="pdf_builder_allowed_roles[]"]:not(:disabled)').prop('checked', false);
        updateSelectedCount();
    });

    // Mettre à jour le compteur de rôles sélectionnés
    function updateSelectedCount() {
        var count = $('input[name="pdf_builder_allowed_roles[]"]:checked').length;
        $('#selected-count').text(count);
    }

    // Fonction pour afficher les notices
    function showNotice(message, type) {
        // Supprimer les notices existantes
        $('.pdf-builder-notice').remove();

        // Créer la nouvelle notice
        var noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible pdf-builder-notice"><p>' + message + '</p></div>');

        // Ajouter au début du conteneur principal
        $('.wrap').prepend(notice);

        // Auto-dismiss après 5 secondes
        setTimeout(function() {
            notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Initialiser le compteur au chargement
    updateSelectedCount();
});
</script>

<style>
/* Styles pour l'onglet Accès */
.access-main-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    align-items: start;
}

.access-role-nav {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.access-select-btn {
    margin-right: 10px !important;
}

.access-selected-count {
    margin-left: auto;
    font-size: 14px;
    color: #666;
}

.roles-toggle-list {
    display: grid;
    gap: 15px;
}

.role-toggle-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    transition: all 0.2s ease;
}

.role-toggle-item:hover {
    border-color: #007cba;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.role-toggle-item.admin-role {
    background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%);
    border-color: #007cba;
}

.role-info {
    flex: 1;
}

.role-name {
    margin: 0 0 5px 0;
    font-size: 16px;
    font-weight: 600;
    color: #23282d;
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-badge {
    background: #007cba;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: normal;
}

.role-description {
    margin: 0 0 3px 0;
    color: #666;
    font-size: 14px;
}

.role-key {
    color: #999;
    font-size: 12px;
    font-family: monospace;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #007cba;
}

input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.toggle-switch input:disabled + .toggle-slider {
    opacity: 0.6;
    cursor: not-allowed;
}

.access-permissions-aside,
.access-warning-aside {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.access-permissions-aside h4,
.access-warning-aside h4 {
    margin-top: 0;
    color: #23282d;
}

.access-permissions-aside ul,
.access-warning-aside ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.access-permissions-aside li {
    color: #46b450;
    margin-bottom: 5px;
}

.access-warning-aside li {
    color: #d63638;
    margin-bottom: 5px;
}

.access-success-notice {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 12px;
    border-radius: 4px;
    margin-bottom: 20px;
}

/* Animation de chargement */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .access-main-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .access-role-nav {
        flex-direction: column;
        align-items: stretch;
    }

    .access-selected-count {
        margin-left: 0;
        margin-top: 10px;
        text-align: center;
    }
}
</style>

