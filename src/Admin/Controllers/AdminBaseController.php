<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Exceptions\NotFoundException;
use Appsolutely\AIO\Models\Model;
use Appsolutely\AIO\Services\SitemapBuilderService;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Http\Controllers\AdminController;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class AdminBaseController extends AdminController
{
    const UPDATE_TO_IGNORE_FIELDS = ['_inline_edit_', '_method', '_token'];

    protected function form() {}

    /**
     * Get the model class name for this controller.
     * Override this method in child controllers to explicitly define the model.
     *
     * @return string Fully qualified model class name
     */
    protected function getModelClass(): string
    {
        // Fallback to reflection-based resolution for backward compatibility
        // Child controllers should override this method for explicit definition
        $controller = Str::before(class_basename($this), 'Controller');
        $model      = (new \ReflectionClass(Model::class))->getNamespaceName() . '\\' . $controller;

        if (! class_exists($model)) {
            $message = "Model class '{$model}' not found for controller '{$controller}'. " .
                       'Please override getModelClass() method in ' . get_class($this);
            log_error($message);
            throw new NotFoundException(
                $model,
                'The requested model could not be found.',
                null,
                ['controller' => $controller, 'model' => $model]
            );
        }

        return $model;
    }

    /**
     * @throws \Exception
     */
    public function update($id)
    {
        if (! request()->get('_inline_edit_')) {
            return parent::update($id);
        }

        $data       = request()->all();
        $modelClass = $this->getModelClass();
        $object     = (new $modelClass())->findOrFail($id);
        $filterData = \Arr::except($data, self::UPDATE_TO_IGNORE_FIELDS);
        $object->update($filterData);

        return (new Form())->response()->success(trans('admin.update_succeeded'))->refresh();
    }

    /**
     * Build admin grid URL links for a model that has a slug and optional categories relationship.
     */
    protected function buildSlugUrls(EloquentModel $model): HtmlString
    {
        $slug = normalize_slug($model->slug ?? '', false);

        if (empty($slug)) {
            return new HtmlString('<span class="text-muted">—</span>');
        }

        $baseUrl = rtrim(app_url(), '/');

        $categories = $model->relationLoaded('categories') ? $model->categories : collect();

        if ($categories->isEmpty()) {
            return new HtmlString(link_tag($baseUrl . '/' . $slug, truncate('/' . $slug)));
        }

        $sitemapBuilder = app(SitemapBuilderService::class);

        return new HtmlString(
            $categories->map(function ($category) use ($slug, $baseUrl, $sitemapBuilder) {
                $fullPath = path_join($sitemapBuilder->buildCategoryPath($category), $slug);

                return link_tag($baseUrl . '/' . $fullPath, truncate('/' . $fullPath));
            })->implode('<br>')
        );
    }

    /**
     * Convert a form tab into an external link that opens in a new browser tab.
     */
    protected function makeTabLink(string $tabLabel, string $url): void
    {
        \Appsolutely\AIO\Admin::script(<<<JS
            (function () {
                document.querySelectorAll('.nav-tabs .nav-link, .nav-tabs > li > a').forEach(function (tab) {
                    if (tab.textContent.trim() === '$tabLabel') {
                        tab.removeAttribute('data-toggle');
                        tab.removeAttribute('data-bs-toggle');
                        tab.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            window.open('$url', '_blank');
                        }, true);
                    }
                });
            })();
        JS);
    }

    protected function detail($id)
    {
        $routeName     = request()->route()->getName();
        $redirectRoute = str_replace('show', 'edit', $routeName);

        return redirect(route($redirectRoute, [$id]));
    }
}
