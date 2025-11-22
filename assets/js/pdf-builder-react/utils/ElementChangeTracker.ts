/**
 * 🔍 REAL-TIME ELEMENT & PROPERTY CHANGE TRACKER
 * 
 * Tracks all element changes, property modifications, and canvas updates
 * in real-time with detailed logging and diff detection.
 */

import type { Element } from '../types/elements';

interface ElementSnapshot {
  id: string;
  type: string;
  x: number;
  y: number;
  width: number;
  height: number;
  visible: boolean;
  locked: boolean;
  [key: string]: unknown;
  timestamp: number;
}

interface PropertyChange {
  elementId: string;
  property: string;
  oldValue: unknown;
  newValue: unknown;
  timestamp: number;
  changeType: 'created' | 'updated' | 'deleted' | 'property_changed';
}

export class ElementChangeTracker {
  private previousSnapshots: Map<string, ElementSnapshot> = new Map();
  private changeHistory: PropertyChange[] = [];
  private maxHistorySize = 500;
  private listeners: ((change: PropertyChange) => void)[] = [];
  public debugEnabled = false; // Set to true to enable logging

  /**
   * Track element state at a specific point in time
   */
  public trackElements(elements: Element[]): PropertyChange[] {
    const currentSnapshots = new Map<string, ElementSnapshot>();
    const changes: PropertyChange[] = [];

    elements.forEach((element: Element) => {
      const snapshot: ElementSnapshot = {
        ...element,
        timestamp: Date.now()
      } as ElementSnapshot;

      currentSnapshots.set(element.id as string, snapshot);

      const previousSnapshot = this.previousSnapshots.get(element.id as string);

      if (!previousSnapshot) {
        // Element created
        changes.push({
          elementId: element.id as string,
          property: '__created__',
          oldValue: undefined,
          newValue: snapshot,
          timestamp: snapshot.timestamp,
          changeType: 'created'
        });

      } else {
        // Check for property changes
        this.detectPropertyChanges(previousSnapshot, snapshot, changes);
      }
    });

    // Check for deleted elements
    this.previousSnapshots.forEach((snapshot, elementId) => {
      if (!currentSnapshots.has(elementId)) {
        changes.push({
          elementId,
          property: '__deleted__',
          oldValue: snapshot,
          newValue: undefined,
          timestamp: Date.now(),
          changeType: 'deleted'
        });

      }
    });

    this.previousSnapshots = currentSnapshots;
    this.addChangesToHistory(changes);
    this.notifyListeners(changes);

    return changes;
  }

  /**
   * Detect all property changes between two snapshots
   */
  private detectPropertyChanges(
    previous: ElementSnapshot,
    current: ElementSnapshot,
    changes: PropertyChange[]
  ): void {
    const allKeys = new Set([
      ...Object.keys(previous),
      ...Object.keys(current)
    ]);

    allKeys.forEach(key => {
      if (key === 'timestamp') return;

      const oldValue = previous[key];
      const newValue = current[key];

      // Deep comparison for objects/arrays
      if (JSON.stringify(oldValue) !== JSON.stringify(newValue)) {
        changes.push({
          elementId: previous.id,
          property: key,
          oldValue,
          newValue,
          timestamp: current.timestamp,
          changeType: 'property_changed'
        });

      }
    });
  }

  /**
   * Get emoji for different property types
   */
  private getPropertyEmoji(property: string): string {
    const emojiMap: { [key: string]: string } = {
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
      text: '📄',
      src: '🖼️',
      url: '🔗',
      backgroundColor: '🎨',
      textColor: '🎨',
      borderColor: '🖌️',
      borderRadius: '🔲',
      showHeaders: '📊',
      showBorders: '📊',
      showAlternatingRows: '📊'
    };

    return emojiMap[property] || '🔧';
  }

  /**
   * Determine the type of property
   */
  private getPropertyType(value: unknown): string {
    if (value === null) return 'null';
    if (Array.isArray(value)) return `array[${value.length}]`;
    if (typeof value === 'object')
      return `object{${Object.keys(value as Record<string, unknown>).length}}`;
    return typeof value;
  }

  /**
   * Format value for logging
   */
  private formatValue(value: unknown, maxLength = 50): string {
    if (value === undefined) return 'undefined';
    if (value === null) return 'null';
    if (typeof value === 'boolean') return value ? '✓ true' : '✗ false';
    if (typeof value === 'number') return value.toFixed(2);
    if (typeof value === 'string') {
      const truncated =
        value.length > maxLength ? value.slice(0, maxLength) + '...' : value;
      return `"${truncated}"`;
    }
    if (Array.isArray(value))
      return `[${value.length} items]`;
    if (typeof value === 'object')
      return `{${Object.keys(value as Record<string, unknown>).length} props}`;
    return String(value);
  }

  /**
   * Add changes to history with size limit
   */
  private addChangesToHistory(changes: PropertyChange[]): void {
    this.changeHistory.push(...changes);

    if (this.changeHistory.length > this.maxHistorySize) {
      this.changeHistory = this.changeHistory.slice(-this.maxHistorySize);
    }
  }

  /**
   * Notify all registered listeners
   */
  private notifyListeners(changes: PropertyChange[]): void {
    changes.forEach(change => {
      this.listeners.forEach(listener => listener(change));
    });
  }

  /**
   * Subscribe to changes
   */
  public onChange(
    callback: (change: PropertyChange) => void
  ): () => void {
    this.listeners.push(callback);
    return () => {
      this.listeners = this.listeners.filter(l => l !== callback);
    };
  }

  /**
   * Get change history
   */
  public getHistory(): PropertyChange[] {
    return [...this.changeHistory];
  }

  /**
   * Get changes for a specific element
   */
  public getElementHistory(elementId: string): PropertyChange[] {
    return this.changeHistory.filter(c => c.elementId === elementId);
  }

  /**
   * Get all changes for a specific property
   */
  public getPropertyHistory(property: string): PropertyChange[] {
    return this.changeHistory.filter(
      c => c.property === property || c.changeType === 'created'
    );
  }

  /**
   * Get changes within a time range
   */
  public getChangesBetween(
    startTime: number,
    endTime: number
  ): PropertyChange[] {
    return this.changeHistory.filter(
      c => c.timestamp >= startTime && c.timestamp <= endTime
    );
  }

  /**
   * Reset history (but keep current snapshots)
   */
  public clearHistory(): void {
    this.changeHistory = [];
  }

  /**
   * Get current snapshots
   */
  public getSnapshots(): Map<string, ElementSnapshot> {
    return new Map(this.previousSnapshots);
  }

  /**
   * Generate a detailed report
   */
  public generateReport(): string {
    const totalChanges = this.changeHistory.length;
    const elementsTracked = this.previousSnapshots.size;

    const changeSummary = this.changeHistory.reduce(
      (acc, change) => {
        acc[change.changeType] = (acc[change.changeType] || 0) + 1;
        return acc;
      },
      {} as Record<string, number>
    );

    return `
📊 ELEMENT TRACKER REPORT
========================
Total Changes: ${totalChanges}
Elements Tracked: ${elementsTracked}
Change Breakdown:
  - Created: ${changeSummary.created || 0}
  - Updated: ${changeSummary.updated || 0}
  - Deleted: ${changeSummary.deleted || 0}
  - Property Changes: ${changeSummary.property_changed || 0}
========================
    `.trim();
  }
}

// Export singleton instance
export const elementChangeTracker = new ElementChangeTracker();
