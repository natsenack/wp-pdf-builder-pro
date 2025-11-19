import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';

declare global {
  interface Window {
    pdfBuilderCanvasSettings?: Record<string, unknown>;
  }
}

export interface CanvasSettingsContextType {
  // Dimensions
  canvasWidth: number;
  canvasHeight: number;
  canvasUnit: 'px' | 'mm' | 'cm' | 'in';
  canvasOrientation: 'portrait' | 'landscape';
  
  // Couleurs
  canvasBackgroundColor: string;
  containerBackgroundColor: string;
  borderColor: string;
  borderWidth: number;
  shadowEnabled: boolean;
  
  // Marges
  marginTop: number;
  marginRight: number;
  marginBottom: number;
  marginLeft: number;
  showMargins: boolean;
  
  // Grille
  gridShow: boolean;
  gridSize: number;
  gridColor: string;
  gridSnapEnabled: boolean;
  gridSnapTolerance: number;
  
  // Zoom
  zoomDefault: number;
  zoomMin: number;
  zoomMax: number;
  zoomStep: number;
  zoomWheelSupport: boolean;
  
  // Sélection
  selectionMultiSelectEnabled: boolean;
  selectionRotationEnabled: boolean;
  selectionCopyPasteEnabled: boolean;
  selectionShowHandles: boolean;
  selectionHandleSize: number;
  selectionHandleColor: string;
  
  // Export
  exportQuality: number;
  exportFormat: 'pdf' | 'png' | 'jpg';
  exportCompression: boolean;
  exportIncludeMetadata: boolean;
  
  // Historique
  historyUndoLevels: number;
  historyRedoLevels: number;
  historyAutoSaveEnabled: boolean;
  historyAutoSaveInterval: number;
  
  isLoading: boolean;
  isReady: boolean;
  error: string | null;
  
  // Fonction pour recharger les paramètres
  refreshSettings: () => void;
}

const CanvasSettingsContext = createContext<CanvasSettingsContextType | undefined>(undefined);

// Valeurs par défaut
const DEFAULT_SETTINGS: CanvasSettingsContextType = {
  canvasWidth: 210,
  canvasHeight: 297,
  canvasUnit: 'mm',
  canvasOrientation: 'portrait',
  
  canvasBackgroundColor: '#ffffff',
  containerBackgroundColor: '#f8f9fa',
  borderColor: '#cccccc',
  borderWidth: 1,
  shadowEnabled: false,
  
  marginTop: 20,
  marginRight: 20,
  marginBottom: 20,
  marginLeft: 20,
  showMargins: false,
  
  gridShow: true,
  gridSize: 10,
  gridColor: '#e5e7eb',
  gridSnapEnabled: true,
  gridSnapTolerance: 8,
  
  zoomDefault: 100,
  zoomMin: 10,
  zoomMax: 500,
  zoomStep: 10,
  zoomWheelSupport: true,
  
  selectionMultiSelectEnabled: true,
  selectionRotationEnabled: false,
  selectionCopyPasteEnabled: true,
  selectionShowHandles: true,
  selectionHandleSize: 8,
  selectionHandleColor: '#0066cc',
  
  exportQuality: 100,
  exportFormat: 'pdf',
  exportCompression: true,
  exportIncludeMetadata: true,
  
  historyUndoLevels: 50,
  historyRedoLevels: 50,
  historyAutoSaveEnabled: true,
  historyAutoSaveInterval: 30000,
  
  isLoading: true,
  isReady: false,
  error: null,
  
  refreshSettings: () => {}
};

interface CanvasSettingsProviderProps {
  children: ReactNode;
}

