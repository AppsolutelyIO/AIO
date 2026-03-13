
interface RowSelectorOptions {
    checkboxSelector: string;
    selectAllSelector: string;
    background: string;
    clickRow: boolean;
    container: string;
}

export default class RowSelector {
    public options: RowSelectorOptions;

    constructor(options: Partial<RowSelectorOptions>) {
        let _this = this;

        _this.options = $.extend({
            checkboxSelector: '',
            selectAllSelector: '',
            background: 'rgba(255, 255,213,0.4)',
            clickRow: false,
            container: 'table',
        }, options) as RowSelectorOptions;

        _this.init()
    }

    init(): void {
        let options = this.options,
            checkboxSelector = options.checkboxSelector,
            $document = $(document),
            selectAll = options.selectAllSelector;

        $(selectAll).on('change', function(this: HTMLInputElement) {
            let checked = this.checked;

            $.each($(this).parents(options.container).find(checkboxSelector), function (_, checkbox) {
                let $this = $(checkbox);

                if (! $this.attr('disabled')) {
                    $this.prop('checked', checked).trigger('change');
                }
            });
        });
        if (options.clickRow) {
            $document.off('click', checkboxSelector).on('click', checkboxSelector, function (e: JQuery.ClickEvent) {
                if (typeof e.cancelBubble != "undefined") {
                    e.cancelBubble = true;
                }
                if (typeof e.stopPropagation != "undefined") {
                    e.stopPropagation();
                }
            });

            $document.off('click', options.container+' tr').on('click', options.container+' tr', function () {
                $(this).find(checkboxSelector).click();
            });
        }

        $document.off('change', checkboxSelector).on('change', checkboxSelector, function (this: HTMLInputElement) {
            var tr = $(this).closest('tr');
            if (this.checked) {
                tr.css('background-color', options.background);

                if ($(checkboxSelector + ':checked').length === $(checkboxSelector).length) {
                    $(selectAll).prop('checked', true)
                }
            } else {
                tr.css('background-color', '');
            }
        });
    }

    getSelectedKeys(): unknown[] {
        let selected: unknown[] = [];

        $(this.options.checkboxSelector+':checked').each(function() {
            var id = $(this).data('id');
            if (selected.indexOf(id) === -1) {
                selected.push(id);
            }
        });

        return selected;
    }

    getSelectedRows(): Array<{ id: unknown; label: unknown }> {
        let selected: Array<{ id: unknown; label: unknown }> = [];

        $(this.options.checkboxSelector+':checked').each(function() {
            var id = $(this).data('id'), i: string, exist: boolean | undefined;

            for (i in selected) {
                if (selected[i].id === id) {
                    exist = true
                }
            }

            exist || selected.push({'id': id, 'label': $(this).data('label')})
        });

        return selected;
    }
}
