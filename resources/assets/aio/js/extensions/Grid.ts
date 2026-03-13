
import RowSelector from './RowSelector';

let defaultName = '_def_';

interface AsyncGridOptions {
    selector: string | null;
    bodySelector: string;
    tableSelector: string;
    queryName: string | null;
    url: string | null;
    loadingStyle: string;
    before: ($box: JQuery, url: string) => void;
    after: ($box: JQuery, url: string, html?: string) => void;
}

export default class Grid {
    public selectors: Record<string, RowSelector>;

    constructor(AIO: AIOInstance) {
        AIO.grid = this as unknown as Record<string, unknown>;

        this.selectors = {};
    }

    addSelector(selector: RowSelector, name?: string): void {
        this.selectors[name || defaultName] = selector
    }

    selected(name?: string): unknown[] {
        return this.selectors[name || defaultName].getSelectedKeys()
    }

    selectedRows(name?: string): Array<{ id: unknown; label: unknown }> {
        return this.selectors[name || defaultName].getSelectedRows()
    }

    async(options: Partial<AsyncGridOptions>): AsyncGrid {
        return new AsyncGrid(options);
    }
}

class AsyncGrid {
    private options: AsyncGridOptions;
    private $box: JQuery;
    private $body: JQuery;
    private loading: boolean;

    constructor(options: Partial<AsyncGridOptions>) {
        let nullFun = function (): void {};

        const merged = $.extend({
            selector: null,
            bodySelector: '.async-body',
            tableSelector: '.async-table',
            queryName: null,
            url: null,
            loadingStyle: 'height:240px;',
            before: nullFun,
            after: nullFun,
        }, options) as AsyncGridOptions;

        this.options = merged;
        this.$box = $(merged.selector as string);
        this.$body = this.$box.find(merged.bodySelector);
        this.loading = false;
    }

    render(url?: string, callback?: ($box: JQuery, url: string, html: string) => void): void {
        let self = this, options = self.options;

        url = url || options.url as string;

        if (self.loading || url.indexOf('javascript:') !== -1) {
            return;
        }
        self.loading = true;

        let $box = self.$box,
            $body = self.$body,
            reqName = options.queryName,
            tableSelector = options.tableSelector,
            $table = $body.find(tableSelector),
            events: Record<number, string> = {0: 'grid:rendering', 1: 'grid:render', 2: 'grid:rendered'},
            before = options.before,
            after = options.after;

        before($box, url);
        $box.trigger(events[0], [url]);
        $body.trigger(events[0], [url]);

        let loadingOptions: Record<string, unknown> = {background: 'transparent'}
        if ($body.find(`${tableSelector} tbody tr`).length <= 2) {
            loadingOptions['style'] = options.loadingStyle;
        }
        ($table as unknown as Record<string, (opts: Record<string, unknown>) => void>).loading(loadingOptions);
        (window as Window & { AIO: AIOInstance }).AIO.NP.start();

        if (url.indexOf('?') === -1) {
            url += '?';
        }

        if (url.indexOf(reqName as string) === -1) {
            url += '&'+reqName+'=1';
        }

        history.pushState({}, '', url.replace(reqName+'=1', ''));

        $box.data('current', url);

        (window as Window & { AIO: AIOInstance }).AIO.helpers.asyncRender(url, function (html: string) {
            self.loading = false;
            (window as Window & { AIO: AIOInstance }).AIO.NP.done();

            $body.html(html);

            let refresh = function (): void {
                self.render($box.data('current') as string);
            };

            $box.off(events[1]).on(events[1], refresh);
            $body.off(events[1]).on(events[1], refresh);
            $table.on(events[1], refresh);

            $box.find('.grid-refresh').off('click').on('click', function () {
                refresh();

                return false;
            });

            $box.find('.pagination .page-link').on('click', loadLink);
            $box.find('.per-pages-selector .dropdown-item a').on('click', loadLink);
            $box.find('.grid-column-header a').on('click', loadLink);

            $box.find('form').off('submit').on('submit', function () {
                var action = $(this).attr('action') as string;

                if ($(this).attr('method') === 'post') {
                    return;
                }

                if (action.indexOf('?') === -1) {
                    action += '?';
                }

                self.render(action+'&'+$(this).serialize());

                return false;
            });

            $box.find('.filter-box .reset').on('click', loadLink);

            $box.find('.grid-selector a').on('click', loadLink);

            $box.trigger(events[2], [url, html]);
            $body.trigger(events[2], [url, html]);
            $table.trigger(events[2], [url, html]);

            after($box, url as string, html);

            callback && callback($box, url as string, html);
        });

        function loadLink(this: HTMLElement): boolean {
            self.render($(this).attr('href'));

            return false;
        }
    }
}