function loadSettingsFromWindowObj(): CanvasSettingsContextType {
  try {
    const windowSettings = window.pdfBuilderCanvasSettings;
    
    if (!windowSettings) {
      return {
        ...DEFAULT_SETTINGS,
        isLoading: false,
        isReady: true
      };
    }

    // Mapper les paramètres depuis le format WordPress vers notre format
    const newSettings: CanvasSettingsContextType = {
      // Dimensions
      canvasWidth: (windowSettings.canvas_width as number) ?? DEFAULT_SETTINGS.canvasWidth,
      canvasHeight: (windowSettings.canvas_height as number) ?? DEFAULT_SETTINGS.canvasHeight,
      canvasUnit: (windowSettings.canvas_unit as 'px' | 'mm' | 'cm' | 'in') ?? DEFAULT_SETTINGS.canvasUnit,
      canvasOrientation: (windowSettings.canvas_orientation as 'portrait' | 'landscape') ?? DEFAULT_SETTINGS.canvasOrientation,
      
      // Couleurs
      canvasBackgroundColor: (windowSettings.canvas_background_color as string) ?? DEFAULT_SETTINGS.canvasBackgroundColor,
      containerBackgroundColor: (windowSettings.container_background_color as string) ?? DEFAULT_SETTINGS.containerBackgroundColor,
      borderColor: (windowSettings.border_color as string) ?? DEFAULT_SETTINGS.borderColor,
      borderWidth: (windowSettings.border_width as number) ?? DEFAULT_SETTINGS.borderWidth,
      shadowEnabled: (windowSettings.shadow_enabled as boolean) === true || (windowSettings.shadow_enabled as string) === '1',
      
      // Marges
      marginTop: (windowSettings.margin_top as number) ?? DEFAULT_SETTINGS.marginTop,
      marginRight: (windowSettings.margin_right as number) ?? DEFAULT_SETTINGS.marginRight,
      marginBottom: (windowSettings.margin_bottom as number) ?? DEFAULT_SETTINGS.marginBottom,
      marginLeft: (windowSettings.margin_left as number) ?? DEFAULT_SETTINGS.marginLeft,
      showMargins: (windowSettings.show_margins as boolean) === true || (windowSettings.show_margins as string) === '1',
      
      // Grille
      gridShow: (windowSettings.show_grid as boolean) !== false,
      gridSize: (windowSettings.grid_size as number) ?? DEFAULT_SETTINGS.gridSize,
      gridColor: (windowSettings.grid_color as string) ?? DEFAULT_SETTINGS.gridColor,
      gridSnapEnabled: (windowSettings.snap_to_grid as boolean) !== false,
      gridSnapTolerance: (windowSettings.snap_tolerance as number) ?? DEFAULT_SETTINGS.gridSnapTolerance,
      
      // Zoom
      zoomDefault: (windowSettings.default_zoom as number) ?? DEFAULT_SETTINGS.zoomDefault,
      zoomMin: (windowSettings.min_zoom as number) ?? DEFAULT_SETTINGS.zoomMin,
      zoomMax: (windowSettings.max_zoom as number) ?? DEFAULT_SETTINGS.zoomMax,
      zoomStep: (windowSettings.zoom_step as number) ?? DEFAULT_SETTINGS.zoomStep,
      zoomWheelSupport: (windowSettings.zoom_with_wheel as boolean) !== false,
      
      // Sélection
      selectionMultiSelectEnabled: (windowSettings.multi_select as boolean) !== false,
      selectionRotationEnabled: (windowSettings.enable_rotation as boolean) !== false,
      selectionCopyPasteEnabled: (windowSettings.copy_paste_enabled as boolean) !== false,
      selectionShowHandles: (windowSettings.show_resize_handles as boolean) !== false,
      selectionHandleSize: (windowSettings.handle_size as number) ?? DEFAULT_SETTINGS.selectionHandleSize,
      selectionHandleColor: (windowSettings.handle_color as string) ?? DEFAULT_SETTINGS.selectionHandleColor,
      
      // Export
      exportQuality: (windowSettings.image_quality as number) ?? DEFAULT_SETTINGS.exportQuality,
      exportFormat: (windowSettings.export_format as 'pdf' | 'png' | 'jpg') ?? DEFAULT_SETTINGS.exportFormat,
      exportCompression: (windowSettings.compress_images as boolean) !== false,
      exportIncludeMetadata: (windowSettings.include_metadata as boolean) !== false,
      
      // Historique
      historyUndoLevels: (windowSettings.history_undo_levels as number) ?? DEFAULT_SETTINGS.historyUndoLevels,
      historyRedoLevels: (windowSettings.history_redo_levels as number) ?? DEFAULT_SETTINGS.historyRedoLevels,
      historyAutoSaveEnabled: (windowSettings.history_auto_save_enabled as boolean) !== false,
      historyAutoSaveInterval: (windowSettings.history_auto_save_interval as number) ?? DEFAULT_SETTINGS.historyAutoSaveInterval,
      
      isLoading: false,
      isReady: true,
      error: null,
      
      refreshSettings: () => {}
    };

    return newSettings;
  } catch (_err) {
    const errorMsg = _err instanceof Error ? _err.message : 'Unknown error';
    return {
      ...DEFAULT_SETTINGS,
      isLoading: false,
      isReady: false,
      error: errorMsg,
      refreshSettings: () => {}
    };
  }
}

