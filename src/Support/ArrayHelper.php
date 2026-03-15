<?php

namespace Appsolutely\AIO\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;

class ArrayHelper
{
    /**
     * 把给定的值转化为数组.
     */
    public static function convert($value, bool $filter = true): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        if ($value instanceof \Closure) {
            $value = $value();
        }

        if (! is_array($value)) {
            if ($value instanceof Jsonable) {
                $value = json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                $value = $value->toArray();
            } elseif (is_string($value)) {
                $array = json_decode($value, true);

                $value = is_array($array) ? $array : explode(',', $value);
            } else {
                $value = (array) $value;
            }
        }

        return $filter ? array_filter($value, function ($v) {
            return $v !== '' && $v !== null;
        }) : $value;
    }

    /**
     * 删除数组中的元素.
     */
    public static function deleteByValue(&$array, $value, bool $strict = false): void
    {
        $value = (array) $value;

        foreach ($array as $index => $item) {
            if (in_array($item, $value, $strict)) {
                unset($array[$index]);
            }
        }
    }

    /**
     * 删除数组中包含指定字符串的元素.
     */
    public static function deleteContains(&$array, $value): void
    {
        $value = (array) $value;

        foreach ($array as $index => $item) {
            foreach ($value as $v) {
                if (Str::contains($item, $v)) {
                    unset($array[$index]);
                }
            }
        }
    }

    /**
     * 生成层级数据.
     */
    public static function buildNested(
        $nodes = [],
        $parentId = 0,
        ?string $primaryKeyName = null,
        ?string $parentKeyName = null,
        ?string $childrenKeyName = null
    ): array {
        $branch = [];
        $primaryKeyName = $primaryKeyName ?: 'id';
        $parentKeyName = $parentKeyName ?: 'parent_id';
        $childrenKeyName = $childrenKeyName ?: 'children';

        $parentId = is_numeric($parentId) ? (int) $parentId : $parentId;

        foreach ($nodes as $node) {
            $pk = $node[$parentKeyName];
            $pk = is_numeric($pk) ? (int) $pk : $pk;

            if ($pk === $parentId) {
                $children = static::buildNested(
                    $nodes,
                    $node[$primaryKeyName],
                    $primaryKeyName,
                    $parentKeyName,
                    $childrenKeyName
                );

                if ($node instanceof \Illuminate\Database\Eloquent\Model) {
                    $node->setRelation($childrenKeyName, collect($children));
                } elseif ($children) {
                    $node[$childrenKeyName] = $children;
                }
                $branch[] = $node;
            }
        }

        return $branch;
    }

    /**
     * 导出数组为 PHP 代码字符串.
     */
    public static function export(array &$array, $level = 1): string
    {
        $start = '[';
        $end = ']';

        $txt = "$start\n";

        foreach ($array as $k => &$v) {
            if (is_array($v)) {
                $pre = is_string($k) ? "'$k' => " : "$k => ";

                $txt .= str_repeat(' ', $level * 4).$pre.static::export($v, $level + 1).",\n";

                continue;
            }
            $t = $v;

            if ($v === true) {
                $t = 'true';
            } elseif ($v === false) {
                $t = 'false';
            } elseif ($v === null) {
                $t = 'null';
            } elseif (is_string($v)) {
                $v = str_replace("'", "\\'", $v);
                $t = "'$v'";
            }

            $pre = is_string($k) ? "'$k' => " : "$k => ";

            $txt .= str_repeat(' ', $level * 4)."{$pre}{$t},\n";
        }

        return $txt.str_repeat(' ', ($level - 1) * 4).$end;
    }

    /**
     * 导出数组为 PHP 文件内容.
     */
    public static function exportPhp(array $array): string
    {
        return "<?php \nreturn ".static::export($array).";\n";
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * @param  array|\ArrayAccess  $array
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if ($key === null) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (! isset($array[$key]) || (! is_array($array[$key]) && ! $array[$key] instanceof \ArrayAccess)) {
                $array[$key] = [];
            }

            if (is_array($array)) {
                $array = &$array[$key];
            } else {
                if (is_object($array[$key])) {
                    $array[$key] = static::set($array[$key], implode('.', $keys), $value);
                } else {
                    $mid = $array[$key];

                    $array[$key] = static::set($mid, implode('.', $keys), $value);
                }
            }
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * 把下划线风格字段名转化为驼峰风格.
     */
    public static function camel(array &$array): array
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                static::camel($v);
            }

            $array[Str::camel($k)] = $v;
        }

        return $array;
    }

    /**
     * 判断键是否存在.
     *
     * @param  string|int  $key
     * @param  array|object  $arrayOrObject
     */
    public static function keyExists($key, $arrayOrObject): bool
    {
        if (is_object($arrayOrObject)) {
            $arrayOrObject = static::convert($arrayOrObject, false);
        }

        return array_key_exists($key, $arrayOrObject);
    }

    /**
     * 判断两个值是否相等.
     */
    public static function equal($value1, $value2): bool
    {
        if ($value1 === null || $value2 === null) {
            return false;
        }

        if (! is_scalar($value1) || ! is_scalar($value2)) {
            return $value1 === $value2;
        }

        return (string) $value1 === (string) $value2;
    }

    /**
     * 判断给定的数组是否包含给定元素.
     */
    public static function inArray($value, array $array): bool
    {
        $array = array_map(function ($v) {
            if (is_scalar($v) || $v === null) {
                $v = (string) $v;
            }

            return $v;
        }, $array);

        return in_array((string) $value, $array, true);
    }
}
