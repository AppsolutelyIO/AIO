
export default class Toastr {
    constructor(AIO: AIOInstance) {
        let _this = this;

        AIO.success = _this.success;
        AIO.error = _this.error;
        AIO.info = _this.info;
        AIO.warning = _this.warning;
    }

    success(message: string, title?: string, options?: Record<string, unknown>): void {
        toastr.success(message, title, options);
    }

    error(message: string, title?: string, options?: Record<string, unknown>): void {
        toastr.error(message, title, options);
    }

    info(message: string, title?: string, options?: Record<string, unknown>): void {
        toastr.info(message, title, options);
    }

    warning(message: string, title?: string, options?: Record<string, unknown>): void {
        toastr.warning(message, title, options);
    }
}
