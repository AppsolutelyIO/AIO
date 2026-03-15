import Swal from 'sweetalert2';

let w = window as any;

export default class SweetAlert2 {
    private swal: typeof Swal;

    constructor(AIO: AIOInstance) {
        let _this = this;

        (Swal as Record<string, unknown>).success = _this.success.bind(_this);
        (Swal as Record<string, unknown>).error = _this.error.bind(_this);
        (Swal as Record<string, unknown>).info = _this.info.bind(_this);
        (Swal as Record<string, unknown>).warning = _this.warning.bind(_this);
        (Swal as Record<string, unknown>).confirm = _this.confirm.bind(_this);

        w.swal = w.Swal = _this.swal = AIO.swal = Swal as any;

        AIO.confirm = (Swal as Record<string, unknown>).confirm as AIOInstance['confirm'];
    }

    success(title: string, message?: string, options?: Record<string, unknown>): Promise<Record<string, unknown>> {
        return this.fire(title, message, 'success', options);
    }

    error(title: string, message?: string, options?: Record<string, unknown>): Promise<Record<string, unknown>> {
        return this.fire(title, message, 'error', options);
    }

    info(title: string, message?: string, options?: Record<string, unknown>): Promise<Record<string, unknown>> {
        return this.fire(title, message, 'info', options);
    }

    warning(title: string, message?: string, options?: Record<string, unknown>): Promise<Record<string, unknown>> {
        return this.fire(title, message, 'warning', options);
    }

    confirm(
        title: string,
        message: string,
        success?: () => void,
        fail?: () => void,
        options?: Record<string, unknown>,
    ): void {
        let lang = (window as Window & { AIO: AIOInstance }).AIO.lang;

        options = $.extend(
            {
                showCancelButton: true,
                showLoaderOnConfirm: true,
                confirmButtonText: lang['confirm'],
                cancelButtonText: lang['cancel'],
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-white ml-1',
                buttonsStyling: false,
            },
            options,
        );

        this.fire(title, message, 'question', options).then(function (result: { value?: unknown }) {
            if (result.value) {
                return success && success();
            }

            fail && fail();
        });
    }

    fire(
        title: string,
        message?: string,
        type?: string,
        options?: Record<string, unknown>,
    ): Promise<Record<string, unknown>> {
        options = $.extend(
            {
                title: title,
                type: type,
                html: message,
            },
            options,
        );

        return this.swal.fire(options) as unknown as Promise<Record<string, unknown>>;
    }
}
