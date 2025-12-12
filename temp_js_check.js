
> <script>
                  // Force cache clear
                  if ('serviceWorker' in navigator) {
                      navigator.serviceWorker.getRegistrations().then(function(registrations) {
                          for(var i = 0; i < registrations.length; i++) {
                              var registration = registrations[i];
                              registration.unregister();
                          }
                      });
                  }
                  console.log('Cache cleared by PDF Builder');
  
                  // Gestionnaire des modales Canvas
                  (function() {
                      'use strict';
  
                      // Valeurs par défaut pour les paramètres Canvas (injectées depuis PHP)
                      <?php
                      $canvas_defaults_json = json_encode($default_canvas_options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | 
JSON_UNESCAPED_UNICODE);
                      echo "var CANVAS_DEFAULT_VALUES = $canvas_defaults_json;";
                      ?>
  
                      console.log('[PDF Builder] 🚀 MODALS_SYSTEM_v2.1 - Initializing Canvas modals system (FIXED VERSION)');
                      console.log('[PDF Builder] 📅 Date: 2025-12-11 21:35');
                      console.log('[PDF Builder] 🔧 Fix: HTML/PHP moved outside script tags');
  
                      // Fonction d'initialisation avec retry
                      function initializeModals(retryCount) {
                          if (typeof retryCount === 'undefined') retryCount = 0;
                          var maxRetries = 5;
                          var retryDelay = 200; // ms
  
                          try {
                              console.log('[PDF Builder] MODALS_INIT - Initializing Canvas modals system (attempt ' + (retryCount + 1) + '/' + 
(maxRetries + 1) + ')');
  
                              // Initialiser l'état des toggles existants
                              initializeToggleStates();
  
                              // Vérifier que les modals existent
                              const modalCategories = ['affichage', 'navigation', 'comportement', 'systeme'];
                              let missingModals = [];
                              let foundModals = [];
  
                              modalCategories.forEach(function(category) {
                                  const modalId = 'canvas-' + category + '-modal-overlay';
                                  const modal = document.getElementById(modalId);
                                  if (!modal) {
                                      missingModals.push(modalId);
                                      console.warn('[PDF Builder] MODALS_INIT - Missing modal: ' + modalId);
                                  } else {
                                      foundModals.push(modalId);
                                      console.log('[PDF Builder] MODALS_INIT - Found modal: ' + modalId);
                                  }
                              });
  
                              // Vérifier que les boutons de configuration existent
                              const configButtons = document.querySelectorAll('.canvas-configure-btn');
                              console.log('[PDF Builder] MODALS_INIT - Found ' + configButtons.length + ' configuration buttons');
  
                              if (missingModals.length > 0) {
                                  if (retryCount < maxRetries) {
                                      console.warn('[PDF Builder] MODALS_INIT - ' + missingModals.length + ' modals missing, retrying in ' + retryDelay + 
'ms...');
                                      setTimeout(function() { initializeModals(retryCount + 1); }, retryDelay);
                                      return;
                                  } else {
                                      console.error('[PDF Builder] MODALS_INIT - ' + missingModals.length + ' modals are missing after ' + maxRetries + ' 
retries:', missingModals);
                                      alert('Attention: ' + missingModals.length + ' modales sont manquantes. Certaines fonctionnalités risquent de ne 
pas fonctionner.');
                                  }
                              } else {
                                  console.log('[PDF Builder] MODALS_INIT - All ' + foundModals.length + ' modals found successfully');
                              }
  
                              if (configButtons.length === 0) {
                                  console.warn('[PDF Builder] MODALS_INIT - No configuration buttons found');
                                  if (retryCount < maxRetries) {
                                      console.warn('[PDF Builder] MODALS_INIT - Retrying buttons check in ' + retryDelay + 'ms...');
                                      setTimeout(function() { initializeModals(retryCount + 1); }, retryDelay);
                                      return;
                                  }
                              }
  
                              console.log('[PDF Builder] MODALS_INIT - Modal system initialized successfully');
  
                              // Attacher les event listeners maintenant que tout est chargé
                              attachEventListeners();
  
                          } catch (error) {
                              console.error('[PDF Builder] MODALS_INIT - Error during initialization:', error);
                              if (retryCount < maxRetries) {
                                  console.warn('[PDF Builder] MODALS_INIT - Retrying after error in ' + retryDelay + 'ms...');
                                  setTimeout(function() { initializeModals(retryCount + 1); }, retryDelay);
                              }
                          }
                      }
  
                      // Fonction pour appliquer les paramètres de la modale (synchroniser et fermer)
                      function applyModalSettings(category) {
                          console.log('[JS APPLY] ===== STARTING applyModalSettings for category:', category);
  
                          const modal = document.querySelector('#canvas-' + category + '-modal-overlay');
                          if (!modal) {
                              console.error('[JS APPLY] ❌ Modal not found for category:', category);
                              return;
                          }
  
                          console.log('[JS APPLY] ✅ Modal found, synchronizing values...');
  
                          // Collecter les valeurs de la modale et mettre à jour les champs cachés
                          const inputs = modal.querySelectorAll('input, select, textarea');
                          console.log('[JS APPLY] Found', inputs.length, 'input elements in modal');
  
                          let updatedCount = 0;
  
                          inputs.forEach(function(input) {
                              console.log('[JS APPLY] Processing input: ' + (input.name || input.id) + ' (type: ' + input.type + ')');
                              if (input.name && input.name.startsWith('pdf_builder_canvas_')) {
                                  // Trouver le champ caché correspondant dans le formulaire principal
                                  const hiddenField = document.querySelector('input[name="pdf_builder_settings[' + input.name + ']"]');
                                  if (hiddenField) {
                                      // Mettre à jour la valeur du champ caché
                                      const newValue = input.type === 'checkbox' ? (input.checked ? '1' : '0') : input.value;
                                      hiddenField.value = newValue;
                                      updatedCount++;
                                      console.log('[JS APPLY] ✅ Synced: ' + input.name + ' = ' + newValue);
                                  } else {
                                      console.warn('[JS APPLY] ⚠️ Hidden field not found for: ' + input.name);
                                  }
                              }
                          });
  
                          console.log('[JS APPLY] Total synced fields: ' + updatedCount);
  
                          // Fermer la modale
                          closeModal('canvas-' + category + '-modal-overlay');
  
                          // Afficher un message de confirmation
                          showNotification('success', '✅ ' + updatedCount + ' paramètres appliqués', {
                              duration: 2000,
                              dismissible: true
                          });
  
                          console.log('[JS APPLY] ===== APPLY PROCESS COMPLETED =====');
  
                          // DEBUG: Vérifier que les champs cachés ont été mis à jour
                          console.log('[JS APPLY] ===== VERIFYING HIDDEN FIELDS =====');
                          inputs.forEach(function(input) {
                              if (input.name && input.name.startsWith('pdf_builder_canvas_')) {
                                  const hiddenField = document.querySelector('input[name="pdf_builder_settings[' + input.name + ']"]');
                                  if (hiddenField) {
                                      console.log('[JS APPLY] VERIFY: ' + input.name + ' -> hidden field value: ' + hiddenField.value);
                                  }
                              }
                          });
                      }
  
                      // Fonction pour attacher tous les event listeners
                      function attachEventListeners() {
                          console.log('[PDF Builder] ATTACH_LISTENERS - Attaching event listeners');
  
                          // Gestionnaire d'événements pour les boutons de configuration - VERSION RENFORCÉE
                          document.addEventListener('click', function(e) {
                              try {
                                  // Gestionnaire pour ouvrir les modales
                                  const button = e.target.closest('.canvas-configure-btn');
                                  if (button) {
                                      e.preventDefault();
                                      console.log('[PDF Builder] CONFIG_BUTTON - Configure button clicked');
  
                                      const card = button.closest('.canvas-card');
                                      if (card) {
                                          const category = card.getAttribute('data-category');
                                          if (category) {
                                              const modalId = 'canvas-' + category + '-modal-overlay';
                                              console.log('[PDF Builder] CONFIG_BUTTON - Opening modal for category: ' + category);
                                              openModal(modalId);
                                          } else {
                                              console.error('[PDF Builder] CONFIG_BUTTON - No data-category attribute found on card');
                                          }
                                      } else {
                                          console.error('[PDF Builder] CONFIG_BUTTON - No canvas-card parent found');
                                      }
                                      return;
                                  }
  
                                  // Gestionnaire pour fermer les modales
                                  const closeBtn = e.target.closest('.canvas-modal-close, .cache-modal-close');
                                  if (closeBtn) {
                                      e.preventDefault();
                                      console.log('[PDF Builder] CLOSE_BUTTON - Close button clicked');
  
                                      const modal = closeBtn.closest('.canvas-modal-overlay, .cache-modal');
                                      if (modal) {
                                          closeModal(modal);
                                      }
                                      return;
                                  }
  
                                  // Gestionnaire pour les clics sur l'overlay (fermer la modale)
                                  if (e.target.classList.contains('canvas-modal-overlay')) {
                                      e.preventDefault();
                                      console.log('[PDF Builder] OVERLAY_CLICK - Overlay clicked, closing modal');
                                      closeModal(e.target);
                                      return;
                                  }
  
                                  // Gestionnaire pour appliquer les paramètres (synchroniser et fermer la modale)
                                  const applyBtn = e.target.closest('.canvas-modal-apply');
                                  if (applyBtn) {
                                      e.preventDefault();
                                      console.log('[PDF Builder] APPLY_BUTTON - Apply button clicked');
  
                                      const category = applyBtn.getAttribute('data-category');
                                      if (category) {
                                          // Synchroniser les valeurs de la modale vers les champs cachés
                                          applyModalSettings(category);
                                      } else {
                                          console.error('[PDF Builder] APPLY_BUTTON - No data-category attribute on apply button');
                                      }
                                      return;
                                  }
  
                                  // Gestionnaire pour réinitialiser les paramètres Canvas
                                  const resetBtn = e.target.closest('#reset-canvas-settings');
                                  if (resetBtn) {
                                      e.preventDefault();
                                      console.log('[PDF Builder] RESET_BUTTON - Reset Canvas settings clicked');
  
                                      if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres Canvas aux valeurs par défaut ? Cette 
action est irréversible.')) {
                                          console.log('[PDF Builder] RESET_BUTTON - User confirmed, calling resetCanvasSettings');
                                          resetCanvasSettings();
                                      } else {
                                          console.log('[PDF Builder] RESET_BUTTON - User cancelled reset');
                                      }
                                      return;
                                  }
  
                                  // Gestionnaire pour annuler les modales
                                  const cancelBtn = e.target.closest('.canvas-modal-cancel, .button-secondary');
                                  if (cancelBtn) {
                                      e.preventDefault();
                                      console.log('[PDF Builder] CANCEL_BUTTON - Cancel button clicked');
  
                                      const modal = cancelBtn.closest('.canvas-modal-overlay');
                                      if (modal) {
                                          closeModal(modal);
                                      }
                                      return;
                                  }
  
                              } catch (error) {
                                  console.error('[PDF Builder] EVENT_HANDLER - Error in click handler:', error);
                              }
                          });
  
                          // Gestionnaire pour la touche Échap - VERSION RENFORCÉE
                          document.addEventListener('keydown', function(e) {
                              if (e.key === 'Escape') {
                                  console.log('[PDF Builder] ESC_KEY - Escape key pressed');
  
                                  // Fermer toutes les modales ouvertes
                                  const openModals = document.querySelectorAll('.canvas-modal-overlay[style*="display: flex"], 
.cache-modal[style*="display: block"]');
                                  openModals.forEach(function(modal) {
                                      closeModal(modal);
                                  });
                              }
                          });
  
                          console.log('[PDF Builder] ATTACH_LISTENERS - Event listeners attached successfully');
                      }
  
                      // Appeler l'initialisation quand le DOM est prêt et les modals sont chargées
                      function initWhenReady() {
                          if (document.readyState === 'loading') {
                              document.addEventListener('DOMContentLoaded', function() { waitForModalsAndInitialize(0); });
                          } else {
                              // DOM déjà chargé, attendre les modals
                              waitForModalsAndInitialize(0);
                          }
                      }
  
                      // Fonction pour attendre que les modals soient chargées
                      function waitForModalsAndInitialize(attempt) {
                          if (typeof attempt === 'undefined') attempt = 0;
                          var maxAttempts = 10;
                          var modalIds = [
                              'canvas-dimensions-modal-overlay',
                              'canvas-apparence-modal-overlay',
                              'canvas-grille-modal-overlay',
                              'canvas-zoom-modal-overlay',
                              'canvas-interactions-modal-overlay',
                              'canvas-export-modal-overlay',
                              'canvas-performance-modal-overlay',
                              'canvas-debug-modal-overlay'
                          ];
  
                          const allModalsLoaded = modalIds.every(function(id) { return document.getElementById(id) !== null; });
  
                          if (allModalsLoaded) {
                              console.log('[PDF Builder] MODALS_READY - All modals loaded, initializing...');
                              initializeModals(0);
                          } else if (attempt < maxAttempts) {
                              console.log('[PDF Builder] MODALS_WAIT - Waiting for modals (attempt ' + (attempt + 1) + '/' + maxAttempts + ')');
                              setTimeout(function() { waitForModalsAndInitialize(attempt + 1); }, 100);
                          } else {
                              console.error('[PDF Builder] MODALS_TIMEOUT - Modals failed to load after maximum attempts');
                              // Essayer quand même d'initialiser avec ce qui est disponible
                              initializeModals(0);
                          }
                      }
  
                      initWhenReady();
  
                      // Fonction pour ouvrir une modale - VERSION RENFORCÉE
                      function openModal(modalId) {
                          try {
                              console.log('[PDF Builder] OPEN_MODAL - Attempting to open: ' + modalId);
  
                              const modal = document.getElementById(modalId);
                              if (!modal) {
                                  console.error('[PDF Builder] OPEN_MODAL - Modal element not found: ' + modalId);
                                  alert('Erreur: La modale ' + modalId + ' n\'a pas été trouvée.');
                                  return;
                              }
  
                              // Extraire la catégorie depuis l'ID de la modale
                              const categoryMatch = modalId.match(/canvas-(\w+)-modal-overlay/);
                              if (categoryMatch) {
                                  const category = categoryMatch[1];
                                  console.log('[PDF Builder] OPEN_MODAL - Opening modal for category: ' + category);
  
                                  // Mettre à jour les valeurs avant d'ouvrir
                                  updateModalValues(category);
                              } else {
                                  console.warn('[PDF Builder] OPEN_MODAL - Could not extract category from modalId: ' + modalId);
                              }
  
                              // Afficher la modale avec animation
                              modal.style.display = 'flex';
                              document.body.style.overflow = 'hidden';
  
                              // Accessibilité - utiliser inert au lieu d'aria-hidden
                              modal.removeAttribute('inert');
                              modal.focus();
  
                              console.log('[PDF Builder] OPEN_MODAL - Modal opened successfully: ' + modalId);
  
                          } catch (error) {
                              console.error('[PDF Builder] OPEN_MODAL - Error opening modal ' + modalId + ':', error);
                              alert('Erreur lors de l\'ouverture de la modale: ' + error.message);
                          }
                      }
  
                      // Fonction pour initialiser l'état des toggles
                      function initializeToggleStates() {
                          console.log('[PDF Builder] TOGGLE_INIT - Initializing toggle states');
  
                          // Parcourir tous les toggles existants
                          const allToggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
                          allToggles.forEach(function(checkbox) {
                              const toggleSwitch = checkbox.closest('.toggle-switch');
                              if (toggleSwitch) {
                                  if (checkbox.checked) {
                                      toggleSwitch.classList.add('checked');
                                  } else {
                                      toggleSwitch.classList.remove('checked');
                                  }
                                  console.log('[PDF Builder] TOGGLE_INIT - ' + (checkbox.id || checkbox.name) + ': checked=' + checkbox.checked);
                              }
                          });
  
                          console.log('[PDF Builder] TOGGLE_INIT - Initialized ' + allToggles.length + ' toggles');
                      }
                          console.log('[PDF Builder] UPDATE_CARDS - Updating canvas cards display');
  
                          try {
                              // Mettre à jour les indicateurs de statut sur les cartes
                              const cards = document.querySelectorAll('.canvas-card');
                              cards.forEach(function(card) {
                                  const category = card.getAttribute('data-category');
                                  if (category) {
                                      // Marquer comme valeurs par défaut
                                      const statusIndicator = card.querySelector('.canvas-status');
                                      if (statusIndicator) {
                                          statusIndicator.textContent = 'Défaut';
                                          statusIndicator.className = 'canvas-status status-default';
                                      }
  
                                      console.log('[PDF Builder] UPDATE_CARDS - Updated card for category: ' + category);
                                  }
                              });
  
                              // Forcer la mise à jour des valeurs dans toutes les modales ouvertes
                              const openModals = document.querySelectorAll('.canvas-modal-overlay[style*="display: flex"]');
                              openModals.forEach(function(modal) {
                                  const category = modal.id.replace('canvas-', '').replace('-modal-overlay', '');
                                  if (category) {
                                      updateModalValues(category);
                                  }
                              });
  
                          } catch (error) {
                              console.error('[PDF Builder] UPDATE_CARDS - Error updating cards display:', error);
                          }
                      }
  
                      // Fonction pour mettre à jour les valeurs d'une modale avec les paramètres actuels
                      function updateModalValues(category) {
                          console.log('[PDF Builder] UPDATE_MODAL - Called with category: ' + category);
                          console.log('[PDF Builder] UPDATE_MODAL - Starting modal value synchronization');
                          const modal = document.querySelector('#canvas-' + category + '-modal-overlay');
                          if (!modal) {
                              console.log('[PDF Builder] UPDATE_MODAL - Modal #canvas-' + category + '-modal-overlay not found');
                              return;
                          }
                          console.log('[PDF Builder] UPDATE_MODAL - Modal found, processing category: ' + category);
  
                          // Mapping des champs selon la catégorie
                          const fieldMappings = {
                              'dimensions': {
                                  'canvas_width': 'pdf_builder_canvas_width',
                                  'canvas_height': 'pdf_builder_canvas_height',
                                  'canvas_dpi': 'pdf_builder_canvas_dpi',
                                  'canvas_format': 'pdf_builder_canvas_format'
                              },
                              'apparence': {
                                  'canvas_bg_color': 'pdf_builder_canvas_bg_color',
                                  'canvas_border_color': 'pdf_builder_canvas_border_color',
                                  'canvas_border_width': 'pdf_builder_canvas_border_width',
                                  'canvas_shadow_enabled': 'pdf_builder_canvas_shadow_enabled',
                                  'canvas_container_bg_color': 'pdf_builder_canvas_container_bg_color'
                              },
                              'grille': {
                                  'canvas_grid_enabled': 'pdf_builder_canvas_grid_enabled',
                                  'canvas_grid_size': 'pdf_builder_canvas_grid_size',
                                  'canvas_guides_enabled': 'pdf_builder_canvas_guides_enabled',
                                  'canvas_snap_to_grid': 'pdf_builder_canvas_snap_to_grid'
                              },
                              'zoom': {
                                  'canvas_zoom_min': 'pdf_builder_canvas_zoom_min',
                                  'canvas_zoom_max': 'pdf_builder_canvas_zoom_max',
                                  'canvas_zoom_default': 'pdf_builder_canvas_zoom_default',
                                  'canvas_zoom_step': 'pdf_builder_canvas_zoom_step'
                              },
                              'interactions': {
                                  'canvas_drag_enabled': 'pdf_builder_canvas_drag_enabled',
                                  'canvas_resize_enabled': 'pdf_builder_canvas_resize_enabled',
                                  'canvas_rotate_enabled': 'pdf_builder_canvas_rotate_enabled',
                                  'canvas_multi_select': 'pdf_builder_canvas_multi_select',
                                  'canvas_selection_mode': 'pdf_builder_canvas_selection_mode',
                                  'canvas_keyboard_shortcuts': 'pdf_builder_canvas_keyboard_shortcuts'
                              },
                              'export': {
                                  'canvas_export_quality': 'pdf_builder_canvas_export_quality',
                                  'canvas_export_format': 'pdf_builder_canvas_export_format',
                                  'canvas_export_transparent': 'pdf_builder_canvas_export_transparent'
                              },
                              'performance': {
                                  'canvas_fps_target': 'pdf_builder_canvas_fps_target',
                                  'canvas_memory_limit_js': 'pdf_builder_canvas_memory_limit_js',
                                  'canvas_response_timeout': 'pdf_builder_canvas_response_timeout'
                              },
                              'debug': {
                                  'canvas_debug_enabled': 'pdf_builder_canvas_debug_enabled',
                                  'canvas_performance_monitoring': 'pdf_builder_canvas_performance_monitoring',
                                  'canvas_error_reporting': 'pdf_builder_canvas_error_reporting'
                              }
                          };
  
                          const mappings = fieldMappings[category];
                          if (!mappings) return;
  
                          // Valeurs par défaut pour les champs Canvas
                          const defaultValues = CANVAS_DEFAULT_VALUES;
  
                          // Mettre à jour chaque champ
                          for (const entry of Object.entries(mappings)) {
                              const fieldId = entry[0];
                              const settingKey = entry[1];
                              const field = modal.querySelector('#' + fieldId + ', [name="' + settingKey + '"]');
                              if (field) {
                                  // Chercher la valeur dans les champs cachés
                                  const hiddenField = document.querySelector('input[name="pdf_builder_settings[' + settingKey + ']"]');
                                  let value = '';
                                  let valueSource = 'default'; // default, custom
                                  
                                  if (hiddenField && hiddenField.value && hiddenField.value.trim() !== '') {
                                      value = hiddenField.value;
                                      valueSource = 'custom';
                                      console.log('[PDF Builder] UPDATE_MODAL - Using custom value for ' + settingKey + ': ' + value);
                                  } else {
                                      // Utiliser la valeur par défaut si rien n'est trouvé
                                      value = defaultValues[settingKey] || '';
                                      valueSource = 'default';
                                      console.log('[PDF Builder] UPDATE_MODAL - Using default value for ' + settingKey + ': ' + value);
                                  }
                                  
                                  if (category === 'grille') {
                                      console.log('[PDF Builder] GRID_UPDATE - Processing grid field ' + fieldId + ' with value: ' + value);
                                  }
                                  
                                  // Log spécifique pour les toggles de grille
                                  if (['canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid'].includes(fieldId)) {
                                      console.log('GRID_TOGGLE: Updating ' + fieldId + ' (' + settingKey + ') with value: ' + value + ', field type: ' + 
field.type);
                                  }
                                  
                                  if (field.type === 'checkbox') {
                                      field.checked = value === '1';
                                      // Synchroniser la classe CSS pour les toggles
                                      const toggleSwitch = field.closest('.toggle-switch');
                                      if (toggleSwitch) {
                                          if (value === '1') {
                                              toggleSwitch.classList.add('checked');
                                          } else {
                                              toggleSwitch.classList.remove('checked');
                                          }
                                          console.log('TOGGLE_DEBUG: ' + fieldId + ' - checked=' + field.checked + ', toggle classes: ' + 
toggleSwitch.className);
                                      } else {
                                          console.log('TOGGLE_DEBUG: ' + fieldId + ' - No toggle-switch parent found');
                                      }
                                      if (['canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid', 'canvas_drag_enabled', 
'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_multi_select', 'canvas_keyboard_shortcuts'].includes(fieldId)) {
                                          console.log('ALL_TOGGLES: Set checkbox ' + fieldId + ' checked to: ' + field.checked + ', toggle class: ' + 
(toggleSwitch ? toggleSwitch.className : 'no toggle'));
                                      }
                                  } else {
                                      field.value = value;
                                      
                                      // Pour les selects, mettre à jour l'attribut selected
                                      if (field.tagName === 'SELECT') {
                                          const options = field.querySelectorAll('option');
                                          options.forEach(function(option) {
                                              option.selected = option.value === value;
                                          });
                                      }
                                  }
  
                                  // Ajouter les indicateurs visuels selon la source de la valeur
                                  field.classList.remove('value-default', 'value-custom', 'value-cached');
                                  field.classList.add('value-' + valueSource);
                                  
                                  // Ajouter un indicateur textuel près du champ
                                  let indicator = field.parentNode.querySelector('.value-indicator');
                                  if (!indicator) {
                                      indicator = document.createElement('span');
                                      indicator.className = 'value-indicator';
                                      field.parentNode.appendChild(indicator);
                                  }
                                  
                                  if (valueSource === 'default') {
                                      indicator.textContent = ' (Défaut)';
                                      indicator.style.color = '#666';
                                  } else if (valueSource === 'custom') {
                                      // Ne plus afficher "(Personnalisé)" car c'est redondant
                                      indicator.textContent = '';
                                  } else if (valueSource === 'cached') {
                                      indicator.textContent = ' (En cache)';
                                      indicator.style.color = '#f39c12';
                                  }
                              } else {
                                  console.log('Field not found for ' + fieldId + ' or ' + settingKey);
                              }
                          }
  
                          // Ajouter les event listeners pour les changements (sans cache localStorage)
                          const allInputs = modal.querySelectorAll('input, select, textarea');
                          allInputs.forEach(function(input) {
                              input.addEventListener('change', function() {
                                  console.log('[PDF Builder] INPUT_CHANGE - ' + input.name + ' changed');
                              });
  
                              // Gestion spécifique des toggles (checkboxes)
                              if (input.type === 'checkbox') {
                                  input.addEventListener('change', function() {
                                      const toggleSwitch = this.closest('.toggle-switch');
                                      if (toggleSwitch) {
                                          if (this.checked) {
                                              toggleSwitch.classList.add('checked');
                                          } else {
                                              toggleSwitch.classList.remove('checked');
                                          }
                                          console.log('[PDF Builder] TOGGLE_CHANGE - ' + this.id + ': checked=' + this.checked + ', class=' + 
toggleSwitch.className);
                                      }
                                  });
                              }
                          });
                      }
  
                      // Fonction pour fermer une modale - VERSION RENFORCÉE
                      function closeModal(modalOrId) {
                          try {
                              let modal;
                              let modalId;
  
                              // Déterminer si c'est un ID ou un élément
                              if (typeof modalOrId === 'string') {
                                  modalId = modalOrId;
                                  modal = document.getElementById(modalId);
                              } else if (modalOrId && modalOrId.nodeType === 1) {
                                  // C'est un élément DOM
                                  modal = modalOrId;
                                  modalId = modal.id || 'unknown-modal';
                              } else {
                                  console.error('[PDF Builder] CLOSE_MODAL - Invalid parameter:', modalOrId);
                                  return;
                              }
  
                              console.log('[PDF Builder] CLOSE_MODAL - Attempting to close: ' + modalId);
  
                              if (!modal) {
                                  console.warn('[PDF Builder] CLOSE_MODAL - Modal element not found: ' + modalId);
                                  return;
                              }
  
                              // Masquer la modale
                              modal.style.display = 'none';
                              document.body.style.overflow = '';
  
                              // Accessibilité - déplacer le focus et utiliser inert
                              document.body.focus();
                              modal.setAttribute('inert', '');
  
                              console.log('[PDF Builder] CLOSE_MODAL - Modal closed successfully: ' + modalId);
  
                          } catch (error) {
                              console.error('[PDF Builder] CLOSE_MODAL - Error closing modal:', error);
                          }
                      }
  
                      // Fonction helper pour les notifications avec fallback
                      function showNotification(type, message, options) {
                          console.log('[PDF Builder] NOTIFICATION_HELPER - Attempting to show ' + type + ' notification:', message);
  
                          if (type === 'success' && typeof showSuccessNotification === 'function') {
                              console.log('[PDF Builder] NOTIFICATION_HELPER - Using showSuccessNotification');
                              showSuccessNotification(message, options);
                          } else if (type === 'error' && typeof showErrorNotification === 'function') {
                              console.log('[PDF Builder] NOTIFICATION_HELPER - Using showErrorNotification');
                              showErrorNotification(message, options);
                          } else {
                              console.log('[PDF Builder] NOTIFICATION_HELPER - Using alert fallback');
                              alert(message);
                          }
                      }
  
                      // Fonction pour réinitialiser tous les paramètres Canvas aux valeurs par défaut
                      function resetCanvasSettings() {
                          console.log('[PDF Builder] RESET_CANVAS - Function called, starting Canvas settings reset');
  
                          try {
                              // Valeurs par défaut pour tous les paramètres Canvas
                              const defaultValues = {
                                  'pdf_builder_canvas_width': '794',
                                  'pdf_builder_canvas_height': '1123',
                                  'pdf_builder_canvas_dpi': '96',
                                  'pdf_builder_canvas_format': 'A4',
                                  'pdf_builder_canvas_bg_color': '#ffffff',
                                  'pdf_builder_canvas_border_color': '#cccccc',
                                  'pdf_builder_canvas_border_width': '1',
                                  'pdf_builder_canvas_container_bg_color': '#f8f9fa',
                                  'pdf_builder_canvas_shadow_enabled': '0',
                                  'pdf_builder_canvas_grid_enabled': '1',
                                  'pdf_builder_canvas_grid_size': '20',
                                  'pdf_builder_canvas_guides_enabled': '1',
                                  'pdf_builder_canvas_snap_to_grid': '1',
                                  'pdf_builder_canvas_zoom_min': '0.1',
                                  'pdf_builder_canvas_zoom_max': '5',
                                  'pdf_builder_canvas_zoom_default': '1',
                                  'pdf_builder_canvas_zoom_step': '0.1',
                                  'pdf_builder_canvas_export_quality': '90',
                                  'pdf_builder_canvas_export_format': 'pdf',
                                  'pdf_builder_canvas_export_transparent': '0',
                                  'pdf_builder_canvas_drag_enabled': '1',
                                  'pdf_builder_canvas_resize_enabled': '1',
                                  'pdf_builder_canvas_rotate_enabled': '1',
                                  'pdf_builder_canvas_multi_select': '1',
                                  'pdf_builder_canvas_selection_mode': 'single',
                                  'pdf_builder_canvas_keyboard_shortcuts': '1',
                                  'pdf_builder_canvas_fps_target': '60',
                                  'pdf_builder_canvas_memory_limit_js': '128',
                                  'pdf_builder_canvas_response_timeout': '5000',
                                  'pdf_builder_canvas_lazy_loading_editor': '1',
                                  'pdf_builder_canvas_preload_critical': '1',
                                  'pdf_builder_canvas_lazy_loading_plugin': '1',
                                  'pdf_builder_canvas_debug_enabled': '0',
                                  'pdf_builder_canvas_performance_monitoring': '1',
                                  'pdf_builder_canvas_error_reporting': '1',
                                  'pdf_builder_canvas_memory_limit_php': '256'
                              };
  
                              // Réinitialiser les champs cachés
                              Object.keys(defaultValues).forEach(function(key) {
                                  const hiddenField = document.querySelector('input[name="pdf_builder_settings[' + key + ']"]');
                                  if (hiddenField) {
                                      hiddenField.value = defaultValues[key];
                                      console.log('[PDF Builder] RESET_CANVAS - Reset ' + key + ' to ' + defaultValues[key]);
                                  }
                              });
  
                              // AJAX supprimé - réinitialisation simplifiée côté client uniquement
                              console.log('[PDF Builder] RESET_CANVAS - Client-side reset completed');
  
                              // Fermer toutes les modales ouvertes
                              const openModals = document.querySelectorAll('.canvas-modal-overlay[style*="display: flex"]');
                              openModals.forEach(function(modal) {
                                  const modalId = modal.id;
                                  closeModal(modalId);
                              });
  
                              // Mettre à jour l'affichage des cartes avec les nouvelles valeurs
                              updateCanvasCardsDisplay();
  
                              // Notification de succès
                              showNotification('success', '✅ Tous les paramètres Canvas ont été réinitialisés aux valeurs par défaut (côté client).', {
                                  duration: 6000,
                                  dismissible: true
                              });
  
                          } catch (error) {
                              console.error('[PDF Builder] RESET_CANVAS - Error during reset:', error);
                              // Notification d'erreur générale
                              showNotification('error', '❌ Erreur lors de la réinitialisation des paramètres.', {
                                  duration: 8000,
                                  dismissible: true
                              });
                          }
                      }
  
                      console.log('Modal manager initialized');
  
                      console.log('SYSTEM_INIT: Canvas modals system initialized successfully');
                  })();
              </script>
  
              <!-- Inclusion des modales Canvas -->
              <?php require_once __DIR__ . '/settings-modals.php'; ?>
  
  </section> <!-- Fermeture de settings-section contenu-settings -->
  


