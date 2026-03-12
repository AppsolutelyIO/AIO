
export default class Ajax {
    constructor(AIO) {
        this.aio = AIO;

        AIO.handleAjaxError = this.handleAjaxError.bind(this);
        AIO.handleJsonResponse = this.handleJsonResponse.bind(this);

        this.init(AIO)
    }

    init(AIO) {
        $.get = function (url, data, success, dataType) {
            let options = {
                type: 'GET',
                url: url,
            };

            if (typeof data === 'function') {
                dataType = success;
                success = data;
                data = null
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

            return $.ajax(options)
        };

        $.post = function (options) {
            options.type = 'POST';
            Object.assign(options.data, {_token: AIO.token});

            return $.ajax(options);
        };

        $.delete = function (options) {
            options.type = 'POST';
            options.data = {_method: 'DELETE', _token: AIO.token};

            return $.ajax(options);
        };

        $.put = function (options) {
            options.type = 'POST';
            Object.assign(options.data, {_method: 'PUT', _token: AIO.token});

            return $.ajax(options);
        };
    }

    handleAjaxError(xhr, text, msg) {
        let AIO = this.aio,
            json = xhr.responseJSON || {},
            _msg = json.message;

        AIO.NP.done();
        AIO.loading(false);// 关闭所有loading效果
        $('.btn-loading').buttonLoading(false);

        switch (xhr.status) {
            case 500:
                return AIO.error(_msg || (AIO.lang['500'] || 'Server internal error.'));
            case 403:
                return AIO.error(_msg || (AIO.lang['403'] || 'Permission deny!'));
            case 401:
                if (json.redirect) {
                    return location.href = json.redirect;
                }
                return AIO.error(AIO.lang['401'] || 'Unauthorized.');
            case 301:
            case 302:
                console.log('admin redirect', json);
                if (json.redirect) {
                    return location.href = json.redirect;
                }
                return;
            case 419:
                return AIO.error(AIO.lang['419'] || 'Sorry, your page has expired.');

            case 422:
                if (json.errors) {
                    try {
                        var err = [], i;
                        for (i in json.errors) {
                            err.push(json.errors[i].join('<br/>'));
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

    // 处理接口返回数据
    handleJsonResponse(response, options) {
        let AIO = this.aio,
            data = response.data;

        if (! response) {
            return;
        }

        if (typeof response !== 'object') {
            return AIO.error('error', 'Oops!');
        }

        var then = function (then) {
            switch (then.action) {
                case 'refresh':
                    AIO.reload();
                    break;
                case 'download':
                    window.open(then.value, '_blank');
                    break;
                case 'redirect':
                    AIO.reload(then.value || null);
                    break;
                case 'location':
                    setTimeout(function () {
                        if (then.value) {
                            window.location = then.value;
                        } else {
                            window.location.reload();
                        }
                    }, 1000);
                    break;
                case 'script':
                    (function () {
                        eval(then.value);
                    })();
                    break;
            }
        };

        if (typeof response.html === 'string' && response.html && options.target) {
            if (typeof options.html === 'function') {
                // 处理api返回的HTML代码
                options.html(options.target, response.html, response);
            } else {
                $(target).html(response.html);
            }
        }

        let message = data.message || response.message;

        // 判断默认弹窗类型.
        if (! data.type) {
            data.type = response.status ? 'success' : 'error';
        }

        if (typeof message === 'string' && data.type && message) {
            if (data.alert) {
                AIO.swal[data.type](message, data.detail);
            } else {
                AIO[data.type](message, null, data.timeout ? {timeOut: data.timeout*1000} : {});
            }
        }

        if (data.then) {
            then(data.then);
        }
    }
}
