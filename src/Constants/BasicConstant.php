<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Constants;

class BasicConstant
{
    const ON = 1;

    const OFF = 0;

    const DEFAULT_STOCK = 999;

    const DEFAULT_SORT = 99;

    const TREE_ROOT = ['Root'];

    const SWITCHER = [
        1 => 'On',
        0 => 'Off',
    ];

    const PAGE_SIZE = 2;

    const CONTENT_SUMMARY_LENGTH = 200;

    const PAGE_GRAPESJS_KEY = 'pages.0.frames.0.component.components';
}
