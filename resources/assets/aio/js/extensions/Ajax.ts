
export default class Ajax {
    private aio: AIOInstance;

    constructor(AIO: AIOInstance) {
        this.aio = AIO;

        AIO.handleAjaxError = this.handleAjaxError.bind(this);
        AIO.handleJsonResponse = this.handleJsonResponse.bind(this);

        this.init(AIO)
    }

    init(AIO: AIOInstance): void {
        ($ as Record<string, unknown>).get = function (
            url: string,
            data?: Record<string, unknown> | ((response: unknown) => void),
            success?: ((response: unknown) => void) | string,
            dataType?: string
        ): JQueryXHR {
            let options: Record<string, unknown> = {
                type: 'GET',
                url: url,
            };

            if (typeof data === 'function') {
                dataType = success as string;
                success = data;
                data = null as unknown as Record<string, unknown>;
            }

            if (typeof success === 'function') {
                options.success = success;
            }

            if (typeof data === 'object') {
                options.data = data
            }

            if (dataType) {
                options.dataType = dataType;
            }

            return $.ajax(options as JQueryAjaxSettings)
        };

        ($ as Record<string, unknown>).post = function (options: Record<string, unknown>): JQueryXHR {
            options.type = 'POST';
            Object.assign(options.data as Record<string, unknown>, {_token: AIO.token});

            return $.ajax(options as JQueryAjaxSettings);
        };

        ($ as Record<string, unknown>).delete = function (options: Record<string, unknown>): JQueryXHR {
            options.type = 'POST';
            options.data = {_method: 'DELETE', _token: AIO.token};

            return $.ajax(options as JQueryAjaxSettings);
        };

        ($ as Record<string, unknown>).put = function (options: Record<string, unknown>): JQueryXHR {
            options.type = 'POST';
            Object.assign(options.data as Record<string, unknown>, {_method: 'PUT', _token: AIO.token});

            return $.ajax(options as JQueryAjaxSettings);
        };
    }

    handleAjaxError(xhr: JQueryXHR, text: string, msg: string): void {
        let AIO = this.aio,
            json = xhr.responseJSON || {} as Record<string, unknown>,
            _msg = json.message as string | undefined;

        AIO.NP.done();
        (AIO as Record<string, unknown>).loading = false;
        $('.btn-loading').buttonLoading(false);

        switch (xhr.status) {
            case 500:
                return AIO.error(_msg || (AIO.lang['500'] || 'Server internal error.'));
            case 403:
                return AIO.error(_msg || (AIO.lang['403'] || 'Permission deny!'));
            case 401:
                if (json.redirect) {
                    location.href = json.redirect as string;
                    return;
                }
                return AIO.error(AIO.lang['401'] || 'Unauthorized.');
            case 301:
            case 302:
                console.log('admin redirect', json);
                if (json.redirect) {
                    location.href = json.redirect as string;
                    return;
                }
                return;
            case 419:
                return AIO.error(AIO.lang['419'] || 'Sorry, your page has expired.');

            case 422:
                if (json.errors) {
                    try {
                        var err: string[] = [], i: string;
                        for (i in json.errors as Record<string, string[]>) {
                            err.push((json.errors as Record<string, string[]>)[i].join('<br/>'));
                        }
                        AIO.error(err.join('<br/>'));
                    } catch (e) {}
                    return;
                }
             case 0:
                return;
        }

        AIO.error(_msg || (xhr.status + ' ' + msg));
    }

    handleJsonResponse(response: Record<string, unknown>, options?: Record<string, unknown>): void {
        let AIO = this.aio,
            data = response.data as Record<string, unknown>;

        if (! response) {
            return;
        }

        if (typeof response !== 'object') {
            return AIO.error('error', 'Oops!');
        }

        var processThen = function (thenData: Record<string, unknown>): void {
            switch (thenData.action) {
                case 'refresh':
                    (AIO as Record<string, unknown>).reload();
                    break;
                case 'download':
                    window.open(thenData.value as string, '_blank');
                    break;
                case 'redirect':
                    (AIO as Record<string, unknown>).reload(thenData.value || null);
                    break;
                case 'location':
                    setTimeout(function () {
                        if (thenData.value) {
                            window.location.href = thenData.value as string;
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                    break;
                case 'script':
                    (function () {
                        // eslint-disable-next-line no-eval
                        const indirectEval = globalThis.eval;
                        indirectEval(thenData.value as string);
                    })();
                    break;
            }
        };

        if (typeof response.html === 'string' && response.html && options && options.target) {
            if (typeof options.html === 'function') {
                options.html(options.target, response.html, response);
            } else {
                $(options.target as string).html(response.html as string);
            }
        }

        let message = (data.message || response.message) as string | undefined;

        if (! data.type) {
            data.type = response.status ? 'success' : 'error';
        }

        if (typeof message === 'string' && data.type && message) {
            if (data.alert) {
                (AIO.swal as Record<string, (...args: unknown[]) => void>)[data.type as string](message, data.detail);
            } else {
                (AIO as Record<string, (...args: unknown[]) => void>)[data.type as string](message, null, data.timeout ? {timeOut: (data.timeout as number)*1000} : {});
            }
        }

        if (data.then) {
            processThen(data.then as Record<string, unknown>);
        }
    }
}
