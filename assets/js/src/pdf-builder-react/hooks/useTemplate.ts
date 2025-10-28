import { useContext, useEffect } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';
import { LoadTemplatePayload } from '../types/elements';

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

      // Parse JSON strings
      let elements = [];
      let canvas = null;
      try {
        elements = templateData.elements ? JSON.parse(templateData.elements) : [];
        canvas = templateData.canvas ? JSON.parse(templateData.canvas) : null;
      } catch (parseError) {
        console.error('Erreur lors du parsing des données du template:', parseError);
        elements = [];
        canvas = null;
      }

      dispatch({
        type: 'LOAD_TEMPLATE',
        payload: {
          id: templateId,
          name: templateData.name,
          elements: elements,
          canvas: canvas,
          lastSaved: new Date(templateData.updated_at)
        } as LoadTemplatePayload
      });

      console.log('Template chargé avec succès:', templateData);
      console.log('Éléments chargés:', elements.length, 'éléments');

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

      // Préparer les données à envoyer
      const formData = new FormData();
      formData.append('action', 'pdf_builder_save_template');
      formData.append('template_id', templateId || '0');
      formData.append('template_name', state.template.name || 'Template sans nom');
      formData.append('elements', JSON.stringify(state.elements));
      formData.append('canvas', JSON.stringify(state.canvas));
      formData.append('nonce', window.pdfBuilderData.nonce);

      console.log('Sauvegarde du template:', {
        template_id: templateId || '0',
        template_name: state.template.name || 'Template sans nom',
        elements: state.elements,
        canvas: state.canvas,
        nonce: window.pdfBuilderData.nonce
      });

      // Faire un appel API pour sauvegarder le template
      const response = await fetch(window.pdfBuilderData.ajaxUrl, {
        method: 'POST',
        body: formData
      });

      if (!response.ok) {
        throw new Error(`Erreur HTTP: ${response.status}`);
      }

      const result = await response.json();

      if (!result.success) {
        throw new Error(result.data || 'Erreur lors de la sauvegarde du template');
      }

      console.log('Template sauvegardé avec succès:', result.data);

      dispatch({
        type: 'SAVE_TEMPLATE',
        payload: {
          id: result.data.id,
          name: result.data.name
        }
      });
    } catch (error) {
      console.error('Erreur lors de la sauvegarde:', error);
      throw error; // Re-throw pour que l'appelant puisse gérer l'erreur
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