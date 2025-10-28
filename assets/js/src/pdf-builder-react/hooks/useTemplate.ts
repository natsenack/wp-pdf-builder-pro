import { useContext, useEffect } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';

export function useTemplate() {
  const { state, dispatch } = useBuilder();

  // Détecter si on est sur un template existant via l'URL
  const getTemplateIdFromUrl = (): string | null => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('template_id');
  };

  const isEditingExistingTemplate = (): boolean => {
    return getTemplateIdFromUrl() !== null;
  };

  // Charger un template existant
  const loadExistingTemplate = async (templateId: string) => {
    try {
      console.log('Chargement du template:', templateId);

      // Faire un appel API pour récupérer les données du template
      const response = await fetch(`${window.pdfBuilderData.ajaxUrl}?action=pdf_builder_get_template&template_id=${templateId}&nonce=${window.pdfBuilderData.nonce}`);

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.data || 'Erreur lors du chargement du template');
      }

      const templateData = result.data;

      dispatch({
        type: 'LOAD_TEMPLATE',
        payload: {
          id: templateId,
          name: templateData.name,
          elements: templateData.elements || [],
          canvas: templateData.canvas || null,
          lastSaved: new Date(templateData.updated_at)
        }
      });

      console.log('Template chargé avec succès:', templateData);
      console.log('Éléments chargés:', templateData.elements?.length || 0, 'éléments');

    } catch (error) {
      console.error('Erreur lors du chargement du template:', error);
      // En cas d'erreur, on peut afficher un message d'erreur à l'utilisateur
    }
  };

  // Effet pour charger automatiquement un template existant au montage
  useEffect(() => {
    const templateId = getTemplateIdFromUrl();
    if (templateId) {
      loadExistingTemplate(templateId);
    }
  }, []);

  const saveTemplate = async () => {
    dispatch({ type: 'SET_TEMPLATE_SAVING', payload: true });

    try {
      const templateId = getTemplateIdFromUrl();

      // Simulation d'une sauvegarde (à remplacer par l'appel API réel)
      await new Promise(resolve => setTimeout(resolve, 1000));

      dispatch({
        type: 'SAVE_TEMPLATE',
        payload: {
          id: templateId || undefined, // Inclure l'ID si on modifie un template existant
          name: state.template.name || 'Template sauvegardé'
        }
      });
    } catch (error) {
      console.error('Erreur lors de la sauvegarde:', error);
    } finally {
      dispatch({ type: 'SET_TEMPLATE_SAVING', payload: false });
    }
  };

  const previewTemplate = () => {
    // Logique de prévisualisation (à implémenter)
    console.log('Prévisualisation du template');
  };

  const newTemplate = () => {
    dispatch({ type: 'NEW_TEMPLATE' });
  };

  const setTemplateModified = (modified: boolean) => {
    dispatch({ type: 'SET_TEMPLATE_MODIFIED', payload: modified });
  };

  return {
    templateName: state.template.name,
    isNewTemplate: state.template.isNew,
    isModified: state.template.isModified,
    isSaving: state.template.isSaving,
    lastSaved: state.template.lastSaved,
    isEditingExistingTemplate: isEditingExistingTemplate(),
    saveTemplate,
    previewTemplate,
    newTemplate,
    setTemplateModified
  };
}