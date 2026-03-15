import '../jquery-form/jquery.form.min';

interface FormOptions {
    form: JQuery | string | null;
    validate: boolean;
    confirm: { title: string | null; content: string | null };
    validationErrorToastr: boolean;
    errorClass: string;
    errorContainerSelector: string;
    groupSelector: string;
    tabSelector: string;
    errorTemplate: string;
    redirect: boolean;
    autoRemoveError: boolean;
    before: (...args: unknown[]) => unknown;
    after: (...args: unknown[]) => unknown;
    success: (...args: unknown[]) => unknown;
    error: (...args: unknown[]) => unknown;
}

interface FormCallbacks {
    before: Array<(...args: unknown[]) => unknown>;
    success: Array<(...args: unknown[]) => unknown>;
    error: Array<(...args: unknown[]) => unknown>;
}

let formCallbacks: FormCallbacks = {
    before: [],
    success: [],
    error: [],
};

class Form {
    public options: FormOptions;
    public originalValues: Record<string, unknown[]>;
    public $form: JQuery;
    public _errColumns: Record<string, JQuery | undefined>;

    static submitting: (callback: (...args: unknown[]) => unknown) => typeof Form;
    static submitted: (
        success?: (...args: unknown[]) => unknown,
        error?: (...args: unknown[]) => unknown,
    ) => typeof Form;

    constructor(options: Partial<FormOptions>) {
        let _this = this;

        _this.options = $.extend(
            {
                form: null,
                validate: false,
                confirm: { title: null, content: null },
                validationErrorToastr: false,
                errorClass: 'has-error',
                errorContainerSelector: '.with-errors',
                groupSelector: '.form-group,.form-label-group,.form-field',
                tabSelector: '.tab-pane',
                errorTemplate:
                    '<label class="control-label" for="inputError"><i class="feather icon-x-circle"></i> {message}</label><br/>',
                redirect: true,
                autoRemoveError: true,
                before: function () {},
                after: function () {},
                success: function () {},
                error: function () {},
            },
            options,
        ) as FormOptions;

        _this.originalValues = {};
        _this.$form = ($(_this.options.form as any) as JQuery).first();
        _this._errColumns = {};

        _this.init();
    }

    init(): void {
        let _this = this;
        let confirm = _this.options.confirm;

        if (!confirm.title) {
            return _this.submit();
        }

        (window as Window & { AIO: AIOInstance }).AIO.confirm(confirm.title, confirm.content as string, function () {
            _this.submit();
        });
    }

    submit(): void {
        let _this = this,
            $form = _this.$form,
            options = _this.options,
            $submitButton = $form.find('[type="submit"],.submit');

        _this.removeErrors();

        ($form as unknown as Record<string, (opts: Record<string, unknown>) => void>).ajaxSubmit({
            data: { _token: (window as Window & { AIO: AIOInstance }).AIO.token },
            beforeSubmit: function (fields: unknown[], form: JQuery, _opt: Record<string, unknown>) {
                if (options.before(fields, form, _opt, _this) === false) {
                    return false;
                }

                if (fire(formCallbacks.before, fields, form, _opt, _this) === false) {
                    return false;
                }

                if (options.validate) {
                    ($form as unknown as Record<string, (method: string) => void>).validator('validate');

                    if ($form.find('.' + options.errorClass).length > 0) {
                        return false;
                    }
                }

                ($submitButton as unknown as Record<string, (state?: boolean) => void>).buttonLoading();
            },
            success: function (response: Record<string, unknown>) {
                setTimeout(function () {
                    ($submitButton as unknown as Record<string, (state?: boolean) => void>).buttonLoading(false);
                }, 700);

                if (options.after(true, response, _this) === false) {
                    return;
                }

                if (options.success(response, _this) === false) {
                    return;
                }

                if (fire(formCallbacks.success, response, _this) === false) {
                    return;
                }

                if (response.redirect === false || !options.redirect) {
                    if (response.data && (response.data as Record<string, unknown>).then) {
                        delete (response.data as Record<string, unknown>)['then'];
                        delete (response.data as Record<string, unknown>)['then'];
                        delete (response.data as Record<string, unknown>)['then'];
                    }
                }

                (window as Window & { AIO: AIOInstance }).AIO.handleJsonResponse(response);
            },
            error: function (response: JQueryXHR) {
                ($submitButton as unknown as Record<string, (state?: boolean) => void>).buttonLoading(false);

                if (options.after(false, response, _this) === false) {
                    return;
                }

                if (options.error(response, _this) === false) {
                    return;
                }

                if (fire(formCallbacks.error, response, _this) === false) {
                    return;
                }

                try {
                    var error = JSON.parse(response.responseText) as Record<string, unknown>,
                        key: string;

                    const AIO = (window as Window & { AIO: AIOInstance }).AIO;

                    if (response.status != 422 || !error || !(AIO.helpers as any).isset(error, 'errors')) {
                        let json = response.responseJSON as Record<string, unknown> | undefined;
                        if (json && json.message) {
                            return AIO.error(json.message as string);
                        }

                        return AIO.error(response.status + ' ' + response.statusText);
                    }
                    error = error.errors as Record<string, string[]>;

                    for (key in error) {
                        _this._errColumns[key] = _this.showError($form, key, error[key] as string[]);
                    }
                } catch (e) {
                    return (window as Window & { AIO: AIOInstance }).AIO.error(
                        response.status + ' ' + response.statusText,
                    );
                }
            },
        });
    }

