/**
 * 📚 EXAMPLES - Real-Time Element Monitoring
 * 
 * ⚠️ NOTE: This file contains examples meant to be COPIED AND PASTED
 * into your browser's developer console (F12) while using the PDF Builder.
 * 
 * These are NOT meant to be imported as code - they're console snippets!
 * 
 * Copy any section below and paste into your console to run.
 */

/* eslint-disable */

// Display full monitoring dashboard
CanvasMonitoringDashboard.showDashboard()


// ============================================
// 2. INSPECT ELEMENT CHANGES
// ============================================

// Get all changes for a specific element
const elementId = "your-element-id-here";
const elementHistory = CanvasMonitoringDashboard.getElementHistory(elementId);
console.table(elementHistory);

// See how many times this element changed
console.log(`Element ${elementId} changed ${elementHistory.length} times`);


// ============================================
// 3. TRACK PROPERTY CHANGES
// ============================================

// Track all position changes
const positionChanges = CanvasMonitoringDashboard.getPropertyHistory("x")
  .concat(CanvasMonitoringDashboard.getPropertyHistory("y"));
console.log(`Position changed ${positionChanges.length} times`);

// Track all color changes
const colorChanges = CanvasMonitoringDashboard.getPropertyHistory("fillColor");
console.log("All color changes:");
colorChanges.forEach(change => {
  console.log(`  ${change.elementId}: ${change.oldValue} → ${change.newValue}`);
});

// Track all text changes
const textChanges = CanvasMonitoringDashboard.getPropertyHistory("text");
console.log("Text changes:", textChanges.length);


// ============================================
// 4. PERFORMANCE ANALYSIS
// ============================================

// Get detailed report
console.log(CanvasMonitoringDashboard.generateReport());

// Most changed elements
const history = CanvasMonitoringDashboard.getHistory();
const elementCounts = {};
history.forEach(change => {
  elementCounts[change.elementId] = (elementCounts[change.elementId] || 0) + 1;
});
const sorted = Object.entries(elementCounts).sort((a, b) => b[1] - a[1]);
console.log("Most changed elements:", sorted.slice(0, 5));

// Most frequently changed properties
const propertyCounts = {};
history.forEach(change => {
  if (change.changeType === 'property_changed') {
    propertyCounts[change.property] = (propertyCounts[change.property] || 0) + 1;
  }
});
const propsSort = Object.entries(propertyCounts).sort((a, b) => b[1] - a[1]);
console.log("Most changed properties:", propsSort.slice(0, 5));


// ============================================
// 5. TIME-BASED ANALYSIS
// ============================================

// Get changes from last 5 seconds
const now = Date.now();
const recentChanges = elementChangeTracker.getChangesBetween(now - 5000, now);
console.log(`Changes in last 5 seconds: ${recentChanges.length}`);

// Get first 10 changes of the session
const firstChanges = CanvasMonitoringDashboard.getHistory().slice(0, 10);
console.table(firstChanges);

// Get last 10 changes
const lastChanges = CanvasMonitoringDashboard.getHistory().slice(-10);
console.table(lastChanges);


// ============================================
// 6. EXPORT DATA
// ============================================

// Export all data as JSON
const exportedData = CanvasMonitoringDashboard.exportHistory();
console.log(exportedData);

// Save to clipboard (then paste to file)
copy(exportedData);
console.log("✅ Data copied to clipboard!");

// Or stringify for sharing
const jsonString = JSON.stringify(
  CanvasMonitoringDashboard.getHistory(),
  null,
  2
);
console.log(jsonString);


// ============================================
// 7. REAL-TIME MONITORING
// ============================================

// Listen to all changes in real-time
elementChangeTracker.onChange(change => {
  const emoji = {
    created: '✨',
    deleted: '🗑️',
    property_changed: '🔧'
  }[change.changeType] || '❓';
  
  console.log(`${emoji} [${change.changeType}] ${change.elementId}.${change.property}`);
});

// Listen only to specific element
elementChangeTracker.onChange(change => {
  if (change.elementId === "text-element-1") {
    console.log(`Text element 1 changed:`, change);
  }
});

// Listen only to color changes
elementChangeTracker.onChange(change => {
  if (change.property === "fillColor") {
    console.log(
      `🎨 ${change.elementId} color: ${change.oldValue} → ${change.newValue}`
    );
  }
});


// ============================================
// 8. DEBUGGING
// ============================================

// Find when a specific element was created
const creations = CanvasMonitoringDashboard.getHistory()
  .filter(c => c.changeType === 'created' && c.elementId === "your-element-id");
if (creations.length > 0) {
  const created = new Date(creations[0].timestamp);
  console.log(`Element created at: ${created.toLocaleTimeString()}`);
}

