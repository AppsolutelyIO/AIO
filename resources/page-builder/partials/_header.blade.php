{{-- Page Builder Top Toolbar --}}
<header class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md" role="banner">
    <div class="mx-auto px-4 py-2 flex items-center justify-between">
        {{-- Left: Logo --}}
        <div class="flex items-center">
            <div class="bg-primary w-8 h-8 rounded-lg flex items-center justify-center mr-3" aria-hidden="true">
                <i class="fas fa-magic text-white"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-800">Page Builder</h1>
        </div>

        {{-- Center: Device Preview --}}
        <div class="flex items-center bg-slate-100 rounded-lg p-1" role="group" aria-label="Device preview">
            <button class="device-btn px-4 py-1 rounded-md text-sm active" data-device="desktop" type="button"
                aria-pressed="true" aria-label="Desktop view">
                <i class="fas fa-desktop mr-2" aria-hidden="true"></i>Desktop
            </button>
            <button class="device-btn px-4 py-1 rounded-md text-sm ml-1" data-device="tablet" type="button"
                aria-pressed="false" aria-label="Tablet view">
                <i class="fas fa-tablet-alt mr-2" aria-hidden="true"></i>Tablet
            </button>
            <button class="device-btn px-4 py-1 rounded-md text-sm ml-1" data-device="mobile" type="button"
                aria-pressed="false" aria-label="Mobile view">
                <i class="fas fa-mobile-alt mr-2" aria-hidden="true"></i>Mobile
            </button>
        </div>

        {{-- Right: Action Buttons --}}
        <div class="flex space-x-2">
            <div class="text-sm text-slate-500 mr-5" aria-live="polite" aria-atomic="true">
                <i class="fas fa-cube mr-1 mt-2" aria-hidden="true"></i><span id="block-count">0</span> blocks
            </div>
            <button id="save-config-btn"
                class="flex items-center bg-slate-100 hover:bg-slate-200 px-3 py-1 rounded-md text-sm" type="button"
                aria-label="Save configuration">
                <i class="fas fa-undo mr-2" aria-hidden="true"></i>Save Config
            </button>
            <button id="undo-btn"
                class="flex items-center bg-slate-100 hover:bg-slate-200 px-3 py-1 rounded-md text-sm" type="button"
                aria-label="Undo">
                <i class="fas fa-undo mr-2" aria-hidden="true"></i>Undo
            </button>
            <button id="redo-btn"
                class="flex items-center bg-slate-100 hover:bg-slate-200 px-3 py-1 rounded-md text-sm" type="button"
                aria-label="Redo">
                <i class="fas fa-redo mr-2" aria-hidden="true"></i>Redo
            </button>
            <button id="reset-btn" class="flex items-center bg-slate-100 hover:bg-red-200 px-3 py-1 rounded-md text-sm"
                type="button" aria-label="Reset page">
                <i class="fas fa-eraser mr-2" aria-hidden="true"></i>Reset
            </button>
            <button id="save-btn"
                class="flex items-center bg-primary hover:bg-indigo-600 px-4 py-1 rounded-md text-sm text-white"
                type="button" aria-label="Save page">
                <i class="fas fa-save mr-2" aria-hidden="true"></i>Save
            </button>
            <button id="preview-btn"
                class="flex items-center bg-secondary hover:bg-purple-600 px-4 py-1 rounded-md text-sm text-white"
                type="button" aria-label="Preview page">
                <i class="fas fa-eye mr-2" aria-hidden="true"></i>Preview
            </button>
        </div>
    </div>
</header>