    showError($form: JQuery, column: string, errors: string[]): JQuery | undefined {
        let _this = this,
            $field = _this.queryFieldByName($form, column),
            $group = $field.closest(_this.options.groupSelector),
            render = function (msg: string | string[]): void {
                $group.addClass(_this.options.errorClass);

                if (typeof msg === 'string') {
                    msg = [msg];
                }

                for (let j in msg) {
                    $group
                        .find(_this.options.errorContainerSelector)
                        .first()
                        .append(_this.options.errorTemplate.replace('{message}', msg[j]));
                }

                if (_this.options.validationErrorToastr) {
                    (window as Window & { AIO: AIOInstance }).AIO.error((msg as string[]).join('<br/>'));
                }
            };

        queryTabTitleError(_this, $field).removeClass('d-none');

        _this.originalValues[column] = _this.getFieldValue($field);

        if (!$field) {
            const AIO = (window as Window & { AIO: AIOInstance }).AIO;
            if (AIO.helpers.len(errors) && errors.length) {
                AIO.error(errors.join('  \n  '));
            }
            return;
        }

        render(errors);

        if (_this.options.autoRemoveError) {
            removeErrorWhenValChanged(_this, $field, column);
        }

        return $field;
    }

    getFieldValue($field: JQuery): unknown[] {
        let vals: unknown[] = [],
            type = $field.attr('type'),
            checker = type === 'checkbox' || type === 'radio',
            i: number;

        for (i = 0; i < $field.length; i++) {
            if (checker) {
                vals.push($($field[i]).prop('checked'));
                continue;
            }

            vals.push($($field[i]).val());
        }

        return vals;
    }

    isValueChanged($field: JQuery, column: string): boolean {
        return !(window as any).AIO.helpers.equal(this.originalValues[column], this.getFieldValue($field));
    }

    queryFieldByName($form: JQuery, column: string | string[]): JQuery {
        if (typeof column === 'string' && column.indexOf('.') !== -1) {
            column = column.split('.');

            let first = column.shift() as string,
                i: string,
                sub = '';

            for (i in column) {
                sub += '[' + column[i] + ']';
            }
            column = first + sub;
        }

        var $c = $form.find('[name="' + column + '"]');

        if (!$c.length) $c = $form.find('[name="' + column + '[]"]');

        if (!$c.length) {
            $c = $form.find('[name="' + (column as string).replace(/start$/, '') + '"]');
        }
        if (!$c.length) {
            $c = $form.find('[name="' + (column as string).replace(/end$/, '') + '"]');
        }

        if (!$c.length) {
            $c = $form.find('[name="' + (column as string).replace(/start\]$/, ']') + '"]');
        }
        if (!$c.length) {
            $c = $form.find('[name="' + (column as string).replace(/end\]$/, ']') + '"]');
        }

        return $c;
    }

    removeError($field: JQuery, column: string): void {
        let options = this.options,
            parent = $field.parents(options.groupSelector),
            errorClass = options.errorClass;

        parent.removeClass(errorClass);
        parent.find(options.errorContainerSelector).html('');

        let tab: JQuery;

        if (!queryTabByField(this, $field).find('.' + errorClass).length) {
            tab = queryTabTitleError(this, $field);
            if (!tab.hasClass('d-none')) {
                tab.addClass('d-none');
            }
        }

        delete this._errColumns[column];
    }

    removeErrors(): void {
        let _this = this,
            column: string,
            tab: JQuery;

        _this.$form.find(_this.options.errorContainerSelector).each(function (_, $err) {
            $($err).parents(_this.options.groupSelector).removeClass(_this.options.errorClass);
            $($err).html('');
        });

        for (column in _this._errColumns) {
            tab = queryTabTitleError(_this, _this._errColumns[column] as unknown as JQuery);
            if (!tab.hasClass('d-none')) {
                tab.addClass('d-none');
            }
        }

        _this._errColumns = {};
    }
}

Form.submitting = function (callback: (...args: unknown[]) => unknown): typeof Form {
    typeof callback == 'function' && formCallbacks.before.push(callback);

    return this;
};

Form.submitted = function (
    success?: (...args: unknown[]) => unknown,
    error?: (...args: unknown[]) => unknown,
): typeof Form {
    typeof success == 'function' && formCallbacks.success.push(success);
    typeof error == 'function' && formCallbacks.error.push(error);

    return this;
};

function removeErrorWhenValChanged(form: Form, $field: JQuery, column: string): void {
    let remove = function (): void {
        form.removeError($field, column);
    };

    $field.one('change', remove);
    $field.off('blur', remove).on('blur', function () {
        if (form.isValueChanged($field, column)) {
            remove();
        }
    });

    let interval = function (): void {
        setTimeout(function () {
            if (!$field.length) {
                return;
            }
            if (form.isValueChanged($field, column)) {
                return remove();
            }

            interval();
        }, 500);
    };

    interval();
}

function getTabId(form: Form, $field: JQuery): string | undefined {
    return $field.parents(form.options.tabSelector).attr('id');
}

function queryTabByField(form: Form, $field: JQuery): JQuery {
    let tabId = getTabId(form, $field);

    if (!tabId) {
        return $('<none></none>');
    }

    return $(`a[href="#${tabId}"]`);
}

function queryTabTitleError(form: Form, $field: JQuery): JQuery {
    return queryTabByField(form, $field).find('.has-tab-error');
}

function fire(callbacks: Array<(...args: unknown[]) => unknown>, ...rest: unknown[]): unknown {
    let i: string, result: unknown;

    for (i in callbacks) {
        result = callbacks[i].apply(callbacks[i], rest);

        if (result === false) {
            return result;
        }
    }
}

($.fn as any).form = function (this: JQuery, options: Record<string, unknown>): void {
    let $this = $(this);

    options = $.extend(options, {
        form: $this,
    });

    $this.on('submit', function () {
        return false;
    });

    $this.find('[type="submit"],.submit').click(function () {
        (window as Window & { AIO: AIOInstance }).AIO.Form(options);

        return false;
    });
};

export default Form;
