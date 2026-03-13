{{-- Page Builder Preview Modal --}}
<div id="preview-modal"
    class="fixed inset-0 flex bg-black bg-opacity-75 items-center justify-center z-50 hidden pt-[10vh]" role="dialog"
    aria-modal="true" aria-labelledby="preview-modal-title" aria-hidden="true" tabindex="-1">
    <div class="bg-white rounded-lg w-11/12 mx-auto h-11/12 my-auto overflow-hidden">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 id="preview-modal-title" class="text-lg font-semibold">Preview</h3>
            <button id="close-preview" class="text-slate-500 hover:text-slate-700" type="button"
                aria-label="Close preview">
                <i class="fas fa-times text-xl" aria-hidden="true"></i>
            </button>
        </div>
        <div class="h-full p-4 overflow-auto" id="preview-content">
        </div>
    </div>
</div>
