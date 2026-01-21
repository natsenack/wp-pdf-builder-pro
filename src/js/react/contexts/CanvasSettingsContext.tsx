import {
  createContext,
  useContext,
  useEffect,
  useState,
  ReactNode,
  useRef,
} from "react";
import { debugError } from "../utils/debug";

declare global {
  interface Window {
    pdfBuilderCanvasSettings?: any;
    pdfBuilderAjax?: {
      nonce: string;
      url: string;
      ajax_url: string;
    };
    pdfBuilderData?: {
      nonce: string;
      ajaxUrl: string;
      templateId?: string | number;
      existingTemplate?: any;
      hasExistingData?: boolean;
    };
  }
}

export interface CanvasSettingsContextType {
  // Dimensions
  canvasWidth: number;
  canvasHeight: number;
  canvasUnit: "px" | "mm" | "cm" | "in";
  canvasOrientation: "portrait" | "landscape";

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
  selectionDragEnabled: boolean;
  selectionMultiSelectEnabled: boolean;
  selectionRotationEnabled: boolean;
  selectionCopyPasteEnabled: boolean;
  selectionShowHandles: boolean;
  selectionHandleSize: number;
  selectionHandleColor: string;
  canvasSelectionMode: "click" | "lasso" | "rectangle";

  // Export
  exportQuality: number;
  exportFormat: "pdf" | "png" | "jpg";
  exportCompression: boolean;
  exportIncludeMetadata: boolean;

  // Historique
  historyUndoLevels: number;
  historyRedoLevels: number;

  // Performance & Lazy Loading
  lazyLoadingEditor: boolean;
  lazyLoadingPlugin: boolean;
  debugMode: boolean;
  memoryLimitJs: number;

  isLoading: boolean;
  isReady: boolean;
  error: string | null;

  // Fonctions pour mettre à jour les paramètres
  updateGridSettings: (
    settings: Partial<{
      gridShow: boolean;
      gridSize: number;
      gridSnapEnabled: boolean;
    }>
  ) => void;
  saveGridSettings: (
    settings: Partial<{
      gridShow: boolean;
      gridSize: number;
      gridSnapEnabled: boolean;
    }>
  ) => Promise<void>;
  updateRotationSettings: (enabled: boolean) => void;
  saveRotationSettings: (enabled: boolean) => Promise<void>;
  refreshSettings: () => void;
}

const CanvasSettingsContext = createContext<
  CanvasSettingsContextType | undefined
>(undefined);

// Valeurs par défaut
const DEFAULT_SETTINGS: Omit<
  CanvasSettingsContextType,
  "updateGridSettings" | "saveGridSettings"
> = {
  canvasWidth: 210,
  canvasHeight: 297,
  canvasUnit: "mm",
  canvasOrientation: "portrait",

  canvasBackgroundColor: "#ffffff",
  containerBackgroundColor: "#f8f9fa",
  borderColor: "#cccccc",
  borderWidth: 1,
  shadowEnabled: false,

  marginTop: 20,
  marginRight: 20,
  marginBottom: 20,
  marginLeft: 20,
  showMargins: false,

  gridShow: true,
  gridSize: 10,
  gridColor: "#e5e7eb",
  gridSnapEnabled: true,
  gridSnapTolerance: 8,
  guidesEnabled: true,

  // 🔍 Zoom & Navigation
  navigationEnabled: true,
  zoomDefault: 100,
  zoomMin: 10,
  zoomMax: 500,
  zoomStep: 25,

  selectionDragEnabled: true,
  selectionMultiSelectEnabled: true,
  selectionRotationEnabled: false,
  selectionCopyPasteEnabled: true,
  selectionShowHandles: true,
  selectionHandleSize: 8,
  selectionHandleColor: "#0066cc",
  canvasSelectionMode: "click",

  exportQuality: 100,
  exportFormat: "pdf",
  exportCompression: true,
  exportIncludeMetadata: true,

  historyUndoLevels: 50,
  historyRedoLevels: 50,

  // Performance & Lazy Loading
  lazyLoadingEditor: true,
  lazyLoadingPlugin: true,
  debugMode: false,
  memoryLimitJs: 256,

  isLoading: true,
  isReady: false,
  error: null,
  refreshSettings: () => {},
};