export function CanvasSettingsProvider({ children }: CanvasSettingsProviderProps) {
  const [settings, setSettings] = useState<CanvasSettingsContextType>(() => DEFAULT_SETTINGS);

  // Load settings from server on mount
  useEffect(() => {
    console.log('CanvasSettingsContext - Loading initial settings from server');
    handleRefresh();
  }, []);

  const handleRefresh = async () => {
    console.log('CanvasSettingsContext - handleRefresh called');
    try {
      // Faire un appel AJAX pour récupérer les paramètres mis à jour
      const response = await fetch(window.pdfBuilderAjax?.ajax_url || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'pdf_builder_get_canvas_settings',
          nonce: window.pdfBuilderAjax?.nonce || ''
        })
      });

      if (response.ok) {
        const data = await response.json();
        console.log('CanvasSettingsContext - AJAX response:', data);
        if (data.success && data.data) {
          console.log('CanvasSettingsContext - border_color from server:', data.data.border_color, 'border_width:', data.data.border_width);
          console.log('CanvasSettingsContext - shadow_enabled from server:', data.data.shadow_enabled, 'type:', typeof data.data.shadow_enabled);
          if (data.debug) {
            console.log('CanvasSettingsContext - DEBUG INFO:', data.debug);
          }
          // Mapper les données reçues vers le format du contexte
          const newSettings: CanvasSettingsContextType = {
            ...DEFAULT_SETTINGS,
            // Dimensions
            canvasWidth: data.data.canvas_width ?? DEFAULT_SETTINGS.canvasWidth,
            canvasHeight: data.data.canvas_height ?? DEFAULT_SETTINGS.canvasHeight,
            canvasUnit: data.data.canvas_unit ?? DEFAULT_SETTINGS.canvasUnit,
            canvasOrientation: data.data.canvas_orientation ?? DEFAULT_SETTINGS.canvasOrientation,
            
            // Couleurs
            canvasBackgroundColor: data.data.canvas_background_color ?? DEFAULT_SETTINGS.canvasBackgroundColor,
            containerBackgroundColor: data.data.container_background_color ?? DEFAULT_SETTINGS.containerBackgroundColor,
            borderColor: data.data.border_color ?? DEFAULT_SETTINGS.borderColor,
            borderWidth: data.data.border_width ?? DEFAULT_SETTINGS.borderWidth,
            shadowEnabled: data.data.shadow_enabled === true || data.data.shadow_enabled === '1',
            
            // Marges
            marginTop: data.data.margin_top ?? DEFAULT_SETTINGS.marginTop,
            marginRight: data.data.margin_right ?? DEFAULT_SETTINGS.marginRight,
            marginBottom: data.data.margin_bottom ?? DEFAULT_SETTINGS.marginBottom,
            marginLeft: data.data.margin_left ?? DEFAULT_SETTINGS.marginLeft,
            showMargins: data.data.show_margins === true || data.data.show_margins === '1',
            
            // Grille
            gridShow: data.data.show_grid !== false,
            gridSize: data.data.grid_size ?? DEFAULT_SETTINGS.gridSize,
            
            // Autres paramètres...
            isLoading: false,
            isReady: true
          };
          
          setSettings(newSettings);
          console.log('CanvasSettingsContext - new settings applied:', newSettings.borderColor, newSettings.borderWidth);
        }
      }
    } catch (error) {
      console.warn('Erreur lors du rechargement des paramètres canvas:', error);
      // Fallback: recharger depuis la variable globale
      setSettings(loadSettingsFromWindowObj());
    }
  };

  // Écouter les événements custom pour les mises à jour
  useEffect(() => {
    console.log('CanvasSettingsContext - Adding event listener for pdfBuilderCanvasSettingsUpdated');
    const handleCustomUpdate = () => {
      console.log('CanvasSettingsContext - Event pdfBuilderCanvasSettingsUpdated received');
      handleRefresh();
    };
    
    window.addEventListener('pdfBuilderCanvasSettingsUpdated', handleCustomUpdate);

    return () => {
      window.removeEventListener('pdfBuilderCanvasSettingsUpdated', handleCustomUpdate);
    };
  }, []);

  // Ajouter la fonction refreshSettings au contexte final
  const contextValue: CanvasSettingsContextType = {
    ...settings,
    refreshSettings: handleRefresh
  };

  return (
    <CanvasSettingsContext.Provider value={contextValue}>
      {children}
    </CanvasSettingsContext.Provider>
  );
}

export function useCanvasSettings(): CanvasSettingsContextType {
  const context = useContext(CanvasSettingsContext);
  if (context === undefined) {
    throw new Error('useCanvasSettings must be used within CanvasSettingsProvider');
  }
  return context;
}
