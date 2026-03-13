import Helper from './Upload/Helper'
import Request from './Upload/Request'
import Input from './Upload/Input'
import Status from './Upload/Status'
import AddFile from './Upload/AddFile'
import AddUploadedFile from './Upload/AddUploadedFile'

/**
 * WebUploader 上传组件
 *
 * @see http://fex.baidu.com/webuploader/
 */
(function (w: Window & { AIO: AIOInstance }, $: JQueryStatic) {
    let AIO = w.AIO;

    interface UploaderOptions {
        wrapper: string;
        addFileButton: string;
        inputSelector: string;
        isImage: boolean;
        preview: Array<{ id: string; url: string; path: string }>;
        server: string;
        updateServer: string;
        autoUpload: boolean;
        sortable: boolean;
        deleteUrl: string;
        deleteData: Record<string, unknown>;
        thumbHeight: number;
        elementName: string;
        disabled: boolean;
        autoUpdateColumn: boolean;
        removable: boolean;
        downloadable: boolean;
        dimensions: Record<string, number>;
        lang: Record<string, string>;
        upload: Record<string, unknown>;
        selector: string;
        formData: Record<string, unknown>;
        [key: string]: unknown;
    }

    class Uploader {
        options: UploaderOptions;
        uploader: WebUploaderInstance;
        $selector: JQuery;
        updateColumn: string;
        relation: unknown;
        helper: Helper;
        request: Request;
        status: Status;
        addFile: AddFile;
        addUploadedFile: AddUploadedFile;
        input: Input;
        lang: { trans(key: string, replacements?: Record<string, unknown>): string };
        percentages: Record<string, [number, number]>;
        faildFiles: Record<string, WebUploaderFile>;
        formFiles: Record<string, WebUploaderFile>;
        fileCount: number;
        fileSize: number;
        $wrapper!: JQuery;
        $files!: JQuery;
        $statusBar!: JQuery;
        $uploadButton!: JQuery;
        $placeholder!: JQuery;
        $progress!: JQuery;
        $infoBox!: JQuery;

        constructor(options: Partial<UploaderOptions>) {
            this.options = options = $.extend({
                wrapper: '.web-uploader', // 图片显示容器选择器
                addFileButton: '.add-file-button', // 继续添加按钮选择器
                inputSelector: '',
                isImage: false,
                preview: [], // 数据预览
                server: '',
                updateServer: '',
                autoUpload: false,
                sortable: false,
                deleteUrl: '',
                deleteData: {},
                thumbHeight: 160,
                elementName: '',
                disabled: false, // 禁止任何上传编辑
                autoUpdateColumn: false,
                removable: false, // 是否允许直接删除服务器图片
                downloadable: false, // 是否允许下载文件
                dimensions: {
                },
                lang: {
                    exceed_size: '文件大小超出',
                    interrupt: '上传暂停',
                    upload_failed: '上传失败，请重试',
                    selected_files: '选中:num个文件，共:size。',
                    selected_has_failed: '已成功上传:success个文件，:fail个文件上传失败，<a class="retry"  href="javascript:"";">重新上传</a>失败文件或<a class="ignore" href="javascript:"";">忽略</a>',
                    selected_success: '共:num个(:size)，已上传:success个。',
                    dot: '，',
                    failed_num: '失败:fail个。',
                    pause_upload: '暂停上传',
                    go_on_upload: '继续上传',
                    start_upload: '开始上传',
                    upload_success_message: '已成功上传:success个文件',
                    go_on_add: '继续添加',
                    Q_TYPE_DENIED: '对不起，不允许上传此类型文件',
                    Q_EXCEED_NUM_LIMIT: '对不起，已超出文件上传数量限制，最多只能上传:num个文件',
                    F_EXCEED_SIZE: '对不起，当前选择的文件过大',
                    Q_EXCEED_SIZE_LIMIT: '对不起，已超出文件大小限制',
                    F_DUPLICATE: '文件重复',
                    confirm_delete_file: '您确定要删除这个文件吗？',
                },
                upload: { // web-uploader配置
                    formData: {
                        _id: null, // 唯一id
                    },
                    thumb: {
                        width: 160,
                        height: 160,
                        quality: 70,
                        allowMagnify: true,
                        crop: true,
                        preserveHeaders: false,
                        type: 'image/jpeg'
                    },
                }
            }, options) as UploaderOptions;

            let _this = this;

            // WebUploader
            // @see http://fex.baidu.com/webuploader/
            _this.uploader = WebUploader.create(options.upload!);

            _this.$selector = $((options as UploaderOptions).selector);
            _this.updateColumn = (options.upload as any).formData.upload_column || ('webup' + (AIO.helpers as any).random());
            _this.relation = (options.upload as any).formData._relation; // 一对多关联关系名称

            // 帮助函数
            let helper = new Helper(_this as any),
                // 请求处理
                request = new Request(_this as any),
                // 状态管理
                status = new Status(_this as any),
                // 添加文件
                addFile = new AddFile(_this as any),
                // 添加已上传文件
                addUploadedFile = new AddUploadedFile(_this as any),
                // 表单
                input = new Input(_this as any);

            _this.helper = helper;
            _this.request = request;
            _this.status = status;
            _this.addFile = addFile;
            _this.addUploadedFile = addUploadedFile;
            _this.input = input;

            // 翻译
            _this.lang = AIO.Translator(options.lang as Record<string, string>);

            // 所有文件的进度信息，key为file id
            _this.percentages = {};
            // 临时存储上传失败的文件，key为file id
            _this.faildFiles = {};
            // 临时存储添加到form表单的文件
            _this.formFiles = {};
            // 添加的文件数量
            _this.fileCount = 0;
            // 添加的文件总大小
            _this.fileSize = 0;

            if (typeof (options.upload as any).formData._id === "undefined" || ! (options.upload as any).formData._id) {
                (options.upload as any).formData._id = _this.updateColumn + (AIO.helpers as any).random();
            }
        }

        // 初始化
        build(): void {
            let _this = this,
                uploader = _this.uploader,
                options = _this.options,
                $wrap = _this.$selector.find(options.wrapper),
                // 图片容器
                $queue = $('<ul class="filelist"></ul>').appendTo($wrap.find('.queueList')),
                // 状态栏，包括进度和控制按钮
                $statusBar = $wrap.find('.statusBar'),
                // 文件总体选择信息。
                $info = $statusBar.find('.info'),
                // 上传按钮
                $upload = $wrap.find('.upload-btn'),
                // 没选择文件之前的内容。
                $placeholder = $wrap.find('.placeholder'),
                $progress = $statusBar.find('.upload-progress').hide();

            // jq选择器
            _this.$wrapper = $wrap;
            _this.$files = $queue;
            _this.$statusBar = $statusBar;
            _this.$uploadButton = $upload;
            _this.$placeholder = $placeholder;
            _this.$progress = $progress;
            _this.$infoBox = $info;

            if ((options.upload as any).fileNumLimit > 1 && ! options.disabled) {
                // 添加"添加文件"的按钮，
                uploader.addButton({
                    id: options.addFileButton,
                    label: '<i class="feather icon-folder"></i> &nbsp;' + _this.lang.trans('go_on_add')
                });
            }

            // 拖拽时不接受 js, txt 文件。
            _this.uploader.on('dndAccept', function (items: any[]) {
                var denied = false,
                    len = items.length,
                    i = 0,
                    // 修改js类型
                    unAllowed = 'text/plain;application/javascript ';

                for (; i < len; i++) {
                    // 如果在列表里面
                    if (~unAllowed.indexOf(items[i].type)) {
                        denied = true;
                        break;
                    }
                }

                return !denied;
            });

            // 进度条更新
            uploader.onUploadProgress = function (file: WebUploaderFile, percentage: number) {
                _this.percentages[file.id][1] = percentage;
                _this.status.updateProgress();
            };

            // 添加文件
            uploader.onFileQueued = function (file: WebUploaderFile) {
                _this.fileCount++;
                _this.fileSize += file.size;

                if (_this.fileCount === 1) {
                    // 隐藏 placeholder
                    $placeholder.addClass('element-invisible');
                    $statusBar.show();
                }

                // 添加文件
                _this.addFile.render(file);
                _this.status.switch('ready');

                // 更新进度条
                _this.status.updateProgress();

                if (!options.disabled && options.autoUpload) {
                    // 自动上传
                    uploader.upload()
                }
            };

            // 删除文件事件监听
            uploader.onFileDequeued = function (file: WebUploaderFile) {
                _this.fileCount--;
                _this.fileSize -= file.size;

                if (! _this.fileCount && !(AIO.helpers as any).len(_this.formFiles)) {
                    _this.status.switch('pending');
                }

                _this.removeUploadFile(file);
            };

            uploader.on('all', function (type: string, obj: any, reason: any) {
                switch (type) {
                    case 'uploadFinished':
                        _this.status.switch('confirm');
                        // 保存已上传的文件名到服务器
                        _this.request.update();
                        break;

                    case 'startUpload':
                        _this.status.switch('uploading');
                        break;

                    case 'stopUpload':
                        _this.status.switch('paused');
                        break;
                    case  'uploadAccept':
                        if (_this._uploadAccept(obj, reason) === false) {
                            return false;
                        }

                        break;
                }
            });

            uploader.onError = function (code: string) {
                switch (code) {
                    case 'Q_TYPE_DENIED':
                        AIO.error(_this.lang.trans('Q_TYPE_DENIED'));
                        break;
                    case 'Q_EXCEED_NUM_LIMIT':
                        AIO.error(_this.lang.trans('Q_EXCEED_NUM_LIMIT', {num: (options.upload as any).fileNumLimit}));
                        break;
                    case 'F_EXCEED_SIZE':
                        AIO.error(_this.lang.trans('F_EXCEED_SIZE'));
                        break;
                    case 'Q_EXCEED_SIZE_LIMIT':
                        AIO.error(_this.lang.trans('Q_EXCEED_SIZE_LIMIT'));
                        break;
                    case 'F_DUPLICATE':
                        AIO.warning(_this.lang.trans('F_DUPLICATE'));
                        break;
                    default:
                        AIO.error('Error: ' + code);
                }

            };

            // 上传按钮点击
            $upload.on('click', function () {
                let state = _this.status.state;

                if ($(this).hasClass('disabled')) {
                    return false;
                }

                if (state === 'ready') {
                    uploader.upload();
                } else if (state === 'paused') {
                    uploader.upload();
                } else if (state === 'uploading') {
                    uploader.stop();
                }
            });

            // 重试按钮
            $info.on('click', '.retry', function () {
                uploader.retry();
            });

            // 忽略按钮
            $info.on('click', '.ignore', function () {
                for (let i in _this.faildFiles) {
                    uploader.removeFile(i, true);

                    delete _this.faildFiles[i];
                }

            });

            // 初始化
            _this.status.switch('init');
        }

        _uploadAccept(obj: any, reason: any): boolean | void {
            let _this = this,
                options = _this.options;

            // 上传失败，返回false
            if (! reason || ! reason.status) {
                _this.helper.showError(reason);

                _this.faildFiles[obj.file.id] = obj.file;

                return false;
            }

            if (reason.data && reason.data.merge) {
                // 分片上传
                return;
            }

            // 上传成功，保存新文件名和路径到file对象
            obj.file.serverId = reason.data.id;
            obj.file.serverName = reason.data.name;
            obj.file.serverPath = reason.data.path;
            obj.file.serverUrl = reason.data.url || null;

            _this.addUploadedFile.add(obj.file);

            _this.input.add(reason.data.id);

            let $li = _this.getFileView(obj.file.id);

            if (! _this.isImage()) {
                $li.find('.file-action').hide();
                $li.find('[data-file-act="delete"]').show();
            }

            if (options.sortable) {
                $li.find('[data-file-act="order"]').removeClass('d-none').show();
            }
            if (options.downloadable) {
                let $download = $li.find('[data-file-act="download"]');
                $download.removeClass('d-none').show();
                $download.attr('data-id', obj.file.serverUrl);
            }
        }

        // 预览
        preview(): void {
            let _this = this,
                options = _this.options,
                i: string;

            for (i in options.preview) {
                let path = options.preview[i].path, ext: string | undefined;

                if (path.indexOf('.')) {
                    ext = path.split('.').pop();
                }

                let file: WebUploaderFile = {
                    serverId: options.preview[i].id,
                    serverUrl: options.preview[i].url,
                    serverPath: path,
                    ext: ext,
                    fake: 1,
                    id: '',
                    name: '',
                    size: 0,
                    type: '',
                };

                _this.status.switch('incrOriginalFileNum');
                _this.status.switch('decrFileNumLimit');

                // 添加文件到预览区域
                _this.addUploadedFile.render(file);
                _this.addUploadedFile.add(file);
            }
        }

        // 重新渲染已上传文件
        reRenderUploadedFiles(): void {
            let _this = this;

            _this.$files.html('');

            _this.addUploadedFile.reRender();
        }

        // 重置按钮位置
        refreshButton(): void {
            this.uploader.refresh();
        }

        // 获取文件视图选择器
        getFileViewSelector(fileId: string): string {
            return this.options.elementName.replace(/[\[\]]*/g, '_') + '-' + fileId;
        }

        getFileView(fileId: string): JQuery {
            return $('#' + this.getFileViewSelector(fileId));
        }

        // 负责view的销毁
        removeUploadFile(file: WebUploaderFile): void {
            let _this = this,
                $li = _this.getFileView(file.id);

            delete _this.percentages[file.id];
            _this.status.updateProgress();

            $li.off().find('.file-panel').off().end().remove();
        }

        // 上传字段名称
        getColumn(): string {
            return this.updateColumn
        }

        // 判断是否是图片上传
        isImage(): boolean {
            return this.options.isImage
        }
    }

    (AIO as any).Uploader = function (options: Partial<UploaderOptions>) {
        return new Uploader(options)
    };

})(window as any, jQuery);
