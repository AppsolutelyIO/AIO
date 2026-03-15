// Theme Sync Manager - Handles cross-theme block synchronization dialog
import { pageBuilderService } from '../services/PageBuilderService';

interface ThemeSyncOption {
    theme: string;
    block_count: number;
}

interface ThemeSyncResponse {
    current_theme: string;
    options: ThemeSyncOption[];
}

export class ThemeSyncManager {
    private dialog: HTMLDialogElement | null = null;

    constructor() {
        this.listenForSyncCheck();
    }

    private listenForSyncCheck(): void {
        window.addEventListener('pagebuilder:check-theme-sync', () => {
            this.checkAndPrompt();
        });
    }

    /**
     * Check if the current page has blocks for the active theme.
     * If not, fetch available themes and show the sync dialog.
     */
    public async checkAndPrompt(): Promise<void> {
        const config = (window as any).pageBuilderConfig;
        const themeSyncUrl = config?.themeSyncUrl;
        if (!themeSyncUrl) return;

        try {
            const response = await fetch(themeSyncUrl);
            const result = await response.json();
            const data: ThemeSyncResponse = result.data;

            if (!data.options || data.options.length === 0) return;

            this.showDialog(data.current_theme, data.options);
        } catch (error) {
            console.error('Failed to check theme sync options:', error);
        }
    }

    private showDialog(currentTheme: string, options: ThemeSyncOption[]): void {
        this.removeDialog();

        this.dialog = document.createElement('dialog');
        this.dialog.className = 'theme-sync-dialog';
        this.buildDialogDOM(this.dialog, currentTheme, options);

        document.body.appendChild(this.dialog);

        // Close on backdrop click
        this.dialog.addEventListener('click', (e) => {
            if (e.target === this.dialog) {
                this.closeDialog();
            }
        });

        // Close on Escape
        this.dialog.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeDialog();
            }
        });

        this.dialog.showModal();
    }

    private buildDialogDOM(dialog: HTMLDialogElement, currentTheme: string, options: ThemeSyncOption[]): void {
        const content = document.createElement('div');
        content.className = 'theme-sync-content';

        const title = document.createElement('h3');
        title.className = 'theme-sync-title';
        title.textContent = `Sync Blocks to "${currentTheme}"`;
        content.appendChild(title);

        const description = document.createElement('p');
        description.className = 'theme-sync-description';
        description.textContent =
            'No blocks configured for the current theme. Only blocks that exist in both themes can be synced — blocks unique to a theme will be skipped.';
        content.appendChild(description);

        const optionsContainer = document.createElement('div');
        optionsContainer.className = 'theme-sync-options';

        options.forEach((opt) => {
            const btn = document.createElement('button');
            btn.className = 'theme-sync-option';
            btn.type = 'button';
            btn.dataset.theme = opt.theme;

            const nameSpan = document.createElement('span');
            nameSpan.className = 'theme-sync-option-name';
            nameSpan.textContent = opt.theme;

            const countSpan = document.createElement('span');
            countSpan.className = 'theme-sync-option-count';
            countSpan.textContent = `${opt.block_count} blocks`;

            btn.appendChild(nameSpan);
            btn.appendChild(countSpan);
            btn.addEventListener('click', () => this.executeSync(opt.theme));

            optionsContainer.appendChild(btn);
        });

        content.appendChild(optionsContainer);

        const footer = document.createElement('div');
        footer.className = 'theme-sync-footer';

        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'theme-sync-cancel';
        cancelBtn.type = 'button';
        cancelBtn.textContent = 'Skip';
        cancelBtn.addEventListener('click', () => this.closeDialog());
        footer.appendChild(cancelBtn);

        content.appendChild(footer);
        dialog.appendChild(content);
    }

    private async executeSync(sourceTheme: string): Promise<void> {
        const config = (window as any).pageBuilderConfig;
        const syncUrl = config?.themeSyncExecuteUrl;
        if (!syncUrl) return;

        // Disable buttons during sync
        this.dialog?.querySelectorAll('button').forEach((btn) => {
            btn.setAttribute('disabled', 'true');
        });

        const descEl = this.dialog?.querySelector('.theme-sync-description');
        if (descEl) {
            descEl.textContent = `Syncing blocks from "${sourceTheme}"...`;
        }

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta?.getAttribute('content') || '';

            const response = await fetch(syncUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ source_theme: sourceTheme }),
            });

            const result = await response.json();

            if (response.ok) {
                this.closeDialog();

                // Reload page data to show synced blocks
                const dataUrl = config?.dataUrl;
                if (dataUrl) {
                    const dataResponse = await fetch(dataUrl);
                    const dataResult = await dataResponse.json();
                    const content = dataResult.data?.page?.content;
                    if (content) {
                        await pageBuilderService.renderPageData(content);
                    }
                }

                this.showNotification(result.message || 'Blocks synced successfully', true);
            } else {
                throw new Error(result.message || 'Sync failed');
            }
        } catch (error) {
            console.error('Theme sync failed:', error);
            this.showNotification('Sync failed. Please try again.', false);
            this.closeDialog();
        }
    }

    private closeDialog(): void {
        if (this.dialog) {
            this.dialog.close();
            this.removeDialog();
        }
    }

    private removeDialog(): void {
        this.dialog?.remove();
        this.dialog = null;
    }

    private showNotification(message: string, isSuccess: boolean): void {
        const notification = document.createElement('div');
        notification.textContent = message;
        notification.className = `notification ${isSuccess ? 'success' : 'error'}`;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
}

// Export singleton instance
export const themeSyncManager = new ThemeSyncManager();
