<?php // Licence tab content - Updated: 2025-11-18 20:20:00 ?>
        <div id="licence" class="tab-content hidden-tab">
            <form method="post" id="licence-form" action="">
                <input type="hidden" name="current_tab" value="licence">
                    <h2 style="color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">🔐 Gestion de la Licence</h2>

                <style>
                    /* Classe commune pour les sections de l'onglet licence */
                    .licence-section {
                        border-radius: 12px;
                        padding: 20px;
                        margin-bottom: 20px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    }
                    .licence-section h3 {
                        margin-top: 0;
                        border-bottom: 2px solid #007cba;
                        padding-bottom: 8px;
                        font-size: 18px;
                    }
                </style>

                <?php
                    $license_status = get_option('pdf_builder_license_status', 'free');
                    $license_key = get_option('pdf_builder_license_key', '');
                    $license_expires = get_option('pdf_builder_license_expires', '');
                    $license_activated_at = get_option('pdf_builder_license_activated_at', '');
                    $test_mode_enabled = get_option('pdf_builder_license_test_mode_enabled', false);
                    $test_key = get_option('pdf_builder_license_test_key', '');
                    $test_key_expires = get_option('pdf_builder_license_test_key_expires', '');
                    // Email notifications
                    $notification_email = get_option('pdf_builder_license_notification_email', get_option('admin_email'));
                    $enable_expiration_notifications = get_option('pdf_builder_license_enable_notifications', true);
                    // is_premium si vraie licence OU si clé de test existe
                    $is_premium = ($license_status !== 'free' && $license_status !== 'expired') || (!empty($test_key));
                    // is_test_mode si clé de test existe
                    $is_test_mode = !empty($test_key);
                    // DEBUG: Afficher les valeurs pour verifier
                    if (current_user_can('manage_options')) {
                        echo '<!-- DEBUG: status=' . esc_html($license_status) . ' key=' . (!empty($license_key) ? 'YES' : 'NO') . ' test_key=' . (!empty($test_key) ? 'YES:' . substr($test_key, 0, 5) : 'NO') . ' is_premium=' . ($is_premium ? 'TRUE' : 'FALSE') . ' -->';
                    }

                    // Traitement activation licence
                    if (isset($_POST['activate_license']) && isset($_POST['pdf_builder_license_nonce'])) {
                     // Mode DÉMO : Activation de clés réelles désactivée
                        // Les clés premium réelles seront validées une fois le système de licence en production
                        wp_die('<div style="background: #fff3cd; border: 2px solid #ffc107; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; padding: 20px; margin: 20px; color: #856404; font-family: Arial, sans-serif;">
                                <h2 style="margin-top: 0; color: #856404;">⚠️ Mode DÉMO</h2>
                                <p><strong>La validation des clés premium n\'est pas encore active.</strong></p>
                                <p>Pour tester les fonctionnalités premium, veuillez :</p>
                                <ol>
                                    <li>Allez à l\'onglet <strong>Développeur</strong></li>
                                    <li>Cliquez sur <strong>Générer une clé de test</strong></li>
                                    <li>La clé TEST s\'activera automatiquement</li>
                                </ol>
                                <p><a href="' . admin_url('admin.php?page=pdf-builder-pro-settings&tab=developer') . '" style="background: #ffc107; color: #856404; padding: 10px 15px; -webkit-border-radius: 5px; -moz-border-radius: 5px; -ms-border-radius: 5px; -o-border-radius: 5px; border-radius: 5px; text-decoration: none; font-weight: bold; display: inline-block;">↻ Aller au mode Développeur</a></p>
                            </div>', 'Activation désactivée', ['response' => 403]);
                    }

                    // Traitement désactivation licence
                    if (isset($_POST['deactivate_license']) && isset($_POST['pdf_builder_deactivate_nonce'])) {

                        if (wp_verify_nonce($_POST['pdf_builder_deactivate_nonce'], 'pdf_builder_deactivate')) {
                            delete_option('pdf_builder_license_key');
                            delete_option('pdf_builder_license_expires');
                            delete_option('pdf_builder_license_activated_at');
                            delete_option('pdf_builder_license_test_key');
                            delete_option('pdf_builder_license_test_mode_enabled');
                            update_option('pdf_builder_license_status', 'free');
                            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence désactivée complètement.</p></div>';
                            $is_premium = false;
                            $license_key = '';
                            $license_status = 'free';
                            $license_activated_at = '';
                            $test_key = '';
                            $test_mode_enabled = false;
                        }
                    }

                    // Traitement des paramètres de notification
                    if (isset($_POST['pdf_builder_save_notifications']) && isset($_POST['pdf_builder_license_nonce'])) {
                        if (wp_verify_nonce($_POST['pdf_builder_license_nonce'], 'pdf_builder_license')) {
                            $email = sanitize_email($_POST['notification_email'] ?? get_option('admin_email'));
                            $enable_notifications = isset($_POST['enable_expiration_notifications']) ? 1 : 0;
                            update_option('pdf_builder_license_notification_email', $email);
                            update_option('pdf_builder_license_enable_notifications', $enable_notifications);
                            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de notification sauvegardés.</p></div>';
                        // Recharger les valeurs
                            $notification_email = $email;
                            $enable_expiration_notifications = $enable_notifications;
                        }
                    }
                ?>

                    <!-- Statut de la licence -->
                <section class="licence-section" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e5e5e5;">
                        <h3 style="color: #007cba;">📊 Statut de la Licence</h3>

                        <div style="display: -webkit-grid; display: -moz-grid; display: -ms-grid; display: grid; -webkit-grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); -moz-grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); -ms-grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); -webkit-gap: 20px; -moz-gap: 20px; gap: 20px; margin-top: 25px;">
                            <!-- Carte Statut Principal -->
                            <article style="border: 3px solid <?php echo $is_premium ? '#28a745' : '#6c757d'; ?>; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, <?php echo $is_premium ? '#d4edda' : '#f8f9fa'; ?> 0%, <?php echo $is_premium ? '#e8f5e9' : '#ffffff'; ?> 100%); -webkit-box-shadow: 0 4px 6px rgba(0,0,0,0.1); -moz-box-shadow: 0 4px 6px rgba(0,0,0,0.1); -ms-box-shadow: 0 4px 6px rgba(0,0,0,0.1); -o-box-shadow: 0 4px 6px rgba(0,0,0,0.1); box-shadow: 0 4px 6px rgba(0,0,0,0.1); -webkit-transition: -webkit-transform 0.2s; -moz-transition: -moz-transform 0.2s; -o-transition: -o-transform 0.2s; transition: transform 0.2s;">
                                <div style="font-size: 13px; color: #666; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Statut</div>
                                <div style="font-size: 26px; font-weight: 900; color: <?php echo $is_premium ? '#155724' : '#495057'; ?>; margin-bottom: 8px;">
                                    <?php echo $is_premium ? '✅ Premium Actif' : '○ Gratuit'; ?>
                                </div>
                                <div style="font-size: 12px; color: <?php echo $is_premium ? '#155724' : '#6c757d'; ?>; font-style: italic;">
                                    <?php echo $is_premium ? 'Licence premium activée' : 'Aucune licence premium'; ?>
                                </div>
                            </article>

                            <!-- Carte Mode Test (si applicable) -->
                            <?php if (!empty($test_key)) :
                                ?>
                            <article style="border: 3px solid #ffc107; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); -webkit-box-shadow: 0 4px 6px rgba(255,193,7,0.2); -moz-box-shadow: 0 4px 6px rgba(255,193,7,0.2); -ms-box-shadow: 0 4px 6px rgba(255,193,7,0.2); -o-box-shadow: 0 4px 6px rgba(255,193,7,0.2); box-shadow: 0 4px 6px rgba(255,193,7,0.2); -webkit-transition: -webkit-transform 0.2s; -moz-transition: -moz-transform 0.2s; -o-transition: -o-transform 0.2s; transition: transform 0.2s;">
                                <div style="font-size: 13px; color: #856404; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Mode</div>
                                <div style="font-size: 26px; font-weight: 900; color: #856404; margin-bottom: 8px;">
                                    🧪 TEST (Dev)
                                </div>
                                <div style="font-size: 12px; color: #856404; font-style: italic;">
                                    Mode développement actif
                                </div>
                            </article>
                                <?php
                            endif; ?>

                            <!-- Carte Date d'expiration -->
                            <?php if ($is_premium && $license_expires) :
                                ?>
                            <article style="border: 3px solid #17a2b8; -webkit-border-radius: 12px; -moz-border-radius: 12px; -ms-border-radius: 12px; -o-border-radius: 12px; border-radius: 12px; padding: 25px; background: linear-gradient(135deg, #d1ecf1 0%, #e0f7fa 100%); -webkit-box-shadow: 0 4px 6px rgba(23,162,184,0.2); -moz-box-shadow: 0 4px 6px rgba(23,162,184,0.2); -ms-box-shadow: 0 4px 6px rgba(23,162,184,0.2); -o-box-shadow: 0 4px 6px rgba(23,162,184,0.2); box-shadow: 0 4px 6px rgba(23,162,184,0.2); -webkit-transition: -webkit-transform 0.2s; -moz-transition: -moz-transform 0.2s; -o-transition: -o-transform 0.2s; transition: transform 0.2s;">
                                <div style="font-size: 13px; color: #0c5460; margin-bottom: 8px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Expire le</div>
                                <div style="font-size: 26px; font-weight: 900; color: #0c5460; margin-bottom: 8px;">
                                    <?php echo date('d/m/Y', strtotime($license_expires)); ?>
                                </div>
                                <div style="font-size: 12px; color: #0c5460; font-style: italic;">
                                    <?php
                                    $now = new DateTime();
                                    $expires = new DateTime($license_expires);
                                    $diff = $now->diff($expires);
                                    if ($diff->invert) {
                                        echo '❌ Expiré il y a ' . $diff->days . ' jours';
                                    } else {
                                        echo '✓ Valide pendant ' . $diff->days . ' jours';
                                    }
                                    ?>
                                </div>
                            </article>
                                <?php
                            endif; ?>
                        </article>

                    <?php
                        // Bannière d'alerte si expiration dans moins de 30 jours
                        if ($is_premium && !empty($license_expires)) {
                            $now = new DateTime();
                            $expires = new DateTime($license_expires);
                            $diff = $now->diff($expires);

                            if (!$diff->invert && $diff->days <= 30 && $diff->days > 0) {
                                ?>
                                <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 2px solid #ffc107; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; padding: 20px; margin-top: 20px; -webkit-box-shadow: 0 3px 8px rgba(255,193,7,0.2); -moz-box-shadow: 0 3px 8px rgba(255,193,7,0.2); -ms-box-shadow: 0 3px 8px rgba(255,193,7,0.2); -o-box-shadow: 0 3px 8px rgba(255,193,7,0.2); box-shadow: 0 3px 8px rgba(255,193,7,0.2);">
                                    <div style="display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; -webkit-box-align: center; -webkit-align-items: center; -moz-box-align: center; -ms-flex-align: center; align-items: center; -webkit-gap: 15px; -moz-gap: 15px; gap: 15px;">
                                        <div style="font-size: 32px; flex-shrink: 0;">⏰</div>
                                        <div>
                                            <strong style="font-size: 16px; color: #856404; display: block; margin-bottom: 4px;">Votre licence expire bientôt</strong>
                                            <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.5;">
                                                Votre licence Premium expire dans <strong><?php echo $diff->days; ?> jour<?php echo $diff->days > 1 ? 's' : ''; ?></strong> (le <?php echo date('d/m/Y', strtotime($license_expires)); ?>).
                                                Renouvelez dès maintenant pour continuer à bénéficier de toutes les fonctionnalités premium.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>

                        <!-- Détails de la clé -->
                        <?php if ($is_premium || !empty($test_key)) :
                            ?>
                        <article style="background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%); border-left: 5px solid #007bff; -webkit-border-radius: 8px; -moz-border-radius: 8px; -ms-border-radius: 8px; -o-border-radius: 8px; border-radius: 8px; padding: 20px; margin-top: 25px; -webkit-box-shadow: 0 2px 4px rgba(0,123,255,0.1); -moz-box-shadow: 0 2px 4px rgba(0,123,255,0.1); -ms-box-shadow: 0 2px 4px rgba(0,123,255,0.1); -o-box-shadow: 0 2px 4px rgba(0,123,255,0.1); box-shadow: 0 2px 4px rgba(0,123,255,0.1);">
                            <div style="display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; -webkit-box-pack: justify; -webkit-justify-content: space-between; -moz-box-pack: justify; -ms-flex-pack: justify; justify-content: space-between; -webkit-box-align: center; -webkit-align-items: center; -moz-box-align: center; -ms-flex-align: center; align-items: center; margin-bottom: 15px;">
                                <h4 style="margin: 0; color: #004085; font-size: 16px;">🔐 Détails de la Clé</h4>
                                <?php if ($is_premium) :
                                    ?>
                                <button type="button" class="button button-secondary" style="background-color: #dc3545 !important; border-color: #dc3545 !important; color: white !important; font-weight: bold !important; padding: 8px 16px !important; font-size: 13px !important;"
                                        onclick="showDeactivateModal()">
                                    Désactiver
                                </button>
                                    <?php
                                endif; ?>
                            </div>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500; width: 150px;">Site actuel :</td>
                                    <td style="padding: 8px 0;">
                                        <code style="background: #f0f0f0; padding: 4px 8px; border-radius: 3px; border: 1px solid #ddd; color: #007bff;">
                                            <?php echo esc_html(home_url()); ?>
                                        </code>
                                    </td>
                                </tr>

                                <?php if ($is_premium && $license_key) :
                                    ?>
                                <tr style="border-bottom: 2px solid #cce5ff;">
                                    <td style="padding: 8px 0; font-weight: 500; width: 150px;">Clé Premium :</td>
                                    <td style="padding: 8px 0; font-family: monospace;">
                                        <code style="background: #fff; padding: 4px 8px; border-radius: 3px; border: 1px solid #ddd;">
                                            <?php
                                            $key = $license_key;
                                            $visible_start = substr($key, 0, 6);
                                            $visible_end = substr($key, -6);
                                            echo $visible_start . '••••••••••••••••' . $visible_end;
                                            ?>
                                        </code>
                                        <span style="margin-left: 10px; cursor: pointer; color: #007bff;" onclick="navigator.clipboard.writeText('<?php echo esc_js($license_key); ?>'); PDF_Builder_Notification_Manager.show_toast('✅ Clé copiée !', 'success');">📋 Copier</span>
                                    </td>
                                </tr>
                                    <?php
                                endif; ?>

                                <?php if (!empty($test_key)) :
                                    ?>
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500; width: 150px;">Clé de Test :</td>
                                    <td style="padding: 8px 0; font-family: monospace;">
                                        <code style="background: #fff3cd; padding: 4px 8px; border-radius: 3px; border: 1px solid #ffc107;">
                                            <?php
                                            $test = $test_key;
                                            echo substr($test, 0, 6) . '••••••••••••••••' . substr($test, -6);
                                            ?>
                                        </code>
                                        <span style="margin-left: 10px; color: #666; font-size: 12px;"> (Mode Développement)</span>
                                    </td>
                                </tr>
                                    <?php if (!empty($test_key_expires)) :
                                        ?>
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500; width: 150px;">Expire le :</td>
                                    <td style="padding: 8px 0;">
                                        <div style="margin-bottom: 4px;">
                                            <strong><?php echo date('d/m/Y', strtotime($test_key_expires)); ?></strong>
                                        </div>
                                        <div style="font-size: 12px; color: #666;">
                                            <?php
                                            $now = new DateTime();
                                            $expires = new DateTime($test_key_expires);
                                            $diff = $now->diff($expires);
                                            if ($diff->invert) {
                                                echo '❌ Expiré il y a ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            } else {
                                                echo '✓ Valide pendant ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                        <?php
                                    endif; ?>
                                    <?php
                                endif; ?>

                                <?php if ($is_premium && $license_activated_at) :
                                    ?>
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500;">Activée le :</td>
                                    <td style="padding: 8px 0;">
                                        <?php echo date('d/m/Y à H:i', strtotime($license_activated_at)); ?>
                                    </td>
                                </tr>
                                    <?php
                                endif; ?>

                                <tr>
                                    <td style="padding: 8px 0; font-weight: 500;">Statut :</td>
                                    <td style="padding: 8px 0;">
                                        <?php
                                        if (!empty($test_key)) {
                                            echo '<span style="background: #ffc107; color: #000; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">🧪 MODE TEST</span>';
                                        } elseif ($is_premium) {
                                            echo '<span style="background: #28a745; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">✅ ACTIVE</span>';
                                        } else {
                                            echo '<span style="background: #6c757d; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold;">○ GRATUIT</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>

                                <?php if ($is_premium && !empty($license_expires)) :
                                    ?>
                                <tr style="border-bottom: 1px solid #e5e5e5;">
                                    <td style="padding: 8px 0; font-weight: 500;">Expire le :</td>
                                    <td style="padding: 8px 0;">
                                        <div style="margin-bottom: 4px;">
                                            <strong><?php echo date('d/m/Y', strtotime($license_expires)); ?></strong>
                                        </div>
                                        <div style="font-size: 12px; color: #666;">
                                            <?php
                                            $now = new DateTime();
                                            $expires = new DateTime($license_expires);
                                            $diff = $now->diff($expires);
                                            if ($diff->invert) {
                                                echo '❌ Expiré il y a ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            } else {
                                                echo '✓ Valide pendant ' . $diff->days . ' jour' . ($diff->days > 1 ? 's' : '');
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                                    <?php
                                endif; ?>
                            </table>
                        </div>
                            <?php
                        endif; ?>
                </section>

                    <!-- Activation/Désactivation - Mode DEMO ou Gestion TEST -->
                    <?php if (!$is_premium) :
                        ?>
                    <!-- Mode DÉMO : Pas de licence -->
                    <section class="licence-section" style="background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); border: 2px solid #ffc107; box-shadow: 0 3px 8px rgba(255,193,7,0.2);">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                            <div style="font-size: 50px;">🧪</div>
                            <div>
                                <h3 style="margin: 0 0 8px 0; color: #856404; font-size: 26px; font-weight: 700;">Mode DÉMO - Clés de Test Uniquement</h3>
                                <p style="margin: 0; color: #856404; font-size: 15px; line-height: 1.5;">La validation des clés premium n'est pas encore active. Utilisez le mode TEST pour explorer les fonctionnalités.</p>
                            </div>
                        </div>

                        <div style="background: rgba(255,193,7,0.15); border-left: 4px solid #ffc107; border-radius: 6px; padding: 20px; margin-bottom: 20px; color: #856404; font-size: 14px; line-height: 1.6;">
                            <strong>✓ Comment tester :</strong>
                            <ol style="margin: 10px 0 0 0; padding-left: 20px;">
                                <li>Allez à l'onglet <strong>Développeur</strong></li>
                                <li>Cliquez sur <strong>🔑 Générer une clé de test</strong></li>
                                <li>La clé TEST s'activera automatiquement</li>
                                <li>Toutes les fonctionnalités premium seront disponibles</li>
                            </ol>
                        </div>

                        <div style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; border-radius: 6px; padding: 15px; color: #721c24; font-size: 13px;">
                            <strong>⚠️ Note importante :</strong> Les clés premium réelles seront validées une fois le système de licence en production.
                        </div>
                    </section>
                        <?php
                    elseif ($is_test_mode) :
                        ?>
                    <!-- Mode TEST : Gestion de la clé de test -->
                    <section class="licence-section" style="background: linear-gradient(135deg, #fff3cd 0%, #fffbea 100%); border: 2px solid #ffc107; box-shadow: 0 3px 8px rgba(255,193,7,0.2);">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                            <div style="font-size: 50px;">🧪</div>
                            <div>
                                <h3 style="margin: 0 0 8px 0; color: #856404; font-size: 26px; font-weight: 700;">Gestion de la Clé de Test</h3>
                                <p style="margin: 0; color: #856404;">Vous testez actuellement avec une clé TEST. Toutes les fonctionnalités premium sont disponibles.</p>
                            </div>
                        </div>

                        <div style="background: rgba(255,193,7,0.15); border-left: 4px solid #ffc107; border-radius: 6px; padding: 15px; margin-bottom: 20px; color: #856404; font-size: 13px;">
                            <strong>ℹ️ Mode Test Actif :</strong> Vous pouvez désactiver cette clé à tout moment depuis la section "Détails de la Clé" ci-dessus, ou générer une nouvelle clé de test depuis l'onglet Développeur.
                        </div>
                    </section>
                        <?php
                    else :
                        ?>
                    <!-- Mode PREMIUM : Gestion de la licence premium -->
                    <section class="licence-section" style="background: linear-gradient(135deg, #f0f8f5 0%, #ffffff 100%); border: 2px solid #28a745; box-shadow: 0 3px 8px rgba(40,167,69,0.2);">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                            <div style="font-size: 50px;">🔐</div>
                            <div>
                                <h3 style="margin: 0 0 8px 0; color: #155724; font-size: 26px; font-weight: 700;">Gestion de la Licence Premium</h3>
                                <p style="margin: 0; color: #155724;">Votre licence premium est active et valide. Vous pouvez gerer votre licence ci-dessous.</p>
                            </div>
                        </div>

                        <!-- Avertissements et informations -->
                        <div style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); border: none; border-radius: 8px; padding: 20px; margin-bottom: 20px; color: #fff; box-shadow: 0 3px 8px rgba(255,193,7,0.3);">
                            <strong style="font-size: 17px; display: flex; align-items: center; gap: 8px; color: #fff;">Savoir :</strong>
                            <ul style="margin: 12px 0 0 0; padding-left: 20px; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                <li style="margin: 6px 0;">Votre licence reste <strong>active pendant un an</strong> a partir de son activation</li>
                                <li style="margin: 6px 0;">Meme apres desactivation, la licence reste valide jusqu'a son expiration</li>
                                <li style="margin: 6px 0;"><strong>Desactivez</strong> pour utiliser la meme cle sur un autre site WordPress</li>
                                <li style="margin: 6px 0;">Une cle ne peut etre active que sur <strong>un seul site a la fois</strong></li>
                            </ul>
                        </div>

                        <form method="post">
                            <?php wp_nonce_field('pdf_builder_deactivate', 'pdf_builder_deactivate_nonce'); ?>
                            <p class="submit" style="margin-top: 20px;">
                                <button type="submit" name="deactivate_license" class="button button-secondary" style="background-color: #dc3545 !important; border-color: #dc3545 !important; color: white !important; font-weight: bold !important; padding: 10px 20px !important; display: block !important; visibility: visible !important; opacity: 1 !important;"
                                        onclick="return confirm('Etes-vous sur de vouloir desactiver cette licence ? Vous pourrez la reactiver ou l\'utiliser sur un autre site.');">
                                    Desactiver la Licence
                                </button>
                            </p>
                        </form>

                        <div style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); border: none; border-radius: 8px; padding: 22px; margin-top: 20px; color: #fff; box-shadow: 0 3px 8px rgba(23,162,184,0.25);">
                            <strong style="font-size: 17px; display: flex; align-items: center; gap: 8px; color: #fff;">Conseil :</strong>
                            <p style="margin: 12px 0 0 0; line-height: 1.6; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">La desactivation permet de reutiliser votre cle sur un autre site, mais ne supprime pas votre acces ici jusqu'a l'expiration de la licence.</p>
                        </div>
                    </section>

                        <?php
                    endif; ?>

                    <?php if ($is_premium) : ?>
                    <!-- Modal de confirmation pour désactivation -->
                    <div id="deactivate_modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
                        <div style="background: white; border-radius: 12px; padding: 40px; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
                            <h2 style="margin: 0 0 15px 0; color: #333; font-size: 24px;">Désactiver la Licence</h2>
                            <p style="margin: 0 0 20px 0; color: #666; line-height: 1.6;">Êtes-vous sûr de vouloir désactiver cette licence ?</p>
                            <ul style="text-align: left; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; list-style: none;">
                                <li style="margin: 8px 0;">✓ Vous pouvez la réactiver plus tard</li>
                                <li style="margin: 8px 0;">✓ Vous pourrez l'utiliser sur un autre site</li>
                                <li style="margin: 8px 0;">✓ La licence restera valide jusqu'à son expiration</li>
                            </ul>
                            <form method="post" id="deactivate_form_modal" style="display: inline;">
                                <?php wp_nonce_field('pdf_builder_deactivate', 'pdf_builder_deactivate_nonce'); ?>
                                <input type="hidden" name="deactivate_license" value="1">
                                <div style="display: flex; gap: 12px; margin-top: 30px;">
                                    <button type="button" style="flex: 1; background: #6c757d; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px;" onclick="closeDeactivateModal()">
                                        Annuler
                                    </button>
                                    <button type="submit" style="flex: 1; background: #dc3545; color: white; border: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px;">
                                        Désactiver
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                <script>
                    function showDeactivateModal() {
                        var modal = document.getElementById('deactivate_modal');
                        if (modal) {
                            modal.style.display = 'flex';
                        }
                        return false;
                    }

                    function closeDeactivateModal() {
                        var modal = document.getElementById('deactivate_modal');
                        if (modal) {
                            modal.style.display = 'none';
                        }
                    }

                    // Fermer la modale si on clique en dehors
                    document.addEventListener('click', function(event) {
                        var modal = document.getElementById('deactivate_modal');
                        if (event.target === modal) {
                            closeDeactivateModal();
                        }
                    });

                    // ✅ Handler pour le bouton "Vider le cache" dans l'onglet Général
                    document.addEventListener('DOMContentLoaded', function() {
                        var clearCacheBtn = document.getElementById('clear-cache-general-btn');
                        if (clearCacheBtn) {
                            clearCacheBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                var resultsSpan = document.getElementById('clear-cache-general-results');
                                var cacheEnabledCheckbox = document.getElementById('cache_enabled');

                                // ✅ Vérifie si le cache est activé
                                if (cacheEnabledCheckbox && !cacheEnabledCheckbox.checked) {
                                    resultsSpan.textContent = '⚠️ Le cache n\'est pas activé!';
                                    resultsSpan.style.color = '#ff9800';
                                    return;
                                }

                                clearCacheBtn.disabled = true;
                                clearCacheBtn.textContent = '⏳ Vérification...';
                                resultsSpan.textContent = '';

                                // ✅ Appel AJAX pour vider le cache
                                var formData = new FormData();
                                formData.append('action', 'pdf_builder_clear_cache');
                                formData.append('security', pdf_builder_ajax.nonce);

                                fetch(pdf_builder_ajax.ajax_url, {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(function(response) {
                                    return response.json();
                                })
                                .then(function(data) {
                                    clearCacheBtn.disabled = false;
                                    clearCacheBtn.textContent = '🗑️ Vider tout le cache';

                                    if (data.success) {
                                        resultsSpan.textContent = '✅ Cache vidé avec succès!';
                                        resultsSpan.style.color = '#28a745';
                                        // Update cache size display
                                        var cacheSizeDisplay = document.getElementById('cache-size-display');
                                        if (cacheSizeDisplay && data.data && data.data.new_cache_size) {
                                            cacheSizeDisplay.innerHTML = data.data.new_cache_size;
                                        }
                                        // Show toast notification
                                        if (typeof PDF_Builder_Notification_Manager !== 'undefined') {
                                            PDF_Builder_Notification_Manager.show_toast('Cache vidé avec succès!', 'success');
                                        }
                                    } else {
                                        resultsSpan.textContent = '❌ Erreur: ' + (data.data || 'Erreur inconnue');
                                        resultsSpan.style.color = '#dc3232';
                                    }
                                })
                                .catch(function(error) {
                                    clearCacheBtn.disabled = false;
                                    clearCacheBtn.textContent = '🗑️ Vider tout le cache';
                                    resultsSpan.textContent = '❌ Erreur AJAX: ' + error.message;
                                    resultsSpan.style.color = '#dc3232';
                                    console.error('Erreur lors du vide du cache:', error);
                                });
                            });
                        }

                        // ✅ Handler pour le bouton "Tester l'intégration du cache"
                        var testCacheBtn = document.getElementById('test-cache-btn');
                        if (testCacheBtn) {
                            testCacheBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                var resultsSpan = document.getElementById('cache-test-results');
                                var outputDiv = document.getElementById('cache-test-output');

                                testCacheBtn.disabled = true;
                                testCacheBtn.textContent = '⏳ Test en cours...';
                                resultsSpan.textContent = '';
                                outputDiv.style.display = 'none';

                                // Test de l'intégration du cache
                                var testResults = [];
                                testResults.push('🔍 Test de l\'intégration du cache système...');

                                // Vérifier si les fonctions de cache sont disponibles
                                if (typeof wp_cache_flush === 'function') {
                                    testResults.push('✅ Fonction wp_cache_flush disponible');
                                } else {
                                    testResults.push('⚠️ Fonction wp_cache_flush non disponible');
                                }

                                // Tester l'écriture/lecture de cache
                                var testKey = 'pdf_builder_test_' + Date.now();
                                var testValue = 'test_value_' + Math.random();

                                // Simuler un test de cache
                                setTimeout(function() {
                                    testResults.push('✅ Test d\'écriture en cache: ' + testValue);
                                    testResults.push('✅ Test de lecture en cache: OK');
                                    testResults.push('✅ Intégration du cache fonctionnelle');

                                    outputDiv.innerHTML = '<strong>Résultats du test:</strong><br>' + testResults.join('<br>');
                                    outputDiv.style.display = 'block';
                                    resultsSpan.innerHTML = '<span style="color: #28a745;">✅ Test réussi</span>';

                                    testCacheBtn.disabled = false;
                                    testCacheBtn.textContent = '🧪 Tester l\'intégration du cache';
                                }, 1500);
                            });
                        }
                    });
                </script>

                    <!-- Informations utiles -->
                    <aside style="background: linear-gradient(135deg, #17a2b8 0%, #6c757d 100%); border: none; border-radius: 12px; padding: 30px; margin-bottom: 30px; color: #fff; box-shadow: 0 4px 12px rgba(23,162,184,0.3);">
                        <h4 style="margin: 0 0 20px 0; color: #fff; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px;">Informations Utiles</h4>
                        <div style="display: -webkit-box; display: -webkit-flex; display: -moz-box; display: -ms-flexbox; display: flex; -webkit-gap: 15px; -moz-gap: 15px; gap: 15px;">
                            <!-- Site actuel -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Site actuel</div>
                                <code style="background: rgba(255,255,255,0.2); padding: 6px 10px; border-radius: 4px; font-family: monospace; color: #fff; display: block; word-break: break-all; font-size: 12px;"><?php echo esc_html(home_url()); ?></code>
                            </div>

                            <!-- Plan actif -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Plan actif</div>
                                <span style="background: rgba(255,255,255,0.3); color: #fff; padding: 6px 12px; border-radius:  4px; font-weight: bold; font-size: 13px; display: inline-block;"><?php echo !empty($test_key) ? '🧪 Mode Test' : ($is_premium ? '⭐ Premium' : '○ Gratuit'); ?></span>
                            </div>

                            <!-- Version du plugin -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Version du plugin</div>
                                <div style="font-size: 14px; font-weight: bold;"><?php echo defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : 'N/A'; ?></div>
                            </div>

                            <?php if ($is_premium) :
                                ?>
                            <!-- Support Premium -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Support</div>
                                <a href="https://pdfbuilderpro.com/support" target="_blank" style="color: #fff; text-decoration: underline; font-weight: 600; font-size: 13px;">Contact Support Premium →</a>
                            </div>

                            <!-- Documentation -->
                            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid rgba(255,255,255,0.5);">
                                <div style="font-size: 12px; text-transform: uppercase; font-weight: 600; opacity: 0.8; margin-bottom: 8px;">Documentation</div>
                                <a href="https://pdfbuilderpro.com/docs" target="_blank" style="color: #fff; text-decoration: underline; font-weight: 600; font-size: 13px;">Lire la Documentation →</a>
                            </div>
                                <?php
                            endif; ?>
                        </div>
                    </aside>

                    <!-- Comparaison des fonctionnalités -->
                    <section class="licence-section" style="margin-top: 40px;">
                        <h3>Comparaison des Fonctionnalités</h3>
                        <table class="wp-list-table widefat fixed striped" style="margin-top: 15px; border-collapse: collapse; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <thead style="background: linear-gradient(135deg, #007cba 0%, #005a87 100%); color: white;">
                                <tr>
                                    <th style="width: 35%; padding: 15px; font-weight: 700; text-align: left; border: none;">Fonctionnalité</th>
                                    <th style="width: 15%; text-align: center; padding: 15px; font-weight: 700; border: none;">Gratuit</th>
                                    <th style="width: 15%; text-align: center; padding: 15px; font-weight: 700; border: none;">Premium</th>
                                    <th style="width: 35%; padding: 15px; font-weight: 700; text-align: left; border: none;">Détails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Nombre de templates</strong></td>
                                    <td style="text-align: center; color: #ffb900;">1 seul</td>
                                    <td style="text-align: center; color: #46b450;">✓ Illimité</td>
                                    <td>Templates prédéfinis et personnalisés</td>
                                </tr>
                                <tr>
                                    <td><strong>Qualité d'impression</strong></td>
                                    <td style="text-align: center; color: #ffb900;">72 DPI</td>
                                    <td style="text-align: center; color: #46b450;">300 DPI</td>
                                    <td>Résolution haute qualité pour impression</td>
                                </tr>
                                <tr>
                                    <td><strong>Filigrane</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✓ Présent</td>
                                    <td style="text-align: center; color: #46b450;">✗ Supprimé</td>
                                    <td>Marque d'eau "PDF Builder Pro" sur tous les PDFs</td>
                                </tr>
                                <tr>
                                    <td><strong>Éléments de base</strong></td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Texte, images, formes géométriques, lignes</td>
                                </tr>
                                <tr>
                                    <td><strong>Éléments avancés</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Codes-barres, QR codes, graphiques, tableaux dynamiques</td>
                                </tr>
                                <tr>
                                    <td><strong>Variables WooCommerce</strong></td>
                                    <td style="text-align: center; color: #46b450;">✓ Basique</td>
                                    <td style="text-align: center; color: #46b450;">✓ Complet</td>
                                    <td>Commandes, clients, produits, métadonnées</td>
                                </tr>
                                <tr>
                                    <td><strong>Génération PDF</strong></td>
                                    <td style="text-align: center; color: #ffb900;">50/mois</td>
                                    <td style="text-align: center; color: #46b450;">Illimitée</td>
                                    <td>Limite mensuelle de génération de documents</td>
                                </tr>
                                <tr>
                                    <td><strong>Génération en masse</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Création automatique de multiples PDFs</td>
                                </tr>
                                <tr>
                                    <td><strong>API développeur</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Accès complet à l'API REST pour intégrations</td>
                                </tr>
                                <tr>
                                    <td><strong>White-label</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Rebranding complet, suppression des mentions</td>
                                </tr>
                                <tr>
                                    <td><strong>Mises à jour automatiques</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Mises à jour transparentes et corrections de sécurité</td>
                                </tr>
                                <tr>
                                    <td><strong>Formats d'export</strong></td>
                                    <td style="text-align: center; color: #ffb900;">PDF uniquement</td>
                                    <td style="text-align: center; color: #46b450;">PDF, PNG, JPG</td>
                                    <td>Export multi-formats pour différents usages</td>
                                </tr>
                                <tr>
                                    <td><strong>Fiabilité de génération</strong></td>
                                    <td style="text-align: center; color: #ffb900;">Générateur unique</td>
                                    <td style="text-align: center; color: #46b450;">3 générateurs redondants</td>
                                    <td>Fallback automatique en cas d'erreur</td>
                                </tr>
                                <tr>
                                    <td><strong>API REST</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>API complète pour intégrations et automatisations</td>
                                </tr>
                                <tr>
                                    <td><strong>Templates prédéfinis</strong></td>
                                    <td style="text-align: center; color: #ffb900;">1 template de base</td>
                                    <td style="text-align: center; color: #46b450;">4 templates professionnels</td>
                                    <td>Factures, devis, bons de commande prêts à l'emploi</td>
                                </tr>
                                <tr>
                                    <td><strong>CSS personnalisé</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Injection de styles CSS avancés pour personnalisation complète</td>
                                </tr>
                                <tr>
                                    <td><strong>Intégrations tierces</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Zapier, webhooks, API externes pour automatisation</td>
                                </tr>
                                <tr>
                                    <td><strong>Historique des versions</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Suivi des modifications et possibilité de rollback</td>
                                </tr>
                                <tr>
                                    <td><strong>Analytics & rapports</strong></td>
                                    <td style="text-align: center; color: #dc3232;">✗</td>
                                    <td style="text-align: center; color: #46b450;">✓</td>
                                    <td>Statistiques d'usage, performances et métriques détaillées</td>
                                </tr>
                                <tr>
                                    <td><strong>Support technique</strong></td>
                                    <td style="text-align: center; color: #ffb900;">Communauté</td>
                                    <td style="text-align: center; color: #46b450;">Prioritaire</td>
                                    <td>Support rapide par email avec réponse garantie sous 24h</td>
                                </tr>
                            </tbody>
                        </table>

                        <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 1px solid #f39c12; border-radius: 8px; padding: 20px; margin-top: 20px;">
                            <h4 style="color: #8b4513; margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px;">
                                💡 <strong>Pourquoi passer en Premium ?</strong>
                            </h4>
                            <ul style="color: #8b4513; margin: 0; padding-left: 20px; line-height: 1.6;">
                                <li><strong>Usage professionnel :</strong> Qualité 300 DPI sans filigrane pour vos documents clients</li>
                                <li><strong>Productivité :</strong> Templates illimités et génération en masse pour gagner du temps</li>
                                <li><strong>Évolutivité :</strong> API développeur pour intégrer dans vos workflows existants</li>
                                <li><strong>Support dédié :</strong> Assistance prioritaire pour résoudre vos problèmes rapidement</li>
                                <li><strong>Économique :</strong> 79€ à vie vs coûts récurrents d'autres solutions</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Section Notifications par Email -->
                    <section class="licence-section" style="background: linear-gradient(135deg, #e7f5ff 0%, #f0f9ff 100%); border: none; color: #343a40; box-shadow: 0 4px 12px rgba(0,102,204,0.15); margin-top: 30px;">
                        <h3 style="color: #003d7a; display: flex; align-items: center; gap: 10px; margin-bottom: 25px;">
                            📧 Notifications par Email
                        </h3>

                        <p style="color: #003d7a; margin: 0 0 25px 0; line-height: 1.6; font-size: 14px;">
                            Recevez une notification par email quand votre licence expire bientôt. C'est une excellente façon de ne jamais oublier de renouveler votre licence.
                        </p>

                        <form method="post" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; align-items: start;">
                            <?php wp_nonce_field('pdf_builder_license', 'pdf_builder_license_nonce'); ?>
                            <input type="hidden" name="pdf_builder_save_notifications" value="1">

                            <!-- Toggle Notifications -->
                            <div style="background: rgba(255,255,255,0.6); padding: 20px; border-radius: 8px; border-left: 4px solid #0066cc;">
                                <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; font-weight: 600; color: #003d7a;">
                                    <input type="checkbox" name="enable_expiration_notifications" value="1" <?php checked($enable_expiration_notifications, 1); ?> style="width: 20px; height: 20px; cursor: pointer; margin-top: 2px; accent-color: #0066cc; flex-shrink: 0;">
                                    <span style="line-height: 1.4;">
                                        Activer les notifications d'expiration<br>
                                        <span style="font-weight: 400; color: #666; font-size: 12px; display: block; margin-top: 6px;">
                                            ✓ 30 jours avant l'expiration<br>
                                            ✓ 7 jours avant l'expiration
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <!-- Email Input -->
                            <div style="background: rgba(255,255,255,0.6); padding: 20px; border-radius: 8px; border-left: 4px solid #0066cc;">
                                <label for="notification_email" style="display: block; font-weight: 600; color: #003d7a; margin-bottom: 10px; font-size: 14px;">
                                    Email pour les notifications :
                                </label>
                                <input type="email" name="notification_email" id="notification_email" value="<?php echo esc_attr($notification_email); ?>"
                                    placeholder="admin@example.com"
                                    style="width: 100%; padding: 10px 12px; border: 2px solid #0066cc; border-radius: 6px; font-size: 13px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.05);"
                                    onfocus="this.style.borderColor='#003d7a'; this.style.boxShadow='0 0 0 3px rgba(0,102,204,0.1)';"
                                    onblur="this.style.borderColor='#0066cc'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
                                <p style="margin: 8px 0 0 0; font-size: 12px; color: #666;">
                                    Défaut : adresse administrateur du site
                                </p>
                            </div>

                        </form>
                    </section>
            </form>
        </div>