import { useState, useEffect } from 'react';

export const useGlobalSettings = () => {
  const [settings, setSettings] = useState({
    // Paramètres généraux du canvas
    defaultCanvasWidth: 210,
    defaultCanvasHeight: 297,
    defaultCanvasUnit: 'mm',
    defaultOrientation: 'portrait',
    canvasBackgroundColor: '#ffffff',
    canvasShowTransparency: false,
    containerBackgroundColor: '#f8f9fa',
    containerShowTransparency: false,

    // Marges de sécurité
    marginTop: 10,
    marginRight: 10,
    marginBottom: 10,
    marginLeft: 10,
    showMargins: true,

    // Paramètres de grille
    showGrid: true,
    gridSize: 10,
    gridColor: '#e0e0e0',
    gridOpacity: 30,

    // Aimantation
    snapToGrid: true,
    snapToElements: true,
    snapToMargins: true,
    snapTolerance: 5,

    // Lignes guides
    showGuides: true,
    lockGuides: false,

    // Paramètres de zoom et navigation
    defaultZoom: '100',
    minZoom: 10,
    maxZoom: 500,
    zoomStep: 25,
    panWithMouse: true,
    smoothZoom: true,
    showZoomIndicator: true,
    zoomWithWheel: true,
    zoomToSelection: true,

    // Paramètres de sélection et manipulation
    showResizeHandles: true,
    handleSize: 8,
    handleColor: '#007cba',
    enableRotation: true,
    rotationStep: 15,
    rotationSnap: true,
    multiSelect: true,
    selectAllShortcut: true,
    showSelectionBounds: true,
    copyPasteEnabled: true,
    duplicateOnDrag: false,

    // Paramètres d'export et qualité
    exportQuality: 'print',
    exportFormat: 'pdf',
    compressImages: true,
    imageQuality: 85,
    maxImageSize: 2048,
    includeMetadata: true,
    pdfAuthor: '',
    pdfSubject: '',
    autoCrop: false,
    embedFonts: true,
    optimizeForWeb: true,

    // Paramètres avancés
    enableHardwareAcceleration: true,
    limitFps: true,
    maxFps: 60,
    autoSaveEnabled: true,
    autoSaveInterval: 30,
    autoSaveVersions: 10,
    undoLevels: 50,
    redoLevels: 50,
    enableKeyboardShortcuts: true,
    debugMode: false,
    showFps: false,

    // Anciens paramètres (pour compatibilité)
    resizeHandleSize: 12,
    resizeHandleColor: '#007cba',
    resizeHandleBorderColor: '#ffffff',
    resizeHandleOffset: -6,
    selectionBorderWidth: 2,
    selectionBorderColor: 'var(--primary-color)',
    selectionShadowOpacity: 0.1,
    selectionBorderSpacing: 2,
    resizeZoneSize: 16,
    resizeZoneOpacity: 0.1,
    showResizeHandlesLegacy: true,
    showResizeZones: true,
    selectionAnimation: true,
    defaultTextColor: '#000000',
    defaultBackgroundColor: '#ffffff',
    defaultFontSize: 14,

    // Paramètres AJAX
    ajaxurl: window.ajaxurl || '',
    nonce: window.pdfBuilderNonce || ''
  });

  // Fonction pour récupérer les paramètres WordPress
  const getWordPressSettings = () => {
    if (window.pdfBuilderCanvasSettings) {
      return window.pdfBuilderCanvasSettings;
    }
    return null;
  };

  // Fonction pour récupérer ajaxurl et nonce
  const getAjaxSettings = () => {
    // Essayer d'abord les variables globales définies par WordPress
    if (window.pdfBuilderAjax) {
      return {
        ajaxurl: window.pdfBuilderAjax.ajaxurl || '',
        nonce: window.pdfBuilderAjax.nonce || ''
      };
    }
    // Fallback vers les variables individuelles
    return {
      ajaxurl: window.ajaxurl || '',
      nonce: window.pdfBuilderNonce || window.pdf_builder_nonce || ''
    };
  };

  // Charger les paramètres depuis WordPress ou localStorage au montage
  useEffect(() => {
    const wpSettings = getWordPressSettings();
    const ajaxSettings = getAjaxSettings();

    console.log('useGlobalSettings - wpSettings:', wpSettings);
    console.log('useGlobalSettings - canvas_background_color:', wpSettings?.canvas_background_color);

    if (wpSettings) {
      // Utiliser les paramètres WordPress
      setSettings(prev => ({
        ...prev,
        ...ajaxSettings, // Ajouter ajaxurl et nonce

        // Paramètres généraux du canvas
        defaultCanvasWidth: wpSettings.default_canvas_width || prev.defaultCanvasWidth,
        defaultCanvasHeight: wpSettings.default_canvas_height || prev.defaultCanvasHeight,
        defaultCanvasUnit: wpSettings.default_canvas_unit || prev.defaultCanvasUnit,
        defaultOrientation: wpSettings.default_orientation || prev.defaultOrientation,
        canvasBackgroundColor: wpSettings.canvas_background_color || prev.canvasBackgroundColor,
        canvasShowTransparency: wpSettings.canvas_show_transparency !== undefined ? wpSettings.canvas_show_transparency : prev.canvasShowTransparency,
        containerBackgroundColor: wpSettings.container_background_color || prev.containerBackgroundColor,
        containerShowTransparency: wpSettings.container_show_transparency !== undefined ? wpSettings.container_show_transparency : prev.containerShowTransparency,

        // Marges de sécurité
        marginTop: wpSettings.margin_top || prev.marginTop,
        marginRight: wpSettings.margin_right || prev.marginRight,
        marginBottom: wpSettings.margin_bottom || prev.marginBottom,
        marginLeft: wpSettings.margin_left || prev.marginLeft,
        showMargins: wpSettings.show_margins !== undefined ? wpSettings.show_margins : prev.showMargins,

        // Paramètres de grille
        showGrid: wpSettings.show_grid !== undefined ? wpSettings.show_grid : prev.showGrid,
        gridSize: wpSettings.grid_size || prev.gridSize,
        gridColor: wpSettings.grid_color || prev.gridColor,
        gridOpacity: wpSettings.grid_opacity || prev.gridOpacity,

        // Aimantation
        snapToGrid: wpSettings.snap_to_grid !== undefined ? wpSettings.snap_to_grid : prev.snapToGrid,
        snapToElements: wpSettings.snap_to_elements !== undefined ? wpSettings.snap_to_elements : prev.snapToElements,
        snapToMargins: wpSettings.snap_to_margins !== undefined ? wpSettings.snap_to_margins : prev.snapToMargins,
        snapTolerance: wpSettings.snap_tolerance || prev.snapTolerance,

        // Lignes guides
        showGuides: wpSettings.show_guides !== undefined ? wpSettings.show_guides : prev.showGuides,
        lockGuides: wpSettings.lock_guides !== undefined ? wpSettings.lock_guides : prev.lockGuides,

        // Paramètres de zoom et navigation
        defaultZoom: wpSettings.default_zoom || prev.defaultZoom,
        minZoom: wpSettings.min_zoom || prev.minZoom,
        maxZoom: wpSettings.max_zoom || prev.maxZoom,
        zoomStep: wpSettings.zoom_step || prev.zoomStep,
        panWithMouse: wpSettings.pan_with_mouse !== undefined ? wpSettings.pan_with_mouse : prev.panWithMouse,
        smoothZoom: wpSettings.smooth_zoom !== undefined ? wpSettings.smooth_zoom : prev.smoothZoom,
        showZoomIndicator: wpSettings.show_zoom_indicator !== undefined ? wpSettings.show_zoom_indicator : prev.showZoomIndicator,
        zoomWithWheel: wpSettings.zoom_with_wheel !== undefined ? wpSettings.zoom_with_wheel : prev.zoomWithWheel,
        zoomToSelection: wpSettings.zoom_to_selection !== undefined ? wpSettings.zoom_to_selection : prev.zoomToSelection,

        // Paramètres de sélection et manipulation
        showResizeHandles: true, // Forcer à true pour corriger le bug des poignées
        handleSize: wpSettings.handle_size || prev.handleSize,
        handleColor: wpSettings.handle_color || prev.handleColor,
        enableRotation: wpSettings.enable_rotation !== undefined ? wpSettings.enable_rotation : prev.enableRotation,
        rotationStep: wpSettings.rotation_step || prev.rotationStep,
        rotationSnap: wpSettings.rotation_snap !== undefined ? wpSettings.rotation_snap : prev.rotationSnap,
        multiSelect: wpSettings.multi_select !== undefined ? wpSettings.multi_select : prev.multiSelect,
        selectAllShortcut: wpSettings.select_all_shortcut !== undefined ? wpSettings.select_all_shortcut : prev.selectAllShortcut,
        showSelectionBounds: wpSettings.show_selection_bounds !== undefined ? wpSettings.show_selection_bounds : prev.showSelectionBounds,
        copyPasteEnabled: wpSettings.copy_paste_enabled !== undefined ? wpSettings.copy_paste_enabled : prev.copyPasteEnabled,
        duplicateOnDrag: wpSettings.duplicate_on_drag !== undefined ? wpSettings.duplicate_on_drag : prev.duplicateOnDrag,

        // Paramètres d'export et qualité
        exportQuality: wpSettings.export_quality || prev.exportQuality,
        exportFormat: wpSettings.export_format || prev.exportFormat,
        compressImages: wpSettings.compress_images !== undefined ? wpSettings.compress_images : prev.compressImages,
        imageQuality: wpSettings.image_quality || prev.imageQuality,
        maxImageSize: wpSettings.max_image_size || prev.maxImageSize,
        includeMetadata: wpSettings.include_metadata !== undefined ? wpSettings.include_metadata : prev.includeMetadata,
        pdfAuthor: wpSettings.pdf_author || prev.pdfAuthor,
        pdfSubject: wpSettings.pdf_subject || prev.pdfSubject,
        autoCrop: wpSettings.auto_crop !== undefined ? wpSettings.auto_crop : prev.autoCrop,
        embedFonts: wpSettings.embed_fonts !== undefined ? wpSettings.embed_fonts : prev.embedFonts,
        optimizeForWeb: wpSettings.optimize_for_web !== undefined ? wpSettings.optimize_for_web : prev.optimizeForWeb,

        // Paramètres avancés
        enableHardwareAcceleration: wpSettings.enable_hardware_acceleration !== undefined ? wpSettings.enable_hardware_acceleration : prev.enableHardwareAcceleration,
        limitFps: wpSettings.limit_fps !== undefined ? wpSettings.limit_fps : prev.limitFps,
        maxFps: wpSettings.max_fps || prev.maxFps,
        autoSaveEnabled: wpSettings.auto_save_enabled !== undefined ? wpSettings.auto_save_enabled : prev.autoSaveEnabled,
        autoSaveInterval: wpSettings.auto_save_interval || prev.autoSaveInterval,
        autoSaveVersions: wpSettings.auto_save_versions || prev.autoSaveVersions,
        undoLevels: wpSettings.undo_levels || prev.undoLevels,
        redoLevels: wpSettings.redo_levels || prev.redoLevels,
        enableKeyboardShortcuts: wpSettings.enable_keyboard_shortcuts !== undefined ? wpSettings.enable_keyboard_shortcuts : prev.enableKeyboardShortcuts,
        debugMode: wpSettings.debug_mode !== undefined ? wpSettings.debug_mode : prev.debugMode,
        showFps: wpSettings.show_fps !== undefined ? wpSettings.show_fps : prev.showFps,

        // Anciens paramètres (pour compatibilité)
        resizeHandleSize: wpSettings.canvas_handle_size || prev.resizeHandleSize,
        resizeHandleColor: wpSettings.canvas_handle_color || prev.resizeHandleColor,
        resizeHandleBorderColor: wpSettings.canvas_handle_hover_color || prev.resizeHandleBorderColor,
        selectionBorderWidth: wpSettings.canvas_border_width || prev.selectionBorderWidth,
        selectionBorderColor: wpSettings.canvas_border_color || prev.selectionBorderColor,
        selectionBorderSpacing: wpSettings.canvas_border_spacing || prev.selectionBorderSpacing,
        showResizeHandlesLegacy: wpSettings.canvas_resize_handles_enabled !== undefined ? wpSettings.canvas_resize_handles_enabled : prev.showResizeHandlesLegacy,
        showResizeZones: wpSettings.canvas_element_borders_enabled !== undefined ? wpSettings.canvas_element_borders_enabled : prev.showResizeZones,
        defaultTextColor: wpSettings.default_text_color || prev.defaultTextColor,
        defaultBackgroundColor: wpSettings.default_background_color || prev.defaultBackgroundColor,
        defaultFontSize: wpSettings.default_font_size || prev.defaultFontSize
      }));
    } else {
      // Fallback vers localStorage si les paramètres WordPress ne sont pas disponibles
      const ajaxSettings = getAjaxSettings();
      const savedSettings = localStorage.getItem('pdf-builder-global-settings');
      if (savedSettings) {
        try {
          const parsedSettings = JSON.parse(savedSettings);
          // Forcer showResizeHandles à true pour corriger le bug des poignées
          parsedSettings.showResizeHandles = true;
          setSettings(prev => ({ ...prev, ...ajaxSettings, ...parsedSettings }));
        } catch (error) {
          setSettings(prev => ({ ...prev, ...ajaxSettings }));
        }
      } else {
        setSettings(prev => ({ ...prev, ...ajaxSettings }));
      }
    }
  }, []);

  // Appliquer les paramètres aux variables CSS
  useEffect(() => {
    const root = document.documentElement;

    // Appliquer les paramètres des points de redimensionnement
    root.style.setProperty('--resize-handle-size', `${settings.resizeHandleSize}px`);
    root.style.setProperty('--resize-handle-color', settings.resizeHandleColor);
    root.style.setProperty('--resize-handle-border-color', settings.resizeHandleBorderColor);
    root.style.setProperty('--resize-handle-offset', `${settings.resizeHandleOffset}px`);

    // Appliquer les paramètres des bordures de sélection
    root.style.setProperty('--selection-border-width', `${settings.selectionBorderWidth}px`);
    root.style.setProperty('--selection-border-color', settings.selectionBorderColor);
    root.style.setProperty('--selection-border-spacing', `${settings.selectionBorderSpacing}px`);
    root.style.setProperty('--selection-shadow-opacity', settings.selectionShadowOpacity);

    // Appliquer les paramètres des zones de redimensionnement
    root.style.setProperty('--resize-zone-size', `${settings.resizeZoneSize}px`);
    root.style.setProperty('--resize-zone-opacity', settings.resizeZoneOpacity);

    // Appliquer les paramètres généraux
    root.style.setProperty('--show-resize-handles', settings.showResizeHandles ? 'block' : 'none');
    root.style.setProperty('--show-resize-zones', settings.showResizeZones ? 'auto' : 'none');
    root.style.setProperty('--selection-animation', settings.selectionAnimation ? 'all 0.2s ease' : 'none');

  }, [settings]);

  const updateSettings = async (newSettings) => {
    setSettings(prev => {
      const updated = { ...prev, ...newSettings };
      // Forcer showResizeHandles à true pour corriger le bug des poignées
      updated.showResizeHandles = true;
      // Sauvegarder dans localStorage pour la compatibilité
      localStorage.setItem('pdf-builder-global-settings', JSON.stringify(updated));

      // Sauvegarder dans WordPress via AJAX
      const ajaxSettings = getAjaxSettings();
      if (ajaxSettings.ajaxurl && ajaxSettings.nonce) {
        // Convertir les paramètres camelCase vers snake_case pour WordPress
        const wpSettings = {
          show_grid: updated.showGrid,
          grid_size: updated.gridSize,
          grid_color: updated.gridColor,
          grid_opacity: updated.gridOpacity,
          snap_to_grid: updated.snapToGrid,
          snap_to_elements: updated.snapToElements,
          snap_to_margins: updated.snapToMargins,
          snap_tolerance: updated.snapTolerance,
          show_guides: updated.showGuides,
          lock_guides: updated.lockGuides,
          default_zoom: updated.defaultZoom,
          min_zoom: updated.minZoom,
          max_zoom: updated.maxZoom,
          zoom_step: updated.zoomStep,
          pan_with_mouse: updated.panWithMouse,
          smooth_zoom: updated.smoothZoom,
          show_zoom_indicator: updated.showZoomIndicator,
          zoom_with_wheel: updated.zoomWithWheel,
          zoom_to_selection: updated.zoomToSelection,
          show_resize_handles: updated.showResizeHandles,
          handle_size: updated.handleSize,
          handle_color: updated.handleColor,
          enable_rotation: updated.enableRotation,
          rotation_step: updated.rotationStep,
          rotation_snap: updated.rotationSnap,
          multi_select: updated.multiSelect,
          select_all_shortcut: updated.selectAllShortcut,
          show_selection_bounds: updated.showSelectionBounds,
          copy_paste_enabled: updated.copyPasteEnabled,
          duplicate_on_drag: updated.duplicateOnDrag,
          export_quality: updated.exportQuality,
          export_format: updated.exportFormat,
          compress_images: updated.compressImages,
          image_quality: updated.imageQuality,
          max_image_size: updated.maxImageSize,
          include_metadata: updated.includeMetadata,
          pdf_author: updated.pdfAuthor,
          pdf_subject: updated.pdfSubject,
          auto_crop: updated.autoCrop,
          embed_fonts: updated.embedFonts,
          optimize_for_web: updated.optimizeForWeb,
          enable_hardware_acceleration: updated.enableHardwareAcceleration,
          limit_fps: updated.limitFps,
          max_fps: updated.maxFps,
          auto_save_enabled: updated.autoSaveEnabled,
          auto_save_interval: updated.autoSaveInterval,
          auto_save_versions: updated.autoSaveVersions,
          undo_levels: updated.undoLevels,
          redo_levels: updated.redoLevels,
          enable_keyboard_shortcuts: updated.enableKeyboardShortcuts,
          debug_mode: updated.debugMode,
          show_fps: updated.showFps,
          // Paramètres canvas
          default_canvas_width: updated.defaultCanvasWidth,
          default_canvas_height: updated.defaultCanvasHeight,
          default_canvas_unit: updated.defaultCanvasUnit,
          canvas_background_color: updated.canvasBackgroundColor,
          canvas_show_transparency: updated.canvasShowTransparency,
          margin_top: updated.marginTop,
          margin_right: updated.marginRight,
          margin_bottom: updated.marginBottom,
          margin_left: updated.marginLeft,
          show_margins: updated.showMargins
        };

        // Faire l'appel AJAX en arrière-plan (sans bloquer)
        fetch(ajaxSettings.ajaxurl + '?action=pdf_builder_save_settings_page', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            ...wpSettings,
            nonce: ajaxSettings.nonce
          })
        })
        .then(response => {
          if (!response.ok) {
            console.warn('Erreur lors de la sauvegarde des paramètres dans WordPress:', response.status);
          } else {
            console.log('Paramètres sauvegardés avec succès dans WordPress');
          }
        })
        .catch(error => {
          console.warn('Erreur AJAX lors de la sauvegarde des paramètres:', error);
        });
      }

      return updated;
    });
  };

  const resetToDefaults = () => {
    const defaultSettings = {
      resizeHandleSize: 12,
      resizeHandleColor: '#007cba',
      resizeHandleBorderColor: '#ffffff',
      selectionBorderWidth: 2,
      selectionBorderColor: 'var(--primary-color)',
      selectionBorderSpacing: 2,
      selectionShadowOpacity: 0.1,
      resizeZoneSize: 16,
      resizeZoneOpacity: 0.1,
      showResizeHandles: true,
      showResizeZones: true,
      selectionAnimation: true
    };
    setSettings(defaultSettings);
  };

  return {
    settings,
    updateSettings,
    resetToDefaults
  };
};