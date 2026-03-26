<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Livewire;

use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;
use Illuminate\Contracts\Container\Container;
use Livewire\Component;

class GeneralBlock extends Component
{
    public array $page = [];

    /**
     * The view name to render (without the 'livewire.' prefix).
     */
    public string $viewName = '';

    /**
     * View style variant (e.g. default, fullscreen).
     * Set from page_block_values.view_style via BlockRendererService.
     */
    public string $viewStyle = 'default';

    public array $displayOptions = [];

    public array $queryOptions = [];

    protected array $defaultQueryOptions = [];

    /**
     * Mount the component with data.
     *
     * @param  array  $page  page data
     * @param  array  $data  The data to pass to the component
     */
    public function mount(array $page, array $data = []): void
    {
        // Merge default query options if available
        if (! empty($this->defaultQueryOptions)) {
            $this->queryOptions = $this->mergeByKey($this->defaultQueryOptions, $this->queryOptions);
        }

        // Get display options from manifest.json if viewName exists
        if (! empty($this->viewName)) {
            $manifestService        = app(ManifestServiceInterface::class);
            $manifestDisplayOptions = $manifestService->getDisplayOptions($this->viewName);

            if (! empty($manifestDisplayOptions)) {
                $this->displayOptions = $this->mergeByKey($manifestDisplayOptions, $this->displayOptions);
            }
        }

        $this->initializeComponent(app());
    }

    /**
     * Initialize the component after mounting.
     * Override this method in child classes to add custom initialization logic.
     *
     * @param  Container  $container  The service container for dependency resolution
     */
    protected function initializeComponent(Container $container): void
    {
        // Override in child classes if needed
    }

    public static function mergeByKey(array $default, array $data): array
    {
        return array_replace($default, array_intersect_key($data, $default));
    }

    protected function defaultConfig(): array
    {
        return array_merge($this->queryOptions, $this->displayOptions);
    }

    /**
     * Get the view name for this component.
     * Override this method in child classes to specify a custom view.
     *
     * @throws \RuntimeException If the view file does not exist
     */
    protected function getViewName(): string
    {
        if (empty($this->viewName)) {
            // Auto-generate view name from class name
            $className      = class_basename($this);
            $this->viewName = \Str::kebab($className);
        }

        // First, try to find view with style suffix (e.g., "article-list_fullscreen")
        if (! empty($this->viewStyle) && $this->viewStyle !== 'default') {
            $styleViewName = $this->viewName . '_' . $this->viewStyle;
            $styleViewPath = 'livewire.' . $styleViewName;

            if (view()->exists($styleViewPath)) {
                return $styleViewName;
            }
        }

        // Fallback to base view name without style
        $baseViewPath = 'livewire.' . $this->viewName;

        if (! view()->exists($baseViewPath)) {
            throw new \RuntimeException(
                sprintf(
                    'View "%s" not found for block "%s". Please ensure the view file exists in the theme.',
                    $baseViewPath,
                    get_class($this)
                )
            );
        }

        return $this->viewName;
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $viewName = 'livewire.' . $this->getViewName();

        return themed_view($viewName, $this->getExtraData());
    }

    protected function getExtraData(): array
    {
        return [];
    }

    /**
     * Get a value from the data array.
     *
     * @param  mixed  $default
     * @return mixed
     */
    protected function getData(string $key, $default = null)
    {
        return $this->displayOptions[$key] ?? $default;
    }

    /**
     * Set a value in the data array.
     *
     * @param  mixed  $value
     */
    protected function setData(string $key, $value): void
    {
        $this->displayOptions[$key] = $value;
    }

    /**
     * Check if a key exists in the data array.
     */
    protected function hasData(string $key): bool
    {
        return isset($this->displayOptions[$key]);
    }

    public function paginationView(): string
    {
        return 'pagination.bootstrap';
    }
}
