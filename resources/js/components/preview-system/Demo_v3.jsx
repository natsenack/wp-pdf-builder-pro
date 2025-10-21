/**
 * DÉMONSTRATION DU SYSTÈME D'APERÇU ULTRA-SIMPLE V3.0
 * Exemple d'utilisation simple et directe
 */

import React, { useState } from 'react';
import { SimplePreviewModal, useSimplePreview, PreviewSystemTestV3 } from './preview-system/index_v3';

function DemoApp() {
  // État pour contrôler l'aperçu
  const [showDemo, setShowDemo] = useState(false);

  // Hook pour gérer l'aperçu facilement
  const { openPreview, PreviewModal } = useSimplePreview();

  // Données d'exemple simples
  const demoElements = [
    {
      id: 1,
      type: 'text',
      x: 50,
      y: 50,
      width: 250,
      height: 40,
      text: 'APERÇU PDF BUILDER PRO',
      fontSize: 20,
      fontWeight: 'bold',
      color: '#1f2937',
      textAlign: 'center'
    },
    {
      id: 2,
      type: 'rectangle',
      x: 50,
      y: 110,
      width: 300,
      height: 80,
      backgroundColor: '#3b82f6',
      borderRadius: 8
    },
    {
      id: 3,
      type: 'text',
      x: 70,
      y: 130,
      width: 260,
      height: 40,
      text: 'Système ultra-simple v3.0',
      fontSize: 16,
      color: 'white',
      textAlign: 'center'
    },
    {
      id: 4,
      type: 'image',
      x: 400,
      y: 50,
      width: 120,
      height: 120,
      src: 'https://via.placeholder.com/120x120/10b981/white?text=✓',
      borderRadius: 60
    },
    {
      id: 5,
      type: 'table',
      x: 50,
      y: 210,
      width: 470,
      height: 100,
      data: [
        ['Fonction', 'Statut', 'Version'],
        ['Positionnement', '✅ Parfait', 'v3.0'],
        ['Rendu', '✅ Ultra-simple', 'v3.0'],
        ['Performance', '✅ Optimisé', 'v3.0']
      ],
      fontSize: 12
    }
  ];

  // Fonction pour ouvrir l'aperçu avec les données de démo
  const handleOpenDemo = () => {
    openPreview({
      elements: demoElements,
      templateWidth: 595,
      templateHeight: 842,
      title: 'Démonstration - Aperçu Ultra-Simple v3.0'
    });
  };

  return (
    <div style={{
      padding: '20px',
      fontFamily: 'system-ui, -apple-system, sans-serif',
      maxWidth: '800px',
      margin: '0 auto'
    }}>
      <h1 style={{
        color: '#1f2937',
        textAlign: 'center',
        marginBottom: '30px'
      }}>
        🎯 Démonstration du système d'aperçu v3.0
      </h1>

      <div style={{
        backgroundColor: 'white',
        borderRadius: '12px',
        padding: '30px',
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
        marginBottom: '30px'
      }}>
        <h2 style={{ color: '#1f2937', marginTop: 0 }}>✨ Fonctionnalités principales</h2>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: '20px', marginBottom: '30px' }}>
          <div style={{ padding: '20px', backgroundColor: '#f8fafc', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
            <h3 style={{ color: '#10b981', margin: '0 0 10px 0' }}>🎯 Positionnement parfait</h3>
            <p style={{ margin: 0, color: '#64748b', fontSize: '14px' }}>
              Calcul mathématique précis des coordonnées et dimensions.
            </p>
          </div>

          <div style={{ padding: '20px', backgroundColor: '#f8fafc', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
            <h3 style={{ color: '#3b82f6', margin: '0 0 10px 0' }}>🚀 Architecture simple</h3>
            <p style={{ margin: 0, color: '#64748b', fontSize: '14px' }}>
              Code ultra-lisible sans complexité inutile.
            </p>
          </div>

          <div style={{ padding: '20px', backgroundColor: '#f8fafc', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
            <h3 style={{ color: '#8b5cf6', margin: '0 0 10px 0' }}>🔍 Débogage intégré</h3>
            <p style={{ margin: 0, color: '#64748b', fontSize: '14px' }}>
              Logs détaillés et mode debug pour faciliter le développement.
            </p>
          </div>

          <div style={{ padding: '20px', backgroundColor: '#f8fafc', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
            <h3 style={{ color: '#f59e0b', margin: '0 0 10px 0' }}>⚡ Performance optimale</h3>
            <p style={{ margin: 0, color: '#64748b', fontSize: '14px' }}>
              Rendu rapide et efficace sans re-renders inutiles.
            </p>
          </div>
        </div>

        <div style={{ textAlign: 'center' }}>
          <button
            onClick={handleOpenDemo}
            style={{
              backgroundColor: '#3b82f6',
              color: 'white',
              border: 'none',
              padding: '12px 24px',
              borderRadius: '8px',
              fontSize: '16px',
              fontWeight: '600',
              cursor: 'pointer',
              boxShadow: '0 2px 4px rgba(59, 130, 246, 0.2)',
              transition: 'all 0.2s ease'
            }}
            onMouseOver={(e) => e.target.style.transform = 'translateY(-1px)'}
            onMouseOut={(e) => e.target.style.transform = 'translateY(0)'}
          >
            🔍 Voir la démonstration
          </button>
        </div>
      </div>

      <div style={{
        backgroundColor: 'white',
        borderRadius: '12px',
        padding: '30px',
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
        marginBottom: '30px'
      }}>
        <h2 style={{ color: '#1f2937', marginTop: 0 }}>🧪 Test complet du système</h2>
        <p style={{ color: '#64748b', marginBottom: '20px' }}>
          Cliquez ci-dessous pour accéder au test complet avec tous les types d'éléments :
        </p>

        <div style={{ textAlign: 'center' }}>
          <button
            onClick={() => setShowDemo(true)}
            style={{
              backgroundColor: '#10b981',
              color: 'white',
              border: 'none',
              padding: '12px 24px',
              borderRadius: '8px',
              fontSize: '16px',
              fontWeight: '600',
              cursor: 'pointer',
              boxShadow: '0 2px 4px rgba(16, 185, 129, 0.2)',
              transition: 'all 0.2s ease'
            }}
            onMouseOver={(e) => e.target.style.transform = 'translateY(-1px)'}
            onMouseOut={(e) => e.target.style.transform = 'translateY(0)'}
          >
            🧪 Ouvrir le test complet
          </button>
        </div>
      </div>

      <div style={{
        backgroundColor: '#f8fafc',
        borderRadius: '12px',
        padding: '20px',
        border: '1px solid #e2e8f0'
      }}>
        <h3 style={{ color: '#1f2937', marginTop: 0 }}>📖 Comment utiliser</h3>
        <pre style={{
          backgroundColor: 'white',
          padding: '15px',
          borderRadius: '6px',
          fontSize: '13px',
          overflow: 'auto',
          border: '1px solid #e2e8f0'
        }}>
{`import { SimplePreviewModal, useSimplePreview } from './preview-system/index_v3';

function MonComposant() {
  const { openPreview, PreviewModal } = useSimplePreview();

  const handlePreview = () => {
    openPreview({
      elements: mesElements,
      templateWidth: 595,
      templateHeight: 842
    });
  };

  return (
    <>
      <button onClick={handlePreview}>Aperçu</button>
      <PreviewModal />
    </>
  );
}`}
        </pre>
      </div>

      {/* Modal de démonstration */}
      <PreviewModal />

      {/* Test complet du système */}
      {showDemo && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          zIndex: 10000,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center'
        }}>
          <div style={{
            width: '95%',
            height: '95%',
            maxWidth: '1200px'
          }}>
            <PreviewSystemTestV3 />
            <div style={{ textAlign: 'center', marginTop: '20px' }}>
              <button
                onClick={() => setShowDemo(false)}
                style={{
                  backgroundColor: '#ef4444',
                  color: 'white',
                  border: 'none',
                  padding: '10px 20px',
                  borderRadius: '6px',
                  cursor: 'pointer'
                }}
              >
                Fermer le test
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}

export default DemoApp;