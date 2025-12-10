import { useState, useEffect, useCallback, useRef } from 'react';
import { PDFBuilderState, PDFElement, PDFTemplate, AjaxResponse } from '@/shared';

/**
 * Hook pour gérer l'état du builder PDF
 */
export const usePDFBuilder = (initialTemplate?: PDFTemplate) => {
  const [state, setState] = useState<PDFBuilderState>({
    currentTemplate: initialTemplate || null,
    elements: [],
    selectedElement: null,
    settings: {
      autoSave: true,
      autoSaveInterval: 30000, // 30 secondes
      previewQuality: 'medium',
      gridSize: 10,
      snapToGrid: true,
      showRulers: true,
      theme: 'light',
    },
    isLoading: false,
    hasUnsavedChanges: false,
    history: {
      past: [],
      present: {
        currentTemplate: initialTemplate || null,
        elements: [],
        selectedElement: null,
        settings: {
          autoSave: true,
          autoSaveInterval: 30000,
          previewQuality: 'medium',
          gridSize: 10,
          snapToGrid: true,
          showRulers: true,
          theme: 'light',
        },
        isLoading: false,
        hasUnsavedChanges: false,
      } as PDFBuilderState,
      future: [],
    },
  });

  const autoSaveTimeoutRef = useRef<NodeJS.Timeout>();

  // Auto-sauvegarde
  useEffect(() => {
    if (state.settings.autoSave && state.hasUnsavedChanges) {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current);
      }

      autoSaveTimeoutRef.current = setTimeout(async () => {
        await saveTemplate();
      }, state.settings.autoSaveInterval);
    }

    return () => {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current);
      }
    };
  }, [state.hasUnsavedChanges, state.settings.autoSave, state.settings.autoSaveInterval]);

  // Sauvegarde du template
  const saveTemplate = useCallback(async (): Promise<boolean> => {
    if (!state.currentTemplate) return false;

    try {
      setState(prev => ({ ...prev, isLoading: true }));

      const response: AjaxResponse<{ success: boolean }> = await fetch(window.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'pdf_builder_save_template',
          template_id: state.currentTemplate!.id,
          template_data: JSON.stringify({
            ...state.currentTemplate,
            elements: state.elements,
          }),
          nonce: window.pdfBuilderPro.nonce,
        }),
      }).then(res => res.json());

      if (response.success) {
        setState(prev => ({ ...prev, hasUnsavedChanges: false }));
        return true;
      } else {
        console.error('Erreur lors de la sauvegarde:', response.data?.message);
        return false;
      }
    } catch (error) {
      console.error('Erreur lors de la sauvegarde:', error);
      return false;
    } finally {
      setState(prev => ({ ...prev, isLoading: false }));
    }
  }, [state.currentTemplate, state.elements]);

  // Ajouter un élément
  const addElement = useCallback((element: Omit<PDFElement, 'id'>) => {
    const newElement: PDFElement = {
      ...element,
      id: `element_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
    };

    setState(prev => ({
      ...prev,
      elements: [...prev.elements, newElement],
      selectedElement: newElement.id,
      hasUnsavedChanges: true,
      history: {
        past: [...prev.history.past, prev.history.present],
        present: {
          ...prev,
          elements: [...prev.elements, newElement],
          selectedElement: newElement.id,
          hasUnsavedChanges: true,
        },
        future: [],
      },
    }));
  }, []);

  // Supprimer un élément
  const removeElement = useCallback((elementId: string) => {
    setState(prev => ({
      ...prev,
      elements: prev.elements.filter(el => el.id !== elementId),
      selectedElement: prev.selectedElement === elementId ? null : prev.selectedElement,
      hasUnsavedChanges: true,
      history: {
        past: [...prev.history.past, prev.history.present],
        present: {
          ...prev,
          elements: prev.elements.filter(el => el.id !== elementId),
          selectedElement: prev.selectedElement === elementId ? null : prev.selectedElement,
          hasUnsavedChanges: true,
        },
        future: [],
      },
    }));
  }, []);

  // Mettre à jour un élément
  const updateElement = useCallback((elementId: string, updates: Partial<PDFElement>) => {
    setState(prev => ({
      ...prev,
      elements: prev.elements.map(el =>
        el.id === elementId ? { ...el, ...updates } : el
      ),
      hasUnsavedChanges: true,
      history: {
        past: [...prev.history.past, prev.history.present],
        present: {
          ...prev,
          elements: prev.elements.map(el =>
            el.id === elementId ? { ...el, ...updates } : el
          ),
          hasUnsavedChanges: true,
        },
        future: [],
      },
    }));
  }, []);

  // Sélectionner un élément
  const selectElement = useCallback((elementId: string | null) => {
    setState(prev => ({ ...prev, selectedElement: elementId }));
  }, []);

  // Annuler la dernière action
  const undo = useCallback(() => {
    setState(prev => {
      if (prev.history.past.length === 0) return prev;

      const previous = prev.history.past[prev.history.past.length - 1];
      const newPast = prev.history.past.slice(0, -1);

      return {
        ...previous,
        history: {
          past: newPast,
          present: previous,
          future: [prev.history.present, ...prev.history.future],
        },
      };
    });
  }, []);

  // Rétablir la dernière action annulée
  const redo = useCallback(() => {
    setState(prev => {
      if (prev.history.future.length === 0) return prev;

      const next = prev.history.future[0];
      const newFuture = prev.history.future.slice(1);

      return {
        ...next,
        history: {
          past: [...prev.history.past, prev.history.present],
          present: next,
          future: newFuture,
        },
      };
    });
  }, []);

  // Changer le template actuel
  const setTemplate = useCallback((template: PDFTemplate) => {
    setState(prev => ({
      ...prev,
      currentTemplate: template,
      elements: [], // Reset elements when changing template
      selectedElement: null,
      hasUnsavedChanges: false,
      history: {
        past: [],
        present: {
          ...prev,
          currentTemplate: template,
          elements: [],
          selectedElement: null,
          hasUnsavedChanges: false,
        },
        future: [],
      },
    }));
  }, []);

  return {
    // État
    ...state,

    // Actions
    addElement,
    removeElement,
    updateElement,
    selectElement,
    saveTemplate,
    setTemplate,
    undo,
    redo,

    // Getters utilitaires
    canUndo: state.history.past.length > 0,
    canRedo: state.history.future.length > 0,
    selectedElementData: state.elements.find(el => el.id === state.selectedElement),
  };
};

/**
 * Hook pour gérer les templates PDF
 */
export const usePDFTemplates = () => {
  const [templates, setTemplates] = useState<PDFTemplate[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  // Charger les templates
  const loadTemplates = useCallback(async (category?: string) => {
    try {
      setIsLoading(true);
      setError(null);

      const response: AjaxResponse<PDFTemplate[]> = await fetch(window.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'pdf_builder_get_templates',
          category: category || '',
          nonce: window.pdfBuilderPro.nonce,
        }),
      }).then(res => res.json());

      if (response.success) {
        setTemplates(response.data);
      } else {
        setError(response.data?.message || 'Erreur lors du chargement des templates');
      }
    } catch (err) {
      setError('Erreur de connexion');
      console.error('Erreur lors du chargement des templates:', err);
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Créer un nouveau template
  const createTemplate = useCallback(async (templateData: Omit<PDFTemplate, 'id'>): Promise<PDFTemplate | null> => {
    try {
      setIsLoading(true);

      const response: AjaxResponse<PDFTemplate> = await fetch(window.ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          action: 'pdf_builder_create_template',
          template_data: JSON.stringify(templateData),
          nonce: window.pdfBuilderPro.nonce,
        }),
      }).then(res => res.json());

      if (response.success) {
        const newTemplate = response.data;
        setTemplates(prev => [...prev, newTemplate]);
        return newTemplate;
      } else {
        setError(response.data?.message || 'Erreur lors de la création du template');
        return null;
      }
    } catch (err) {
      setError('Erreur de connexion');
      console.error('Erreur lors de la création du template:', err);
      return null;
    } finally {
      setIsLoading(false);
    }
  }, []);

  return {
    templates,
    isLoading,
    error,
    loadTemplates,
    createTemplate,
  };
};