{{-- Page Builder Sidebar: Blocks panel only --}}
<aside class="page-builder-sidebar w-80 bg-white shadow-lg rounded-lg p-4 overflow-y-auto"
    aria-label="Page builder panels">
    <div class="flex border-b border-slate-200 mb-4" role="tablist">
        <button id="blocks-tab" class="tab-btn active py-2 px-4 font-medium" role="tab" aria-selected="true"
            aria-controls="blocks-content" type="button">Blocks</button>
    </div>

    <div id="blocks-content" class="config-panel" role="tabpanel" aria-labelledby="blocks-tab">
        <h2 class="text-lg font-semibold mb-4 text-slate-700">
            <i class="fas fa-boxes mr-2 text-secondary"></i>Blocks
        </h2>

        <div class="relative mb-4 hidden">
            <input type="text" placeholder="Search blocks..."
                class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
            <i class="fas fa-search absolute right-3 top-3 text-slate-400"></i>
        </div>

        <div class="gjs-blocks-c space-y-6 !bg-white" id="blocks">
        </div>
    </div>
</aside>
