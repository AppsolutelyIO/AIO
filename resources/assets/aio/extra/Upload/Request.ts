
declare const AIO: AIOInstance;

interface UploaderParent {
    uploader: WebUploaderInstance & { getStats(): { successNum: number } };
    options: Record<string, unknown>;
    lang: { trans(key: string, replacements?: Record<string, unknown>): string };
    input: { get(): string[]; delete(id: string): void };
    helper: { showError(response: any): void };
    getColumn(): string;
    relation: unknown;
    [key: string]: unknown;
}

export default class Request {
    uploader: UploaderParent;

    constructor(Uploader: UploaderParent) {
        this.uploader = Uploader;
    }

    delete(file: { serverId: string }, callback: (result: any) => void): void {
        let _this = this,
            parent = _this.uploader,
            options = parent.options,
            uploader = parent.uploader;

        AIO.confirm(parent.lang.trans('confirm_delete_file'), file.serverId, function () {
            var post = options.deleteData as Record<string, unknown>;

            (post as any).key = file.serverId;

            if (! (post as any).key) {
                parent.input.delete(file.serverId);

                return uploader.removeFile(file as any);
            }

            (post as any)._column = parent.getColumn();
            (post as any)._relation = parent.relation;

            (AIO as any).loading();

            $.post({
                url: options.deleteUrl as string,
                data: post,
                success: function (result: any) {
                    (AIO as any).loading(false);

                    if (result.status) {
                        callback(result);

                        return;
                    }

                    parent.helper.showError(result)
                }
            });

        });
    }

    // 保存已上传的文件名到服务器
    update(): void {
        let _this = this,
            parent = _this.uploader,
            uploader = parent.uploader,
            options = parent.options,
            updateColumn = parent.getColumn(),
            relation = _this.uploader.relation as [string, string] | undefined,
            values = parent.input.get(), // 获取表单值
            num = uploader.getStats().successNum,
            form = $.extend({}, options.formData) as Record<string, unknown>;

        if (!num || !values || !options.autoUpdateColumn) {
            return;
        }

        if (relation) {
            if (!relation[1]) {
                // 新增子表记录，则不调用update接口
                return;
            }

            form[relation[0]] = {} as Record<string, unknown>;

            (form[relation[0]] as Record<string, unknown>)[relation[1]] = {} as Record<string, unknown>;
            ((form[relation[0]] as Record<string, unknown>)[relation[1]] as Record<string, unknown>)[updateColumn] = values.join(',');
        } else {
            form[updateColumn] = values.join(',');
        }

        delete form['_relation'];
        delete form['upload_column'];

        $.post({
            url: options.updateServer as string,
            data: form,
        });
    }
}
