<?php // Securite tab content - Updated: 2025-11-18 20:20:00 ?>
            <h2>🔒 Sécurité & Conformité</h2>

            <!-- Section Sécurité -->
            <div style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                    <span style="display: inline-flex; align-items: center; gap: 10px;">
                        🛡️ Sécurité
                        <span class="security-status" style="font-size: 12px; background: <?php echo get_option('pdf_builder_enable_logging', true) ? '#28a745' : '#dc3545'; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;"><?php echo get_option('pdf_builder_enable_logging', true) ? 'ACTIF' : 'INACTIF'; ?></span>
                    </span>
                </h3>

                <!-- Section Paramètres de sécurité -->
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="security_level">Niveau de sécurité</label></th>
                        <td>
                            <select id="security_level" name="security_level">
                                <option value="low" <?php selected(get_option('pdf_builder_security_level', 'medium'), 'low'); ?>>Faible</option>
                                <option value="medium" <?php selected(get_option('pdf_builder_security_level', 'medium'), 'medium'); ?>>Moyen</option>
                                <option value="high" <?php selected(get_option('pdf_builder_security_level', 'medium'), 'high'); ?>>Élevé</option>
                            </select>
                            <p class="description">Niveau de sécurité pour la génération de PDF</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="enable_logging">Journalisation activée</label></th>
                        <td>
                            <label class="toggle-switch">
                                <input type="checkbox" id="enable_logging" name="enable_logging" value="1" <?php checked(get_option('pdf_builder_enable_logging', true)); ?>>
                                <span class="toggle-slider"></span>
                            </label>
                            <p class="description">Active la journalisation des actions pour audit</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Section RGPD -->
            <div style="background: linear-gradient(135deg, #d4edda 0%, #e8f5e8 100%); border: 2px solid #28a745; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                <h3 style="color: #155724; margin-top: 0; border-bottom: 2px solid #28a745; padding-bottom: 10px;">
                    <span style="display: inline-flex; align-items: center; gap: 10px;">
                        📋 Gestion RGPD & Conformité
                        <span class="rgpd-status" style="font-size: 12px; background: <?php echo get_option('pdf_builder_gdpr_enabled', true) ? '#28a745' : '#dc3545'; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;"><?php echo get_option('pdf_builder_gdpr_enabled', true) ? 'ACTIF' : 'INACTIF'; ?></span>
                    </span>
                </h3>

                <!-- Section Paramètres RGPD -->
                <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">⚙️ Paramètres RGPD</h4>
                <table class="form-table">
                        <tr>
                            <th scope="row"><label for="gdpr_enabled">RGPD Activé</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="gdpr_enabled" name="gdpr_enabled" value="1" <?php checked(get_option('pdf_builder_gdpr_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Activer la conformité RGPD pour le plugin</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_consent_required">Consentement RGPD requis</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="gdpr_consent_required" name="gdpr_consent_required" value="1" <?php checked(get_option('pdf_builder_gdpr_consent_required', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Exiger le consentement RGPD avant génération de PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_data_retention">Rétention des données (jours)</label></th>
                            <td>
                                <input type="number" id="gdpr_data_retention" name="gdpr_data_retention" value="<?php
                                    $current_value = get_option('pdf_builder_gdpr_data_retention');
                                    if ($current_value === false || $current_value === '') {
                                        $current_value = 2555; // Valeur par défaut si l'option n'existe pas
                                        update_option('pdf_builder_gdpr_data_retention', $current_value);
                                    }
                                    echo esc_attr($current_value);
                                ?>" min="30" max="3650">
                                <p class="description">Nombre de jours avant suppression automatique des données utilisateur (RGPD: 7 ans recommandé)</p>
                                <?php
                                echo "<!-- DEBUG: Current gdpr_data_retention value: $current_value -->";
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_audit_enabled">Audit Logging</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="gdpr_audit_enabled" name="gdpr_audit_enabled" value="1" <?php checked(get_option('pdf_builder_gdpr_audit_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Activer la journalisation des actions pour audit RGPD</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_encryption_enabled">Chiffrement des données</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="gdpr_encryption_enabled" name="gdpr_encryption_enabled" value="1" <?php checked(get_option('pdf_builder_gdpr_encryption_enabled', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Chiffrer les données sensibles des utilisateurs</p>
                            </td>
                        </tr>
                    </table>

                    <!-- Section Types de Consentement -->
                    <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">🤝 Types de Consentement</h4>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="gdpr_consent_analytics">Consentement Analytics</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="gdpr_consent_analytics" name="gdpr_consent_analytics" value="1" <?php checked(get_option('pdf_builder_gdpr_consent_analytics', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Collecte de données d'utilisation anonymes pour améliorer le service</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_consent_templates">Consentement Templates</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="gdpr_consent_templates" name="gdpr_consent_templates" value="1" <?php checked(get_option('pdf_builder_gdpr_consent_templates', true)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Sauvegarde des templates personnalisés sur le serveur</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="gdpr_consent_marketing">Consentement Marketing</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="gdpr_consent_marketing" name="gdpr_consent_marketing" value="1" <?php checked(get_option('pdf_builder_gdpr_consent_marketing', false)); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Réception d'informations sur les nouvelles fonctionnalités et mises à jour</p>
                            </td>
                        </tr>
                    </table>

                    <!-- Section Actions Utilisateur -->
                    <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">👤 Actions RGPD Utilisateur</h4>
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <p style="margin-top: 0; color: #495057;"><strong>Droits RGPD :</strong> En tant qu'administrateur, vous pouvez gérer vos propres données personnelles.</p>

                        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <select id="export-format" style="min-width: 100px;">
                                    <option value="json">JSON</option>
                                    <option value="csv">CSV</option>
                                    <option value="xml">XML</option>
                                    <option value="txt">Texte</option>
                                </select>
                                <button type="button" id="export-my-data" class="button button-secondary" style="display: flex; align-items: center; gap: 8px;">
                                    📥 Exporter mes données
                                </button>
                            </div>
                            <button type="button" id="delete-my-data" class="button button-danger" style="display: flex; align-items: center; gap: 8px; background: #dc3545; color: white; border-color: #dc3545;">
                                🗑️ Supprimer mes données
                            </button>
                            <button type="button" id="view-consent-status" class="button button-info" style="display: flex; align-items: center; gap: 8px; background: #17a2b8; color: white; border-color: #17a2b8;">
                                👁️ Voir mes consentements
                            </button>
                        </div>

                        <div id="gdpr-user-actions-result" style="margin-top: 15px; display: none;"></div>
                        <input type="hidden" id="export_user_data_nonce" value="<?php echo wp_create_nonce('pdf_builder_gdpr'); ?>" />
                        <input type="hidden" id="delete_user_data_nonce" value="<?php echo wp_create_nonce('pdf_builder_gdpr'); ?>" />
                    </div>

                    <!-- Section Logs d'Audit -->
                    <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">📊 Logs d'Audit RGPD</h4>
                    <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                        <p style="margin-top: 0; color: #495057;">Consultez et exportez les logs d'audit RGPD pour vérifier la conformité.</p>

                        <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
                            <button type="button" id="refresh-audit-log" class="button button-secondary" style="display: flex; align-items: center; gap: 8px;">
                                🔄 Actualiser les logs
                            </button>
                            <button type="button" id="export-audit-log" class="button button-primary" style="display: flex; align-items: center; gap: 8px;">
                                📤 Exporter les logs
                            </button>
                        </div>

                        <div id="audit-log-container" style="margin-top: 20px; max-height: 300px; overflow-y: auto; background: white; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; display: none;">
                            <div id="audit-log-content"></div>
                        </div>
                    </div>
                </div>