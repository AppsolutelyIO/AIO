export default class Translator {
    private aio: AIOInstance;
    private lang: Record<string, string>;
    [key: string]: unknown;

    constructor(AIO: AIOInstance, lang: Record<string, string>) {
        this.aio = AIO;
        this.lang = lang;

        for (let i in lang) {
            if (!(AIO.helpers as any).isset(this, i)) {
                (this as Record<string, unknown>)[i] = lang[i];
            }
        }
    }

    trans(label: string, replace?: Record<string, string>): string {
        let _this = this,
            helpers = _this.aio.helpers;

        if (typeof _this.lang !== 'object') {
            return label;
        }

        var text = (helpers as any).get(_this.lang, label) as string | null,
            i: string;
        if (!helpers.isset(text)) {
            return label;
        }

        if (!replace) {
            return text as string;
        }

        for (i in replace) {
            text = helpers.replace(text as string, ':' + i, replace[i]);
        }

        return text as string;
    }
}
