<?php

namespace Appsolutely\AIO\Support;

use Appsolutely\AIO\Grid;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;

class HtmlHelper
{
    /**
     * 把给定的值转化为字符串.
     *
     * @param  string|Grid|\Closure|Renderable|Htmlable  $value
     * @param  array  $params
     * @param  object  $newThis
     */
    public static function render($value, $params = [], $newThis = null): string
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value instanceof \Closure) {
            $newThis && ($value = $value->bindTo($newThis));

            $value = $value(...(array) $params);
        }

        if ($value instanceof Renderable) {
            return (string) $value->render();
        }

        if ($value instanceof Htmlable) {
            return (string) $value->toHtml();
        }

        return (string) $value;
    }

    /**
     * 构建 HTML 属性字符串.
     */
    public static function buildAttributes($attributes): string
    {
        $html = '';

        foreach ((array) $attributes as $key => &$value) {
            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            if (is_numeric($key)) {
                $key = $value;
            }

            $element = '';

            if ($value !== null) {
                $element = $key.'="'.htmlentities($value, ENT_QUOTES, 'UTF-8').'" ';
            }

            $html .= $element;
        }

        return $html;
    }

    /**
     * Html 转义.
     *
     * @param  array|string  $item
     * @return mixed
     */
    public static function entityEncode($item)
    {
        if (is_object($item)) {
            return $item;
        }
        if (is_array($item)) {
            array_walk_recursive($item, function (&$value) {
                $value = htmlentities($value ?? '');
            });
        } else {
            $item = htmlentities($item ?? '');
        }

        return $item;
    }

    /**
     * 格式化表单元素 name 属性.
     *
     * @param  string|array  $name
     * @return mixed|string
     */
    public static function formatElementName($name)
    {
        if (! $name) {
            return $name;
        }

        if (is_array($name)) {
            foreach ($name as &$v) {
                $v = static::formatElementName($v);
            }

            return $name;
        }

        $name = explode('.', $name);

        if (count($name) === 1) {
            return $name[0];
        }

        $html = array_shift($name);
        foreach ($name as $piece) {
            $html .= "[$piece]";
        }

        return $html;
    }
}
