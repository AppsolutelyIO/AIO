// Preview Manager - Handles preview modal functionality
import { pageBuilderService } from '../services/PageBuilderService';
import { notificationManager } from './NotificationManager';

export class PreviewManager {
    private modal: HTMLElement | null = null;
    private content: HTMLElement | null = null;
    private closeBtn: HTMLElement | null = null;
    private service: typeof pageBuilderService;

    constructor() {
        this.service = pageBuilderService;
        this.initializePreviewModal();
    }

    private initializePreviewModal(): void {
        this.modal = document.getElementById('preview-modal');
        this.content = document.getElementById('preview-content');
        this.closeBtn = document.getElementById('close-preview');

        if (!this.modal || !this.content || !this.closeBtn) {
            console.error('Preview modal elements not found');
            return;
        }

        this.setupEventListeners();
    }

    private setupEventListeners(): void {
        // Preview button
        const previewBtn = document.getElementById('preview-btn');
        if (previewBtn) {
            previewBtn.addEventListener('click', () => this.showPreview());
        }

        // Close button
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.hidePreview());
        }

        // Close on background click
        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.hidePreview();
                }
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isVisible()) {
                this.hidePreview();
            }
        });
    }

    public async showPreview(): Promise<void> {
        if (!this.modal || !this.content) return;

        try {
            const previewData = await this.service.getPreviewContent();

            this.content.innerHTML = `
        <style>${previewData.css}</style>
        <div class="max-w-full mx-auto p-5">${previewData.html}</div>
      `;

            this.modal.classList.remove('hidden');
            this.modal.setAttribute('aria-hidden', 'false');
            this.closeBtn?.focus();
            notificationManager.success('Preview loaded successfully');
        } catch (error) {
            console.error('Failed to generate preview:', error);
            notificationManager.error('Failed to generate preview');
        }
    }

    public hidePreview(): void {
        if (!this.modal) return;

        this.modal.classList.add('hidden');
        this.modal.setAttribute('aria-hidden', 'true');
    }

    public isVisible(): boolean {
        return this.modal ? !this.modal.classList.contains('hidden') : false;
    }

    public updatePreview(): void {
        if (this.isVisible()) {
            this.showPreview();
        }
    }

    public async getPreviewContent(): Promise<{ html: string; css: string }> {
        return await this.service.getPreviewContent();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new PreviewManager();
});
