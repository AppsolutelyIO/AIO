<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Helpers;

final class AdminButtonHelper
{
    /**
     * Generate the HTML for a button that calls a JS function (e.g. onclick="markEntrySpam(id)").
     * Returns only the <a> tag; the script must be added via scriptForApiButton() + admin_script().
     *
     * Required: function_name, api_url.
     * Options: text, icon, class, attributes, payload.
     */
    public static function apiButton(array $options): string
    {
        $allowed = ['text', 'icon', 'class', 'attributes', 'function_name', 'api_url', 'payload'];
        $options = array_intersect_key($options, array_flip($allowed));

        $defaults = [
            'text'       => 'Action',
            'icon'       => null,
            'class'      => 'text-primary',
            'attributes' => [],
        ];

        $config = array_merge($defaults, $options);

        // Validate required options
        if (empty($config['api_url'])) {
            throw new \InvalidArgumentException('api_url is required');
        }

        if (empty($config['function_name'])) {
            throw new \InvalidArgumentException('function_name is required');
        }

        // Generate unique function name if needed
        $functionName = $config['function_name'];

        $data = [
            'href'    => 'javascript:void(0)',
            'class'   => trim($config['class']),
            'onclick' => $functionName . '(' . ($config['payload'] ?? '') . ')',
        ];

        // Build button attributes
        $attributes = array_merge($config['attributes'], $data);

        // Build attribute string
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= " {$key}=\"" . htmlspecialchars($value) . '"';
        }

        // Build button content
        $content = '';
        if ($config['icon']) {
            $content .= "<i class=\"{$config['icon']}\"></i> ";
        }
        $content .= "<span>{$config['text']}</span> ";

