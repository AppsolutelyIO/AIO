<?php

declare(strict_types=1);

if (! function_exists('admin_button')) {
    function admin_button(?string $text = 'Create', ?string $icon = 'icon-plus', ?string $button = 'primary'): string
    {
        return sprintf(
            '<button class="btn btn-icon btn-%s"><i class="feather %s"></i> %s</button>',
            $button,
            $icon,
            __t($text)
        );
    }
}

if (! function_exists('admin_create_button')) {
    function admin_create_button(): string
    {
        return admin_button();
    }
}

if (! function_exists('placeholder')) {
    function placeholder(string $value = '—', string $class = 'text-muted'): string
    {
        return '<span class="' . $class . '">' . $value . '</span>';
    }
}

if (! function_exists('truncate')) {
    function truncate(?string $value, int $limit = 30, string $end = '...'): string
    {
        if (empty($value)) {
            return placeholder();
        }

        $short = mb_strimwidth($value, 0, $limit, $end);

        if ($short === $value) {
            return htmlspecialchars($value);
        }

        return '<span title="' . htmlspecialchars($value) . '" data-toggle="tooltip">' . $short . '</span>';
    }
}

if (! function_exists('link_tag')) {
    function link_tag(string $url, ?string $text = null, string $target = '_blank'): string
    {
        return '<a href="' . e($url) . '" target="' . e($target) . '">' . ($text ?? e($url)) . '</a>';
    }
}

if (! function_exists('admin_link_action')) {
    function admin_link_action(string $text, string $link, ?string $target = '_self', ?string $icon = 'icon-plus', ?string $color = 'primary'): string
    {
        $html = admin_row_action(__t($text), $icon, $color);

        return sprintf('<a href="%s" target="%s">%s</a>', $link, $target, $html);
    }
}

if (! function_exists('admin_row_action')) {
    function admin_row_action(?string $text = 'Create', ?string $icon = '', ?string $color = ''): string
    {
        return sprintf(
            '<i class="feather %s"></i><span class="%s"> %s</span>',
            $icon . ' ' . $color,
            $color,
            __t($text)
        );
    }
}

if (! function_exists('admin_edit_action')) {
    function admin_edit_action(?string $icon = 'icon-edit-1', ?string $color = 'text-custom'): string
    {
        return admin_row_action(__t('Edit'), $icon, $color);
    }
}

if (! function_exists('admin_delete_action')) {
    function admin_delete_action(?string $icon = 'icon-alert-triangle', ?string $color = 'text-danger'): string
    {
        return admin_row_action(__t('Delete'), $icon, $color);
    }
}
