import Helpers from './extensions/Helpers';
import Translator from './extensions/Translator';

let $ = jQuery,
    $document = $(document) as JQuery<any>,
    waiting = false,
    bootingCallbacks: Array<[(aio: AIO) => void, boolean]> = [],
    actions: Record<string, (selector: string, aio: AIO) => void> = {},
    initialized: Record<string, { disconnect(): void } | null> = {},
    defaultOptions: Record<string, unknown> = {
        pjax_container_selector: '#pjax-container',
    };

export default class AIO {
    token: string | null;
    lang: Record<string, string> | ReturnType<AIO['Translator']> | null;
    config: Record<string, unknown>;
    helpers: Record<string, unknown>;
    [key: string]: unknown;

    constructor(config: Record<string, unknown>) {
        this.token = null;
        this.lang = null;
        this.config = {};
        this.helpers = {};

        // 工具函数
        new Helpers(this as unknown as AIOInstance);

        this.withConfig(config);
    }

    /**
     * 初始化事件监听方法
     */
    booting(callback: (aio: AIO) => void, once?: boolean): AIO {
        once = once === undefined ? true : once;

        bootingCallbacks.push([callback, once]);

        return this;
    }

    /**
     * 初始化事件监听方法，每个请求都会触发
     */
    bootingEveryRequest(callback: (aio: AIO) => void): AIO {
        return this.booting(callback, false);
    }

    /**
     * 初始化
     */
    boot(): void {
        let _this = this,
            callbacks = bootingCallbacks;

        bootingCallbacks = [];

        callbacks.forEach((data) => {
            data[0](this);

            if (data[1] === false) {
                bootingCallbacks.push(data);
            }
        });

        // 脚本加载完毕后重新触发
        _this.onPjaxLoaded(_this.boot.bind(this));
    }

    /**
     * 监听所有js脚本加载完毕事件，需要用此方法代替 $.ready 方法
     * 此方法允许在iframe中监听父窗口的事件
     */
    ready(callback: (e?: JQuery.Event) => void, _window?: Window & { AIO: AIO; $: JQueryStatic }): JQuery | void {
        let _this = this;

        if (!_window || _window === (window as any)) {
            if (!waiting) {
                return $(callback as any);
            }

            return _this.onPjaxLoaded(callback);
        }

        function run(e?: JQuery.Event): void {
            _window!.$(_this.config.pjax_container_selector as string).one('pjax:loaded', run);

            callback(e);
        }

        _window.AIO.ready(run);
    }

    /**
     * 监听动态生成元素.
     */
    init(selector: string, callback: ($el: JQuery, id: string) => void, options?: Record<string, unknown>): void {
        let self = this,
            clear = function (): void {
                if (initialized[selector]) {
                    initialized[selector]!.disconnect();
                }
            };

        $document.one('pjax:complete', clear);

        clear();

        setTimeout(function () {
            initialized[selector] = ($ as any).initialize(
                selector,
                function (this: HTMLElement) {
                    let $this = $(this),
                        id = $this.attr('id');

                    if ($this.attr('initialized')) {
                        return;
                    }
                    $this.attr('initialized', '1');

                    // 如果没有ID，则自动生成
                    if (!id) {
                        id = '_' + (self.helpers as any).random();
                        $this.attr('id', id!);
                    }

                    callback.call(this, $this, id!);
                },
                options,
            );
        });
    }

    /**
     * 清理注册过的init回调.
     */
    offInit(selector: string): void {
        if (initialized[selector]) {
            initialized[selector]!.disconnect();
        }

        $(document).trigger('aio:init:off', [selector, initialized[selector]]);

        initialized[selector] = null;
    }

    /**
     * 主动触发 ready 事件
     */
    triggerReady(): void {
        if (!waiting) {
            return;
        }

        $(() => {
            $document.trigger('pjax:loaded');
        });
    }

    /**
     * 等待JS脚本加载完成
     */
    wait(value?: boolean): AIO {
        waiting = value !== false;

        $document.trigger('aio:waiting');

        return this;
    }

    /**
     * 使用pjax重载页面
     */
    reload(url?: string): void {
        let container = this.config.pjax_container_selector as string,
            opt: Record<string, unknown> = { container: container };

        if ($(container).length) {
            url && (opt.url = url);

            ($ as any).pjax.reload(opt);

            return;
        }

        if (url) {
            location.href = url;
        } else {
            location.reload();
        }
    }

    /**
     * 监听pjax加载js脚本完毕事件方法，此事件在 pjax:complete 事件之后触发
     */
    onPjaxLoaded(callback: (e?: JQuery.Event) => void, once?: boolean): JQuery {
        once = once === undefined ? true : once;

        if (once) {
            return $document.one('pjax:loaded', callback);
        }

        return $document.on('pjax:loaded', callback);
    }

    /**
     * 监听pjax加载完毕完毕事件方法
     */
    onPjaxComplete(callback: (e?: JQuery.Event) => void, once?: boolean): JQuery {
        once = once === undefined ? true : once;

        if (once) {
            return $document.one('pjax:complete', callback);
        }

        return $document.on('pjax:complete', callback);
    }

    withConfig(config: Record<string, unknown>): AIO {
        this.config = $.extend(defaultOptions, config);
        this.withLang(config.lang as Record<string, string>);
        this.withToken(config.token as string);

        delete config.lang;
        delete config.token;

        return this;
    }

    withToken(token: string): AIO {
        token && (this.token = token);

        return this;
    }

    withLang(lang: Record<string, string>): AIO {
        if (lang && typeof lang === 'object') {
            this.lang = this.Translator(lang);
        }

        return this;
    }

    // 语言包
    Translator(lang: Record<string, string>): InstanceType<typeof Translator> {
        return new Translator(this as unknown as AIOInstance, lang);
    }

    // 注册动作
    addAction(name: string, callback: (selector: string, aio: AIO) => void): void {
        if (typeof callback === 'function') {
            actions[name] = callback;
        }
    }

    // 获取动作
    actions(): Record<string, (selector: string, aio: AIO) => void> {
        return actions;
    }
}