        return "\n<a{$attrString}>{$content}</a>";
    }

    /**
     * Generate the script for an API button. Call once per page/grid and pass the result to admin_script().
     * Use the same function_name and api_url as apiButton() (use __ID__ in api_url for row-specific URLs).
     *
     * Required: function_name, api_url.
     * Options: confirm, method, success_message, error_message, refresh, redirect, payload, additional_payload.
     */
    public static function scriptForApiButton(array $options): string
    {
        $allowed = [
            'function_name', 'api_url', 'confirm', 'method', 'success_message', 'error_message',
            'refresh', 'redirect', 'payload', 'additional_payload',
        ];
        $options = array_intersect_key($options, array_flip($allowed));

        $defaults = [
            'confirm'         => null,
            'method'          => 'POST',
            'success_message' => null,
            'error_message'   => null,
            'refresh'         => true,
            'redirect'        => null,
        ];

        $config = array_merge($defaults, $options);

        if (empty($config['api_url'])) {
            throw new \InvalidArgumentException('api_url is required');
        }

        if (empty($config['function_name'])) {
            throw new \InvalidArgumentException('function_name is required');
        }

        return self::generateJavaScriptFunction($config['function_name'], $config);
    }

    /**
     * Generate delete button with confirmation
     */
    public static function deleteButton(int $id, string $apiUrl): string
    {
        return self::apiButton([
            'text'          => __t('Delete'),
            'icon'          => 'fa fa-trash',
            'class'         => 'text-danger',
            'function_name' => 'deleteItem',
            'api_url'       => $apiUrl,
            'payload'       => $id,
        ]);
    }

    /**
     * Generate toggle status button
     */
    public static function toggleStatusButton(int $id, string $apiUrl, bool $currentStatus): string
    {
        $action = $currentStatus ? 'disable' : 'enable';
        $style  = $currentStatus ? 'text-warning' : 'text-success';
        $icon   = $currentStatus ? 'fa fa-pause' : 'fa fa-play';

        return self::apiButton([
            'text'          => $currentStatus ? __t('Disable') : __t('Enable'),
            'icon'          => $icon,
            'class'         => $style,
            'function_name' => 'toggleStatus',
            'api_url'       => $apiUrl,
            'payload'       => $id,
        ]);
    }

    /**
     * Generate duplicate button
     */
    public static function duplicateButton(int $id, string $apiUrl): string
    {
        return self::apiButton([
            'text'          => __t('Duplicate'),
            'icon'          => 'fa fa-copy',
            'class'         => 'text-info',
            'function_name' => 'duplicateItem',
            'api_url'       => $apiUrl,
            'payload'       => $id,
        ]);
    }

    /**
     * Generate preview button
     */
    public static function previewButton(int $id, string $modalUrl): string
    {
        return self::apiButton([
            'text'          => __t('Preview'),
            'icon'          => 'fa fa-eye',
            'function_name' => 'previewItem',
            'api_url'       => $modalUrl,
            'payload'       => $id,
        ]);
    }

    /**
     * Generate JavaScript function for API calls.
     * When api_url contains __ID__, the URL is built at runtime (one script serves all rows).
     */
    protected static function generateJavaScriptFunction(string $functionName, array $config): string
    {
        $hasPayload    = isset($config['payload']) || str_contains($config['api_url'], '__ID__');
        $paramList     = $hasPayload ? 'id' : '';
        $urlIsTemplate = str_contains($config['api_url'], '__ID__');

        // Assign to window so onclick="markEntryValid(id)" works (admin_script() wraps code in an immediately-invoked function)
        $js = "window.{$functionName} = function({$paramList}) {\n";

        // Add confirmation if specified
        if ($config['confirm']) {
            $js .= "    if (!confirm('" . addslashes($config['confirm']) . "')) {\n";
            $js .= "        return false;\n";
            $js .= "    }\n\n";
        }

        // Build URL when using __ID__ template
        if ($urlIsTemplate) {
            $escapedUrl = addslashes($config['api_url']);
            $js .= "    var url = '" . $escapedUrl . "'.replace('__ID__', id);\n\n";
        }

        // Build payload
        $js .= "    var payload = {};\n";
        if ($hasPayload) {
            $js .= "    payload.id = id;\n";
        }

        // Add any additional payload data
        if (isset($config['additional_payload']) && is_array($config['additional_payload'])) {
            foreach ($config['additional_payload'] as $key => $value) {
                $js .= "    payload.{$key} = " . json_encode($value) . ";\n";
            }
        }

        // AJAX call
        $js .= "\n    $.ajax({\n";
        $js .= '        url: ' . ($urlIsTemplate ? 'url' : "'" . addslashes($config['api_url']) . "'") . ",\n";
        $js .= "        type: '" . $config['method'] . "',\n";
        $js .= "        data: payload,\n";
        $js .= "        headers: {\n";
        $js .= "            'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')\n";
        $js .= "        },\n";
        $js .= "        success: function(response) {\n";

        if ($config['success_message']) {
            $js .= "            AIO.success('" . addslashes($config['success_message']) . "');\n";
        }

        if ($config['refresh']) {
            $js .= "            AIO.reload();\n";
        }

        if ($config['redirect']) {
            $js .= "            window.location.href = '" . $config['redirect'] . "';\n";
        }

        // Handle preview/modal response
        if ($config['method'] === 'GET' && ! $config['refresh']) {
            $js .= "            if (response.data && response.data.content) {\n";
            $js .= "                var modal = $('<div class=\"modal fade\" tabindex=\"-1\">').html(response.data.content);\n";
            $js .= "                $('body').append(modal);\n";
            $js .= "                modal.modal('show');\n";
            $js .= "                modal.on('hidden.bs.modal', function() {\n";
            $js .= "                    modal.remove();\n";
            $js .= "                });\n";
            $js .= "            }\n";
        }

        $js .= "        },\n";
        $js .= "        error: function(xhr, status, error) {\n";

        if ($config['error_message']) {
            $js .= "            var message = '" . addslashes($config['error_message']) . "';\n";
            $js .= "            if (xhr.responseJSON && xhr.responseJSON.message) {\n";
            $js .= "                message = xhr.responseJSON.message;\n";
            $js .= "            }\n";
            $js .= "            AIO.error(message);\n";
        } else {
            $js .= "            AIO.error('An error occurred');\n";
        }

        $js .= "        }\n";
        $js .= "    });\n";
        $js .= "};\n";

        return $js;
    }

    /**
     * Generate multiple buttons in a button group
     */
    public static function buttonGroup(array $buttons): string
    {
        $html = '<div class="btn-group" role="group">';

        foreach ($buttons as $button) {
            $html .= $button;
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Generate a custom API button with full control
     */
    public static function customButton(string $text, string $functionName, string $apiUrl, array $options = []): string
    {
        $config = array_merge([
            'text'          => $text,
            'function_name' => $functionName,
            'api_url'       => $apiUrl,
        ], $options);

        return self::apiButton($config);
    }
}
