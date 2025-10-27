import { useContext } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';

export function useTemplate() {
  const { state, dispatch } = useBuilder();

  const saveTemplate = async () => {
    dispatch({ type: 'SET_TEMPLATE_SAVING', payload: true });

    try {
      // Simulation d'une sauvegarde (à remplacer par l'appel API réel)
      await new Promise(resolve => setTimeout(resolve, 1000));

      dispatch({
        type: 'SAVE_TEMPLATE',
        payload: {
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
    saveTemplate,
    previewTemplate,
    newTemplate,
    setTemplateModified
  };
}