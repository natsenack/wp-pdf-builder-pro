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
      // Simulation du chargement d'un template existant
      // À remplacer par un vrai appel API
      console.log('Chargement du template:', templateId);

      // Simulation d'un délai de chargement
      await new Promise(resolve => setTimeout(resolve, 500));

      // Données fictives du template (à remplacer par les vraies données)
      const templateData = {
        id: templateId,
        name: `Template ${templateId}`,
        elements: [
          // Éléments du template chargés depuis la DB
        ],
        lastSaved: new Date()
      };

      dispatch({
        type: 'LOAD_TEMPLATE',
        payload: {
          id: templateId,
          name: templateData.name,
          elements: templateData.elements,
          lastSaved: templateData.lastSaved
        }
      });

    } catch (error) {
      console.error('Erreur lors du chargement du template:', error);
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