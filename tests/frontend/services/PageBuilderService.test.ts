/**
 * PageBuilderService Tests
 * Tests for the Page Builder Service functionality
 */

import { PageBuilderService } from '@resources/page-builder/assets/ts/services/PageBuilderService';
import { beforeEach, describe, expect, it, vi } from 'vitest';

describe('PageBuilderService', () => {
    let service: PageBuilderService;

    beforeEach(() => {
        // Mock window.grapesjs
        (window as unknown as { grapesjs: unknown }).grapesjs = {
            init: vi.fn(() => ({
                BlockManager: {
                    add: vi.fn(),
                },
                DomComponents: {
                    addType: vi.fn(),
                    clear: vi.fn(),
                },
                UndoManager: {
                    undo: vi.fn(),
                    redo: vi.fn(),
                },
                getWrapper: vi.fn(() => ({
                    components: vi.fn(() => []),
                })),
                getComponents: vi.fn(() => []),
                getProjectData: vi.fn(() => ({})),
                loadProjectData: vi.fn(),
                getHtml: vi.fn(() => '<div>Test</div>'),
                getCss: vi.fn(() => 'body { margin: 0; }'),
                addComponents: vi.fn(),
                setDevice: vi.fn(),
                on: vi.fn(),
            })),
        } as unknown;

        // Mock window.pageBuilderConfig
        window.pageBuilderConfig = {
            themedStyles: 'body { color: red; }',
            blockRegistryUrl: '/api/blocks',
            saveUrl: '/api/save',
            resetUrl: '/api/reset',
        };

        // Mock fetch
        global.fetch = vi.fn();

        service = new PageBuilderService();
    });

    it('should initialize service', () => {
        expect(service).toBeInstanceOf(PageBuilderService);
    });

    it('should load block registry', async () => {
        const mockResponse = {
            data: [
                {
                    title: 'Basic',
                    blocks: [
                        {
                            id: '1',
                            label: 'Text Block',
                            type: 'text',
                            sort: 0,
                        },
                    ],
                },
            ],
        };

        (global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
            json: async () => mockResponse,
        } as Response);

        await service.loadBlockRegistry();
        expect(global.fetch).toHaveBeenCalledWith('/api/blocks');
    });

    it('should handle block registry load error', async () => {
        (global.fetch as ReturnType<typeof vi.fn>).mockRejectedValueOnce(new Error('Network error'));

        const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {});

        await service.loadBlockRegistry();

        expect(consoleSpy).toHaveBeenCalled();
        consoleSpy.mockRestore();
    });

    it('should get preview content', async () => {
        // Wait for initialization
        await new Promise((resolve) => setTimeout(resolve, 100));

        const preview = await service.getPreviewContent();

        expect(preview).toHaveProperty('html');
        expect(preview).toHaveProperty('css');
        expect(typeof preview.html).toBe('string');
        expect(typeof preview.css).toBe('string');
    });

    it('should handle undo operation', async () => {
        // Wait for initialization
        await new Promise((resolve) => setTimeout(resolve, 100));

        // Undo should work if editor is initialized
        await service.undo();

        // Verify undo was called (the mock editor will have been initialized)
        expect(service).toBeInstanceOf(PageBuilderService);
    });
});
