import Dropdown from "../../../adminlte/js/Dropdown";
import AIO from "../AIO";

let $document = $(document);

type ActionHandler = (action: string, aio: AIO) => void;

let defaultActions: Record<string, ActionHandler> = {
    // 刷新按钮
    refresh (action: string, AIO: AIO): void {
        $document.on('click', action, function () {
            (AIO as any).reload($(this).data('url'));
        });
    },
    // 删除按钮初始化
    delete (action: string, AIO: AIO): void {
        let lang = AIO.lang as any;

        $document.on('click', action, function() {
            let url = $(this).data('url'),
                redirect = $(this).data('redirect'),
                msg = $(this).data('message');

            (AIO as any).confirm(lang.delete_confirm, msg, function () {
                (AIO as any).NP.start();
                ($ as any).delete({
                    url: url,
                    success: function (response: any) {
                        (AIO as any).NP.done();

                        response.data.detail = msg;

                        if (redirect && ! response.data.then) {
                            response.data.then = {action: 'redirect', value: redirect}
                        }

                        (AIO as any).handleJsonResponse(response);
                    }
                });
            });
        });
    },
    // 批量删除按钮初始化
    'batch-delete' (action: string, AIO: AIO): void {
        $document.on('click', action, function() {
            let url = $(this).data('url'),
                name = $(this).data('name'),
                redirect = $(this).data('redirect'),
                keys = (AIO as any).grid.selected(name),
                lang = AIO.lang as any;

            if (! keys.length) {
                return;
            }
            let msg = 'ID - ' + keys.join(', ');

            (AIO as any).confirm(lang.delete_confirm, msg, function () {
                (AIO as any).NP.start();
                ($ as any).delete({
                    url: url + '/' + keys.join(','),
                    success: function (response: any) {
                        (AIO as any).NP.done();

                        if (redirect && ! response.data.then) {
                            response.data.then = {action: 'redirect', value: redirect}
                        }

                        (AIO as any).handleJsonResponse(response);
                    }
                });
            });
        });
    },

    // 图片预览
    'preview-img' (action: string, AIO: AIO): void {
        $document.on('click', action, function () {
            return (AIO as any).helpers.previewImage($(this).attr('src'));
        });
    },

    'popover' (action: string, AIO: AIO): void {
        AIO.onPjaxComplete(function () {
            $('.popover').remove();
        }, false);

        $document.on('click', action, function () {
            ($(this) as any).popover()
        });
    },

    'box-actions' (): void {
        $document.on('click', '.box [data-action="collapse"]', function (e: JQuery.Event) {
            e.preventDefault();

            $(this).find('i').toggleClass('icon-minus icon-plus');

            ($(this).closest('.box').find('.box-body').first() as any).collapse("toggle");
        });

        // Close box
        $document.on('click', '.box [data-action="remove"]', function () {
            $(this).closest(".box").removeClass().slideUp("fast");
        });
    },

    dropdown (): void {
        function hide(): void {
            $('.dropdown-menu').removeClass('show')
        }
        $document.off('click', document as any, hide)
        $document.on('click', hide);

        function toggle(this: HTMLElement, event: JQuery.Event): void {
            var $this = $(this);

            $('.dropdown-menu').each(function () {
                if ($this.next()[0] !== this) {
                    $(this).removeClass('show');
                }
            });

            ($this as any).Dropdown('toggleSubmenu')
        }

        function fix(this: HTMLElement, event: JQuery.Event): void {
            event.preventDefault()
            event.stopPropagation()

            let $this = $(this);

            setTimeout(function() {
                ($this as any).Dropdown('fixPosition')
            }, 1)
        }

        let selector = '[data-toggle="dropdown"]';

        $document.off('click',selector).on('click', selector, toggle).on('click', selector, fix);
    },
};

export default class DataActions {
    constructor(AIO: AIO) {
        let actions = $.extend(defaultActions, AIO.actions()) as Record<string, ActionHandler>,
            name: string;

        for (name in actions) {
            actions[name](`[data-action="${name}"]`, AIO);
        }
    }
}
