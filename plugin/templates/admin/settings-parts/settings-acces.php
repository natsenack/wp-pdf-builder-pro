<?php // Acces tab content - Updated: 2025-11-18 20:20:00

    require_once __DIR__ . '/settings-helpers.php';

    global $wp_roles;
    $all_roles = $wp_roles->roles;
    $allowed_roles = pdf_builder_safe_get_option('pdf_builder_allowed_roles', ['administrator', 'editor', 'shop_manager']);
    if (!is_array($allowed_roles)) {
        $allowed_roles = ['administrator', 'editor', 'shop_manager'];
    }

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
                        </section>                    </div> <!-- Fin colonne principale -->

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

</div>

