
import Helper from './Grid/Helper'
import Tree from './Grid/Tree'
import Orderable from './Grid/Orderable'
import AsyncTable from './Grid/AsyncTable'

(function (w, $) {
    let AIO = w.AIO,
        h = new Helper();

    // 树形表格
    AIO.grid.Tree = function (opts) {
        return new Tree(h, opts);
    };

    // 列表行可排序
    AIO.grid.Orderable = function (opts) {
        return new Orderable(h, opts);
    };

    // 异步表格
    AIO.grid.AsyncTable =function (opts) {
        return new AsyncTable(opts)
    }
})(window, jQuery);