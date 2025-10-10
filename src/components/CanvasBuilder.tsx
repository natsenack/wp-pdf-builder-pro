import React, { useState, useRef, useEffect } from 'react';

interface CanvasBuilderProps {
  width?: number;
  height?: number;
}

const CanvasBuilder: React.FC<CanvasBuilderProps> = ({
  width = 800,
  height = 600
}) => {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const [isReady, setIsReady] = useState(false);

  useEffect(() => {
    if (canvasRef.current) {
      const canvas = canvasRef.current;
      const ctx = canvas.getContext('2d');

      if (ctx) {
        // Configuration de base du canvas
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);

        // Bordure
        ctx.strokeStyle = '#dee2e6';
        ctx.lineWidth = 1;
        ctx.strokeRect(0, 0, width, height);

        setIsReady(true);
      }
    }
  }, [width, height]);

  return (
    <div style={{
      padding: '20px',
      backgroundColor: '#f8f9fa',
      borderRadius: '8px',
      boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
    }}>
      <h2 style={{
        margin: '0 0 20px 0',
        color: '#333',
        fontSize: '24px',
        fontWeight: 'bold'
      }}>
        🆕 Nouveau Builder Canvas
      </h2>

      <div style={{
        backgroundColor: 'white',
        padding: '20px',
        borderRadius: '4px',
        display: 'inline-block'
      }}>
        <canvas
          ref={canvasRef}
          width={width}
          height={height}
          style={{
            border: '2px solid #007cba',
            borderRadius: '4px',
            cursor: 'crosshair'
          }}
        />
      </div>

      <div style={{
        marginTop: '20px',
        padding: '15px',
        backgroundColor: '#f8fafc',
        borderRadius: '4px',
        border: '1px solid #e2e8f0'
      }}>
        <h3 style={{ margin: '0 0 10px 0', color: '#1976d2' }}>
          🚀 Status: {isReady ? 'Prêt' : 'Initialisation...'}
        </h3>
        <p style={{ margin: '0', color: '#64748b' }}>
          Canvas de base créé. Prêt pour ajouter des fonctionnalités !
        </p>
      </div>
    </div>
  );
};

export default CanvasBuilder;