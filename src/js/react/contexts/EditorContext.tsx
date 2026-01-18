import React, {
  createContext,
  useContext,
  useState,
  FC,
  ReactNode,
} from "react";

interface EditorContextType {
  selectedElement: string | null;
  setSelectedElement: (id: string | null) => void;
  scale: number;
  setScale: (scale: number) => void;
  showGrid: boolean;
  setShowGrid: (show: boolean) => void;
  snapToGrid: boolean;
  setSnapToGrid: (snap: boolean) => void;
}

const EditorContext = createContext<EditorContextType | undefined>(undefined);

export const EditorProvider: FC<{ children: ReactNode }> = ({ children }) => {
  const [selectedElement, setSelectedElement] = useState<string | null>(null);
  const [scale, setScale] = useState(100);
  const [showGrid, setShowGrid] = useState(false);
  const [snapToGrid, setSnapToGrid] = useState(true);

  return (
    <EditorContext.Provider
      value={{
        selectedElement,
        setSelectedElement,
        scale,
        setScale,
        showGrid,
        setShowGrid,
        snapToGrid,
        setSnapToGrid,
      }}
    >
      {children}
    </EditorContext.Provider>
  );
};

export const useEditor = () => {
  const context = useContext(EditorContext);
  if (!context) {
    throw new Error("useEditor must be used within EditorProvider");
  }
  return context;
};