// Find all property changes for specific element
const allProps = CanvasMonitoringDashboard.getElementHistory("your-element-id")
  .filter(c => c.changeType === 'property_changed')
  .map(c => ({
    property: c.property,
    old: c.oldValue,
    new: c.newValue,
    time: new Date(c.timestamp).toLocaleTimeString()
  }));
console.table(allProps);

// Check if element was deleted
const deletions = CanvasMonitoringDashboard.getHistory()
  .filter(c => c.changeType === 'deleted' && c.elementId === "your-element-id");
if (deletions.length > 0) {
  console.log("Element was deleted!");
  console.log(deletions[0]);
}


// ============================================
// 9. STATE VERIFICATION
// ============================================

// Verify state synchronization
const currentSnapshots = elementChangeTracker.getSnapshots();
console.log(`${currentSnapshots.size} elements in tracking state`);

// Check specific element's current state
const snapshot = currentSnapshots.get("element-id");
console.log("Current element state:", snapshot);

// List all tracked elements
currentSnapshots.forEach((snapshot, elementId) => {
  console.log(`📦 ${elementId}: (${snapshot.x}, ${snapshot.y}) ${snapshot.width}×${snapshot.height}`);
});


// ============================================
// 10. RESET & CLEAR
// ============================================

// Clear history to start fresh test
CanvasMonitoringDashboard.clearHistory();
console.log("✅ Monitoring history cleared");

// Then try your test scenario
// ... make some element changes ...

// View results
CanvasMonitoringDashboard.showDashboard();


// ============================================
// BONUS: CUSTOM ANALYSIS FUNCTIONS
// ============================================

/**
 * Find all changes for a specific element within a time range
 */
function analyzeElementInTimeRange(elementId, startTime, endTime) {
  return CanvasMonitoringDashboard.getHistory()
    .filter(c => 
      c.elementId === elementId && 
      c.timestamp >= startTime && 
      c.timestamp <= endTime
    );
}

// Usage:
const oneMinuteAgo = Date.now() - 60000;
const changes = analyzeElementInTimeRange("element-1", oneMinuteAgo, Date.now());
console.log(`Changes in last minute: ${changes.length}`);


/**
 * Generate statistics about property changes
 */
function analyzePropertyChanges(propertyName) {
  const changes = CanvasMonitoringDashboard.getPropertyHistory(propertyName);
  const uniqueElements = new Set(changes.map(c => c.elementId)).size;
  const uniqueValues = new Set(changes.map(c => c.newValue)).size;
  
  return {
    totalChanges: changes.length,
    uniqueElements,
    uniqueValues,
    elements: Array.from(new Set(changes.map(c => c.elementId))),
    values: Array.from(new Set(changes.map(c => c.newValue)))
  };
}

// Usage:
console.log("Color analysis:", analyzePropertyChanges("fillColor"));
console.log("Position analysis:", analyzePropertyChanges("x"));


/**
 * Monitor performance: which operations cause the most changes
 */
function identifyExpensiveOperations(maxChanges = 5) {
  const history = CanvasMonitoringDashboard.getHistory();
  const operations = [];
  
  // Group by 100ms windows
  let currentBatch = [];
  let lastTime = 0;
  
  history.forEach((change, i) => {
    if (change.timestamp - lastTime > 100) {
      if (currentBatch.length > 0) {
        operations.push({
          time: new Date(lastTime).toLocaleTimeString(),
          changes: currentBatch.length,
          properties: [...new Set(currentBatch.map(c => c.property))]
        });
      }
      currentBatch = [change];
      lastTime = change.timestamp;
    } else {
      currentBatch.push(change);
    }
  });
  
  return operations
    .sort((a, b) => b.changes - a.changes)
    .slice(0, maxChanges);
}

// Usage:
console.table(identifyExpensiveOperations());


// ============================================
// TIPS & TRICKS
// ============================================

// Tip 1: Use console.table() for better formatting
console.table(CanvasMonitoringDashboard.getHistory().slice(-5));

// Tip 2: Filter and count
const textElements = CanvasMonitoringDashboard.getHistory()
  .filter(c => c.elementId.includes("text"))
  .length;
console.log(`Text element changes: ${textElements}`);

// Tip 3: Compare states before/after
const before = CanvasMonitoringDashboard.getHistory().length;
// ... make changes ...
const after = CanvasMonitoringDashboard.getHistory().length;
console.log(`Changes made: ${after - before}`);

// Tip 4: Export and paste into Excel/Sheets
const csv = CanvasMonitoringDashboard.getHistory()
  .map(c => `${c.elementId},${c.property},${c.oldValue},${c.newValue}`)
  .join('\n');
console.log(csv);

// Tip 5: Search within history
const myChanges = CanvasMonitoringDashboard.getHistory()
  .filter(c => JSON.stringify(c).includes("search-term"));
console.log(myChanges);

