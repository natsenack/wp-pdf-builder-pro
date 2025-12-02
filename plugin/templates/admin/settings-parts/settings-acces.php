<?php // Acces tab content - Updated: 2025-11-18 20:20:00 ?>
            <h2>👥 Gestion des Rôles et Permissions</h2>

            <!-- Message de confirmation que l'onglet est chargé -->
            <aside style="margin-bottom: 20px; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
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

            <p style="margin-bottom: 20px;">Sélectionnez les rôles WordPress qui auront accès à PDF Builder Pro.</p>

            <!-- Disposition en colonnes -->
            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; align-items: start;">

                <!-- Colonne principale : toggles des rôles -->
                <div>

                    <!-- Access Settings Section (No Form - AJAX Centralized) -->
                    <section id="access-settings-container" aria-label="Paramètres d'accès">

                        <!-- Boutons de contrôle rapide -->
                        <nav style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #e9ecef;">
                            <button type="button" id="select-all-roles" class="button button-secondary" style="margin-right: 5px;">
                                Sélectionner Tout
                            </button>
                            <button type="button" id="select-common-roles" class="button button-secondary" style="margin-right: 5px;">
                                Rôles Courants
                            </button>
                            <button type="button" id="select-none-roles" class="button button-secondary" style="margin-right: 5px;">
                                Désélectionner Tout
                            </button>
                            <span class="description" style="margin-left: 10px;">
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

                        <style>
                            .roles-toggle-list {
                                max-width: 600px;
                            }

                            .role-toggle-item {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                padding: 15px 20px;
                                margin-bottom: 8px;
                                background: #f8f9fa;
                                border: 1px solid #e9ecef;
                                border-radius: 8px;
                                transition: all 0.2s ease;
                            }

                            .role-toggle-item:hover {
                                background: #e9ecef;
                                border-color: #dee2e6;
                            }

                            .role-toggle-item.admin-role {
                                background: #fce4ec;
                                border-color: #f8bbd9;
                            }

                            .role-info {
                                flex: 1;
                            }

                            .role-name {
                                font-weight: 600;
                                font-size: 15px;
                                color: #333;
                                margin-bottom: 2px;
                                display: flex;
                                align-items: center;
                                gap: 8px;
                            }

                            .role-name,
                            .role-description,
                            .role-key {
                                margin: 0;
                            }

                            .role-description {
                                font-size: 13px;
                                color: #666;
                                margin-bottom: 2px;
                            }

                            .role-key {
                                font-size: 11px;
                                color: #999;
                                font-family: monospace;
                            }

                            .toggle-switch {
                                position: relative;
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
                                transition: 0.3s;
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
                                transition: 0.3s;
                                border-radius: 50%;
                                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                            }

                            input:checked + .toggle-slider {
                                background-color: #2271b1;
                            }

                            input:checked + .toggle-slider:before {
                                transform: translateX(26px);
                            }

                            .toggle-switch input:disabled + .toggle-slider {
                                background-color: #d63384;
                                cursor: not-allowed;
                                opacity: 0.7;
                            }

                            .toggle-switch input:disabled:checked + .toggle-slider {
                                background-color: #d63384;
                            }

                            /* Animation au survol */
                            .toggle-slider:hover {
                                box-shadow: 0 0 8px rgba(34, 113, 177, 0.3);
                            }
                        </style>

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
                    <aside style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h4 style="margin-top: 0; color: #003d66; font-size: 14px;">🔐 Permissions Incluses</h4>
                        <p style="margin: 10px 0 15px 0; color: #003d66; font-size: 13px;">Les rôles sélectionnés auront accès à :</p>
                        <ul style="margin: 0; padding-left: 20px; color: #003d66; font-size: 13px; line-height: 1.5;">
                            <li>✅ Création, édition et suppression de templates PDF</li>
                            <li>✅ Génération et téléchargement de PDF</li>
                            <li>✅ Accès aux paramètres et configuration</li>
                            <li>✅ Prévisualisation avant génération</li>
                            <li>✅ Gestion des commandes WooCommerce (si applicable)</li>
                        </ul>
                    </aside>

                    <!-- Avertissement important -->
                    <aside style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h4 style="margin-top: 0; color: #856404; font-size: 14px;">⚠️ Informations Importantes</h4>
                        <ul style="margin: 0; padding-left: 20px; color: #856404; font-size: 13px; line-height: 1.5;">
                            <li>Les rôles non sélectionnés n'auront aucun accès à PDF Builder Pro</li>
                            <li>Le rôle "Administrator" a toujours accès complet, indépendamment</li>
                            <li>Minimum requis : au moins un rôle sélectionné</li>
                        </ul>
                    </aside>

                </div> <!-- Fin colonne informations -->

            </div> <!-- Fin disposition en colonnes -->

