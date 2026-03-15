<?php

namespace Appsolutely\AIO\Support;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class Helper
{
    /**
     * @var array
     */
    public static $fileTypes = [
        'image'      => 'png|jpg|jpeg|tmp|gif',
        'word'       => 'doc|docx',
        'excel'      => 'xls|xlsx|csv',
        'powerpoint' => 'ppt|pptx',
        'pdf'        => 'pdf',
        'code'       => 'php|js|java|python|ruby|go|c|cpp|sql|m|h|json|html|aspx',
        'archive'    => 'zip|tar\.gz|rar|rpm',
        'txt'        => 'txt|pac|log|md',
        'audio'      => 'mp3|wav|flac|3pg|aa|aac|ape|au|m4a|mpc|ogg',
        'video'      => 'mkv|rmvb|flv|mp4|avi|wmv|rm|asf|mpeg',
    ];

    protected static $controllerNames = [];

    /**
     * @deprecated Use ArrayHelper::convert() instead.
     */
    public static function array($value, bool $filter = true): array
    {
        return ArrayHelper::convert($value, $filter);
    }

    /**
     * @deprecated Use HtmlHelper::render() instead.
     */
    public static function render($value, $params = [], $newThis = null): string
    {
        return HtmlHelper::render($value, $params, $newThis);
    }

    /**
     * 获取当前控制器名称.
     *
     * @return mixed|string
     */
    public static function getControllerName()
    {
        $router = app('router');

        if (! $router->current()) {
            return 'undefined';
        }

        $actionName = $router->current()->getActionName();

        if (! isset(static::$controllerNames[$actionName])) {
            $controller = class_basename(explode('@', $actionName)[0]);

            static::$controllerNames[$actionName] = str_replace('Controller', '', $controller);
        }

        return static::$controllerNames[$actionName];
    }

    /**
     * @deprecated Use HtmlHelper::buildAttributes() instead.
     */
    public static function buildHtmlAttributes($attributes)
    {
        return HtmlHelper::buildAttributes($attributes);
    }

    /**
     * @deprecated Use UrlHelper::withQuery() instead.
     */
    public static function urlWithQuery(?string $url, array $query = [])
    {
        return UrlHelper::withQuery($url, $query);
    }

    /**
     * @deprecated Use UrlHelper::withoutQuery() instead.
     */
    public static function urlWithoutQuery($url, $keys)
    {
        return UrlHelper::withoutQuery($url, $keys);
    }

    /**
     * @deprecated Use UrlHelper::fullUrlWithoutQuery() instead.
     */
    public static function fullUrlWithoutQuery($keys)
    {
        return UrlHelper::fullUrlWithoutQuery($keys);
    }

    /**
     * @deprecated Use UrlHelper::hasQuery() instead.
     */
    public static function urlHasQuery(string $url, $keys)
    {
        return UrlHelper::hasQuery($url, $keys);
    }

    /**
     * @deprecated Use UrlHelper::matchRequestPath() instead.
     */
    public static function matchRequestPath($path, ?string $current = null)
    {
        return UrlHelper::matchRequestPath($path, $current);
    }

    /**
     * @deprecated Use ArrayHelper::buildNested() instead.
     */
    public static function buildNestedArray(
        $nodes = [],
        $parentId = 0,
        ?string $primaryKeyName = null,
        ?string $parentKeyName = null,
        ?string $childrenKeyName = null
    ) {
        return ArrayHelper::buildNested($nodes, $parentId, $primaryKeyName, $parentKeyName, $childrenKeyName);
    }

    /**
     * @return mixed
     */
    public static function slug(string $name, string $symbol = '-')
    {
        $text = preg_replace_callback('/([A-Z])/', function ($text) use ($symbol) {
            return $symbol . strtolower($text[1]);
        }, $name);

        return str_replace('_', $symbol, ltrim($text, $symbol));
    }

    /**
     * @deprecated Use ArrayHelper::export() instead.
     */
    public static function exportArray(array &$array, $level = 1)
    {
        return ArrayHelper::export($array, $level);
    }

    /**
     * @deprecated Use ArrayHelper::exportPhp() instead.
     */
    public static function exportArrayPhp(array $array)
    {
        return ArrayHelper::exportPhp($array);
    }

    /**
     * @deprecated Use ArrayHelper::deleteByValue() instead.
     */
    public static function deleteByValue(&$array, $value, bool $strict = false)
    {
        ArrayHelper::deleteByValue($array, $value, $strict);
    }

    /**
     * @deprecated Use ArrayHelper::deleteContains() instead.
     */
    public static function deleteContains(&$array, $value)
    {
        ArrayHelper::deleteContains($array, $value);
    }

