// ESM wrapper for nprogress CJS module
// @rollup/plugin-commonjs wraps CJS exports behind a lazy __require function
// in namespace imports. We resolve it here to get the actual NProgress object.
import * as NProgressNamespace from 'nprogress';

type NProgressType = {
    configure: (options: Record<string, unknown>) => void;
    start: () => void;
    done: (force?: boolean) => void;
    set: (n: number) => void;
    inc: (amount?: number) => void;
    [key: string]: unknown;
};

// In Vite IIFE builds, CJS modules end up as { __require: fn } namespace.
// Resolve through __require if present, otherwise use the namespace directly.
const ns = NProgressNamespace as any;
const NProgress: NProgressType =
    typeof ns.__require === 'function'
        ? (ns.__require as () => NProgressType)()
        : (NProgressNamespace as unknown as NProgressType);

export default NProgress;
