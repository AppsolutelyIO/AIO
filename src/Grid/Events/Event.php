<?php

namespace Appsolutely\AIO\Grid\Events;

use Appsolutely\AIO\Grid;

abstract class Event
{
    /**
     * @var Grid
     */
    public $grid;

    public $payload = [];

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;
    }
}
