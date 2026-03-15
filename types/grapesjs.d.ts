/**
 * GrapesJS Type Definitions
 * Type definitions for the GrapesJS editor library
 */

export interface GrapesJS {
    init(config: EditorConfig): Editor;
}

export interface Editor {
    BlockManager: BlockManager;
    DomComponents: DomComponents;
    UndoManager: UndoManager;
    getWrapper(): Component;
    getComponents(): Component[];
    getProjectData(): ProjectData;
    loadProjectData(data: ProjectData): void;
    getHtml(): string;
    getCss(): string;
    addComponents(html: string): void;
    setDevice(device: string): void;
    on(event: string, callback: (...args: unknown[]) => void): void;
}

export interface EditorConfig {
    container: string;
    fromElement?: boolean;
    height?: string;
    width?: string;
    storageManager?: boolean | StorageManagerConfig;
    canvas?: CanvasConfig;
    blockManager?: BlockManagerConfig;
    panels?: PanelsConfig;
    deviceManager?: DeviceManagerConfig;
}

export interface StorageManagerConfig {
    type?: string;
    autosave?: boolean;
    autoload?: boolean;
    stepsBeforeSave?: number;
}

export interface CanvasConfig {
    styles?: string[];
}

export interface BlockManagerConfig {
    appendTo?: string;
}

export interface PanelsConfig {
    defaults?: PanelConfig[];
}

export interface PanelConfig {
    id: string;
    el: string;
}

export interface DeviceManagerConfig {
    devices: DeviceConfig[];
}

export interface DeviceConfig {
    id: string;
    name: string;
    width: string;
    widthMedia?: string;
}

export interface BlockManager {
    add(id: string, block: BlockDefinition): void;
}

export interface BlockDefinition {
    label: string;
    category: BlockCategory;
    content: BlockContent;
    order?: number;
}

export interface BlockCategory {
    id: string;
    label: string;
}

export interface BlockContent {
    block_id: string;
    type: string;
    droppable: boolean;
}

export interface DomComponents {
    addType(type: string, component: ComponentDefinition): void;
    clear(): void;
}

export interface ComponentDefinition {
    model: {
        defaults: ComponentDefaults;
    };
}

export interface ComponentDefaults {
    tagName: string;
    content: string;
    [key: string]: unknown;
}

export interface Component {
    get(attribute: string): unknown;
    set(attribute: string, value: unknown): void;
    components(): Component[];
}

export interface UndoManager {
    undo(): void;
    redo(): void;
}

export interface ProjectData {
    [key: string]: unknown;
}

declare global {
    interface Window {
        grapesjs?: GrapesJS;
    }
}

export {};
