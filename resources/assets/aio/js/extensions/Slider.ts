
declare class PerfectScrollbar {
    constructor(selector: string);
}

interface SliderOptions {
    target: string | HTMLElement | null;
    class: string | null;
    autoDestory: boolean;
}

let idPrefix = 'aio-slider-',
    template = `<div id="{id}" class="slider-panel {class}">
    <div class="slider-content position-fixed p-1 ps ps--active-y"></div>
</div>`;

export default class Slider {
    private options: SliderOptions;
    private id: string;
    private $target: JQuery;
    private $container: JQuery;

    constructor(AIO: AIOInstance, options: Partial<SliderOptions>) {
        let _this = this;

        _this.options = $.extend({
            target: null,
            class: null,
            autoDestory: true,
        }, options) as SliderOptions;

        _this.id = idPrefix + AIO.helpers.random();
        _this.$target = $(_this.options.target as string | HTMLElement);
        _this.$container = $(
            template
                .replace('{id}', _this.id)
                .replace('{class}', _this.options.class || '')
        );

        _this.$container.appendTo('body');
        _this.$container.find('.slider-content').append(_this.$target);

        new PerfectScrollbar(`#${_this.id} .slider-content`);

        if (_this.options.autoDestory) {
            (AIO as Record<string, (cb: () => void) => void>).onPjaxComplete(() => {
                _this.destroy();
            });
        }
    }

    open(): void {
        this.$container.addClass('open');
    }

    close(): void {
        this.$container.removeClass('open');
    }

    toggle(): void {
        this.$container.toggleClass('open');
    }

    destroy(): void {
        this.$container.remove()
    }
}
