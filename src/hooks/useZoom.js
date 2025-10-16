import { useState, useCallback } from '@wordpress/element';

export const useZoom = ({
  initialZoom = 1,
  minZoom = 0.1,
  maxZoom = 3,
  zoomStep = 0.1
}) => {
  const [zoom, setZoom] = useState(initialZoom);
  const [zoomOrigin, setZoomOrigin] = useState({ x: 0, y: 0 });

  const zoomIn = useCallback(() => {
    setZoom(prevZoom => Math.min(prevZoom + zoomStep, maxZoom));
  }, [maxZoom, zoomStep]);

  const zoomOut = useCallback(() => {
    setZoom(prevZoom => Math.max(prevZoom - zoomStep, minZoom));
  }, [minZoom, zoomStep]);

  const setZoomLevel = useCallback((newZoom) => {
    const clampedZoom = Math.max(minZoom, Math.min(maxZoom, newZoom));
    setZoom(clampedZoom);
  }, [minZoom, maxZoom]);

  const resetZoom = useCallback(() => {
    setZoom(initialZoom);
  }, [initialZoom]);

  const zoomToFit = useCallback((canvasWidth, canvasHeight, containerWidth, containerHeight) => {
    const scaleX = containerWidth / canvasWidth;
    const scaleY = containerHeight / canvasHeight;
    const newZoom = Math.min(scaleX, scaleY, maxZoom);

    setZoom(Math.max(newZoom, minZoom));
  }, [minZoom, maxZoom]);

  const zoomToPoint = useCallback((pointX, pointY, zoomFactor) => {
    const newZoom = Math.max(minZoom, Math.min(maxZoom, zoom * zoomFactor));
    setZoomOrigin({ x: pointX, y: pointY });
    setZoom(newZoom);
  }, [zoom, minZoom, maxZoom]);

  const zoomToSelection = useCallback((selectedElements, canvasWidth, canvasHeight, containerWidth, containerHeight) => {
    if (!selectedElements || selectedElements.length === 0) return;

    // Calculer le rectangle englobant de tous les éléments sélectionnés
    let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;

    selectedElements.forEach(element => {
      minX = Math.min(minX, element.x);
      minY = Math.min(minY, element.y);
      maxX = Math.max(maxX, element.x + element.width);
      maxY = Math.max(maxY, element.y + element.height);
    });

    const selectionWidth = maxX - minX;
    const selectionHeight = maxY - minY;

    // Ajouter une marge de 20% autour de la sélection
    const margin = 0.2;
    const paddedWidth = selectionWidth * (1 + margin);
    const paddedHeight = selectionHeight * (1 + margin);

    // Calculer le zoom nécessaire pour faire tenir la sélection dans le conteneur
    const scaleX = containerWidth / paddedWidth;
    const scaleY = containerHeight / paddedHeight;
    const newZoom = Math.min(scaleX, scaleY, maxZoom);

    // S'assurer que le zoom est dans les limites
    const clampedZoom = Math.max(minZoom, Math.min(maxZoom, newZoom));

    // Calculer le centre de la sélection
    const selectionCenterX = minX + selectionWidth / 2;
    const selectionCenterY = minY + selectionHeight / 2;

    // Calculer le décalage pour centrer la sélection dans le conteneur
    const containerCenterX = containerWidth / 2;
    const containerCenterY = containerHeight / 2;

    // Le décalage nécessaire pour centrer la sélection
    const offsetX = containerCenterX - (selectionCenterX * clampedZoom);
    const offsetY = containerCenterY - (selectionCenterY * clampedZoom);

    setZoom(clampedZoom);
    setZoomOrigin({ x: offsetX, y: offsetY });
  }, [minZoom, maxZoom]);

  const getTransformStyle = useCallback(() => {
    return {
      transform: `scale(${zoom})`,
      transformOrigin: `${zoomOrigin.x}px ${zoomOrigin.y}px`
    };
  }, [zoom, zoomOrigin]);

  const screenToCanvas = useCallback((screenX, screenY) => {
    return {
      x: (screenX - zoomOrigin.x) / zoom,
      y: (screenY - zoomOrigin.y) / zoom
    };
  }, [zoom, zoomOrigin]);

  const canvasToScreen = useCallback((canvasX, canvasY) => {
    return {
      x: canvasX * zoom + zoomOrigin.x,
      y: canvasY * zoom + zoomOrigin.y
    };
  }, [zoom, zoomOrigin]);

  return {
    zoom,
    zoomOrigin,
    zoomIn,
    zoomOut,
    setZoomLevel,
    resetZoom,
    zoomToFit,
    zoomToPoint,
    zoomToSelection,
    getTransformStyle,
    screenToCanvas,
    canvasToScreen
  };
};
