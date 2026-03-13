// Block Option Manager - Handles the block option modal (display_options + query_options from API)
import { pageBuilderService } from '../services/PageBuilderService';

function escapeHtml(s: string): string {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
}

type BlockOption = {
    block_id: number;
    type: string;
    reference: string;
    display_options: Record<string, unknown>;
    query_options: Record<string, unknown>;
    view?: string;
    view_style?: string;
    // Server-rendered form HTML returned by BlockOptionFormRenderer
    display_options_html?: string;
    query_options_html?: string;
};

export class BlockOptionManager {
    private currentOption: BlockOption | null = null;

    constructor() {
        this.initializeModal();
    }

    private initializeModal(): void {
        const modal = document.getElementById('block-option-modal');
        const closeBtn = document.getElementById('block-option-modal-close');
        const cancelBtn = document.getElementById('block-option-modal-cancel');
        const saveBtn = document.getElementById('block-option-modal-save');

        const closeModal = () => {
            modal?.classList.add('hidden');
            modal?.setAttribute('aria-hidden', 'true');
        };

        closeBtn?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);

        saveBtn?.addEventListener('click', () => this.handleSave());

        // Event delegation for table row add / remove (buttons live inside server-rendered HTML)
        document.getElementById('block-option-form')?.addEventListener('click', (e) => {
            const target = e.target as HTMLElement;

            const addBtn = target.closest<HTMLElement>('[data-pb-add-row]');
            if (addBtn) {
                const tableKey = addBtn.dataset.pbAddRow!;
                const wrapper = addBtn.closest<HTMLElement>('[data-pb-table]');
                const tpl = wrapper?.querySelector<HTMLTemplateElement>(`template[data-pb-row-template="${tableKey}"]`);
                const container = wrapper?.querySelector('[data-pb-rows-container]');
                if (tpl && container) {
                    container.appendChild(tpl.content.cloneNode(true));
                    const newRow = container.lastElementChild;
                    if (newRow) this.fixClonedRowUploadIds(newRow);
                }
                return;
            }

            const removeBtn = target.closest('[data-pb-remove-row]');
            if (removeBtn) {
                removeBtn.closest('[data-pb-row]')?.remove();
            }

            const clearUploadBtn = target.closest<HTMLElement>('[data-pb-upload-clear]');
            if (clearUploadBtn) {
                this.handleUploadClear(clearUploadBtn);
            }
        });

        // Auto-upload when user selects a file (change fires on input[type=file])
        document.getElementById('block-option-form')?.addEventListener('change', (e) => {
            const input = (e.target as HTMLElement).closest<HTMLInputElement>('input[data-pb-upload-input]');
            if (input?.files?.length) this.handleFileSelect(input);
        });

