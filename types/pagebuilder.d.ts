/**
 * Page Builder Type Definitions
 * Type definitions for the Page Builder system
 */

export interface PageBuilderConfig {
    themedStyles?: string | string[];
    blockRegistryUrl?: string;
    blockConfigUrl?: string;
    blockHtmlUrl?: string;
    renderBlockUrl?: string;
    saveUrl?: string;
    resetUrl?: string;
    dataUrl?: string;
}

export interface BlockRegistryCategory {
    title: string;
    label?: string;
    blocks: BlockDefinition[];
}

export interface BlockDefinition {
    id: string;
    label: string;
    type: string;
    content?: string;
    tagName?: string;
    description?: string;
    sort?: number;
    droppable?: boolean;
    schema?: BlockSchema;
}

export interface BlockSchema {
    [key: string]: SchemaField;
}

export interface SchemaField {
    type: 'text' | 'textarea' | 'select' | 'number' | 'boolean' | 'color';
    label?: string;
    placeholder?: string;
    default?: string | number | boolean;
    options?: SchemaOption[];
}

export interface SchemaOption {
    value: string | number;
    label?: string;
}

export interface BlockConfig {
    [key: string]: string | number | boolean;
}

export interface PreviewContent {
    html: string;
    css: string;
}

declare global {
    interface Window {
        pageBuilderData?: string;
        pageBuilderConfig?: PageBuilderConfig;
        pageBuilderService?: PageBuilderService;
    }
}

export interface PageBuilderService {
    loadBlockRegistry(): Promise<void>;
    renderPageData(pageData: string | null): Promise<void>;
    savePageData(): Promise<Response>;
    resetPageData(): Promise<Response>;
    getPreviewContent(): Promise<PreviewContent>;
    undo(): Promise<void>;
    redo(): Promise<void>;
    setDevice(device: string): Promise<void>;
}

export {};
