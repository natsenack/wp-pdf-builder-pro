import React, { createContext, useState } from 'react';

export const ModalContext = createContext();

export const ModalProvider = ({ children }) => {
  const [showTemplateSettingsModal, setShowTemplateSettingsModal] = useState(false);

  return (
    <ModalContext.Provider value={{ showTemplateSettingsModal, setShowTemplateSettingsModal }}>
      {children}
    </ModalContext.Provider>
  );
};

export const useModalContext = () => {
  const context = React.useContext(ModalContext);
  if (!context) {
    throw new Error('useModalContext must be used within ModalProvider');
  }
  return context;
};
