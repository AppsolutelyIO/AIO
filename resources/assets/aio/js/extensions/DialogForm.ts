
interface DialogFormOptions {
    title: string;
    defaultUrl: string;
    buttonSelector: string;
    area: string[];
    lang: { submit: string; reset: string };
    query: string;
    forceRefresh: boolean;
    resetButton: boolean;
    saved: (status: boolean, response: Record<string, unknown>) => unknown;
    success: (status: boolean, response: Record<string, unknown>) => unknown;
    error: (status: boolean, response: Record<string, unknown>) => unknown;
}

let w = window as Window & Record<string, unknown>;

if (top && (w as Record<string, unknown>).layer) {
    w = top as unknown as Window & Record<string, unknown>;
}

export default class DialogForm {
    public options: DialogFormOptions;
    public $form: JQuery | null;
    public $target: JQuery | null;
    private _dialog: typeof layer;
    private _counter: number;
    private _idx: Record<number, number>;
    private _dialogs: Record<number, JQuery | null>;
    public rendering: number;
    public submitting: number;

    constructor(AIO: AIOInstance, options: Partial<DialogFormOptions>) {
        let self = this, nullFun = function (): void {};

        self.options = $.extend({
            title: '',
            defaultUrl: '',
            buttonSelector: '',
            area: [],
            lang: {
                submit: AIO.lang['submit'] || 'Submit',
                reset: AIO.lang['reset'] || 'Reset',
            },
            query: '',
            forceRefresh: false,
            resetButton: true,
            saved: nullFun,
            success: nullFun,
            error: nullFun,
        }, options) as DialogFormOptions;

        self.$form = null;
        self.$target = null;
        self._dialog = (w as unknown as Record<string, unknown>).layer as typeof layer;
        self._counter = 1;
        self._idx = {};
        self._dialogs = {};
        self.rendering = 0;
        self.submitting = 0;

        self.init(self.options)
    }

    init(options: DialogFormOptions): void {
        let self = this,
            defUrl = options.defaultUrl,
            selector = options.buttonSelector;

        selector && $(selector).off('click').click(function () {
            self.$target = $(this);

            let counter = self.$target.attr('counter') as unknown as number, url: string;

            if (! counter) {
                counter = self._counter;

                self.$target.attr('counter', counter);

                self._counter++;
            }

            url = self.$target.data('url') as string || defUrl;

            if (url.indexOf('?') === -1) {
                url += '?' + options.query + '=1'
            } else if (url.indexOf(options.query) === -1) {
                url += '&' + options.query + '=1'
            }

            self._build(url, counter);
        });

        selector || setTimeout(function () {
            self._build(defUrl, self._counter)
        }, 400);
    }

    _build(url: string, counter: number): void {
        let self = this,
            $btn = self.$target;

        if (! url || self.rendering) {
            return;
        }

        if (self._dialogs[counter]) {
            (self._dialogs[counter] as JQuery).show();

            try {
                (self._dialog as unknown as Record<string, (idx: number) => void>).restore(self._idx[counter]);
            } catch (e) {
            }

            return;
        }

        const AIO = (window as Window & { AIO: AIOInstance }).AIO;

        (AIO as Record<string, unknown>).onPjaxComplete = ((AIO as Record<string, (cb: () => void) => void>).onPjaxComplete || function () {});
        (AIO as Record<string, (cb: () => void) => void>).onPjaxComplete(() => {
            self._destroy(counter);
        });

        self.rendering = 1;

        $btn && ($btn as unknown as Record<string, () => void>).buttonLoading();

        AIO.NP.start();

        $.ajax({
            url: url,
            success: function (template: string) {
                self.rendering = 0;
                AIO.NP.done();

                if ($btn) {
                    ($btn as unknown as Record<string, (state: boolean) => void>).buttonLoading(false);

                    setTimeout(function () {
                        ($btn as JQuery).find('.waves-ripple').remove();
                    }, 50);
                }

                self._popup(template, counter);
            }
        });
    }

    _popup(template: string, counter: number): void {
        let self = this,
            options = self.options;

        const AIO = (window as Window & { AIO: AIOInstance }).AIO;

        const resolved = AIO.assets.resolveHtml(template, function () {}) as unknown as { render(): string };
        template = resolved.render();

        let btns: string[] = [options.lang.submit],
            dialogOpts: Record<string, unknown> = {
                type: 1,
                area: (function (v: string[]) {
                        if ((w as Window).screen.width <= 800) {
                            return ['100%', '100%',];
                        }

                        return v;
                    })(options.area),
                content: template,
                title: options.title,
                yes: function () {
                    self.submit()
                },
                cancel: function () {
                    if (options.forceRefresh) {
                        self._dialogs[counter] = null;
                        self._idx[counter] = 0;
                    } else {
                        (self._dialogs[counter] as JQuery).hide();
                        return false;
                    }
                }
            };

        if (options.resetButton) {
            btns.push(options.lang.reset);

            dialogOpts.btn2 = function (): boolean {
                (self.$form as JQuery).trigger('reset');

                return false;
            };
        }

        dialogOpts.btn = btns;

        self._idx[counter] = self._dialog.open(dialogOpts);
        self._dialogs[counter] = (w as Window & { $: JQueryStatic }).$ ('#layui-layer' + self._idx[counter]) as JQuery;
        self.$form = (self._dialogs[counter] as JQuery).find('form').first();
    }

    _destroy(counter: number): void {
        let dialogs = this._dialogs;

        this._dialog.close(this._idx[counter]);

        dialogs[counter] && (dialogs[counter] as JQuery).remove();

        dialogs[counter] = null;
    }

    submit(): boolean {
        let self = this,
            options = self.options,
            counter = (self.$target as JQuery).attr('counter') as unknown as number,
            $submitBtn = (self._dialogs[counter] as JQuery).find('.layui-layer-btn0');

        if (self.submitting) {
            return false;
        }

        const AIO = (window as Window & { AIO: AIOInstance }).AIO;

        AIO.Form({
            form: self.$form as unknown as Record<string, unknown>,
            redirect: false,
            confirm: (AIO as Record<string, unknown>).FormConfirm as Record<string, unknown>,
            before: function () {
                (self.$form as unknown as Record<string, (method: string) => void>).validator('validate');

                if ((self.$form as JQuery).find('.has-error').length > 0) {
                    return false;
                }

                self.submitting = 1;

                ($submitBtn as unknown as Record<string, () => void>).buttonLoading();
            },
            after: function (status: boolean, response: Record<string, unknown>) {
                ($submitBtn as unknown as Record<string, (state: boolean) => void>).buttonLoading(false);

                self.submitting = 0;

                if (options.saved(status, response) === false) {
                    return false;
                }

                if (! status) {
                    return options.error(status, response);
                }
                if (response.status) {
                    let r = options.success(status, response);

                    self._destroy(counter);

                    return r;
                }

                return options.error(status, response);
            }
        });

        return false;
    }
}
