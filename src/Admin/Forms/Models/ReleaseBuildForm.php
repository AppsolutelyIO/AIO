<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Enums\Architecture;
use Appsolutely\AIO\Enums\BuildStatus;
use Appsolutely\AIO\Enums\Platform;
use Appsolutely\AIO\Models\ReleaseBuild;
use Appsolutely\AIO\Repositories\ReleaseBuildRepository;
use Appsolutely\AIO\Repositories\ReleaseVersionRepository;
use Appsolutely\AIO\Widgets\Form;

class ReleaseBuildForm extends ModelForm
{
    protected ReleaseBuildRepository $repository;

    protected ReleaseVersionRepository $versionRepository;

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->repository        = app(ReleaseBuildRepository::class);
        $this->versionRepository = app(ReleaseVersionRepository::class);
    }

    protected function initializeModel(): void
    {
        $this->model = new ReleaseBuild();
    }

    public function form(): void
    {
        parent::form();

        $this->hidden('id');

        $versionOptions = $this->versionRepository->all()->pluck('version', 'id')->toArray();

        $this->column(6, function (Form $form) use ($versionOptions) {
            $form->select('version_id', __t('Version'))
                ->options($versionOptions)->required();

            $form->select('platform', __t('Platform'))->options(Platform::toArray())
                ->default('windows')
                ->help('Select a platform or enter a custom one below.')
                ->when('other', function (Form $form) {
                    $form->text('_platform', __t('Custom Platform (optional)'))
                        ->help('If filled, this will override the selected platform.');
                });
            $form->select('arch', __t('Arch'))->options(Architecture::toArray())
                ->help('Select the architecture or enter a custom one below.')
                ->when('other', function (Form $form) {
                    $form->text('_arch', __t('Custom Arch (optional)'))
                        ->help('If filled, this will override the selected architecture.');
                });
            $form->textarea('release_notes', __t('Release Notes'));
            $form->keyValue('gray_strategy', __t('Gray Strategy'))
                ->default([])
                ->setKeyLabel('Key')
                ->setValueLabel('Value')
                ->saveAsJson()
                ->help('Key-value pairs will be saved as JSON. Example: Key: percent   Value: 20 | Key: uuid_hash_range   Value: [0, 2000]');

        });

        $this->column(6, function (Form $form) {
            $form->select('build_status', __t('Build Status'))->options(BuildStatus::toArray())->nullable();
            $form->text('build_log', __t('Build Log'));
            $form->file('path', __t('Build File'))
                ->autoUpload()
                ->url(upload_to_api(ReleaseBuild::class, $form->getKey()))
                ->uniqueName()
                ->help('Upload a build file. The path will be stored and used for download. You can also enter a path manually.');

            $form->text('signature', __t('Signature'));

            $form->switch('force_update', __t('Force Update'));
            $form->switch('status', __t('Status'));
            $form->datetime('published_at', __t('Published At'));
        });
    }
}
