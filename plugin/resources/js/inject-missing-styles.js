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

/* ===== ACCORDION STYLES ===== */
.accordion {
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  margin-bottom: 8px;
  overflow: hidden;
}

.accordion-header {
  width: 100%;
  padding: 12px 16px;
  background: #f9fafb;
  border: none;
  text-align: left;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
  transition: background-color 0.2s ease;
}

.accordion-header:hover {
  background: #f3f4f6;
}

.accordion-header:focus {
  outline: 2px solid #2563eb;
  outline-offset: -2px;
}

.accordion-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.accordion-icon {
  font-size: 16px;
}

.accordion-arrow {
  font-size: 12px;
  transition: transform 0.2s ease;
  color: #6b7280;
}

.accordion-arrow.open {
  transform: rotate(180deg);
}

.accordion-content {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  background: #ffffff;
}

.accordion-content.open {
  max-height: 1000px; /* Ajuster selon le contenu */
}

/* ===== PROPERTY ROW STYLES ===== */
.property-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 0;
  gap: 12px;
}

.property-row label {
  flex: 1;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  margin: 0;
}

/* ===== TOGGLE STYLES ===== */
.toggle {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  flex-shrink: 0;
}

.toggle input {
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
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.toggle input:checked + .toggle-slider {
  background-color: #2563eb;
}

.toggle input:checked + .toggle-slider:before {
  transform: translateX(20px);
  background-color: white;
}

/* ===== COLOR PICKER STYLES ===== */
.color-input {
  width: 50px;
  height: 36px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  cursor: pointer;
  background: white;
  padding: 2px;
}

.color-input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.color-presets {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-top: 8px;
}

.color-preset {
  width: 24px;
  height: 24px;
  border-radius: 4px;
  border: 2px solid #e5e7eb;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.color-preset:hover {
  transform: scale(1.1);
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.color-preset.active {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
}

.color-preset.transparent {
  background: linear-gradient(45deg, #f3f4f6 25%, transparent 25%, transparent 75%, #f3f4f6 75%);
  background-size: 8px 8px;
}

.color-preset.active::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 12px;
  font-weight: bold;
  text-shadow: 0 1px 2px rgba(0,0,0,0.5);
}

/* ===== FORM CONTROLS STYLES ===== */
.properties-content input[type="text"],
.properties-content input[type="number"],
.properties-content select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 13px;
  background: white;
  color: #374151;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.properties-content input:focus,
.properties-content select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.properties-content input:disabled,
.properties-content select:disabled {
  background: #f9fafb;
  color: #9ca3af;
  cursor: not-allowed;
}

/* ===== NUMBER INPUT WITH UNITS ===== */
.number-input-container {
  display: flex;
  align-items: center;
  gap: 4px;
}

.number-input-container input {
  flex: 1;
}

.unit-label {
  font-size: 12px;
  color: #6b7280;
  font-weight: 500;
  min-width: 20px;
}

/* ===== RANGE SLIDER STYLES ===== */
.range-slider {
  width: 100%;
  height: 6px;
  border-radius: 3px;
  background: #e5e7eb;
  outline: none;
  -webkit-appearance: none;
}

.range-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
  border: 2px solid white;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.range-slider::-moz-range-thumb {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
  border: 2px solid white;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* ===== SELECT STYLES ===== */
.properties-content select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 8px center;
  background-repeat: no-repeat;
  background-size: 16px 16px;
  padding-right: 32px;
}

/* ===== PROPERTIES TABS STYLES ===== */
.properties-tabs {
  display: flex;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 16px;
}

.tab-btn {
  flex: 1;
  padding: 10px 16px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  color: #6b7280;
  border-bottom: 2px solid transparent;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.tab-btn:hover {
  color: #374151;
  background: #f9fafb;
}

.tab-btn.active {
  color: #2563eb;
  border-bottom-color: #2563eb;
  background: white;
}

.tab-content {
  padding: 16px 0;
}

/* ===== NO SELECTION STATE ===== */
.no-selection {
  text-align: center;
  padding: 40px 20px;
  color: #6b7280;
}

.no-selection-icon {
  font-size: 48px;
  margin-bottom: 16px;
  display: block;
}

.no-selection p {
  margin: 8px 0 0 0;
  font-size: 14px;
}

.selection-info {
  font-size: 12px;
  color: #9ca3af;
  margin-top: 4px;
}

/* ===== SLIDER STYLES ===== */
.slider-container {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
}

.slider {
  flex: 1;
  height: 6px;
  border-radius: 3px;
  background: #e5e7eb;
  outline: none;
  -webkit-appearance: none;
  appearance: none;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
  border: 2px solid white;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.slider::-moz-range-thumb {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #2563eb;
  cursor: pointer;
  border: 2px solid white;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

.slider-value {
  min-width: 35px;
  text-align: center;
  font-size: 12px;
  font-weight: 600;
  color: #374151;
  background: #f9fafb;
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
}

/* ===== TEXTAREA STYLES ===== */
.text-input,
textarea.text-input {
  width: 100%;
  min-height: 80px;
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 13px;
  font-family: inherit;
  line-height: 1.4;
  resize: vertical;
  background: white;
  color: #374151;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.text-input:focus,
textarea.text-input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.text-input::placeholder,
textarea.text-input::placeholder {
  color: #9ca3af;
  font-style: italic;
}

/* ===== ADAPTIVE CONTROL STYLES ===== */
.adaptive-compact {
  margin-bottom: 8px;
}

.adaptive-compact label {
  font-size: 12px;
  font-weight: 500;
  color: #374151;
  margin-bottom: 4px;
  display: block;
}

/* ===== LAYOUT SECTIONS STYLES ===== */
.layout-sections {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* ===== TABLE STYLE PREVIEW STYLES ===== */
.table-style-preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
  gap: 12px;
  margin-top: 8px;
  max-width: 100%;
}

.table-style-preview-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.table-style-preview-container {
  position: relative;
  border: 2px solid transparent;
  border-radius: 8px;
  padding: 6px;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-style-preview-container:hover {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.table-style-preview-container.selected {
  border-color: #3b82f6;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
}

.table-style-preview-container.selected::before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border-radius: 10px;
  z-index: -1;
  opacity: 0.1;
}

.table-style-preview-thumbnail {
  width: 100%;
  height: 70px;
  border-radius: 6px;
  background-color: #ffffff;
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
}

.table-style-preview-thumbnail:hover {
  transform: scale(1.03);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.table-style-selection-indicator {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 12px;
  height: 12px;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  z-index: 10;
}

.table-style-selection-indicator::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 6px;
  color: white;
  font-weight: bold;
}

.table-style-name {
  font-size: 11px;
  color: #374151;
  text-align: center;
  font-weight: 500;
  margin-top: 4px;
}

.table-style-description {
  font-size: 9px;
  color: #6b7280;
  text-align: center;
  line-height: 1.2;
  margin-top: 2px;
}

/* ===== CHECKBOX GROUP STYLES ===== */
.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.checkbox-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.checkbox-item input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #2563eb;
}

.checkbox-item label {
  font-size: 13px;
  color: #374151;
  cursor: pointer;
  margin: 0;
}

/* ===== ADAPTIVE LAYOUT STYLES ===== */
/* Système de layout adaptatif pour les contrôles du sidebar */

/* Layout horizontal par défaut */
.adaptive-control.adaptive-horizontal {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.adaptive-control.adaptive-horizontal .adaptive-label {
  flex: 0 0 auto;
  min-width: 120px;
  font-weight: 600;
  color: #374151;
  font-size: 13px;
  white-space: nowrap;
}

.adaptive-control.adaptive-horizontal .adaptive-content {
  flex: 1;
  min-width: 0; /* Permet au contenu de rétrécir */
}

/* Layout vertical quand espace insuffisant */
.adaptive-control.adaptive-vertical {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
}

.adaptive-control.adaptive-vertical .adaptive-label {
  font-weight: 600;
  color: #374151;
  font-size: 13px;
  margin-bottom: 4px;
}

.adaptive-control.adaptive-vertical .adaptive-content {
  width: 100%;
}

/* Animations fluides pour les transitions */
.adaptive-control {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.adaptive-control .adaptive-content {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Classes utilitaires pour forcer les layouts */
.adaptive-force-horizontal {
  flex-direction: row !important;
}

.adaptive-force-vertical {
  flex-direction: column !important;
}

/* Indicateurs de mode démo */
.adaptive-demo-mode .adaptive-control::before {
  content: 'H';
  position: absolute;
  top: -8px;
  right: -8px;
  width: 16px;
  height: 16px;
  background: #10b981;
  color: white;
  border-radius: 50%;
  font-size: 10px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.adaptive-demo-mode .adaptive-control.adaptive-vertical::before {
  content: 'V';
  background: #f59e0b;
}
  `;

  const styleElement = document.createElement('style');
  styleElement.id = 'pdf-builder-injected-styles';
  styleElement.textContent = styles;
  document.head.appendChild(styleElement);

  console.log('[PDF Builder] ✅ Styles injectés avec succès');
}

// Injector au chargement du DOM
document.addEventListener('DOMContentLoaded', injectMissingStyles);

// Injector immédiat si le DOM est déjà chargé
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', injectMissingStyles);
} else {
  injectMissingStyles();
}