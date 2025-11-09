# 🔍 REAL-TIME ELEMENT PROPERTY MONITORING GUIDE

## 📊 Overview

The PDF Builder Pro now includes a comprehensive **Real-Time Element Tracking System** that monitors all element changes and property modifications as they happen in the canvas.

### What It Tracks

✅ **Element Changes**:
- Element creation
- Element deletion
- Element updates (position, size, rotation, opacity)

✅ **Property Changes** (ALL properties):
- Spatial properties: `x`, `y`, `width`, `height`, `rotation`
- Visual properties: `color`, `fillColor`, `strokeColor`, `textColor`, `backgroundColor`, `opacity`
- Text properties: `text`, `fontSize`, `fontFamily`, `fontWeight`
- State properties: `visible`, `locked`
- Element-specific properties: All custom properties per element type

✅ **Change History**:
- Complete change log with timestamps
- Filterable by element, property, or time range
- Exportable to JSON for analysis

## 🎯 Usage

### 1. **View Real-Time Dashboard**

Open your browser's Developer Console (F12 or Ctrl+Shift+I) and run:

```javascript
CanvasMonitoringDashboard.showDashboard()
```

**Example Output:**
```
🎯 CANVAS MONITORING DASHBOARD
═══════════════════════════════════════
📊 SESSION STATISTICS
  Uptime: 45.23s
  Total Renders: 125
  Total Changes: 342
  Elements Tracked: 8

🔄 CHANGE BREAKDOWN
  ✨ created: 8
  🗑️ deleted: 1
  🔧 property_changed: 333

🔧 TOP CHANGED PROPERTIES
  📍 x: 85 changes
  📍 y: 82 changes
  📏 width: 45 changes
  📏 height: 43 changes
  🎨 fillColor: 28 changes

🎨 MOST CHANGED ELEMENTS
  📦 text-element-1: 187 changes
  📦 shape-element-2: 92 changes
  📦 image-element-3: 37 changes
  📦 table-element-4: 18 changes
```

### 2. **Get Complete Change History**

```javascript
CanvasMonitoringDashboard.getHistory()
```

Returns array of all changes with:
- `elementId`: The affected element
- `property`: Property name
- `oldValue`: Previous value
- `newValue`: New value
- `timestamp`: When the change occurred
- `changeType`: 'created' | 'deleted' | 'updated' | 'property_changed'

### 3. **Track Specific Element**

```javascript
CanvasMonitoringDashboard.getElementHistory("element-id-here")
```

Shows all changes made to a specific element:
```javascript
// Example: Track text element
const textChanges = CanvasMonitoringDashboard.getElementHistory("text-element-1");
console.table(textChanges);
```

### 4. **Track Specific Property**

```javascript
CanvasMonitoringDashboard.getPropertyHistory("x")  // Position tracking
CanvasMonitoringDashboard.getPropertyHistory("fillColor")  // Color changes
CanvasMonitoringDashboard.getPropertyHistory("text")  // Text content changes
```

### 5. **Generate Detailed Report**

```javascript
console.log(CanvasMonitoringDashboard.generateReport())
```

Example:
```
📋 DETAILED MONITORING REPORT
═══════════════════════════════════════
Total Events: 342
Elements: 8

Recent Changes (last 10):
  [14:32:45.123] ✨ Created: text-element-5
  [14:32:47.456] 🔧 text-element-5.text: "" → "Invoice #12345"
  [14:32:48.789] 🎨 text-element-5.fillColor: "#000000" → "#FF6B6B"
  [14:32:50.012] 📍 text-element-5.x: 100 → 105
  [14:32:50.345] 📍 text-element-5.y: 50 → 55
```

### 6. **Export Data**

Export all tracking data to JSON:

```javascript
const data = CanvasMonitoringDashboard.exportHistory();
console.log(data);
// Copy and save to file for analysis
```

### 7. **Clear History**

Reset monitoring (useful for testing):

```javascript
CanvasMonitoringDashboard.clearHistory()
```

## 🎨 Real-Time Console Logging

When you modify elements, you'll see detailed logs:

```
📊 [REAL-TIME] 3 changement(s) détecté(s):
  ✨ Créé: text-element-123
  📍 [PROPERTY] text-element-123 → x: 100 → 105 (number)
  🎨 [PROPERTY] text-element-123 → fillColor: "#000000" → "#FF6B6B" (string)
```

