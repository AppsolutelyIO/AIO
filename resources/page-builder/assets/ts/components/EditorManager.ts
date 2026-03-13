// Editor Manager - Handles editor initialization and events
import { pageBuilderService } from '../services/PageBuilderService';

export class EditorManager {
    private service: typeof pageBuilderService;

    constructor() {
        this.service = pageBuilderService;
        this.initializeEventListeners();
    }

    private initializeEventListeners(): void {
        // Device buttons
        document.querySelectorAll('.device-btn').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                const target = e.target as HTMLElement;
                const device = target.dataset.device;
                if (device) {
                    this.setActiveDevice(target);
                    this.service.setDevice(device);
                }
            });
        });

        // Undo/Redo buttons
        const undoBtn = document.getElementById('undo-btn');
        const redoBtn = document.getElementById('redo-btn');

        if (undoBtn) {
            undoBtn.addEventListener('click', () => this.service.undo());
        }

        if (redoBtn) {
            redoBtn.addEventListener('click', () => this.service.redo());
        }

        // Reset button
        const resetBtn = document.getElementById('reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.handleReset());
        }

        // Save button
        const saveBtn = document.getElementById('save-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.handleSave());
        }

        // Save config button
        const saveConfigBtn = document.getElementById('save-config-btn');
        if (saveConfigBtn) {
            saveConfigBtn.addEventListener('click', () => this.handleSaveConfig());
        }
    }

    private setActiveDevice(activeBtn: HTMLElement): void {
        document.querySelectorAll('.device-btn').forEach((btn) => {
            btn.classList.remove('active');
        });
        activeBtn.classList.add('active');
    }

    private async handleReset(): Promise<void> {
        if (confirm('Are you sure you want to reset the page content? This cannot be undone.')) {
            try {
                const response = await this.service.resetPageData();
                const data = await response.json();

                if (response.ok) {
                    this.showNotification(data.message || 'Page content has been reset.', true);
                } else {
                    throw new Error('Failed to reset');
                }
            } catch (error) {
                console.error('Reset failed:', error);
                this.showNotification('Reset failed ❌', false);
            }
        }
    }

    private async handleSave(): Promise<void> {
        try {
            const response = await this.service.savePageData();
            const data = await response.json();

            if (response.ok) {
                this.showNotification(data.message || 'Saved successfully ✅', true);
            } else {
                throw new Error('Failed to save');
            }
        } catch (error) {
            console.error('Save failed:', error);
            this.showNotification('Save failed ❌', false);
        }
    }

    private async handleSaveConfig(): Promise<void> {
        try {
            // Use the URL from window configuration (set by Blade template)
            const dataUrl = (window as any).pageBuilderConfig?.dataUrl || '/admin/api/pages/data';
            const response = await fetch(dataUrl);
            const result = await response.json();
            const content = result.data.page.content;
            await this.service.renderPageData(content);
        } catch (error) {
            console.error('Failed to load config:', error);
            this.showNotification('Failed to load config ❌', false);
        }
    }

    private showNotification(message: string, isSuccess: boolean): void {
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.className = `notification ${isSuccess ? 'success' : 'error'}`;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new EditorManager();
});
