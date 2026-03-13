/// <reference types="jquery" />

// jQuery is loaded globally
declare const $: JQueryStatic;
declare const jQuery: JQueryStatic;

// layer.js dialog library
declare const layer: {
    config(options: Record<string, unknown>): void;
    open(options: Record<string, unknown>): number;
    close(index: number): void;
    closeAll(type?: string): void;
    msg(content: string, options?: Record<string, unknown>, callback?: () => void): number;
    alert(content: string, options?: Record<string, unknown>, callback?: () => void): number;
    confirm(content: string, yes?: (index: number) => void, cancel?: (index: number) => void): number;
    load(icon?: number, options?: Record<string, unknown>): number;
    tips(content: string, follow: string | HTMLElement, options?: Record<string, unknown>): number;
    prompt(options: Record<string, unknown>, yes: (value: string, index: number) => void): number;
};

// toastr notification library
declare const toastr: {
    success(message: string, title?: string, options?: Record<string, unknown>): void;
    error(message: string, title?: string, options?: Record<string, unknown>): void;
    warning(message: string, title?: string, options?: Record<string, unknown>): void;
    info(message: string, title?: string, options?: Record<string, unknown>): void;
    clear(): void;
    options: Record<string, unknown>;
};

// WebUploader file upload library
declare const WebUploader: {
    create(options: Record<string, unknown>): WebUploaderInstance;
    Deferred(): { resolve(): void; reject(): void; promise(): unknown };
};

interface WebUploaderInstance {
    addButton(options: Record<string, unknown>): void;
    upload(): void;
    stop(): void;
    retry(): void;
    removeFile(fileId: string, removeFromQueue?: boolean): void;
    refresh(): void;
    on(event: string, callback: (...args: unknown[]) => void): void;
    onError?: (code: string) => void;
    onUploadProgress?: (file: WebUploaderFile, percentage: number) => void;
    onFileQueued?: (file: WebUploaderFile) => void;
    onFileDequeued?: (file: WebUploaderFile) => void;
}

interface WebUploaderFile {
    id: string;
    name: string;
    size: number;
    type: string;
    ext?: string;
    fake?: number;
    serverId?: string;
    serverName?: string;
    serverPath?: string;
    serverUrl?: string | null;
}

// sweetalert2 (vendored UMD library)
declare module '*/sweetalert/sweetalert2' {
    const Swal: {
        fire(options: Record<string, unknown>): Promise<{ value?: unknown; isConfirmed: boolean; isDismissed: boolean }>;
        close(): void;
        isVisible(): boolean;
    };
    export default Swal;
}

// jquery.form plugin (vendored UMD library)
declare module '*/jquery-form/jquery.form.min' {
    export default function(): void;
}

// AIO global on window
interface Window {
    CreateAIO: (config: Record<string, unknown>) => AIOInstance;
    AIO: AIOInstance;
}

// AIO instance type
interface AIOInstance {
    token: string;
    lang: Record<string, string>;
    config: Record<string, unknown>;
    NP: {
        configure(options: Record<string, unknown>): void;
        start(): void;
        done(force?: boolean): void;
        inc(amount?: number): void;
        set(n: number): void;
    };
    helpers: {
        random(len?: number): string;
        len(obj: unknown): number;
        slug(text: string, separator?: string): string;
        replace(str: string, search: string | RegExp, replace: string): string;
        isset(value: unknown): boolean;
        asyncRender(url: string, done?: (html: string) => void): void;
        [key: string]: unknown;
    };
    grid: Record<string, unknown>;
    color: Record<string, unknown>;
    darkMode: Record<string, unknown>;
    assets: {
        load(urls: string | string[]): Promise<void>;
        resolveHtml(html: string, cb: () => void): void;
    };
    validator: Record<string, unknown>;

    success(message: string, title?: string, options?: Record<string, unknown>): void;
    error(message: string, title?: string, options?: Record<string, unknown>): void;
    warning(message: string, title?: string, options?: Record<string, unknown>): void;
    info(message: string, title?: string, options?: Record<string, unknown>): void;
    confirm(title: string, message: string, callback: () => void): void;
    swal: Record<string, unknown>;

    handleAjaxError(xhr: JQueryXHR, status: string, error: string): void;
    handleJsonResponse(response: Record<string, unknown>): void;

    booting(callback: () => void): void;
    bootingEveryRequest(callback: () => void): void;
    boot(): void;
    ready(callback: () => void): void;
    init(): void;
    triggerReady(): void;

    Translator(translations: Record<string, string>): { trans(key: string, replacements?: Record<string, unknown>): string };
    RowSelector: (options: Record<string, unknown>) => unknown;
    Form: (options: Record<string, unknown>) => unknown;
    DialogForm: (options: Record<string, unknown>) => unknown;
    Slider: (options: Record<string, unknown>) => unknown;
    Uploader: (options: Record<string, unknown>) => unknown;

    [key: string]: unknown;
}