// Export des valeurs par défaut pour utilisation dans les composants
export { DEFAULT_SETTINGS };

interface CanvasSettingsProviderProps {
  children: ReactNode;
}

function loadSettingsFromWindowObj(): CanvasSettingsContextType {
  try {
    const windowSettings =
      window.pdfBuilderData?.canvasSettings || 
      window.pdfBuilderSettings?.canvas || 
      window.pdfBuilderCanvasSettings;

    // Debug: Check if window settings exist
    if (typeof window !== "undefined") {
      // Uncomment for debugging: console.log('[CanvasSettings] Selected windowSettings:', windowSettings);
    }

    if (!windowSettings) {
      
      return {
        ...DEFAULT_SETTINGS,
        isLoading: false,
        isReady: true,
        updateGridSettings: () => {},
        saveGridSettings: async () => {},
      };
    }

    // Vérifier que windowSettings est un objet
    if (typeof windowSettings !== "object" || windowSettings === null) {
      
      throw new Error("Les paramètres du canvas ne sont pas un objet valide");
    }

    // Mapper les paramètres depuis le format WordPress vers notre format
    const newSettings: CanvasSettingsContextType = {
      // Dimensions
      canvasWidth:
        (windowSettings.default_canvas_width as number) ??
        DEFAULT_SETTINGS.canvasWidth,
      canvasHeight:
        (windowSettings.default_canvas_height as number) ??
        DEFAULT_SETTINGS.canvasHeight,
      canvasUnit:
        (windowSettings.default_canvas_unit as "px" | "mm" | "cm" | "in") ??
        DEFAULT_SETTINGS.canvasUnit,
      canvasOrientation:
        (windowSettings.default_canvas_orientation as
          | "portrait"
          | "landscape") ?? DEFAULT_SETTINGS.canvasOrientation,

      // Couleurs
      canvasBackgroundColor:
        (windowSettings.canvas_background_color as string) ??
        DEFAULT_SETTINGS.canvasBackgroundColor,
      containerBackgroundColor:
        (windowSettings.container_background_color as string) ??
        DEFAULT_SETTINGS.containerBackgroundColor,
      borderColor:
        (windowSettings.border_color as string) ?? DEFAULT_SETTINGS.borderColor,
      borderWidth:
        (windowSettings.border_width as number) ?? DEFAULT_SETTINGS.borderWidth,
      shadowEnabled:
        (windowSettings.shadow_enabled as boolean) === true ||
        (windowSettings.shadow_enabled as string) === "1",

      // Marges
      marginTop:
        (windowSettings.margin_top as number) ?? DEFAULT_SETTINGS.marginTop,
      marginRight:
        (windowSettings.margin_right as number) ?? DEFAULT_SETTINGS.marginRight,
      marginBottom:
        (windowSettings.margin_bottom as number) ??
        DEFAULT_SETTINGS.marginBottom,
      marginLeft:
        (windowSettings.margin_left as number) ?? DEFAULT_SETTINGS.marginLeft,
      showMargins:
        (windowSettings.show_margins as boolean) === true ||
        (windowSettings.show_margins as string) === "1",

      // Grille
      gridShow:
        windowSettings.show_grid === true || windowSettings.show_grid === "1",
      gridSize:
        windowSettings.show_grid === true || windowSettings.show_grid === "1"
          ? (windowSettings.grid_size as number) ?? DEFAULT_SETTINGS.gridSize
          : 0, // Désactiver gridSize si gridShow est false
      gridColor:
        (windowSettings.grid_color as string) ?? DEFAULT_SETTINGS.gridColor,
      gridSnapEnabled:
        (windowSettings.show_grid === true ||
          windowSettings.show_grid === "1") &&
        (windowSettings.snap_to_grid === true ||
          windowSettings.snap_to_grid === "1"),
      gridSnapTolerance:
        (windowSettings.snap_tolerance as number) ??
        DEFAULT_SETTINGS.gridSnapTolerance,
      guidesEnabled:
        windowSettings.show_guides === true ||
        windowSettings.show_guides === "1",

      // 🔍 Zoom & Navigation
      navigationEnabled:
        windowSettings.pan_with_mouse === true ||
        windowSettings.pan_with_mouse === "1",
      zoomDefault: (() => {
        const minZoom = Math.max(
          1,
          (windowSettings.min_zoom as number) ?? DEFAULT_SETTINGS.zoomMin
        );
        const maxZoom = Math.max(
          minZoom,
          (windowSettings.max_zoom as number) ?? DEFAULT_SETTINGS.zoomMax
        );
        const defaultZoom =
          (windowSettings.default_zoom as number) ??
          DEFAULT_SETTINGS.zoomDefault;
        return Math.max(minZoom, Math.min(maxZoom, defaultZoom));
      })(),
      zoomMin: Math.max(
        1,
        (windowSettings.min_zoom as number) ?? DEFAULT_SETTINGS.zoomMin
      ),
      zoomMax: (() => {
        const minZoom = Math.max(
          1,
          (windowSettings.min_zoom as number) ?? DEFAULT_SETTINGS.zoomMin
        );
        const maxZoom =
          (windowSettings.max_zoom as number) ?? DEFAULT_SETTINGS.zoomMax;
        return Math.max(minZoom, maxZoom);
      })(),
      zoomStep: Math.max(
        1,
        (windowSettings.zoom_step as number) ?? DEFAULT_SETTINGS.zoomStep
      ),

      // Sélection
      selectionDragEnabled: true, // Default to enabled
      selectionMultiSelectEnabled:
        windowSettings.multi_select === true ||
        windowSettings.multi_select === "1",
      selectionRotationEnabled:
        windowSettings.enable_rotation === true ||
        windowSettings.enable_rotation === "1",

      // Debug: Log rotation setting in React context
      _debug_rotation_react: (() => {
        console.error('🔥 [REACT CONTEXT] windowSettings object:', windowSettings);
        console.error('🔥 [REACT CONTEXT] windowSettings.enable_rotation:', windowSettings.enable_rotation, 'type:', typeof windowSettings.enable_rotation);
        console.error('🔥 [REACT CONTEXT] selectionRotationEnabled:', windowSettings.enable_rotation === true || windowSettings.enable_rotation === "1");
        return null;
      })(),
      selectionCopyPasteEnabled:
        windowSettings.copy_paste_enabled === true ||
        windowSettings.copy_paste_enabled === "1",
      selectionShowHandles:
        windowSettings.show_resize_handles === true ||
        windowSettings.show_resize_handles === "1",
      selectionHandleSize:
        (windowSettings.handle_size as number) ??
        DEFAULT_SETTINGS.selectionHandleSize,
      selectionHandleColor:
        (windowSettings.handle_color as string) ??
        DEFAULT_SETTINGS.selectionHandleColor,
      canvasSelectionMode:
        (windowSettings.canvas_selection_mode as
          | "click"
          | "lasso"
          | "rectangle") ?? DEFAULT_SETTINGS.canvasSelectionMode,

      // Export
      exportQuality:
        (windowSettings.image_quality as number) ??
        DEFAULT_SETTINGS.exportQuality,
      exportFormat:
        (windowSettings.export_format as "pdf" | "png" | "jpg") ??
        DEFAULT_SETTINGS.exportFormat,
      exportCompression:
        windowSettings.compress_images === true ||
        windowSettings.compress_images === "1",
      exportIncludeMetadata:
        windowSettings.include_metadata === true ||
        windowSettings.include_metadata === "1",

      // Historique
      historyUndoLevels:
        (windowSettings.undo_levels as number) ??
        DEFAULT_SETTINGS.historyUndoLevels,
      historyRedoLevels:
        (windowSettings.redo_levels as number) ??
        DEFAULT_SETTINGS.historyRedoLevels,
      // Performance & Lazy Loading
      lazyLoadingEditor: false, // Default to disabled
      lazyLoadingPlugin: false, // Default to disabled
      debugMode:
        windowSettings.debug_mode === true || windowSettings.debug_mode === "1",
      memoryLimitJs: DEFAULT_SETTINGS.memoryLimitJs, // Use default

      isLoading: false,
      isReady: true,
      error: null,
      refreshSettings: () => {},
      updateGridSettings: () => {},
      saveGridSettings: async () => {},
    };

    return newSettings;
  } catch (_err) {
    const errorMsg = _err instanceof Error ? _err.message : "Erreur inconnue";
    

    return {
      ...DEFAULT_SETTINGS,
      isLoading: false,
      isReady: false,
      error: errorMsg,
      refreshSettings: () => {},
      updateGridSettings: () => {},
      saveGridSettings: async () => {},
    };
  }
}