        // When the canvas toolbar "Open options" button is clicked
        window.addEventListener('pagebuilder:open-block-option', () => this.openModal());
    }

    /** Open the modal and load options from API. */
    public async openModal(): Promise<void> {
        const modal = document.getElementById('block-option-modal');
        const placeholder = document.getElementById('block-option-placeholder');
        if (!modal || !placeholder) return;

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        placeholder.classList.remove('hidden');
        const placeholderP = placeholder.querySelector('p');
        if (placeholderP) placeholderP.textContent = 'Loading options…';
        placeholder.querySelector('i')?.classList.remove('fa-mouse-pointer');
        placeholder.querySelector('i')?.classList.add('fa-spinner', 'fa-spin');
        const tabsWrap = document.getElementById('block-option-tabs-wrap');
        if (tabsWrap) tabsWrap.classList.add('hidden');

        const editor = pageBuilderService.getEditor();
        const component = pageBuilderService.getSelectedComponent() ?? editor?.getSelected?.();

        if (!component) {
            if (placeholderP) placeholderP.textContent = 'Please select a block on the canvas first.';
            placeholder.querySelector('i')?.classList.remove('fa-spinner', 'fa-spin');
            placeholder.querySelector('i')?.classList.add('fa-mouse-pointer');
            return;
        }

        const reference = component.get?.('reference') ?? component.get?.('attributes')?.reference ?? '';
        const type = component.get?.('type') ?? component.get?.('attributes')?.type ?? '';
        const blockId = component.get?.('attributes')?.block_id ?? pageBuilderService.getBlockIdByType(type);

        if (!type) {
            if (placeholderP) placeholderP.textContent = 'Block type not found.';
            placeholder.querySelector('i')?.classList.remove('fa-spinner', 'fa-spin');
            return;
        }

        const optionUrl = (window as any).pageBuilderConfig?.blockOptionUrl;
        if (!optionUrl) {
            if (placeholderP) placeholderP.textContent = 'Block option API not configured.';
            placeholder.querySelector('i')?.classList.remove('fa-spinner', 'fa-spin');
            return;
        }

        try {
            const params = new URLSearchParams();
            if (reference) params.set('reference', reference);
            else if (blockId && type) {
                params.set('block_id', String(blockId));
                params.set('type', type);
            } else {
                if (placeholderP) {
                    placeholderP.textContent = 'Block identifier missing (reference or block_id+type).';
                }
                placeholder.querySelector('i')?.classList.remove('fa-spinner', 'fa-spin');
                return;
            }

            const res = await fetch(`${optionUrl}?${params.toString()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const json = await res.json();

            if (!res.ok || !json.data) {
                if (placeholderP) {
                    placeholderP.textContent = json.message || 'Failed to load block options.';
                }
                placeholder.querySelector('i')?.classList.remove('fa-spinner', 'fa-spin');
                return;
            }

            const option = json.data as BlockOption;
            this.currentOption = option;
            this.renderPanels(option);
            placeholder.classList.add('hidden');
            document.getElementById('block-option-tabs-wrap')?.classList.remove('hidden');
        } catch (err) {
            console.error('[PageBuilder] Failed to load block options:', err);
            if (placeholderP) placeholderP.textContent = 'Failed to load options.';
            placeholder.querySelector('i')?.classList.remove('fa-spinner', 'fa-spin');
        }
    }

    /** Inject server-rendered HTML into the two tab panels. */
    private renderPanels(option: BlockOption): void {
        const displayPanel = document.getElementById('block-option-panel-display');
        const queryPanel = document.getElementById('block-option-panel-query');
        const tabDisplay = document.getElementById('block-option-tab-display');
        const tabQuery = document.getElementById('block-option-tab-query');

        if (!displayPanel || !queryPanel) return;

        displayPanel.innerHTML =
            option.display_options_html ??
            '<p class="text-sm text-slate-500 italic">No display options defined for this block.</p>';
        queryPanel.innerHTML =
            option.query_options_html ??
            '<p class="text-sm text-slate-500 italic">No query options defined for this block.</p>';

        // Clone buttons to remove any previously attached listeners
        const freshTabDisplay = tabDisplay?.cloneNode(true) as HTMLElement | undefined;
        const freshTabQuery = tabQuery?.cloneNode(true) as HTMLElement | undefined;
        tabDisplay?.replaceWith(freshTabDisplay!);
        tabQuery?.replaceWith(freshTabQuery!);

        // Tab switching — closures reference the fresh (live) elements
        const showDisplay = () => {
            displayPanel.classList.remove('hidden');
            queryPanel.classList.add('hidden');
            freshTabDisplay?.setAttribute('aria-selected', 'true');
            freshTabQuery?.setAttribute('aria-selected', 'false');
            freshTabDisplay?.classList.add('block-option-tab--active');
            freshTabQuery?.classList.remove('block-option-tab--active');
        };
        const showQuery = () => {
            displayPanel.classList.add('hidden');
            queryPanel.classList.remove('hidden');
            freshTabDisplay?.setAttribute('aria-selected', 'false');
            freshTabQuery?.setAttribute('aria-selected', 'true');
            freshTabQuery?.classList.add('block-option-tab--active');
            freshTabDisplay?.classList.remove('block-option-tab--active');
        };

        freshTabDisplay?.addEventListener('click', showDisplay);
        freshTabQuery?.addEventListener('click', showQuery);

        showDisplay();
    }

    // -------------------------------------------------------------------------
    // Save — collect values from server-rendered HTML via data-pb-* attributes
    // -------------------------------------------------------------------------

    /** Upload a single file input and return the resulting URL. Returns null only when input has no files; throws on config or API error. */
    private async uploadSingleFileInput(input: HTMLInputElement): Promise<string | null> {
        const uploadUrl = (window as any).pageBuilderConfig?.fileUploadUrl;
        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
        if (!uploadUrl) throw new Error('File upload URL not configured.');
        if (!input.files?.length) return null;

        const formData = new FormData();
        formData.append('_file_', input.files[0]);

        const res = await fetch(uploadUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });
        const json = await res.json();
        if (res.ok && json.data?.url) return json.data.url as string;
        throw new Error(json.message || 'File upload failed.');
    }

    /** Upload any pending file inputs in a panel, populating hidden URL inputs. Reuses uploadSingleFileInput. */
    private async uploadPendingFiles(panel: Element): Promise<void> {
        const fileInputs = panel.querySelectorAll<HTMLInputElement>('[data-pb-upload-input]');
        const withFiles = Array.from(fileInputs).filter((input) => input.files?.length);
        if (!withFiles.length) return;

        await Promise.all(
            withFiles.map(async (input) => {
                const url = await this.uploadSingleFileInput(input);
                const targetId = input.dataset['pbUploadTarget'];
                const hidden = targetId ? (document.getElementById(targetId) as HTMLInputElement | null) : null;
                if (url && hidden) hidden.value = url;
            })
        );
    }

    /** Auto-upload on file select: upload then update URL display and preview in the same wrapper. */
    private async handleFileSelect(input: HTMLInputElement): Promise<void> {
        const wrapper = input.closest<HTMLElement>('[data-pb-upload-wrapper]');
        const targetId = input.dataset['pbUploadTarget'];
        const hidden = targetId ? (document.getElementById(targetId) as HTMLInputElement | null) : null;
        const urlDisplay = wrapper?.querySelector<HTMLInputElement>('[data-pb-upload-url]');
        const preview = wrapper?.querySelector<HTMLElement>('[data-pb-upload-preview]');
        if (!wrapper || !hidden || !urlDisplay) return;

        const showUploading = () => {
            input.disabled = true;
            urlDisplay.value = '';
            urlDisplay.placeholder = 'Uploading…';
        };
        const hideUploading = () => {
            input.disabled = false;
            input.value = '';
            urlDisplay.placeholder = '';
        };

        try {
            showUploading();
            const url = await this.uploadSingleFileInput(input);
            if (!url) return;
            hidden.value = url;
            urlDisplay.value = url;
            urlDisplay.placeholder = '';
            if (preview) {
                const isImage = /\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i.test(url);
                preview.innerHTML = isImage
                    ? `<img src="${escapeHtml(url)}" class="max-h-40 w-auto object-contain rounded border border-slate-200" alt="">`
                    : `<span class="text-xs text-slate-600 break-all">${escapeHtml(url.split('/').pop() ?? url)}</span>`;
            }
        } catch (err: unknown) {
            alert(err instanceof Error ? err.message : 'File upload failed.');
            return;
        } finally {
            hideUploading();
        }
    }

    /** Clear image/file: reset hidden, URL display, preview, and file input. */
    private handleUploadClear(button: HTMLElement): void {
        const wrapper = button.closest<HTMLElement>('[data-pb-upload-wrapper]');
        const targetId = wrapper?.dataset.pbUploadTargetId;
        if (!wrapper || !targetId) return;
        const hidden = document.getElementById(targetId) as HTMLInputElement | null;
        const urlDisplay = wrapper.querySelector<HTMLInputElement>('[data-pb-upload-url]');
        const preview = wrapper.querySelector<HTMLElement>('[data-pb-upload-preview]');
        const fileInput = wrapper.querySelector<HTMLInputElement>('[data-pb-upload-input]');
        if (hidden) hidden.value = '';
        if (urlDisplay) urlDisplay.value = '';
        if (preview) {
            preview.innerHTML =
                '<span class="text-xs text-slate-400 italic">Preview will appear here after upload</span>';
        }
        if (fileInput) fileInput.value = '';
    }

    private fixClonedRowUploadIds(row: Element): void {
        const uniq = Date.now();
        row.querySelectorAll<HTMLInputElement>('input[type="hidden"][id$="-tpl"]').forEach((hidden, i) => {
            const oldId = hidden.id;
            const newId = oldId.replace(/-tpl$/, `-tpl-${uniq}-${i}`);
            const fileInput = row.querySelector<HTMLInputElement>(`[data-pb-upload-target="${oldId}"]`);
            if (fileInput) fileInput.setAttribute('data-pb-upload-target', newId);
            hidden.id = newId;
            const wrapper = hidden.closest<HTMLElement>('[data-pb-upload-wrapper]');
            if (wrapper) wrapper.dataset.pbUploadTargetId = newId;
            const clearBtn = row.querySelector<HTMLElement>(`[data-pb-upload-clear][data-pb-upload-target="${oldId}"]`);
            if (clearBtn) clearBtn.setAttribute('data-pb-upload-target', newId);
        });
    }

    private async handleSave(): Promise<void> {
        if (!this.currentOption) return;

        const reference = this.currentOption.reference;
        if (!reference) {
            alert('Cannot save: this block has no reference (not yet placed on the page).');
            return;
        }

        const displayPanel = document.getElementById('block-option-panel-display');
        const queryPanel = document.getElementById('block-option-panel-query');

        // Upload any pending file inputs first
        try {
            if (displayPanel) await this.uploadPendingFiles(displayPanel);
            if (queryPanel) await this.uploadPendingFiles(queryPanel);
        } catch (err: any) {
            alert(err.message || 'File upload failed.');
            return;
        }

        const displayOptions = displayPanel ? this.collectPanelValues(displayPanel) : {};
        const queryOptions = queryPanel ? this.collectPanelValues(queryPanel) : {};

        const saveBtn = document.getElementById('block-option-modal-save') as HTMLButtonElement | null;
        const cancelBtn = document.getElementById('block-option-modal-cancel') as HTMLButtonElement | null;

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2" aria-hidden="true"></i>Saving…';
        }
        if (cancelBtn) cancelBtn.disabled = true;

        const optionUrl = (window as any).pageBuilderConfig?.blockOptionUrl;
        const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

        try {
            const res = await fetch(optionUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    reference,
                    display_options: displayOptions,
                    query_options: queryOptions,
                }),
            });

            const json = await res.json();

            if (!res.ok) {
                throw new Error(json.message || 'Failed to save block options.');
            }

            document.getElementById('block-option-modal')?.classList.add('hidden');
            document.getElementById('block-option-modal')?.setAttribute('aria-hidden', 'true');
        } catch (err: any) {
            console.error('[PageBuilder] block option save error', err);
            alert(err.message || 'Failed to save block options.');
        } finally {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save mr-2" aria-hidden="true"></i>Save';
            }
            if (cancelBtn) cancelBtn.disabled = false;
        }
    }

    /** Value from an input or select (checkbox → checked, else value). */
    private getInputValue(el: HTMLInputElement | HTMLSelectElement): string | boolean {
        return el instanceof HTMLInputElement && el.type === 'checkbox' ? el.checked : el.value;
    }

    /**
     * Collect all field values from a rendered panel element.
     *
     * Handles three categories based on data-pb-* markers set by BlockOptionFormRenderer:
     *   - data-pb-field       → scalar value
     *   - data-pb-object      → nested object (reads data-pb-sub-field children)
     *   - data-pb-table       → array of row objects (reads data-pb-col children per row)
     */
    private collectPanelValues(panel: Element): Record<string, unknown> {
        const result: Record<string, unknown> = {};

        panel.querySelectorAll<HTMLInputElement | HTMLSelectElement>('[data-pb-field]').forEach((el) => {
            result[el.dataset['pbField']!] = this.getInputValue(el);
        });

        panel.querySelectorAll<HTMLElement>('[data-pb-object]').forEach((wrapper) => {
            const obj: Record<string, unknown> = {};
            wrapper.querySelectorAll<HTMLInputElement | HTMLSelectElement>('[data-pb-sub-field]').forEach((el) => {
                obj[(el as HTMLElement).dataset['pbSubField']!] = this.getInputValue(el);
            });
            result[wrapper.dataset.pbObject!] = obj;
        });

        panel.querySelectorAll<HTMLElement>('[data-pb-table]').forEach((wrapper) => {
            const rows: Record<string, unknown>[] = [];
            wrapper.querySelectorAll<HTMLElement>('[data-pb-row]').forEach((row) => {
                const rowData: Record<string, unknown> = {};
                row.querySelectorAll<HTMLInputElement | HTMLSelectElement>('[data-pb-col]').forEach((el) => {
                    rowData[(el as HTMLElement).dataset['pbCol']!] = this.getInputValue(el);
                });
                rows.push(rowData);
            });
            result[wrapper.dataset.pbTable!] = rows;
        });

        return result;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new BlockOptionManager();
});
