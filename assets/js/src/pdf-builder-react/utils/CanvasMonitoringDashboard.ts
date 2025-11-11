/**
 * 🎯 CANVAS MONITORING DASHBOARD
 * 
 * Provides real-time monitoring, visualization, and debugging tools
 * for element changes, renders, and performance metrics.
 */

import { elementChangeTracker } from './ElementChangeTracker';

export class CanvasMonitoringDashboard {
  private static monitoringEnabled = false;
  private static startTime: number = 0;
  private static renderCount: number = 0;
  private static elementChanges: number = 0;

  /**
   * Initialize and start monitoring
   */
  static initialize(): void {
    this.monitoringEnabled = true;
    this.startTime = Date.now();
    // Silent initialization - dashboard is available via showDashboard()
  }

  /**
   * Increment render counter
   */
  static recordRender(): void {
    if (this.monitoringEnabled) {
      this.renderCount++;
    }
  }

  /**
   * Record element changes
   */
  static recordChanges(count: number): void {
    if (this.monitoringEnabled) {
      this.elementChanges += count;
    }
  }

  /**
   * Display comprehensive dashboard
   */
  static showDashboard(): void {
    const uptime = Date.now() - this.startTime;
    const history = elementChangeTracker.getHistory();
    const snapshots = elementChangeTracker.getSnapshots();

    // Count changes by type
    const changeBreakdown = history.reduce(
      (acc, change) => {
        acc[change.changeType] = (acc[change.changeType] || 0) + 1;
        return acc;
      },
      {} as Record<string, number>
    );

    // Count changes by property
    const propertyChanges = history.reduce(
      (acc, change) => {
        if (change.changeType === 'property_changed') {
          acc[change.property] = (acc[change.property] || 0) + 1;
        }
        return acc;
      },
      {} as Record<string, number>
    );

    // Count changes by element
    const elementChanges = history.reduce(
      (acc, change) => {
        acc[change.elementId] = (acc[change.elementId] || 0) + 1;
        return acc;
      },
      {} as Record<string, number>
    );

    console.clear();
    console.log('%c🎯 CANVAS MONITORING DASHBOARD', 'font-size: 20px; font-weight: bold; color: #2196F3;');
    console.log('%c═══════════════════════════════════════', 'font-size: 14px; color: #2196F3;');

    // Session Stats
    console.log('%c📊 SESSION STATISTICS', 'font-size: 14px; font-weight: bold; color: #4CAF50;');
    console.log(`  Uptime: ${(uptime / 1000).toFixed(2)}s`);
    console.log(`  Total Renders: ${this.renderCount}`);
    console.log(`  Total Changes: ${history.length}`);
    console.log(`  Elements Tracked: ${snapshots.size}`);

    // Change Breakdown
    if (Object.keys(changeBreakdown).length > 0) {
      console.log('%c🔄 CHANGE BREAKDOWN', 'font-size: 14px; font-weight: bold; color: #FF9800;');
      Object.entries(changeBreakdown).forEach(([type, count]) => {
        const emoji = this.getChangeTypeEmoji(type);
        console.log(`  ${emoji} ${type}: ${count}`);
      });
    }

    // Top Changed Properties
    if (Object.keys(propertyChanges).length > 0) {
      console.log('%c🔧 TOP CHANGED PROPERTIES', 'font-size: 14px; font-weight: bold; color: #9C27B0;');
      const topProps = Object.entries(propertyChanges)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5);
      topProps.forEach(([prop, count]) => {
        const emoji = this.getPropertyEmoji(prop);
        console.log(`  ${emoji} ${prop}: ${count} changes`);
      });
    }

