<?php
// Test file pour deployment propre
echo "<h1>Logs JavaScript Modal Test</h1>";

// Bouton de test
echo '<button type="button" id="view_logs_js_btn" class="button button-primary">📄 Logs JS</button>';

// Modal
echo '
<div id="js-logs-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:10000; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:8px; width:90%; max-width:1000px; height:80%; max-height:800px; display:flex; flex-direction:column; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
        <div style="padding:15px 20px; border-bottom:1px solid #ddd; display:flex; align-items:center; justify-content:space-between;">
            <h3 style="margin:0; color:#333; display:flex; align-items:center; gap:10px;">
                <span style="font-size:1.5em;">📄</span> Logs JavaScript - Console Dynamique
            </h3>
            <div style="display:flex; gap:10px;">
                <button type="button" id="refresh-logs-btn" class="button button-secondary" style="font-size:0.9em;">🔄 Actualiser</button>
                <button type="button" id="export-logs-btn" class="button button-secondary" style="font-size:0.9em;">💾 Exporter</button>
                <button type="button" id="clear-logs-btn" class="button button-secondary" style="font-size:0.9em;">🗑️ Vider</button>
                <button type="button" id="close-logs-modal-btn" class="button" style="font-size:0.9em;">✕ Fermer</button>
            </div>
        </div>
        <div id="logs-content" style="flex:1; overflow:auto; padding:20px; font-family:monospace; font-size:0.9em; background:#f8f9fa; line-height:1.4;">
            <div style="color:#6c757d; text-align:center; padding:40px;">
                🔄 Chargement des logs JavaScript...
            </div>
        </div>
    </div>
</div>';

