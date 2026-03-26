{{-- Block option modal: form loaded from getBlockOption (display_options + query_options from API) --}}
<div id="block-option-modal" class="fixed inset-0 flex bg-black bg-opacity-50 items-center justify-center z-[60] hidden"
    role="dialog" aria-modal="true" aria-labelledby="block-option-modal-title" aria-hidden="true" tabindex="-1">
    <div id="block-option-modal-container" class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col mx-4">
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
        <div id="block-option-modal-body" class="flex-1 flex flex-col min-h-0">
            <div class="text-center py-10 text-slate-400 px-6" id="block-option-placeholder">
                <i class="fas fa-spinner fa-spin text-4xl mb-3" aria-hidden="true"></i>
                <p>Loading options…</p>
            </div>
            <div id="block-option-tabs-wrap" class="hidden flex-1 flex flex-col min-h-0">
                <nav id="block-option-tabs" class="flex gap-1 border-b border-slate-200 px-6 pt-4 shrink-0" role="tablist"
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
                    <button type="button" id="block-option-tab-schedule" role="tab" aria-selected="false"
                        aria-controls="block-option-panel-schedule"
                        class="block-option-tab px-4 py-2 text-sm font-medium rounded-t-md -mb-px">
                        Schedule
                    </button>
                </nav>
                <form id="block-option-form" class="flex-1 overflow-y-auto p-6 space-y-0" aria-label="Block options form">
                    <div id="block-option-panel-display" role="tabpanel" aria-labelledby="block-option-tab-display"
                        class="space-y-3">
                        {{-- Display options rendered by BlockOptionManager from display_options_definition --}}
                    </div>
                    <div id="block-option-panel-query" role="tabpanel" aria-labelledby="block-option-tab-query"
                        class="hidden space-y-3">
                        {{-- Query options rendered by BlockOptionManager from query_options_definition --}}
                    </div>
                    <div id="block-option-panel-schedule" role="tabpanel" aria-labelledby="block-option-tab-schedule"
                        class="hidden space-y-4">
                        <div class="w-1/2">
                            <label for="block-option-published-at"
                                class="block text-sm font-medium text-slate-700 mb-1">Publish at</label>
                            <div>
                                <input type="text" id="block-option-published-at" placeholder="Select date and time"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                                    aria-describedby="block-option-published-at-help">
                            </div>
                            <p id="block-option-published-at-help" class="mt-1 text-xs text-slate-500">
                                Leave empty to publish immediately.
                            </p>
                        </div>
                        <div class="w-1/2">
                            <label for="block-option-expired-at"
                                class="block text-sm font-medium text-slate-700 mb-1">Expire at</label>
                            <div>
                                <input type="text" id="block-option-expired-at" placeholder="Select date and time"
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary"
                                    aria-describedby="block-option-expired-at-help">
                            </div>
                            <p id="block-option-expired-at-help" class="mt-1 text-xs text-slate-500">
                                Leave empty to never expire.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-6">
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
                </form>
            </div>
        </div>
    </div>
</div>
