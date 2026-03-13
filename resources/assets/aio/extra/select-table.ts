(function (w: Window & { AIO: AIOInstance }) {

    interface SelectTableValue {
        id: string;
        label: string;
    }

    interface SelectTableOptions {
        dialog: string | null;
        container: string | null;
        input: string | null;
        button: string;
        cancel: string;
        table: string;
        multiple: boolean;
        max: number;
        values: SelectTableValue[];
        lang: {
            exceed_max_item: string;
        };
    }

    function SelectTable(this: any, options: Partial<SelectTableOptions>): void {
        options = $.extend({
            dialog: null,
            container: null,
            input: null,
            button: '.submit-btn',
            cancel: '.cancel-btn',
            table: '.async-table',
            multiple: false,
            max: 0,
            values: [],
            lang: {
                exceed_max_item: (w.AIO as any).lang.exceed_max_item || '已超出最大可选择的数量',
            },
        }, options) as SelectTableOptions;

        let self = this;

        self.options = options;
        self.$input = $((options as SelectTableOptions).input!);
        self.selected = {} as Record<string, SelectTableValue>; // 保存临时选中的ID

        self.init();
    }

    SelectTable.prototype = {
        init(this: any): void {
            let self = this,
                options = self.options as SelectTableOptions,
                values = options.values;

            self.labels = {} as Record<string, string>;

            for (let i in values) {
                self.labels[values[i]['id']] = values[i]['label']
            }

            // 保存临时选中的值
            self.resetSelected();

            $(document).on('dialog:shown', options.dialog!, function () {
                self.$dialog = $(options.dialog!);
                self.$button = self.$dialog.find(options.button);
                self.$cancel = self.$dialog.find(options.cancel);

                // 提交按钮
                self.$button.on('click', function () {
                    var selected = self.getSelectedRows();

                    self.setKeys(selected[1]);

                    self.render(selected[0]);

                    self.$dialog.trigger('dialog:close');

                    // 重置已选中数据
                    self.resetSelected();
                });

                // 取消按钮
                self.$cancel.on('click', function () {
                    self.$dialog.trigger('dialog:close');
                });

                // 绑定相关事件
                self.bind();

                // 重置已选中数据
                self.resetSelected();
            });

            // 渲染选中的数据
            self.render(values);
        },

        bind(this: any): void {
            let self = this,
                options = self.options as SelectTableOptions;

            // 表格加载完成事件
            self.$dialog.find(options.table).on('table:loaded', function () {
                let checkbox = self.getCheckbox();

                if (! options.multiple) {
                    // 移除全选按钮
                    $(this).find('.checkbox-grid-header').remove();
                }

                checkbox.on('change', function (this: HTMLInputElement) {
                    let $this = $(this),
                        id = $this.data('id') as string,
                        label = $this.data('label') as string;

                    if (this.checked) {
                        if (! options.multiple) {
                            self.selected = {};
                        }
                        self.selected[id] = {id: id, label: label};

                        // 多选
                        if (options.max && (self.getSelectedRows()[0].length > options.max)) {
                            $this.prop('checked', false);
                            delete self.selected[id];

                            return (w.AIO as any).warning(self.options.lang.exceed_max_item);
                        }
                    } else {
                        delete self.selected[id];
                    }

                    if (! options.multiple) {
                        if (this.checked) {
                            // 单选效果
                            checkbox.each(function () {
                                let $this = $(this);

                                if ($this.data('id') != id) {
                                    $this.prop('checked', false);
                                    $this.parents('tr').css('background-color', '');
                                }
                            });
                        }
                    }
                });

                // 选中默认选项
                checkbox.each(function () {
                    let $this = $(this),
                        current = $this.data('id') as string;

                    // 保存label字段
                    self.labels[current] = $this.data('label');

                    for (let i in self.selected) {
                        if (current == i) {
                            $this.prop('checked', true).trigger('change');

                            continue;
                        }
                    }

                    $this.trigger('change');
                });
            })
        },

        // 重置已选中数据
        resetSelected(this: any): void {
            let self = this,
                keys = self.getKeys();

            self.selected = {};

            for (let i in keys) {
                self.selected[keys[i]] = {id: keys[i], label: self.labels[keys[i]]};
            }
        },

        getCheckbox(this: any): JQuery {
            return this.$dialog.find('.checkbox-grid-column input[type="checkbox"]');
        },

        getSelectedRows(this: any): [SelectTableValue[], string[]] {
            let self = this,
                selected: SelectTableValue[] = [],
                ids: string[] = [];

            for (let i in self.selected) {
                if (! self.selected[i]) {
                    continue;
                }

                ids.push(i);
                selected.push(self.selected[i])
            }

            return [selected, ids];
        },

        render(this: any, selected: SelectTableValue[]): void {
            let self = this,
                options = self.options as SelectTableOptions,
                box = $(options.container!),
                placeholder = box.find('.default-text'),
                option = box.find('.option');

            if (! selected || ! selected.length) {
                placeholder.removeClass('d-none');
                option.addClass('d-none');

                if (options.multiple) {
                    box.addClass('form-control');
                }

                return;
            }

            placeholder.addClass('d-none');
            option.removeClass('d-none');

            if (! options.multiple) {
                return renderDefault(selected, self, options);
            }

            return renderMultiple(selected, self, options);
        },

        setKeys(this: any, keys: string[]): void {
            // 手动触发change事件，方便监听值变化
            this.$input.val(keys.length ? keys.join(',') : '').trigger('change');
        },

        deleteKey(this: any, key: string): void {
            let val = this.getKeys() as string[],
                results: string[] = [];

            for (let i in val) {
                if (val[i] != key) {
                    results.push(val[i])
                }
            }

            this.setKeys(results)
        },

        getKeys(this: any): string[] {
            let val = this.$input.val() as string;

            if (! val) return [];

            return String(val).split(',');
        },
    };

    // 多选
    function renderMultiple(selected: SelectTableValue[], self: any, options: SelectTableOptions): void {
        let html: string[] = [],
            box = $(options.container!),
            placeholder = box.find('.default-text'),
            option = box.find('.option');

        if (! box.hasClass('select2')) {
            box.addClass('select2 select2-container select2-container--default select2-container--below');
        }
        box.removeClass('form-control');

        for (let i in selected) {
            html.push(`<li class="select2-selection__choice" >
    ${selected[i]['label']} <span data-id="${selected[i]['id']}" class="select2-selection__choice__remove remove " role="presentation"> ×</span>
</li>`);
        }

        html.unshift('<span class="select2-selection__clear remove-all">×</span>');

        let htmlStr = `<span class="select2-selection select2-selection--multiple">
 <ul class="select2-selection__rendered">${html.join('')}</ul>
 </span>`;

        var $tags = $(htmlStr);

        option.html($tags as any);

        $tags.find('.remove').on('click', function () {
            var $this = $(this);

            self.deleteKey($this.data('id'));

            $this.parent().remove();

            if (! self.getKeys().length) {
                removeAll();
            }
        });

        function removeAll(): void {
            option.html('');
            placeholder.removeClass('d-none');
            option.addClass('d-none');

            box.addClass('form-control');

            self.setKeys([]);
        }

        $tags.find('.remove-all').on('click', removeAll);
    }

    // 单选
    function renderDefault(selected: SelectTableValue[], self: any, options: SelectTableOptions): void {
        let box = $(options.container!),
            placeholder = box.find('.default-text'),
            option = box.find('.option');

        var remove = $("<div class='pull-right ' style='font-weight:bold;cursor:pointer'>×</div>");

        option.text(selected[0]['label']);
        option.append(remove);

        remove.on('click', function () {
            self.setKeys([]);
            placeholder.removeClass('d-none');
            option.addClass('d-none');
        });
    }

    (w.AIO.grid as any).SelectTable = function (opts: Partial<SelectTableOptions>) {
        return new (SelectTable as any)(opts)
    };
})(window as any)
