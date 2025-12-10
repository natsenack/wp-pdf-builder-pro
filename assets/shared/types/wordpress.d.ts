// Types globaux pour WordPress
declare global {
  interface Window {
    wp: {
      apiFetch: any;
      blocks: any;
      data: any;
      element: any;
      i18n: any;
      plugins: any;
    };
    ajaxurl: string;
    pdfBuilderPro: {
      ajaxUrl: string;
      nonce: string;
      version: string;
    };
  }
}

// Types pour les requêtes AJAX
export interface AjaxResponse<T = any> {
  success: boolean;
  data: T;
  message?: string;
}

export interface AjaxError {
  success: false;
  data: {
    message: string;
    code?: string;
  };
}

// Types pour les paramètres WordPress
export interface WPPost {
  id: number;
  title: {
    rendered: string;
  };
  content: {
    rendered: string;
  };
  excerpt: {
    rendered: string;
  };
  status: 'publish' | 'draft' | 'private';
  type: string;
  author: number;
  date: string;
  modified: string;
}

export interface WPMedia {
  id: number;
  title: {
    rendered: string;
  };
  source_url: string;
  alt_text: string;
  mime_type: string;
  media_type: string;
}

// Types pour les métaboxes WordPress
export interface MetaBoxData {
  [key: string]: any;
}

export interface MetaBoxProps {
  postId: number;
  metaKey: string;
  value: any;
  onChange: (value: any) => void;
}