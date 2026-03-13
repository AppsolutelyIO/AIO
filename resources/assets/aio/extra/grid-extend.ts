
import Helper from './Grid/Helper'
import Tree from './Grid/Tree'
import Orderable from './Grid/Orderable'
import AsyncTable from './Grid/AsyncTable'

(function (w: Window & { AIO: AIOInstance }, $: JQueryStatic) {
    let AIO = w.AIO,
        h = new Helper();

    // 树形表格
    (AIO.grid as any).Tree = function (opts: Record<string, unknown>) {
        return new Tree(h, opts);
    };

    // 列表行可排序
    (AIO.grid as any).Orderable = function (opts: Record<string, unknown>) {
        return new Orderable(h, opts);
    };

    // 异步表格
    (AIO.grid as any).AsyncTable = function (opts: Record<string, unknown>) {
        return new AsyncTable(opts)
    }
})(window as any, jQuery);
