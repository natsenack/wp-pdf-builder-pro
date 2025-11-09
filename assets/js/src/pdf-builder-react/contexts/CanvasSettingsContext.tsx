import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react';

declare global {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  interface Window {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    pdfBuilderCanvasSettings?: Record<string, any>;
  }
}

export interface CanvasSettingsContextType {
  // Dimensions
  canvasWidth: number;
  canvasHeight: number;
  canvasUnit: 'px' | 'mm' | 'cm' | 'in';
  canvasOrientation: 'portrait' | 'landscape';
  
  // Marges
  marginTop: number;
  marginRight: number;
  marginBottom: number;
  marginLeft: number;
  
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
  
  marginTop: 20,
  marginRight: 20,
  marginBottom: 20,
  marginLeft: 20,
  
  gridShow: true,
  gridSize: 10,
  gridColor: '#e5e7eb',
  gridSnapEnabled: true,
  gridSnapTolerance: 8,
  
  zoomDefault: 1,
  zoomMin: 0.1,
  zoomMax: 5,
  zoomStep: 0.1,
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
      console.warn('Canvas settings not found in window object, using defaults');
      return {
        ...DEFAULT_SETTINGS,
        isLoading: false,
        isReady: true
      };
    }

    console.log('Loading canvas settings from window', windowSettings);

    // Mapper les paramètres depuis le format WordPress vers notre format
    const newSettings: CanvasSettingsContextType = {
      // Dimensions
      canvasWidth: (windowSettings.canvas_width as number) ?? DEFAULT_SETTINGS.canvasWidth,
      canvasHeight: (windowSettings.canvas_height as number) ?? DEFAULT_SETTINGS.canvasHeight,
      canvasUnit: (windowSettings.canvas_unit as 'px' | 'mm' | 'cm' | 'in') ?? DEFAULT_SETTINGS.canvasUnit,
      canvasOrientation: (windowSettings.canvas_orientation as 'portrait' | 'landscape') ?? DEFAULT_SETTINGS.canvasOrientation,
      
      // Marges
      marginTop: (windowSettings.margin_top as number) ?? DEFAULT_SETTINGS.marginTop,
      marginRight: (windowSettings.margin_right as number) ?? DEFAULT_SETTINGS.marginRight,
      marginBottom: (windowSettings.margin_bottom as number) ?? DEFAULT_SETTINGS.marginBottom,
      marginLeft: (windowSettings.margin_left as number) ?? DEFAULT_SETTINGS.marginLeft,
      
      // Grille
      gridShow: (windowSettings.grid_show as boolean) !== false,
      gridSize: (windowSettings.grid_size as number) ?? DEFAULT_SETTINGS.gridSize,
      gridColor: (windowSettings.grid_color as string) ?? DEFAULT_SETTINGS.gridColor,
      gridSnapEnabled: (windowSettings.grid_snap_enabled as boolean) !== false,
      gridSnapTolerance: (windowSettings.grid_snap_tolerance as number) ?? DEFAULT_SETTINGS.gridSnapTolerance,
      
      // Zoom
      zoomDefault: (windowSettings.zoom_default as number) ?? DEFAULT_SETTINGS.zoomDefault,
      zoomMin: (windowSettings.zoom_min as number) ?? DEFAULT_SETTINGS.zoomMin,
      zoomMax: (windowSettings.zoom_max as number) ?? DEFAULT_SETTINGS.zoomMax,
      zoomStep: (windowSettings.zoom_step as number) ?? DEFAULT_SETTINGS.zoomStep,
      zoomWheelSupport: (windowSettings.zoom_wheel_support as boolean) !== false,
      
      // Sélection
      selectionMultiSelectEnabled: (windowSettings.selection_multi_select_enabled as boolean) !== false,
      selectionRotationEnabled: (windowSettings.selection_rotation_enabled as boolean) !== false,
      selectionCopyPasteEnabled: (windowSettings.selection_copy_paste_enabled as boolean) !== false,
      selectionShowHandles: (windowSettings.selection_show_handles as boolean) !== false,
      selectionHandleSize: (windowSettings.selection_handle_size as number) ?? DEFAULT_SETTINGS.selectionHandleSize,
      selectionHandleColor: (windowSettings.selection_handle_color as string) ?? DEFAULT_SETTINGS.selectionHandleColor,
      
      // Export
      exportQuality: (windowSettings.export_quality as number) ?? DEFAULT_SETTINGS.exportQuality,
      exportFormat: (windowSettings.export_format as 'pdf' | 'png' | 'jpg') ?? DEFAULT_SETTINGS.exportFormat,
      exportCompression: (windowSettings.export_compression as boolean) !== false,
      exportIncludeMetadata: (windowSettings.export_include_metadata as boolean) !== false,
      
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
    console.warn('Error loading canvas settings:', errorMsg);
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
  const [settings, setSettings] = useState<CanvasSettingsContextType>(() => loadSettingsFromWindowObj());

  const handleRefresh = () => {
    setSettings(loadSettingsFromWindowObj());
  };

  // Écouter les événements custom pour les mises à jour
  useEffect(() => {
    const handleCustomUpdate = () => {
      console.log('Canvas settings update detected, refreshing...');
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
