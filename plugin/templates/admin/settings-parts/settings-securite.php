<?php // Securite tab content - Updated: 2025-11-18 20:20:00

if (!defined('ABSPATH')) {
    exit;
}

    // Préparer les variables nécessaires
    $settings = pdf_builder_get_option('pdf_builder_settings', array());
    error_log('[PDF Builder] settings-securite.php loaded - security_level: ' . ($settings['pdf_builder_security_level'] ?? 'not set') . ', enable_logging: ' . ($settings['pdf_builder_enable_logging'] ?? 'not set'));
    $security_level = $settings['pdf_builder_security_level'] ?? 'medium';
    $enable_logging = $settings['pdf_builder_enable_logging'] ?? '1';
    $gdpr_enabled = $settings['pdf_builder_gdpr_enabled'] ?? '1';
    $gdpr_consent_required = $settings['pdf_builder_gdpr_consent_required'] ?? '1';
    $gdpr_data_retention = $settings['pdf_builder_gdpr_data_retention'] ?? 2555;
    $gdpr_audit_enabled = $settings['pdf_builder_gdpr_audit_enabled'] ?? '1';
    $gdpr_encryption_enabled = $settings['pdf_builder_gdpr_encryption_enabled'] ?? '1';
    $gdpr_consent_analytics = $settings['pdf_builder_gdpr_consent_analytics'] ?? '1';
    $gdpr_consent_templates = $settings['pdf_builder_gdpr_consent_templates'] ?? '1';
    $gdpr_consent_marketing = $settings['pdf_builder_gdpr_consent_marketing'] ?? '0';
