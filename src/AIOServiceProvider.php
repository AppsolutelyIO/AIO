<?php

declare(strict_types=1);

namespace Appsolutely\AIO;

use Appsolutely\AIO\Events\ArticleCreated;
use Appsolutely\AIO\Events\ArticleDeleted;
use Appsolutely\AIO\Events\ArticleUpdated;
use Appsolutely\AIO\Events\FormSubmitted;
use Appsolutely\AIO\Events\PageCreated;
use Appsolutely\AIO\Events\PageDeleted;
use Appsolutely\AIO\Events\PageUpdated;
use Appsolutely\AIO\Events\ProductCreated;
use Appsolutely\AIO\Events\ProductDeleted;
use Appsolutely\AIO\Events\ProductUpdated;
use Appsolutely\AIO\Jobs\ProcessMissingTranslations;
use Appsolutely\AIO\Listeners\ClearPageSlugAliasCache;
use Appsolutely\AIO\Listeners\ClearSitemapCache;
use Appsolutely\AIO\Listeners\TriggerFormNotifications;
use Appsolutely\AIO\Livewire\Anchor;
use Appsolutely\AIO\Livewire\ArticleList;
use Appsolutely\AIO\Livewire\DynamicForm;
use Appsolutely\AIO\Livewire\Footer;
use Appsolutely\AIO\Livewire\GeneralBlock;
use Appsolutely\AIO\Livewire\Header;
use Appsolutely\AIO\Livewire\TransitionSection;
use Appsolutely\AIO\Observers\ArticleObserver;
use Appsolutely\AIO\Observers\OrderObserver;
use Appsolutely\AIO\Observers\PageBlockSettingObserver;
use Appsolutely\AIO\Observers\PageBlockValueObserver;
use Appsolutely\AIO\Observers\PageObserver;
use Appsolutely\AIO\Observers\ProductObserver;
use Appsolutely\AIO\Repositories\TranslationRepository;
use Appsolutely\AIO\Services\PageBlockService;
use Appsolutely\AIO\Services\Translation\DeepSeekTranslator;
use Appsolutely\AIO\Services\Translation\OpenAITranslator;
use Appsolutely\AIO\Services\Translation\TranslatorInterface;
use Appsolutely\AIO\Services\TranslationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AIOServiceProvider extends ServiceProvider
{
    /**
     * Console commands provided by the package.
     *
     * @var array<int, class-string>
     */
    protected array $commands = [
        Console\CleanupOrphanFilesCommand::class,
        Console\ClearManifestCacheCommand::class,
        Console\ExportFormEntriesCommand::class,
        Console\GenerateConfigClassesCommand::class,
        Console\GenerateSitemapCommand::class,
        Console\MigrateBlockValueDisplayOptionToColumnCommand::class,
        Console\NotificationQueueStatusCommand::class,
        Console\ProcessAndSendNotificationsCommand::class,
        Console\ProcessNotificationQueueCommand::class,
        Console\ResyncFormEntryNotificationsCommand::class,
        Console\TestNotificationEmailCommand::class,
        Console\TranslateCommand::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aio.php', 'aio');

        $this->registerServices();
        $this->registerRouteMacros();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'aio');

        $this->registerRoutes();
        $this->registerObservers();
        $this->registerEventListeners();
        $this->registerBladeDirectives();
        $this->registerLivewireComponents();
        $this->registerPageBuilderViewNamespace();
        $this->configureRateLimiting();
        $this->registerCommands();
        $this->registerSchedule();
        $this->registerPublishing();
    }

    /**
     * Register service interface bindings from config.
     */
    protected function registerServices(): void
    {
        $this->app->singleton(TranslationRepository::class);
        $this->app->singleton(TranslationService::class);

        $this->app->bind(TranslatorInterface::class, function ($app) {
            $provider = $app['config']->get('services.translation.provider', 'deepseek');

            return match ($provider) {
                'openai' => new OpenAITranslator(),
                default  => new DeepSeekTranslator(),
            };
        });

        foreach ((array) config('aio.services') as $interface => $implementation) {
            $this->app->singleton($interface, $implementation);
        }
    }

    /**
     * Register AIO routes based on config toggles.
     */
    protected function registerRoutes(): void
    {
        if (config('aio.routes.web', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        }

        if (config('aio.routes.api', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }

        if (config('aio.routes.cache', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/cache/test.php');
        }
    }

    /**
     * Register model observers using config-resolved model classes.
     */
    protected function registerObservers(): void
    {
        $modelMap = [
            'order'              => OrderObserver::class,
            'page'               => PageObserver::class,
            'page_block_setting' => PageBlockSettingObserver::class,
            'page_block_value'   => PageBlockValueObserver::class,
            'product'            => ProductObserver::class,
            'article'            => ArticleObserver::class,
        ];

        foreach ($modelMap as $modelKey => $observerClass) {
            $modelClass = config("aio.models.{$modelKey}");

            if ($modelClass && class_exists($modelClass) && class_exists($observerClass)) {
                $modelClass::observe($observerClass);
            }
        }
    }

    /**
     * Register Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('t', function ($expression) {
            return "<?php echo __t($expression); ?>";
        });

        Blade::directive('tv', function ($expression) {
            return "<?php echo __tv($expression); ?>";
        });

        Blade::directive('renderBlock', function ($expression) {
            return "<?php echo app('" . PageBlockService::class . "')->renderBlockSafely($expression); ?>";
        });

        Blade::directive('title', function ($expression) {
            return "<?php echo page_meta($expression, 'meta_title'); ?>";
        });

        Blade::directive('keywords', function ($expression) {
            return "<?php echo page_meta($expression, 'keywords'); ?>";
        });

        Blade::directive('description', function ($expression) {
            return "<?php echo page_meta($expression, 'description'); ?>";
        });
    }

    /**
     * Register Livewire components with aliases.
     */
    protected function registerLivewireComponents(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        Livewire::component('aio-anchor', Anchor::class);
        Livewire::component('aio-article-list', ArticleList::class);
        Livewire::component('aio-dynamic-form', DynamicForm::class);
        Livewire::component('aio-footer', Footer::class);
        Livewire::component('aio-general-block', GeneralBlock::class);
        Livewire::component('aio-header', Header::class);
        Livewire::component('aio-transition-section', TransitionSection::class);
    }

    /**
     * Register the page builder view namespace.
     */
    protected function registerPageBuilderViewNamespace(): void
    {
        View::addNamespace('page-builder', __DIR__ . '/../resources/page-builder');
    }

    /**
     * Configure rate limiters.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('api:authenticated', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('form-submission', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('admin-api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(100)->by($request->user()->id)
                : Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = $request->input('email');
            $key   = $email ? strtolower($email) : $request->ip();

            return Limit::perHour(3)->by($key);
        });

        RateLimiter::for('email-verification', function (Request $request) {
            return $request->user()
                ? Limit::perHour(5)->by($request->user()->id)
                : Limit::perHour(3)->by($request->ip());
        });

        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });
    }

    /**
     * Register the localized Route macro.
     */
    protected function registerRouteMacros(): void
    {
        Route::macro('localized', function (\Closure $callback) {
            if (config('app.localization', false)) {
                Route::prefix(\LaravelLocalization::setLocale())
                    ->middleware(['localeCookieRedirect', 'localizationRedirect', 'localeViewPath'])
                    ->group($callback);
            } else {
                Route::group([], $callback);
            }
        });
    }

    /**
     * Register event listeners for AIO domain events.
     */
    protected function registerEventListeners(): void
    {
        // Page events
        Event::listen(PageCreated::class, ClearPageSlugAliasCache::class);
        Event::listen(PageCreated::class, ClearSitemapCache::class);
        Event::listen(PageUpdated::class, ClearPageSlugAliasCache::class);
        Event::listen(PageUpdated::class, ClearSitemapCache::class);
        Event::listen(PageDeleted::class, ClearPageSlugAliasCache::class);
        Event::listen(PageDeleted::class, ClearSitemapCache::class);

        // Product events
        Event::listen(ProductCreated::class, ClearSitemapCache::class);
        Event::listen(ProductUpdated::class, ClearSitemapCache::class);
        Event::listen(ProductDeleted::class, ClearSitemapCache::class);

        // Article events
        Event::listen(ArticleCreated::class, ClearSitemapCache::class);
        Event::listen(ArticleUpdated::class, ClearSitemapCache::class);
        Event::listen(ArticleDeleted::class, ClearSitemapCache::class);

        // Form events
        Event::listen(FormSubmitted::class, TriggerFormNotifications::class);
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    /**
     * Register scheduled tasks.
     */
    protected function registerSchedule(): void
    {
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            // Run the translation job every minute
            $schedule->job(new ProcessMissingTranslations(null, 10))
                ->everyMinute()
                ->withoutOverlapping()
                ->onFailure(function () {
                    Log::error('Translation job failed to schedule');
                });

            // Regenerate sitemap every hour
            $schedule->command('sitemap:generate --force')
                ->hourly()
                ->withoutOverlapping()
                ->onFailure(function () {
                    Log::error('Sitemap generation failed');
                });

            // Process notification queue every minute
            $schedule->command('notifications:process-queue --once')
                ->everyMinute()
                ->withoutOverlapping()
                ->onFailure(function () {
                    Log::error('Notification queue processing failed');
                });
        });
    }

    /**
     * Register the package's publishable resources.
     */
    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            [__DIR__ . '/../config/aio.php' => config_path('aio.php')],
            'aio-config'
        );

        $this->publishes(
            [__DIR__ . '/../resources/views' => resource_path('views/vendor/aio')],
            'aio-views'
        );

        $this->publishes(
            [__DIR__ . '/../database/migrations' => database_path('migrations')],
            'aio-migrations'
        );

        $this->publishes(
            [__DIR__ . '/../database/seeders' => database_path('seeders/aio')],
            'aio-seeders'
        );

        $this->publishes(
            [__DIR__ . '/../themes/tabler' => base_path('themes/tabler')],
            'aio-theme'
        );
    }
}
