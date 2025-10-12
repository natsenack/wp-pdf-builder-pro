import { useState, useCallback } from 'react';

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
    getTransformStyle,
    screenToCanvas,
    canvasToScreen
  };
};