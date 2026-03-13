
declare const seajs: {
    use(deps: string[], callback?: (...args: unknown[]) => void): void;
};

interface FilteredScripts {
    scripts: JQuery;
    contents: JQuery & { render: () => string };
    js: string[];
}

export default class AssetsLoader {
    private aio: AIOInstance;

    constructor(AIO: AIOInstance) {
        let _this = this;

        _this.aio = AIO;

        AIO.assets = {
            load: _this.load.bind(_this),
            resolveHtml: _this.resolveHtml.bind(_this)
        };
    }

    load(urls: string[], callback?: (args?: unknown) => void, args?: unknown): void {
        let _this = this;
        if (urls.length < 1) {
            (! callback) || callback(args);

            _this.fire();
            return;
        }

        seajs.use([urls.shift() as string], function () {
            _this.load(urls, callback, args);
        });
    }

    filterScripts(content: string | JQuery): FilteredScripts {
        var obj = {} as FilteredScripts;

        if (typeof content == 'string') {
            content = $(content);
        }

        obj.scripts = this.findAll(content, 'script[src]').remove();
        obj.contents = content.not(obj.scripts) as JQuery & { render: () => string };

        obj.contents.render = this.toString;
        obj.js = (function () {
            var urls: string[] = [];
            obj.scripts.each(function (k: number, v: HTMLElement) {
                if ((v as HTMLScriptElement).src) {
                    urls.push((v as HTMLScriptElement).src);
                }
            });

            return urls;
        })();

        return obj;
    }

    resolveHtml(content: string, callback?: (contents: JQuery) => void): JQuery & { render: () => string } {
        var obj = this.filterScripts(content);

        this.load(obj.js, function () {
            (!callback) || callback(obj.contents);
        });

        return obj.contents;
    }

    findAll($el: string | JQuery, selector: string): JQuery {
        if (typeof $el === 'string') {
            $el = $($el);
        }

        return $el.filter(selector).add($el.find(selector));
    }

    fire(): void {
        (this.aio as Record<string, unknown>).wait && (this.aio as Record<string, () => void>).wait();

        setTimeout(this.aio.triggerReady, 1);
    }

    toString(this: JQuery): string {
        var html = '', out: string | undefined;

        this.each(function (k: number, v: HTMLElement) {
            if ((out = v.outerHTML)) {
                html += out;
            }
        });

        return html;
    }
}
