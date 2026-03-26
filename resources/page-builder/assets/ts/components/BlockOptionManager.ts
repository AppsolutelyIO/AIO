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
    published_at?: string | null;
    expired_at?: string | null;
    view?: string;
    view_style?: string;
    display_options_definition?: Record<string, unknown>;
    query_options_definition?: Record<string, unknown>;
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
            placeholder.classList.add('hidden');
            document.getElementById('block-option-tabs-wrap')?.classList.remove('hidden');
            this.renderPanels(option);
        } catch (err) {
            console.error('[PageBuilder] Failed to load block options:', err);
            if (placeholderP) placeholderP.textContent = 'Failed to load options.';
            placeholder.querySelector('i')?.classList.remove('fa-spinner', 'fa-spin');
        }
    }

    /**
     * Inject server-rendered HTML into tab panels and populate schedule fields.
     *
     * Note: innerHTML is used here intentionally — the HTML comes from our own
     * server-side BlockOptionFormRenderer (trusted), not from user input.
     */
    private renderPanels(option: BlockOption): void {
        const displayPanel = document.getElementById('block-option-panel-display');
        const queryPanel = document.getElementById('block-option-panel-query');
        const schedulePanel = document.getElementById('block-option-panel-schedule');
        const tabDisplay = document.getElementById('block-option-tab-display');
        const tabQuery = document.getElementById('block-option-tab-query');
        const tabSchedule = document.getElementById('block-option-tab-schedule');

        if (!displayPanel || !queryPanel || !schedulePanel) return;

        // Server-rendered HTML from BlockOptionFormRenderer (trusted source)
        displayPanel.innerHTML =
            option.display_options_html ??
            '<p class="text-sm text-slate-500 italic">No display options defined for this block.</p>';

        const hasQueryOptions = !!option.query_options_definition && Object.keys(option.query_options_definition).length > 0;
        if (hasQueryOptions) {
            queryPanel.innerHTML = option.query_options_html!;
        }

        // Populate schedule inputs
        const publishedAtInput = document.getElementById('block-option-published-at') as HTMLInputElement | null;
        const expiredAtInput = document.getElementById('block-option-expired-at') as HTMLInputElement | null;
        if (publishedAtInput) publishedAtInput.value = this.toDatetimeLocalValue(option.published_at);
        if (expiredAtInput) expiredAtInput.value = this.toDatetimeLocalValue(option.expired_at);

        // Clone buttons to remove any previously attached listeners
        const freshTabDisplay = tabDisplay?.cloneNode(true) as HTMLElement | undefined;
        const freshTabQuery = tabQuery?.cloneNode(true) as HTMLElement | undefined;
        const freshTabSchedule = tabSchedule?.cloneNode(true) as HTMLElement | undefined;
        tabDisplay?.replaceWith(freshTabDisplay!);
        tabQuery?.replaceWith(freshTabQuery!);
        tabSchedule?.replaceWith(freshTabSchedule!);

        // Hide query tab if no query options defined
        if (!hasQueryOptions) {
            freshTabQuery?.classList.add('hidden');
            queryPanel.classList.add('hidden');
        }

        const allTabs = [freshTabDisplay, hasQueryOptions ? freshTabQuery : undefined, freshTabSchedule];
        const allPanels = [displayPanel, hasQueryOptions ? queryPanel : undefined, schedulePanel];

        const showTab = (activeIndex: number) => {
            allPanels.forEach((panel, i) => panel?.classList.toggle('hidden', i !== activeIndex));
            allTabs.forEach((tab, i) => {
                tab?.setAttribute('aria-selected', i === activeIndex ? 'true' : 'false');
                tab?.classList.toggle('block-option-tab--active', i === activeIndex);
            });
        };

        freshTabDisplay?.addEventListener('click', () => showTab(0));
        freshTabQuery?.addEventListener('click', () => showTab(1));
        freshTabSchedule?.addEventListener('click', () => showTab(2));

        // Lock modal container height: show each panel, measure container, take max
        this.lockContainerHeight(allPanels);

        showTab(0);
    }

    /**
     * Lock the modal container to the height of its tallest tab.
     * Shows each panel one at a time, measures the container, sets a fixed height.
     */
    private lockContainerHeight(panels: (HTMLElement | undefined)[]): void {
        const container = document.getElementById('block-option-modal-container');
        if (!container) return;

        container.style.height = '';

        let maxHeight = 0;
        for (const panel of panels) {
            if (!panel) continue;
            // Show only this panel
            panels.forEach((p) => p?.classList.add('hidden'));
            panel.classList.remove('hidden');
            // Force layout reflow then measure
            const h = container.getBoundingClientRect().height;
            if (h > maxHeight) maxHeight = h;
        }

        panels.forEach((p) => p?.classList.add('hidden'));

        if (maxHeight > 0) {
            container.style.height = `${maxHeight}px`;
        }
    }

    /** Convert an ISO datetime string to datetime-local input value (YYYY-MM-DDTHH:mm). */
    private toDatetimeLocalValue(value: string | null | undefined): string {
        if (!value) return '';
        const date = new Date(value);
        if (isNaN(date.getTime())) return '';
        const pad = (n: number) => String(n).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
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
            }),
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

        const publishedAtInput = document.getElementById('block-option-published-at') as HTMLInputElement | null;
        const expiredAtInput = document.getElementById('block-option-expired-at') as HTMLInputElement | null;
        const publishedAt = publishedAtInput?.value || null;
        const expiredAt = expiredAtInput?.value || null;

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
                    published_at: publishedAt,
                    expired_at: expiredAt,
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
