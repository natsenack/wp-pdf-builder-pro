<?php
// Nettoyage du fichier settings-developpeur.php - Suppression des duplications HTML

// Copie des variables PHP
$license_test_mode = isset($_POST['pdf_builder_license_test_mode_enabled']) ? '1' : (get_option('pdf_builder_license_test_mode_enabled', '0'));
$license_test_key = get_option('pdf_builder_license_test_key', '');
$license_test_key_expires = get_option('pdf_builder_license_test_key_expires', '');

// Récupération des settings développeur
$settings = [
    'pdf_builder_developer_enabled' => get_option('pdf_builder_developer_enabled', '0'),
    'pdf_builder_developer_password' => get_option('pdf_builder_developer_password', ''),
    'pdf_builder_canvas_debug_enabled' => get_option('pdf_builder_canvas_debug_enabled', '0'),
    'pdf_builder_license_test_mode_enabled' => $license_test_mode,
    'pdf_builder_license_test_key' => $license_test_key,
    'pdf_builder_license_test_key_expires' => $license_test_key_expires
];

// Styles CSS (simplifiés - seules les parties nécessaires)
?>
<style>
.dev-tab-container { max-width: 1200px; margin: 0 auto; }
.dev-status-banner { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.dev-status-banner.active { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); }
.dev-status-banner.inactive { background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); }
.dev-quick-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.dev-section { background: white; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 25px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.dev-section.collapsed .dev-section-content { display: none; }
.dev-section-header { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #e0e0e0; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: background-color 0.2s; }
.dev-section-header:hover { background: #e9ecef; }
.dev-section-content { padding: 20px; }
.dev-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-bottom: 20px; }
.dev-card { background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px; padding: 15px; transition: all 0.2s; }
.dev-card:hover { border-color: #007cba; box-shadow: 0 2px 8px rgba(0,123,186,0.1); }
.dev-tools-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-top: 15px; }
.dev-tool-btn { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 12px 16px; text-align: center; cursor: pointer; transition: all 0.2s; font-size: 0.9em; font-weight: 500; }
.dev-tool-btn:hover { background: #007cba; color: white; border-color: #007cba; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,123,186,0.2); }
.dev-password-field { position: relative; max-width: 300px; }
.dev-password-toggle { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #6c757d; padding: 4px; border-radius: 3px; transition: color 0.2s; }
.dev-password-toggle:hover { color: #007cba; }
.dev-todo-list { min-height: 200px; }
.dev-todo-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: white; border: 1px solid #e0e0e0; border-radius: 6px; margin-bottom: 8px; transition: all 0.2s; }
.dev-todo-item:hover { border-color: #007cba; box-shadow: 0 2px 8px rgba(0,123,186,0.1); }
.dev-filter-btn { padding: 6px 12px; border: 1px solid #dee2e6; background: white; border-radius: 4px; cursor: pointer; transition: all 0.2s; }
.dev-filter-btn:hover { background: #f8f9fa; }
.dev-filter-btn.active { background: #007cba; color: white; border-color: #007cba; }
.dev-warning-box { background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-top: 30px; }
</style>

<div class="dev-tab-container">
    <!-- Status Banner -->
    <div class="dev-status-banner <?php echo ($settings['pdf_builder_developer_enabled'] === '1') ? 'active' : 'inactive'; ?>">
        <div style="font-size: 1.5em; margin-bottom: 5px;">🚀 Mode Développeur</div>
        <p style="margin: 0;"><?php echo ($settings['pdf_builder_developer_enabled'] === '1') ? 'Activé' : 'Désactivé'; ?></p>
        <div class="dev-quick-actions">
            <button type="button" class="button button-primary" id="dev-quick-enable" style="display: <?php echo ($settings['pdf_builder_developer_enabled'] === '1') ? 'none' : 'inline-block'; ?>;">⚡ Activer</button>
            <button type="button" class="button button-secondary" id="dev-export-settings">📤 Exporter Config</button>
            <button type="button" class="button button-secondary" id="dev-import-settings">📥 Importer Config</button>
        </div>
    </div>

    <form method="post" id="settings-developpeur-form">
        <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
        <input type="hidden" name="submit_developpeur" value="1">

        <!-- Section Licence de Test -->
        <div class="dev-section collapsed" id="license-section">
            <div class="dev-section-header" role="button" tabindex="0" aria-expanded="false">
                <h3>🔑 Licence de Test</h3>
                <span class="dev-section-toggle">▶️</span>
            </div>
            <div class="dev-section-content" aria-hidden="true">
                <table class="form-table">
                    <tr>
                        <th scope="row">Mode Test</th>
                        <td>
                            <label class="toggle-switch">
                                <input type="checkbox" id="license_test_mode" name="pdf_builder_license_test_mode_enabled" value="1" <?php checked($license_test_mode, '1'); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;"><?php echo $license_test_mode === '1' ? '✅ ACTIF' : '❌ INACTIF'; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Clé de Test</th>
                        <td>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <code style="background: #fff3cd; padding: 4px 8px; border-radius: 3px;">
                                    <?php echo $license_test_key ? substr($license_test_key, 0, 6) . '••••••••' : 'AUCUNE'; ?>
                                </code>
                                <button type="button" class="button button-small">📋 Copier</button>
                                <button type="button" class="button button-secondary">👁️ Afficher</button>
                            </div>
                            <input type="hidden" name="pdf_builder_license_test_key" value="<?php echo esc_attr($license_test_key); ?>" />
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Section Développement (toujours visible) -->
        <div class="dev-section" id="dev-section">
            <div class="dev-section-header">
                <h3>⚙️ Outils Développeur</h3>
            </div>
            <div class="dev-section-content">
                <div class="dev-grid">
                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">🎯</span>
                            <h4>Mode Développeur</h4>
                        </div>
                        <div style="margin-top: 15px;">
                            <label class="toggle-switch">
                                <input type="checkbox" id="developer_enabled" name="pdf_builder_developer_enabled" value="1"
                                       <?php checked($settings['pdf_builder_developer_enabled'], '1'); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">
                                <?php echo ($settings['pdf_builder_developer_enabled'] === '1') ? 'Activé' : 'Désactivé'; ?>
                            </span>
                        </div>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">📄</span>
                            <h4>Logs JavaScript</h4>
                        </div>
                        <div style="margin-top: 15px;">
                            <label class="toggle-switch">
                                <input type="checkbox" id="canvas_debug_enabled" name="pdf_builder_canvas_debug_enabled" value="1"
                                       <?php checked($settings['pdf_builder_canvas_debug_enabled'], '1'); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="margin-left: 10px;">
                                <?php echo ($settings['pdf_builder_canvas_debug_enabled'] === '1') ? 'Activé' : 'Désactivé'; ?>
                            </span>
                        </div>
                    </div>

                    <div class="dev-card">
                        <div class="dev-card-header">
                            <span class="dev-card-icon">🔑</span>
                            <h4>Sécurité d'Accès</h4>
                        </div>
                        <div class="dev-password-field">
                            <input type="password" id="developer_password" name="pdf_builder_developer_password"
                                   placeholder="Mot de passe (optionnel)" value="<?php echo esc_attr($settings['pdf_builder_developer_password']); ?>" />
                            <button type="button" id="toggle_password" class="dev-password-toggle">👁️</button>
                        </div>
                    </div>
                </div>

                <!-- Section Logs et Outils Développeur -->
                <div class="dev-section" id="logs-tools-section">
                    <div class="dev-section-header">
                        <h3>📋 Outils de Développement</h3>
                    </div>
                    <div class="dev-section-content">
                        <div class="dev-tools-grid">
                            <button type="button" id="view_logs_js_btn" class="dev-tool-btn">
                                📄<br/>Logs JS
                            </button>
                            <button type="button" id="clear_cache_btn" class="dev-tool-btn">
                                🔄<br/>Vider Cache
                            </button>
                            <button type="button" id="system_info_btn" class="dev-tool-btn">
                                ℹ️<br/>Info Système
                            </button>
                            <button type="button" id="backup_config_btn" class="dev-tool-btn">
                                💾<br/>Sauvegarde
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section TODO -->
        <div class="dev-section collapsed" id="todo-section">
            <div class="dev-section-header" role="button" tabindex="0">
                <h3>📝 À Faire</h3>
                <span class="dev-section-toggle">▶️</span>
            </div>
            <div class="dev-section-content" aria-hidden="true">
                <div style="margin-bottom: 20px;">
                    <input type="text" id="new-todo-input" placeholder="Nouvelle tâche..." style="flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; margin-right: 10px;" />
                    <select id="todo-priority" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="low">🟢 Faible</option>
                        <option value="medium" selected>🟡 Moyenne</option>
                        <option value="high">🔴 Haute</option>
                        <option value="urgent">🚨 Urgent</option>
                    </select>
                    <button type="button" id="add-todo-btn" class="button button-primary" style="margin-left: 10px;">➕ Ajouter</button>
                </div>
                <div id="todo-list" class="dev-todo-list">
                    <div class="dev-todo-empty" style="text-align: center; color: #6c757d; padding: 40px;">
                        <div style="font-size: 3em; margin-bottom: 10px;">📝</div>
                        <p>Aucune tâche pour le moment</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs JavaScript Modal -->
        <div id="js-logs-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:10000; align-items:center; justify-content:center;">
            <div style="background:white; border-radius:8px; width:90%; max-width:1000px; height:80%; max-height:800px; display:flex; flex-direction:column;">
                <div style="padding:15px 20px; border-bottom:1px solid #ddd; display:flex; align-items:center; justify-content:space-between;">
                    <h3 style="margin:0; color:#333; display:flex; align-items:center; gap:10px;">
                        <span style="font-size:1.5em;">📄</span> Logs JavaScript - Console Dynamique
                    </h3>
                    <div style="display:flex; gap:10px;">
                        <button type="button" id="refresh-logs-btn" class="button button-secondary">🔄 Actualiser</button>
                        <button type="button" id="export-logs-btn" class="button button-secondary">💾 Exporter</button>
                        <button type="button" id="clear-logs-btn" class="button button-secondary">🗑️ Vider</button>
                        <button type="button" id="close-logs-modal-btn" class="button">✕ Fermer</button>
                    </div>
                </div>
                <div id="logs-content" style="flex:1; overflow:auto; padding:20px; font-family:monospace; font-size:0.9em; background:#f8f9fa; line-height:1.4;">
                    <div style="color:#6c757d; text-align:center; padding:40px;">
                        🔄 Chargement des logs JavaScript...
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning Production -->
        <div class="dev-warning-box">
            <h3>🚨 Avertissement Production</h3>
            <ul>
                <li>Ne jamais activer le mode développeur en production</li>
                <li>Les logs peuvent contenir des informations sensibles</li>
                <li>Utiliser un mot de passe pour sécuriser l'accès</li>
            </ul>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[DEV TAB] Initialisation...');

    // === GESTION DU MODE DÉVELOPPEUR ===
    const developerToggle = document.getElementById('developer_enabled');
    const devSections = ['logs-tools-section'];

    function updateDeveloperMode() {
        if (!developerToggle) return;
        const isEnabled = developerToggle.checked;
        devSections.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            if (section) section.style.display = isEnabled ? 'block' : 'none';
        });
    }

    if (developerToggle) {
        developerToggle.addEventListener('change', updateDeveloperMode);
        updateDeveloperMode(); // Initial
    }

    // Mot de passe toggle
    const toggleBtn = document.getElementById('toggle_password');
    const passField = document.getElementById('developer_password');
    if (toggleBtn && passField) {
        toggleBtn.addEventListener('click', function() {
            passField.type = passField.type === 'password' ? 'text' : 'password';
            toggleBtn.textContent = passField.type === 'password' ? '👁️' : '🙈';
        });
    }

    // === GESTION DES LOGS JAVASCRIPT ===
    const viewLogsBtn = document.getElementById('view_logs_js_btn');
    const logsModal = document.getElementById('js-logs-modal');
    const closeLogsBtn = document.getElementById('close-logs-modal-btn');
    const refreshLogsBtn = document.getElementById('refresh-logs-btn');
    const exportLogsBtn = document.getElementById('export-logs-btn');
    const clearLogsBtn = document.getElementById('clear-logs-btn');
    const logsContent = document.getElementById('logs-content');

    if (viewLogsBtn) {
        viewLogsBtn.addEventListener('click', function() {
            if (logsModal) {
                logsModal.style.display = 'flex';
                loadLogs();
            }
        });
    }

    if (closeLogsBtn) {
        closeLogsBtn.addEventListener('click', function() {
            if (logsModal) logsModal.style.display = 'none';
        });
    }

    if (logsModal) {
        logsModal.addEventListener('click', function(e) {
            if (e.target === logsModal) logsModal.style.display = 'none';
        });
    }

    function loadLogs() {
        if (!logsContent) return;
        logsContent.innerHTML = '<div style="text-align:center; padding:40px; color:#6c757d;"><div style="font-size:2em;">🔄</div>Chargement...</div>';

        setTimeout(() => {
            const logs = collectLogs();
            displayLogs(logs);
        }, 500);
    }

    function collectLogs() {
        const logs = [];

        // Logs système
        if (typeof window.CanvasMonitoringDashboard !== 'undefined') {
            logs.push({
                type: 'info',
                timestamp: new Date().toISOString(),
                message: '=== RAPPORT MONITORING CANVAS ===',
                source: 'CanvasMonitoringDashboard'
            });
        }

        logs.push({
            type: 'info',
            timestamp: new Date().toISOString(),
            message: `Mode développeur: ${document.getElementById('developer_enabled')?.checked ? 'Activé' : 'Désactivé'}`,
            source: 'DeveloperMode'
        });

        // Logs d'exemple
        const exampleLogs = [
            { type: 'log', message: '[PDF Builder] Initialisation terminée', source: 'Init' },
            { type: 'warn', message: '[PDF Builder] Cache expiré', source: 'Cache' },
            { type: 'error', message: '[PDF Builder] Élément non trouvé', source: 'Canvas' }
        ];

        exampleLogs.forEach(log => {
            logs.push({
                type: log.type,
                timestamp: new Date(Date.now() - Math.random() * 3600000).toISOString(),
                message: log.message,
                source: log.source
            });
        });

        return logs;
    }

    function displayLogs(logs) {
        if (!logsContent) return;

        let html = '<div style="background:#1e1e1e; color:#d4d4d4; padding:10px; border-radius:4px; font-family:monospace; font-size:0.85em; line-height:1.4;">';

        logs.forEach(log => {
            const time = new Date(log.timestamp).toLocaleTimeString('fr-FR', {
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });

            const colors = { error: '#f44747', warn: '#cca700', warning: '#cca700', info: '#3794ff', log: '#d4d4d4' };
            const prefixes = { error: '🔴 ERROR', warn: '🟡 WARN', warning: '🟡 WARN', info: '🔵 INFO', log: '⚪ LOG' };

            const color = colors[log.type] || colors.log;
            const prefix = prefixes[log.type] || prefixes.log;

            html += `<div style="color:${color}"><span style="color:#6c757d;">[${time}]</span> <span style="font-weight:bold;">${prefix}</span> <span style="color:#c586c0;">[${log.source}]</span> ${log.message}</div>`;
        });

        html += '</div>';
        html += `<div style="margin-top:15px; padding:10px; background:#f8f9fa; border:1px solid #dee2e6; border-radius:4px;">
            <strong>Résumé:</strong> ${logs.length} logs (${logs.filter(l => l.type === 'error').length} erreurs, ${logs.filter(l => l.type === 'warn' || l.type === 'warning').length} warnings, ${logs.filter(l => l.type === 'info').length} infos)
        </div>`;

        logsContent.innerHTML = html;
    }

    if (refreshLogsBtn) refreshLogsBtn.addEventListener('click', loadLogs);

    if (exportLogsBtn) {
        exportLogsBtn.addEventListener('click', function() {
            const logs = collectLogs();
            const data = {
                timestamp: new Date().toISOString(),
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
        });
    }

    if (clearLogsBtn) {
        clearLogsBtn.addEventListener('click', function() {
            if (confirm('Vider tous les logs ?')) {
                logsContent.innerHTML = '<div style="text-align:center; padding:40px; color:#28a745;"><div style="font-size:3em;">🗑️</div><h3>Logs vidés</h3></div>';
            }
        });
    }

    // === GESTION TODO ===
    const newTodoInput = document.getElementById('new-todo-input');
    const todoPriority = document.getElementById('todo-priority');
    const addTodoBtn = document.getElementById('add-todo-btn');
    const todoList = document.getElementById('todo-list');

    let todos = JSON.parse(localStorage.getItem('pdfBuilderDevTodos') || '[]');

    function renderTodos() {
        if (!todoList) return;

        if (todos.length === 0) {
            todoList.innerHTML = '<div class="dev-todo-empty" style="text-align: center; color: #6c757d; padding: 40px;"><div style="font-size: 3em; margin-bottom: 10px;">📝</div><p>Aucune tâche</p></div>';
            return;
        }

        todoList.innerHTML = todos.map((todo, index) => `
            <div class="dev-todo-item">
                <input type="checkbox" class="dev-todo-checkbox" ${todo.completed ? 'checked' : ''} data-index="${index}" />
                <span class="dev-todo-priority ${todo.priority}">${getPriorityText(todo.priority)}</span>
                <span class="dev-todo-text">${todo.text}</span>
                <span class="dev-todo-date">${formatDate(todo.created)}</span>
                <button class="dev-todo-delete" data-index="${index}">×</button>
            </div>
        `).join('');
    }

    function addTodo() {
        const text = newTodoInput.value.trim();
        if (!text) return;

        todos.unshift({
            id: Date.now(),
            text: text,
            priority: todoPriority.value,
            completed: false,
            created: new Date().toISOString()
        });

        localStorage.setItem('pdfBuilderDevTodos', JSON.stringify(todos));
        renderTodos();
        newTodoInput.value = '';
    }

    if (addTodoBtn) addTodoBtn.addEventListener('click', addTodo);
    if (newTodoInput) {
        newTodoInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') addTodo();
        });
    }

    function getPriorityText(priority) {
        const texts = { low: 'Faible', medium: 'Moyenne', high: 'Haute', urgent: 'Urgent' };
        return texts[priority] || priority;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));

        if (days === 0) return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        if (days === 1) return 'Hier';
        if (days < 7) return `Il y a ${days} jours`;
        return date.toLocaleDateString('fr-FR');
    }

    renderTodos();

    console.log('[DEV TAB] Initialisation terminée');
});
</script>
