import debounce from './Debounce';

interface LoadFieldsOptions {
    textField: string;
    idField: string;
    values?: string | string[];
    fields: string[];
    urls: string[];
    group: string;
}

export default class Helpers {
    public aio: AIOInstance;
    public debounce: typeof debounce;

    constructor(AIO: AIOInstance) {
        AIO.helpers = this as unknown as AIOInstance['helpers'];

        this.aio = AIO;

        this.debounce = debounce;
    }

    len(obj: unknown): number {
        if (typeof obj !== 'object') {
            return 0;
        }
        let i: string,
            len = 0;

        for (i in obj as Record<string, unknown>) {
            len += 1;
        }

        return len;
    }

    isset(_var: unknown, key?: string): boolean {
        let isset = typeof _var !== 'undefined' && _var !== null;

        if (typeof key === 'undefined') {
            return isset;
        }

        return isset && typeof (_var as Record<string, unknown>)[key] !== 'undefined';
    }

    empty(obj: unknown, key?: string): boolean {
        return !(this.isset(obj, key) && (obj as Record<string, unknown>)[key as string]);
    }

    get(arr: unknown, key: string, def?: unknown): unknown {
        def = null;

        if (this.len(arr) < 1) {
            return def;
        }

        const keys = String(key).split('.');

        for (var i = 0; i < keys.length; i++) {
            if (this.isset(arr, keys[i])) {
                arr = (arr as Record<string, unknown>)[keys[i]];
            } else {
                return def;
            }
        }

        return arr;
    }

    has(arr: unknown, key: string): boolean {
        if (this.len(arr) < 1) return false;
        const keys = String(key).split('.');

        for (var i = 0; i < keys.length; i++) {
            if (this.isset(arr, keys[i])) {
                arr = (arr as Record<string, unknown>)[keys[i]];
            } else {
                return false;
            }
        }

        return true;
    }

    inObject(arr: unknown, val: unknown, strict?: boolean): boolean {
        if (this.len(arr) < 1) {
            return false;
        }

        for (var i in arr as Record<string, unknown>) {
            if (strict) {
                if (val === (arr as Record<string, unknown>)[i]) {
                    return true;
                }
                continue;
            }

            if (val == (arr as Record<string, unknown>)[i]) {
                return true;
            }
        }
        return false;
    }

    equal(array: unknown, array2: unknown, strict?: boolean): boolean {
        if (!array || !array2) {
            return false;
        }

        let len1 = this.len(array),
            len2 = this.len(array2),
            i: string;

        if (len1 !== len2) {
            return false;
        }

        for (i in array as Record<string, unknown>) {
            if (!this.isset(array2, i)) {
                return false;
            }

            if ((array as Record<string, unknown>)[i] === null && (array2 as Record<string, unknown>)[i] === null) {
                return true;
            }

            if (
                typeof (array as Record<string, unknown>)[i] === 'object' &&
                typeof (array2 as Record<string, unknown>)[i] === 'object'
            ) {
                if (
                    !this.equal((array as Record<string, unknown>)[i], (array2 as Record<string, unknown>)[i], strict)
                ) {
                    return false;
                }
                continue;
            }

            if (strict) {
                if ((array as Record<string, unknown>)[i] !== (array2 as Record<string, unknown>)[i]) {
                    return false;
                }
            } else {
                if ((array as Record<string, unknown>)[i] != (array2 as Record<string, unknown>)[i]) {
                    return false;
                }
            }
        }
        return true;
    }

    replace(str: string, replace: string, subject: string): string {
        if (!str) {
            return str;
        }

        return str.replace(new RegExp(replace, 'g'), subject);
    }

    random(len?: number): string {
        return Math.random()
            .toString(12)
            .substr(2, len || 16);
    }

    previewImage(src: string, width?: string, title?: string): void {
        let AIO = this.aio,
            img = new Image(),
            win = this.isset(window.top) ? (top as Window) : window,
            clientWidth = Math.ceil(win.screen.width * 0.6),
            clientHeight = Math.ceil(win.screen.height * 0.8);

        img.style.display = 'none';
        img.style.height = 'auto';
        img.style.width = width || '100%';
        img.src = src;

        document.body.appendChild(img);

        (AIO as Record<string, unknown>).loading && (AIO as Record<string, () => void>).loading();
        img.onload = function (this: HTMLImageElement) {
            (AIO as Record<string, (state: boolean) => void>).loading(false);
            let srcw = this.width,
                srch = this.height,
                imgWidth = srcw > clientWidth ? clientWidth : srcw,
                imgHeight = Math.ceil(imgWidth * (srch / srcw));

            imgHeight = imgHeight > clientHeight ? clientHeight : imgHeight;

            title = title || (src.split('/').pop() as string);

            if (title.length > 50) {
                title = title.substr(0, 50) + '...';
            }

            layer.open({
                type: 1,
                shade: 0.2,
                title: false,
                maxmin: false,
                shadeClose: true,
                closeBtn: 2,
                content: $(img),
                area: [imgWidth + 'px', imgHeight + 'px'],
                skin: 'layui-layer-nobg',
                end: function () {
                    document.body.removeChild(img);
                },
            });
        };
        img.onerror = function () {
            (AIO as Record<string, (state: boolean) => void>).loading(false);
            AIO.error((AIO.lang as any).trans('no_preview'));
        };
    }

    asyncRender(
        url: string,
        done: (html: string) => void,
        error?: (a: JQueryXHR, b: string, c: string) => unknown,
    ): void {
        let AIO = this.aio;

        $.ajax(url).then(
            function (data: string) {
                const resolved = AIO.assets.resolveHtml(data, AIO.triggerReady) as unknown as { render(): string };
                done(resolved.render());
            },
            function (a: JQueryXHR, b: string, c: string) {
                if (error) {
                    if (error(a, b, c) === false) {
                        return false;
                    }
                }

                AIO.handleAjaxError(a, b, c);
            },
        );
    }

    loadFields(_this: HTMLElement | JQuery, options: LoadFieldsOptions): void {
        const AIO = this.aio;

        let refreshOptions = function (url: string, target: JQuery): void {
            (AIO as Record<string, unknown>).loading && (AIO as Record<string, () => void>).loading();

            $.ajax(url).then(function (data: Array<Record<string, unknown>>) {
                (AIO as Record<string, (state: boolean) => void>).loading(false);
                target.find('option').remove();

                $.map(data, function (d: Record<string, unknown>) {
                    target.append(
                        new Option(d[options.textField] as string, d[options.idField] as string, false, false),
                    );
                });

                $(target)
                    .val(String(target.data('value')).split(','))
                    .trigger('change');
            });
        };

        let values: string[] = [];

        if (!options.values) {
            $(_this)
                .find('option:selected')
                .each(function () {
                    if (String((this as HTMLOptionElement).value) === '0' || (this as HTMLOptionElement).value) {
                        values.push((this as HTMLOptionElement).value);
                    }
                });
        } else {
            if (typeof options.values === 'string') {
                values = [options.values];
            } else {
                values = options.values;
            }
        }

        if (!values.length) {
            return;
        }

        options.fields.forEach(function (field: string, index: number) {
            var target = $(_this)
                .closest(options.group)
                .find('.' + options.fields[index]);

            if (!values.length) {
                return;
            }
            refreshOptions(
                options.urls[index] + (options.urls[index].match(/\?/) ? '&' : '?') + 'q=' + values.join(','),
                target,
            );
        });
    }
}
