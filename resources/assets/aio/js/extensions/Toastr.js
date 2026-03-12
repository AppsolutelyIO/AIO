
export default class Toastr {
    constructor(AIO) {
        let _this = this;

        AIO.success = _this.success;
        AIO.error = _this.error;
        AIO.info = _this.info;
        AIO.warning = _this.warning;
    }

    success(message, title, options) {
        toastr.success(message, title, options);
    }

    error(message, title, options) {
        toastr.error(message, title, options);
    }

    info(message, title, options) {
        toastr.info(message, title, options);
    }

    warning(message, title, options) {
        toastr.warning(message, title, options);
    }
}
