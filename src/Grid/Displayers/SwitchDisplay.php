<?php

namespace Appsolutely\AIO\Grid\Displayers;

use Appsolutely\AIO\Admin;

class SwitchDisplay extends AbstractDisplayer
{
    protected $color;

    public function color($color)
    {
        $this->color = Admin::color()->get($color);
    }

    public function display(string $color = '', $refresh = false)
    {
        if ($color instanceof \Closure) {
            $color->call($this->row, $this);
        } else {
            $this->color($color);
        }

        $column = $this->column->getName();
        $value = $this->value instanceof \BackedEnum ? $this->value->value : $this->value;
        $checked = $value ? 'checked' : '';
        $color = $this->color ?: Admin::color()->primary();
        $url = $this->url();
        $inlineUpdateEndpoint = route(admin_api_route_name('inline-update'));
        $model = get_class($this->row);
        $id = $this->getKey();

        return Admin::view(
            'admin::grid.displayer.switch',
            ['column' => $column, 'color' => $color, 'refresh' => $refresh, 'checked' => $checked, 'url' => $url, 'inlineUpdateEndpoint' => $inlineUpdateEndpoint, 'model' => $model, 'id' => $id]
        );
    }

    protected function url()
    {
        return $this->resource().'/'.$this->getKey();
    }
}
