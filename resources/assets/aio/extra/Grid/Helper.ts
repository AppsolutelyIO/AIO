
export default class Helper {
    getChildren(all: JQuery, parent: JQuery | HTMLElement): HTMLElement[] {
        let _this = this,
            arr: HTMLElement[] = [],
            isBreak = false,
            firstTr: JQuery | undefined;

        all.each(function (_, v) {
            // 过滤非tr标签
            if (! _this.isTr(v) || isBreak) return;

            firstTr || (firstTr = $(v));

            // 非连续的子节点
            if (firstTr && ! _this.isChildren(parent, firstTr)) {
                return;
            }

            if (_this.isChildren(parent, v)) {
                arr.push(v)
            } else {
                isBreak = true;
            }
        });

        return arr;
    }

    swapable(_o: JQuery | undefined, depth: number): boolean | undefined {
        if (
            _o
            && _o.length
            && depth === this.getDepth(_o)
        ) {
            return true
        }
    }

    sibling(all: JQuery, depth: number): JQuery | undefined {
        let _this = this,
            next: JQuery | undefined;

        all.each(function (_, v) {
            if (_this.getDepth(v) === depth && ! next && _this.isTr(v)) {
                next = $(v);
            }
        });

        return next;
    }

    isChildren(parent: JQuery | HTMLElement, child: JQuery | HTMLElement): boolean {
        return this.getDepth(child) > this.getDepth(parent);
    }

    getDepth(v: JQuery | HTMLElement): number {
        return parseInt($(v).data('depth') || 0);
    }

    isTr(v: HTMLElement | JQuery): boolean {
        return $(v).prop('tagName').toLocaleLowerCase() === 'tr'
    }
}
