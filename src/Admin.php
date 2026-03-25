<?php

namespace Appsolutely\AIO;

use Appsolutely\AIO\Admin\Controllers\Api\AttributeGroupController as AttributeGroupApiController;
use Appsolutely\AIO\Admin\Controllers\Api\DynamicFormApiController;
use Appsolutely\AIO\Admin\Controllers\Api\FileController as FileApiController;
use Appsolutely\AIO\Admin\Controllers\Api\MenuApiController;
use Appsolutely\AIO\Admin\Controllers\Api\NotificationApiController;
use Appsolutely\AIO\Admin\Controllers\Api\PageBuilderAdminApiController;
use Appsolutely\AIO\Admin\Controllers\ArticleCategoryController;
use Appsolutely\AIO\Admin\Controllers\ArticleController;
use Appsolutely\AIO\Admin\Controllers\CouponController;
use Appsolutely\AIO\Admin\Controllers\DynamicFormController;
use Appsolutely\AIO\Admin\Controllers\FileController;
use Appsolutely\AIO\Admin\Controllers\HomeController;
use Appsolutely\AIO\Admin\Controllers\MenuController as CmsMenuController;
use Appsolutely\AIO\Admin\Controllers\NotificationController;
use Appsolutely\AIO\Admin\Controllers\OrderController;
use Appsolutely\AIO\Admin\Controllers\OrderShipmentController;
use Appsolutely\AIO\Admin\Controllers\PageBlockController;
use Appsolutely\AIO\Admin\Controllers\PageController;
use Appsolutely\AIO\Admin\Controllers\ProductAttributeController;
use Appsolutely\AIO\Admin\Controllers\ProductCategoryController;
use Appsolutely\AIO\Admin\Controllers\ProductController;
use Appsolutely\AIO\Admin\Controllers\ProductImageController;
use Appsolutely\AIO\Admin\Controllers\ProductReviewController;
use Appsolutely\AIO\Admin\Controllers\ProductSkuController;
use Appsolutely\AIO\Admin\Controllers\RefundController;
use Appsolutely\AIO\Admin\Controllers\ReleaseController;
use Appsolutely\AIO\Contracts\ExceptionHandler;
use Appsolutely\AIO\Contracts\Repository;
use Appsolutely\AIO\Exception\InvalidArgumentException;
use Appsolutely\AIO\Extend\Manager;
use Appsolutely\AIO\Extend\ServiceProvider;
use Appsolutely\AIO\Http\Controllers\AuthController;
use Appsolutely\AIO\Http\JsonResponse;
use Appsolutely\AIO\Layout\Menu;
use Appsolutely\AIO\Layout\Navbar;
use Appsolutely\AIO\Layout\SectionManager;
use Appsolutely\AIO\Repositories\EloquentRepository;
use Appsolutely\AIO\Support\Composer;
use Appsolutely\AIO\Support\Context;
use Appsolutely\AIO\Support\HtmlHelper;
use Appsolutely\AIO\Support\Setting;
use Appsolutely\AIO\Support\Translator;
use Appsolutely\AIO\Traits\HasAssets;
use Appsolutely\AIO\Traits\HasHtml;
use Appsolutely\AIO\Traits\HasPermissions;
use Closure;
use Composer\Autoload\ClassLoader;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    use HasAssets;
    use HasHtml;

    const VERSION = '1.0.8';

    const WEBSITE_URL = 'https://appsolutely.io/';

    const GITHUB_URL = 'https://github.com/AppsolutelyIO/AIO';

    const SECTION = [
        // 往 <head> 标签内输入内容
        'HEAD' => 'ADMIN_HEAD',

        // 往body标签内部输入内容
        'BODY_INNER_BEFORE' => 'ADMIN_BODY_INNER_BEFORE',
        'BODY_INNER_AFTER'  => 'ADMIN_BODY_INNER_AFTER',

        // 往#app内部输入内容
        'APP_INNER_BEFORE' => 'ADMIN_APP_INNER_BEFORE',
        'APP_INNER_AFTER'  => 'ADMIN_APP_INNER_AFTER',

        // 顶部导航栏用户面板
        'NAVBAR_USER_PANEL'       => 'ADMIN_NAVBAR_USER_PANEL',
        'NAVBAR_AFTER_USER_PANEL' => 'ADMIN_NAVBAR_AFTER_USER_PANEL',
        // 顶部导航栏之前
        'NAVBAR_BEFORE' => 'ADMIN_NAVBAR_BEFORE',
        // 顶部导航栏底下
        'NAVBAR_AFTER' => 'ADMIN_NAVBAR_AFTER',

        // 侧边栏顶部用户信息面板
        'LEFT_SIDEBAR_USER_PANEL' => 'ADMIN_LEFT_SIDEBAR_USER_PANEL',
        // 菜单栏
        'LEFT_SIDEBAR_MENU' => 'ADMIN_LEFT_SIDEBAR_MENU',
        // 菜单栏顶部
        'LEFT_SIDEBAR_MENU_TOP' => 'ADMIN_LEFT_SIDEBAR_MENU_TOP',
        // 菜单栏底部
        'LEFT_SIDEBAR_MENU_BOTTOM' => 'ADMIN_LEFT_SIDEBAR_MENU_BOTTOM',
    ];

    private static $defaultPjaxContainerId = 'pjax-container';

    /**
     * 版本.
     *
     * @return string
     */
    public static function longVersion()
    {
        return sprintf('Appsolutely AIO <comment>version</comment> <info>%s</info>', static::VERSION);
    }

    /**
     * @return Color
     */
    public static function color()
    {
        return app('admin.color');
    }

    /**
     * 菜单管理.
     *
     * @return Menu
     */
    public static function menu(?Closure $builder = null)
    {
        $menu = app('admin.menu');

        if ($builder) {
            $builder($menu);
        }

        return $menu;
    }

    /**
     * 设置 title.
     *
     * @return string|void
     */
    public static function title($title = null)
    {
        if ($title === null) {
            return static::context()->metaTitle ?: config('admin.title');
        }

        static::context()->metaTitle = $title;
    }

    /**
     * @param  null|string  $favicon
     * @return string|void
     */
    public static function favicon($favicon = null)
    {
        if ($favicon === null) {
            return static::context()->favicon ?: config('admin.favicon');
        }

        static::context()->favicon = $favicon;
    }

    /**
     * 设置翻译文件路径.
     */
    public static function translation(?string $path)
    {
        static::context()->translation = $path;
    }

    /**
     * 获取登录用户模型.
     *
     * @return Model|Authenticatable|HasPermissions
     */
    public static function user()
    {
        return static::guard()->user();
    }

    /**
     * @return Guard|StatefulGuard|GuardHelpers
     */
    public static function guard()
    {
        return Auth::guard(config('admin.auth.guard') ?: 'admin');
    }

    /**
     * @return Navbar
     */
    public static function navbar(?Closure $builder = null)
    {
        $navbar = app('admin.navbar');

        if ($builder) {
            $builder($navbar);
        }

        return $navbar;
    }

    /**
     * 启用或禁用Pjax.
     *
     * @return void
     */
    public static function pjax(bool $value = true)
    {
        static::context()->pjaxContainerId = $value ? static::$defaultPjaxContainerId : false;
    }

    /**
     * 禁用pjax.
     *
     * @return void
     */
    public static function disablePjax()
    {
        static::pjax(false);
    }

    /**
     * 获取pjax ID.
     *
     * @return string|void
     */
    public static function getPjaxContainerId()
    {
        $id = static::context()->pjaxContainerId;

        if ($id === false) {
            return;
        }

        return $id ?: static::$defaultPjaxContainerId;
    }

    /**
     * section.
     *
     * @return SectionManager
     */
    public static function section(?Closure $builder = null)
    {
        $manager = app('admin.sections');

        if ($builder) {
            $builder($manager);
        }

        return $manager;
    }

    /**
     * 配置.
     *
     * @return Setting
     */
    public static function setting()
    {
        return app('admin.setting');
    }

    /**
     * 创建数据仓库实例.
     *
     * @param  string|Repository|Model|Builder  $value
     * @return Repository
     */
    public static function repository($repository, array $args = [])
    {
        if (is_string($repository)) {
            $repository = new $repository($args);
        }

        if ($repository instanceof Model || $repository instanceof Builder) {
            $repository = EloquentRepository::make($repository);
        }

        if (! $repository instanceof Repository) {
            $class = is_object($repository) ? get_class($repository) : $repository;

            throw new InvalidArgumentException("The class [{$class}] must be a type of [" . Repository::class . '].');
        }

        return $repository;
    }

    /**
     * 应用管理.
     *
     * @return Application
     */
    public static function app()
    {
        return app('admin.app');
    }

    /**
     * 处理异常.
     *
     * @return mixed
     */
    public static function handleException(\Throwable $e)
    {
        return app(ExceptionHandler::class)->handle($e);
    }

    /**
     * 上报异常.
     *
     * @return mixed
     */
    public static function reportException(\Throwable $e)
    {
        return app(ExceptionHandler::class)->report($e);
    }

    /**
     * 显示异常信息.
     *
     * @return mixed
     */
    public static function renderException(\Throwable $e)
    {
        return app(ExceptionHandler::class)->render($e);
    }

    /**
     * @param  callable  $callback
     */
    public static function booting($callback)
    {
        Event::listen('admin:booting', $callback);
    }

    /**
     * @param  callable  $callback
     */
    public static function booted($callback)
    {
        Event::listen('admin:booted', $callback);
    }

    /**
     * @return void
     */
    public static function callBooting()
    {
        Event::dispatch('admin:booting');
    }

    /**
     * @return void
     */
    public static function callBooted()
    {
        Event::dispatch('admin:booted');
    }

    /**
     * 上下文管理.
     *
     * @return Context
     */
    public static function context()
    {
        return app('admin.context');
    }

    /**
     * 翻译器.
     *
     * @return Translator
     */
    public static function translator()
    {
        return app('admin.translator');
    }

    /**
     * @param  array|string  $name
     * @return void
     */
    public static function addIgnoreQueryName($name)
    {
        $context = static::context();

        $ignoreQueries = $context->ignoreQueries ?? [];

        $context->ignoreQueries = array_merge($ignoreQueries, (array) $name);
    }

    /**
     * @return array
     */
    public static function getIgnoreQueryNames()
    {
        return static::context()->ignoreQueries ?? [];
    }

    /**
     * 中断默认的渲染逻辑.
     *
     * @param  string|Renderable|Closure  $value
     */
    public static function prevent($value)
    {
        if ($value !== null) {
            static::context()->add('contents', $value);
        }
    }

    /**
     * @return bool
     */
    public static function shouldPrevent()
    {
        return count(static::context()->getArray('contents')) > 0;
    }

    /**
     * 渲染内容.
     *
     * @return string|void
     */
    public static function renderContents()
    {
        if (! static::shouldPrevent()) {
            return;
        }

        $results = '';

        foreach (static::context()->getArray('contents') as $content) {
            $results .= HtmlHelper::render($content);
        }

        // 等待JS脚本加载完成
        static::script('AIO.wait()', true);

        $asset = static::asset();

        static::baseCss([], false);
        static::baseJs([], false);
        static::headerJs([], false);
        static::fonts([]);

        return $results
            . static::html()
            . $asset->jsToHtml()
            . $asset->cssToHtml()
            . $asset->scriptToHtml()
            . $asset->styleToHtml();
    }

    /**
     * 响应json数据.
     *
     * @return JsonResponse
     */
    public static function json(array $data = [])
    {
        return JsonResponse::make($data);
    }

    /**
     * 插件管理.
     *
     * @return Manager|ServiceProvider|null
     */
    public static function extension(?string $name = null)
    {
        if ($name) {
            return app('admin.extend')->get($name);
        }

        return app('admin.extend');
    }

    /**
     * 响应并中断后续逻辑.
     *
     * @param  Response|string|array  $response
     *
     * @throws HttpResponseException
     */
    public static function exit($response = '')
    {
        if (is_array($response)) {
            $response = response()->json($response);
        } elseif ($response instanceof JsonResponse) {
            $response = $response->send();
        }

        throw new HttpResponseException($response instanceof Response ? $response : response($response));
    }

    /**
     * 类自动加载器.
     *
     * @return ClassLoader
     */
    public static function classLoader()
    {
        return Composer::loader();
    }

    /**
     * 往分组插入中间件.
     */
    public static function mixMiddlewareGroup(array $mix = [])
    {
        $router = app('router');

        $group = $router->getMiddlewareGroups()['admin'] ?? [];

        if ($mix) {
            $finalGroup = [];

            foreach ($group as $i => $mid) {
                $next = $i + 1;

                $finalGroup[] = $mid;

                if (! isset($group[$next]) || $group[$next] !== 'admin.permission') {
                    continue;
                }

                $finalGroup = array_merge($finalGroup, $mix);

                $mix = [];
            }

            if ($mix) {
                $finalGroup = array_merge($finalGroup, $mix);
            }

            $group = $finalGroup;
        }

        $router->middlewareGroup('admin', $group);
    }

    /**
     * 获取js配置.
     *
     * @return string
     */
    public static function jsVariables(?array $variables = null)
    {
        $jsVariables = static::context()->jsVariables ?: [];

        if ($variables !== null) {
            static::context()->jsVariables = array_merge(
                $jsVariables,
                $variables
            );

            return;
        }

        $sidebarStyle = config('admin.layout.sidebar_style') ?: 'light';

        $pjaxId = static::getPjaxContainerId();

        $jsVariables['pjax_container_selector'] = $pjaxId ? ('#' . $pjaxId) : '';
        $jsVariables['token']                   = csrf_token();
        $jsVariables['lang']                    = ($lang = __('admin.client')) ? array_merge($lang, $jsVariables['lang'] ?? []) : [];
        $jsVariables['colors']                  = static::color()->all();
        $jsVariables['dark_mode']               = static::isDarkMode();
        $jsVariables['sidebar_dark']            = config('admin.layout.sidebar_dark') || ($sidebarStyle === 'dark');
        $jsVariables['sidebar_light_style']     = in_array($sidebarStyle, ['dark', 'light'], true) ? 'sidebar-light-primary' : 'sidebar-primary';

        return admin_javascript_json($jsVariables);
    }

    /**
     * @return bool
     */
    public static function isDarkMode()
    {
        $bodyClass = config('admin.layout.body_class');

        return in_array(
            'dark-mode',
            is_array($bodyClass) ? $bodyClass : explode(' ', $bodyClass),
            true
        );
    }

    /**
     * 注册路由.
     *
     * @return void
     */
    public static function routes()
    {
        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];

        if (config('admin.auth.enable', true)) {
            app('router')->group($attributes, function ($router) {
                /* @var \Illuminate\Routing\Router $router */
                $router->namespace('Appsolutely\AIO\Http\Controllers')->group(function ($router) {
                    /* @var \Illuminate\Routing\Router $router */
                    $router->resource('auth/users', 'UserController');
                    $router->resource('auth/menu', 'MenuController', ['except' => ['create', 'show']]);

                    if (config('admin.permission.enable')) {
                        $router->resource('auth/roles', 'RoleController');
                        $router->resource('auth/permissions', 'PermissionController');
                    }

                    $router->get('site-settings', 'SiteSettingController@index');
                    $router->post('site-settings', 'SiteSettingController@store');
                });

                $router->resource('auth/extensions', 'Appsolutely\AIO\Http\Controllers\ExtensionController', ['only' => ['index', 'store', 'update']]);

                $authController = config('admin.auth.controller', AuthController::class);

                $router->get('auth/login', $authController . '@getLogin');
                $router->post('auth/login', $authController . '@postLogin');
                $router->get('auth/logout', $authController . '@getLogout');
                $router->get('auth/setting', $authController . '@getSetting');
                $router->put('auth/setting', $authController . '@putSetting');
            });
        }

        static::registerCmsRoutes();
        static::registerHelperRoutes();
    }

    /**
     * Register CMS business routes.
     *
     * @return void
     */
    public static function registerCmsRoutes()
    {
        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];

        app('router')->group($attributes, function ($router) {
            $router->get('', [HomeController::class, 'index']);

            $router->resource('file-manager', FileController::class)->names('file-manager');
            $router->get('uploads/{path?}', [FileController::class, 'retrieve'])->where('path', '(.*)')->name('file.self');

            // Content Management Routes
            $router->prefix('articles')->name('articles.')->group(function () use ($router) {
                $router->resource('entry', ArticleController::class);
                $router->resource('categories', ArticleCategoryController::class)->names('categories');
            });

            $router->prefix('pages')->name('pages.')->group(function () use ($router) {
                $router->resource('entry', PageController::class);
                $router->get('{reference}/design', [PageController::class, 'design'])->name('design');
                $router->resource('blocks', PageBlockController::class)->names('blocks');
            });

            // Menu Management Routes
            $router->prefix('menus')->name('menus.')->group(function () use ($router) {
                $router->resource('entry', CmsMenuController::class)->names('entry');
            });

            // Product Management Routes
            $router->prefix('products')->name('products.')->group(function () use ($router) {
                $router->resource('entry', ProductController::class);
                $router->resource('categories', ProductCategoryController::class)->names('categories');
                $router->resource('skus', ProductSkuController::class)->names('skus');
                $router->resource('attributes', ProductAttributeController::class)->names('attributes');
                $router->resource('images', ProductImageController::class)->names('images');
                $router->resource('reviews', ProductReviewController::class)->names('reviews');
            });

            // Order Management Routes
            $router->prefix('orders')->name('orders.')->group(function () use ($router) {
                $router->resource('entry', OrderController::class);
                $router->resource('shipments', OrderShipmentController::class)->names('shipments');
                $router->resource('refunds', RefundController::class)->names('refunds');
            });

            // Coupon Management Routes
            $router->prefix('coupons')->name('coupons.')->group(function () use ($router) {
                $router->resource('entry', CouponController::class);
            });

            // Application releases
            $router->prefix('releases')->name('releases.')->group(function () use ($router) {
                $router->resource('', ReleaseController::class)->names('entry');
            });

            // Dynamic Forms Management
            $router->prefix('forms')->name('forms.')->group(function () use ($router) {
                $router->resource('', DynamicFormController::class)->only(['index'])->names('entry');
            });

            // Notifications Management
            $router->prefix('notifications')->name('notifications.')->group(function () use ($router) {
                $router->resource('', NotificationController::class)->only(['index'])->names('entry');
            });

            // CMS API Routes
            $router->prefix('api/')->name('api.')->middleware('throttle:admin-api')->group(function () use ($router) {
                $router->match(['get', 'post'], 'files', [FileApiController::class, 'upload'])->name('files.upload')->middleware('admin.upload');
                $router->get('file-library', [FileApiController::class, 'library'])->name('file-library');
                $router->get('products/attribute-groups', [AttributeGroupApiController::class, 'query'])->name('attribute-groups');

                // Page Builder Routes
                $router->prefix('pages')->name('pages.')->group(function () use ($router) {
                    $router->get('{reference}/data', [PageBuilderAdminApiController::class, 'getPageData'])->name('data');
                    $router->put('{reference}/save', [PageBuilderAdminApiController::class, 'savePageData'])->name('save');
                    $router->put('{reference}/reset', [PageBuilderAdminApiController::class, 'resetPageData'])->name('reset');
                    $router->get('block-registry', [PageBuilderAdminApiController::class, 'getBlockRegistry'])->name('block-registry');
                    $router->get('block/schema-fields', [PageBuilderAdminApiController::class, 'getSchemaFields'])->name('block.schema-fields');
                    $router->get('block-option', [PageBuilderAdminApiController::class, 'getBlockOption'])->name('block-option');
                    $router->patch('block-option', [PageBuilderAdminApiController::class, 'updateBlockOption'])->name('block-option.update');
                    $router->get('block-html', [PageBuilderAdminApiController::class, 'getBlockHtml'])->name('block-html');
                    $router->post('render-block', [PageBuilderAdminApiController::class, 'renderBlockWithOptions'])->name('render-block');
                    $router->get('{reference}/theme-sync', [PageBuilderAdminApiController::class, 'getThemeSyncOptions'])->name('theme-sync');
                    $router->post('{reference}/theme-sync', [PageBuilderAdminApiController::class, 'syncThemeBlocks'])->name('theme-sync.execute');
                });

                // Menu API Routes
                $router->prefix('menus')->name('menus.')->group(function () use ($router) {
                    $router->get('active-by-page-slug', [MenuApiController::class, 'activeMenusByPageSlug'])->name('active-by-page-slug');
                });

                // Dynamic Forms API Routes
                $router->prefix('forms')->name('forms.')->group(function () use ($router) {
                    $router->post('entries/{id}/mark-spam', [DynamicFormApiController::class, 'markAsSpam'])->name('entries.mark-spam');
                    $router->post('entries/{id}/mark-not-spam', [DynamicFormApiController::class, 'markAsNotSpam'])->name('entries.mark-not-spam');
                    $router->get('entries/export', [DynamicFormApiController::class, 'exportCsv'])->name('entries.export');
                    $router->get('{formId}/entries/export', [DynamicFormApiController::class, 'exportCsv'])->name('entries.export-by-form');
                });

                // Notifications API Routes
                $router->prefix('notifications')->name('notifications.')->group(function () use ($router) {
                    $router->post('process-queue', [NotificationApiController::class, 'processQueue'])->name('process-queue');
                    $router->post('retry-failed', [NotificationApiController::class, 'retryFailed'])->name('retry-failed');
                    $router->delete('clean-old', [NotificationApiController::class, 'cleanOld'])->name('clean-old');
                    $router->post('{id}/retry', [NotificationApiController::class, 'retry'])->name('retry');
                    $router->post('{id}/cancel', [NotificationApiController::class, 'cancel'])->name('cancel');
                    $router->post('{id}/duplicate-template', [NotificationApiController::class, 'duplicateTemplate'])->name('duplicate-template');
                    $router->post('test-rule/{id}', [NotificationApiController::class, 'testRule'])->name('test-rule');
                    $router->get('{id}/preview', [NotificationApiController::class, 'preview'])->name('preview');
                });
            });
        });
    }

    /**
     * 注册api路由.
     *
     * @return void
     */
    public static function registerApiRoutes()
    {
        $attributes = [
            'prefix'     => admin_base_path('api'),
            'middleware' => config('admin.route.middleware'),
            'namespace'  => 'Appsolutely\AIO\Http\Controllers',
            'as'         => 'api.',
        ];

        app('router')->group($attributes, function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->post('action', 'HandleActionController@handle')->name('action');
            $router->post('form', 'HandleFormController@handle')->name('form');
            $router->post('value', 'ValueController@handle')->name('value');
            $router->post('inline-update', 'InlineUpdateController@handle')->name('inline-update');
            $router->get('render', 'RenderableController@handle')->name('render');

            // Upload routes — admin.upload middleware handles chunked file merging
            $router->group(['middleware' => 'admin.upload'], function ($router) {
                $router->post('form/upload', 'HandleFormController@uploadFile')->name('form.upload');
                $router->post('form/destroy-file', 'HandleFormController@destroyFile')->name('form.destroy-file');
                $router->post('tinymce/upload', 'TinymceController@upload')->name('tinymce.upload');
                $router->post('editor-md/upload', 'EditorMDController@upload')->name('editor-md.upload');
                $router->post('vditor/upload', 'VditorController@upload')->name('vditor.upload');
            });
        });
    }

    /**
     * 注册开发工具路由.
     *
     * @return void
     */
    public static function registerHelperRoutes()
    {
        if (! config('admin.helpers.enable', true) || ! config('app.debug')) {
            return;
        }

        $attributes = [
            'prefix'     => config('admin.route.prefix'),
            'middleware' => config('admin.route.middleware'),
        ];

        app('router')->group($attributes, function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->get('helpers/scaffold', 'Appsolutely\AIO\Http\Controllers\ScaffoldController@index');
            $router->post('helpers/scaffold', 'Appsolutely\AIO\Http\Controllers\ScaffoldController@store');
            $router->post('helpers/scaffold/table', 'Appsolutely\AIO\Http\Controllers\ScaffoldController@table');
            $router->get('helpers/icons', 'Appsolutely\AIO\Http\Controllers\IconController@index');
        });
    }
}
