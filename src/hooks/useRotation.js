import React from 'react';
const { useState, useCallback } = React;

export const useRotation = (onElementRotate, rotationStep = 15, rotationSnap = true) => {
  const [isRotating, setIsRotating] = useState(false);
  const [rotationStart, setRotationStart] = useState({ angle: 0, centerX: 0, centerY: 0 });

  const handleRotationStart = useCallback((e, element) => {
    e.preventDefault();
    e.stopPropagation();

    setIsRotating(true);

    const rect = e.currentTarget.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    // Calculer l'angle initial entre le centre de l'élément et la position de la souris
    const deltaX = e.clientX - centerX;
    const deltaY = e.clientY - centerY;
    const initialAngle = Math.atan2(deltaY, deltaX) * (180 / Math.PI);

    setRotationStart({
      angle: initialAngle - (element.rotation || 0),
      centerX,
      centerY
    });

    // Ajouter les écouteurs d'événements globaux
    const handleRotationMove = (moveEvent) => {
      if (!isRotating) return;

      const deltaX = moveEvent.clientX - centerX;
      const deltaY = moveEvent.clientY - centerY;
      let newAngle = Math.atan2(deltaY, deltaX) * (180 / Math.PI);

      // Calculer la rotation relative
      newAngle = newAngle - rotationStart.angle;

      // Appliquer l'aimantation si activée
      if (rotationSnap) {
        newAngle = Math.round(newAngle / rotationStep) * rotationStep;
      }

      // Normaliser l'angle entre 0 et 360
      newAngle = ((newAngle % 360) + 360) % 360;

      onElementRotate(newAngle);
    };

    const handleRotationEnd = () => {
      setIsRotating(false);
      document.removeEventListener('mousemove', handleRotationMove);
      document.removeEventListener('mouseup', handleRotationEnd);
    };

    document.addEventListener('mousemove', handleRotationMove);
    document.addEventListener('mouseup', handleRotationEnd);
  }, [isRotating, rotationStart, rotationStep, rotationSnap, onElementRotate]);

  return {
    isRotating,
    handleRotationStart
  };
};