    /**
     * @deprecated Use ColorHelper::lighten() instead.
     */
    public static function colorLighten(string $color, int $amt)
    {
        return ColorHelper::lighten($color, $amt);
    }

    /**
     * @deprecated Use ColorHelper::darken() instead.
     */
    public static function colorDarken(string $color, int $amt)
    {
        return ColorHelper::darken($color, $amt);
    }

    /**
     * @deprecated Use ColorHelper::alpha() instead.
     */
    public static function colorAlpha(string $color, $alpha)
    {
        return ColorHelper::alpha($color, $alpha);
    }

    /**
     * @deprecated Use ColorHelper::toRGB() instead.
     */
    public static function colorToRBG(string $color, int $amt = 0)
    {
        return ColorHelper::toRGB($color, $amt);
    }

    /**
     * 验证扩展包名称.
     *
     * @param  string  $name
     * @return int
     */
    public static function validateExtensionName($name)
    {
        return preg_match('/^[\w\-_]+\/[\w\-_]+$/', $name);
    }

    /**
     * Get file icon.
     *
     * @param  string  $file
     * @return string
     */
    public static function getFileIcon($file = '')
    {
        $extension = File::extension($file);

        foreach (static::$fileTypes as $type => $regex) {
            if (preg_match("/^($regex)$/i", $extension) !== 0) {
                return "fa fa-file-{$type}-o";
            }
        }

        return 'fa fa-file-o';
    }

    /**
     * 判断是否是ajax请求.
     *
     * @return bool
     */
    public static function isAjaxRequest(?Request $request = null)
    {
        /* @var Request $request */
        $request = $request ?: request();

        return $request->ajax() && ! $request->pjax();
    }

