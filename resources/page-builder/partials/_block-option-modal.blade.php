{{-- Block option modal: form loaded from getBlockOption (display_options + query_options from API) --}}
<div id="block-option-modal" class="fixed inset-0 flex bg-black bg-opacity-50 items-center justify-center z-[60] hidden"
    role="dialog" aria-modal="true" aria-labelledby="block-option-modal-title" aria-hidden="true" tabindex="-1">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col mx-4">
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-200 shrink-0">
            <h2 id="block-option-modal-title" class="text-lg font-semibold text-slate-800">
                <i class="fas fa-sliders-h mr-2 text-primary" aria-hidden="true"></i>
                Block options
            </h2>
            <button id="block-option-modal-close" type="button" class="text-slate-500 hover:text-slate-700 p-1 rounded"
                aria-label="Close">
                <i class="fas fa-times text-xl" aria-hidden="true"></i>
            </button>
        </div>
        <div id="block-option-modal-body" class="p-6 overflow-y-auto flex-1">
            <div class="text-center py-10 text-slate-400" id="block-option-placeholder">
                <i class="fas fa-spinner fa-spin text-4xl mb-3" aria-hidden="true"></i>
                <p>Loading options…</p>
            </div>
            <div id="block-option-tabs-wrap" class="hidden">
                <nav id="block-option-tabs" class="flex gap-1 border-b border-slate-200 mb-4" role="tablist"
                    aria-label="Block option sections">
                    <button type="button" id="block-option-tab-display" role="tab" aria-selected="true"
                        aria-controls="block-option-panel-display"
                        class="block-option-tab block-option-tab--active px-4 py-2 text-sm font-medium rounded-t-md -mb-px">
                        Display options
                    </button>
                    <button type="button" id="block-option-tab-query" role="tab" aria-selected="false"
                        aria-controls="block-option-panel-query"
                        class="block-option-tab px-4 py-2 text-sm font-medium rounded-t-md -mb-px">
                        Query options
                    </button>
                </nav>
                <form id="block-option-form" class="space-y-0" aria-label="Block options form">
                    <div id="block-option-panel-display" role="tabpanel" aria-labelledby="block-option-tab-display"
                        class="space-y-3">
                        {{-- Display options rendered by BlockOptionManager from display_options_definition --}}
                    </div>
                    <div id="block-option-panel-query" role="tabpanel" aria-labelledby="block-option-tab-query"
                        class="hidden space-y-3">
                        {{-- Query options rendered by BlockOptionManager from query_options_definition --}}
                    </div>
                </form>
            </div>
        </div>
        <div class="flex justify-end gap-2 px-6 py-4 border-t border-slate-200 shrink-0 bg-slate-50 rounded-b-lg">
            <button id="block-option-modal-cancel" type="button"
                class="px-4 py-2 text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50">
                Cancel
            </button>
            <button id="block-option-modal-save" type="button"
                class="px-4 py-2 text-white bg-primary rounded-md hover:opacity-90">
                <i class="fas fa-save mr-2" aria-hidden="true"></i>
                Save
            </button>
        </div>
    </div>
</div>
