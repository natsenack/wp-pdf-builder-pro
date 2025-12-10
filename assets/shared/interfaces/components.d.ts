// Interfaces communes pour les composants React

// Interface de base pour tous les composants
export interface BaseComponentProps {
  className?: string;
  style?: React.CSSProperties;
  children?: React.ReactNode;
  id?: string;
  'data-testid'?: string;
}

// Interface pour les composants avec gestion d'état de chargement
export interface LoadingComponentProps extends BaseComponentProps {
  isLoading?: boolean;
  loadingText?: string;
  error?: Error | null;
}

// Interface pour les composants avec gestion d'erreurs
export interface ErrorComponentProps extends BaseComponentProps {
  error: Error;
  onRetry?: () => void;
  retryText?: string;
}

// Interface pour les composants de formulaire
export interface FormComponentProps<T = any> extends BaseComponentProps {
  value: T;
  onChange: (value: T) => void;
  onBlur?: () => void;
  onFocus?: () => void;
  disabled?: boolean;
  required?: boolean;
  error?: string;
  helperText?: string;
}

// Interface pour les composants de liste
export interface ListComponentProps<T = any> extends BaseComponentProps {
  items: T[];
  renderItem: (item: T, index: number) => React.ReactNode;
  keyExtractor?: (item: T, index: number) => string;
  emptyText?: string;
  isLoading?: boolean;
}

// Interface pour les composants de modal/dialogue
export interface ModalComponentProps extends BaseComponentProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  size?: 'small' | 'medium' | 'large' | 'fullscreen';
  closable?: boolean;
  backdrop?: boolean;
}

// Interface pour les composants de navigation
export interface NavigationComponentProps extends BaseComponentProps {
  currentPath: string;
  onNavigate: (path: string) => void;
  items: NavigationItem[];
}

export interface NavigationItem {
  id: string;
  label: string;
  path: string;
  icon?: React.ComponentType;
  children?: NavigationItem[];
  isActive?: boolean;
  isDisabled?: boolean;
}

// Interface pour les composants de tableau
export interface TableComponentProps<T = any> extends BaseComponentProps {
  data: T[];
  columns: TableColumn<T>[];
  sortable?: boolean;
  selectable?: boolean;
  selectedRows?: string[];
  onRowSelect?: (rowIds: string[]) => void;
  onSort?: (column: string, direction: 'asc' | 'desc') => void;
  loading?: boolean;
  emptyText?: string;
}

export interface TableColumn<T = any> {
  key: keyof T;
  label: string;
  sortable?: boolean;
  width?: number | string;
  align?: 'left' | 'center' | 'right';
  render?: (value: any, row: T) => React.ReactNode;
}

// Interface pour les composants de graphique
export interface ChartComponentProps extends BaseComponentProps {
  data: ChartData;
  type: 'line' | 'bar' | 'pie' | 'doughnut' | 'radar';
  options?: ChartOptions;
  loading?: boolean;
}

export interface ChartData {
  labels: string[];
  datasets: ChartDataset[];
}

export interface ChartDataset {
  label: string;
  data: number[];
  backgroundColor?: string | string[];
  borderColor?: string | string[];
  borderWidth?: number;
}

export interface ChartOptions {
  responsive?: boolean;
  maintainAspectRatio?: boolean;
  plugins?: {
    legend?: {
      display?: boolean;
      position?: 'top' | 'bottom' | 'left' | 'right';
    };
    tooltip?: {
      enabled?: boolean;
    };
  };
  scales?: {
    x?: {
      display?: boolean;
      title?: {
        display?: boolean;
        text?: string;
      };
    };
    y?: {
      display?: boolean;
      title?: {
        display?: boolean;
        text?: string;
      };
    };
  };
}