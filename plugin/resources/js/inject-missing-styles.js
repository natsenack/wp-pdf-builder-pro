/**
 * Inject Missing Styles - Injecte les styles CSS manquants directement dans le DOM
 * Les CSS-in-JS du TemplateHeader, Toolbar et PropertiesPanel ne sont pas compilés correctement par webpack
 * Cette solution injecte les styles directement pour garantir l'affichage
 */

function injectMissingStyles() {
  // Vérifier si les styles existent déjà
  if (document.getElementById('pdf-builder-injected-styles')) {
    return; // Déjà injecté
  }

  const styles = `
/* ===== TEMPLATE HEADER STYLES ===== */
.template-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  border-bottom: 1px solid rgba(255,255,255,0.1);
  z-index: 1000;
  position: relative;
  min-height: 60px;
}

.header-left, .header-center, .header-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-center {
  flex: 1;
  text-align: center;
}

.header-right {
  gap: 8px;
}

.header-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  background: rgba(255,255,255,0.1);
  color: white;
}

.header-btn:hover {
  background: rgba(255,255,255,0.2);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.header-btn:active {
  transform: translateY(0);
}

.new-template-btn {
  background: rgba(52, 152, 219, 0.8);
  border-color: rgba(52, 152, 219, 1);
}

.new-template-btn:hover {
  background: rgba(52, 152, 219, 1);
}

.save-btn {
  background: rgba(46, 204, 113, 0.8);
  border-color: rgba(46, 204, 113, 1);
}

.save-btn:hover {
  background: rgba(46, 204, 113, 1);
}

.preview-btn {
  background: rgba(155, 89, 182, 0.8);
  border-color: rgba(155, 89, 182, 1);
}

.preview-btn:hover {
  background: rgba(155, 89, 182, 1);
}

.template-title {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: white;
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

/* ===== PDF BUILDER TOOLBAR ===== */
.pdf-builder-toolbar,
.ribbon-toolbar {
  display: flex;
  flex-direction: column;
  gap: 0;
  padding: 0;
  width: 100%;
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  z-index: 100;
}

/* Onglets du toolbar */
.toolbar-tabs {
  display: flex;
  gap: 0;
  background: #fafbfc;
  border-bottom: 1px solid #e5e7eb;
  padding: 0;
}

.tab-button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  transition: all 0.2s ease;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
}

.tab-button:hover {
  background: #f3f4f6;
  color: #374151;
}

.tab-button.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
  background: white;
}

.tab-icon {
  font-size: 16px;
}

.tab-label {
  font-weight: 600;
}

/* Contenu du toolbar */
.toolbar-content {
  display: flex;
  gap: 0;
  padding: 8px 8px;
  background: white;
  overflow-x: auto;
  overflow-y: hidden;
  width: 100%;
  height: auto;
}

.tab-content {
  display: flex;
  gap: 0;
  width: 100%;
  flex-wrap: nowrap;
  align-items: flex-start;
}

/* Groupes d'outils */
.toolbar-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 0 12px;
  border-right: 1px solid #e5e7eb;
  flex-shrink: 0;
}

.toolbar-group:last-child {
  border-right: none;
}

.toolbar-group h5 {
  margin: 0;
  font-size: 11px;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  text-align: center;
  min-width: 60px;
}

.group-buttons {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}

.group-buttons.shapes-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
}

/* Boutons d'édition */
.edit-button,
.tool-button,
.zoom-button {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px 8px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 3px;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  transition: all 0.15s ease;
  min-width: 32px;
  min-height: 28px;
}

.edit-button:hover:not(:disabled),
.tool-button:hover,
.zoom-button:hover {
  background: #f3f4f6;
  border-color: #9ca3af;
  color: #1f2937;
}

.edit-button:active:not(:disabled),
.tool-button:active,
.zoom-button:active {
  background: #e5e7eb;
  border-color: #6b7280;
}

.edit-button.active,
.tool-button.active,
.zoom-button.active {
  background: #2563eb;
  color: white;
  border-color: #2563eb;
}

.edit-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.button-content,
.tool-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
}

.button-icon,
.tool-icon {
  font-size: 14px;
  line-height: 1;
}

.button-text,
.tool-label {
  font-size: 10px;
  line-height: 1;
}

/* Contrôles de zoom */
.zoom-controls {
  display: flex;
  align-items: center;
  gap: 6px;
}

.zoom-value {
  min-width: 35px;
  text-align: center;
  font-size: 12px;
  font-weight: 600;
  color: #374151;
}

/* Options d'affichage */
.display-options {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  cursor: pointer;
  font-size: 12px;
  color: #374151;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 3px;
  transition: all 0.15s ease;
  user-select: none;
  position: relative;
}

.toggle-label:hover {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.toggle-label input[type="checkbox"] {
  cursor: pointer;
  width: 14px;
  height: 14px;
}

.toggle-label.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.toggle-text {
  font-weight: 500;
}

.toggle-shortcut {
  font-size: 10px;
  color: #9ca3af;
}

.tooltip-hint {
  position: absolute;
  bottom: -20px;
  left: 0;
  font-size: 10px;
  white-space: nowrap;
  background: #1f2937;
  color: white;
  padding: 2px 6px;
  border-radius: 2px;
  z-index: 1000;
  display: none;
}

.toggle-label:hover .tooltip-hint {
  display: block;
}

/* ===== PDF BUILDER PROPERTIES PANEL ===== */
.pdf-builder-properties,
.properties-panel-container {
  width: 340px;
  background: #fafbfc;
  border-left: 2px solid #e5e7eb;
  overflow-y: auto;
  padding: 16px;
  box-shadow: -2px 0 4px rgba(0,0,0,0.06);
  z-index: 50;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.pdf-builder-properties h3,
.properties-panel-container h3 {
  margin: 0 0 12px 0;
  font-size: 13px;
  font-weight: 700;
  color: #1f2937;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 8px;
}

.pdf-builder-properties .property-group,
.properties-panel-container .property-group {
  margin-bottom: 16px;
  padding: 12px;
  background: #ffffff;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
}

.pdf-builder-properties label,
.properties-panel-container label {
  display: block;
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 6px;
}

.pdf-builder-properties input,
.pdf-builder-properties select,
.pdf-builder-properties textarea,
.properties-panel-container input,
.properties-panel-container select,
.properties-panel-container textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 13px;
  font-family: inherit;
}

.pdf-builder-properties input:focus,
.pdf-builder-properties select:focus,
.pdf-builder-properties textarea:focus,
.properties-panel-container input:focus,
.properties-panel-container select:focus,
.properties-panel-container textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* ===== PROPERTIES PANEL TABS ===== */
.properties-tabs {
  display: flex;
  gap: 0;
  background: #f3f4f6;
  border-bottom: 1px solid #e5e7eb;
  margin: -16px -16px 16px -16px;
  padding: 0;
}

.properties-tab-button {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px 12px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  color: #6b7280;
  transition: all 0.2s ease;
  border-bottom: 2px solid transparent;
}

.properties-tab-button:hover {
  background: #e5e7eb;
  color: #374151;
}

.properties-tab-button.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
  background: white;
}

.properties-tab-icon {
  font-size: 14px;
}

.properties-tab-label {
  font-weight: 600;
}

/* Contenu des onglets des propriétés */
.properties-tab-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* ===== SIDEBAR / ELEMENT LIBRARY ===== */
.pdf-builder-sidebar,
.element-library-panel {
  width: 280px;
  background: #fafbfc;
  border-right: 1px solid #f1f3f4;
  overflow-y: auto;
  box-shadow: 1px 0 3px rgba(0,0,0,0.03);
}

.pdf-builder-element-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid #f1f3f4;
  cursor: pointer;
  transition: background 0.2s ease;
}

.pdf-builder-element-item:hover {
  background: #f0f4ff;
}

.pdf-builder-element-item icon {
  font-size: 20px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pdf-builder-element-item span {
  font-size: 13px;
  color: #374151;
  font-weight: 500;
}

/* ===== CANVAS CONTAINER ===== */
.pdf-builder-canvas-container,
.canvas-container {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  background: #fafbfc;
  overflow: auto;
  padding: 20px;
  position: relative;
}

.pdf-builder-canvas,
.pdf-canvas {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  border: 1px solid #f1f3f4;
  position: relative;
}

/* ===== PROPERTIES PANEL TOGGLE BUTTON ===== */
.properties-panel-toggle {
  position: absolute;
  right: 0;
  top: 0;
  width: 32px;
  height: 32px;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: none;
  transition: all 0.2s ease;
  z-index: 200;
  margin: 8px;
}

.properties-panel-toggle:hover {
  background: #e5e7eb;
  border-color: #9ca3af;
}

.properties-panel-toggle:active {
  background: #d1d5db;
}

.properties-panel-toggle .toggle-arrow {
  font-size: 16px;
  color: #374151;
  font-weight: bold;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease;
}

/* Quand le panel est ouvert, la flèche pointe vers la droite */
.properties-panel-toggle.open .toggle-arrow {
  transform: rotate(180deg);
}

/* ===== WORKSPACE ===== */
.pdf-builder-workspace,
.editor-workspace {
  display: flex;
  flex: 1;
  overflow: hidden;
  background: #f9fafb;
  gap: 0;
}

.editor-toolbar-secondary {
  display: flex;
  gap: 12px;
  padding: 12px 16px;
  background: #ffffff;
  border-top: 1px solid #f1f3f4;
  align-items: center;
  flex-wrap: wrap;
}

.editor-toolbar-secondary button {
  padding: 6px 12px;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  transition: all 0.2s ease;
}

.editor-toolbar-secondary button:hover {
  background: #f3f4f6;
  border-color: #d1d5db;
}

.editor-toolbar-secondary button.active {
  background: #2563eb;
  color: white;
  border-color: #2563eb;
}

.status-info {
  margin-left: auto;
  font-size: 12px;
  color: #6b7280;
}
  `;

  const styleElement = document.createElement('style');
  styleElement.id = 'pdf-builder-injected-styles';
  styleElement.textContent = styles;
  document.head.appendChild(styleElement);

  console.log('[PDF Builder] ✅ Styles injectés avec succès');
}

// Injector au chargement du DOM
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', injectMissingStyles);
} else {
  injectMissingStyles();
}

// Aussi injector après un délai pour les cas où React charge plus tard
setTimeout(injectMissingStyles, 500);