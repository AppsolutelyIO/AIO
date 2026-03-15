<?php

namespace Appsolutely\AIO\Support;

class ColorHelper
{
    /**
     * 颜色转亮.
     */
    public static function lighten(string $color, int $amt): string
    {
        if (! $amt) {
            return $color;
        }

        $hasPrefix = false;

        if (str_starts_with($color, '#')) {
            $color = mb_substr($color, 1);

            $hasPrefix = true;
        }

        [$red, $blue, $green] = static::toRGB($color, $amt);

        return ($hasPrefix ? '#' : '') . dechex($green + ($blue << 8) + ($red << 16));
    }

    /**
     * 颜色转暗.
     */
    public static function darken(string $color, int $amt): string
    {
        return static::lighten($color, -$amt);
    }

    /**
     * 颜色透明度.
     */
    public static function alpha(string $color, $alpha): string
    {
        if ($alpha >= 1) {
            return $color;
        }

        if (str_starts_with($color, '#')) {
            $color = mb_substr($color, 1);
        }

        [$red, $blue, $green] = static::toRGB($color);

        return "rgba($red, $blue, $green, $alpha)";
    }

    /**
     * 十六进制颜色转 RGB.
     */
    public static function toRGB(string $color, int $amt = 0): array
    {
        $format = function ($value) {
            if ($value > 255) {
                return 255;
            }
            if ($value < 0) {
                return 0;
            }

            return $value;
        };

        $num = hexdec($color);

        $red   = $format(($num >> 16) + $amt);
        $blue  = $format((($num >> 8) & 0x00FF) + $amt);
        $green = $format(($num & 0x0000FF) + $amt);

        return [$red, $blue, $green];
    }
}
