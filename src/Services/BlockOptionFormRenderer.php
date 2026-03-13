<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

/**
 * Renders server-side HTML form fields for block display/query option definitions.
 *
 * Supported field types: text, number, boolean, select, textarea, color, email, url,
 * date, time, image (image upload, accept defaults to image/*),
 * file/upload (generic file upload → stores URL), object (nested sub-fields),
 * table (editable rows with typed columns).
 *
 * Data attributes used by BlockOptionManager.ts for value collection:
 *   data-pb-field="key"         — scalar field
 *   data-pb-object="key"        — object wrapper
 *   data-pb-sub-field="subKey"  — sub-field inside an object
 *   data-pb-table="key"         — table wrapper
 *   data-pb-col="colKey"        — cell input inside a table row
 *   data-pb-add-row="key"       — "Add row" button for a table
 *   data-pb-remove-row          — "Remove row" button on a table row
 */
final readonly class BlockOptionFormRenderer
{
    private const INPUT_CLASS = 'w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary';

    private const LABEL_CLASS = 'block text-sm font-medium text-slate-700 mb-1';

    private const GROUP_CLASS = 'form-group';

    private const IMAGE_EXT_REGEX = '/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i';

    private static function isFileUploadType(string $type): bool
    {
        return in_array($type, ['file', 'upload', 'image'], true);
    }

    /**
     * Render form HTML for a full definition + values set.
     *
     * @param  array<string, array<string, mixed>>  $definition  Field definitions
     * @param  array<string, mixed>  $values  Current values
     */
    public function render(array $definition, array $values): string
    {
        if (empty($definition)) {
            return '<p class="text-sm text-slate-500 italic">No options defined for this block.</p>';
        }

        $html = '';
        foreach ($definition as $key => $field) {
            $value = $values[$key] ?? $field['default'] ?? null;
            $html .= $this->renderField($key, $field, $value);
        }

        return $html;
    }

    // -------------------------------------------------------------------------
    // Field dispatching
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $field */
    private function renderField(string $key, array $field, mixed $value): string
    {
        return match ($field['type'] ?? 'text') {
            'object' => $this->renderObjectField($key, $field, $value),
            'table'  => $this->renderTableField($key, $field, $value),
            default  => $this->renderScalarField($key, $field, $value),
        };
    }

    // -------------------------------------------------------------------------
    // Scalar fields
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $field */
    private function renderScalarField(string $key, array $field, mixed $value): string
    {
        $type    = $field['type'] ?? 'text';
        $label   = e($field['label'] ?? $key);
        $inputId = 'pbf-' . e($key);
        $desc    = $this->descHtml($field);

        $html  = '<div class="' . self::GROUP_CLASS . '">';
        $html .= "<label for=\"{$inputId}\" class=\"" . self::LABEL_CLASS . "\">{$label}</label>{$desc}";
        $html .= $this->buildScalarInput($inputId, "data-pb-field=\"{$key}\"", $type, $field, $value);
        $html .= '</div>';

        return $html;
    }

    /**
     * Build the actual <input>, <select>, or <textarea> element.
     * Used for both top-level scalar fields and object sub-fields.
     *
     * @param  array<string, mixed>  $field
     */
    private function buildScalarInput(
        string $inputId,
        string $dataAttr,
        string $type,
        array $field,
        mixed $value
    ): string {
        if (self::isFileUploadType($type)) {
            return $this->buildFileInput($inputId, $dataAttr, $field, $value, $type === 'image' ? 'image/*' : null);
        }

        if ($type === 'boolean') {
            $checked = ($value === true || $value === 'true' || $value === 1) ? 'checked' : '';

            return "<input type=\"checkbox\" id=\"{$inputId}\" {$dataAttr} value=\"1\" {$checked} "
                . 'class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary">';
        }

        if ($type === 'select') {
            $html = "<select id=\"{$inputId}\" {$dataAttr} class=\"" . self::INPUT_CLASS . '">';
            foreach ($field['options'] ?? [] as $opt) {
                [$optVal, $optLabel] = $this->resolveOption($opt);
                $selected            = (string) $optVal === (string) $value ? 'selected' : '';
                $html .= '<option value="' . e($optVal) . "\" {$selected}>" . e($optLabel) . '</option>';
            }
            $html .= '</select>';

            return $html;
        }

        if ($type === 'textarea') {
            $escaped = e($this->str($value));

            return "<textarea id=\"{$inputId}\" {$dataAttr} rows=\"3\" class=\"" . self::INPUT_CLASS . "\">{$escaped}</textarea>";
        }

        if ($type === 'number') {
            $min = isset($field['min']) ? 'min="' . $field['min'] . '"' : '';
            $max = isset($field['max']) ? 'max="' . $field['max'] . '"' : '';

            return "<input type=\"number\" id=\"{$inputId}\" {$dataAttr} value=\"" . e($this->str($value)) . "\" {$min} {$max} class=\"" . self::INPUT_CLASS . '">';
        }

        $inputType = $this->safeInputType($type);

        return "<input type=\"{$inputType}\" id=\"{$inputId}\" {$dataAttr} value=\"" . e($this->str($value)) . '" class="' . self::INPUT_CLASS . '">';
    }

    // -------------------------------------------------------------------------
    // Object field
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $field */
    private function renderObjectField(string $key, array $field, mixed $value): string
    {
        $label  = e($field['label'] ?? $key);
        $desc   = $this->descHtml($field);
        $fields = $field['fields'] ?? [];
        $obj    = is_array($value) && ! array_is_list($value) ? $value : [];

        $html  = '<div class="' . self::GROUP_CLASS . "\" data-pb-object=\"{$key}\">";
        $html .= '<span class="' . self::LABEL_CLASS . "\">{$label}</span>{$desc}";
        $html .= '<div class="border border-slate-200 rounded-md p-3 space-y-3 bg-slate-50">';

        if (empty($fields)) {
            $html .= '<p class="text-xs text-slate-400 italic">No sub-fields defined.</p>';
        } else {
            foreach ($fields as $subKey => $subField) {
                $subValue = $obj[$subKey] ?? $subField['default'] ?? null;
                $html .= $this->renderSubField($key, $subKey, $subField, $subValue);
            }
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * A sub-field inside an object — same inputs but smaller labels and data-pb-sub-field attr.
     *
     * @param  array<string, mixed>  $subField
     */
    private function renderSubField(string $parentKey, string $subKey, array $subField, mixed $value): string
    {
        $type    = $subField['type'] ?? 'text';
        $label   = e($subField['label'] ?? $subKey);
        $inputId = 'pbf-' . e($parentKey) . '-' . e($subKey);
        $attr    = "data-pb-sub-field=\"{$subKey}\"";

        $html  = '<div class="form-group">';
        $html .= "<label for=\"{$inputId}\" class=\"block text-xs font-medium text-slate-600 mb-1\">{$label}</label>";
        $html .= $this->buildScalarInput($inputId, $attr, $type, $subField, $value);
        $html .= '</div>';

        return $html;
    }

    // -------------------------------------------------------------------------
    // Table field
    // -------------------------------------------------------------------------

    private const WIDE_TABLE_THRESHOLD = 3; // columns > this → vertical card layout

    /** @param array<string, mixed> $field */
    private function renderTableField(string $key, array $field, mixed $value): string
    {
        $label  = e($field['label'] ?? $key);
        $desc   = $this->descHtml($field);
        $fields = $field['fields'] ?? [];
        $rows   = is_array($value) ? array_values($value) : [];

        $html  = '<div class="' . self::GROUP_CLASS . "\" data-pb-table=\"{$key}\">";
        $html .= '<span class="' . self::LABEL_CLASS . "\">{$label}</span>{$desc}";

        if (empty($fields)) {
            $html .= '<p class="text-xs text-slate-400 italic">No columns defined.</p>';
        } elseif (count($fields) > self::WIDE_TABLE_THRESHOLD) {
            $html .= $this->renderTableVertical($key, $fields, $rows);
        } else {
            $html .= $this->renderTableHorizontal($key, $fields, $rows);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Horizontal <table> layout — for tables with few columns.
     *
     * @param  array<string, array<string, mixed>>  $fields
     * @param  array<mixed>  $rows
     */
    private function renderTableHorizontal(string $key, array $fields, array $rows): string
    {
        $html  = '<div class="border border-slate-200 rounded-md overflow-hidden">';
        $html .= '<div class="overflow-x-auto"><table class="w-full text-sm">';

        // Header
        $html .= '<thead class="bg-slate-100"><tr>';
        foreach ($fields as $colKey => $colField) {
            $html .= '<th class="px-3 py-2 text-left text-xs font-medium text-slate-600 uppercase tracking-wide whitespace-nowrap">'
                . e($colField['label'] ?? $colKey) . '</th>';
        }
        $html .= '<th class="px-2 py-2 w-8"></th>';
        $html .= '</tr></thead>';

        // Rows
        $html .= '<tbody data-pb-rows-container>';
        foreach ($rows as $i => $row) {
            $html .= $this->renderTableRowHorizontal($key, $fields, is_array($row) ? $row : [], $i);
        }
        $html .= '</tbody></table></div>';

        $html .= "<template data-pb-row-template=\"{$key}\">"
            . $this->renderTableRowHorizontal($key, $fields, [], 'tpl')
            . '</template>';

        $html .= $this->addRowButton($key);
        $html .= '</div>';

        return $html;
    }

    /**
     * Vertical card layout — for tables with many columns.
     *
     * @param  array<string, array<string, mixed>>  $fields
     * @param  array<mixed>  $rows
     */
    private function renderTableVertical(string $key, array $fields, array $rows): string
    {
        $html  = '<div style="display:flex;flex-direction:column;gap:0.75rem;" data-pb-rows-container>';
        foreach ($rows as $i => $row) {
            $html .= $this->renderTableRowVertical($key, $fields, is_array($row) ? $row : [], $i);
        }
        $html .= '</div>';

        $html .= "<template data-pb-row-template=\"{$key}\">"
            . $this->renderTableRowVertical($key, $fields, [], 'tpl')
            . '</template>';

        $html .= $this->addRowButton($key);

        return $html;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $rowData
     * @param  int|string  $rowIndex  Row index for unique cell IDs; use 'tpl' for template row.
     */
    private function renderTableRowHorizontal(string $tableKey, array $fields, array $rowData, int|string $rowIndex): string
    {
        $html = '<tr class="border-t border-slate-100" data-pb-row>';
        foreach ($fields as $colKey => $colField) {
            $colValue = $rowData[$colKey] ?? $colField['default'] ?? '';
            $html .= '<td class="px-2 py-1">' . $this->renderTableCell($tableKey, $colKey, $colField, $colValue, $rowIndex) . '</td>';
        }
        $html .= '<td class="px-2 py-1 text-center">' . $this->removeRowButton() . '</td>';
        $html .= '</tr>';

        return $html;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @param  array<string, mixed>  $rowData
     * @param  int|string  $rowIndex  Row index for unique cell IDs; use 'tpl' for template row.
     */
    private function renderTableRowVertical(string $tableKey, array $fields, array $rowData, int|string $rowIndex): string
    {
        $html  = '<div style="border:1px solid #e2e8f0;border-radius:0.375rem;padding:1.25rem;background:#fff;" data-pb-row>';
        $html .= '<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.625rem 1.5rem;">';
        foreach ($fields as $colKey => $colField) {
            $colValue = $rowData[$colKey] ?? $colField['default'] ?? '';
            $label    = e($colField['label'] ?? $colKey);
            $html .= '<div>';
            $html .= "<label style=\"display:block;font-size:0.75rem;font-weight:500;color:#475569;margin-bottom:0.375rem;\">{$label}</label>";
            $html .= $this->renderTableCell($tableKey, $colKey, $colField, $colValue, $rowIndex);
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '<div style="margin-top:0.75rem;display:flex;justify-content:flex-end;">' . $this->removeRowButton() . '</div>';
        $html .= '</div>';

        return $html;
    }

    private function removeRowButton(): string
    {
        return '<button type="button" data-pb-remove-row '
            . 'class="text-slate-400 hover:text-red-500 text-xs leading-none" aria-label="Remove row">'
            . '<i class="fas fa-times" aria-hidden="true"></i> Remove</button>';
    }

    private function addRowButton(string $key): string
    {
        return '<div class="mt-2">'
            . "<button type=\"button\" data-pb-add-row=\"{$key}\" "
            . 'class="text-xs text-primary hover:underline font-medium">'
            . '<i class="fas fa-plus mr-1" aria-hidden="true"></i>Add row</button>'
            . '</div>';
    }

    /**
     * @param  array<string, mixed>  $field
     * @param  int|string  $rowIndex  Row index for unique IDs; use 'tpl' for template row.
     */
    private function renderTableCell(string $tableKey, string $colKey, array $field, mixed $value, int|string $rowIndex): string
    {
        $type  = $field['type'] ?? 'text';
        $cls   = 'w-full px-2 py-1 border border-slate-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary';
        $attr  = "data-pb-col=\"{$colKey}\"";

        if ($type === 'boolean') {
            $checked = ($value === true || $value === 'true' || $value === 1) ? 'checked' : '';

            return "<input type=\"checkbox\" {$attr} value=\"1\" {$checked} class=\"h-4 w-4 rounded border-slate-300\">";
        }

        if ($type === 'select') {
            $html = "<select {$attr} class=\"{$cls}\">";
            foreach ($field['options'] ?? [] as $opt) {
                [$optVal, $optLabel] = $this->resolveOption($opt);
                $selected            = (string) $optVal === (string) $value ? 'selected' : '';
                $html .= '<option value="' . e($optVal) . "\" {$selected}>" . e($optLabel) . '</option>';
            }
            $html .= '</select>';

            return $html;
        }

        if ($type === 'number') {
            $min = isset($field['min']) ? 'min="' . $field['min'] . '"' : '';
            $max = isset($field['max']) ? 'max="' . $field['max'] . '"' : '';

            return "<input type=\"number\" {$attr} value=\"" . e($this->str($value)) . "\" {$min} {$max} class=\"{$cls}\">";
        }

        if (self::isFileUploadType($type)) {
            return $this->buildFileInputForCell($tableKey, $colKey, $rowIndex, $field, $value, $type === 'image' ? 'image/*' : null);
        }

        $inputType = $this->safeInputType($type);

        return "<input type=\"{$inputType}\" {$attr} value=\"" . e($this->str($value)) . "\" class=\"{$cls}\">";
    }

    /**
     * File/image upload cell — hidden URL input + preview + file input.
     * ID uses tableKey-colKey-rowIndex so template row can use 'tpl' and JS can assign unique id on clone.
     *
     * @param  array<string, mixed>  $field
     */
    private function buildFileInputForCell(
        string $tableKey,
        string $colKey,
        int|string $rowIndex,
        array $field,
        mixed $value,
        ?string $defaultAccept
    ): string {
        $inputId = 'pbf-' . e($tableKey) . '-' . e($colKey) . '-' . e((string) $rowIndex);
        $attr    = "data-pb-col=\"{$colKey}\"";

        return $this->buildFileInput($inputId, $attr, $field, $value, $defaultAccept);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** @param array<string, mixed> $field */
    private function descHtml(array $field): string
    {
        return isset($field['description'])
            ? '<p class="text-xs text-slate-500 mt-0.5 mb-1">' . e((string) $field['description']) . '</p>'
            : '';
    }

    private function resolveOption(mixed $opt): array
    {
        if (is_array($opt)) {
            $val   = (string) ($opt['value'] ?? '');
            $label = (string) ($opt['label'] ?? $val);

            return [$val, $label];
        }

        $str = (string) $opt;

        return [$str, $str];
    }

    /**
     * Render a file/upload field.
     * Stores the resulting URL in a hidden input (data-pb-field / data-pb-sub-field / data-pb-col).
     * A visible <input type="file"> carries data-pb-upload-input and data-pb-upload-target pointing
     * to the hidden input's id so BlockOptionManager can upload and populate it before saving.
     *
     * @param  array<string, mixed>  $field
     */
    private function buildFileInput(string $inputId, string $dataAttr, array $field, mixed $value, ?string $defaultAccept = null): string
    {
        $url     = $this->str($value);
        $accept  = e($field['accept'] ?? $defaultAccept ?? '*/*');
        $isImage = $url !== '' && (bool) preg_match(self::IMAGE_EXT_REGEX, $url);

        $html = '<div class="space-y-1" data-pb-upload-wrapper data-pb-upload-target-id="' . e($inputId) . '">';

        // Hidden input holds the resolved URL and is read by collectPanelValues
        $html .= "<input type=\"hidden\" id=\"{$inputId}\" {$dataAttr} value=\"" . e($url) . '">';

        // Disabled input showing the current URL (updated by JS after upload)
        $html .= '<input type="text" data-pb-upload-url value="' . e($url) . '" disabled '
            . 'class="w-full px-3 py-2 border border-slate-200 rounded-md text-sm bg-slate-50 text-slate-600" '
            . 'placeholder="' . e(__t('URL will appear after upload')) . '">';

        // Preview area: image thumb or filename (with label and placeholder when empty)
        $html .= '<div class="mt-1">';
        $html .= '<span class="block text-xs font-medium text-slate-500 mb-1">' . e(__t('Preview')) . '</span>';
        $html .= '<div data-pb-upload-preview class="min-h-[6rem] flex items-center justify-center rounded-md border border-slate-200 bg-slate-50/50 p-2">';
        if ($url !== '') {
            if ($isImage) {
                $html .= '<img src="' . e($url) . '" class="max-h-40 w-auto object-contain rounded border border-slate-200" alt="">';
            } else {
                $html .= '<span class="text-xs text-slate-600 break-all">' . e(basename($url)) . '</span>';
            }
        } else {
            $html .= '<span class="text-xs text-slate-400 italic">' . e(__t('Preview will appear here after upload')) . '</span>';
        }
        $html .= '</div></div>';

        // File picker + clear button row
        $html .= '<div class="flex items-center gap-2 flex-wrap">';
        $html .= "<input type=\"file\" data-pb-upload-input data-pb-upload-target=\"{$inputId}\" accept=\"{$accept}\" "
            . 'class="block flex-1 min-w-0 text-sm text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 '
            . 'file:text-xs file:font-medium file:bg-primary file:text-white hover:file:opacity-90 cursor-pointer">';
        $html .= '<button type="button" data-pb-upload-clear data-pb-upload-target="' . e($inputId) . '" '
            . 'class="shrink-0 text-xs text-slate-500 hover:text-red-600" aria-label="' . e(__t('Remove image')) . '">'
            . '<i class="fas fa-times mr-1" aria-hidden="true"></i>' . e(__t('Remove')) . '</button>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    private function safeInputType(string $type): string
    {
        return in_array($type, ['text', 'email', 'url', 'color', 'date', 'time', 'password'], true)
            ? $type
            : 'text';
    }

    /** Safely cast any value to string — arrays become empty string to avoid conversion errors. */
    private function str(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return '';
        }

        return (string) ($value ?? '');
    }
}
