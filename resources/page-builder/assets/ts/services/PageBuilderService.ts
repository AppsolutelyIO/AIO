// Page Builder Service - Core functionality
import {
    attachDataSrcToSrcInDocument,
    makeLazyImagesVisibleInDocument,
    prepareLazyContentForEditor,
} from '../utils/prepareHtmlForEditor';

export class PageBuilderService {
    private editor: any;
    private blockRegistry: any[] = [];
    private blockRegistryPromise: Promise<void> | null = null;
    private initializationPromise: Promise<void>;

    constructor() {
        this.initializationPromise = this.initializeEditor();
    }

    private async initializeEditor(): Promise<void> {
        // Wait for GrapesJS to be available
        if (typeof window !== 'undefined' && (window as any).grapesjs) {
            this.setupEditor();
        } else {
            // Retry after a short delay
            await new Promise((resolve) => setTimeout(resolve, 100));
            return this.initializeEditor();
        }
    }

    private setupEditor(): void {
        const grapesjs = (window as any).grapesjs;

        this.editor = grapesjs.init({
            container: '#editor-canvas',
            fromElement: false,
            height: '100%',
            width: 'auto',
            storageManager: false,
            canvas: {
                styles: this.getThemedStyles(),
            },
            blockManager: {
                appendTo: '#blocks',
            },
            panels: {
                defaults: [
                    {
                        id: 'blocks',
                        el: '#blocks',
                    },
                ],
            },
            deviceManager: {
                devices: [
                    {
                        id: 'desktop',
                        name: 'Desktop',
                        width: '',
                    },
                    {
                        id: 'tablet',
                        name: 'Tablet',
                        width: '768px',
                        widthMedia: '992px',
                    },
                    {
                        id: 'mobile',
                        name: 'Mobile',
                        width: '320px',
                        widthMedia: '768px',
                    },
                ],
            },
        });

        this.setupEventListeners();
    }

    private selectedComponent: any = null;

    private setupEventListeners(): void {
        this.editor.on('load', () => {
            this.registerOpenConfigCommand();
            this.editor.on('component:remove', () => this.updateBlockCount());
            this.editor.on('component:add', (component: any) => {
                this.ensureComponentReference(component);
                this.updateBlockCount();
                this.attachDataSrcInCanvas();
            });
            this.editor.on('component:update', (component: any) => {
                this.ensureComponentReference(component);
                this.attachDataSrcInCanvas();
            });
            this.editor.on('component:selected', (component: any) => {
                this.selectedComponent = component;
                this.addConfigToolbarButton(component);
            });
            this.editor.on('component:deselected', () => {
                this.selectedComponent = null;
            });
        });
    }

    /** Register command that opens the block option modal (dispatches event for BlockOptionManager). */
    private registerOpenConfigCommand(): void {
        this.editor.Commands.add('tlb-open-config', () => {
            window.dispatchEvent(new Event('pagebuilder:open-block-option'));
        });
    }

    /**
     * Add "Open options" button to the component toolbar (first position) with icon and tooltip.
     * Fixes the empty first slot: show cog icon and hover tooltip.
     */
    private addConfigToolbarButton(component: any): void {
        const defaultToolbar = component.get('toolbar') || [];
        const configButton = {
            attributes: {
                class: 'fa fa-cog',
                title: 'Open options',
            },
            command: 'tlb-open-config',
        };
        const hasConfigBtn = defaultToolbar.some(
            (b: any) =>
                b.command === 'tlb-open-config' ||
                (b.attributes && b.attributes.class && b.attributes.class.includes('fa-cog')),
        );
        if (!hasConfigBtn) {
            component.set('toolbar', [configButton, ...defaultToolbar]);
        }
    }

    /** Get the editor instance (null until initialized). */
    public getEditor(): any {
        return this.editor ?? null;
    }

    /** Get the currently selected component in the canvas, if any. */
    public getSelectedComponent(): any {
        return this.selectedComponent ?? null;
    }

    /**
     * Attach src from data-src and make lazy images visible in the canvas iframe.
     * GrapesJS canvas renders in an iframe; lazy-loading JS does not run there.
     */
    private attachDataSrcInCanvas(): void {
        requestAnimationFrame(() => {
            const doc = this.editor.Canvas.getDocument?.() ?? this.editor.Canvas.getFrameEl()?.contentDocument;
            if (!doc) return;
            attachDataSrcToSrcInDocument(doc);
            makeLazyImagesVisibleInDocument(doc);
        });
    }

    private getThemedStyles(): string | string[] {
        // Use themed styles from window configuration (set by Blade template)
        // Array of CSS URLs for canvas iframe; GrapesJS injects as <link> tags
        return (window as any).pageBuilderConfig?.themedStyles || [];
    }

    public async loadBlockRegistry(): Promise<void> {
        if (this.blockRegistryPromise) {
            return this.blockRegistryPromise;
        }

        this.blockRegistryPromise = this.fetchBlockRegistry();
        return this.blockRegistryPromise;
    }

    private async fetchBlockRegistry(): Promise<void> {
        try {
            const blockRegistryUrl =
                (window as any).pageBuilderConfig?.blockRegistryUrl || '/admin/api/pages/block-registry';

            const response = await fetch(blockRegistryUrl);
            const result = await response.json();
            this.blockRegistry = result.data;

            this.registerBlocks();
        } catch (error) {
            console.error('Failed to load block registry:', error);
            this.blockRegistryPromise = null;
        }
    }

