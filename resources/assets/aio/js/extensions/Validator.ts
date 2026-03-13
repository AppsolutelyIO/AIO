
export default class Validator {
    constructor(AIO: AIOInstance) {
        AIO.validator = this as unknown as Record<string, unknown>;
    }

    extend(rule: string, callback: (...args: unknown[]) => boolean, message?: string | null): void {
        let DEFAULTS = ($.fn as Record<string, unknown>).validator as Record<string, Record<string, Record<string, unknown>>>;

        DEFAULTS.Constructor.DEFAULTS.custom[rule] = callback;
        DEFAULTS.Constructor.DEFAULTS.errors[rule] = message || null;
    }
}
