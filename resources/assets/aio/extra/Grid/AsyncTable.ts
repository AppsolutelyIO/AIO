/**
 * 异步渲染表格
 */

declare const AIO: AIOInstance;

interface AsyncTableOptions {
    container: string;
}

export default class AsyncTable {
    options: AsyncTableOptions;

    constructor(options: Partial<AsyncTableOptions>) {
        this.options = $.extend({
            container: '.table-card',
        }, options) as AsyncTableOptions;

        let _this = this;

        $(this.options.container).on('table:load', function () {
            _this.load($(this).data('url'), $(this));
        });
    }

    load(url: string, box: JQuery): void {
        let _this = this;

        if (! url) {
            return;
        }

        // 缓存当前请求地址
        box.attr('data-current', url);

        (box as any).loading({background: 'transparent!important'});

        AIO.helpers.asyncRender(url, function (html: string) {
            (box as any).loading(false);
            box.html(html);
            _this.bind(box);
            box.trigger('table:loaded');
        });
    }

    bind(box: JQuery): void {
        let _this = this;

        function loadLink(this: HTMLElement): boolean {
            _this.load($(this).attr('href')!, box);

            return false;
        }

        box.find('.pagination .page-link').on('click', loadLink);
        box.find('.grid-column-header a').on('click', loadLink);

        box.find('form').on('submit', function () {
            _this.load($(this).attr('action')!+'&'+$(this).serialize(), box);

            return false;
        });

        box.find('.filter-box .reset').on('click', loadLink);

        box.find('.grid-selector a').on('click', loadLink);

        AIO.ready(function () {
            setTimeout(function () {
                box.find('.grid-refresh').off('click').on('click', function () {
                    _this.load(box.data('current'), box);

                    return false;
                })
            }, 10)
        })
    }
}
