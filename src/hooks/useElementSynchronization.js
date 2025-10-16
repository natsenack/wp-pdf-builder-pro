import React from 'react';
const { useCallback, useEffect, useRef } = React;

/**
 * Hook pour gérer la synchronisation des personnalisations d'éléments
 * Gère la persistance, la validation et la synchronisation avec le backend
 */
export const useElementSynchronization = (
  elements,
  onPropertyChange,
  onBatchUpdate,
  autoSave = true,
  autoSaveDelay = 3000
) => {
  const pendingChangesRef = useRef(new Map());
  const autoSaveTimeoutRef = useRef(null);
  const lastSavedRef = useRef(new Map());

  // Synchronisation différée pour éviter les appels trop fréquents
  const debouncedSync = useCallback((elementId, property, value) => {
    // Annuler la sauvegarde automatique précédente
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
    }

    // Ajouter le changement aux modifications en attente
    const key = `${elementId}.${property}`;
    pendingChangesRef.current.set(key, { elementId, property, value });

    // Programmer une sauvegarde automatique
    if (autoSave) {
      autoSaveTimeoutRef.current = setTimeout(() => {
        syncPendingChanges();
      }, autoSaveDelay);
    }
  }, [autoSave, autoSaveDelay]);

  // Synchroniser immédiatement
  const immediateSync = useCallback((elementId, property, value) => {
    // Annuler la sauvegarde automatique
    if (autoSaveTimeoutRef.current) {
      clearTimeout(autoSaveTimeoutRef.current);
    }

    // Synchroniser immédiatement
    onPropertyChange(elementId, property, value);

    // Mettre à jour la référence de dernière sauvegarde
    const key = `${elementId}.${property}`;
    lastSavedRef.current.set(key, value);

    // Vider les changements en attente pour cette propriété
    pendingChangesRef.current.delete(key);
  }, [onPropertyChange]);

  // Synchroniser tous les changements en attente
  const syncPendingChanges = useCallback(() => {
    if (pendingChangesRef.current.size === 0) return;

    const changes = Array.from(pendingChangesRef.current.values());

    // Grouper les changements par élément pour optimiser
    const changesByElement = changes.reduce((acc, change) => {
      if (!acc[change.elementId]) {
        acc[change.elementId] = {};
      }
      acc[change.elementId][change.property] = change.value;
      return acc;
    }, {});

    // Si on a une fonction de mise à jour par lot, l'utiliser
    if (onBatchUpdate && Object.keys(changesByElement).length > 1) {
      onBatchUpdate(changesByElement);
    } else {
      // Sinon, mettre à jour élément par élément
      changes.forEach(({ elementId, property, value }) => {
        onPropertyChange(elementId, property, value);
      });
    }

    // Mettre à jour les références de dernière sauvegarde
    changes.forEach(({ elementId, property, value }) => {
      const key = `${elementId}.${property}`;
      lastSavedRef.current.set(key, value);
    });

    // Vider les changements en attente
    pendingChangesRef.current.clear();
  }, [onPropertyChange, onBatchUpdate]);

  // Forcer la synchronisation immédiate
  const forceSync = useCallback(() => {
    syncPendingChanges();
  }, [syncPendingChanges]);

  // Vérifier si des changements sont en attente
  const hasPendingChanges = useCallback(() => {
    return pendingChangesRef.current.size > 0;
  }, []);

  // Obtenir les changements en attente pour un élément
  const getPendingChanges = useCallback((elementId) => {
    const changes = [];
    pendingChangesRef.current.forEach((change, key) => {
      if (change.elementId === elementId) {
        changes.push(change);
      }
    });
    return changes;
  }, []);

  // Annuler les changements en attente pour un élément
  const cancelPendingChanges = useCallback((elementId) => {
    const keysToDelete = [];
    pendingChangesRef.current.forEach((change, key) => {
      if (change.elementId === elementId) {
        keysToDelete.push(key);
      }
    });
    keysToDelete.forEach(key => pendingChangesRef.current.delete(key));
  }, []);

  // Restaurer les dernières valeurs sauvegardées
  const restoreLastSaved = useCallback((elementId, property) => {
    const key = `${elementId}.${property}`;
    const lastSavedValue = lastSavedRef.current.get(key);

    if (lastSavedValue !== undefined) {
      // Annuler le changement en attente
      pendingChangesRef.current.delete(key);

      // Restaurer la valeur
      return lastSavedValue;
    }

    return null;
  }, []);

  // Validation des propriétés avant synchronisation
  const validateAndSync = useCallback((elementId, property, value, validator) => {
    let validatedValue = value;

    // Appliquer la validation si fournie
    if (validator) {
      validatedValue = validator(value);
    }

    // Appliquer la validation par défaut selon le type de propriété
    validatedValue = validatePropertyValue(property, validatedValue);

    // Synchroniser
    debouncedSync(elementId, property, validatedValue);

    return validatedValue;
  }, [debouncedSync]);

  // Validation des valeurs de propriétés
  const validatePropertyValue = (property, value) => {
    switch (property) {
      case 'x':
      case 'y':
      case 'width':
      case 'height':
        return Math.max(0, parseInt(value) || 0);

      case 'fontSize':
        return Math.max(8, Math.min(72, parseInt(value) || 14));

      case 'borderWidth':
        return Math.max(0, Math.min(20, parseInt(value) || 0));

      case 'borderRadius':
        return Math.max(0, Math.min(100, parseInt(value) || 0));

      case 'rotation':
        return ((parseInt(value) || 0) % 360 + 360) % 360;

      case 'scale':
        return Math.max(10, Math.min(200, parseInt(value) || 100));

      case 'opacity':
        return Math.max(0, Math.min(100, parseInt(value) || 100));

      case 'brightness':
      case 'contrast':
      case 'saturate':
        return Math.max(0, Math.min(200, parseInt(value) || 100));

      case 'shadowOffsetX':
      case 'shadowOffsetY':
        return Math.max(-50, Math.min(50, parseInt(value) || 0));

      default:
        return value;
    }
  };

  // Nettoyer les timeouts au démontage
  useEffect(() => {
    return () => {
      if (autoSaveTimeoutRef.current) {
        clearTimeout(autoSaveTimeoutRef.current);
      }
    };
  }, []);

  // Synchronisation automatique lors des changements d'éléments
  useEffect(() => {
    // Sauvegarder automatiquement quand les éléments changent
    if (autoSave && pendingChangesRef.current.size > 0) {
      syncPendingChanges();
    }
  }, [elements, autoSave, syncPendingChanges]);

  return {
    // Méthodes de synchronisation
    sync: debouncedSync,
    syncImmediate: immediateSync,
    syncPending: syncPendingChanges,
    forceSync,

    // Gestion des changements en attente
    hasPendingChanges,
    getPendingChanges,
    cancelPendingChanges,

    // Validation et restauration
    validateAndSync,
    restoreLastSaved,

    // État
    pendingChangesCount: pendingChangesRef.current.size
  };
};
