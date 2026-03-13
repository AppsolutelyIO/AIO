<?php

namespace Appsolutely\AIO\Form\Concerns;

use Appsolutely\AIO\Form\Field;

trait HandleCascadeFields
{
    /**
     * @param  array  $dependency
     * @param  \Closure  $closure
     * @return Field\CascadeGroup
     */
    public function cascadeGroup(\Closure $closure, array $dependency)
    {
        $this->pushField($group = new Field\CascadeGroup($dependency));

        $closure($this);

        $this->html($group->end())->plain();

        return $group;
    }
}
