<?php

namespace Dcat\Admin\Form\Field;

use Dcat\Admin\Admin;
use Dcat\Admin\Form\Field;
use Dcat\Admin\Support\Helper;

/**
 * @see https://b3log.org/vditor/
 */
class Vditor extends Field
{
    /**
     * 编辑器配置.
     *
     * @var array
     */
    protected $options = [
        'height'   => 500,
        'mode'     => 'sv',
        'typewriterMode' => false,
        'outline'  => ['enable' => false],
        'toolbar'  => [
            'emoji', 'headings', 'bold', 'italic', 'strike', 'link',
            '|', 'list', 'ordered-list', 'check', 'outdent', 'indent',
            '|', 'quote', 'line', 'code', 'inline-code', 'insert-before', 'insert-after',
            '|', 'upload', 'table',
            '|', 'undo', 'redo',
            '|', 'fullscreen', 'edit-mode',
        ],
    ];

    protected $disk;

    protected $imageUploadDirectory = 'vditor/images';

    /**
     * 设置编辑器高度.
     *
     * @param  int  $height
     * @return $this
     */
    public function height(int $height)
    {
        $this->options['height'] = $height;

        return $this;
    }

    /**
     * 设置编辑模式: sv (分屏预览) | wysiwyg (所见即所得) | ir (即时渲染).
     *
     * @param  string  $mode
     * @return $this
     */
    public function mode(string $mode)
    {
        $this->options['mode'] = $mode;

        return $this;
    }

    /**
     * 设置文件上传存储配置.
     *
     * @param  string  $disk
     * @return $this
     */
    public function disk(string $disk)
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * 设置图片上传文件夹.
     *
     * @param  string  $dir
     * @return $this
     */
    public function imageDirectory(string $dir)
    {
        $this->imageUploadDirectory = $dir;

        return $this;
    }

    /**
     * 自定义图片上传接口.
     *
     * @param  string  $url
     * @return $this
     */
    public function imageUrl(string $url)
    {
        $this->options['upload']['url'] = admin_url($url);

        return $this;
    }

    /**
     * @return string
     */
    protected function defaultImageUploadUrl(): string
    {
        return Helper::urlWithQuery(
            route(admin_api_route_name('vditor.upload')),
            [
                '_token' => csrf_token(),
                'disk'   => $this->disk,
                'dir'    => $this->imageUploadDirectory,
            ]
        );
    }

    /**
     * @return string
     */
    public function render()
    {
        $cdn = admin_asset('@admin/dcat/plugins/vditor');

        $this->options['cdn'] = $cdn;
        $this->options['lang'] = $this->resolveLang();

        if (empty($this->options['upload']['url'])) {
            $this->options['upload']['url'] = $this->defaultImageUploadUrl();
            $this->options['upload']['fieldName'] = 'file[]';
            $this->options['upload']['multiple'] = true;
        }

        $this->addVariables(['cdn' => $cdn]);

        Admin::requireAssets('@vditor');

        return parent::render();
    }

    protected function resolveLang(): string
    {
        $locale = config('app.locale');

        $map = [
            'zh_CN' => 'zh_CN',
            'zh_TW' => 'zh_TW',
            'en'    => 'en_US',
            'ja'    => 'ja_JP',
            'ko'    => 'ko_KR',
            'ru'    => 'ru_RU',
        ];

        return $map[$locale] ?? 'en_US';
    }
}
