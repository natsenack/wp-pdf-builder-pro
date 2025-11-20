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
  guidesEnabled: boolean;
  
  // 🔍 Zoom & Navigation
  navigationEnabled: boolean;
  zoomDefault: number;
  zoomMin: number;
  zoomMax: number;
  zoomStep: number;
  
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
  
  // Fonctions pour mettre à jour les paramètres
  updateGridSettings: (settings: Partial<{ gridShow: boolean; gridSize: number; gridSnapEnabled: boolean }>) => void;
  saveGridSettings: (settings: Partial<{ gridShow: boolean; gridSize: number; gridSnapEnabled: boolean }>) => Promise<void>;
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
  guidesEnabled: true,
  
  // 🔍 Zoom & Navigation
  navigationEnabled: true,
  zoomDefault: 100,
  zoomMin: 10,
  zoomMax: 500,
  zoomStep: 25,
  
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
      gridShow: windowSettings.show_grid === true || windowSettings.show_grid === '1',
      gridSize: (windowSettings.show_grid === true || windowSettings.show_grid === '1') 
        ? ((windowSettings.grid_size as number) ?? DEFAULT_SETTINGS.gridSize)
        : 0, // Désactiver gridSize si gridShow est false
      gridColor: (windowSettings.grid_color as string) ?? DEFAULT_SETTINGS.gridColor,
      gridSnapEnabled: (windowSettings.show_grid === true || windowSettings.show_grid === '1') && (windowSettings.snap_to_grid === true || windowSettings.snap_to_grid === '1'),
      gridSnapTolerance: (windowSettings.snap_tolerance as number) ?? DEFAULT_SETTINGS.gridSnapTolerance,
      guidesEnabled: windowSettings.show_guides === true || windowSettings.show_guides === '1',
      
      // 🔍 Zoom & Navigation
      navigationEnabled: windowSettings.navigation_enabled === true || windowSettings.navigation_enabled === '1',
      zoomDefault: (() => {
        const minZoom = Math.max(1, (windowSettings.min_zoom as number) ?? DEFAULT_SETTINGS.zoomMin);
        const maxZoom = Math.max(minZoom, (windowSettings.max_zoom as number) ?? DEFAULT_SETTINGS.zoomMax);
        const defaultZoom = (windowSettings.default_zoom as number) ?? DEFAULT_SETTINGS.zoomDefault;
        return Math.max(minZoom, Math.min(maxZoom, defaultZoom));
      })(),
      zoomMin: Math.max(1, (windowSettings.min_zoom as number) ?? DEFAULT_SETTINGS.zoomMin),
      zoomMax: (() => {
        const minZoom = Math.max(1, (windowSettings.min_zoom as number) ?? DEFAULT_SETTINGS.zoomMin);
        const maxZoom = (windowSettings.max_zoom as number) ?? DEFAULT_SETTINGS.zoomMax;
        return Math.max(minZoom, maxZoom);
      })(),
      zoomStep: Math.max(1, (windowSettings.zoom_step as number) ?? DEFAULT_SETTINGS.zoomStep),
      
      // Sélection
      selectionMultiSelectEnabled: windowSettings.multi_select === true || windowSettings.multi_select === '1',
      selectionRotationEnabled: windowSettings.enable_rotation === true || windowSettings.enable_rotation === '1',
      selectionCopyPasteEnabled: windowSettings.copy_paste_enabled === true || windowSettings.copy_paste_enabled === '1',
      selectionShowHandles: windowSettings.show_resize_handles === true || windowSettings.show_resize_handles === '1',
      selectionHandleSize: (windowSettings.handle_size as number) ?? DEFAULT_SETTINGS.selectionHandleSize,
      selectionHandleColor: (windowSettings.handle_color as string) ?? DEFAULT_SETTINGS.selectionHandleColor,
      
      // Export
      exportQuality: (windowSettings.image_quality as number) ?? DEFAULT_SETTINGS.exportQuality,
      exportFormat: (windowSettings.export_format as 'pdf' | 'png' | 'jpg') ?? DEFAULT_SETTINGS.exportFormat,
      exportCompression: windowSettings.compress_images === true || windowSettings.compress_images === '1',
      exportIncludeMetadata: windowSettings.include_metadata === true || windowSettings.include_metadata === '1',
      
      // Historique
      historyUndoLevels: (windowSettings.history_undo_levels as number) ?? DEFAULT_SETTINGS.historyUndoLevels,
      historyRedoLevels: (windowSettings.history_redo_levels as number) ?? DEFAULT_SETTINGS.historyRedoLevels,
      historyAutoSaveEnabled: windowSettings.history_auto_save_enabled === true || windowSettings.history_auto_save_enabled === '1',
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
    handleRefresh();
  }, []);

  // Listen for settings update events
  useEffect(() => {
    const handleSettingsUpdate = () => {
      handleRefresh();
    };
    window.addEventListener('canvasSettingsUpdated', handleSettingsUpdate);
    return () => window.removeEventListener('canvasSettingsUpdated', handleSettingsUpdate);
  }, []);

  const handleRefresh = async () => {
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
        if (data.success && data.data) {
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
            gridShow: data.data.show_grid === true || data.data.show_grid === '1',
            gridSize: (data.data.show_grid === true || data.data.show_grid === '1')
              ? (data.data.grid_size ?? DEFAULT_SETTINGS.gridSize)
              : 0, // Désactiver gridSize si gridShow est false
            gridColor: data.data.grid_color ?? DEFAULT_SETTINGS.gridColor,
            gridSnapEnabled: (data.data.show_grid === true || data.data.show_grid === '1') && (data.data.snap_to_grid === true || data.data.snap_to_grid === '1'),
            gridSnapTolerance: data.data.snap_tolerance ?? DEFAULT_SETTINGS.gridSnapTolerance,
            guidesEnabled: data.data.show_guides === true || data.data.show_guides === '1',
            
            // Autres paramètres...
            isLoading: false,
            isReady: true
          };
          
          setSettings(newSettings);
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
    const handleCustomUpdate = () => {
      handleRefresh();
    };
    
    window.addEventListener('pdfBuilderCanvasSettingsUpdated', handleCustomUpdate);

    return () => {
      window.removeEventListener('pdfBuilderCanvasSettingsUpdated', handleCustomUpdate);
    };
  }, []);

  // Synchronisation automatique : si gridShow est désactivé, désactiver gridSnapEnabled et gridSize
  useEffect(() => {
    if (!settings.gridShow) {
      let needsUpdate = false;
      const updates: Partial<CanvasSettingsContextType> = {};

      if (settings.gridSnapEnabled) {
        updates.gridSnapEnabled = false;
        needsUpdate = true;
      }

      if (settings.gridSize !== 0) {
        updates.gridSize = 0;
        needsUpdate = true;
      }

      if (needsUpdate) {
        setSettings(prev => ({
          ...prev,
          ...updates
        }));
      }
    }
  }, [settings.gridShow, settings.gridSnapEnabled, settings.gridSize]);

  // Ajouter la fonction refreshSettings au contexte final
  const contextValue: CanvasSettingsContextType = {
    ...settings,
    updateGridSettings: (newSettings: Partial<{ gridShow: boolean; gridSize: number; gridSnapEnabled: boolean }>) => {
      setSettings(prev => ({ ...prev, ...newSettings }));
    },
    saveGridSettings: async (newSettings: Partial<{ gridShow: boolean; gridSize: number; gridSnapEnabled: boolean }>) => {
      try {
        // Appliquer la synchronisation automatique
        const syncedSettings = { ...newSettings };
        if (newSettings.gridShow === false) {
          syncedSettings.gridSize = 0;
          syncedSettings.gridSnapEnabled = false;
        }

        // Préparer les données pour l'AJAX
        const formData = new URLSearchParams();
        formData.append('action', 'pdf_builder_save_canvas_settings');
        formData.append('nonce', window.pdfBuilderAjax?.nonce || '');
        
        if (syncedSettings.gridShow !== undefined) {
          formData.append('canvas_grid_enabled', syncedSettings.gridShow ? '1' : '0');
        }
        if (syncedSettings.gridSize !== undefined) {
          formData.append('canvas_grid_size', syncedSettings.gridSize.toString());
        }
        if (syncedSettings.gridSnapEnabled !== undefined) {
          formData.append('canvas_snap_to_grid', syncedSettings.gridSnapEnabled ? '1' : '0');
        }

        // Sauvegarder côté serveur
        const response = await fetch(window.pdfBuilderAjax?.ajax_url || '/wp-admin/admin-ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: formData
        });

        if (response.ok) {
          const result = await response.json();
          if (result.success) {
            // Mettre à jour l'état local
            setSettings(prev => ({ ...prev, ...syncedSettings }));
          } else {
            console.error('Erreur lors de la sauvegarde des paramètres de grille:', result.message);
          }
        } else {
          console.error('Erreur HTTP lors de la sauvegarde des paramètres de grille');
        }
      } catch (error) {
        console.error('Erreur lors de la sauvegarde des paramètres de grille:', error);
      }
    },
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
