<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Admin;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Repositories\MenuRepository;
use Appsolutely\AIO\Services\PageBuilderDataEnricherService;
use Appsolutely\AIO\Services\PageService;

final class PageController extends AdminBaseController
{
    public function __construct(
        protected PageService $pageService,
        protected PageBuilderDataEnricherService $dataEnricherService,
        protected MenuRepository $menuRepository
    ) {}

    protected function grid(): Grid
    {
        return Grid::make(Page::query(), function (Grid $grid) {
            $grid->column('id', __t('ID'))->sortable();
            $grid->column('name', __t('Name (Internal use)'))->help(__t('form_help.internal_reference'))->display(function ($value) use ($grid) {
                return link_tag($grid->getEditUrl($this->id), e($value), '_self');
            });
            $grid->column('link', __t('Link'))->display(function () {
                $clean = trim($this->slug, '/');
                $url   = app_url($clean);

                return '<a href="' . $url . '" target="_blank">/' . $clean . '</a>';
            });
            $grid->column('title', __t('Title'))->editable();
            $grid->column('published_at', __t('Published At'))->display(column_time_format())->sortable();
            $grid->column('expired_at', __t('Expired At'))->display(column_time_format())->sortable();
            $grid->column('status', __t('Status'))->switch();
            $grid->model()->orderByDesc('id');

            $grid->quickSearch('id', 'name', 'slug', 'title');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id')->width(4);
                $filter->like('name')->width(4);
                $filter->like('slug')->width(4);
                $filter->like('title')->width(4);
                $filter->equal('status')->width(4);
                $filter->between('created_at')->datetime()->width(4);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();

                $pageId    = $actions->row?->id;
                $reference = $actions->row?->reference;
                // $blockUrl  = admin_route('pages.blocks.index') . '?page_id=' . (int) $pageId . '#block-settings';
                // $actions->prepend(admin_link_action('Manage Blocks', $blockUrl, '_blank', 'icon-box', 'primary'));
                $actions->prepend(admin_link_action('Design', admin_route('pages.design', [$reference]), '_blank'));
            });
        });
    }

    protected function form(): Form
    {
        return Form::make(Page::query(), function (Form $form) {
            $form->tab(__t('Basic'), function (Form $form) {
                $form->display('id', __t('ID'));
                $form->text('title', __t('Title'))->required();
                $form->text('name', __t('Name (Internal use)'))->help(__t('form_help.name_fallback_title'));
                $form->text('slug', __t('Slug'));
                $form->text('keywords', __t('Keywords'));
                $form->textarea('description', __t('Description'))->rows(3);
                // $form->editor('content', __t('Content'));
                $form->datetime('published_at', __t('Published At (%s)', [app_local_timezone()]));
                $form->datetime('expired_at', __t('Expired At (%s)', [app_local_timezone()]));
                $form->switch('status', __t('Status'));
            })->tab(__t('SEO & Meta'), function (Form $form) {
                $form->text('h1_text', __t('H1 Text'));
                $form->text('canonical_url', __t('Canonical URL'));
                $form->text('meta_robots', __t('Meta Robots'));
                $form->text('og_title', __t('OG Title'));
                $form->textarea('og_description', __t('OG Description'))->rows(3);
                $form->text('og_image', __t('OG Image'));
                $form->keyValue('structured_data', __t('Structured Data'))->default([])->setKeyLabel('Key')->setValueLabel('Value')->saveAsJson();
                $form->text('hreflang', __t('Hreflang'));
                $form->text('language', __t('Language'));
            })->tab(__t('Design'), function (Form $form) {
                // Content intentionally empty — this tab acts as a navigation link (see script below)
            });

            if ($form->getKey()) {
                $this->makeTabLink(__t('Design'), admin_route('pages.design', [$form->model()->reference]));
                $this->addMenuDeactivationConfirmation($form);
            }

            $form->saving(function (Form $form) {
                // If name is empty, use title value
                if (empty($form->input('name'))) {
                    $form->input('name', $form->input('title'));
                }

                // Check for active menus when deactivating a page
                $response = $this->checkMenuDeactivation($form);
                if ($response !== null) {
                    return $response;
                }
            });

            $form->disableViewButton();
            $form->disableViewCheck();
        });
    }

    /**
     * Override update to check for active menus on inline grid status toggle.
     *
     * @throws \Exception
     */
    public function update($id)
    {
        if (request()->get('_inline_edit_')) {
            $status = request()->input('status');

            if ($status !== null && (int) $status === Status::INACTIVE->value) {
                $page = Page::find($id);

                if ($page) {
                    $activeMenus = $this->menuRepository->findActiveMenusByPageSlug($page->slug ?? '');

                    if ($activeMenus->isNotEmpty()) {
                        $confirmed = request()->boolean('_confirm_menu_deactivation');

                        if (! $confirmed) {
                            $menuTitles = $activeMenus->pluck('title')->implode(', ');

                            return (new Form())
                                ->response()
                                ->error(__t('This page has active menu items: %s. Please confirm deactivation from the edit form.', [$menuTitles]));
                        }
                    }
                }
            }
        }

        return parent::update($id);
    }

    /**
     * Add JavaScript for menu deactivation confirmation dialog on the edit form.
     */
    private function addMenuDeactivationConfirmation(Form $form): void
    {
        $page = $form->model();
        $slug = $page->slug ?? '';

        if (empty($slug)) {
            return;
        }

        $activeMenus = $this->menuRepository->findActiveMenusByPageSlug($slug);

        if ($activeMenus->isEmpty()) {
            return;
        }

        $menuTitles    = $activeMenus->pluck('title')->implode(', ');
        $confirmTitle  = __t('Active Menu Warning');
        $confirmText   = __t('This page is linked to active menu(s): %s. Are you sure you want to deactivate this page?', [$menuTitles]);
        $confirmButton = __t('Yes, deactivate');
        $cancelButton  = __t('Cancel');

        $form->hidden('_confirm_menu_deactivation')->default('0');

        Admin::script(<<<JS
            (function() {
                var originalFormSubmit = null;
                var formEl = document.querySelector('form[method="POST"]');
                if (!formEl) return;

                formEl.addEventListener('submit', function(e) {
                    var statusField = formEl.querySelector('[name="status"]');
                    if (!statusField) return;

                    var isChecked = statusField.type === 'checkbox' ? statusField.checked : statusField.value == '1';
                    var confirmField = formEl.querySelector('[name="_confirm_menu_deactivation"]');

                    if (!isChecked && confirmField && confirmField.value !== '1') {
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        Swal.fire({
                            title: '{$confirmTitle}',
                            text: '{$confirmText}',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: '{$confirmButton}',
                            cancelButtonText: '{$cancelButton}'
                        }).then(function(result) {
                            if (result.isConfirmed) {
                                confirmField.value = '1';
                                formEl.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
                            }
                        });
                    }
                }, true);
            })();
        JS);
    }

    /**
     * Server-side check for active menus when deactivating a page.
     *
     * @return mixed|null Response to halt saving, or null to continue
     */
    private function checkMenuDeactivation(Form $form): mixed
    {
        $statusValue = $form->input('status');

        $isDeactivating = in_array($statusValue, ['off', '0', 0, 'false', false], true);

        if (! $isDeactivating || ! $form->isEditing()) {
            return null;
        }

        $confirmed = request()->boolean('_confirm_menu_deactivation');

        if ($confirmed) {
            return null;
        }

        $page = $form->model();
        $slug = $page->slug ?? '';

        if (empty($slug)) {
            return null;
        }

        $activeMenus = $this->menuRepository->findActiveMenusByPageSlug($slug);

        if ($activeMenus->isEmpty()) {
            return null;
        }

        $menuTitles = $activeMenus->pluck('title')->implode(', ');

        return $form->response()
            ->error(__t('This page has active menu items: %s. Are you sure you want to deactivate?', [$menuTitles]));
    }

    /**
     * Show the page builder interface.
     * Enriches page setting with server-rendered block HTML for canvas display.
     */
    public function design(string $reference)
    {
        $page = $this->pageService->findByReference($reference);

        if (empty($page)) {
            abort(404);
        }

        $setting  = $page->setting ?? [];
        $enriched = is_array($setting) ? $this->dataEnricherService->enrich($page, $setting) : $setting;

        $page->setAttribute('content', $enriched);

        return view('page-builder::modern', [
            'page'      => $page,
            'reference' => $reference,
        ]);
    }
}
