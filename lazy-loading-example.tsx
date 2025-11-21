// 💡 EXEMPLE D'IMPLÉMENTATION: Lazy Loading Canvas
// ================================================

// 📄 Dans Canvas.tsx - Hook personnalisé pour lazy loading
const useLazyElement = (elementId: string, isVisible: boolean) => {
  const [isLoaded, setIsLoaded] = useState(false);
  const [elementData, setElementData] = useState(null);

  useEffect(() => {
    if (isVisible && !isLoaded) {
      // Charger l'élément de manière lazy
      loadElementData(elementId).then(setElementData);
      setIsLoaded(true);
    }
  }, [isVisible, isLoaded, elementId]);

  return { elementData, isLoaded };
};

// 🎯 Composant LazyElement avec Suspense
const LazyElement = ({ element, isVisible }) => {
  const { elementData, isLoaded } = useLazyElement(element.id, isVisible);

  if (!isLoaded) {
    return <div className="lazy-placeholder">Chargement...</div>;
  }

  return <ElementRenderer element={elementData} />;
};

// 📊 Intersection Observer pour visibilité
const useIntersectionObserver = (ref, options = {}) => {
  const [isIntersecting, setIsIntersecting] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      ([entry]) => setIsIntersecting(entry.isIntersecting),
      { threshold: 0.1, ...options }
    );

    if (ref.current) observer.observe(ref.current);
    return () => observer.disconnect();
  }, [ref, options]);

  return isIntersecting;
};

// 🔧 Intégration dans Canvas.tsx
const Canvas = ({ width, height, className }: CanvasProps) => {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const { state, dispatch } = useBuilder();
  const canvasSettings = useCanvasSettings();

  // ✅ Récupérer le paramètre lazy loading
  const lazyLoadingEnabled = canvasSettings.lazy_loading_editor;

  // ✅ État pour les éléments visibles (viewport)
  const [visibleElements, setVisibleElements] = useState(new Set());

  // ✅ Observer pour détecter les éléments visibles
  const observerRef = useRef(null);
  const isCanvasVisible = useIntersectionObserver(observerRef, {
    threshold: 0.1,
    rootMargin: '50px'
  });

  // ✅ Rendu conditionnel selon lazy loading
  const renderElement = (element: Element, index: number) => {
    if (!lazyLoadingEnabled) {
      // Mode normal - tous les éléments rendus
      return <ElementRenderer key={element.id} element={element} />;
    }

    // Mode lazy - seulement éléments visibles
    const isVisible = visibleElements.has(element.id) || index < 10; // 10 premiers toujours visibles
    return (
      <LazyElement
        key={element.id}
        element={element}
        isVisible={isVisible}
      />
    );
  };

  return (
    <div ref={observerRef} className="canvas-container">
      <canvas
        ref={canvasRef}
        width={width}
        height={height}
        className={className}
      />
      {/* Rendu des éléments avec lazy loading */}
      {state.elements.map(renderElement)}
    </div>
  );
};