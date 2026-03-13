
declare const AIO: AIOInstance;

interface UploaderParent {
    uploader: WebUploaderInstance;
    options: Record<string, unknown>;
    addUploadedFile: { uploadedFiles: WebUploaderFile[] };
    input: { set(files: string[]): void };
    reRenderUploadedFiles(): void;
    [key: string]: unknown;
}

export default class Helper {
    uploader: UploaderParent;
    isSupportBase64: boolean;

    constructor(Uploder: UploaderParent) {
        this.uploader = Uploder;

        this.isSupportBase64 = this.supportBase64();
    }

    // 判断是否支持base64
    supportBase64(): boolean {
        let data = new Image(),
            support = true;

        data.onload = data.onerror = function (this: HTMLImageElement) {
            if (this.width != 1 || this.height != 1) {
                support = false;
            }
        } as any;
        data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";

        return support;
    }

    // 显示api响应的错误信息
    showError(response: any): void {
        var message = 'Unknown error!';
        if (response && response.data) {
            message = response.data.message || message;
        }

        AIO.error(message)
    }

    // 文件排序
    orderFiles($this: JQuery): void {
        var _this = this,
            $li = $this.parents('li').first(),
            fileId = $this.data('id') as string,
            order = $this.data('order') as number,
            $prev = $li.prev(),
            $next = $li.next();

        if (order) {
            // 升序
            if (!$prev.length) {
                return;
            }
            _this.swrapUploadedFile(fileId, order);
            _this.uploader.reRenderUploadedFiles();

            return;
        }

        if (!$next.length) {
            return;
        }

        _this.swrapUploadedFile(fileId, order);
        _this.uploader.reRenderUploadedFiles();
    }

    // 交换文件排序
    swrapUploadedFile(fileId: string, order: number): void {
        let _this = this,
            parent = _this.uploader,
            uploadedFiles = parent.addUploadedFile.uploadedFiles,
            index = parseInt(String(_this.searchUploadedFile(fileId))),
            currentFile = uploadedFiles[index],
            prevFile = uploadedFiles[index - 1],
            nextFile = uploadedFiles[index + 1];

        if (order) {
            if (index === 0) {
                return;
            }

            uploadedFiles[index - 1] = currentFile;
            uploadedFiles[index] = prevFile;
        } else {
            if (!nextFile) {
                return;
            }

            uploadedFiles[index + 1] = currentFile;
            uploadedFiles[index] = nextFile;
        }

        _this.setUploadedFilesToInput();
    }

    setUploadedFilesToInput(): void {
        let _this = this,
            parent = _this.uploader,
            uploadedFiles = parent.addUploadedFile.uploadedFiles,
            files: string[] = [],
            i: string;

        for (i in uploadedFiles) {
            if (uploadedFiles[i]) {
                files.push(uploadedFiles[i].serverId!);
            }
        }

        parent.input.set(files);
    }

    // 查找文件位置
    searchUploadedFile(fileId: string): number | string {
        let _this = this,
            parent = _this.uploader,
            uploadedFiles = parent.addUploadedFile.uploadedFiles;

        for (var i in uploadedFiles) {
            if (uploadedFiles[i].serverId === fileId) {
                return i;
            }
        }

        return -1;
    }
}
