import { TextElement } from "../../types/elements";
import { NumericPropertyInput } from "../ui/NumericPropertyInput";
import { ColorPropertyInput } from "../ui/ColorPropertyInput";

interface TextPropertiesProps {
  element: TextElement;
  onChange: (elementId: string, property: string, value: unknown) => void;
  activeTab: {
    [key: string]: "fonctionnalites" | "personnalisation" | "positionnement";
  };
  setActiveTab: (tabs: {
    [key: string]: "fonctionnalites" | "personnalisation" | "positionnement";
  }) => void;
}

export function TextProperties({
  element,
  onChange,
  activeTab,
  setActiveTab,
}: TextPropertiesProps) {
  const textCurrentTab = activeTab[element.id] || "fonctionnalites";
  const setTextCurrentTab = (
    tab: "fonctionnalites" | "personnalisation" | "positionnement",
  ) => {
    setActiveTab({ ...activeTab, [element.id]: tab });
  };

  return (
    <>
      {/* Système d'onglets pour Text */}
      <div
        style={{
          display: "flex",
          marginBottom: "12px",
          borderBottom: "2px solid #ddd",
          gap: "2px",
          flexWrap: "wrap",
        }}
      >
        <button
          onClick={() => setTextCurrentTab("fonctionnalites")}
          style={{
            flex: "1 1 30%",
            padding: "8px 6px",
            backgroundColor:
              textCurrentTab === "fonctionnalites" ? "#007bff" : "#f0f0f0",
            color: textCurrentTab === "fonctionnalites" ? "#fff" : "#333",
            border: "none",
            cursor: "pointer",
            fontSize: "11px",
            fontWeight: "bold",
            borderRadius: "3px 3px 0 0",
            minWidth: "0",
            whiteSpace: "nowrap",
            overflow: "hidden",
            textOverflow: "ellipsis",
          }}
          title="Fonctionnalités"
        >
          Fonctionnalités
        </button>
        <button
          onClick={() => setTextCurrentTab("personnalisation")}
          style={{
            flex: "1 1 30%",
            padding: "8px 6px",
            backgroundColor:
              textCurrentTab === "personnalisation" ? "#007bff" : "#f0f0f0",
            color: textCurrentTab === "personnalisation" ? "#fff" : "#333",
            border: "none",
            cursor: "pointer",
            fontSize: "11px",
            fontWeight: "bold",
            borderRadius: "3px 3px 0 0",
            minWidth: "0",
            whiteSpace: "nowrap",
            overflow: "hidden",
            textOverflow: "ellipsis",
          }}
          title="Personnalisation"
        >
          Personnalisation
        </button>
        <button
          onClick={() => setTextCurrentTab("positionnement")}
          style={{
            flex: "1 1 30%",
            padding: "8px 6px",
            backgroundColor:
              textCurrentTab === "positionnement" ? "#007bff" : "#f0f0f0",
            color: textCurrentTab === "positionnement" ? "#fff" : "#333",
            border: "none",
            cursor: "pointer",
            fontSize: "11px",
            fontWeight: "bold",
            borderRadius: "3px 3px 0 0",
            minWidth: "0",
            whiteSpace: "nowrap",
            overflow: "hidden",
            textOverflow: "ellipsis",
          }}
          title="Positionnement"
        >
          Positionnement
        </button>
      </div>

      {/* Onglet Fonctionnalités */}
      {textCurrentTab === "fonctionnalites" && (
        <>
          <div style={{ marginBottom: "12px" }}>
            <label
              style={{
                display: "block",
                fontSize: "12px",
                fontWeight: "bold",
                marginBottom: "6px",
              }}
            >
              Contenu du texte
            </label>
            <textarea
              value={element.text || ""}
              onChange={(e) => onChange(element.id, "text", e.target.value)}
              style={{
                width: "100%",
                minHeight: "60px",
                padding: "8px",
                border: "1px solid #ccc",
                borderRadius: "3px",
                fontSize: "12px",
                fontFamily: "monospace",
                resize: "vertical",
              }}
              placeholder="Entrez votre texte ici..."
            />
          </div>

          <div style={{ marginBottom: "12px" }}>
            <label
              style={{
                display: "block",
                fontSize: "12px",
                fontWeight: "bold",
                marginBottom: "6px",
              }}
            >
              Alignement horizontal
            </label>
            <select
              value={element.textAlign || "left"}
              onChange={(e) =>
                onChange(element.id, "textAlign", e.target.value)
              }
              style={{
                width: "100%",
                padding: "4px 8px",
                border: "1px solid #ccc",
                borderRadius: "3px",
                fontSize: "12px",
              }}
            >
              <option value="left">Gauche</option>
              <option value="center">Centre</option>
              <option value="right">Droite</option>
              <option value="justify">Justifié</option>
            </select>
          </div>

          <div style={{ marginBottom: "12px" }}>
            <label
              style={{
                display: "block",
                fontSize: "12px",
                fontWeight: "bold",
                marginBottom: "6px",
              }}
            >
              Alignement vertical
            </label>
            <select
              value={element.verticalAlign || "top"}
              onChange={(e) =>
                onChange(element.id, "verticalAlign", e.target.value)
              }
              style={{
                width: "100%",
                padding: "4px 8px",
                border: "1px solid #ccc",
                borderRadius: "3px",
                fontSize: "12px",
              }}
            >
              <option value="top">Haut</option>
              <option value="middle">Milieu</option>
              <option value="bottom">Bas</option>
            </select>
          </div>
        </>
      )}

      {/* Onglet Personnalisation */}
      {textCurrentTab === "personnalisation" && (
        <>
          <div style={{ marginBottom: "12px" }}>
            <label
              style={{
                display: "block",
                fontSize: "12px",
                fontWeight: "bold",
                marginBottom: "6px",
              }}
            >
              Police
            </label>
            <select
              value={element.fontFamily || "Arial"}
              onChange={(e) =>
                onChange(element.id, "fontFamily", e.target.value)
              }
              style={{
                width: "100%",
                padding: "4px 8px",
                border: "1px solid #ccc",
                borderRadius: "3px",
                fontSize: "12px",
              }}
            >
              <option value="Arial">Arial</option>
              <option value="Helvetica">Helvetica</option>
              <option value="Times New Roman">Times New Roman</option>
              <option value="Courier New">Courier New</option>
              <option value="Georgia">Georgia</option>
              <option value="Verdana">Verdana</option>
              <option value="Tahoma">Tahoma</option>
            </select>
          </div>

          <NumericPropertyInput
            label="Taille de police"
            value={element.fontSize}
            defaultValue={16}
            min={6}
            max={72}
            step={1}
            unit="px"
            onChange={(value) => onChange(element.id, "fontSize", value)}
            description="Taille de la police en pixels"
          />

          <div style={{ marginBottom: "12px" }}>
            <label
              style={{
                display: "block",
                fontSize: "12px",
                fontWeight: "bold",
                marginBottom: "6px",
              }}
            >
              Style de police
            </label>
            <div style={{ display: "flex", gap: "4px", flexWrap: "wrap" }}>
              <button
                onClick={() =>
                  onChange(
                    element.id,
                    "fontWeight",
                    element.fontWeight === "bold" ? "normal" : "bold",
                  )
                }
                style={{
                  padding: "4px 8px",
                  border: "1px solid #ccc",
                  borderRadius: "3px",
                  backgroundColor:
                    element.fontWeight === "bold" ? "#007bff" : "#f8f9fa",
                  color: element.fontWeight === "bold" ? "#fff" : "#333",
                  cursor: "pointer",
                  fontSize: "11px",
                  fontWeight: "bold",
                }}
              >
                B
              </button>
              <button
                onClick={() =>
                  onChange(
                    element.id,
                    "fontStyle",
                    element.fontStyle === "italic" ? "normal" : "italic",
                  )
                }
                style={{
                  padding: "4px 8px",
                  border: "1px solid #ccc",
                  borderRadius: "3px",
                  backgroundColor:
                    element.fontStyle === "italic" ? "#007bff" : "#f8f9fa",
                  color: element.fontStyle === "italic" ? "#fff" : "#333",
                  cursor: "pointer",
                  fontSize: "11px",
                  fontStyle: "italic",
                }}
              >
                I
              </button>
              <button
                onClick={() =>
                  onChange(
                    element.id,
                    "textDecoration",
                    element.textDecoration === "underline"
                      ? "none"
                      : "underline",
                  )
                }
                style={{
                  padding: "4px 8px",
                  border: "1px solid #ccc",
                  borderRadius: "3px",
                  backgroundColor:
                    element.textDecoration === "underline"
                      ? "#007bff"
                      : "#f8f9fa",
                  color:
                    element.textDecoration === "underline" ? "#fff" : "#333",
                  cursor: "pointer",
                  fontSize: "11px",
                  textDecoration: "underline",
                }}
              >
                U
              </button>
            </div>
          </div>

          <ColorPropertyInput
            label="Couleur du texte"
            value={element.color || "#000000"}
            defaultValue="#000000"
            onChange={(value) => onChange(element.id, "color", value)}
          />

          <ColorPropertyInput
            label="Couleur de fond"
            value={element.backgroundColor || "#ffffff"}
            defaultValue="#ffffff"
            onChange={(value) => onChange(element.id, "backgroundColor", value)}
          />
        </>
      )}

      {/* Onglet Positionnement */}
      {textCurrentTab === "positionnement" && (
        <>
          <div style={{ marginBottom: "12px" }}>
            <NumericPropertyInput
              label="Position X"
              value={element.x}
              defaultValue={0}
              unit="px"
              onChange={(value) => onChange(element.id, "x", value)}
            />
          </div>

          <div style={{ marginBottom: "12px" }}>
            <NumericPropertyInput
              label="Position Y"
              value={element.y}
              defaultValue={0}
              unit="px"
              onChange={(value) => onChange(element.id, "y", value)}
            />
          </div>

          <div style={{ marginBottom: "12px" }}>
            <NumericPropertyInput
              label="Largeur"
              value={element.width}
              defaultValue={100}
              min={1}
              unit="px"
              onChange={(value) => onChange(element.id, "width", value)}
            />
          </div>

          <div style={{ marginBottom: "12px" }}>
            <NumericPropertyInput
              label="Hauteur"
              value={element.height}
              defaultValue={50}
              min={1}
              unit="px"
              onChange={(value) => onChange(element.id, "height", value)}
            />
          </div>

          <div style={{ marginBottom: "12px" }}>
            <NumericPropertyInput
              label="Rotation"
              value={element.rotation}
              defaultValue={0}
              min={-180}
              max={180}
              unit="°"
              onChange={(value) => onChange(element.id, "rotation", value)}
            />
          </div>

          <div style={{ marginBottom: "12px" }}>
            <NumericPropertyInput
              label="Padding interne (px)"
              value={element.padding || 12}
              defaultValue={12}
              min={0}
              max={50}
              onChange={(value) => onChange(element.id, "padding", value)}
            />
          </div>

          <div style={{ marginBottom: "12px" }}>
            <label
              style={{
                display: "block",
                fontSize: "12px",
                fontWeight: "bold",
                marginBottom: "6px",
              }}
            >
              Opacité{" "}
              <span style={{ color: "#666", fontSize: "10px" }}>
                ({Math.round((element.opacity || 1) * 100)}%)
              </span>
            </label>
            <input
              type="range"
              min="0"
              max="1"
              step="0.1"
              value={element.opacity || 1}
              onChange={(e) =>
                onChange(element.id, "opacity", parseFloat(e.target.value))
              }
              style={{ width: "100%" }}
            />
          </div>

        </>
      )}
    </>
  );
}