?>
            <h3 style="display: flex; justify-content: flex-start; align-items: center;">
                <span>🔒 Sécurité & Conformité</span>
            </h3>

            <!-- Formulaire pour les paramètres de sécurité -->
            <!-- <form id="securite-settings-form" method="post" action="">
                <?php wp_nonce_field('pdf_builder_save_settings', 'pdf_builder_securite_nonce'); ?>
                <input type="hidden" name="current_tab" value="securite"> -->

                <!-- Section Sécurité -->
                <div id="securite" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border: 2px solid #e9ecef; border-radius: 12px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                    <h3 style="color: #495057; margin-top: 0; border-bottom: 2px solid #e9ecef; padding-bottom: 10px;">
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            🛡️ Sécurité
                            <span id="security-status-indicator" class="security-status" style="font-size: 12px; background: <?php echo $enable_logging ? '#28a745' : '#dc3545'; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;"><?php echo $enable_logging ? 'ACTIF' : 'INACTIF'; ?></span>
                        </span>
                    </h3>

                    <!-- Section Paramètres de sécurité -->
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="security_level">Niveau de sécurité</label></th>
                            <td>
                                <select id="security_level" name="pdf_builder_settings[pdf_builder_security_level]">
                                    <option value="low" <?php selected($security_level, 'low'); ?>>Faible</option>
                                    <option value="medium" <?php selected($security_level, 'medium'); ?>>Moyen</option>
                                    <option value="high" <?php selected($security_level, 'high'); ?>>Élevé</option>
                                </select>
                                <p class="description">Niveau de sécurité pour la génération de PDF</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="enable_logging">Journalisation activée</label></th>
                            <td>
                                <label class="toggle-switch">
                                    <input type="hidden" name="pdf_builder_settings[pdf_builder_enable_logging]" value="0">
                                    <input type="checkbox" id="enable_logging" name="pdf_builder_settings[pdf_builder_enable_logging]" value="1" <?php checked($enable_logging, '1'); ?>>
                                    <span class="toggle-slider"></span>
                                </label>
                                <p class="description">Active la journalisation des actions pour audit</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Section RGPD -->
                <div id="securite" style="background: linear-gradient(135deg, #d4edda 0%, #e8f5e8 100%); border: 2px solid #28a745; border-radius: 12px; padding: 30px; margin-bottom: 30px;">
                    <h3 style="color: #155724; margin-top: 0; border-bottom: 2px solid #28a745; padding-bottom: 10px;">
                        <span style="display: inline-flex; align-items: center; gap: 10px;">
                            📋 Gestion RGPD & Conformité
                            <span id="rgpd-status-indicator" class="rgpd-status" style="font-size: 12px; background: <?php echo $gdpr_enabled ? '#28a745' : '#dc3545'; ?>; color: white; padding: 2px 8px; border-radius: 10px; font-weight: normal;"><?php echo $gdpr_enabled ? 'ACTIF' : 'INACTIF'; ?></span>
                        </span>
                    </h3>

                    <!-- Section Paramètres RGPD -->
                    <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">⚙️ Paramètres RGPD</h4>
                    <table class="form-table">
                            <tr>
                                <th scope="row"><label for="gdpr_enabled">RGPD Activé</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_gdpr_enabled]" value="0">
                                        <input type="checkbox" id="gdpr_enabled" name="pdf_builder_settings[pdf_builder_gdpr_enabled]" value="1" <?php checked($gdpr_enabled, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Activer la conformité RGPD pour le plugin</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gdpr_consent_required">Consentement RGPD requis</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_gdpr_consent_required]" value="0">
                                        <input type="checkbox" id="gdpr_consent_required" name="pdf_builder_settings[pdf_builder_gdpr_consent_required]" value="1" <?php checked($gdpr_consent_required, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Exiger le consentement RGPD avant génération de PDF</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gdpr_data_retention">Rétention des données (jours)</label></th>
                                <td>
                                    <input type="number" id="gdpr_data_retention" name="pdf_builder_settings[pdf_builder_gdpr_data_retention]" value="<?php echo esc_attr($gdpr_data_retention); ?>" min="30" max="3650">
                                    <p class="description">Nombre de jours avant suppression automatique des données utilisateur (RGPD: 7 ans recommandé)</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gdpr_audit_enabled">Audit Logging</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_gdpr_audit_enabled]" value="0">
                                        <input type="checkbox" id="gdpr_audit_enabled" name="pdf_builder_settings[pdf_builder_gdpr_audit_enabled]" value="1" <?php checked($gdpr_audit_enabled, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Activer la journalisation des actions pour audit RGPD</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gdpr_encryption_enabled">Chiffrement des données</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_gdpr_encryption_enabled]" value="0">
                                        <input type="checkbox" id="gdpr_encryption_enabled" name="pdf_builder_settings[pdf_builder_gdpr_encryption_enabled]" value="1" <?php checked($gdpr_encryption_enabled, '1'); ?>>
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
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_gdpr_consent_analytics]" value="0">
                                        <input type="checkbox" id="gdpr_consent_analytics" name="pdf_builder_settings[pdf_builder_gdpr_consent_analytics]" value="1" <?php checked($gdpr_consent_analytics, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Collecte de données d'utilisation anonymes pour améliorer le service</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gdpr_consent_templates">Consentement Templates</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_gdpr_consent_templates]" value="0">
                                        <input type="checkbox" id="gdpr_consent_templates" name="pdf_builder_settings[pdf_builder_gdpr_consent_templates]" value="1" <?php checked($gdpr_consent_templates, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Sauvegarde des templates personnalisés sur le serveur</p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="gdpr_consent_marketing">Consentement Marketing</label></th>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="hidden" name="pdf_builder_settings[pdf_builder_gdpr_consent_marketing]" value="0">
                                        <input type="checkbox" id="gdpr_consent_marketing" name="pdf_builder_settings[pdf_builder_gdpr_consent_marketing]" value="1" <?php checked($gdpr_consent_marketing, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <p class="description">Réception d'informations sur les nouvelles fonctionnalités et mises à jour</p>
                                </td>
                            </tr>
                        </table>

                        <!-- Section Actions Utilisateur -->
                        <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">👤 Actions RGPD Utilisateur</h4>
                        <div id="securite" class="gdpr-section" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                            <p style="margin-top: 0; color: #495057;"><strong>Droits RGPD :</strong> En tant qu'administrateur, vous pouvez gérer vos propres données personnelles.</p>

                            <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <select id="export-format" style="min-width: 130px;">
                                        <option value="html">📄 HTML (Lisible)</option>
                                        <option value="json">📋 JSON (Brut)</option>
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
                            <input type="hidden" id="export_user_data_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_gdpr')); ?>" />
                            <input type="hidden" id="delete_user_data_nonce" value="<?php echo esc_attr(wp_create_nonce('pdf_builder_gdpr')); ?>" />
                            <input type="hidden" id="audit_log_nonce"        value="<?php echo esc_attr(wp_create_nonce('pdf_builder_gdpr')); ?>" />
                        </div>

                        <!-- Section Logs d'Audit -->
                        <h4 style="color: #155724; margin-top: 30px; margin-bottom: 15px;">📊 Logs d'Audit RGPD</h4>
                        <div id="securite" class="gdpr-section" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
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
            </form>

            <!-- JavaScript déplacé vers settings-main.php pour éviter les conflits -->

<script type="text/javascript">
(function($) {
    'use strict';

    // ── Indicateurs de statut ─────────────────────────────────────────────────
    function updateSecurityStatusIndicators() {
        var loggingEnabled = document.getElementById('enable_logging').checked;
        var securityEl     = document.getElementById('security-status-indicator');
        if (securityEl) {
            securityEl.textContent        = loggingEnabled ? 'ACTIF' : 'INACTIF';
            securityEl.style.backgroundColor = loggingEnabled ? '#28a745' : '#dc3545';
        }
        var gdprEnabled = document.getElementById('gdpr_enabled').checked;
        var rgpdEl      = document.getElementById('rgpd-status-indicator');
        if (rgpdEl) {
            rgpdEl.textContent        = gdprEnabled ? 'ACTIF' : 'INACTIF';
            rgpdEl.style.backgroundColor = gdprEnabled ? '#28a745' : '#dc3545';
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function gdprNonce() {
        return document.getElementById('export_user_data_nonce')?.value || '';
    }

    function showResult(html, isError) {
        var $el = $('#gdpr-user-actions-result');
        var bg  = isError ? '#f8d7da' : '#d4edda';
        var col = isError ? '#721c24' : '#155724';
        $el.html('<div style="padding:12px;background:' + bg + ';color:' + col + ';border-radius:6px;border:1px solid ' + (isError ? '#f5c6cb' : '#c3e6cb') + '">' + html + '</div>').show();
    }

    function setLoading($btn, loading) {
        if (loading) {
            $btn.prop('disabled', true).data('orig', $btn.html()).html('⏳ Chargement…');
        } else {
            $btn.prop('disabled', false).html($btn.data('orig'));
        }
    }

    function ajaxGdpr(action, extra, onSuccess, onError) {
        $.post(ajaxurl, $.extend({ action: action, nonce: gdprNonce() }, extra), function(res) {
            if (res.success) { onSuccess(res.data); }
            else             { onError(res.data?.message || 'Erreur'); }
        }).fail(function() { onError('Erreur de connexion'); });
    }

    // ── 📥 Exporter mes données ───────────────────────────────────────────────
    $('#export-my-data').on('click', function() {
        var $btn = $(this);
        var fmt  = $('#export-format').val() || 'html';
        setLoading($btn, true);

        ajaxGdpr('pdf_builder_export_gdpr_data', { format: fmt }, function(data) {
            setLoading($btn, false);

            if (fmt === 'json') {
                var blob = new Blob([JSON.stringify(data.content, null, 2)], { type: 'application/json' });
                var url  = URL.createObjectURL(blob);
                var a    = document.createElement('a');
                a.href = url; a.download = 'mes-donnees-rgpd.json'; a.click();
                URL.revokeObjectURL(url);
                showResult('✅ Export JSON téléchargé.', false);
                return;
            }

            // Format HTML → nouvel onglet avec page complète + bouton télécharger
            var htmlContent = data.content;
            var filename    = 'mes-donnees-rgpd-' + new Date().toISOString().slice(0, 10) + '.html';

            var fullPage = '<!DOCTYPE html><html lang="fr"><head>'
                + '<meta charset="UTF-8">'
                + '<meta name="viewport" content="width=device-width,initial-scale=1">'
                + '<title>Mes données personnelles — PDF Builder Pro</title>'
                + '<style>'
                + 'body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f4f6f9;color:#333}'
                + '.header{background:#155724;color:#fff;padding:24px 40px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,.2)}'
                + '.header h1{margin:0;font-size:22px;font-weight:600;display:flex;align-items:center;gap:10px}'
                + '.header small{opacity:.8;font-size:13px;margin-top:4px;display:block}'
                + '.dl-btn{background:#fff;color:#155724;border:none;padding:10px 22px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;text-decoration:none;transition:background .2s}'
                + '.dl-btn:hover{background:#e8f5e8}'
                + '.content{max-width:860px;margin:40px auto;background:#fff;border-radius:12px;box-shadow:0 4px 16px rgba(0,0,0,.08);padding:36px;}'
                + '.badge{display:inline-block;background:#d4edda;color:#155724;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;margin-bottom:18px}'
                + '.footer{text-align:center;color:#999;font-size:12px;margin:32px 0 20px}'
                + '</style>'
                + '</head><body>'
                + '<div class="header">'
                + '  <div><h1>📋 Mes données personnelles<br><small>PDF Builder Pro — Export RGPD</small></h1></div>'
                + '  <a class="dl-btn" id="dlBtn" href="#" download="' + filename + '">📥 Télécharger cette page</a>'
                + '</div>'
                + '<div class="content">'
                + '  <span class="badge">✅ Généré le ' + new Date().toLocaleString('fr-FR') + '</span>'
                + htmlContent
                + '</div>'
                + '<div class="footer">Document généré par PDF Builder Pro · Conforme RGPD</div>'
                + '<script>'
                + 'document.getElementById("dlBtn").addEventListener("click",function(e){'
                + '  e.preventDefault();'
                + '  var src=document.documentElement.outerHTML;'
                + '  var blob=new Blob([src],{type:"text/html;charset=utf-8"});'
                + '  var url=URL.createObjectURL(blob);'
                + '  var a=document.createElement("a");a.href=url;a.download="' + filename + '";a.click();'
                + '  setTimeout(function(){URL.revokeObjectURL(url);},2000);'
                + '});'
                + '<\/script>'
                + '</body></html>';

            var tab = window.open('', '_blank');
            if (tab) {
                tab.document.open();
                tab.document.write(fullPage);
                tab.document.close();
            } else {
                showResult('⚠️ Le navigateur a bloqué l\'ouverture de l\'onglet. Veuillez autoriser les pop-ups.', true);
            }
        }, function(msg) {
            setLoading($btn, false);
            showResult('❌ ' + msg, true);
        });
    });

    // ── 🗑️ Supprimer mes données ─────────────────────────────────────────────
    $('#delete-my-data').on('click', function() {
        if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer vos données personnelles stockées par le plugin ?')) return;
        var $btn = $(this);
        setLoading($btn, true);

        ajaxGdpr('pdf_builder_delete_gdpr_data', {}, function(data) {
            setLoading($btn, false);
            showResult('✅ ' + data.message, false);
        }, function(msg) {
            setLoading($btn, false);
            showResult('❌ ' + msg, true);
        });
    });

    // ── 👁️ Voir mes consentements ────────────────────────────────────────────
    $('#view-consent-status').on('click', function() {
        var $btn = $(this);
        setLoading($btn, true);

        ajaxGdpr('pdf_builder_get_consent_status', {}, function(data) {
            setLoading($btn, false);
            var rows = data.consents.map(function(c) {
                var icon = (c.value === true || c.value === 1) ? '✅' : (c.value === false ? '❌' : '');
                var val  = (typeof c.value === 'boolean') ? (c.value ? 'Oui' : 'Non') : c.value;
                return '<tr><td style="padding:4px 10px;font-weight:600">' + c.label + '</td><td style="padding:4px 10px">' + icon + ' ' + val + '</td></tr>';
            }).join('');
            showResult('<strong>👁️ État des consentements RGPD</strong><br><table style="margin-top:8px;width:100%">' + rows + '</table>', false);
        }, function(msg) {
            setLoading($btn, false);
            showResult('❌ ' + msg, true);
        });
    });

    // ── 🔄 Actualiser les logs ───────────────────────────────────────────────
    $('#refresh-audit-log').on('click', function() {
        var $btn = $(this);
        setLoading($btn, true);

        ajaxGdpr('pdf_builder_get_audit_log', { limit: 50 }, function(data) {
            setLoading($btn, false);
            var $container = $('#audit-log-container');
            var $content   = $('#audit-log-content');
            $container.show();

            if (!data.logs || data.logs.length === 0) {
                $content.html('<p style="color:#6c757d;text-align:center;margin:10px 0">Aucune entrée de log disponible.</p>');
                return;
            }

            var rows = data.logs.map(function(e) {
                return '<tr style="border-bottom:1px solid #f0f0f0">'
                    + '<td style="padding:4px 8px;font-size:11px;color:#666;white-space:nowrap">' + (e.date || '') + '</td>'
                    + '<td style="padding:4px 8px;font-weight:600">' + (e.user || '') + '</td>'
                    + '<td style="padding:4px 8px"><code style="background:#e9ecef;padding:2px 6px;border-radius:3px;font-size:11px">' + (e.action || '') + '</code></td>'
                    + '<td style="padding:4px 8px;font-size:12px;color:#495057">' + (e.details || '') + '</td>'
                    + '</tr>';
            }).join('');

            $content.html(
                '<table style="width:100%;border-collapse:collapse">'
                + '<thead><tr style="background:#f8f9fa">'
                + '<th style="padding:6px 8px;text-align:left;font-size:12px">Date</th>'
                + '<th style="padding:6px 8px;text-align:left;font-size:12px">Utilisateur</th>'
                + '<th style="padding:6px 8px;text-align:left;font-size:12px">Action</th>'
                + '<th style="padding:6px 8px;text-align:left;font-size:12px">Détails</th>'
                + '</tr></thead><tbody>' + rows + '</tbody></table>'
            );
        }, function(msg) {
            setLoading($btn, false);
            $('#audit-log-container').show();
            $('#audit-log-content').html('<p style="color:#dc3545">❌ ' + msg + '</p>');
        });
    });

    // ── 📤 Exporter les logs ─────────────────────────────────────────────────
    $('#export-audit-log').on('click', function() {
        var $btn = $(this);
        setLoading($btn, true);

        ajaxGdpr('pdf_builder_export_audit_log', {}, function(data) {
            setLoading($btn, false);
            if (!data.count) {
                showResult('ℹ️ Aucun log à exporter.', false);
                return;
            }
            var blob = new Blob([data.csv], { type: 'text/csv;charset=utf-8;' });
            var url  = URL.createObjectURL(blob);
            var a    = document.createElement('a');
            a.href = url; a.download = data.filename; a.click();
            URL.revokeObjectURL(url);
            showResult('✅ ' + data.count + ' entrée(s) exportée(s) dans <strong>' + data.filename + '</strong>', false);
        }, function(msg) {
            setLoading($btn, false);
            showResult('❌ ' + msg, true);
        });
    });

    // ── Init ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        var loggingCb = document.getElementById('enable_logging');
        var gdprCb    = document.getElementById('gdpr_enabled');
        if (loggingCb) loggingCb.addEventListener('change', updateSecurityStatusIndicators);
        if (gdprCb)    gdprCb.addEventListener('change',    updateSecurityStatusIndicators);
        updateSecurityStatusIndicators();
    });

})(jQuery);
</script>







