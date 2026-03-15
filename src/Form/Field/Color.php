<?php

namespace Appsolutely\AIO\Form\Field;

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

class Color extends Text
{
    protected $view = 'admin::form.color';

    /**
     * Use `hex` format.
     *
     * @return $this
     */
    public function hex()
    {
        return $this->mergeOptions(['format' => 'hex']);
    }

    /**
     * Use `rgb` format.
     *
     * @return $this
     */
    public function rgb()
    {
        return $this->mergeOptions(['format' => 'rgb']);
    }

    /**
     * Use `rgba` format.
     *
     * @return $this
     */
    public function rgba()
    {
        return $this->mergeOptions(['format' => 'rgba']);
    }

    /**
     * Render this filed.
     *
     * @return Factory|View
     */
    public function render()
    {
        $this->defaultAttribute('style', 'width: 160px;flex:none');

        return parent::render();
    }
}
