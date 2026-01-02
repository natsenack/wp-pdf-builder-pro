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
.pdf-builder-toolbar {
  display: flex;
  gap: 8px;
  padding: 12px 16px;
  background: #ffffff;
  border-bottom: 2px solid #e5e7eb;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.06);
  flex-wrap: wrap;
  min-height: 50px;
  z-index: 100;
}

.pdf-builder-toolbar button,
.pdf-builder-toolbar select,
.pdf-builder-toolbar input {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s ease;
  color: #374151;
}

.pdf-builder-toolbar button:hover {
  background: #f3f4f6;
  border-color: #9ca3af;
  color: #1f2937;
}

.pdf-builder-toolbar button:active {
  background: #e5e7eb;
}

.pdf-builder-toolbar button.active {
  background: #2563eb;
  color: white;
  border-color: #2563eb;
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
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  background: #2563eb;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
  transition: all 0.3s ease;
  z-index: 200;
}

.properties-panel-toggle:hover {
  background: #1d4ed8;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
  transform: translateY(-50%) scale(1.1);
}

.properties-panel-toggle:active {
  transform: translateY(-50%) scale(0.95);
}

.properties-panel-toggle .toggle-arrow {
  font-size: 20px;
  color: white;
  font-weight: bold;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.3s ease;
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