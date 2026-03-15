<?php

namespace Appsolutely\AIO\Grid\Filter\Presenter;

use Appsolutely\AIO\Admin;

class MultipleSelectTable extends SelectTable
{
    public static $css = [
        '@select2',
    ];

    protected $view = 'admin::filter.selecttable';

    /**
     * @var int
     */
    protected $max = 0;

    /**
     * 设置最大选择数量.
     *
     * @return $this
     */
    public function max(int $max)
    {
        $this->max = $max;

        return $this;
    }

    protected function addScript()
    {
        $options = json_encode($this->options);

        Admin::script(
            <<<JS
AIO.init('#{$this->id}', function (self) {
    var dialogId = self.parent().find('{$this->dialog->getElementSelector()}').attr('id');
    AIO.grid.SelectTable({
        dialog: '[data-id="' + dialogId + '"]',
        container: '#{$this->id}',
        input: '#hidden-{$this->id}',
        multiple: true,
        max: {$this->max},
        values: {$options},
    });
})
JS
        );
    }
}
