// Page Builder Main TypeScript Entry Point

// External Dependencies
import grapesjs from 'grapesjs';

// Make GrapesJS available globally
(window as any).grapesjs = grapesjs;

// Page Builder Components
import './services/PageBuilderService';
import './components/EditorManager';
import './components/BlockManager';
import './components/BlockOptionManager';
import './components/NotificationManager';
import './components/DeviceManager';
import './components/PreviewManager';
import './components/ThemeSyncManager';

// Initialize Page Builder when DOM is ready
document.addEventListener('DOMContentLoaded', async () => {
    // Initialize with data from window object
    if (window.pageBuilderData && window.pageBuilderConfig) {
        const service = (window as any).pageBuilderService;
        if (service) {
            // renderPageData handles loading block registry first
            await service.renderPageData(window.pageBuilderData);

            // After rendering, check if theme sync is needed (empty canvas)
            window.dispatchEvent(new Event('pagebuilder:check-theme-sync'));
        }
    }
});
