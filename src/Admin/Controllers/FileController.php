<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers;

use Appsolutely\AIO\Form;
use Appsolutely\AIO\Grid;
use Appsolutely\AIO\Helpers\DashboardHelper;
use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Appsolutely\AIO\Services\Contracts\StorageServiceInterface;
use Appsolutely\AIO\Show;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class FileController extends AdminBaseController
{
    public function __construct(
        private readonly StorageServiceInterface $storageService
    ) {}

    /**
     * Make a grid builder.
     */
    protected function grid(): Grid
    {
        return Grid::make(new File(), function (Grid $grid) {
            $grid->column('id', __t('ID'))->sortable();

            // Display file preview for images
            $grid->column('preview', __t('Preview'))->display(function () {
                return DashboardHelper::imageThumbnail($this->full_path);
            });

            $grid->column('filename', __t('Filename'))->width('120px')->sortable();
            $grid->column('original_filename', __t('Original Filename'))->width('80px')->sortable();
            $grid->column('extension', __t('Extension'))->sortable();
            $grid->column('mime_type', __t('MIME Type'))->width('80px');
            $grid->column('size', __t('Size'))->display(column_file_size())->sortable();
            $grid->column('created_at', __t('Created At'))->display(column_time_format())->sortable();
            $grid->model()->orderByDesc('id');

            $grid->quickSearch('id', 'original_filename', 'filename', 'extension');
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('original_filename')->width(4);
                $filter->like('filename')->width(4);
                $filter->equal('extension')->width(4);
                $filter->like('mime_type')->width(4);
                $filter->between('created_at')->datetime()->width(4);
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableEdit();
            });
        });
    }

    /**
     * Make a show builder.
     */
    protected function detail(mixed $id): Show
    {
        return Show::make($id, new File(), function (Show $show) {
            $show->field('id');
            $show->field('original_filename');
            $show->field('filename');

            // Display file preview for images
            $show->field('preview')->unescape()->as(function () {
                return DashboardHelper::imageThumbnail($this->full_path);
            });

            $show->field('extension');
            $show->field('mime_type');
            $show->field('path');

            // Use the helper to format the file size
            $show->field('size')->as(column_file_size());

            $show->field('hash');
            $show->field('created_at')->as(column_time_format());
            $show->field('updated_at')->as(column_time_format());

            $show->disableEditButton();
            // Display related file attachments
            $show->relation('File Attachments', function ($model) {
                $grid = new Grid(new FileAttachment());
                $grid->model()->where('file_id', $model->id);
                $grid->column('id');
                $grid->column('file_path');
                $grid->column('attachable_type');
                $grid->column('attachable_id');
                $grid->column('type');
                $grid->column('created_at')->display(column_time_format());
                $grid->disableActions();

                return $grid;
            });
        });
    }

    /**
     * Make a form builder.
     */
    protected function form(): Form
    {
        return Form::make(new File(), function (Form $form) {
            if ($form->isCreating()) {
                $form->multipleFile('files', 'Upload Files')
                    ->required()
                    ->accept('*')
                    ->autoUpload()
                    ->url(upload_to_api())
                    ->uniqueName()
                    ->help('Upload any file types. You can select multiple files at once.');

                $form->disableSubmitButton();
                $form->disableResetButton();

                $form->html('<div class="form-footer text-center">
                <a href="' . admin_route('file-manager.index') . '" class="btn btn-primary">
                    <i class="feather icon-list"></i><span>&nbsp;Back to List</span>
                </a></div>');
            }

        });
    }

    public function retrieve(Request $request, ?string $filePath = null): Response|JsonResponse
    {
        if (empty($filePath)) {
            abort(404);
        }

        return $this->storageService->response($request, $filePath);
    }
}
