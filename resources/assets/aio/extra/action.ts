(function (AIO: AIOInstance) {
    interface ActionOptions {
        selector: string | null;
        event: string;
        method: string;
        key: string | null;
        url: string | null;
        data: Record<string, unknown>;
        confirm: [string, string] | null;
        calledClass: string | null;
        before: (data: Record<string, unknown>, target: JQuery, action: Action) => boolean | void;
        html: (target: JQuery, html: string, data: Record<string, unknown>) => void;
        success: (target: JQuery, results: Record<string, unknown>) => boolean | void;
        error: (target: JQuery, results: unknown) => boolean | void;
    }

    class Action {
        options: ActionOptions;

        constructor(options: Partial<ActionOptions>) {
            this.options = $.extend(
                {
                    selector: null, // 按钮选择器
                    event: 'click',
                    method: 'POST',
                    key: null, // 行主键
                    url: null,
                    data: {}, // 发送到接口的附加参数
                    confirm: null,
                    calledClass: null,
                    before: function (data: Record<string, unknown>, target: JQuery) {}, // 发起请求之前回调，返回false可以中断请求
                    html: function (target: JQuery, html: string, data: Record<string, unknown>) {
                        // 处理返回的HTML代码
                        target.html(html);
                    },
                    success: function (target: JQuery, results: Record<string, unknown>) {}, // 请求成功回调，返回false可以中断默认的成功处理逻辑
                    error: function (target: JQuery, results: unknown) {}, // 请求出错回调，返回false可以中断默认的错误处理逻辑
                },
                options,
            ) as ActionOptions;

            this.init();
        }

        init(): void {
            let _this = this,
                options = _this.options;

            $(options.selector!)
                .off(options.event)
                .on(options.event, function (e: JQuery.Event) {
                    let data = $(this).data() as Record<string, unknown>,
                        target = $(this);
                    if (parseInt(target.attr('loading') || '0') > 0) {
                        return;
                    }

                    if (options.before(data, target, _this) === false) {
                        return;
                    }

                    // 发起请求
                    function request(): void {
                        target.attr('loading', 1);

                        Object.assign(data, options.data);

                        _this.promise(target, data).then(_this.resolve()).catch(_this.reject());
                    }

                    var conform = options.confirm;

                    if (conform) {
                        AIO.confirm(conform[0], conform[1], request);
                    } else {
                        request();
                    }
                });
        }

        resolve(): (result: [Record<string, unknown>, JQuery]) => void {
            let _this = this,
                options = _this.options;

            return function (result: [Record<string, unknown>, JQuery]) {
                var response = result[0],
                    target = result[1];

                if (options.success(target, response) === false) {
                    return;
                }

                (AIO as any).handleJsonResponse(response, { html: options.html, target: target });
            };
        }

        reject(): (result: [JQueryXHR, JQuery]) => void {
            let options = this.options;

            return function (result: [JQueryXHR, JQuery]) {
                var request = result[0],
                    target = result[1];

                if (options.success(target, request as any) === false) {
                    return;
                }

                if (request && typeof (request as any).responseJSON === 'object') {
                    AIO.error((request as any).responseJSON.message);
                }
                console.error(result);
            };
        }

        promise(target: JQuery, data: Record<string, unknown>): Promise<[Record<string, unknown>, JQuery]> {
            let options = this.options;

            return new Promise(function (resolve, reject) {
                Object.assign(data, {
                    _action: options.calledClass,
                    _key: options.key,
                    _token: AIO.token,
                });

                AIO.NP.start();

                $.ajax({
                    method: options.method,
                    url: options.url!,
                    data: data,
                    success: function (data: Record<string, unknown>) {
                        target.attr('loading', 0);
                        AIO.NP.done();
                        resolve([data, target]);
                    },
                    error: function (request: JQueryXHR) {
                        target.attr('loading', 0);
                        AIO.NP.done();
                        reject([request, target]);
                    },
                });
            });
        }
    }

    (AIO as any).Action = function (opts: Partial<ActionOptions>) {
        return new Action(opts);
    };
})((window as any).AIO as AIOInstance);