?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Test modal initialized');

    // === GESTION DES LOGS JAVASCRIPT ===
    const viewLogsBtn = document.getElementById('view_logs_js_btn');
    const logsModal = document.getElementById('js-logs-modal');
    const closeLogsModalBtn = document.getElementById('close-logs-modal-btn');
    const refreshLogsBtn = document.getElementById('refresh-logs-btn');
    const exportLogsBtn = document.getElementById('export-logs-btn');
    const clearLogsBtn = document.getElementById('clear-logs-btn');
    const logsContent = document.getElementById('logs-content');

    console.log('Elements found:', { viewLogsBtn, logsModal, logsContent });

    // Ouvrir la modal des logs
    if (viewLogsBtn) {
        viewLogsBtn.addEventListener('click', function() {
            console.log('Opening logs modal');
            if (logsModal) {
                logsModal.style.display = 'flex';
                loadLogs();
            }
        });
    }

    // Fermer la modal
    if (closeLogsModalBtn) {
        closeLogsModalBtn.addEventListener('click', function() {
            if (logsModal) {
                logsModal.style.display = 'none';
            }
        });
    }

    // Fermer en cliquant sur le fond
    if (logsModal) {
        logsModal.addEventListener('click', function(e) {
            if (e.target === logsModal) {
                logsModal.style.display = 'none';
            }
        });
    }

    // Fonction pour charger les logs
    function loadLogs() {
        console.log('Loading logs...');
        if (!logsContent) return;

        logsContent.innerHTML = '<div style="color:#6c757d; text-align:center; padding:40px;"><div style="font-size:2em; margin-bottom:10px;">🔄</div>Chargement des logs JavaScript...</div>';

        setTimeout(() => {
            const logs = collectLogs();
            displayLogs(logs);
        }, 500);
    }

    // Collecter tous les logs disponibles
    function collectLogs() {
        console.log('Collecting logs...');
        const logs = [];

        try {
            // Logs du CanvasMonitoringDashboard (simulation)
            logs.push({
                type: 'info',
                timestamp: new Date().toISOString(),
                message: '=== RAPPORT MONITORING CANVAS ===',
                source: 'CanvasMonitoringDashboard'
            });

            logs.push({
                type: 'info',
                timestamp: new Date().toISOString(),
                message: 'Monitoring dashboard loaded successfully',
                source: 'CanvasMonitoringDashboard'
            });

            // Historique des changements d'éléments (simulation)
            logs.push({
                type: 'info',
                timestamp: new Date().toISOString(),
                message: '=== HISTORIQUE DES CHANGEMENTS ===',
                source: 'ElementChangeTracker'
            });

            // Logs de l'état actuel
            logs.push({
                type: 'info',
                timestamp: new Date().toISOString(),
                message: `Mode développeur: Activé`,
                source: 'DeveloperMode'
            });

            logs.push({
                type: 'info',
                timestamp: new Date().toISOString(),
                message: `Logs JS activés: Activé`,
                source: 'CanvasDebug'
            });

            // Logs depuis la console (simulation)
            logs.push({
                type: 'info',
                timestamp: new Date().toISOString(),
                message: '=== LOGS DE LA CONSOLE SIMULÉS ===',
                source: 'Console'
            });

            // Ajouter quelques exemples de logs
            const exampleLogs = [
                { type: 'log', message: '[PDF Builder] Initialisation terminée', source: 'Init' },
                { type: 'warn', message: '[PDF Builder] Avertissement: Cache expiré', source: 'Cache' },
                { type: 'error', message: '[PDF Builder] Erreur: Élément non trouvé #canvas-element', source: 'Canvas' },
                { type: 'log', message: '[PDF Builder] Sauvegarde automatique effectuée', source: 'AutoSave' },
                { type: 'info', message: '[PDF Builder] 3 éléments rendus, 2 modifiés', source: 'Render' }
            ];

            exampleLogs.forEach(log => {
                logs.push({
                    type: log.type,
                    timestamp: new Date(Date.now() - Math.random() * 3600000).toISOString(),
                    message: log.message,
                    source: log.source
                });
            });

        } catch (error) {
            logs.push({
                type: 'error',
                timestamp: new Date().toISOString(),
                message: `Erreur lors de la collecte des logs: ${error.message}`,
                source: 'ErrorHandler'
            });
        }

        return logs;
    }

    // Afficher les logs dans la modal
    function displayLogs(logs) {
        console.log('Displaying logs:', logs);
        if (!logsContent) return;

        if (logs.length === 0) {
            logsContent.innerHTML = '<div style="color:#6c757d; text-align:center; padding:40px;"><div style="font-size:3em; margin-bottom:10px;">📄</div><h3 style="margin:0 0 10px 0;">Aucun log disponible</h3><p style="margin:0;">Activez le mode développeur et utilisez l\'éditeur PDF pour générer des logs.</p></div>';
            return;
        }

        let html = '<div style="background:#1e1e1e; color:#d4d4d4; padding:10px; border-radius:4px; font-family:monospace; font-size:0.85em; line-height:1.4; white-space:pre-wrap; overflow-wrap:break-word;">';

        logs.forEach(log => {
            const time = new Date(log.timestamp).toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                fractionalSecondDigits: 3
            });

            let prefix = '';
            let style = '';

            switch (log.type) {
                case 'error':
                    prefix = '🔴 ERROR';
                    style = 'color:#f44747;';
                    break;
                case 'warn':
                case 'warning':
                    prefix = '🟡 WARN';
                    style = 'color:#cca700;';
                    break;
                case 'info':
                    prefix = '🔵 INFO';
                    style = 'color:#3794ff;';
                    break;
                case 'log':
                default:
                    prefix = '⚪ LOG';
                    style = 'color:#d4d4d4;';
                    break;
            }

            html += `<div style="${style}"><span style="color:#6c757d;">[${time}]</span> <span style="font-weight:bold;">${prefix}</span> <span style="color:#c586c0;">[${log.source}]</span> ${log.message}</div>`;
        });

        html += '</div>';

        // Ajouter un résumé
        html += `<div style="margin-top:15px; padding:10px; background:#f8f9fa; border:1px solid #dee2e6; border-radius:4px; font-size:0.9em;">
            <strong>Résumé:</strong> ${logs.length} logs collectés
            (${logs.filter(l => l.type === 'error').length} erreurs,
            ${logs.filter(l => l.type === 'warn' || l.type === 'warning').length} avertissements,
            ${logs.filter(l => l.type === 'info').length} infos,
            ${logs.filter(l => l.type === 'log').length} logs)
            <br><small style="color:#6c757d;">Dernière mise à jour: ${new Date().toLocaleString('fr-FR')}</small>
        </div>`;

        logsContent.innerHTML = html;

        // Défiler vers le bas
        setTimeout(() => {
            logsContent.scrollTop = logsContent.scrollHeight;
        }, 100);
    }

    // Actualiser les logs
    if (refreshLogsBtn) {
        refreshLogsBtn.addEventListener('click', loadLogs);
    }

    // Exporter les logs
    if (exportLogsBtn) {
        exportLogsBtn.addEventListener('click', function() {
            const logs = collectLogs();

            const data = {
                timestamp: new Date().toISOString(),
                summary: {
                    total: logs.length,
                    errors: logs.filter(l => l.type === 'error').length,
                    warnings: logs.filter(l => l.type === 'warn' || l.type === 'warning').length,
                    infos: logs.filter(l => l.type === 'info').length,
                    logs: logs.filter(l => l.type === 'log').length
                },
                logs: logs
            };

            const dataStr = JSON.stringify(data, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);

            const link = document.createElement('a');
            link.href = url;
            link.download = `pdf-builder-js-logs-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);

            console.log('Logs exported');
        });
    }

    // Vider les logs
    if (clearLogsBtn) {
        clearLogsBtn.addEventListener('click', function() {
            if (confirm('Vider tous les logs ? Cette action est irréversible.')) {
                if (logsContent) {
                    logsContent.innerHTML = '<div style="color:#28a745; text-align:center; padding:40px;"><div style="font-size:3em; margin-bottom:10px;">🗑️</div><h3 style="margin:0;">Logs vidés avec succès</h3><p style="margin:10px 0 0 0;">Les logs ont été supprimés. Rechargez la page pour en générer de nouveaux.</p></div>';
                }
                console.log('Logs cleared');
            }
        });
    }

    console.log('Logs modal handlers initialized');
});
</script>
