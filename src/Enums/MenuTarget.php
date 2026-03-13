<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum MenuTarget: string
{
    case Self   = '_self';
    case Blank  = '_blank';
    case Parent = '_parent';
    case Top    = '_top';
    case Modal  = 'modal';
    case Iframe = 'iframe';

    public function toArray(): string
    {
        return match ($this) {
            self::Self   => 'Same Tab',
            self::Blank  => 'New Tab',
            self::Parent => 'Parent Frame ',
            self::Top    => 'Top Frame',
            self::Modal  => 'Open in Modal',
            self::Iframe => 'Load in Iframe',
        };
    }
}
