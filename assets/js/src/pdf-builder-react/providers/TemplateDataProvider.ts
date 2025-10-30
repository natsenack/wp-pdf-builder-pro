/**
 * TemplateDataProvider - Fournisseur de données depuis le template enregistré
 *
 * Récupère les données des éléments du template JSON pour l'aperçu temps réel
 * dans l'éditeur. Combine les données fictives pour les variables manquantes.
 *
 * Phase 3.0 - Support des données de template réel
 */

import { DataProvider } from '../renderers/PreviewRenderer';
import { Element } from '../types/elements';
import { CanvasDataProvider } from './CanvasDataProvider';

export class TemplateDataProvider implements DataProvider {
  private canvasProvider: CanvasDataProvider;
  private elements: Element[];
  private extractedVariables: Map<string, string>;

  constructor(elements: Element[]) {
    this.canvasProvider = new CanvasDataProvider();
    this.elements = elements || [];
    this.extractedVariables = new Map();
    this.extractVariablesFromTemplate();
  }

  /**
   * Extrait les variables du texte des éléments du template
   */
  private extractVariablesFromTemplate(): void {
    const regex = /\{\{([^}]+)\}\}/g;

    this.elements.forEach((element: any) => {
      // Rechercher dans le texte
      if (element.text && typeof element.text === 'string') {
        let match;
        while ((match = regex.exec(element.text)) !== null) {
          const varName = match[1].trim();
          // Essayer de récupérer la valeur depuis le DataProvider
          const value = this.canvasProvider.getVariableValue(varName);
          if (value && !value.startsWith('{{')) {
            this.extractedVariables.set(varName, value);
          }
        }
      }

      // Rechercher dans les propriétés dynamiques
      if (element.dynamic_text_variable) {
        const value = this.canvasProvider.getVariableValue(element.dynamic_text_variable);
        if (value && !value.startsWith('{{')) {
          this.extractedVariables.set(element.dynamic_text_variable, value);
        }
      }
    });
  }

  getMode(): 'canvas' {
    return 'canvas';
  }

  /**
   * Récupère la valeur d'une variable
   * Priorité : template extrait > données fictives
   */
  getVariableValue(variable: string): string {
    // Vérifier si la variable a été extraite du template
    if (this.extractedVariables.has(variable)) {
      return this.extractedVariables.get(variable) || '';
    }

    // Sinon, utiliser les données fictives du DataProvider par défaut
    return this.canvasProvider.getVariableValue(variable);
  }

  /**
   * Retourne toutes les variables extraites du template
   */
  getExtractedVariables(): Map<string, string> {
    return this.extractedVariables;
  }

  /**
   * Ajoute une variable personnalisée
   */
  setVariable(variable: string, value: string): void {
    this.extractedVariables.set(variable, value);
  }

  /**
   * Réinitialise les variables extraites (utile après mise à jour du template)
   */
  refresh(elements: Element[]): void {
    this.elements = elements || [];
    this.extractedVariables.clear();
    this.extractVariablesFromTemplate();
  }
}
