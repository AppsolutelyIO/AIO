
import AIO from "../AIO";

let $d = $(document);

export default class Pjax {
    constructor(AIO: AIO) {
        this.boot(AIO)
    }

    boot(AIO: AIO): void {
        let container = AIO.config.pjax_container_selector as string,
            formContainer = 'form[pjax-container]',
            scriptContainer = 'script[data-exec-on-popstate]';

        ($ as any).pjax.defaults.timeout = 5000;
        ($ as any).pjax.defaults.maxCacheLength = 0;

        $('a:not(a[target="_blank"])').click(function (event: JQuery.Event) {
            ($ as any).pjax.click(event, container, {
                container: container,
                fragment: container,
                timeout: 8000
            });
        });

        $d.on('pjax:timeout', function (event: JQuery.Event) {
            event.preventDefault();
        });

        $d.off('submit', formContainer).on('submit', formContainer, function (event: JQuery.Event) {
            ($ as any).pjax.submit(event, container)
        });

        $d.on("pjax:popstate", function () {
            $d.one("pjax:end", function (event: JQuery.Event) {
                $(event.target!).find(scriptContainer).each(function () {
                    ($ as any).globalEval((this as HTMLScriptElement).text || (this as HTMLScriptElement).textContent || (this as HTMLScriptElement).innerHTML || '');
                });
            });
        });

        $d.on('pjax:send', function (xhr: any) {
            if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
                $(formContainer).find('[type="submit"],.submit').buttonLoading();
            }
            (AIO as any).NP.start();
        });

        $d.on('pjax:complete', function (xhr: any) {
            if (xhr.relatedTarget && xhr.relatedTarget.tagName && xhr.relatedTarget.tagName.toLowerCase() === 'form') {
                $(formContainer).find('[type="submit"],.submit').buttonLoading(false)
            }

            var $body = $('body');

            // 移除遮罩层
            $(".modal-backdrop").remove();
            $body.removeClass("modal-open");

            // 刷新页面后需要重置modal弹窗设置的间隔
            if ($body.css('padding-right')) {
                $body.css('padding-right', '');
            }
        });

        $d.on('pjax:loaded', () => {
            (AIO as any).NP.done();
        });
    }
}
