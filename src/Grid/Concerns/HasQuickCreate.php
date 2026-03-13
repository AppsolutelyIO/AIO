<?php

namespace Appsolutely\AIO\Grid\Concerns;

use Closure;
use Appsolutely\AIO\Grid\Tools\QuickCreate;

trait HasQuickCreate
{
    /**
     * @var QuickCreate
     */
    protected $quickCreate;

    /**
     * @param  Closure  $callback
     * @return $this
     */
    public function quickCreate(\Closure $callback)
    {
        $this->quickCreate = new QuickCreate($this);

        $callback($this->quickCreate);

        return $this;
    }

    /**
     * Indicates grid has quick-create.
     *
     * @return bool
     */
    public function hasQuickCreate()
    {
        return $this->quickCreate !== null;
    }

    /**
     * Render quick-create form.
     *
     * @return array|string
     */
    public function renderQuickCreate()
    {
        $columnCount = $this->columns->count();

        return $this->quickCreate->render($columnCount);
    }
}
