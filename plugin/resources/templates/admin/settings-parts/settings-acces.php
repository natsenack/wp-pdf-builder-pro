<?php
    require_once __DIR__ . '/../settings-helpers.php';

    global $wp_roles;
    $all_roles = $wp_roles->roles;
    $allowed_roles = pdf_builder_get_allowed_roles();

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

            <p>Sélectionnez les rôles WordPress qui auront accès à PDF Builder Pro.</p>

            <!-- Status display -->
            <div class="roles-status" style="background: #f0f8ff; border: 1px solid #007cba; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <strong>📊 Statut actuel:</strong> <span id="roles-count"><?php echo count($allowed_roles); ?> rôle(s) sélectionné(s)</span>
                <div style="margin-top: 10px; font-size: 14px;">
                    Rôles actifs: <strong><?php echo implode(', ', array_map('ucfirst', $allowed_roles)); ?></strong>
                </div>
            </div>

            <!-- Simple form with checkboxes -->
            <form method="post" action="" id="roles-form" style="background: #fff; border: 1px solid #ddd; padding: 20px; border-radius: 8px;">
                <?php wp_nonce_field('pdf_builder_save_settings', 'pdf_builder_acces_nonce'); ?>

                <div class="roles-grid" style="display: grid; gap: 15px;">
                    <?php foreach ($all_roles as $role_key => $role) :
                        $role_name = translate_user_role($role['name']);
                        $is_selected = in_array($role_key, $allowed_roles);
                        $description = $role_descriptions[$role_key] ?? 'Rôle personnalisé';
                        $is_admin = $role_key === 'administrator';
                        ?>
                        <div class="role-item" style="display: flex; align-items: center; justify-content: space-between; padding: 15px; border: 1px solid #e1e1e1; border-radius: 6px; <?php echo $is_admin ? 'background: linear-gradient(135deg, #f0f8ff 0%, #e6f3ff 100%); border-color: #007cba;' : ''; ?>">
                            <div class="role-info" style="flex: 1;">
                                <label for="role_<?php echo esc_attr($role_key); ?>" style="cursor: pointer; display: block;">
                                    <strong style="color: #23282d;"><?php echo esc_html($role_name); ?></strong>
                                    <?php if ($is_admin) : ?>
                                        <span style="background: #007cba; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 10px;">🔒 Toujours actif</span>
                                    <?php endif; ?>
                                    <br>
                                    <span style="color: #666; font-size: 14px;"><?php echo esc_html($description); ?></span>
                                </label>
                            </div>
                            <div class="role-checkbox">
                                <input type="checkbox"
                                    id="role_<?php echo esc_attr($role_key); ?>"
                                    name="pdf_builder_allowed_roles[]"
                                    value="<?php echo esc_attr($role_key); ?>"
                                    <?php echo $is_selected ? 'checked' : ''; ?>
                                    <?php echo $is_admin ? 'disabled' : ''; ?>
                                    style="width: 20px; height: 20px; margin: 0;" />
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </form>

            <!-- Message d'aide pour la sauvegarde -->
            <aside style="background: #f0f8ff; border: 1px solid #007cba; padding: 15px; border-radius: 8px; margin-top: 20px;">
                <h4 style="margin: 0 0 10px 0; color: #007cba;">💡 Comment sauvegarder les rôles</h4>
                <p style="margin: 0;">
                    Cochez/décochez les rôles souhaités, puis utilisez le bouton <strong>"💾 Enregistrer"</strong> flottant en bas à droite pour sauvegarder.
                </p>
            </aside>

            <script>
            jQuery(document).ready(function($) {

                // Fonction pour mettre à jour le compteur global
                function updateGlobalStatus() {
                    var selectedRoles = [];
                    $('input[name="pdf_builder_allowed_roles[]"]:checked:not(:disabled)').each(function() {
                        selectedRoles.push($(this).val());
                    });

                    var count = selectedRoles.length;
                    $('#roles-count').text(count + ' rôle(s) sélectionné(s)');

                    // Mettre à jour la liste des rôles actifs
                    var roleNames = selectedRoles.map(function(role) {
                        return $('label[for="role_' + role + '"] strong').text() || role;
                    });

                    $('#roles-count').next().find('strong').text(roleNames.join(', ') || 'Aucun');
                }

                // Activer automatiquement les rôles demandés (Auteur, Contributeur, Abonné, Client)
                function activateSpecificRoles() {
                    var rolesToActivate = ['author', 'contributor', 'subscriber', 'customer'];

                    rolesToActivate.forEach(function(roleKey) {
                        var $checkbox = $('input[name="pdf_builder_allowed_roles[]"][value="' + roleKey + '"]:not(:disabled)');
                        if ($checkbox.length && !$checkbox.prop('checked')) {
                            $checkbox.prop('checked', true);
                            console.log('✅ Activation automatique du rôle:', roleKey);
                        }
                    });

                    updateGlobalStatus();
                }

                // Mettre à jour le compteur quand on change les checkboxes
                $(document).on('change', 'input[name="pdf_builder_allowed_roles[]"]', function() {
                    updateGlobalStatus();
                });

                // Initialiser le compteur
                updateGlobalStatus();

                // Activer automatiquement les rôles demandés au chargement de la page
                setTimeout(function() {
                    activateSpecificRoles();
                }, 500);

                console.log('🔄 Système de rôles initialisé - rôles activés automatiquement');
            });
            </script>




