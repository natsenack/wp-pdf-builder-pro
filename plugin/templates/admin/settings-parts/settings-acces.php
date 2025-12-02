<?php // Acces tab content - Updated: 2025-11-18 20:20:00 ?>
            <h2>👥 Gestion des Rôles et Permissions</h2>

            <!-- Message de confirmation que l'onglet est chargé -->
            <aside class="access-success-notice">
                ✅ Section Rôles chargée - Utilise le bouton "Enregistrer" flottant pour sauvegarder
            </aside>

            <?php
                global $wp_roles;
                $all_roles = $wp_roles->roles;
                $allowed_roles = get_option('pdf_builder_allowed_roles', ['administrator', 'editor', 'shop_manager']);
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
                                            <?php checked($is_selected); ?>
                                            <?php echo $is_admin ? 'disabled' : ''; ?> />
                                        <label for="role_<?php echo esc_attr($role_key); ?>" class="toggle-slider"></label>
                                    </div>
                                </article>
                                <?php
                            endforeach; ?>
                        </div>

                        

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const roleToggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
                                const selectedCount = document.getElementById('selected-count');
                                const selectAllBtn = document.getElementById('select-all-roles');
                                const selectCommonBtn = document.getElementById('select-common-roles');
                                const selectNoneBtn = document.getElementById('select-none-roles');

                                // Fonction pour mettre à jour le compteur
                                function updateSelectedCount() {
                                    const checkedBoxes = document.querySelectorAll('.toggle-switch input[type="checkbox"]:checked');
                                    if (selectedCount) {
                                        selectedCount.textContent = checkedBoxes.length;

                                    }
                                }

                                // Bouton Sélectionner Tout
                                if (selectAllBtn) {
                                    selectAllBtn.addEventListener('click', function() {
                                        const togglesLength = roleToggles.length;
                                        for (let i = 0; i < togglesLength; i++) {
                                            const checkbox = roleToggles[i];
                                            if (!checkbox.disabled) {
                                                checkbox.checked = true;
                                            }
                                        }
                                        // Différer la mise à jour du compteur pour éviter les violations de performance
                                        requestAnimationFrame(updateSelectedCount);
                                    });
                                }

                                // Bouton Rôles Courants
                                if (selectCommonBtn) {
                                    selectCommonBtn.addEventListener('click', function() {
                                        const commonRoles = ['administrator', 'editor', 'shop_manager'];
                                        const togglesLength = roleToggles.length;
                                        for (let i = 0; i < togglesLength; i++) {
                                            const checkbox = roleToggles[i];
                                            const isCommon = commonRoles.includes(checkbox.value);
                                            if (!checkbox.disabled) {
                                                checkbox.checked = isCommon;
                                            }
                                        }
                                        // Différer la mise à jour du compteur pour éviter les violations de performance
                                        requestAnimationFrame(updateSelectedCount);
                                    });
                                }

                                // Bouton Désélectionner Tout
                                if (selectNoneBtn) {
                                    selectNoneBtn.addEventListener('click', function() {
                                        const togglesLength = roleToggles.length;
                                        for (let i = 0; i < togglesLength; i++) {
                                            const checkbox = roleToggles[i];
                                            if (!checkbox.disabled) {
                                                checkbox.checked = false;
                                            }
                                        }
                                        // Différer la mise à jour du compteur pour éviter les violations de performance
                                        requestAnimationFrame(updateSelectedCount);
                                    });
                                }

                                // Mettre à jour le compteur quand un toggle change (avec debounce pour éviter les appels trop fréquents)
                                let updateTimeout;
                                roleToggles.forEach(function(checkbox) {
                                    checkbox.addEventListener('change', function() {
                                        // Debounce les appels pour éviter les appels trop fréquents
                                        clearTimeout(updateTimeout);
                                        updateTimeout = setTimeout(updateSelectedCount, 10);
                                    });
                                });

                                // Initialiser le compteur
                                updateSelectedCount();

                            });
                        </script>
                    </section>

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