    private registerBlocks(): void {
        const blockManager = this.editor.BlockManager;
        const domComponents = this.editor.DomComponents;

        this.blockRegistry.forEach((category) => {
            const categoryId = category.title;
            const categoryLabel = category.label || category.title;

            category.blocks
                .sort((a: any, b: any) => a.sort - b.sort)
                .forEach((comp: any) => {
                    const {
                        id,
                        label,
                        type,
                        content = '<div></div>',
                        tagName = 'div',
                        description = '',
                        sort = 0,
                        droppable = false,
                    } = comp;

                    domComponents.addType(type, {
                        model: {
                            defaults: {
                                tagName,
                                content: prepareLazyContentForEditor(content),
                            },
                        },
                    });

                    blockManager.add(type, {
                        label: this.createBlockLabel(label, description),
                        category: {
                            id: categoryId,
                            label: categoryLabel,
                        },
                        content: {
                            block_id: id,
                            type,
                            droppable,
                        },
                        order: sort,
                    });
                });
        });
    }

    private createBlockLabel(label: string, description: string): string {
        return `
      <div class="flex items-start text-left">
        <div class="bg-gray-200 border-2 border-dashed rounded-xl w-16 h-16 mr-3"></div>
        <div class="flex-1">
          <strong class="text-base">${label}</strong>
          <div class="text-sm text-gray-500">${description}</div>
        </div>
      </div>
    `;
    }

    public async renderPageData(pageData: any): Promise<void> {
        // Wait for editor initialization to complete
        await this.initializationPromise;

        if (!this.editor) {
            console.error('Editor not initialized yet');
            return;
        }

        // Ensure block registry is loaded first
        if (this.blockRegistry.length === 0) {
            await this.loadBlockRegistry();
        }

        if (pageData) {
            const data = typeof pageData === 'string' ? JSON.parse(pageData) : pageData;
            this.editor.loadProjectData(data);
            this.updateBlockCount();
            this.attachDataSrcInCanvas();
        } else {
            const defaultHtml = `
        <h1 class="text-3xl mt-5 text-center">Hello, welcome to Page Builder<br/>Start dragging components from the right!</h1>
      `;
            this.editor.addComponents(defaultHtml);
        }
    }

    public async savePageData(): Promise<any> {
        // Wait for editor initialization to complete
        await this.initializationPromise;

        const projectData = this.editor.getProjectData();

        // Ensure all components have unique references
        const components = this.editor.getComponents();
        components.forEach((component: any) => {
            this.ensureComponentReference(component);
        });

        // Use the URL from window configuration (set by Blade template)
        const saveUrl = (window as any).pageBuilderConfig?.saveUrl || '/admin/api/pages/save';

        return fetch(saveUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCSRFToken(),
            },
            body: JSON.stringify({
                data: projectData,
            }),
        });
    }

    public async resetPageData(): Promise<any> {
        // Wait for editor initialization to complete
        await this.initializationPromise;

        this.editor.DomComponents.clear();
        const defaultHtml = `
      <h1 class="text-3xl mt-5 text-center">Hello, welcome to Page Builder<br/>Start dragging components from the right!</h1>
    `;
        this.editor.addComponents(defaultHtml);

        // Use the URL from window configuration (set by Blade template)
        const resetUrl = (window as any).pageBuilderConfig?.resetUrl || '/admin/api/pages/reset';

        return fetch(resetUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCSRFToken(),
            },
            body: JSON.stringify({}),
        });
    }

    public async getPreviewContent(): Promise<{ html: string; css: string }> {
        // Wait for editor initialization to complete
        await this.initializationPromise;

        return {
            html: prepareLazyContentForEditor(this.editor.getHtml()),
            css: this.editor.getCss(),
        };
    }

    private generateRandomId(type: string): string {
        const rand = crypto.getRandomValues(new Uint32Array(1))[0].toString(36);
        return `${type.toLowerCase()}-${rand}`;
    }

    private ensureComponentReference(component: any): void {
        if (!component.get('reference')) {
            component.set('reference', this.generateRandomId(component.get('type')));
        }
        // Ensure block_id and type are on attributes for option panel (getBlockOption / getSchemaFields)
        const attrs = component.get('attributes') || {};
        const type = component.get('type') || attrs.type;
        if (type && (attrs.block_id == null || attrs.block_id === undefined)) {
            const blockId = this.findBlockIdByType(type);
            if (blockId != null) {
                component.set('attributes', { ...attrs, block_id: blockId, type });
            }
        }
    }

    private findBlockIdByType(type: string): number | null {
        for (const category of this.blockRegistry) {
            const block = (category.blocks || []).find((b: any) => b.type === type);
            if (block?.id) return block.id;
        }
        return null;
    }

    /** Resolve block_id from block type (e.g. for config modal when component has no block_id yet). */
    public getBlockIdByType(type: string): number | null {
        return this.findBlockIdByType(type);
    }

    private updateBlockCount(): void {
        const wrapper = this.editor.getWrapper();
        if (!wrapper || typeof wrapper.components !== 'function') {
            return;
        }
        const components = wrapper.components();
        const countElement = document.getElementById('block-count');
        if (countElement) {
            countElement.textContent = components.length.toString();
        }
    }

    private getCSRFToken(): string {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') || '' : '';
    }

    public async undo(): Promise<void> {
        // Wait for editor initialization to complete
        await this.initializationPromise;
        this.editor.UndoManager.undo();
    }

    public async redo(): Promise<void> {
        // Wait for editor initialization to complete
        await this.initializationPromise;
        this.editor.UndoManager.redo();
    }

    public async setDevice(device: string): Promise<void> {
        // Wait for editor initialization to complete
        await this.initializationPromise;
        this.editor.setDevice(device);
    }
}

// Export singleton instance
export const pageBuilderService = new PageBuilderService();

// Make it available globally
if (typeof window !== 'undefined') {
    (window as any).pageBuilderService = pageBuilderService;
}
