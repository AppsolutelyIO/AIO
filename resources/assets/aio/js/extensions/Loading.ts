
let tpl = '<div class="aio-loading d-flex items-center align-items-center justify-content-center pin" style="{style}">{svg}</div>',
    loadingSelector = '.aio-loading',
    LOADING_SVG: string[] = [
        '<svg xmlns="http://www.w3.org/2000/svg" class="mx-auto block" style="width:{width};{svg_style}" viewBox="0 0 120 30" fill="{color}"><circle cx="15" cy="15" r="15"><animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="15;9;15" calcMode="linear" repeatCount="indefinite"/><animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite" /></circle><circle cx="60" cy="15" r="9" fill-opacity="0.3"><animate attributeName="r" from="9" to="9" begin="0s" dur="0.8s" values="9;15;9" calcMode="linear" repeatCount="indefinite" /><animate attributeName="fill-opacity" from="0.5" to="0.5" begin="0s" dur="0.8s" values=".5;1;.5" calcMode="linear" repeatCount="indefinite" /></circle><circle cx="105" cy="15" r="15"><animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="15;9;15" calcMode="linear" repeatCount="indefinite" /><animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite" /></circle></svg>',
    ];

interface LoadingOptions {
    container: JQuery | string;
    zIndex: number;
    width: string;
    color: string;
    background: string;
    style: string;
    svg: string;
}

class Loading {
    private $container: JQuery;

    constructor(AIO: AIOInstance, options?: Partial<LoadingOptions>) {
        const merged = $.extend({
            container: AIO.config.pjax_container_selector,
            zIndex: 100,
            width: '52px',
            color: (AIO.color as Record<string, string>).dark60,
            background: '#fff',
            style: '',
            svg: LOADING_SVG[0]
        }, options) as LoadingOptions;

        let _this = this,
            defStyle = 'position:absolute;',
            content: JQuery;

        _this.$container = $(merged.container);

        content = $(
            tpl
                .replace('{svg}', merged.svg)
                .replace('{color}', merged.color)
                .replace('{color}', merged.color)
                .replace('{width}', merged.width)
                .replace('{style}', `${defStyle}background:${merged.background};z-index:${merged.zIndex};${merged.style}`)
        );
        content.appendTo(_this.$container);
    }

    destroy(): void {
        this.$container.find(loadingSelector).remove();
    }
}

function destroyAll(): void {
    $(loadingSelector).remove();
}

interface FullScreenLoadingOptions {
    zIndex: number;
    width: string;
    shade: string;
    background: string;
    top: number;
    svg: string;
    [key: string]: unknown;
}

function extend(AIO: AIOInstance): void {
    (AIO as Record<string, unknown>).loading = function (options?: Partial<FullScreenLoadingOptions> | false): ReturnType<typeof setTimeout> | Loading | undefined {
        if (options === false) {
            return setTimeout(destroyAll, 70);
        }
        const merged = $.extend({
            zIndex: 999991014,
            width: '58px',
            shade: 'rgba(255, 255, 255, 0.1)',
            background: 'transparent',
            top: 200,
            svg: LOADING_SVG[0],
        }, options) as FullScreenLoadingOptions;

        var win = $(window),
            $container = $('<div class="aio-loading" style="z-index:'+merged.zIndex+';width:300px;position:fixed"></div>'),
            shadow = $('<div class="layui-layer-shade aio-loading" style="z-index:'+(merged.zIndex-2)+'; background-color:'+merged.shade+'"></div>');

        $container.appendTo('body');

        if (merged.shade) {
            shadow.appendTo('body');
        }

        function resize(): void {
            $container.css({
                left: ((win.width() as number) - 300)/2,
                top: ((win.height() as number) - merged.top)/2
            });
        }
        win.on('resize', resize);
        resize();

        ($container as unknown as Record<string, (opts: Record<string, unknown>) => Loading>).loading(merged as unknown as Record<string, unknown>);
    };

    ($.fn as Record<string, unknown>).loading = function (this: JQuery, opt?: Partial<LoadingOptions> | false): JQuery | Loading {
        if (opt === false) {
            return $(this).find(loadingSelector).remove();
        }

        const opts = (opt || {}) as Record<string, unknown>;
        opts.container = $(this);

        return new Loading(AIO, opts as Partial<LoadingOptions>);
    };

    ($.fn as Record<string, unknown>).buttonLoading = function (this: JQuery, start?: boolean): JQuery {
        let $this = $(this),
            loadingId = $this.attr('data-loading'),
            content: string;

        if (start === false) {
            if (! loadingId) {
                return $this;
            }

            $this.find('.waves-ripple').remove();

            return $this
                .removeClass('disabled btn-loading waves-effect')
                .removeAttr('disabled')
                .removeAttr('data-loading')
                .html(
                    $this.find('.' + loadingId).html() as string
                );
        }

        if (loadingId) {
            return $this;
        }

        content = $this.html() as string;

        loadingId = 'ld-'+AIO.helpers.random();

        let loadingSvg = `<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>`;
        let btnClass = ['btn', 'layui-layer-btn0', 'layui-layer-btn1'];

        for (let i in btnClass) {
            if ($this.hasClass(btnClass[i])) {
                loadingSvg = LOADING_SVG[0].replace('{color}', 'currentColor').replace('{width}', '50px;height:11px;');
            }
        }

        return $this
            .addClass('disabled btn-loading')
            .attr('disabled', 'disabled')
            .attr('data-loading', loadingId)
            .html(`
<div class="${loadingId}" style="display:none">${content}</div>
${loadingSvg}
`);
    }

}

export default extend