    // Most Changed Elements
    if (Object.keys(elementChanges).length > 0) {
      console.log('%c🎨 MOST CHANGED ELEMENTS', 'font-size: 14px; font-weight: bold; color: #F44336;');
      const topElements = Object.entries(elementChanges)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5);
      topElements.forEach(([elementId, count]) => {
        console.log(`  📦 ${elementId}: ${count} changes`);
      });
    }

    // Current Snapshots
    if (snapshots.size > 0) {
      console.log('%c📸 CURRENT ELEMENT SNAPSHOTS', 'font-size: 14px; font-weight: bold; color: #00BCD4;');
      Array.from(snapshots.values()).slice(0, 3).forEach(snapshot => {
        console.log(`  ${snapshot.id} (${snapshot.type}): (${snapshot.x}, ${snapshot.y}) ${snapshot.width}×${snapshot.height}`);
      });
      if (snapshots.size > 3) {
        console.log(`  ... and ${snapshots.size - 3} more elements`);
      }
    }

    console.log('%c═══════════════════════════════════════', 'font-size: 14px; color: #2196F3;');
    console.log('%cCommands:', 'font-weight: bold;');
    console.log('  - CanvasMonitoringDashboard.getHistory()');
    console.log('  - CanvasMonitoringDashboard.getElementHistory("elementId")');
    console.log('  - CanvasMonitoringDashboard.getPropertyHistory("propertyName")');
    console.log('  - CanvasMonitoringDashboard.generateReport()');
  }

  /**
   * Get formatted history
   */
  static getHistory() {
    return elementChangeTracker.getHistory();
  }

  /**
   * Get history for specific element
   */
  static getElementHistory(elementId: string) {
    return elementChangeTracker.getElementHistory(elementId);
  }

  /**
   * Get history for specific property
   */
  static getPropertyHistory(property: string) {
    return elementChangeTracker.getPropertyHistory(property);
  }

  /**
   * Generate detailed report
   */
  static generateReport(): string {
    const history = elementChangeTracker.getHistory();
    const snapshots = elementChangeTracker.getSnapshots();

    let report = '\n📋 DETAILED MONITORING REPORT\n';
    report += '═══════════════════════════════════════\n';
    report += `Total Events: ${history.length}\n`;
    report += `Elements: ${snapshots.size}\n\n`;

    // Recent changes
    report += 'Recent Changes (last 10):\n';
    history.slice(-10).forEach(change => {
      const time = new Date(change.timestamp).toLocaleTimeString();
      if (change.changeType === 'created') {
        report += `  [${time}] ✨ Created: ${change.elementId}\n`;
      } else if (change.changeType === 'deleted') {
        report += `  [${time}] 🗑️ Deleted: ${change.elementId}\n`;
      } else {
        report += `  [${time}] 🔧 ${change.elementId}.${change.property}: ${change.oldValue} → ${change.newValue}\n`;
      }
    });

    return report;
  }

  /**
   * Get emoji for change type
   */
  private static getChangeTypeEmoji(type: string): string {
    const emojiMap: Record<string, string> = {
      created: '✨',
      deleted: '🗑️',
      updated: '🔄',
      property_changed: '🔧'
    };
    return emojiMap[type] || '❓';
  }

  /**
   * Get emoji for property
   */
  private static getPropertyEmoji(prop: string): string {
    const emojiMap: Record<string, string> = {
      x: '📍',
      y: '📍',
      width: '📏',
      height: '📏',
      rotation: '🔄',
      opacity: '👁️',
      visible: '👁️',
      locked: '🔒',
      color: '🎨',
      fillColor: '🎨',
      strokeColor: '🖌️',
      fontSize: '📝',
      fontFamily: '📝',
      text: '📄'
    };
    return emojiMap[prop] || '🔧';
  }

  /**
   * Export history as JSON
   */
  static exportHistory(): string {
    return JSON.stringify(
      {
        timestamp: new Date().toISOString(),
        history: elementChangeTracker.getHistory(),
        snapshots: Array.from(elementChangeTracker.getSnapshots().values())
      },
      null,
      2
    );
  }

  /**
   * Clear all history
   */
  static clearHistory(): void {
    elementChangeTracker.clearHistory();
    this.renderCount = 0;
    this.elementChanges = 0;
    console.log('🧹 Monitoring history cleared');
  }
}

// Make available globally for console debugging
declare global {
  interface Window {
    CanvasMonitoringDashboard: typeof CanvasMonitoringDashboard;
  }
}

if (typeof window !== 'undefined') {
  (window as unknown as Record<string, unknown>).CanvasMonitoringDashboard = CanvasMonitoringDashboard;
}
