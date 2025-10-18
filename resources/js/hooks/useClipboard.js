import { useState, useCallback } from 'react';

export const useClipboard = ({
  onPaste
}) => {
  const [clipboardData, setClipboardData] = useState(null);

  const copy = useCallback((data) => {
    setClipboardData(data);
    // Ici on pourrait aussi utiliser l'API Clipboard du navigateur
    // mais pour la compatibilité, on utilise un état interne
  }, []);

  const paste = useCallback(() => {
    if (clipboardData && onPaste) {
      onPaste(clipboardData);
    }
    return clipboardData;
  }, [clipboardData, onPaste]);

  const cut = useCallback((data) => {
    copy(data);
    // La suppression sera gérée par le composant parent
    return data;
  }, [copy]);

  const hasData = useCallback(() => {
    return clipboardData !== null;
  }, [clipboardData]);

  const clear = useCallback(() => {
    setClipboardData(null);
  }, []);

  return {
    copy,
    paste,
    cut,
    hasData,
    clear,
    clipboardData
  };
};