**Console Emoji Legend:**
- 📍 Position changes (x, y)
- 📏 Size changes (width, height)
- 🔄 Rotation changes
- 👁️ Visibility/opacity changes
- 🔒 Lock state changes
- 🎨 Color changes
- 📝 Text/font changes
- 🖼️ Image/media changes
- 📊 Table property changes
- ✨ Element creation
- 🗑️ Element deletion
- 🔧 Other property changes

## 💡 Use Cases

### 1. **Debug State Synchronization**

Track if Redux state updates are properly synced to canvas:

```javascript
// Check if element position change propagates
const before = CanvasMonitoringDashboard.getElementHistory("elem-1").length;
// ... move element ...
const after = CanvasMonitoringDashboard.getElementHistory("elem-1").length;
console.log(`Changes: ${after - before}`);
```

### 2. **Performance Analysis**

Monitor which properties change most frequently:

```javascript
const report = CanvasMonitoringDashboard.showDashboard();
// Check "TOP CHANGED PROPERTIES" section
```

### 3. **Regression Testing**

Compare before/after monitoring data:

```javascript
// Save baseline
const baseline = CanvasMonitoringDashboard.exportHistory();

// Make changes
// ... test scenario ...

// Compare with new data
const current = CanvasMonitoringDashboard.exportHistory();
```

### 4. **Property Flow Tracing**

See exactly when and how a property changes:

```javascript
// Get all fillColor changes
const colorChanges = CanvasMonitoringDashboard.getPropertyHistory("fillColor");
colorChanges.forEach(change => {
  console.log(`${change.elementId}: ${change.oldValue} → ${change.newValue}`);
});
```

## 🔧 API Reference

### `CanvasMonitoringDashboard`

| Method | Returns | Description |
|--------|---------|-------------|
| `initialize()` | void | Start monitoring (auto-called) |
| `showDashboard()` | void | Display formatted dashboard |
| `getHistory()` | PropertyChange[] | All changes |
| `getElementHistory(id)` | PropertyChange[] | Changes for element |
| `getPropertyHistory(prop)` | PropertyChange[] | Changes for property |
| `generateReport()` | string | Formatted text report |
| `exportHistory()` | string | JSON export |
| `clearHistory()` | void | Reset monitoring |

### `PropertyChange` Interface

```typescript
interface PropertyChange {
  elementId: string;           // Affected element ID
  property: string;            // Property name
  oldValue: unknown;          // Previous value
  newValue: unknown;          // New value
  timestamp: number;          // Unix timestamp
  changeType: 'created' | 'deleted' | 'updated' | 'property_changed';
}
```

## 📈 Real-Time Console Output

When Canvas renders, you'll see:

```
🔄 [EFFECT] useEffect de rendu déclenché (125), state.elements.length= 8
📊 [REAL-TIME] 2 changement(s) détecté(s):
  📍 [PROPERTY] text-1 → x: 100 → 105 (number)
  🎨 [PROPERTY] text-1 → fillColor: "#000000" → "#FF0000" (string)
✨ [CREATED] Element: image-2 (image) at (200, 300)
🔄 [CANVAS] Rendu complet terminé
```

## ⚠️ Performance Considerations

- Monitoring has **minimal performance impact** (uses efficient diff algorithm)
- Hash-based change detection vs JSON.stringify
- History limited to 500 most recent changes (configurable)
- Console logging can be toggled via dev tools filters

## 🚀 Advanced Debugging

### Filter History by Type

```javascript
const creates = CanvasMonitoringDashboard.getHistory()
  .filter(c => c.changeType === 'created');
const updates = CanvasMonitoringDashboard.getHistory()
  .filter(c => c.changeType === 'property_changed');
```

### Find When Property Changed

```javascript
CanvasMonitoringDashboard.getHistory()
  .filter(c => c.property === 'fillColor' && c.newValue === '#FF0000')
  .map(c => ({ ...c, time: new Date(c.timestamp).toLocaleTimeString() }))
```

### Monitor Specific Element in Real-Time

```javascript
// Listen to changes
elementChangeTracker.onChange(change => {
  if (change.elementId === 'your-element-id') {
    console.log(`${change.property} changed:`, change.oldValue, '→', change.newValue);
  }
});
```

## 📝 Notes

- **Auto-initialized** when Canvas component mounts
- **Global access** via `window.CanvasMonitoringDashboard`
- **No configuration needed** - works out of the box
- **Production-safe** - minimal overhead, only logs in development mode (configurable)

---

**Version:** 1.0.0  
**Last Updated:** 2025-01-01  
**Status:** Production Ready ✅