    /**
     * 判断是否是IE浏览器.
     *
     * @return false|int
     */
    public static function isIEBrowser()
    {
        return (bool) preg_match('/Mozilla\/5\.0 \(Windows NT 10\.0; WOW64; Trident\/7\.0; rv:[0-9\.]*\) like Gecko/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    /**
     * 判断是否QQ浏览器.
     *
     * @return bool
     */
    public static function isQQBrowser()
    {
        return mb_strpos(mb_strtolower($_SERVER['HTTP_USER_AGENT'] ?? ''), 'qqbrowser') !== false;
    }

    /**
     * @param  string  $url
     * @return void
     */
    public static function setPreviousUrl($url)
    {
        session()->flash('admin.prev.url', static::urlWithoutQuery((string) $url, '_pjax'));
    }

    /**
     * @return string
     */
    public static function getPreviousUrl()
    {
        return (string) (session()->get('admin.prev.url') ? url(session()->get('admin.prev.url')) : url()->previous());
    }

    /**
     * @param  mixed  $command
     * @param  int  $timeout
     * @param  null  $input
     * @param  null  $cwd
     * @return Process
     */
    public static function process($command, $timeout = 100, $input = null, $cwd = null)
    {
        $parameters = [
            $command,
            $cwd,
            [],
            $input,
            $timeout,
        ];

        return is_string($command)
            ? Process::fromShellCommandline(...$parameters)
            : new Process(...$parameters);
    }

    /**
     * @deprecated Use ArrayHelper::equal() instead.
     */
    public static function equal($value1, $value2)
    {
        return ArrayHelper::equal($value1, $value2);
    }

    /**
     * @deprecated Use ArrayHelper::inArray() instead.
     */
    public static function inArray($value, array $array)
    {
        return ArrayHelper::inArray($value, $array);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param  string  $value
     * @param  int  $limit
     * @param  string  $end
     * @return string
     */
    public static function strLimit($value, $limit = 100, $end = '...')
    {
        if (mb_strlen($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit, 'UTF-8')) . $end;
    }

    /**
     * 获取类名或对象的文件路径.
     *
     * @param  string|object  $class
     * @return string
     *
     * @throws \ReflectionException
     */
    public static function guessClassFileName($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        try {
            if (class_exists($class)) {
                return (new \ReflectionClass($class))->getFileName();
            }
        } catch (\Throwable $e) {
        }

        $class = trim($class, '\\');

        $composer = Composer::parse(base_path('composer.json'));

        $map = collect($composer->autoload['psr-4'] ?? [])->mapWithKeys(function ($path, $namespace) {
            $namespace = trim($namespace, '\\') . '\\';

            return [$namespace => [$namespace, $path]];
        })->sortBy(function ($_, $namespace) {
            return strlen($namespace);
        }, SORT_REGULAR, true);

        $prefix = explode($class, '\\')[0];

        if ($map->isEmpty()) {
            if (Str::startsWith($class, 'App\\')) {
                $values = ['App\\', 'app/'];
            }
        } else {
            $values = $map->filter(function ($_, $k) use ($class) {
                return Str::startsWith($class, $k);
            })->first();
        }

        if (empty($values)) {
            $values = [$prefix . '\\', self::slug($prefix) . '/'];
        }

        [$namespace, $path] = $values;

        return base_path(str_replace([$namespace, '\\'], [$path, '/'], $class)) . '.php';
    }

    /**
     * Is input data is has-one relation.
     */
    public static function prepareHasOneRelation(Collection $fields, array &$input)
    {
        $relations = [];
        $fields->each(function ($field) use (&$relations) {
            $column = $field->column();

            if (is_array($column)) {
                foreach ($column as $v) {
                    if (Str::contains($v, '.')) {
                        $first             = explode('.', $v)[0];
                        $relations[$first] = null;
                    }
                }

                return;
            }

            if (Str::contains($column, '.')) {
                $first             = explode('.', $column)[0];
                $relations[$first] = null;
            }
        });

        foreach ($relations as $first => $v) {
            if (isset($input[$first])) {
                foreach ($input[$first] as $key => $value) {
                    if (is_array($value)) {
                        $input["$first.$key"] = $value;
                    }
                }

                $input = array_merge($input, Arr::dot([$first => $input[$first]]));
            }
        }
    }

    /**
     * 设置查询条件.
     *
     * @param  mixed  $model
     * @return void
     */
    public static function withQueryCondition($model, ?string $column, string $query, array $params)
    {
        if (! Str::contains($column, '.')) {
            $model->$query($column, ...$params);

            return;
        }

        $method   = $query === 'orWhere' ? 'orWhere' : 'where';
        $subQuery = $query === 'orWhere' ? 'where' : $query;

        $model->$method(function ($q) use ($column, $subQuery, $params) {
            static::withRelationQuery($q, $column, $subQuery, $params);
        });
    }

    /**
     * 设置关联关系查询条件.
     *
     * @param  mixed  $model
     * @param  mixed  ...$params
     * @return void
     */
    public static function withRelationQuery($model, ?string $column, string $query, array $params)
    {
        $column = explode('.', $column);

        $relColumn = array_pop($column);

        $model->whereHas(implode('.', $column), function ($relation) use ($relColumn, $params, $query) {
            $table = $relation->getModel()->getTable();
            $relation->$query("{$table}.{$relColumn}", ...$params);
        });
    }

    /**
     * @deprecated Use HtmlHelper::entityEncode() instead.
     */
    public static function htmlEntityEncode($item)
    {
        return HtmlHelper::entityEncode($item);
    }

    /**
     * @deprecated Use HtmlHelper::formatElementName() instead.
     */
    public static function formatElementName($name)
    {
        return HtmlHelper::formatElementName($name);
    }

    /**
     * @deprecated Use ArrayHelper::set() instead.
     */
    public static function arraySet(&$array, $key, $value)
    {
        return ArrayHelper::set($array, $key, $value);
    }

    /**
     * @deprecated Use ArrayHelper::camel() instead.
     */
    public static function camelArray(array &$array)
    {
        return ArrayHelper::camel($array);
    }

    /**
     * 获取文件名称.
     *
     * @param  string  $name
     * @return array|mixed
     */
    public static function basename($name)
    {
        if (! $name) {
            return $name;
        }

        return last(explode('/', $name));
    }

    /**
     * @deprecated Use ArrayHelper::keyExists() instead.
     */
    public static function keyExists($key, $arrayOrObject)
    {
        return ArrayHelper::keyExists($key, $arrayOrObject);
    }

    /**
     * 跳转.
     *
     * @param  string  $to
     * @param  Request  $request
     * @return Application|ResponseFactory|JsonResponse|RedirectResponse|Response|Redirector
     */
    public static function redirect($to, int $statusCode = 302, $request = null)
    {
        $request = $request ?: request();

        if (! URL::isValidUrl($to)) {
            $to = admin_base_path($to);
        }

        if ($request->ajax() && ! $request->pjax()) {
            return response()->json(['redirect' => $to], $statusCode);
        }

        if ($request->pjax()) {
            return response("<script>location.href = '{$to}';</script>");
        }

        $redirectCodes = [201, 301, 302, 303, 307, 308];

        return redirect($to, in_array($statusCode, $redirectCodes, true) ? $statusCode : 302);
    }
}
