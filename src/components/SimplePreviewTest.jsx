import React from 'react';

// Composant de test simple pour l'aperçu
const SimplePreviewTest = () => {
  const testElements = [
    {
      id: 'test-text-1',
      type: 'text',
      content: 'Test Aperçu PDF',
      x: 50,
      y: 50,
      width: 200,
      height: 30,
      fontSize: 16,
      color: '#000000'
    },
    {
      id: 'test-rect-1',
      type: 'rectangle',
      x: 40,
      y: 40,
      width: 220,
      height: 50,
      borderColor: '#ff0000',
      borderWidth: 2
    }
  ];

  console.log('SimplePreviewTest - Éléments à rendre:', testElements);

  return (
    <div style={{
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      background: 'rgba(0, 0, 0, 0.6)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 2000
    }}>
      <div style={{
        background: 'white',
        borderRadius: '12px',
        boxShadow: '0 20px 40px rgba(0, 0, 0, 0.3)',
        maxWidth: '90vw',
        maxHeight: '90vh',
        width: '800px',
        overflow: 'hidden',
        display: 'flex',
        flexDirection: 'column'
      }}>
        <div style={{
          padding: '20px 24px',
          borderBottom: '1px solid #e2e8f0',
          background: 'linear-gradient(135deg, #007bff 0%, #28a745 100%)',
          color: 'white'
        }}>
          <h3 style={{ margin: 0, fontSize: '18px', fontWeight: 600 }}>
            🧪 Test Aperçu Simple
          </h3>
        </div>

        <div style={{ padding: '24px', overflowY: 'auto', flex: 1 }}>
          <div style={{
            padding: '20px',
            background: '#f8f9fa',
            borderRadius: '4px'
          }}>
            <div style={{
              width: '595px',
              height: '842px',
              margin: '0 auto',
              border: '1px solid #e2e8f0',
              background: 'white',
              position: 'relative',
              overflow: 'hidden',
              transform: 'scale(0.5)',
              transformOrigin: 'top center'
            }}>
              {testElements.map(element => {
                console.log('Rendu élément:', element);

                if (element.type === 'text') {
                  return (
                    <div
                      key={element.id}
                      style={{
                        position: 'absolute',
                        left: element.x,
                        top: element.y,
                        width: element.width,
                        height: element.height,
                        fontSize: element.fontSize,
                        color: element.color,
                        background: 'transparent',
                        border: 'none',
                        fontFamily: 'Arial, sans-serif',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        textAlign: 'center'
                      }}
                    >
                      {element.content}
                    </div>
                  );
                }

                if (element.type === 'rectangle') {
                  return (
                    <div
                      key={element.id}
                      style={{
                        position: 'absolute',
                        left: element.x,
                        top: element.y,
                        width: element.width,
                        height: element.height,
                        border: `${element.borderWidth}px solid ${element.borderColor}`,
                        background: 'transparent'
                      }}
                    />
                  );
                }

                return null;
              })}
            </div>
          </div>
        </div>

        <div style={{
          padding: '20px 24px',
          borderTop: '1px solid #e2e8f0',
          display: 'flex',
          justifyContent: 'flex-end',
          gap: '12px'
        }}>
          <button style={{
            padding: '10px 20px',
            border: '1px solid #e2e8f0',
            background: 'white',
            borderRadius: '6px',
            cursor: 'pointer'
          }}>
            ❌ Fermer
          </button>
        </div>
      </div>
    </div>
  );
};

export default SimplePreviewTest;