export function CanvasSettingsProvider({
  children,
}: CanvasSettingsProviderProps) {
  const [settings, setSettings] = useState<CanvasSettingsContextType>(() => {
    // Try to load from window object first
    const windowSettings = loadSettingsFromWindowObj();

    return windowSettings;
  });

  // ✅ CORRECTION: Flag pour éviter les boucles infinies lors des mises à jour d'événements
  const isUpdatingFromEventRef = useRef(false);
  const hasInitializedRef = useRef(false);

  // Function to refresh settings from window object
  const handleRefresh = () => {
    const windowSettings = loadSettingsFromWindowObj();
    setSettings(windowSettings);
  };

  // Load settings from server on mount - simplified - ONLY ONCE
  useEffect(() => {
    if (hasInitializedRef.current) return; // Éviter les doublons au montage
    hasInitializedRef.current = true;

    // For now, just use window settings - AJAX calls can be added later if needed
    const windowSettings = loadSettingsFromWindowObj();
    setSettings(windowSettings);
  }, []);

  // Listen for settings update events
  useEffect(() => {
    const handleSettingsUpdate = () => {
      if (isUpdatingFromEventRef.current) return; // Éviter les boucles infinies

      isUpdatingFromEventRef.current = true;

      const windowSettings = loadSettingsFromWindowObj();
      setSettings(windowSettings);

      // Reset flag after a short delay
      setTimeout(() => {
        isUpdatingFromEventRef.current = false;
      }, 100);
    };
    window.addEventListener(
      "pdfBuilderCanvasSettingsUpdated",
      handleSettingsUpdate,
      { passive: true }
    );
    return () =>
      window.removeEventListener(
        "pdfBuilderCanvasSettingsUpdated",
        handleSettingsUpdate
      );
  }, []);

  // Synchronisation automatique : si gridShow est désactivé, désactiver gridSnapEnabled et gridSize
  // Commenté pour éviter les boucles infinies de rendu
  // useEffect(() => {
  //   if (!settings.gridShow) {
  //     let needsUpdate = false;
  //     const updates: Partial<CanvasSettingsContextType> = {};

  //     if (settings.gridSnapEnabled) {
  //       updates.gridSnapEnabled = false;
  //       needsUpdate = true;
  //     }

  //     if (settings.gridSize !== 0) {
  //       updates.gridSize = 0;
  //       needsUpdate = true;
  //     }

  //     if (needsUpdate) {
  //       setSettings(prev => ({
  //         ...prev,
  //         ...updates
  //       }));
  //     }
  //   }
  // }, [settings.gridShow, settings.gridSnapEnabled, settings.gridSize]);

  // Ajouter la fonction refreshSettings au contexte final
  const contextValue: CanvasSettingsContextType = {
    ...settings,
    updateGridSettings: (
      newSettings: Partial<{
        gridShow: boolean;
        gridSize: number;
        gridSnapEnabled: boolean;
      }>
    ) => {
      setSettings((prev) => ({ ...prev, ...newSettings }));
    },

    saveGridSettings: async (
      newSettings: Partial<{
        gridShow: boolean;
        gridSize: number;
        gridSnapEnabled: boolean;
      }>
    ) => {
      try {
        // Appliquer la synchronisation automatique
        const syncedSettings = { ...newSettings };
        if (newSettings.gridShow === false) {
          syncedSettings.gridSize = 0;
          syncedSettings.gridSnapEnabled = false;
        }

        // Préparer les données pour l'AJAX
        const formData = new URLSearchParams();
        formData.append("action", "pdf_builder_save_canvas_settings");
        formData.append("nonce", window.pdfBuilderAjax?.nonce || "");

        if (syncedSettings.gridShow !== undefined) {
          formData.append(
            "canvas_grid_enabled",
            syncedSettings.gridShow ? "1" : "0"
          );
        }
        if (syncedSettings.gridSize !== undefined) {
          formData.append(
            "canvas_grid_size",
            syncedSettings.gridSize.toString()
          );
        }
        if (syncedSettings.gridSnapEnabled !== undefined) {
          formData.append(
            "canvas_snap_to_grid",
            syncedSettings.gridSnapEnabled ? "1" : "0"
          );
        }

        // Sauvegarder côté serveur
        const response = await fetch(
          window.pdfBuilderAjax?.ajax_url || "/wp-admin/admin-ajax.php",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: formData,
          }
        );

        if (response.ok) {
          const result = await response.json();
          if (result.success) {
            // Mettre à jour l'état local
            setSettings((prev) => ({ ...prev, ...syncedSettings }));
          } else {
            debugError(
              "Erreur lors de la sauvegarde des paramètres de grille:",
              result.message
            );
          }
        } else {
          debugError(
            "Erreur HTTP lors de la sauvegarde des paramètres de grille"
          );
        }
      } catch (error) {
        debugError(
          "Erreur lors de la sauvegarde des paramètres de grille:",
          error
        );
      }
    },
    updateRotationSettings: (enabled: boolean) => {
      setSettings((prev) => ({
        ...prev,
        selectionRotationEnabled: enabled,
      }));
    },
    saveRotationSettings: async (enabled: boolean) => {
      try {
        // Préparer les données pour l'AJAX
        const formData = new URLSearchParams();
        formData.append("action", "pdf_builder_save_canvas_settings");
        formData.append("nonce", window.pdfBuilderAjax?.nonce || "");
        formData.append("canvas_rotate_enabled", enabled ? "1" : "0");

        // Sauvegarder côté serveur
        const response = await fetch(
          window.pdfBuilderAjax?.ajax_url || "/wp-admin/admin-ajax.php",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: formData,
          }
        );

        if (response.ok) {
          const result = await response.json();
          if (result.success) {
            // Mettre à jour l'état local
            setSettings((prev) => ({
              ...prev,
              selectionRotationEnabled: enabled,
            }));
          } else {
            debugError(
              "Erreur lors de la sauvegarde des paramètres de rotation:",
              result.message
            );
          }
        } else {
          debugError(
            "Erreur HTTP lors de la sauvegarde des paramètres de rotation"
          );
        }
      } catch (error) {
        debugError(
          "Erreur lors de la sauvegarde des paramètres de rotation:",
          error
        );
      }
    },
    refreshSettings: handleRefresh,
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
    throw new Error(
      "useCanvasSettings must be used within CanvasSettingsProvider"
    );
  }
  return context;
}


