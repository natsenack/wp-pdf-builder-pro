import { useCallback } from 'react';
import { useBuilder } from '../contexts/builder/BuilderContext.tsx';

// Import du service de personnalisation pour les calculs de position
// Note: Cette import pourrait nécessiter un ajustement selon l'architecture
const getCustomizationService = () => {
  // Pour l'instant, on simule le calcul de position dynamique
  return {
    getDefaultProperties: (type: string, existingElements: any[] = []) => {
      // Positions de base stratégiques
      const basePositions: { [key: string]: { x: number, y: number } } = {
        'product_table': { x: 50, y: 50 },
        'customer_info': { x: 50, y: 220 },
        'company_info': { x: 50, y: 340 },
        'document_type': { x: 50, y: 430 },
        'mentions': { x: 50, y: 480 },
        'dynamic-text': { x: 50, y: 550 },
        'company_logo': { x: 350, y: 50 },
        'order_number': { x: 350, y: 130 },
        'woocommerce-order-date': { x: 350, y: 160 },
        'woocommerce-invoice-number': { x: 350, y: 190 },
        'text': { x: 50, y: 600 },
        'text-title': { x: 50, y: 10 },
        'text-subtitle': { x: 50, y: 60 },
        'rectangle': { x: 50, y: 700 },
        'circle': { x: 150, y: 700 },
        'arrow': { x: 250, y: 700 },
        'image': { x: 400, y: 500 }
      };

      const basePosition = basePositions[type] || { x: 50, y: 50 };

      // Si pas d'éléments existants, retourner la position de base
      if (existingElements.length === 0) {
        return {
          x: basePosition.x,
          y: basePosition.y,
          width: 100,
          height: 50,
          // ... autres propriétés par défaut
        };
      }

      // Calcul dynamique similaire à pdf-canvas-customization.js
      const isLeftColumn = ['product_table', 'customer_info', 'company_info', 'document_type', 'mentions', 'dynamic-text', 'text', 'text-title', 'text-subtitle', 'rectangle'].includes(type);
      const isRightColumn = ['company_logo', 'order_number', 'woocommerce-order-date', 'woocommerce-invoice-number'].includes(type);

      let columnX = 0, columnWidth = 600;
      if (isLeftColumn) {
        columnX = 0;
        columnWidth = 300;
      } else if (isRightColumn) {
        columnX = 300;
        columnWidth = 300;
      }

      // Trouver l'espace disponible
      const columnElements = existingElements
        .filter(el => {
          const elX = el.x || 0;
          const elWidth = el.width || 100;
          return elX < columnX + columnWidth && elX + elWidth > columnX;
        })
        .sort((a, b) => (a.y || 0) - (b.y || 0));

      let currentY = 10;
      const elementHeight = 50; // Hauteur par défaut
      const spacing = 20;

      for (const element of columnElements) {
        const elementBottom = (element.y || 0) + (element.height || 50) + spacing;
        if (currentY + elementHeight <= (element.y || 0)) {
          break;
        }
        currentY = Math.max(currentY, elementBottom);
      }

      const maxY = 800;
      if (currentY + elementHeight > maxY) {
        currentY = maxY - elementHeight - 10;
      }

      return {
        x: basePosition.x,
        y: Math.max(10, currentY),
        width: 100,
        height: 50,
        // ... autres propriétés par défaut
      };
    }
  };
};

interface UseCanvasDropProps {
  canvasRef: React.RefObject<HTMLCanvasElement>;
  canvasWidth: number;
  canvasHeight: number;
  elements: any[]; // Éléments existants pour calcul dynamique des positions
}

export const useCanvasDrop = ({ canvasRef, canvasWidth, canvasHeight, elements }: UseCanvasDropProps) => {
  const { dispatch } = useBuilder();

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();

    try {
      const elementData = JSON.parse(e.dataTransfer.getData('application/json'));

      // Calculer la position relative au canvas
      const canvas = canvasRef.current;
      if (!canvas) return;

      const rect = canvas.getBoundingClientRect();
      const scaleX = canvasWidth / rect.width;
      const scaleY = canvasHeight / rect.height;

      const x = (e.clientX - rect.left) * scaleX;
      const y = (e.clientY - rect.top) * scaleY;

      // Calculer la position dynamique basée sur les éléments existants
      const customizationService = getCustomizationService();
      const dynamicProps = customizationService.getDefaultProperties(elementData.type, elements);

      // Créer un nouvel élément avec les propriétés calculées dynamiquement
      const newElement = {
        id: `element_${Date.now()}`,
        type: elementData.type,
        ...dynamicProps,
        // Fusionner avec les autres propriétés des defaultProps (sans x, y qui sont déjà calculés)
        ...Object.fromEntries(
          Object.entries(elementData.defaultProps).filter(([key]) => !['x', 'y'].includes(key))
        ),
        // Propriétés requises par BaseElement
        visible: true,
        locked: false,
        createdAt: new Date(),
        updatedAt: new Date()
      };

      // Ajouter l'élément au state
      dispatch({ type: 'ADD_ELEMENT', payload: newElement });

    } catch (error) {
      // Erreur silencieuse lors du drop
    }
  }, [canvasRef, canvasWidth, canvasHeight, elements, dispatch]);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
  }, []);

  return {
    handleDrop,
    handleDragOver
  };
};